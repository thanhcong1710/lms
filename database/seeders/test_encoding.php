<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/lms_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/lms_cookies.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/eval/getInputList.do?draw=1&start=0&length=100&searchDatas%5B0%5D%5Bcol%5D=testCd&searchDatas%5B0%5D%5Bval%5D=&searchDatas%5B1%5D%5Bcol%5D=statCd&searchDatas%5B1%5D%5Bval%5D=&searchDatas%5B2%5D%5Bcol%5D=levelCd&searchDatas%5B2%5D%5Bval%5D=&searchCols=&searchText=');
$response = curl_exec($ch);

$encodings = ['EUC-KR', 'CP949', 'Windows-1258', 'ISO-8859-1'];
foreach ($encodings as $enc) {
    $converted = mb_convert_encoding($response, 'UTF-8', $enc);
    $data = json_decode($converted, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "Successfully decoded with $enc:\n";
        if (isset($data['data'][0])) {
            echo "Sample text: " . $data['data'][0]['stuNm'] . " - " . $data['data'][0]['testNm'] . "\n";
        }
    } else {
        echo "Failed to decode after converting from $enc\n";
    }
}
