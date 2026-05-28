<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/lms_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/lms_cookies.txt');
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
    'membId' => 'cms.vn',
    'membPasswd' => 'hpa@XuanDieu'
]));
curl_exec($ch);

$testCd = 'TT001';
$levelCd = 'L4';
$testSeq = 1;
$resultSeq = 2312; // Student: Trịnh Vũ Tuệ Châu

// 2. Fetch inputView.do to get the teacher's grading inputs
echo "Fetching inputView.do for detailed grades...\n";
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/eval/inputView.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'testCd' => $testCd,
    'levelCd' => $levelCd,
    'testSeq' => $testSeq,
    'resultSeq' => $resultSeq,
    'paramMap' => ''
]));
$inputViewHtml = curl_exec($ch);
file_put_contents(__DIR__ . '/student_detail_inputView.html', $inputViewHtml);
echo "Saved student_detail_inputView.html (" . strlen($inputViewHtml) . " bytes)\n";

// 3. Fetch viewReport.do to get the final generated report
echo "Fetching viewReport.do for generated report...\n";
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/eval/viewReport.do');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'testCd' => $testCd,
    'levelCd' => $levelCd,
    'testSeq' => $testSeq,
    'resultSeq' => $resultSeq
]));
$reportHtml = curl_exec($ch);
file_put_contents(__DIR__ . '/student_detail_viewReport.html', $reportHtml);
echo "Saved student_detail_viewReport.html (" . strlen($reportHtml) . " bytes)\n";

curl_close($ch);
