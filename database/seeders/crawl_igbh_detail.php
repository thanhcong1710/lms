<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/igbh_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/igbh_cookies.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$headers = [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
    'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$resultSeq = 12025;
$testSeq = 124;
$stuSeq = 27958;

// 1. Fetch diagMod.do
echo "Fetching diagMod.do...\n";
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/diagMod.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'testSeq' => $testSeq,
    'stuSeq' => $stuSeq
]));
$modHtml = curl_exec($ch);
file_put_contents(__DIR__ . '/igbh_diagMod.html', $modHtml);
echo "Saved igbh_diagMod.html (" . strlen($modHtml) . " bytes)\n";

// 2. Fetch viewReportIGDiag.do
echo "Fetching viewReportIGDiag.do...\n";
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/viewReportIGDiag.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'resultSeq' => $resultSeq,
    'testSeq' => $testSeq,
    'stuSeq' => $stuSeq
]));
$reportHtml = curl_exec($ch);
file_put_contents(__DIR__ . '/igbh_viewReport.html', $reportHtml);
echo "Saved igbh_viewReport.html (" . strlen($reportHtml) . " bytes)\n";

curl_close($ch);
