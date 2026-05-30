<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrawlIgbhConfigs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:igbh-configs {--start=100} {--end=200}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl and parse IG.BH Test Configurations from CMS LMS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start = (int)$this->option('start');
        $end = (int)$this->option('end');

        $this->info("Starting crawl for test_seq from $start to $end");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/igbh_crawler_admin.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/igbh_crawler_admin.txt');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // 1. Login
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
            $this->error("Login failed!");
            return;
        }
        $this->info("Login successful!");

        $successCount = 0;

        for ($testSeq = $start; $testSeq <= $end; $testSeq++) {
            curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/sysm/igmetaDiagReg.do');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'testSeq' => $testSeq
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            ]);
            $html = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode !== 200 || strlen($html) < 1000) {
                // Not a valid config
                continue;
            }

            // Check if it's a valid config page by searching for Tên bài kiểm tra
            if (!preg_match('/<th[^>]*>Tên bài kiểm tra<\/th>\s*<td>([^<]+)<\/td>/i', $html, $nameMatch)) {
                continue;
            }

            $testName = trim($nameMatch[1]);
            $this->info("Found config for testSeq $testSeq: $testName");

            // Extract level cd (e.g. Lớp 2)
            $levelCdNm = '';
            if (preg_match('/<th[^>]*>Cấp<\/th>\s*<td>([^<]+)<\/td>/i', $html, $levelMatch)) {
                $levelCdNm = trim($levelMatch[1]);
            }

            $this->parseAndSaveConfig($testSeq, $testName, $levelCdNm, $html);
            $successCount++;
            
            usleep(200000); // 200ms delay to avoid rate limiting
        }

        curl_close($ch);
        $this->info("Crawling finished! Successfully crawled $successCount configurations.");
    }

    private function parseAndSaveConfig($testSeq, $testName, $levelCdNm, $html)
    {
        DB::beginTransaction();
        try {
            // Delete old data
            DB::table('igbh_test_configs')->where('test_seq', $testSeq)->delete();
            DB::table('igbh_test_questions')->where('test_seq', $testSeq)->delete();
            DB::table('igbh_test_comments')->where('test_seq', $testSeq)->delete();
            
            // Insert test into igbh_tests if not exists
            $exists = DB::table('igbh_tests')->where('test_seq', $testSeq)->exists();
            if (!$exists) {
                DB::table('igbh_tests')->insert([
                    'test_seq' => $testSeq,
                    'test_nm' => $testName,
                    'level_cd' => $levelCdNm,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('igbh_tests')->where('test_seq', $testSeq)->update([
                    'test_nm' => $testName,
                    'level_cd' => $levelCdNm,
                    'updated_at' => now(),
                ]);
            }

            $sectors = [];
            preg_match_all('/name=\"sectorNm\".*?value=\"([^\"]+)\"/s', $html, $sectorsMatch);
            if (!empty($sectorsMatch[1])) {
                foreach ($sectorsMatch[1] as $idx => $name) {
                    $sectors[chr(65 + $idx)] = html_entity_decode($name, ENT_QUOTES, 'UTF-8');
                }
            }

            DB::table('igbh_test_configs')->insert([
                'test_seq' => $testSeq,
                'sectors' => json_encode($sectors, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            $xpath = new \DOMXPath($dom);

            // 1. Curriculum
            $sbjRows = $xpath->query("//table[contains(@class, 'table1')]//tbody/tr");
            $curriculumData = [];
            foreach ($sbjRows as $row) {
                $sortNo = $xpath->evaluate("string(.//input[@name='sbjSortNo']/@value)", $row);
                if (!$sortNo) continue;
                
                $sectorCd = $xpath->evaluate("string(.//input[starts-with(@name, 'sbjSectorCd') and @checked='checked']/@data-shortcd)", $row);
                $diffCd = $xpath->evaluate("string(.//input[starts-with(@name, 'sbjDifficultyCd') and @checked='checked']/@value)", $row);
                $typeCd = $xpath->evaluate("string(.//input[starts-with(@name, 'sbjTypeCd') and @checked='checked']/@value)", $row);
                $answer = $xpath->evaluate("string(.//input[@name='sbjAnswer']/@value)", $row);
                $point = $xpath->evaluate("string(.//input[@name='sbjPoint']/@value)", $row);
                
                $curriculumData[] = [
                    'test_seq' => $testSeq,
                    'question_type' => 'curriculum',
                    'sort_no' => (int)$sortNo,
                    'sector' => $sectorCd ?: null,
                    'difficulty' => $diffCd ?: null,
                    'type_cd' => $typeCd ?: null,
                    'answer' => $answer ?: null,
                    'areas' => null,
                    'standard_point' => (int)$point,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($curriculumData)) {
                DB::table('igbh_test_questions')->insert($curriculumData);
            }

            // 2. Thinking
            $thkRows = $xpath->query("//table[contains(@class, 'table3')]//tbody/tr");
            $thinkingData = [];
            foreach ($thkRows as $row) {
                $sortNo = $xpath->evaluate("string(.//input[@name='thkSortNo']/@value)", $row);
                if (!$sortNo) continue;
                
                $understand = $xpath->evaluate("string(.//input[@name='understand']/@checked)", $row);
                $insight = $xpath->evaluate("string(.//input[@name='insight']/@checked)", $row);
                $organize = $xpath->evaluate("string(.//input[@name='organize']/@checked)", $row);
                $abstraction = $xpath->evaluate("string(.//input[@name='abstraction']/@checked)", $row);
                $view = $xpath->evaluate("string(.//input[@name='view']/@checked)", $row);
                $reasoning = $xpath->evaluate("string(.//input[@name='reasoning']/@checked)", $row);
                
                $diffCd = $xpath->evaluate("string(.//input[starts-with(@name, 'thkDifficultyCd') and @checked='checked']/@value)", $row);
                $point = $xpath->evaluate("string(.//input[@name='thkPoint']/@value)", $row);
                
                $areas = [];
                if ($understand) $areas[] = 'A';
                if ($insight) $areas[] = 'B';
                if ($organize) $areas[] = 'C';
                if ($abstraction) $areas[] = 'D';
                if ($view) $areas[] = 'E';
                if ($reasoning) $areas[] = 'F';
                
                $thinkingData[] = [
                    'test_seq' => $testSeq,
                    'question_type' => 'thinking',
                    'sort_no' => (int)$sortNo,
                    'sector' => null,
                    'difficulty' => $diffCd ?: null,
                    'type_cd' => null,
                    'answer' => null,
                    'areas' => json_encode($areas),
                    'standard_point' => (int)$point,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($thinkingData)) {
                DB::table('igbh_test_questions')->insert($thinkingData);
            }

            // 3. Comments (Tab 2)
            $commentsData = [];

            // Total Comments
            $totalRows = $xpath->query("//div[@id='tab2']//tr[.//textarea[@name='goodComment']]");
            foreach ($totalRows as $row) {
                $conditionRaw = $xpath->evaluate("string(.//th)", $row);
                $condition = trim(preg_replace('/<[^>]+>/', '', $conditionRaw)); // Remove input hidden tags
                
                $goodComment = $xpath->evaluate("string(.//textarea[@name='goodComment'])", $row);
                $weakComment = $xpath->evaluate("string(.//textarea[@name='weakComment'])", $row);
                
                if ($condition) {
                    $commentsData[] = [
                        'test_seq' => $testSeq,
                        'comment_type' => 'total',
                        'condition_value' => $condition,
                        'good_comment' => $goodComment,
                        'weak_comment' => $weakComment,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Unit Comments
            $unitRows = $xpath->query("//div[@id='tab2']//tr[.//textarea[@name='unitComment']]");
            foreach ($unitRows as $row) {
                // The DB config mapping needs sector A, B, C.
                // The HTML has "When A.Các số đến 9 is missing". We can extract 'A' from the <th> text!
                $thText = trim($xpath->evaluate("string(.//th)", $row));
                $sectorCd = '';
                if (preg_match('/When\s+([A-Z])\./i', $thText, $matches)) {
                    $sectorCd = strtoupper($matches[1]);
                }
                
                $unitComment = $xpath->evaluate("string(.//textarea[@name='unitComment'])", $row);
                
                if ($sectorCd && $unitComment) {
                    $commentsData[] = [
                        'test_seq' => $testSeq,
                        'comment_type' => 'unit',
                        'condition_value' => $sectorCd,
                        'good_comment' => null,
                        'weak_comment' => $unitComment,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($commentsData)) {
                DB::table('igbh_test_comments')->insert($commentsData);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to parse/save config for testSeq $testSeq: " . $e->getMessage());
        }
    }
}
