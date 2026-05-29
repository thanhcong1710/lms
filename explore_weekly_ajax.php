<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/teacher_cookie4.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/teacher_cookie4.txt');
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

// Datatable list for weekly evals
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/getWeeklyList.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'start' => 0,
    'length' => 100,
    'sTestSeq' => '',
    'sClassSeq' => '',
    'sEachCd' => ''
]));
$resp = curl_exec($ch);
file_put_contents('/tmp/getWeeklyList.json', $resp);

echo "getWeeklyList: " . strlen($resp) . "\n";
