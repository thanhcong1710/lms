<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/admin_cookie2.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/admin_cookie2.txt');
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

curl_setopt($ch, CURLOPT_POST, false);

curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/sysm/weeklyMod.do');
$html = curl_exec($ch);
file_put_contents('/tmp/weeklyMod.html', $html);

curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/sysm/viewReportIGSumm.do');
$html = curl_exec($ch);
file_put_contents('/tmp/viewReportIGSumm.html', $html);

echo "Downloaded!\n";
