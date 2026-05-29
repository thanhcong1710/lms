<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/teacher_cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/teacher_cookie.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Login as teacher
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'loanntp',
    'membPasswd' => '@12345678'
]));
curl_exec($ch);

// Fetch weeklyMod.do with all parameters
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/weeklyMod.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'eachSeq' => 36160,
    'eachCd' => 'SE003',
    'classSeq' => 6950,
    'paramMap' => ''
]));
$html = curl_exec($ch);
file_put_contents('/tmp/weeklyMod_full.html', $html);
echo "weeklyMod length: " . strlen($html) . "\n";
