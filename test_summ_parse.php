<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/admin_summ.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/admin_summ.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Login
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'cms.vn', 'membPasswd' => 'hpa@XuanDieu'
]));
curl_exec($ch);

// Get summative config via correct URL
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/sysm/igmetaSummReg.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['testSeq' => 87])); // BH-R4
$html = curl_exec($ch);
file_put_contents('/tmp/summReg_87.html', $html);
echo "summReg_87 size: " . strlen($html) . "\n";

// Parse it
$dom = new DOMDocument();
@$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
$xpath = new DOMXPath($dom);

// Get test name
$testNm = trim($xpath->evaluate("string(//table[@class='table table_row']//tr[1]/td[1])"));
$datePeriod = trim($xpath->evaluate("string(//table[@class='table table_row']//tr[1]/td[2])"));
$levelCd = trim($xpath->evaluate("string(//table[@class='table table_row']//tr[2]/td[1])"));
$evalYear = trim($xpath->evaluate("string(//table[@class='table table_row']//tr[2]/td[2])"));
echo "Test: $testNm | Date: $datePeriod | Level: $levelCd | Year: $evalYear\n";

// Get eval content items
$rows = $xpath->query("//table[contains(@class,'ui celled')]//tbody/tr");
echo "Found " . $rows->length . " rows\n";
foreach ($rows as $i => $row) {
    $tds = $row->getElementsByTagName('td');
    $sortNo = '';
    $content = '';
    $score = '';
    
    foreach ($tds as $j => $td) {
        $text = trim($td->textContent);
        if ($j == 0) $sortNo = $text;
        if ($j == 1) $content = $text;
        if ($j == 2) $score = $text;
    }
    
    // Check for input-based content
    $inputs = $xpath->query('.//input[@name="evalContent"]', $row);
    if ($inputs->length > 0) {
        $content = $inputs->item(0)->getAttribute('value');
    }
    $scoreInputs = $xpath->query('.//input[@name="stdScore"]', $row);
    if ($scoreInputs->length > 0) {
        $score = $scoreInputs->item(0)->getAttribute('value');
    }
    
    if ($sortNo && $sortNo != 'Tổng') {
        echo "  Q$sortNo: $content (Score: $score)\n";
    }
}
