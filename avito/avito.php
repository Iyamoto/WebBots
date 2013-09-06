<?php
require_once '..\libs\LIB_parse.php';
require_once '..\libs\LIB_http.php';
require_once '..\libs\simple_html_dom.php';
echo "\n[+]Started\n";

$debug_file = 'debug.html';
$url = "http://www.avito.ru/sankt-peterburg/ohota_i_rybalka?metro_id=170&user=1&s=1";
$in = http_get_debug($url,$debug_file);
$tidy = tidy_html($in);
$html = str_get_html($tidy);

$mark1 = 'img';
$mark2 = 'title';
$mark3 = 'руб';
$i=0;
$sum=0;
foreach($html->find('div') as $element) {
    $div = $element->innertext;
	if(stristr($div, $mark1) and stristr($div, $mark2) and stristr($div, $mark3)) {
		$divs[$i]['html'] = $div;
		$divs[$i]['size'] = strlen($div);
		$sum+= $divs[$i]['size'];
		$i++;
	}
}
$average = round($sum/$i);
echo "[+]Sum $sum\n";
echo "[+]Average $average\n";
echo "[+]Found $i div blocks\n";
$html->clear();
$g=0;
foreach($divs as $n=>$el){
	if($el['size']<$average) {
		$good_div[] = $el['html'];
		$g++;
	}	
}
echo "[+]Found $g good div blocks\n";

?>