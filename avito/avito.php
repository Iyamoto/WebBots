<?php
require_once '..\libs\LIB_parse.php';
require_once '..\libs\LIB_http.php';
require_once '..\libs\simple_html_dom.php';
echo "\n[+]Started\n";

$debug_file = 'debug.html';
$url = "http://www.avito.ru/sankt-peterburg/ohota_i_rybalka?metro_id=170&user=1&s=1";
$in = http_get_debug($url,$debug_file);
$tidy = tidy_html($in);

$marks[] = 'img';
$marks[] = 'руб';
$good_divs = get_divs($tidy,$marks);

var_dump($good_divs);

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
	unset($html);
	
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