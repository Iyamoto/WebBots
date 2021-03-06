<?php

/*
 * Module 3 - Tagging and Clustering
 */
require_once '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'web_bots.php';
require_once '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'taxonomy.php';
echo "\n[+] Started\n";
$exec_time = microtime(true);

$db_dir = '..' . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR . 'db';
$db_global_file = $db_dir . DIRECTORY_SEPARATOR . 'avito-global.gz'; //global data base
$db_stats_file = $db_dir . DIRECTORY_SEPARATOR . 'avito-stats.gz';
$db_clusters_file = $db_dir . DIRECTORY_SEPARATOR . 'avito-clusters.gz';

//Read global db
$global_blocks = read_db_from_file($db_global_file);
if ($global_blocks) { //Global db exists
    $global_size = sizeof($global_blocks);
    echo "[+] Read $global_size global blocks\n";
    
    tagged($global_blocks); //Tag blocks
    
    //reClustering
    $clusters = form_clusters($global_blocks);
    $clusters_size = sizeof($clusters);
    echo "[+] Formed $clusters_size clusters\n";

    //Statistics
    foreach ($clusters as $category => $tagged_blocks) {
        $stats[$category]['size'] = sizeof($tagged_blocks);
        $stats[$category]['sum'] = 0;
        $SX2 = 0;
        foreach ($tagged_blocks as $tagged_block) {
            $stats[$category]['hashes'][] = $tagged_block['hash']; 
            $stats[$category]['prices'][] = $tagged_block['price'];
            $stats[$category]['sum'] += $tagged_block['price'];
            $SX2 += $tagged_block['price'] * $tagged_block['price'];
        }
        $stats[$category]['average'] = round($stats[$category]['sum'] / $stats[$category]['size']);
        //Sigma (standard_deviation)
        $stats[$category]['standard_deviation'] = round(sqrt(($SX2 / $stats[$category]['size'] - pow($stats[$category]['sum'] / $stats[$category]['size'], 2))));
        // 1 Sigma
        $stats[$category]['low_limit'] = round($stats[$category]['average'] - 1 * $stats[$category]['standard_deviation']);
        $stats[$category]['high_limit'] = round($stats[$category]['average'] + 1 * $stats[$category]['standard_deviation']);
    }
}
else
    exit('Problem with global blocks');

//if (save_json($db_tagged_file, $tagged_blocks))
  //  echo "[+] Saved global db file\n";
if (save_json($db_stats_file, $stats))
    echo "[+] Saved stats file\n";
if (save_json($db_clusters_file, $clusters))
    echo "[+] Saved clusters file\n";

$exec_time = round(microtime(true) - $exec_time,2);
echo "[i] Execution time: $exec_time sec.\n";

function tagged(&$blocks) { //Tagging
    $size = sizeof($blocks);
    global $taxonomy;
    $untagged_blocks_counter = 0;
    for ($i = 0; $i < $size; $i++) {
        $notag = true;
        foreach ($taxonomy as $category => $marks) {
            //unset($blocks[$i]['tags']);
            foreach ($marks as $mark) {
                if (mb_stristr($blocks[$i]['clear_text'], $mark)) {
                    $blocks[$i]['tags'][] = $category;
                    $notag = false;
                    break;
                }
            }
        }
        if ($notag) {
            $blocks[$i]['tags'][] = 'NA';
            $untagged_blocks_counter++;
        }
    }
    echo "[i] Tags not found for $untagged_blocks_counter blocks\n";
    return sizeof($blocks);
}
?>