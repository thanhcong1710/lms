<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/admin_cookie5.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/admin_cookie5.txt');
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

// getSmtvList.do
$params = http_build_query([
    'draw' => 1,
    'start' => 0,
    'length' => 1000
]);
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/getSmtvList.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
$smtvJson = curl_exec($ch);
file_put_contents('/tmp/smtvList.json', $smtvJson);
echo "getSmtvList: " . substr($smtvJson, 0, 100) . "...\n";

// Login as teacher (weekly evaluation list might require teacher account)
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'loanntp',
    'membPasswd' => '@12345678'
]));
curl_exec($ch);

// getWeeklyList.do
$params = http_build_query([
    'draw' => 1,
    'start' => 0,
    'length' => 1000
]);
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/getWeeklyList.do');
curl_setopt($ch, CURLOPT_POST, true); // Actually DataTables might use POST with draw, start, length
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
$weeklyJson = curl_exec($ch);
file_put_contents('/tmp/weeklyList.json', $weeklyJson);
echo "getWeeklyList (POST): " . substr($weeklyJson, 0, 100) . "...\n";

// If method not allowed, try GET
if (strpos($weeklyJson, '405') !== false) {
    curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/getWeeklyList.do?' . $params);
    curl_setopt($ch, CURLOPT_POST, false);
    $weeklyJson = curl_exec($ch);
    file_put_contents('/tmp/weeklyList.json', $weeklyJson);
    echo "getWeeklyList (GET): " . substr($weeklyJson, 0, 100) . "...\n";
}
