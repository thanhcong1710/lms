<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/igbh_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/igbh_cookies.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$headers = [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
    'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// 1. Login
echo "Logging in...\n";
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'loanntp',
    'membPasswd' => '@12345678'
]));
$loginRes = curl_exec($ch);
echo "Login response: " . $loginRes . "\n";

// 2. Fetch diagList.do
echo "Fetching diagList.do...\n";
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/diagList.do');
curl_setopt($ch, CURLOPT_POST, false);
$listHtml = curl_exec($ch);
file_put_contents(__DIR__ . '/igbh_diagList.html', $listHtml);
echo "Saved igbh_diagList.html (" . strlen($listHtml) . " bytes)\n";

curl_close($ch);
