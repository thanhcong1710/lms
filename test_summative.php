<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/igbh_crawler_admin.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/igbh_crawler_admin.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// 1. Login
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'cms.vn',
    'membPasswd' => 'hpa@XuanDieu'
]));
$loginResp = curl_exec($ch);
echo "Login Response: " . $loginResp . "\n";

// 2. Fetch list of summative results
$params = http_build_query([
    'draw' => 1,
    'start' => 0,
    'length' => 5,
    'searchDatas[0][col]' => 'testSeq',
    'searchDatas[0][val]' => '',
    'searchDatas[1][col]' => 'levelCd',
    'searchDatas[1][val]' => '',
    'searchDatas[2][col]' => 'sortCd',
    'searchDatas[2][val]' => '',
    'searchCols' => '',
    'searchText' => '',
    'searchFromDt' => '',
    'searchToDt' => ''
]);

curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/getSmtvList.do?' . $params);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: Mozilla/5.0',
    'Accept: application/json, text/javascript, */*; q=0.01',
    'X-Requested-With: XMLHttpRequest'
]);

$listJson = curl_exec($ch);
$listData = json_decode($listJson, true);
if (isset($listData['data']) && count($listData['data']) > 0) {
    echo "Sample data: \n";
    print_r($listData['data'][0]);
}

curl_close($ch);
