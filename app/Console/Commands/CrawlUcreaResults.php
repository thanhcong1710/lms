<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;

class CrawlUcreaResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lms:crawl-ucrea';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl UCREA evaluation data from the legacy LMS system and store in database';

    // Cookies file
    private $cookieFile = '/tmp/lms_cookies_artisan.txt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting UCREA Crawler...");

        // 1. Login to LMS
        if (!$this->loginToLms()) {
            $this->error("Failed to login to LMS.");
            return;
        }
        $this->info("Logged in successfully.");

        // 2. Fetch Result List
        $resultList = $this->fetchResultList();
        if (empty($resultList)) {
            $this->error("No result list found.");
            return;
        }
        
        $this->info("Found " . count($resultList) . " result items. Start crawling details...");

        $bar = $this->output->createProgressBar(count($resultList));
        $bar->start();

        foreach ($resultList as $item) {
            // Save general test info
            $this->saveTestInfo($item);

            // Only crawl detail if it's graded
            if (in_array($item['resultCd'], ['IS002', 'IS003', 'IS004'])) {
                $this->processStudentResult($item);
            }
            $bar->advance();
            // Sleep to avoid overloading the server
            usleep(200000); 
        }

        $bar->finish();
        $this->info("\nDone crawling UCREA data!");
    }

    private function saveTestInfo($item)
    {
        DB::table('ucrea_tests')->updateOrInsert(
            [
                'test_cd' => $item['testCd'],
                'level_cd' => $item['levelCd'],
                'test_seq' => $item['testSeq'],
            ],
            [
                'test_nm' => $item['testNm'],
                'level_cd_nm' => $item['levelCdNm'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function processStudentResult($item)
    {
        try {
            $testCd = $item['testCd'];
            $levelCd = $item['levelCd'];
            $testSeq = $item['testSeq'];
            $resultSeq = $item['resultSeq'];

            // 1. Fetch inputView.do
            $inputViewHtml = $this->fetchUrl('https://lms-vn.cmsedu.net/eval/inputView.do', [
                'testCd' => $testCd,
                'levelCd' => $levelCd,
                'testSeq' => $testSeq,
                'resultSeq' => $resultSeq,
                'paramMap' => ''
            ]);

            // 2. Fetch viewReport.do
            $viewReportHtml = $this->fetchUrl('https://lms-vn.cmsedu.net/eval/viewReport.do', [
                'testCd' => $testCd,
                'levelCd' => $levelCd,
                'testSeq' => $testSeq,
                'resultSeq' => $resultSeq
            ]);

            // 3. Parse data
            $parsedData = $this->parseHtml($inputViewHtml, $viewReportHtml);

            // 4. Save to DB
            $studentResultId = DB::table('ucrea_student_results')->updateOrInsert(
                [
                    'result_seq' => $resultSeq
                ],
                [
                    'test_cd' => $testCd,
                    'level_cd' => $levelCd,
                    'test_seq' => $testSeq,
                    'memb_nm' => $item['membNm'],
                    'stu_nm' => $item['stuNm'],
                    'class_nm' => $item['classNm'] ?? null,
                    'eval_dt' => $item['evalDt'] ?? null,
                    'result_cd' => $item['resultCd'],
                    'result_cd_nm' => $item['resultCdNm'],
                    'report_data' => json_encode($parsedData['report_data']),
                    'skills_grade' => json_encode($parsedData['skills_grade']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // If updateOrInsert is used, we need to get the ID back
            $resultRow = DB::table('ucrea_student_results')->where('result_seq', $resultSeq)->first();

            // Clear old details
            DB::table('ucrea_student_result_details')->where('ucrea_student_result_id', $resultRow->id)->delete();

            // Insert new details
            $details = [];
            foreach ($parsedData['rubrics'] as $rubric) {
                $details[] = [
                    'ucrea_student_result_id' => $resultRow->id,
                    'question_no' => $rubric['question_no'],
                    'main_category' => $rubric['main_category'],
                    'sub_category' => $rubric['sub_category'],
                    'rubric_name' => $rubric['rubric_name'],
                    'assigned_score' => $rubric['assigned_score'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($details)) {
                DB::table('ucrea_student_result_details')->insert($details);
            }
        } catch (\Exception $e) {
            Log::error("Failed to process resultSeq {$item['resultSeq']}: " . $e->getMessage());
        }
    }

    private function parseHtml($inputViewHtml, $viewReportHtml)
    {
        $result = [
            'rubrics' => [],
            'report_data' => [],
            'skills_grade' => []
        ];

        // Parse inputView.do
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $inputViewHtml);
        $xpath = new DOMXPath($dom);

        $rows = $xpath->query('//table[contains(@class, "bulk_action")]/tbody/tr');
        for ($i = 0; $i < $rows->length; $i += 2) {
            $row = $rows->item($i);
            $tds = $xpath->query('td', $row);
            
            if ($tds->length >= 10) {
                $qNo = trim($tds->item(0)->nodeValue);
                $mainCat = trim($tds->item(1)->nodeValue);
                $subCat = trim($tds->item(2)->nodeValue);
                $rubricName = trim($tds->item(3)->nodeValue);
                
                $assignedScore = null;
                for ($j = 6; $j < $tds->length; $j += 2) {
                    $checkIcon = $xpath->query('.//i[contains(@class, "fa-check")]', $tds->item($j));
                    if ($checkIcon->length > 0) {
                        $assignedScore = trim($tds->item($j + 1)->nodeValue);
                        break;
                    }
                }
                
                $result['rubrics'][] = [
                    'question_no' => $qNo,
                    'main_category' => $mainCat,
                    'sub_category' => $subCat,
                    'rubric_name' => $rubricName,
                    'assigned_score' => $assignedScore
                ];
            }
        }

        // Parse viewReport.do (JS Data)
        if (preg_match('/var tt\d+data = (\{.*?\})/', $viewReportHtml, $matches)) {
            $jsonStr = str_replace("'", '"', $matches[1]);
            $result['report_data'] = json_decode($jsonStr, true);
        }

        // Parse viewReport.do (Table Grades)
        $domReport = new DOMDocument();
        @$domReport->loadHTML('<?xml encoding="UTF-8">' . $viewReportHtml);
        $xpathReport = new DOMXPath($domReport);

        $gradeRows = $xpathReport->query('//table[contains(@class, "table_detail")]/tbody/tr');
        foreach ($gradeRows as $row) {
            $tds = $xpathReport->query('td', $row);
            if ($tds->length == 3) {
                $skillName = trim($tds->item(0)->nodeValue);
                $gradeNode = $xpathReport->query('.//span[contains(@class, "no-show")]', $tds->item(2));
                if ($gradeNode->length > 0) {
                    $result['skills_grade'][] = [
                        'skill' => $skillName,
                        'grade' => trim($gradeNode->item(0)->nodeValue)
                    ];
                }
            }
        }

        return $result;
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
            'membId' => 'cms.vn',
            'membPasswd' => 'hpa@XuanDieu'
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
        $url = 'https://lms-vn.cmsedu.net/eval/getResultList.do?' . http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 1000,
            'searchDatas' => [
                ['col' => 'testCd', 'val' => ''],
                ['col' => 'statCd', 'val' => ''],
                ['col' => 'levelCd', 'val' => '']
            ],
            'searchCols' => '',
            'searchText' => '',
            'searchFromDt' => '',
            'searchToDt' => ''
        ]);
        curl_setopt($ch, CURLOPT_URL, $url);
        
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
}
