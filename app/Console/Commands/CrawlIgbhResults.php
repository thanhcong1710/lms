<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;

class CrawlIgbhResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lms:crawl-igbh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl IG.BH diagnostic evaluation data from legacy LMS and store in database';

    // Cookies file
    private $cookieFile = '/tmp/igbh_cookies_artisan.txt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting IG.BH Crawler...");

        // 1. Login
        if (!$this->loginToLms()) {
            $this->error("Failed to login to LMS.");
            return;
        }
        $this->info("Logged in successfully.");

        // 2. Fetch Diag List
        $resultList = $this->fetchResultList();
        if (empty($resultList)) {
            $this->error("No results found on LMS.");
            return;
        }

        $this->info("Found " . count($resultList) . " result items. Start processing...");

        $bar = $this->output->createProgressBar(count($resultList));
        $bar->start();

        foreach ($resultList as $item) {
            // Save test mapping
            $this->saveTestInfo($item);

            // Process student result & detail
            $this->processStudentResult($item);

            $bar->advance();
            usleep(150000); // polite sleep
        }

        $bar->finish();
        $this->info("\nDone crawling IG.BH data!");
    }

    private function loginToLms()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'membId' => 'loanntp',
            'membPasswd' => '@12345678'
        ]));
        
        $headers = [
            'Accept: application/json, text/javascript, */*; q=0.01',
            'X-Requested-With: XMLHttpRequest',
            'User-Agent: Mozilla/5.0'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $res = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($res, true);
        return isset($json['result']) && $json['result'] == 1;
    }

    private function fetchResultList()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $params = [
            'draw' => 1,
            'start' => 0,
            'length' => 1000,
            'searchCols' => '',
            'searchText' => '',
            'searchDatas[0][col]' => 'quarterCd',
            'searchDatas[0][val]' => '',
            'searchDatas[1][col]' => 'levelCd',
            'searchDatas[1][val]' => ''
        ];
        
        curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/getDiagList.do?' . http_build_query($params));
        
        $headers = [
            'Accept: application/json, text/javascript, */*; q=0.01',
            'X-Requested-With: XMLHttpRequest',
            'User-Agent: Mozilla/5.0'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $res = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($res, true);
        return $json['data'] ?? [];
    }

    private function saveTestInfo($item)
    {
        DB::table('igbh_tests')->updateOrInsert(
            [
                'test_seq' => $item['testSeq'],
            ],
            [
                'test_nm' => $item['evalNm'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function processStudentResult($item)
    {
        try {
            $testSeq = $item['testSeq'];
            $stuSeq = $item['stuSeq'];
            $resultSeq = $item['resultSeq'];

            // 1. Fetch diagMod.do HTML (to get saSeq, taSeq, form values)
            $modHtml = $this->fetchUrl('https://lms-vn.cmsedu.net/igel/diagMod.do', [
                'testSeq' => $testSeq,
                'stuSeq' => $stuSeq
            ]);

            // 2. Fetch viewReportIGDiag.do HTML (to get unit categorizations, is_correct status, max scores)
            $reportHtml = $this->fetchUrl('https://lms-vn.cmsedu.net/igel/viewReportIGDiag.do', [
                'resultSeq' => $resultSeq,
                'testSeq' => $testSeq,
                'stuSeq' => $stuSeq
            ]);

            // Parse
            $parsed = $this->parseHtmlData($modHtml, $reportHtml);

            // Save general result
            DB::table('igbh_student_results')->updateOrInsert(
                [
                    'result_seq' => $resultSeq
                ],
                [
                    'test_seq' => $testSeq,
                    'stu_seq' => $stuSeq,
                    'stu_nm' => $item['stuNm'],
                    'stu_birth_dt' => $item['stuBirthDt'] ?? null,
                    'reg_name' => $item['regName'] ?? null,
                    'eval_dt' => $item['evalDt'] ?? null,
                    'reg_date' => $item['regDate'] ? date('Y-m-d H:i:s', strtotime($item['regDate'])) : null,
                    
                    'total_score' => $item['totalScore'] ?? 0,
                    'subject_total' => $item['subjectTotal'] ?? 0,
                    'thinking_total' => $item['thinkingTotal'] ?? 0,
                    
                    'assigned_level' => $item['assignedLevel'] ?? null,
                    'quarter_cd' => $item['quarterCd'] ?? null,
                    'quarter_cd_nm' => $item['quarterCdNm'] ?? null,
                    'class_type_cd' => $item['classTypeCd'] ?? null,
                    
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $resultRow = DB::table('igbh_student_results')->where('result_seq', $resultSeq)->first();

            // Clear old details
            DB::table('igbh_student_result_details')->where('igbh_student_result_id', $resultRow->id)->delete();

            // Bulk Insert details
            $details = [];
            
            // Add curriculum details
            foreach ($parsed['curriculum'] as $qNo => $data) {
                $details[] = [
                    'igbh_student_result_id' => $resultRow->id,
                    'question_no' => $qNo,
                    'question_type' => 'curriculum',
                    'seq_id' => $data['saSeq'] ?? null,
                    'assigned_score' => $data['answer'] ?? null,
                    'unit' => $data['unit'] ?? null,
                    'is_correct' => $data['is_correct'] ?? null,
                    'max_score' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Add thinking details
            foreach ($parsed['thinking'] as $qNo => $data) {
                $details[] = [
                    'igbh_student_result_id' => $resultRow->id,
                    'question_no' => $qNo,
                    'question_type' => 'thinking',
                    'seq_id' => $data['taSeq'] ?? null,
                    'assigned_score' => $data['score'] ?? null,
                    'unit' => null,
                    'is_correct' => null,
                    'max_score' => $data['max_score'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($details)) {
                DB::table('igbh_student_result_details')->insert($details);
            }

        } catch (\Exception $e) {
            Log::error("Failed to process IG.BH resultSeq {$item['resultSeq']}: " . $e->getMessage());
        }
    }

    private function parseHtmlData($modHtml, $reportHtml)
    {
        $curriculum = [];
        $thinking = [];

        // 1. Parse modHtml (Get saSeq/answer and taSeq/score)
        $domMod = new DOMDocument();
        @$domMod->loadHTML('<?xml encoding="UTF-8">' . $modHtml);
        $xpathMod = new DOMXPath($domMod);

        // Curriculum
        $answerInputs = $xpathMod->query('//input[@name="answer"]');
        foreach ($answerInputs as $input) {
            $id = $input->getAttribute('id'); // answer_1
            $qNo = (int)str_replace('answer_', '', $id);
            $val = $input->getAttribute('value');
            
            $parent = $input->parentNode;
            $saSeqNode = $xpathMod->query('.//input[@name="saSeq"]', $parent);
            $saSeq = $saSeqNode->length > 0 ? (int)$saSeqNode->item(0)->getAttribute('value') : null;
            
            $curriculum[$qNo] = [
                'saSeq' => $saSeq,
                'answer' => $val
            ];
        }

        // Thinking
        $scoreInputs = $xpathMod->query('//input[@name="score"]');
        foreach ($scoreInputs as $input) {
            $id = $input->getAttribute('id'); // score_1
            $qNo = (int)str_replace('score_', '', $id);
            $val = $input->getAttribute('value');
            
            $parent = $input->parentNode;
            $taSeqNode = $xpathMod->query('.//input[@name="taSeq"]', $parent);
            $taSeq = $taSeqNode->length > 0 ? (int)$taSeqNode->item(0)->getAttribute('value') : null;
            
            $thinking[$qNo] = [
                'taSeq' => $taSeq,
                'score' => $val
            ];
        }

        // 2. Parse reportHtml (Get Unit categories, correct status, thinking max scores)
        $domReport = new DOMDocument();
        @$domReport->loadHTML('<?xml encoding="UTF-8">' . $reportHtml);
        $xpathReport = new DOMXPath($domReport);

        // Fetch curriculum unit/is_correct
        $tableWhole = $xpathReport->query('//table[contains(@class, "table_whole") and .//th[text()="Mục"]]');
        if ($tableWhole->length > 0) {
            $tbodyRows = $xpathReport->query('.//tbody/tr', $tableWhole->item(0));
            if ($tbodyRows->length >= 3) {
                // Row 0 is Unit
                $unitTds = $xpathReport->query('td', $tbodyRows->item(0));
                // Row 2 is Result
                $correctTds = $xpathReport->query('td', $tbodyRows->item(2));
                
                for ($j = 0; $j < 20; $j++) {
                    $qNo = $j + 1;
                    $unit = $unitTds->length > $j ? trim($unitTds->item($j)->nodeValue) : null;
                    $correctNode = $correctTds->length > $j ? $correctTds->item($j) : null;
                    $isCorrect = $correctNode ? trim($correctNode->nodeValue) : null;
                    
                    if (isset($curriculum[$qNo])) {
                        $curriculum[$qNo]['unit'] = $unit;
                        $curriculum[$qNo]['is_correct'] = $isCorrect;
                    } else {
                        $curriculum[$qNo] = [
                            'saSeq' => null,
                            'answer' => null,
                            'unit' => $unit,
                            'is_correct' => $isCorrect
                        ];
                    }
                }
            }
        }

        // Fetch thinking max scores
        $tbodyRows = $xpathReport->query('//table[contains(@class, "table_whole")]/tbody/tr');
        foreach ($tbodyRows as $row) {
            $th = $xpathReport->query('th', $row);
            if ($th->length > 0) {
                $rowName = trim($th->item(0)->nodeValue);
                $tds = $xpathReport->query('td', $row);
                if ($rowName === 'Điểm chuẩn') {
                    for ($j = 0; $j < 10; $j++) {
                        $qNo = $j + 1;
                        $maxScore = $tds->length > $j ? (int)trim($tds->item($j)->nodeValue) : null;
                        if (isset($thinking[$qNo])) {
                            $thinking[$qNo]['max_score'] = $maxScore;
                        } else {
                            $thinking[$qNo] = [
                                'taSeq' => null,
                                'score' => null,
                                'max_score' => $maxScore
                            ];
                        }
                    }
                }
            }
        }

        return [
            'curriculum' => $curriculum,
            'thinking' => $thinking
        ];
    }

    private function fetchUrl($url, $postData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        
        $headers = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'User-Agent: Mozilla/5.0'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }
}
