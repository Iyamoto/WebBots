<?php
require_once '..\libs\LIB_parse.php';
require_once '..\libs\LIB_http.php';
require_once '..\libs\LIB_download_images.php';
require_once '..\libs\simple_html_dom.php';

mb_internal_encoding("UTF-8");

$price_marks[] = 'руб.';
$price_marks[] = 'рублей';

//what to do with several prices?
function get_price($str){
	global $price_marks;
	foreach($price_marks as $mark){
		if(mb_stristr($str, $mark)) {
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

function get_links($str, $base_url){
	$links = parse_array($str, '<a href="', '"', 1);
	$uniq_links = array_unique($links);
        for($i=0;$i<sizeof($uniq_links);$i++){
            $url = $uniq_links[$i];
            $uniq_links[$i] = resolve_address($url, $base_url);
        }
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

function get_imgs($str, $base_url){
	$imgs = parse_array($str, '<img', '>');
	foreach($imgs as $img){
                $url = get_attribute($img, 'src');
                $img_links[] = resolve_address($url, $base_url);
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
	echo "[+] Sum $sum\n";
	echo "[+] Average $average\n";
	echo "[+] Found $i div blocks\n";

	$g=0;
	foreach($divs as $n=>$div){
		if($div['size']<$average) {
			$good_divs[] = $div['html'];
			$g++;
		}	
	}
	echo "[+] Found $g good div blocks\n";
	return $good_divs;
}

function save_json($fn,$data){
	$json = json_encode($data);
	$gz = gzcompress($json);
	//var_dump(strlen($json), strlen($gz));
	return file_put_contents($fn, $gz);
}

function load_json($fn){
	$gz = file_get_contents($fn);
	if($gz){
		$json = gzuncompress($gz);
		$data = json_decode($json,true);
		return $data;
	} else {
		echo "[-] Cant load file $fn\n";
		return false;
	}	
}

function read_db_from_file($filename){
    if(file_exists($filename)){
    	$json = load_json($filename);
        if($json) return $json;
        else return false;
    } else {
        echo "[-] $filename not found\n";
        return false;
    }    
}

function load_urls($urls_file){
    $str = file_get_contents($urls_file);
    $urls = explode("\n",$str);
    return $urls;
}

?>