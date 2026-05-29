<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use DOMDocument;
use DOMXPath;

class CrawlIgbhSummative extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:igbh-summative {--start=76} {--end=95}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl and parse IG.BH Summative Test Configurations from CMS LMS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start = (int)$this->option('start');
        $end = (int)$this->option('end');

        $this->info("Starting crawl for summative test_seq from $start to $end");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/igbh_crawler_summative.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/igbh_crawler_summative.txt');
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
            curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/sysm/igmetaSummReg.do');
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

            // Parse it
            $dom = new DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
            $xpath = new DOMXPath($dom);

            // Get test name
            $testNmNode = $xpath->query("//table[@class='table table_row']//tr[1]/td[1]");
            if ($testNmNode->length == 0) {
                 continue; // not found
            }
            $testNm = trim($testNmNode->item(0)->textContent);
            
            $levelCdNode = $xpath->query("//table[@class='table table_row']//tr[2]/td[1]");
            $levelCd = $levelCdNode->length > 0 ? trim($levelCdNode->item(0)->textContent) : '';

            $this->info("Found config for testSeq $testSeq: $testNm");

            $this->parseAndSaveConfig($testSeq, $testNm, $levelCd, $xpath);
            $successCount++;
            
            usleep(200000); // 200ms delay to avoid rate limiting
        }

        curl_close($ch);
        $this->info("Crawling finished! Successfully crawled $successCount configurations.");
    }

    private function parseAndSaveConfig($testSeq, $testName, $levelCdNm, $xpath)
    {
        DB::beginTransaction();
        try {
            // Delete old data
            DB::table('igbh_summative_themes')->where('test_seq', $testSeq)->delete();
            
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

            // 1. Summative Themes (Thành tích theo từng bài học Đánh giá)
            $rows = $xpath->query("//table[contains(@class,'ui celled')]//tbody/tr");
            $themesData = [];
            foreach ($rows as $row) {
                $sortNoInput = $xpath->query('.//input[@name="sortNo"]', $row);
                $themeSeqInput = $xpath->query('.//input[@name="themeSeq"]', $row);
                $descInput = $xpath->query('.//input[@name="themeDesc"]', $row);
                $pointInput = $xpath->query('.//input[@name="themePoint"]', $row);
                
                if ($sortNoInput->length > 0 && $descInput->length > 0) {
                    $sortNo = $sortNoInput->item(0)->getAttribute('value');
                    $themeSeq = $themeSeqInput->length > 0 ? $themeSeqInput->item(0)->getAttribute('value') : null;
                    $desc = $descInput->item(0)->getAttribute('value');
                    $point = $pointInput->length > 0 ? $pointInput->item(0)->getAttribute('value') : 0;
                    
                    if ($sortNo && $desc) {
                        $themesData[] = [
                            'test_seq' => $testSeq,
                            'sort_no' => (int)$sortNo,
                            'theme_seq' => $themeSeq ? (int)$themeSeq : null,
                            'theme_desc' => $desc,
                            'theme_point' => (int)$point,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
            if (!empty($themesData)) {
                DB::table('igbh_summative_themes')->insert($themesData);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to parse/save config for testSeq $testSeq: " . $e->getMessage());
        }
    }
}
