<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/igbh_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/igbh_cookies.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$headers = [
    'Accept: application/json, text/javascript, */*; q=0.01',
    'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
    'X-Requested-With: XMLHttpRequest'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// 1. Fetch getDiagList.do
echo "Fetching getDiagList.do...\n";
$params = [
    'draw' => 1,
    'start' => 0,
    'length' => 100,
    'searchCols' => '',
    'searchText' => '',
    'searchDatas[0][col]' => 'quarterCd',
    'searchDatas[0][val]' => '',
    'searchDatas[1][col]' => 'levelCd',
    'searchDatas[1][val]' => ''
];
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/getDiagList.do?' . http_build_query($params));
curl_setopt($ch, CURLOPT_POST, false);
$res = curl_exec($ch);
echo "Response: " . substr($res, 0, 1000) . "\n";
file_put_contents(__DIR__ . '/igbh_list.json', $res);

curl_close($ch);
