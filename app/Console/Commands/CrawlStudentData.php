<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;

class CrawlStudentData extends Command
{
    protected $signature = 'lms:crawl-student {id_lms : The student ID (stuSeq or membNm) to crawl}';
    protected $description = 'Efficiently crawl all LMS data (UCREA, IG.BH Diag, Summative, Weekly) for a specific student';

    private $cookieAdmin = '/tmp/lms_crawler_admin.txt';
    private $cookieTeacher = '/tmp/lms_crawler_teacher.txt';

    public function handle()
    {
        $stuId = $this->argument('id_lms');
        $this->info("Starting targeted crawl for student ID: $stuId");

        if (!$this->loginAdmin()) {
            $this->error("Failed to login as Admin.");
            return;
        }
        if (!$this->loginTeacher()) {
            $this->error("Failed to login as Teacher.");
            return;
        }

        $studentClassSeqs = [];

        // 1. Crawl UCREA
        $this->info("Fetching UCREA list...");
        $ucreaMatches = [];
        $studentRecord = DB::table('students')->where('id_lms', $stuId)->first();
        $studentName = $studentRecord ? strtolower(trim($studentRecord->name)) : '';

        $this->fetchJsonList('https://lms-vn.cmsedu.net/eval/getResultList.do', [
            'searchDatas' => [
                ['col' => 'testCd', 'val' => ''],
                ['col' => 'statCd', 'val' => ''],
                ['col' => 'levelCd', 'val' => '']
            ]
        ], function($data) use ($stuId, $studentName, &$ucreaMatches) {
            foreach ($data as $r) {
                if (
                    ($r['stuSeq'] ?? '') == $stuId || 
                    ($studentName && strtolower(trim($r['stuNm'] ?? '')) === $studentName)
                ) {
                    $ucreaMatches[] = $r;
                }
            }
        });
        
        $this->info("Found " . count($ucreaMatches) . " UCREA results for student.");
        foreach ($ucreaMatches as $item) {
            $this->processUcreaResult($item);
        }

        // 2. Crawl IG.BH Diagnostic
        $this->info("Fetching IG.BH Diag list...");
        $diagMatches = [];
        $this->fetchJsonList('https://lms-vn.cmsedu.net/igel/getDiagList.do', [
            'searchDatas[0][col]' => 'quarterCd', 'searchDatas[0][val]' => '',
            'searchDatas[1][col]' => 'levelCd', 'searchDatas[1][val]' => ''
        ], function($data) use ($stuId, &$diagMatches) {
            foreach ($data as $r) {
                if (($r['stuSeq'] ?? '') == $stuId) {
                    $diagMatches[] = $r;
                }
            }
        });

        $this->info("Found " . count($diagMatches) . " IG.BH Diag results for student.");
        foreach ($diagMatches as $item) {
            if (isset($item['classSeq'])) {
                $studentClassSeqs[] = $item['classSeq'];
            }
            $this->processIgbhDiagResult($item);
        }

        // 3. Crawl IG.BH Summative
        $this->info("Fetching IG.BH Summative list...");
        $smtvMatches = [];
        $this->fetchJsonList('https://lms-vn.cmsedu.net/igel/getSmtvList.do', [
            'searchDatas[0][col]' => 'testSeq', 'searchDatas[0][val]' => '',
            'searchDatas[1][col]' => 'levelCd', 'searchDatas[1][val]' => '',
            'searchDatas[2][col]' => 'sortCd', 'searchDatas[2][val]' => ''
        ], function($data) use ($stuId, &$smtvMatches) {
            foreach ($data as $r) {
                if (($r['stuSeq'] ?? '') == $stuId) {
                    $smtvMatches[] = $r;
                }
            }
        });

        $this->info("Found " . count($smtvMatches) . " IG.BH Summative results for student.");
        foreach ($smtvMatches as $item) {
            if (isset($item['classSeq'])) {
                $studentClassSeqs[] = $item['classSeq'];
            }
            $this->processIgbhSummativeResult($item);
        }

        // 4. Crawl IG.BH Weekly Evals
        $studentClassSeqs = array_unique($studentClassSeqs);
        if (empty($studentClassSeqs)) {
            $this->warn("No class sequences found for this student. Trying to find from DB...");
            $dbClasses = DB::table('igbh_summative_results')->where('stu_seq', $stuId)->pluck('class_seq')->toArray();
            $studentClassSeqs = array_unique($dbClasses);
        }
        
        if (!empty($studentClassSeqs)) {
            $this->info("Student belonged to classes: " . implode(', ', $studentClassSeqs));
            $this->info("Fetching IG.BH Weekly list...");
            
            $weeklyMatches = [];
            $this->fetchJsonList('https://lms-vn.cmsedu.net/igel/getWeeklyList.do', [
                'searchDatas[0][col]' => 'levelCd', 'searchDatas[0][val]' => '',
                'searchDatas[1][col]' => 'eachCd', 'searchDatas[1][val]' => ''
            ], function($data) use ($studentClassSeqs, &$weeklyMatches) {
                foreach ($data as $r) {
                    if (in_array($r['classSeq'] ?? null, $studentClassSeqs)) {
                        $weeklyMatches[] = $r;
                    }
                }
            });

            $this->info("Found " . count($weeklyMatches) . " IG.BH Weekly class evaluations to parse.");
            foreach ($weeklyMatches as $item) {
                $this->processIgbhWeeklyClass($item, (int)$stuId);
            }
        } else {
            $this->warn("Could not determine any class for student to crawl weekly evals.");
        }
        
        $this->info("Finished crawling all data for student $stuId.");
    }

