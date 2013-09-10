<?php
/*
 * Urler
 * Avito.ru category links parser
*/
require_once '..'.DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'web_bots.php';
echo "\n[+] Started\n";

//Init
$db_dir ='..'.DIRECTORY_SEPARATOR.'db';
$tmp_dir ='..'.DIRECTORY_SEPARATOR.'tmp';
$urls_file = $db_dir.DIRECTORY_SEPARATOR.'urls.txt';
if(!is_dir($db_dir)) mkdir ($db_dir);
if(!is_dir($tmp_dir)) mkdir ($tmp_dir);

$url = 'http://avito.ru/';
$hash = md5($url);
$debug_file = $tmp_dir.DIRECTORY_SEPARATOR.$hash.'.html';//cache
$in = http_get_debug($url,$debug_file);
if(!$in) exit('[-] Cant load html');

//Base Url 
$url = $in['STATUS']['url'];
$base_url = get_base_page_address($url);
echo "[+] Base url: $base_url\n";

$tidy = tidy_html($in['FILE']);

$links = get_all_links($tidy);
var_dump($links);

//Marks for blocks
//$html_blocks = get_divs($tidy,$div_marks);//Get blocks from html
//$corrupt_blocks = 0;

//echo "[+] $global_size blocks found\n";
//if(save_json($db_file,$global_blocks)) echo "[+] Saved\n";

function get_all_links($str){
    $links = parse_array($str, "<a", "</a>");
    foreach($links as $link){
        $hrefs[] = get_attribute($link, "href");
    }
    return $hrefs;
}

?>