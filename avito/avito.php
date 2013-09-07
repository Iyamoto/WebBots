<?php
require_once '..\libs\LIB_parse.php';
require_once '..\libs\LIB_http.php';
require_once '..\libs\simple_html_dom.php';
echo "\n[+]Started\n";

$debug_file = 'debug.html';
$url = "http://www.avito.ru/sankt-peterburg/ohota_i_rybalka?metro_id=170&user=1&s=1";
$in = http_get_debug($url,$debug_file);
$tidy = tidy_html($in);

$div_marks[] = 'img';
$div_marks[] = 'руб';
$good_divs = get_divs($tidy,$div_marks);

$price_marks[] = 'руб.';
$price_marks[] = 'рублей';


for($i=0;$i<count($good_divs);$i++){
	$data[$i]['imgs']= get_imgs($good_divs[$i]);
	$data[$i]['links']= get_links($good_divs[$i]);
	$data[$i]['raw_text']= strip_tags($good_divs[$i]);
	$data[$i]['clear_text']= clear_text($data[$i]['raw_text']);
	$data[$i]['price']= get_price($data[$i]['clear_text']);
	var_dump($good_divs[$i]);
	break;
}
var_dump($data);

//get type of item (taxonomy)
//get price
//get description
//design changes checker


function get_price($str){
	global $price_marks;
	foreach($price_marks as $mark){
		if(stristr($str, $mark)) {
			$pattern = '|(\d+)[^\d]*'.$mark.'|';
			$r = preg_match($pattern, $str,$m);
			if($r) return $m[1];
		}
	}
	return false;
}

function clear_text($str){
	$str = preg_replace('|(\d+)&nbsp;(\d+)|',"$1$2",$str);//avito prices
	$str = preg_replace('|&.*;|U',' ',$str);
	$str = preg_replace('|\x20+|',' ',$str);
	$str = trim($str);
	return $str;
}

function get_links($str){
	$links = parse_array($str, '<a href="', '"', 1);
	$uniq_links = array_unique($links);
	return $uniq_links;
}

/*
function get_imgs_dom($str){
	$html = str_get_html($str);
	foreach($html->find('img') as $element) {
		$imgs[] = $element;// -> add something here 
	}
	$html->clear();
	return $imgs;
}*/

function get_imgs($str){
	$imgs = parse_array($str, '<img', '/>');
	foreach($imgs as $img){
		$img_links[] = get_attribute($img, 'src');
	}
	return $img_links;
}

function get_divs($str,$marks){
	$html = str_get_html($str);
	$i=0;
	$sum=0;
	foreach($html->find('div') as $element) {
		$div = $element->innertext;
		$m = sizeof($marks);
		$c=0;
		foreach($marks as $mark){
			if(stristr($div, $mark)) $c++;
		}
		if ($c == $m) {
			$divs[$i]['html'] = $div;
			$divs[$i]['size'] = strlen($div);
			$sum+= $divs[$i]['size'];
			$i++;		
		}
	}
	$html->clear();
	
	$average = round($sum/$i);
	echo "[+]Sum $sum\n";
	echo "[+]Average $average\n";
	echo "[+]Found $i div blocks\n";

	$g=0;
	foreach($divs as $n=>$div){
		if($div['size']<$average) {
			$good_divs[] = $div['html'];
			$g++;
		}	
	}
	echo "[+]Found $g good div blocks\n";
	return $good_divs;
}

?>