    private function loginAdmin() {
        $ch = curl_init('https://lms-vn.cmsedu.net/login/ajaxLogin.do');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->cookieAdmin,
            CURLOPT_COOKIEFILE => $this->cookieAdmin, CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query(['membId' => 'cms.vn', 'membPasswd' => 'hpa@XuanDieu']),
            CURLOPT_HTTPHEADER => ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']
        ]);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return isset($res['result']) && $res['result'] == 1;
    }

    private function loginTeacher() {
        $ch = curl_init('https://lms-vn.cmsedu.net/login/ajaxLogin.do');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->cookieTeacher,
            CURLOPT_COOKIEFILE => $this->cookieTeacher, CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query(['membId' => 'loanntp', 'membPasswd' => '@12345678']),
            CURLOPT_HTTPHEADER => ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']
        ]);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return isset($res['result']) && $res['result'] == 1;
    }

    private function fetchJsonList($url, $extraParams, callable $callback) {
        $start = 0;
        $length = 2000;
        
        while (true) {
            $params = array_merge([
                'draw' => 1, 'start' => $start, 'length' => $length,
                'searchCols' => '', 'searchText' => '', 'searchFromDt' => '', 'searchToDt' => ''
            ], $extraParams);
            
            $ch = curl_init($url . '?' . http_build_query($params));
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->cookieAdmin,
                CURLOPT_COOKIEFILE => $this->cookieAdmin, CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']
            ]);
            $res = curl_exec($ch);
            curl_close($ch);
            
            $json = json_decode($res, true);
            $data = $json['data'] ?? [];
            if (empty($data)) break;
            
            // Invoke the callback on the chunk to filter data immediately
            $callback($data);
            
            // Clear memory
            unset($data);
            unset($json);
            unset($res);
            
            // If the returned data is less than length, we reached the end
            if (count($json['data'] ?? []) < $length) break;
            
            $start += $length;
            
            // Prevent infinite loops safely (60k max)
            if ($start > 100000) break;
        }
    }

    private function fetchHtml($url, $postData, $useTeacher = false) {
        $cookie = $useTeacher ? $this->cookieTeacher : $this->cookieAdmin;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $cookie,
            CURLOPT_COOKIEFILE => $cookie, CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'User-Agent: Mozilla/5.0']
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    // ========== UCREA Logic ==========
    private function processUcreaResult($item) {
        if (!in_array($item['resultCd'], ['IS002', 'IS003', 'IS004'])) return;
        
        DB::table('ucrea_tests')->updateOrInsert(
            ['test_cd' => $item['testCd'], 'level_cd' => $item['levelCd'], 'test_seq' => $item['testSeq']],
            ['test_nm' => $item['testNm'], 'level_cd_nm' => $item['levelCdNm'], 'updated_at' => now()]
        );
        
        $inHtml = $this->fetchHtml('https://lms-vn.cmsedu.net/eval/inputView.do', [
            'testCd' => $item['testCd'], 'levelCd' => $item['levelCd'],
            'testSeq' => $item['testSeq'], 'resultSeq' => $item['resultSeq'], 'paramMap' => ''
        ]);
        
        $rpHtml = $this->fetchHtml('https://lms-vn.cmsedu.net/eval/viewReport.do', [
            'testCd' => $item['testCd'], 'levelCd' => $item['levelCd'],
            'testSeq' => $item['testSeq'], 'resultSeq' => $item['resultSeq']
        ]);
        
        $reportData = [];
        if (preg_match('/var tt\d+data = (\{.*?\})/', $rpHtml, $matches)) {
            $reportData = json_decode(str_replace("'", '"', $matches[1]), true);
        }
        
        $dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="UTF-8">' . $rpHtml);
        $xpath = new DOMXPath($dom);
        $skillsGrade = [];
        foreach ($xpath->query('//table[contains(@class, "table_detail")]/tbody/tr') as $row) {
            $tds = $xpath->query('td', $row);
            if ($tds->length == 3) {
                $skill = trim($tds->item(0)->nodeValue);
                $grade = $xpath->query('.//span[contains(@class, "no-show")]', $tds->item(2));
                if ($grade->length > 0) $skillsGrade[] = ['skill' => $skill, 'grade' => trim($grade->item(0)->nodeValue)];
            }
        }
        
        DB::table('ucrea_student_results')->updateOrInsert(
            ['result_seq' => $item['resultSeq']],
            [
                'test_cd' => $item['testCd'], 'level_cd' => $item['levelCd'], 'test_seq' => $item['testSeq'],
                'memb_nm' => $item['membNm'], 'stu_nm' => $item['stuNm'], 'class_nm' => $item['classNm'] ?? null,
                'eval_dt' => $item['evalDt'] ?? null, 'result_cd' => $item['resultCd'], 'result_cd_nm' => $item['resultCdNm'],
                'report_data' => json_encode($reportData), 'skills_grade' => json_encode($skillsGrade), 'updated_at' => now()
            ]
        );
        $this->info("Updated UCREA result for {$item['stuNm']}");
    }

    // ========== IG.BH Diag Logic ==========
    private function processIgbhDiagResult($item) {
        $testSeq = $item['testSeq']; $stuSeq = $item['stuSeq']; $resultSeq = $item['resultSeq'];
        
        DB::table('igbh_tests')->updateOrInsert(['test_seq' => $testSeq], ['test_nm' => $item['evalNm'], 'updated_at' => now()]);
        
        $modHtml = $this->fetchHtml('https://lms-vn.cmsedu.net/igel/diagMod.do', ['testSeq' => $testSeq, 'stuSeq' => $stuSeq], true);
        $rpHtml = $this->fetchHtml('https://lms-vn.cmsedu.net/igel/viewReportIGDiag.do', ['resultSeq' => $resultSeq, 'testSeq' => $testSeq, 'stuSeq' => $stuSeq], true);
        
        DB::table('igbh_student_results')->updateOrInsert(
            ['result_seq' => $resultSeq],
            [
                'test_seq' => $testSeq, 'stu_seq' => $stuSeq, 'stu_nm' => $item['stuNm'],
                'eval_dt' => $item['evalDt'] ?? null, 'total_score' => $item['totalScore'] ?? 0,
                'subject_total' => $item['subjectTotal'] ?? 0, 'thinking_total' => $item['thinkingTotal'] ?? 0,
                'assigned_level' => $item['assignedLevel'] ?? null, 'quarter_cd' => $item['quarterCd'] ?? null,
                'quarter_cd_nm' => $item['quarterCdNm'] ?? null, 'class_type_cd' => $item['classTypeCd'] ?? null,
                'updated_at' => now()
            ]
        );
        $resultId = DB::table('igbh_student_results')->where('result_seq', $resultSeq)->value('id');
        if ($resultId) {
            $curriculum = [];
            $thinking = [];
            
            // 1. Parse modHtml (Get saSeq/answer and taSeq/score)
            $domMod = new DOMDocument();
            @$domMod->loadHTML('<?xml encoding="UTF-8">' . $modHtml);
            $xpathMod = new DOMXPath($domMod);
            
            foreach ($xpathMod->query('//input[@name="answer"]') as $input) {
                $qNo = (int)str_replace('answer_', '', $input->getAttribute('id'));
                $saSeqNode = $xpathMod->query('.//input[@name="saSeq"]', $input->parentNode);
                $curriculum[$qNo] = [
                    'saSeq' => $saSeqNode->length > 0 ? (int)$saSeqNode->item(0)->getAttribute('value') : null,
                    'answer' => $input->getAttribute('value')
                ];
            }
            
            foreach ($xpathMod->query('//input[@name="score"]') as $input) {
                $qNo = (int)str_replace('score_', '', $input->getAttribute('id'));
                $taSeqNode = $xpathMod->query('.//input[@name="taSeq"]', $input->parentNode);
                $thinking[$qNo] = [
                    'taSeq' => $taSeqNode->length > 0 ? (int)$taSeqNode->item(0)->getAttribute('value') : null,
                    'score' => $input->getAttribute('value')
                ];
            }
            
            // 2. Parse reportHtml (Get Unit categories, correct status, thinking max scores)
            $domReport = new DOMDocument();
            @$domReport->loadHTML('<?xml encoding="UTF-8">' . $rpHtml);
            $xpathReport = new DOMXPath($domReport);
            
            $tableWhole = $xpathReport->query('//table[contains(@class, "table_whole") and .//th[text()="Mục"]]');
            if ($tableWhole->length > 0) {
                $tbodyRows = $xpathReport->query('.//tbody/tr', $tableWhole->item(0));
                if ($tbodyRows->length >= 3) {
                    $unitTds = $xpathReport->query('td', $tbodyRows->item(0));
                    $correctTds = $xpathReport->query('td', $tbodyRows->item(2));
                    
                    for ($j = 0; $j < 20; $j++) {
                        $qNo = $j + 1;
                        $unit = $unitTds->length > $j ? trim($unitTds->item($j)->nodeValue) : null;
                        $isCorrect = $correctTds->length > $j ? trim($correctTds->item($j)->nodeValue) : null;
                        
                        if (isset($curriculum[$qNo])) {
                            $curriculum[$qNo]['unit'] = $unit;
                            $curriculum[$qNo]['is_correct'] = $isCorrect;
                        } else {
                            $curriculum[$qNo] = ['saSeq' => null, 'answer' => null, 'unit' => $unit, 'is_correct' => $isCorrect];
                        }
                    }
                }
            }
            
            foreach ($xpathReport->query('//table[contains(@class, "table_whole")]/tbody/tr') as $row) {
                $th = $xpathReport->query('th', $row);
                if ($th->length > 0 && trim($th->item(0)->nodeValue) === 'Điểm chuẩn') {
                    $tds = $xpathReport->query('td', $row);
                    for ($j = 0; $j < 10; $j++) {
                        $qNo = $j + 1;
                        $maxScore = $tds->length > $j ? (int)trim($tds->item($j)->nodeValue) : null;
                        if (isset($thinking[$qNo])) {
                            $thinking[$qNo]['max_score'] = $maxScore;
                        } else {
                            $thinking[$qNo] = ['taSeq' => null, 'score' => null, 'max_score' => $maxScore];
                        }
                    }
                }
            }
            
            DB::table('igbh_student_result_details')->where('igbh_student_result_id', $resultId)->delete();
            $details = [];
            foreach ($curriculum as $qNo => $data) {
                $details[] = [
                    'igbh_student_result_id' => $resultId, 'question_no' => $qNo, 'question_type' => 'curriculum',
                    'seq_id' => $data['saSeq'] ?? null, 'assigned_score' => $data['answer'] ?? null,
                    'unit' => $data['unit'] ?? null, 'is_correct' => $data['is_correct'] ?? null, 'max_score' => null,
                    'created_at' => now(), 'updated_at' => now()
                ];
            }
            foreach ($thinking as $qNo => $data) {
                $details[] = [
                    'igbh_student_result_id' => $resultId, 'question_no' => $qNo, 'question_type' => 'thinking',
                    'seq_id' => $data['taSeq'] ?? null, 'assigned_score' => $data['score'] ?? null,
                    'unit' => null, 'is_correct' => null, 'max_score' => $data['max_score'] ?? null,
                    'created_at' => now(), 'updated_at' => now()
                ];
            }
            if (!empty($details)) DB::table('igbh_student_result_details')->insert($details);
        }
        $this->info("Updated IG.BH Diag result for {$item['stuNm']}");
    }

    // ========== IG.BH Summative Logic ==========
    private function processIgbhSummativeResult($record) {
        $testSeq = $record['testSeq']; $stuSeq = $record['stuSeq'];
        DB::table('igbh_summative_results')->updateOrInsert(
            ['test_seq' => $testSeq, 'stu_seq' => $stuSeq],
            [
                'stu_nm' => $record['stuNm'] ?? null, 'class_seq' => $record['classSeq'] ?? null,
                'class_nm' => $record['classNm'] ?? null, 'teacher_nm' => $record['membNm'] ?? null,
                'total_score' => is_numeric($record['totalScore'] ?? '') ? (float)$record['totalScore'] : 0,
                'eval_dt' => $record['evalYmd'] ?? null, 'status' => 'D', 'updated_at' => now()
            ]
        );
        $resultObj = DB::table('igbh_summative_results')->where(['test_seq' => $testSeq, 'stu_seq' => $stuSeq])->first();
        if ($resultObj) {
            $html = $this->fetchHtml('https://lms-vn.cmsedu.net/igel/viewReportIGSumm.do', [
                'finalSeq' => $record['finalSeq'] ?? '', 'resultSeq' => $record['resultSeq'] ?? '',
                'eachSeq' => $record['eachSeq'] ?? '', 'stuSeq' => $stuSeq
            ], false);
            
            if ($html) {
                // Extract BTM and LTM
                $btmPos = mb_strpos($html, 'Phân tích BTM');
                $ltmPos = mb_strpos($html, 'Phân tích LTM');
                if ($btmPos !== false && $ltmPos !== false) {
                    $extractAnalysis = function($tableHtml) {
                        preg_match_all('/<th[^>]*>(Số \d+)<\/th>/iu', $tableHtml, $qMatches);
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
                            'q1_label' => $qMatches[1][0] ?? '', 'q2_label' => $qMatches[1][1] ?? '',
                            'concept' => $extractData($tableHtml, 'Khái niệm/hiểu'),
                            'strategy' => $extractData($tableHtml, 'Chiến lược/suy luận'),
                            'calculation' => $extractData($tableHtml, 'Tính toán/thực hành'),
                            'expression' => $extractData($tableHtml, 'Diễn đạt/biểu hiện'),
                            'average' => $extractData($tableHtml, 'Trung bình')
                        ];
                    };
                    $btmEnd = mb_strpos($html, '</table>', $btmPos);
                    $ltmEnd = mb_strpos($html, '</table>', $ltmPos);
                    
                    DB::table('igbh_summative_results')->where('id', $resultObj->id)->update([
                        'subjective_analysis' => json_encode([
                            'btm' => $extractAnalysis(mb_substr($html, $btmPos, $btmEnd - $btmPos)),
                            'ltm' => $extractAnalysis(mb_substr($html, $ltmPos, $ltmEnd - $ltmPos))
                        ], JSON_UNESCAPED_UNICODE)
                    ]);
                }
                
                // Extract Details
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
                                    'summative_result_id' => $resultObj->id, 'sort_no' => $i + 1,
                                    'max_score' => isset($maxScores[$i]) ? (float)$maxScores[$i] : null,
                                    'score' => (float)$scores[$i],
                                    'concept' => isset($concepts[$i]) ? (float)$concepts[$i] : null,
                                    'strategy' => isset($strategies[$i]) ? (float)$strategies[$i] : null,
                                    'calculation' => isset($calculations[$i]) ? (float)$calculations[$i] : null,
                                    'expression' => isset($expressions[$i]) ? (float)$expressions[$i] : null,
                                    'created_at' => now(), 'updated_at' => now()
                                ]);
                            }
                        }
                    }
                }
            }
        }
        $this->info("Updated IG.BH Summative result for {$record['stuNm']}");
    }

    // ========== IG.BH Weekly Logic ==========
    private function processIgbhWeeklyClass($record, $targetStuSeq) {
        $testSeq = DB::table('igbh_tests')->where('test_nm', $record['evalNm'])->value('test_seq');
        if (!$testSeq) return;

        DB::table('igbh_weekly_evals')->updateOrInsert(
            ['test_seq' => $testSeq, 'class_seq' => $record['classSeq'], 'each_cd' => $record['eachCd']],
            [
                'class_nm' => $record['classNm'] ?? null, 'each_cd_nm' => $record['eachCdNm'] ?? null,
                'teacher_nm' => $record['membNm'] ?? null, 'eval_ymd' => $record['evalYmd'] ?? null, 'updated_at' => now()
            ]
        );
        $weeklyId = DB::table('igbh_weekly_evals')->where(['test_seq' => $testSeq, 'class_seq' => $record['classSeq'], 'each_cd' => $record['eachCd']])->value('id');
        
        $html = $this->fetchHtml('https://lms-vn.cmsedu.net/igel/weeklyMod.do', [
            'eachSeq' => $record['eachSeq'], 'eachCd' => $record['eachCd'], 'classSeq' => $record['classSeq'], 'paramMap' => ''
        ], true);
        
        if (strlen($html) > 1000) {
            $dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            $xpath = new DOMXPath($dom);
            $foundStus = [];
            foreach ($xpath->query("//tbody[@id='each_tbody']/tr") as $row) {
                $stuSeq = (int)$row->getAttribute('data-stuseq');
                $foundStus[] = $stuSeq;
                if ($stuSeq === $targetStuSeq) {
                    $stuNm = trim(preg_replace('/<[^>]*>/', '', $xpath->evaluate("string(.//th)", $row)));
                    $val = fn($n) => (int)$xpath->evaluate("string(.//input[contains(@name, '$n')]/@value)", $row);
                    
                    DB::table('igbh_weekly_eval_details')->updateOrInsert(
                        ['weekly_eval_id' => $weeklyId, 'stu_seq' => $targetStuSeq],
                        [
                            'stu_nm' => $stuNm ?: null,
                            'workbook' => $val('workbook'),
                            'attd_listen' => $val('attdListen'), 'attd_join' => $val('attdJoin'),
                            'attd_express' => $val('attdExpress'), 'attd_coop' => $val('attdCoop'),
                            'detect_normal' => $val('detectNormal'), 'detect_leadersh' => $val('detectLeadersh'),
                            'detect_math' => $val('detectMath'), 'detect_creative' => $val('detectCreative'),
                            'updated_at' => now()
                        ]
                    );
                    $this->info("Updated IG.BH Weekly for stuSeq {$targetStuSeq} (Week {$record['eachCd']})");
                }
            }
            if (!in_array($targetStuSeq, $foundStus)) {
                $this->warn("Student {$targetStuSeq} not found in weekly HTML. Found: " . implode(',', $foundStus));
            }
        }
    }
}
