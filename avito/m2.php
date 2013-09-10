<?php

/*
 * Module 2 - Analysis
 */
require_once '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'web_bots.php';
require_once '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'taxonomy.php';
echo "\n[+] Started\n";

$db_dir = '..' . DIRECTORY_SEPARATOR . 'db';
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
    $tagged_blocks = tagged($uniq_blocks); //Tag uniq blocks
    unset($uniq_blocks);
    unset($new_blocks);
    //Insert new tagged blocks into global db
    $global_blocks = insert_to_array($global_blocks, $tagged_blocks);
    unset($tagged_blocks);
} else { //Global db is empty
    $global_blocks = tagged($new_blocks); //Tag uniq blocks
    unset($new_blocks);
}

$global_size = sizeof($global_blocks);
echo "[+] Global db size: $global_size\n";

//Parse global to clusters
//Why do it every run for all global blocks? TODO
$clusters = form_clusters($global_blocks);
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

if (save_json($db_global_file, $global_blocks))
    echo "[+] Saved global db file\n";
if (save_json($db_stats_file, $stats))
    echo "[+] Saved stats file\n";

function form_clusters($blocks) {
    foreach ($blocks as $block) {
        foreach ($block['tags'] as $tag) {
            $clusters[$tag][] = $block;
        }
    }
    return $clusters;
}

function tagged($blocks) { //Clustering
    $size = sizeof($blocks);
    global $taxonomy;
    $untagged_blocks_counter = 0;
    for ($i = 0; $i < $size; $i++) {
        $notag = true;
        foreach ($taxonomy as $category => $marks) {
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

function insert_to_array($base_array, $add_array) {
    foreach ($base_array as $array) {
        $add_array[] = $array;
    }
    return $add_array;
}

/*
  Filter Uniqs
  Optimization needed
  Go throw new blocks
  Check hash of new block throw global blocks
  Equal - next new block
  Not equal - next global block
  Not found - uniq +1
 */

function get_uniq_blocks($new_blocks, $global_blocks) {
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