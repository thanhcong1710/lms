<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/admin_cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/admin_cookie.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

echo "Logging in...\n";
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'cms.vn',
    'membPasswd' => 'hpa@XuanDieu'
]));
$resp = curl_exec($ch);
echo "Login Response: " . $resp . "\n";

curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/main/leftMenu.do');
curl_setopt($ch, CURLOPT_POST, false);
$html = curl_exec($ch);
if (strlen($html) > 0) {
    preg_match_all('/href="([^"]+)"\s*>([^<]+)/i', $html, $matches);
    for($i = 0; $i < count($matches[0]); $i++) {
        echo $matches[2][$i] . " => " . $matches[1][$i] . "\n";
    }
}
