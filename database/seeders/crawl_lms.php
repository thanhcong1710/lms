<?php

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/lms_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/lms_cookies.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// 1. Login
echo "Logging in...\n";
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'cms.vn',
    'membPasswd' => 'hpa@XuanDieu'
]));
$loginResponse = curl_exec($ch);
$loginData = json_decode($loginResponse, true);

if (!isset($loginData['result']) || $loginData['result'] != 1) {
    die("Login failed: " . $loginResponse . "\n");
}
echo "Login successful!\n";
function safe_json_decode($response, $label)
{
    // Try raw decode
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "[$label] Decoded successfully with raw UTF-8.\n";
        return $data;
    }

    // Try UTF-8 ignore conversion
    $clean = mb_convert_encoding($response, 'UTF-8', 'UTF-8');
    $data = json_decode($clean, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "[$label] Decoded successfully after mb_convert_encoding(UTF-8, UTF-8).\n";
        return $data;
    }

    // Try EUC-KR conversion
    $clean = mb_convert_encoding($response, 'UTF-8', 'EUC-KR');
    $data = json_decode($clean, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "[$label] Decoded successfully after conversion from EUC-KR.\n";
        return $data;
    }

    // Try CP949 conversion
    $clean = mb_convert_encoding($response, 'UTF-8', 'CP949');
    $data = json_decode($clean, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "[$label] Decoded successfully after conversion from CP949.\n";
        return $data;
    }

    // Try Windows-1258 conversion
    $clean = mb_convert_encoding($response, 'UTF-8', 'Windows-1258');
    $data = json_decode($clean, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "[$label] Decoded successfully after conversion from Windows-1258.\n";
        return $data;
    }

    // Try iconv ignore UTF-8
    $clean = iconv('UTF-8', 'UTF-8//IGNORE', $response);
    $data = json_decode($clean, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "[$label] Decoded successfully with iconv IGNORE.\n";
        return $data;
    }

    echo "[$label] All decode attempts failed. Last error: " . json_last_error_msg() . "\n";
    return null;
}

// 2. Fetch IG.BH list
echo "Fetching IG.BH list...\n";
$igbhUrl = 'https://lms-vn.cmsedu.net/igel/getPaperList.do?draw=1&start=0&length=100&searchDatas%5B0%5D%5Bcol%5D=testTypeCd&searchDatas%5B0%5D%5Bval%5D=&searchDatas%5B1%5D%5Bcol%5D=quarterCd&searchDatas%5B1%5D%5Bval%5D=&searchDatas%5B2%5D%5Bcol%5D=gradeLevelCd&searchDatas%5B2%5D%5Bval%5D=&searchCols=&searchText=';
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_URL, $igbhUrl);
$igbhResponse = curl_exec($ch);
$igbhCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "IG.BH HTTP Code: $igbhCode, Length: " . strlen($igbhResponse) . "\n";
$igbhData = safe_json_decode($igbhResponse, 'IG.BH');
$igbhItems = $igbhData['data'] ?? [];
echo "Fetched " . count($igbhItems) . " IG.BH items.\n";

// 3. Fetch UCREA list
echo "Fetching UCREA list...\n";
$ucreaUrl = 'https://lms-vn.cmsedu.net/test/getPaperList.do?draw=2&start=0&length=100&searchDatas%5B0%5D%5Bcol%5D=levelCd&searchDatas%5B0%5D%5Bval%5D=&searchDatas%5B1%5D%5Bcol%5D=testCd&searchDatas%5B1%5D%5Bval%5D=&searchDatas%5B2%5D%5Bcol%5D=statCd&searchDatas%5B2%5D%5Bval%5D=TS002&searchList=info';
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_URL, $ucreaUrl);
$ucreaResponse = curl_exec($ch);
$ucreaCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "UCREA HTTP Code: $ucreaCode, Length: " . strlen($ucreaResponse) . "\n";
$ucreaData = safe_json_decode($ucreaResponse, 'UCREA');
$ucreaItems = $ucreaData['data'] ?? [];
echo "Fetched " . count($ucreaItems) . " UCREA items.\n";

$combined = [];

function fix_vietnamese_text($text) {
    if (!$text) return $text;
    $replacements = [
        'l?p' => 'lớp',
        'L?p' => 'Lớp',
        '??nh gi?' => 'Đánh giá',
        '?nh gi' => 'Đánh giá',
        'h?c th?' => 'học thử',
        'H?c sinh gi?i' => 'Học sinh giỏi',
        'Qu? I' => 'Quý I',
    ];
    return str_replace(array_keys($replacements), array_values($replacements), $text);
}

// Process IG.BH
foreach ($igbhItems as $item) {
    $combined[] = [
        'test_type' => 'IG.BH',
        'testSeq' => $item['testSeq'] ?? null,
        'evalNm' => fix_vietnamese_text($item['evalNm'] ?? null),
        'testGradeCdNm' => fix_vietnamese_text($item['testGradeCdNm'] ?? null),
        'testGradeCd' => $item['testGradeCd'] ?? null,
        'testTypeCdNm' => fix_vietnamese_text($item['testTypeCdNm'] ?? null),
        'testTypeCd' => $item['testTypeCd'] ?? null,
        'filePath' => $item['filePath'] ?? null,
        'realFileNm' => $item['realFileNm'] ?? null,
    ];
}

// Process UCREA
foreach ($ucreaItems as $idx => $item) {
    $attachId = $item['attachId'] ?? '';
    $attachSeq = $item['attachSeq'] ?? 1;
    if (empty($attachSeq)) {
        $attachSeq = 1;
    }

    $filePath = null;
    if ($attachId) {
        $filePath = "/popup/previewPaper.do?attachId=" . urlencode($attachId) . "&attachSeq=" . urlencode($attachSeq);
    }

    $combined[] = [
        'test_type' => 'UCREA',
        'testSeq' => $item['testSeq'] ?? null,
        'evalNm' => fix_vietnamese_text($item['testNm'] ?? null),
        'testGradeCdNm' => fix_vietnamese_text($item['levelCdNm'] ?? null),
        'testGradeCd' => $item['testCd'] ?? null,
        'testTypeCdNm' => fix_vietnamese_text($item['testCdNm'] ?? null),
        'testTypeCd' => $item['testCd'] ?? null,
        'filePath' => $filePath,
        'realFileNm' => $filePath ? basename($filePath) : null,
    ];
}

echo "Total items crawled: " . count($combined) . "\n";

$jsonFile = __DIR__ . '/lms_tests.json';
file_put_contents($jsonFile, json_encode($combined, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Saved combined JSON to: {$jsonFile}\n";

curl_close($ch);
