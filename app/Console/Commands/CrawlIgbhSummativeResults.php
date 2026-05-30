<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrawlIgbhSummativeResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:igbh-summative-results';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl IG.BH Summative Results (Summary List) from CMS LMS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting crawl for IG.BH Summative Results...");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/igbh_crawler_summative_results.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/igbh_crawler_summative_results.txt');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // 1. Login as admin
        $this->info("Logging in to CMS LMS as admin...");
        curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'membId' => 'cms.vn',
            'membPasswd' => 'hpa@XuanDieu'
        ]));
        $loginResp = curl_exec($ch);
        $loginData = json_decode($loginResp, true);
        if (!isset($loginData['result']) || $loginData['result'] != 1) {
            $this->error("Admin login failed.");
            return;
        }
        $this->info("Login successful!");

        // 2. Fetch list of summative results
        $this->info("Fetching list of summative results...");
        $params = http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 50000, // Large number to get all
            'searchDatas[0][col]' => 'testSeq',
            'searchDatas[0][val]' => '',
            'searchDatas[1][col]' => 'levelCd',
            'searchDatas[1][val]' => '',
            'searchDatas[2][col]' => 'sortCd',
            'searchDatas[2][val]' => '',
            'searchCols' => '',
            'searchText' => '',
            'searchFromDt' => '',
            'searchToDt' => ''
        ]);
        
        curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/getSmtvList.do?' . $params);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Accept: application/json, text/javascript, */*; q=0.01',
            'X-Requested-With: XMLHttpRequest'
        ]);
        
        $json = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode !== 200 || !$json) {
            $this->error("Failed to fetch list. HTTP Code: $httpCode");
            return;
        }

        $listData = json_decode($json, true);
        if (!isset($listData['data']) || !is_array($listData['data'])) {
            $this->error("Invalid JSON response or 'data' missing.");
            return;
        }

        $records = $listData['data'];
        $this->info("Found " . count($records) . " summative result records.");

        $successCount = 0;
        
        DB::beginTransaction();
        try {
            // First, empty the old summative results table
            // But if we want to update it incrementally, we can just use updateOrInsert
            
            foreach ($records as $index => $record) {
                $testSeq = $record['testSeq'] ?? null;
                $stuSeq = $record['stuSeq'] ?? null;
                
                if (!$testSeq || !$stuSeq) continue;

                $totalScore = $record['totalScore'] ?? 0;
                
                DB::table('igbh_summative_results')->updateOrInsert(
                    [
                        'test_seq' => $testSeq,
                        'stu_seq' => $stuSeq,
                    ],
                    [
                        'stu_nm' => $record['stuNm'] ?? null,
                        'class_seq' => $record['classSeq'] ?? null,
                        'class_nm' => $record['classNm'] ?? null,
                        'teacher_nm' => $record['membNm'] ?? null,
                        'total_score' => is_numeric($totalScore) ? (float)$totalScore : 0,
                        'eval_dt' => $record['evalYmd'] ?? null,
                        'status' => 'D', // Done
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                // Get the ID of the result we just inserted/updated
                $resultObj = DB::table('igbh_summative_results')
                    ->where('test_seq', $testSeq)
                    ->where('stu_seq', $stuSeq)
                    ->first();
                
                $finalSeq = $record['finalSeq'] ?? '';
                $resultSeq = $record['resultSeq'] ?? '';
                $eachSeq = $record['eachSeq'] ?? '';
                
                if ($finalSeq && $resultSeq && $resultObj) {
                    curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/viewReportIGSumm.do');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                        'finalSeq' => $finalSeq,
                        'resultSeq' => $resultSeq,
                        'eachSeq' => $eachSeq,
                        'stuSeq' => $stuSeq
                    ]));
                    
                    $html = curl_exec($ch);
                    
                    if ($html) {
                        $sectionPos = mb_strpos($html, 'Kết quả đánh giá theo câu hỏi riêng biệt');
                        if ($sectionPos !== false) {
                            $tableStart = mb_strpos($html, '<table', $sectionPos);
                            $tableEnd = mb_strpos($html, '</table>', $tableStart);
                            if ($tableStart !== false && $tableEnd !== false) {
                                $tableHtml = mb_substr($html, $tableStart, $tableEnd - $tableStart);
                                
                                $extract = function($h, $header) {
                                    $p = mb_strpos($h, $header);
                                    if ($p === false) return [];
                                    $trE = mb_strpos($h, '</tr>', $p);
                                    if ($trE === false) return [];
                                    $trH = mb_substr($h, $p, $trE - $p);
                                    preg_match_all('/<td[^>]*>(?:<span[^>]*>)?\s*([\d\.]+)\s*(?:<\/span>)?\s*<\/td>/iu', $trH, $matches);
                                    return array_slice($matches[1], 0, 5);
                                };
                                
                                $maxScores = $extract($tableHtml, 'Điểm chuẩn');
                                $scores = $extract($tableHtml, 'Điểm thực tế');
                                $concepts = $extract($tableHtml, 'Khái niệm/hiểu');
                                $strategies = $extract($tableHtml, 'Chiến lược/suy luận');
                                $calculations = $extract($tableHtml, 'Tính toán/thực hành');
                                $expressions = $extract($tableHtml, 'Diễn đạt/biểu hiện');
                                
                                DB::table('igbh_summative_result_details')->where('summative_result_id', $resultObj->id)->delete();
                                
                                for ($i = 0; $i < 5; $i++) {
                                    if (isset($scores[$i])) {
                                        DB::table('igbh_summative_result_details')->insert([
                                            'summative_result_id' => $resultObj->id,
                                            'sort_no' => $i + 1,
                                            'max_score' => isset($maxScores[$i]) ? (float)$maxScores[$i] : null,
                                            'score' => (float)$scores[$i],
                                            'concept' => isset($concepts[$i]) ? (float)$concepts[$i] : null,
                                            'strategy' => isset($strategies[$i]) ? (float)$strategies[$i] : null,
                                            'calculation' => isset($calculations[$i]) ? (float)$calculations[$i] : null,
                                            'expression' => isset($expressions[$i]) ? (float)$expressions[$i] : null,
                                            'created_at' => now(),
                                            'updated_at' => now()
                                        ]);
                                    }
                                }
                            }
                        }

                        // Extract BTM and LTM
                        $btmPos = mb_strpos($html, 'Phân tích BTM');
                        $ltmPos = mb_strpos($html, 'Phân tích LTM');
                        
                        if ($btmPos !== false && $ltmPos !== false) {
                            $extractAnalysis = function($tableHtml) {
                                preg_match_all('/<th[^>]*>(Số \d+)<\/th>/iu', $tableHtml, $qMatches);
                                $q1 = $qMatches[1][0] ?? '';
                                $q2 = $qMatches[1][1] ?? '';
                                
                                $extractData = function($h, $header) {
                                    $p = mb_strpos($h, $header);
                                    if ($p === false) return [];
                                    $trE = mb_strpos($h, '</tr>', $p);
                                    if ($trE === false) return [];
                                    $trH = mb_substr($h, $p, $trE - $p);
                                    preg_match_all('/(?:<td[^>]*>(?:<span[^>]*>)?\s*([^<]+)\s*(?:<\/span>)?\s*<\/td>)/iu', $trH, $matches);
                                    return array_map('trim', $matches[1]);
                                };
                                
                                return [
                                    'q1_label' => $q1,
                                    'q2_label' => $q2,
                                    'concept' => $extractData($tableHtml, 'Khái niệm/hiểu'),
                                    'strategy' => $extractData($tableHtml, 'Chiến lược/suy luận'),
                                    'calculation' => $extractData($tableHtml, 'Tính toán/thực hành'),
                                    'expression' => $extractData($tableHtml, 'Diễn đạt/biểu hiện'),
                                    'average' => $extractData($tableHtml, 'Trung bình')
                                ];
                            };

                            $btmEnd = mb_strpos($html, '</table>', $btmPos);
                            $btmHtml = mb_substr($html, $btmPos, $btmEnd - $btmPos);
                            
                            $ltmEnd = mb_strpos($html, '</table>', $ltmPos);
                            $ltmHtml = mb_substr($html, $ltmPos, $ltmEnd - $ltmPos);
                            
                            $btmData = $extractAnalysis($btmHtml);
                            $ltmData = $extractAnalysis($ltmHtml);
                            
                            DB::table('igbh_summative_results')
                                ->where('id', $resultObj->id)
                                ->update([
                                    'subjective_analysis' => json_encode([
                                        'btm' => $btmData,
                                        'ltm' => $ltmData
                                    ], JSON_UNESCAPED_UNICODE)
                                ]);
                        }
                    }
                }
                
                $successCount++;
                if ($successCount % 500 == 0) {
                    $this->info("Processed $successCount records...");
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to save summative results: " . $e->getMessage());
        }

        curl_close($ch);
        $this->info("Crawling finished! Successfully processed $successCount summative results.");
    }
}
