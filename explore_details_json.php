<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/crawl_cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/crawl_cookie.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Login as teacher
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'loanntp',
    'membPasswd' => '@12345678'
]));
curl_exec($ch);

// Test fetching getWeeklyStudentEachList.do
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/getWeeklyStudentEachList.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'testSeq' => 89, // BH-B2
    'classSeq' => 6950,
    'eachSeq' => 36160
]));
$json = curl_exec($ch);
file_put_contents('/tmp/weekly_students.json', $json);
echo "weekly_students: " . substr($json, 0, 500) . "...\n";

// Login as admin
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'cms.vn',
    'membPasswd' => 'hpa@XuanDieu'
]));
curl_exec($ch);

// Test fetching getSmtvResultList.do or similar
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/getSmtvResultList.do'); // Guessing the endpoint
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'finalSeq' => 3982,
    'resultSeq' => 23870,
    'stuSeq' => 29009
]));
$json = curl_exec($ch);
file_put_contents('/tmp/smtv_result.json', $json);
echo "smtv_result: " . substr($json, 0, 100) . "...\n";

