<?php

/*
 * Module 2 - Filter
 * Filter to global DB
 */
require_once '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'web_bots.php';
require_once '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'taxonomy.php';
echo "\n[+] Started\n";
$exec_time = microtime(true);

$db_dir = '..' . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR . 'db';
$db_in_file = $db_dir . DIRECTORY_SEPARATOR . 'avito.gz';
$db_global_file = $db_dir . DIRECTORY_SEPARATOR . 'avito-global.gz'; //global data base
$db_stats_file = $db_dir . DIRECTORY_SEPARATOR . 'avito-stats.gz';

//Read new blocks
$new_blocks = read_db_from_file($db_in_file);
if ($new_blocks) {
    $new_size = sizeof($new_blocks);
    echo "[+] Read $new_size new blocks\n";
}
else
    exit('Problem with new blocks');

//Read global db
$global_blocks = read_db_from_file($db_global_file);
if ($global_blocks) { //Global db exists
    $global_size = sizeof($global_blocks);
    echo "[+] Read $global_size global blocks\n";

    //Filter Uniqs
    $uniq_blocks = get_uniq_blocks($new_blocks, $global_blocks);
    if (!$uniq_blocks)
        exit('[-] Exit: Zero uniq blocks found');
    unset($new_blocks);
    //Add global blocks to uniq blocks, new blocks stay upper
    add_to_array($global_blocks, $uniq_blocks);
    $global_blocks = &$uniq_blocks;
} else { //Global db is empty
    $global_blocks = &$new_blocks;
}

$global_size = sizeof($global_blocks);
echo "[+] Global db size: $global_size\n";

if (save_json($db_global_file, $global_blocks))
    echo "[+] Saved global db file\n";

$exec_time = round(microtime(true) - $exec_time,2);
echo "[i] Execution time: $exec_time sec.\n";

/*
  Filter Uniqs
  Optimization needed
  Go throw new blocks
  Check hash of new block throw global blocks
  Equal - next new block
  Not equal - next global block
  Not found - uniq +1
 */

function get_uniq_blocks(&$new_blocks, &$global_blocks) {
    $uniq_blocks_counter = 0;
    $global_size = sizeof($global_blocks);
    foreach ($new_blocks as $new_block) {
        $i = 0;
        foreach ($global_blocks as $global_block) {
            if ($new_block['hash'] == $global_block['hash'])
                break;
            $i++;
        }
        if ($i == $global_size) {
            $uniq_blocks_counter++;
            $uniq_blocks[] = $new_block; //Order of global blocks?
        }
    }
    echo "[+] Uniq Blocks found: $uniq_blocks_counter\n";
    if ($uniq_blocks_counter > 0)
        return $uniq_blocks;
    else
        return false;
}

?>