<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/igbh_crawler_admin.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/igbh_crawler_admin.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// 1. Login
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/login/ajaxLogin.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'membId' => 'cms.vn',
    'membPasswd' => 'hpa@XuanDieu'
]));
curl_exec($ch);

// 2. Fetch Report
// Using finalSeq: 3982, resultSeq: 23876, stuSeq: 33450 from the previous getSmtvList.do output
// Wait, I don't know eachSeq, maybe pass empty string.
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/viewReportIGSumm.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'finalSeq' => '3982',
    'resultSeq' => '23876',
    'eachSeq' => '',
    'stuSeq' => '33450'
]));
$html = curl_exec($ch);
file_put_contents('viewReportIGSumm.html', $html);
curl_close($ch);
