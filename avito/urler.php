<?php
/*
 * Urler
 * Avito.ru category links parser
 */
require_once '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'web_bots.php';
echo "\n[+] Started\n";

//Init
$db_dir = '..' . DIRECTORY_SEPARATOR . 'db';
$tmp_dir = '..' . DIRECTORY_SEPARATOR . 'tmp';
$urls_file = $db_dir . DIRECTORY_SEPARATOR . 'urls.txt';
if (!is_dir($db_dir))
    mkdir($db_dir);
if (!is_dir($tmp_dir))
    mkdir($tmp_dir);

$url = 'http://www.avito.ru/sankt-peterburg';
$marker = '/sankt-peterburg/';
$add_url = '?metro_id=170&user=1&s=1';
$hash = md5($url);
$debug_file = $tmp_dir . DIRECTORY_SEPARATOR . $hash . '.html'; //cache
$in = http_get_debug($url, $debug_file);
if (!$in)
    exit('[-] Cant load html');

//Base Url 
$url = $in['STATUS']['url'];
$base_url = get_base_page_address($url);
echo "[+] Base url: $base_url\n";

$tidy = tidy_html($in['FILE']);

$links = get_all_links($tidy);
foreach ($links as $link){
    if(stristr($link, $marker)) {
        $n = substr_count($link, '/');
        if($n==2) $good_links[]= resolve_address($link, $base_url).$add_url;
    }    
}
$uniq_links = array_unique($good_links);
$uniq_links_size = sizeof($uniq_links);
echo "[+] $uniq_links_size urls found\n";

if(array2file($urls_file,$uniq_links)) echo "[+] Saved to $urls_file\n";

?>