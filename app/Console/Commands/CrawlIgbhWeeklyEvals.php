<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use DOMDocument;
use DOMXPath;

class CrawlIgbhWeeklyEvals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:igbh-weekly-evals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl and parse IG.BH Weekly Evaluations from CMS LMS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting crawl for IG.BH weekly evaluations...");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/igbh_crawler_weekly.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/igbh_crawler_weekly.txt');
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
            $this->error("Admin login failed. Trying teacher login...");
            // Fallback to teacher login if admin fails
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'membId' => 'loanntp',
                'membPasswd' => '@12345678'
            ]));
            $loginResp = curl_exec($ch);
            $loginData = json_decode($loginResp, true);
            if (!isset($loginData['result']) || $loginData['result'] != 1) {
                $this->error("Teacher login failed too!");
                return;
            }
        }
        $this->info("Login successful!");

        // 1.5 Login as teacher for weeklyMod.do
        $this->info("Logging in to CMS LMS as teacher for fetching details...");
        $chTeacher = curl_init();
        curl_setopt($chTeacher, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chTeacher, CURLOPT_COOKIEJAR, '/tmp/igbh_crawler_weekly_teacher.txt');
        curl_setopt($chTeacher, CURLOPT_COOKIEFILE, '/tmp/igbh_crawler_weekly_teacher.txt');
        curl_setopt($chTeacher, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($chTeacher, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($chTeacher, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($chTeacher, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
        curl_setopt($chTeacher, CURLOPT_POST, true);
        curl_setopt($chTeacher, CURLOPT_POSTFIELDS, http_build_query([
            'membId' => 'loanntp',
            'membPasswd' => '@12345678'
        ]));
        curl_exec($chTeacher);

        // 2. Fetch list of weekly evaluations
        $this->info("Fetching list of weekly evaluations as Admin...");
        $params = http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 10000,
            'searchDatas[0][col]' => 'levelCd',
            'searchDatas[0][val]' => '',
            'searchDatas[1][col]' => 'eachCd',
            'searchDatas[1][val]' => '',
            'searchCols' => '',
            'searchText' => '',
            'searchFromDt' => '',
            'searchToDt' => ''
        ]);
        curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/getWeeklyList.do?' . $params);
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
        $this->info("Found " . count($records) . " weekly evaluation records.");

        $successCount = 0;
        foreach ($records as $index => $record) {
            $eachSeq = $record['eachSeq'] ?? null;
            $classSeq = $record['classSeq'] ?? null;
            $eachCd = $record['eachCd'] ?? null;
            $evalNm = $record['evalNm'] ?? null;

            if (!$eachSeq || !$classSeq || !$eachCd) {
                continue;
            }

            $this->info("Processing record " . ($index + 1) . "/" . count($records) . " (eachSeq: $eachSeq, class: {$record['classNm']})");

            // Look up testSeq by evalNm
            $testObj = DB::table('igbh_tests')->where('test_nm', $evalNm)->first();
            $testSeq = $testObj ? $testObj->test_seq : 0;
            if (!$testSeq) {
                $this->warn("Cannot find testSeq for evalNm '$evalNm', skipping.");
                continue;
            }

            DB::beginTransaction();
            try {
                // Upsert weekly evaluation
                $evalId = DB::table('igbh_weekly_evals')->updateOrInsert(
                    [
                        'test_seq' => $testSeq,
                        'class_seq' => $classSeq,
                        'each_cd' => $eachCd,
                    ],
                    [
                        'class_nm' => $record['classNm'] ?? null,
                        'each_cd_nm' => $record['eachCdNm'] ?? null,
                        'teacher_nm' => $record['membNm'] ?? null,
                        'eval_ymd' => $record['evalYmd'] ?? null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $weeklyEvalObj = DB::table('igbh_weekly_evals')
                    ->where('test_seq', $testSeq)
                    ->where('class_seq', $classSeq)
                    ->where('each_cd', $eachCd)
                    ->first();
                
                $weeklyEvalId = $weeklyEvalObj->id;

                // Delete old details
                DB::table('igbh_weekly_eval_details')->where('weekly_eval_id', $weeklyEvalId)->delete();

                // Fetch student details from weeklyMod.do using TEACHER session
                curl_setopt($chTeacher, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/weeklyMod.do');
                curl_setopt($chTeacher, CURLOPT_POST, true);
                curl_setopt($chTeacher, CURLOPT_POSTFIELDS, http_build_query([
                    'eachSeq' => $eachSeq,
                    'eachCd' => $eachCd,
                    'classSeq' => $classSeq,
                    'paramMap' => ''
                ]));
                // Reset headers for standard form post
                curl_setopt($chTeacher, CURLOPT_HTTPHEADER, [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'Content-Type: application/x-www-form-urlencoded'
                ]);

                $html = curl_exec($chTeacher);
                
                if (strlen($html) > 1000) {
                    $dom = new DOMDocument();
                    @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
                    $xpath = new DOMXPath($dom);
                    
                    $studentRows = $xpath->query("//tbody[@id='each_tbody']/tr");
                    $detailsData = [];
                    
                    foreach ($studentRows as $row) {
                        $stuSeq = $row->getAttribute('data-stuseq');
                        $allowYn = $row->getAttribute('data-stuallowyn');
                        
                        if (!$stuSeq) continue;
                        
                        // If student is not allowed, scores are probably empty or invalid
                        if ($allowYn === 'N') {
                            continue;
                        }

                        $stuNmRaw = $xpath->evaluate("string(.//th)", $row);
                        // Clean student name by removing hidden input content if needed
                        $stuNm = trim(preg_replace('/<[^>]*>/', '', $stuNmRaw));
                        
                        $workbook = $xpath->evaluate("string(.//input[contains(@name, 'workbook')]/@value)", $row);
                        $attdListen = $xpath->evaluate("string(.//input[contains(@name, 'attdListen')]/@value)", $row);
                        $attdJoin = $xpath->evaluate("string(.//input[contains(@name, 'attdJoin')]/@value)", $row);
                        $attdExpress = $xpath->evaluate("string(.//input[contains(@name, 'attdExpress')]/@value)", $row);
                        $attdCoop = $xpath->evaluate("string(.//input[contains(@name, 'attdCoop')]/@value)", $row);
                        $detectNormal = $xpath->evaluate("string(.//input[contains(@name, 'detectNormal')]/@value)", $row);
                        $detectLeadersh = $xpath->evaluate("string(.//input[contains(@name, 'detectLeadersh')]/@value)", $row);
                        $detectMath = $xpath->evaluate("string(.//input[contains(@name, 'detectMath')]/@value)", $row);
                        $detectCreative = $xpath->evaluate("string(.//input[contains(@name, 'detectCreative')]/@value)", $row);
                        
                        $detailsData[] = [
                            'weekly_eval_id' => $weeklyEvalId,
                            'stu_seq' => (int)$stuSeq,
                            'stu_nm' => $stuNm ?: null,
                            'workbook' => (int)$workbook,
                            'attd_listen' => (int)$attdListen,
                            'attd_join' => (int)$attdJoin,
                            'attd_express' => (int)$attdExpress,
                            'attd_coop' => (int)$attdCoop,
                            'detect_normal' => (int)$detectNormal,
                            'detect_leadersh' => (int)$detectLeadersh,
                            'detect_math' => (int)$detectMath,
                            'detect_creative' => (int)$detectCreative,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    
                    if (!empty($detailsData)) {
                        DB::table('igbh_weekly_eval_details')->insert($detailsData);
                    }
                }
                
                DB::commit();
                $successCount++;
                
                usleep(200000); // 200ms delay
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to parse/save details for eachSeq $eachSeq: " . $e->getMessage());
            }
        }

        curl_close($ch);
        curl_close($chTeacher);
        $this->info("Crawling finished! Successfully processed $successCount weekly evaluations.");
    }
}
