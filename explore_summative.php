<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/admin_cookie3.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/admin_cookie3.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Login as admin
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'cms.vn',
    'membPasswd' => 'hpa@XuanDieu'
]));
curl_exec($ch);

// smtvList.do (Tóm tắt kết quả đánh giá IG.BH)
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/smtvList.do');
curl_setopt($ch, CURLOPT_POST, false);
$html = curl_exec($ch);
file_put_contents('/tmp/smtvList.html', $html);
echo "smtvList size: " . strlen($html) . "\n";

// finalList.do (Tóm tắt đánh giá cuối kỳ IG.BH)
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/finalList.do');
curl_setopt($ch, CURLOPT_POST, false);
$html = curl_exec($ch);
file_put_contents('/tmp/finalList.html', $html);
echo "finalList size: " . strlen($html) . "\n";

// Now crawl a summative test config (e.g. testSeq=76, IG-J summative test)
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/sysm/igmetaDiagReg.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['testSeq' => 76]));
$html = curl_exec($ch);
file_put_contents('/tmp/summative_76.html', $html);
echo "summative_76 size: " . strlen($html) . "\n";

// Also try smtvMod.do
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/smtvMod.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['testSeq' => 76]));
$html = curl_exec($ch);
file_put_contents('/tmp/smtvMod.html', $html);
echo "smtvMod size: " . strlen($html) . "\n";

// Try viewReportIGSumm.do 
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/sysm/viewReportIGSumm.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['stuSeq' => 22612, 'testSeq' => 76]));
$html = curl_exec($ch);
file_put_contents('/tmp/viewReportIGSumm.html', $html);
echo "viewReportIGSumm size: " . strlen($html) . "\n";
