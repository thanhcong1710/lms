<?php
/**
 * Crawl IG.BH Test Config from CMS LMS
 * Login as cms.vn (admin) to access Quản lý hệ thống -> Quản lý bài kiểm tra IG.BH
 */

$cookieFile = '/tmp/igbh_admin_cookies.txt';
$outputDir = __DIR__;

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$headers = [
    'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
    'Accept-Language: vi-VN,vi;q=0.9',
    'X-Requested-With: XMLHttpRequest',
    'Accept: application/json, text/javascript, */*; q=0.01'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// =============================================
// 1. Login
// =============================================
echo "=== STEP 1: Login ===\n";
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'cms.vn',
    'membPasswd' => 'hpa@XuanDieu'
]));

$loginResp = curl_exec($ch);
$loginData = json_decode($loginResp, true);
if (!isset($loginData['result']) || $loginData['result'] != 1) {
    die("Login failed: $loginResp\n");
}
echo "Login OK!\n";

// =============================================
// 2. Fetch test list (admin API for IGBH tests)
// =============================================
echo "\n=== STEP 2: Fetch IG.BH Test List (Admin) ===\n";

// Try getTestList endpoint (admin side)
$testListUrls = [
    'https://lms-vn.cmsedu.net/sys/getIgElTestList.do',
    'https://lms-vn.cmsedu.net/igel/getTestList.do',
    'https://lms-vn.cmsedu.net/sys/getIgelTestList.do',
];

foreach ($testListUrls as $url) {
    echo "Trying: $url\n";
    curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query([
        'draw' => 1,
        'start' => 0,
        'length' => 200
    ]));
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_POST, false);

    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "HTTP $httpCode, Length: " . strlen($resp) . "\n";

    if ($httpCode === 200 && strlen($resp) > 10) {
        $data = json_decode($resp, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($data['data'])) {
            echo "SUCCESS! Found " . count($data['data']) . " tests\n";
            file_put_contents("$outputDir/igbh_test_list.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo "Saved to igbh_test_list.json\n";
            break;
        }
    }
    echo "  -> No data\n";
}

// =============================================
// 3. Fetch admin page HTML for IGBH test management
// =============================================
echo "\n=== STEP 3: Fetch Admin Test Management Pages ===\n";

$adminPages = [
    'sys_igbh_list' => 'https://lms-vn.cmsedu.net/sys/igelTestList.do',
    'sys_igbh_list2' => 'https://lms-vn.cmsedu.net/igel/testList.do',
    'sys_igbh_list3' => 'https://lms-vn.cmsedu.net/sys/igelList.do',
];

foreach ($adminPages as $name => $url) {
    echo "Trying: $url\n";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: vi-VN,vi;q=0.9'
    ]);

    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "HTTP $httpCode, Length: " . strlen($resp) . "\n";

    if ($httpCode === 200 && strlen($resp) > 500) {
        file_put_contents("$outputDir/{$name}.html", $resp);
        echo "Saved {$name}.html\n";
    }
}

// =============================================
// 4. Try fetching specific test config by testSeq
// =============================================
echo "\n=== STEP 4: Try fetching test config URLs ===\n";

// From previous crawls we know testSeq values exist (e.g. 124 was used)
// Let's try a range to find valid configs
$testConfigUrls = [
    'https://lms-vn.cmsedu.net/sys/igelTestMod.do?testSeq=124',
    'https://lms-vn.cmsedu.net/sys/igelTestView.do?testSeq=124',
    'https://lms-vn.cmsedu.net/igel/testMod.do?testSeq=124',
    'https://lms-vn.cmsedu.net/igel/testView.do?testSeq=124',
    'https://lms-vn.cmsedu.net/sys/getIgelTestInfo.do?testSeq=124',
];

foreach ($testConfigUrls as $url) {
    echo "Trying: $url\n";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: Mozilla/5.0',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    ]);

    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "HTTP $httpCode, Length: " . strlen($resp) . "\n";

    if ($httpCode === 200 && strlen($resp) > 500) {
        $filename = preg_replace('/[^a-z0-9_]/', '_', strtolower(parse_url($url, PHP_URL_PATH))) . '.html';
        file_put_contents("$outputDir/config_{$filename}", $resp);
        echo "Saved config file\n";
        break;
    }
}

// =============================================
// 5. Crawl JSON for test diagnosis list (teacher account) to find testSeqs
// =============================================
echo "\n=== STEP 5: Re-login as teacher and fetch getDiagList for testSeq info ===\n";

// Login as loanntp (teacher)
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'loanntp',
    'membPasswd' => '@12345678'
]));
$loginResp2 = curl_exec($ch);
$loginData2 = json_decode($loginResp2, true);
echo "Teacher login: " . ($loginData2['result'] == 1 ? "OK" : "FAIL") . "\n";

// Get diag list with all testSeqs
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/getDiagList.do?' . http_build_query([
    'draw' => 1,
    'start' => 0,
    'length' => 1000,
]));
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json, text/javascript, */*; q=0.01',
    'X-Requested-With: XMLHttpRequest',
    'User-Agent: Mozilla/5.0'
]);

$diagResp = curl_exec($ch);
$diagData = json_decode($diagResp, true);
if (json_last_error() === JSON_ERROR_NONE && isset($diagData['data'])) {
    // Get unique testSeq values
    $testSeqs = array_unique(array_column($diagData['data'], 'testSeq'));
    echo "Unique testSeq values found: " . implode(', ', $testSeqs) . "\n";

    // Try to get test config for each unique testSeq
    // Re-login as admin first
    curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'membId' => 'cms.vn',
        'membPasswd' => 'hpa@XuanDieu'
    ]));
    curl_exec($ch);
    echo "Admin re-login done\n";

    foreach ($testSeqs as $testSeq) {
        // Try admin config endpoints
        $configUrls = [
            "https://lms-vn.cmsedu.net/sys/igelTestMod.do?testSeq={$testSeq}",
            "https://lms-vn.cmsedu.net/sys/igelTestInfo.do?testSeq={$testSeq}",
        ];

        foreach ($configUrls as $url) {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: Mozilla/5.0',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ]);

            $resp = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode === 200 && strlen($resp) > 500) {
                $filename = "igbh_test_config_{$testSeq}.html";
                file_put_contents("$outputDir/$filename", $resp);
                echo "Saved config for testSeq $testSeq\n";
                break;
            }
        }
        usleep(200000);
    }
}

// =============================================
// 6. Try finding the admin list page via navigation
// =============================================
echo "\n=== STEP 6: Fetch admin navigation HTML ===\n";
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/sys/igelTestList.do');
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: Mozilla/5.0',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language: vi-VN,vi;q=0.9'
]);
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "igelTestList.do: HTTP $httpCode, Length " . strlen($resp) . "\n";
if ($httpCode === 200 && strlen($resp) > 500) {
    file_put_contents("$outputDir/igbh_admin_test_list.html", $resp);
    echo "Saved admin test list HTML\n";
}

curl_close($ch);
echo "\n=== DONE ===\n";
