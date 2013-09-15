<?php

/*
 * Module 4 - Reporter
 */
require_once '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'web_bots.php';
echo "\n[+] Started\n";
$exec_time = microtime(true);

$db_dir = '..' . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR . 'db';
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
} else 
    exit('Problem with global blocks');

$needle = '1000';
//$needle = 'b1bd9c575e81451dd08f11bbe8c79938';
$block = search_for_block($global_blocks,$needle);
var_dump($stats);

$exec_time = round(microtime(true) - $exec_time,2);
echo "[i] Execution time: $exec_time sec.\n";

?>