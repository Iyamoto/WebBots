<?php
/*
 * Module 1 - Collector
 * Avito.ru grabber and parser
 */
require_once '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'web_bots.php';
echo "\n[+] Started\n";

//Init
$db_dir = '..' . DIRECTORY_SEPARATOR . 'db';
$tmp_dir = '..' . DIRECTORY_SEPARATOR . 'tmp';
$db_file = $db_dir . DIRECTORY_SEPARATOR . 'avito.gz'; //local, one run data base
$urls_file = $db_dir . DIRECTORY_SEPARATOR . 'urls.txt';
if (!is_dir($db_dir))
    mkdir($db_dir);
if (!is_dir($tmp_dir))
    mkdir($tmp_dir);
$ref = 'http://www.avito.ru';
$div_marks[] = 'img';
$div_marks[] = 'руб';

$urls = load_urls($urls_file);//Load urls
shuffle($urls);
if (!$urls)
    exit('[-] Cant load urls');
foreach ($urls as $url) {
    $url = trim($url);
    echo "[+] Processing url: $url\n";
    $hash = md5($url);
    $debug_file = $tmp_dir . DIRECTORY_SEPARATOR . $hash . '.html'; //cache
    $in = http_get_debug($url, $debug_file, $ref);
    if (!$in)
        exit('[-] Cant load html');
    $ref = $url;

    //Base Url 
    $url = $in['STATUS']['url'];
    $base_url = get_base_page_address($url);
    echo "[+] Base url: $base_url\n";

    $tidy = tidy_html($in['FILE']);

    //Marks for blocks
    $html_blocks = get_divs($tidy, $div_marks); //Get blocks from html
    $corrupt_blocks = 0;

    //Blocks to elements
    for ($i = 0; $i < count($html_blocks); $i++) {
        $fill = 0;
        $blocks[$i]['imgs'] = get_imgs($html_blocks[$i], $base_url);
        if (sizeof($blocks[$i]['imgs']) > 0)
            $fill++;
        $blocks[$i]['links'] = get_links($html_blocks[$i], $base_url);
        if (sizeof($blocks[$i]['imgs']) > 0)
            $fill++;
        $blocks[$i]['raw_text'] = strip_tags($html_blocks[$i]); //Should I keep a raw text? @todo
        if (strlen($blocks[$i]['raw_text']) > 0)
            $fill++;
        $blocks[$i]['clear_text'] = clear_text($blocks[$i]['raw_text']);
        if (strlen($blocks[$i]['clear_text']) > 0)
            $fill++;
        $blocks[$i]['price'] = get_price($blocks[$i]['clear_text']);
        if (strlen($blocks[$i]['price']) > 0)
            $fill++;
        if ($fill < 5) {
            echo "[-] Corrupted block: $i\n";
            $corrupt_blocks++;
            $blocks[$i]['hash'] = md5($blocks[$i]['clear_text']);//FIXME 
        } else {
            $blocks[$i]['hash'] = md5($blocks[$i]['clear_text']);
        }
        $global_blocks[] = $blocks[$i];
        //break;
    }

    echo "[i] Corrupted blocks: $corrupt_blocks\n";

    unset($blocks);
} //end of main cicle
//var_dump($global_blocks);
$global_size = sizeof($global_blocks);
echo "[+] $global_size blocks found\n";
if (save_json($db_file, $global_blocks))
    echo "[+] Saved\n";
?>