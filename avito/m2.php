<?php
/*
Module 2 - Analysis
*/
require_once '..\libs\web_bots.php';
require_once '..\libs\taxonomy.php';
echo "\n[+] Started\n";

$db_in_file = '..\db\avito.gz';
$db_tagged_file = '..\db\avito-tagged.gz';//global data base
$db_stats_file = '..\db\avito-stats.gz';
if(file_exists($db_in_file)){
	$new_blocks = load_json($db_in_file);
}
$new_size = count($new_blocks);
echo "[+] Read $new_size new blocks\n";

if(file_exists($db_tagged_file)){
	$global_blocks = load_json($db_tagged_file);
}
$global_size = count($global_blocks);
echo "[+] Read $global_size global blocks\n";

//Filter Uniqs
//Optimization needed
/*
Go throw new blocks
Check hash of new block throw global blocks
Equal - next new block
Not equal - next global block
Not found - uniq +1
*/
$uniq_blocks = 0;
foreach($new_blocks as $new_block){
	$i=0;
	foreach($global_blocks as $global_block){
		if($new_block['hash'] == $global_block['hash']) break;
		$i++;
	}
	if($i==$global_size) {
		$uniq_blocks++;
		$global_block[] = $new_block;//Order of global blocks?
	}	
}
echo "[+] Uniq Blocks found: $uniq_blocks\n";

$blocks = $new_blocks;
$size = $new_size;

//Clustering
$untagged_blocks = 0;
for($i=0;$i<$size;$i++){
	$notag = true;
	foreach($taxonomy as $category=>$marks){
		foreach($marks as $mark){
			if(mb_stristr($blocks[$i]['clear_text'], $mark)) { 
				$blocks[$i]['tags'][] = $category;
				$clusters[$category][]=$blocks[$i];
				$notag = false;
				break;
			}
		}
	}
	if($notag) {
		$blocks[$i]['tags'][] = 'NA';
		$untagged_blocks++;
	}	
}
echo "[i] Blocks without tags: $untagged_blocks\n";

//Statistics
foreach($clusters as $category=>$tagged_blocks){
	$stats[$category]['size'] = sizeof($tagged_blocks);
	$stats[$category]['sum'] = 0;
	$SX2=0;
	foreach($tagged_blocks as $tagged_block){
		$stats[$category]['pool'][] = $tagged_block['price'];
		$stats[$category]['sum'] += $tagged_block['price'];
		$SX2 += $tagged_block['price']*$tagged_block['price'];
	}
	$stats[$category]['average'] = round($stats[$category]['sum']/$stats[$category]['size']);
	//Sigma (standard_deviation)
	$stats[$category]['standard_deviation'] = round(sqrt(($SX2/$stats[$category]['size']-pow($stats[$category]['sum']/$stats[$category]['size'],2))));
	// 3 Sigma
	$stats[$category]['low_limit'] = round($stats[$category]['average']-3*$stats[$category]['standard_deviation']);
	$stats[$category]['high_limit'] = round($stats[$category]['average']+3*$stats[$category]['standard_deviation']);
}
//var_dump($stats);

if(save_json($db_tagged_file,$blocks)) echo "[+] Saved tagged blocks\n";
if(save_json($db_stats_file,$stats)) echo "[+] Saved stats\n";


?>