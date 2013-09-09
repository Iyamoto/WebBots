﻿<?php
/*
 * Module 1
 * Avito.ru grabber and parser
*/
require_once '..'.DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'web_bots.php';
echo "\n[+] Started\n";

//Init
$db_dir ='..'.DIRECTORY_SEPARATOR.'db';
$tmp_dir ='..'.DIRECTORY_SEPARATOR.'tmp';
$db_file = $db_dir.DIRECTORY_SEPARATOR.'avito.gz';//local, one run data base
$urls_file = $db_dir.DIRECTORY_SEPARATOR.'links.txt';
if(!is_dir($db_dir)) mkdir ($db_dir);
if(!is_dir($tmp_dir)) mkdir ($tmp_dir);

//$urls = load_urls($urls_file);
//$hash = md5($url);
//$debug_file = $tmp_dir.DIRECTORY_SEPARATOR.$hash.'.html';//cache
$debug_file = $tmp_dir.DIRECTORY_SEPARATOR.'debug.html';//cache
$url = "http://www.avito.ru/sankt-peterburg/ohota_i_rybalka?metro_id=170&user=1&s=1";

$in = http_get_debug($url,$debug_file);
if(!$in) {
	echo "[-] Cant load html\n";
	exit;
}	
//Base Url 
$url = $in['STATUS']['url'];
$base_url = get_base_page_address($url);
echo "[+] Base url: $base_url\n";

$tidy = tidy_html($in['FILE']);

//Marks for blocks
$div_marks[] = 'img';
$div_marks[] = 'руб';
$html_blocks = get_divs($tidy,$div_marks);//Get blocks from html
$corrupt_blocks = 0;

//Blocks to elements
for($i=0;$i<count($html_blocks);$i++){
	$fill = 0;
	$blocks[$i]['imgs']= get_imgs($html_blocks[$i], $base_url);
	if(sizeof($blocks[$i]['imgs'])>0) $fill++;
	$blocks[$i]['links']= get_links($html_blocks[$i], $base_url);
	if(sizeof($blocks[$i]['imgs'])>0) $fill++;
	$blocks[$i]['raw_text']= strip_tags($html_blocks[$i]);//Should I keep a raw text?
	if(strlen($blocks[$i]['raw_text'])>0) $fill++;
	$blocks[$i]['clear_text']= clear_text($blocks[$i]['raw_text']);
	if(strlen($blocks[$i]['clear_text'])>0) $fill++;
	$blocks[$i]['price']= get_price($blocks[$i]['clear_text']);
	if(strlen($blocks[$i]['price'])>0) $fill++;
	if($fill<5) {
		echo "[-] Corrupted block: $i\n";
		$corrupt_blocks++;
	} else {
		$blocks[$i]['hash'] = md5($blocks[$i]['clear_text']);
	}
	//break;
}

echo "[i] Corrupted blocks: $corrupt_blocks\n";
//var_dump($blocks);
	
if(save_json($db_file,$blocks)) echo "[+] Saved\n";

?>