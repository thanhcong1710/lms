<?php
$html = file_get_contents('viewReportIGSumm.html');

$btmPos = mb_strpos($html, 'Phân tích BTM');
$btmEnd = mb_strpos($html, '</table>', $btmPos);
$btmHtml = mb_substr($html, $btmPos, $btmEnd - $btmPos);

$ltmPos = mb_strpos($html, 'Phân tích LTM');
$ltmEnd = mb_strpos($html, '</table>', $ltmPos);
$ltmHtml = mb_substr($html, $ltmPos, $ltmEnd - $ltmPos);

function extractAnalysis($tableHtml) {
    preg_match_all('/<th[^>]*>(Số \d+)<\/th>/iu', $tableHtml, $qMatches);
    $q1 = $qMatches[1][0] ?? '';
    $q2 = $qMatches[1][1] ?? '';
    
    $extract = function($h, $header) {
        $p = mb_strpos($h, $header);
        if ($p === false) return [];
        $trE = mb_strpos($h, '</tr>', $p);
        $trH = mb_substr($h, $p, $trE - $p);
        
        preg_match_all('/(?:<td[^>]*>(?:<span[^>]*>)?\s*([^<]+)\s*(?:<\/span>)?\s*<\/td>)/iu', $trH, $matches);
        return array_map('trim', $matches[1]);
    };
    
    return [
        'q1_label' => $q1,
        'q2_label' => $q2,
        'concept' => $extract($tableHtml, 'Khái niệm/hiểu'),
        'strategy' => $extract($tableHtml, 'Chiến lược/suy luận'),
        'calculation' => $extract($tableHtml, 'Tính toán/thực hành'),
        'expression' => $extract($tableHtml, 'Diễn đạt/biểu hiện'),
        'average' => $extract($tableHtml, 'Trung bình')
    ];
}

print_r(extractAnalysis($btmHtml));
print_r(extractAnalysis($ltmHtml));
