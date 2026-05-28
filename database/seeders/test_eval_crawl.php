<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/lms_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/lms_cookies.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$headers = [
    'Accept: application/json, text/javascript, */*; q=0.01',
    'X-Requested-With: XMLHttpRequest',
    'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// 1. Login
echo "Logging in...\n";
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'cms.vn',
    'membPasswd' => 'hpa@XuanDieu'
]));
$loginResponse = curl_exec($ch);

function safe_json_decode($response) {
    // Just use raw decode now that headers are set
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $data;
    }
    echo "JSON Decode Error: " . json_last_error_msg() . "\n";
    return null;
}

// 2. Get Input List
echo "\nFetching getInputList.do...\n";
$inputListUrl = 'https://lms-vn.cmsedu.net/eval/getInputList.do?draw=1&start=0&length=100&searchDatas%5B0%5D%5Bcol%5D=testCd&searchDatas%5B0%5D%5Bval%5D=&searchDatas%5B1%5D%5Bcol%5D=statCd&searchDatas%5B1%5D%5Bval%5D=&searchDatas%5B2%5D%5Bcol%5D=levelCd&searchDatas%5B2%5D%5Bval%5D=&searchCols=&searchText=';
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_URL, $inputListUrl);
$inputListResponse = curl_exec($ch);
$inputList = safe_json_decode($inputListResponse);
if ($inputList) {
    echo "Fetched " . count($inputList['data']) . " input list items.\n";
    file_put_contents(__DIR__ . '/ucrea_eval_input_list.json', json_encode($inputList['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
} else {
    echo "Failed to decode input list.\n";
}

// 3. Get Result List
echo "\nFetching getResultList.do...\n";
$resultListUrl = 'https://lms-vn.cmsedu.net/eval/getResultList.do?draw=1&start=0&length=100&searchDatas%5B0%5D%5Bcol%5D=testCd&searchDatas%5B0%5D%5Bval%5D=&searchDatas%5B1%5D%5Bcol%5D=levelCd&searchDatas%5B1%5D%5Bval%5D=&searchCols=&searchText=';
curl_setopt($ch, CURLOPT_URL, $resultListUrl);
$resultListResponse = curl_exec($ch);
$resultList = safe_json_decode($resultListResponse);
if ($resultList) {
    echo "Fetched " . count($resultList['data']) . " result list items.\n";
    file_put_contents(__DIR__ . '/ucrea_eval_result_list.json', json_encode($resultList['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
} else {
    echo "Failed to decode result list.\n";
}

// 4. Get one Input View HTML to extract rubrics
if (!empty($inputList['data'])) {
    $first = $inputList['data'][0];
    $testCd = $first['testCd'];
    $levelCd = $first['levelCd'];
    $testSeq = $first['testSeq'];
    $resultSeq = $first['resultSeq'] ?? '';
    
    echo "\nFetching inputView.do for testSeq $testSeq...\n";
    $inputViewUrl = "https://lms-vn.cmsedu.net/eval/inputView.do";
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_URL, $inputViewUrl);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'testCd' => $testCd,
        'levelCd' => $levelCd,
        'testSeq' => $testSeq,
        'resultSeq' => $resultSeq,
        'paramMap' => ''
    ]));
    $inputViewHtml = curl_exec($ch);
    $inputViewHtml = mb_convert_encoding($inputViewHtml, 'UTF-8', 'ISO-8859-1');
    file_put_contents(__DIR__ . '/inputView.html', $inputViewHtml);
    echo "Saved inputView.html, size: " . strlen($inputViewHtml) . "\n";
}

curl_close($ch);
