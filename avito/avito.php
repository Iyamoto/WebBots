<?php
/*
Avito.ru grabber and parser
*/
require_once '..\libs\web_bots.php';
echo "\n[+] Started\n";

$db_file = 'avito.gz';
$debug_file = 'debug.html';
$url = "http://www.avito.ru/sankt-peterburg/ohota_i_rybalka?metro_id=170&user=1&s=1";
$in = http_get_debug($url,$debug_file);
if(!$in) {
	echo "[-] Cant load html\n";
	exit;
}	
$tidy = tidy_html($in);
//add code page check and convert if needed


$div_marks[] = 'img';
$div_marks[] = 'руб';
$good_divs = get_divs($tidy,$div_marks);
$corrupt_blocks = 0;

for($i=0;$i<count($good_divs);$i++){
	$fill = 0;
	$data[$i]['imgs']= get_imgs($good_divs[$i]);
	if(sizeof($data[$i]['imgs'])>0) $fill++;
	$data[$i]['links']= get_links($good_divs[$i]);
	if(sizeof($data[$i]['imgs'])>0) $fill++;
	$data[$i]['raw_text']= strip_tags($good_divs[$i]);//Should I keep a raw text?
	if(strlen($data[$i]['raw_text'])>0) $fill++;
	$data[$i]['clear_text']= clear_text($data[$i]['raw_text']);
	if(strlen($data[$i]['clear_text'])>0) $fill++;
	$data[$i]['price']= get_price($data[$i]['clear_text']);
	if(strlen($data[$i]['price'])>0) $fill++;
	if($fill<5) {
		echo "[-] Corrupted block: $i\n";
		$corrupt_blocks++;
	} else {
		$data[$i]['hash'] = md5($data[$i]['clear_text']);
	}
	//break;
}

echo "[i] Corrupted blocks: $corrupt_blocks\n";
var_dump($data);
	
if(save_json($db_file,$data)) echo "[+] Saved\n";
// Or add to mysql db

?>