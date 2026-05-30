<?php
$html = file_get_contents('viewReportIGSumm.html');

// Find the section
$sectionPos = mb_strpos($html, 'Kết quả đánh giá theo câu hỏi riêng biệt');
$tableStart = mb_strpos($html, '<table', $sectionPos);
$tableEnd = mb_strpos($html, '</table>', $tableStart);
$tableHtml = mb_substr($html, $tableStart, $tableEnd - $tableStart);

function extractRowValues($html, $rowHeader) {
    $pos = mb_strpos($html, $rowHeader);
    if ($pos === false) return null;
    
    $trEnd = mb_strpos($html, '</tr>', $pos);
    $trHtml = mb_substr($html, $pos, $trEnd - $pos);
    
    preg_match_all('/<td[^>]*>(?:<span[^>]*>)?\s*([\d\.]+)\s*(?:<\/span>)?\s*<\/td>/iu', $trHtml, $matches);
    
    return array_slice($matches[1], 0, 5);
}

$scores = [
    'max_score' => extractRowValues($tableHtml, 'Điểm chuẩn'),
    'score' => extractRowValues($tableHtml, 'Điểm thực tế'),
    'concept' => extractRowValues($tableHtml, 'Khái niệm/hiểu'),
    'strategy' => extractRowValues($tableHtml, 'Chiến lược/suy luận'),
    'calculation' => extractRowValues($tableHtml, 'Tính toán/thực hành'),
    'expression' => extractRowValues($tableHtml, 'Diễn đạt/biểu hiện')
];

print_r($scores);
