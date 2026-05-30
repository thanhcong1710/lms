<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/igbh_crawler_admin.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/igbh_crawler_admin.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Fetch HTML of the list page to find JS functions
curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net/igel/smtvList.do');
curl_setopt($ch, CURLOPT_POST, false);
$html = curl_exec($ch);
file_put_contents('smtvList_html.txt', $html);

// Now try to fetch the JS file that handles the list
preg_match_all('/<script\s+src="([^"]+)"/', $html, $matches);
foreach ($matches[1] as $src) {
    if (strpos($src, 'smtv') !== false || strpos($src, 'igel') !== false) {
        curl_setopt($ch, CURLOPT_URL, 'https://lms-vn.cmsedu.net' . $src);
        $js = curl_exec($ch);
        file_put_contents(basename($src) . '.txt', $js);
        echo "Found JS: " . $src . "\n";
    }
}
curl_close($ch);
