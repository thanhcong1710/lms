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
