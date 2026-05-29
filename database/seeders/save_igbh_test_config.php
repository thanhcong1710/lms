<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$html = file_get_contents(__DIR__.'/igbh_test_config_124.html');

$testSeq = 124;

// Xóa dữ liệu cũ nếu có
DB::table('igbh_test_configs')->where('test_seq', $testSeq)->delete();
DB::table('igbh_test_questions')->where('test_seq', $testSeq)->delete();
DB::table('igbh_test_comments')->where('test_seq', $testSeq)->delete();

$sectors = [];
// Extract sectors
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

$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
$xpath = new DOMXPath($dom);

// 1. Curriculum
$sbjRows = $xpath->query("//table[contains(@class, 'table1')]//tbody/tr");
$curriculumData = [];
foreach ($sbjRows as $row) {
    $sortNo = $xpath->evaluate("string(.//input[@name='sbjSortNo']/@value)", $row);
    if (!$sortNo) continue;
    
    $sectorCd = $xpath->evaluate("string(.//input[starts-with(@name, 'sbjSectorCd') and @checked='checked']/@data-shortCd)", $row);
    $diffCd = $xpath->evaluate("string(.//input[starts-with(@name, 'sbjDifficultyCd') and @checked='checked']/@value)", $row);
    $typeCd = $xpath->evaluate("string(.//input[starts-with(@name, 'sbjTypeCd') and @checked='checked']/@value)", $row);
    $answer = $xpath->evaluate("string(.//input[@name='sbjAnswer']/@value)", $row);
    $point = $xpath->evaluate("string(.//input[@name='sbjPoint']/@value)", $row);
    
    $curriculumData[] = [
        'test_seq' => $testSeq,
        'question_type' => 'curriculum',
        'sort_no' => (int)$sortNo,
        'sector' => $sectorCd,
        'difficulty' => $diffCd,
        'type_cd' => $typeCd,
        'answer' => $answer,
        'areas' => null,
        'standard_point' => (int)$point,
        'created_at' => now(),
        'updated_at' => now(),
    ];
}
DB::table('igbh_test_questions')->insert($curriculumData);

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
        'difficulty' => $diffCd,
        'type_cd' => null,
        'answer' => null,
        'areas' => json_encode($areas),
        'standard_point' => (int)$point,
        'created_at' => now(),
        'updated_at' => now(),
    ];
}
DB::table('igbh_test_questions')->insert($thinkingData);

// 3. Comments (Tab 2)
$commentsData = [];

// Total Comments
$totalRows = $xpath->query("//div[@id='tab2']//table[1]//tbody/tr");
foreach ($totalRows as $row) {
    // "5 EA "
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
$unitRows = $xpath->query("//div[@id='tab2']//table[2]//tbody/tr");
foreach ($unitRows as $row) {
    // Hidden input has sectorCd
    $sectorCd = $xpath->evaluate("string(.//input[@name='sectorCd']/@value)", $row);
    $unitComment = $xpath->evaluate("string(.//textarea[@name='unitComment'])", $row);
    
    if ($sectorCd) {
        $commentsData[] = [
            'test_seq' => $testSeq,
            'comment_type' => 'unit',
            'condition_value' => $sectorCd,
            'good_comment' => null, // Unit just has a general weak comment
            'weak_comment' => $unitComment,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

DB::table('igbh_test_comments')->insert($commentsData);

echo "Successfully saved configuration to DB for Test Seq $testSeq" . PHP_EOL;
