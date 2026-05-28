<?php
// Load HTML files
$inputViewHtml = file_get_contents(__DIR__ . '/student_detail_inputView.html');
$viewReportHtml = file_get_contents(__DIR__ . '/student_detail_viewReport.html');

$result = [
    'rubrics' => [],
    'report_data' => [],
    'skills_grade' => []
];

// 1. Parse teacher's input (rubric scores) from inputView.html
$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $inputViewHtml);
$xpath = new DOMXPath($dom);

// Find the table body rows
$rows = $xpath->query('//table[contains(@class, "bulk_action")]/tbody/tr');

for ($i = 0; $i < $rows->length; $i += 2) {
    // Each rubric takes 2 rows (rowspan="2"), the first row has the data
    $row = $rows->item($i);
    $tds = $xpath->query('td', $row);
    
    if ($tds->length >= 10) {
        $qNo = trim($tds->item(0)->nodeValue);
        $mainCat = trim($tds->item(1)->nodeValue);
        $subCat = trim($tds->item(2)->nodeValue);
        $rubricName = trim($tds->item(3)->nodeValue);
        
        // Find the cell with the red check
        $assignedScore = null;
        for ($j = 6; $j < $tds->length; $j += 2) {
            $checkIcon = $xpath->query('.//i[contains(@class, "fa-check")]', $tds->item($j));
            if ($checkIcon->length > 0) {
                // The score is in the next td
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

// 2. Parse report chart data from viewReport.html
if (preg_match('/var tt\d+data = (\{.*?\})/', $viewReportHtml, $matches)) {
    // Convert Javascript object to JSON (fixing single quotes to double quotes if needed)
    $jsonStr = str_replace("'", '"', $matches[1]);
    $result['report_data'] = json_decode($jsonStr, true);
}

// 3. Parse skill grades from viewReport.html
$domReport = new DOMDocument();
@$domReport->loadHTML('<?xml encoding="UTF-8">' . $viewReportHtml);
$xpathReport = new DOMXPath($domReport);

// Extract from table_detail
$gradeRows = $xpathReport->query('//table[contains(@class, "table_detail")]/tbody/tr');
foreach ($gradeRows as $row) {
    $tds = $xpathReport->query('td', $row);
    if ($tds->length == 3) {
        $skillName = trim($tds->item(0)->nodeValue);
        $gradeNode = $xpathReport->query('.//span[contains(@class, "no-show")]', $tds->item(2));
        if ($gradeNode->length > 0) {
            $grade = trim($gradeNode->item(0)->nodeValue);
            $result['skills_grade'][] = [
                'skill' => $skillName,
                'grade' => $grade
            ];
        }
    }
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
