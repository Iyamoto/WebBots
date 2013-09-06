<?php
require_once 'R:\0code\Lib\LIB_parse.php';
require_once 'R:\0code\Lib\LIB_http.php';
echo "\n[+]Started\n";

$debug_file = 'F:\tmp\avito\debug.html';
$url = "http://www.avito.ru/sankt-peterburg/ohota_i_rybalka?metro_id=170&user=1&s=1";
$in = http_get_debug($url,$debug_file);

/*
$mark1 = 'photo';
$mark2 = 'title';
$mark3 = 'description';
foreach($divs->children() as $tmp){
	var_dump($tmp);
	$div = $tmp->plaintext;
	//if(stristr($div, $mark1) and stristr($div, $mark2) and stristr($div, $mark3)) {
	if(stristr($div, $mark1)){
		var_dump($div);
	}
}
*/

function get_div($str,$needle='<div'){
	if(stristr($str, $div)){
		//есть еще дивы, идем в рекурсию
		get_div($str1,$needle);
	} else {
		//больше дивов нет, возвращаем
		return $something;
	}
	//проверка на конец строки
	//вычисление позиций
}

?>