<?php
/*
Avito.ru grabber and parser
*/
require_once '..\libs\web_bots.php';
echo "\n[+] Started\n";

$debug_file = 'debug.html';
$url = "http://www.avito.ru/sankt-peterburg/ohota_i_rybalka?metro_id=170&user=1&s=1";
$in = http_get_debug($url,$debug_file);
$tidy = tidy_html($in);

$div_marks[] = 'img';
$div_marks[] = 'руб';
$good_divs = get_divs($tidy,$div_marks);

for($i=0;$i<count($good_divs);$i++){
	$data[$i]['imgs']= get_imgs($good_divs[$i]);
	$data[$i]['links']= get_links($good_divs[$i]);
	$data[$i]['raw_text']= strip_tags($good_divs[$i]);
	$data[$i]['clear_text']= clear_text($data[$i]['raw_text']);
	$data[$i]['price']= get_price($data[$i]['clear_text']);
}

if(save_json('avito.gz',$data)) echo "[+] Saved\n";
// Or add to mysql db

?>