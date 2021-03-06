<?php

/*
 * Module 4 - Reporter
 */
require_once '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'web_bots.php';
echo "\n[+] Started\n";
$exec_time = microtime(true);

$db_dir = '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'db';
$db_global_file = $db_dir . DIRECTORY_SEPARATOR . 'avito-global.gz'; //global data base
$db_stats_file = $db_dir . DIRECTORY_SEPARATOR . 'avito-stats.gz';

//Read stats
$stats = read_db_from_file($db_stats_file);
if ($stats) {
    $stats_size = sizeof($stats);
    echo "[+] Read $stats_size stats blocks\n";
}
else
    exit('Problem with stats');

$global_blocks = read_db_from_file($db_global_file);
if ($global_blocks) { //Global db exists
    $global_size = sizeof($global_blocks);
    echo "[+] Read $global_size global blocks\n";
}
else
    exit('Problem with global blocks');

$interests['Куртки'] = 1000;
$interests['Рюкзаки'] = 1000;
$interests['Блесны'] = 30;

foreach ($stats as $category => $tmp) {
    if (isset($interests[$category]))
        $lvl = $interests[$category];
    else
        $lvl = false;
    $good_blocks[$category] = get_blocks_from_category($category, $stats, $global_blocks, $lvl);
}

$exec_time = round(microtime(true) - $exec_time, 2);
echo "[i] Execution time: $exec_time sec.\n";

function get_blocks_from_category($category, &$stats, &$blocks, $lvl = false) {
    if ($lvl == false) {
        $lvl = $stats[$category]['low_limit'];
    }
    echo "[+] Search level set to $lvl in $category\n";
    for ($i = 0; $i < $stats[$category]['size']; $i++) {
        if ($stats[$category]['prices'][$i] <= $lvl) {
            $good_blocks[] = search_for_block($blocks, $stats[$category]['hashes'][$i]);
        }
    }

    if (isset($good_blocks)) {
        $size = sizeof($good_blocks);
        echo "[+] Found $size blocks in $category\n";
        return $good_blocks;
    } else {
        echo "[-] Found nothing in $category\n";
        return false;
    }
}

//TODO function get_category($str)
?>