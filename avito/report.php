<?php

/*
 * Module 4 - Reporter
 */
require_once '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'web_bots.php';
echo "\n[+] Started\n";

$db_dir = '..' . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR . 'db';
$db_global_file = $db_dir . DIRECTORY_SEPARATOR . 'avito-global.gz'; //global data base
$db_stats_file = $db_dir . DIRECTORY_SEPARATOR . 'avito-stats.gz';

//Read stats
$stats = read_db_from_file($db_stats_file);
if ($stats) {
    $stats_size = sizeof($stats);
    echo "[+] Read $stats_size stats\n";
}
else
    exit('Problem with stats');

var_dump($stats);

?>