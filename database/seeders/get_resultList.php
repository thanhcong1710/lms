<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/lms_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/lms_cookies.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$headers = [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
    'User-Agent: Mozilla/5.0'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/eval/resultList.do');
$html = curl_exec($ch);
file_put_contents('/tmp/resultList.html', $html);
echo "Fetched resultList.do\n";
