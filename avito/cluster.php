<?php

/*
 * Module 4 - ReTagging and ReClustering
 */
require_once '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'web_bots.php';
require_once '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'taxonomy.php';
echo "\n[+] Started\n";

$db_dir = '..' . DIRECTORY_SEPARATOR . 'db';
$db_global_file = $db_dir . DIRECTORY_SEPARATOR . 'avito-global.gz'; //global data base
$db_stats_file = $db_dir . DIRECTORY_SEPARATOR . 'avito-stats.gz';
$db_clusters_file = $db_dir . DIRECTORY_SEPARATOR . 'avito-clusters.gz';

//Read global db
$global_blocks = read_db_from_file($db_global_file);
if ($global_blocks) { //Global db exists
    $global_size = sizeof($global_blocks);
    echo "[+] Read $global_size global blocks\n";
    
    $tagged_blocks = tagged($global_blocks); //reTag blocks
    unset($global_blocks);
    
    //reClustering
    $clusters = form_clusters($tagged_blocks);
    $clusters_size = sizeof($clusters);
    echo "[+] Formed $clusters_size clusters\n";

    //Statistics
    foreach ($clusters as $category => $tagged_blocks) {
        $stats[$category]['size'] = sizeof($tagged_blocks);
        $stats[$category]['sum'] = 0;
        $SX2 = 0;
        foreach ($tagged_blocks as $tagged_block) {
            $stats[$category]['pool'][] = $tagged_block['price']; //add block hash?
            $stats[$category]['sum'] += $tagged_block['price'];
            $SX2 += $tagged_block['price'] * $tagged_block['price'];
        }
        $stats[$category]['average'] = round($stats[$category]['sum'] / $stats[$category]['size']);
        //Sigma (standard_deviation)
        $stats[$category]['standard_deviation'] = round(sqrt(($SX2 / $stats[$category]['size'] - pow($stats[$category]['sum'] / $stats[$category]['size'], 2))));
        // 3 Sigma
        $stats[$category]['low_limit'] = round($stats[$category]['average'] - 3 * $stats[$category]['standard_deviation']);
        $stats[$category]['high_limit'] = round($stats[$category]['average'] + 3 * $stats[$category]['standard_deviation']);
    }
}
else
    exit('Problem with global blocks');

//if (save_json($db_global_file, $tagged_blocks))
  //  echo "[+] Saved global db file\n";
if (save_json($db_stats_file, $stats))
    echo "[+] Saved stats file\n";
if (save_json($db_clusters_file, $clusters))
    echo "[+] Saved clusters file\n";

function tagged($blocks) { //Tagging
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
    echo "[i] Blocks without tags: $untagged_blocks_counter\n";
    return $blocks;
}
?>