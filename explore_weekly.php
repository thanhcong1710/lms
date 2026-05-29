<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/teacher_cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/teacher_cookie.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

echo "Logging in...\n";
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'loanntp',
    'membPasswd' => '@12345678'
]));
$resp = curl_exec($ch);
echo "Login Response: " . $resp . "\n";

// Weekly Eval list page
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/sysm/igWeeklyDiagList.do'); // Guessing URL based on earlier patterns
curl_setopt($ch, CURLOPT_POST, false);
$html = curl_exec($ch);

// Try to find the exact URL in the menu
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/main/index.do');
$html = curl_exec($ch);
preg_match_all('/href="([^"]+)".*?hàng tuần/i', $html, $matches);
print_r($matches);
