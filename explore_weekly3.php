<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/teacher_cookie2.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/teacher_cookie2.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Login as teacher
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'loanntp',
    'membPasswd' => '@12345678'
]));
$resp = curl_exec($ch);
echo "Login: " . $resp . "\n\n";

// Get weeklyReg.do
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/weeklyReg.do');
curl_setopt($ch, CURLOPT_POST, false);
$html = curl_exec($ch);
file_put_contents('/tmp/weeklyReg.html', $html);
echo "weeklyReg size: " . strlen($html) . "\n";

// Get weeklyMod.do (with first record from list)
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/weeklyMod.do');
curl_setopt($ch, CURLOPT_POST, true);
// Try with some params 
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'testSeq' => '',
    'classSeq' => '',
    'eachCd' => 'EC003' // Tuần thứ 3
]));
$html = curl_exec($ch);
file_put_contents('/tmp/weeklyMod.html', $html);
echo "weeklyMod size: " . strlen($html) . "\n";

// Get weeklyList.do
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/weeklyList.do');
curl_setopt($ch, CURLOPT_POST, false);
$html = curl_exec($ch);
file_put_contents('/tmp/weeklyList.html', $html);
echo "weeklyList size: " . strlen($html) . "\n";
