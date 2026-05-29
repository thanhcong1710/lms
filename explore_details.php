<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/crawl_cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/crawl_cookie.txt');
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

// Test fetching one smtvMod.do
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/smtvMod.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'finalSeq' => 3982,
    'resultSeq' => 23870,
    'stuSeq' => 29009,
    'classSeq' => 6895
]));
$html = curl_exec($ch);
file_put_contents('/tmp/smtvMod_test.html', $html);
echo "smtvMod length: " . strlen($html) . "\n";

// Login as teacher
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'loanntp',
    'membPasswd' => '@12345678'
]));
curl_exec($ch);

// Test fetching one weeklyMod.do
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/weeklyMod.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'eachSeq' => 36160
]));
$html = curl_exec($ch);
file_put_contents('/tmp/weeklyMod_test.html', $html);
echo "weeklyMod length: " . strlen($html) . "\n";
