<?php
/*
Module 2 - Analysis
*/
require_once '..\libs\web_bots.php';
require_once '..\libs\taxonomy.php';
echo "\n[+] Started\n";

$db_in_file = 'avito.gz';
$db_tagged_file = 'avito-tagged.gz';
$db_stats_file = 'avito-stats.gz';
if(file_exists($db_in_file)){
	$blocks = load_json($db_in_file);
}
$size = count($blocks);
echo "[+] Read $size elements\n";

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
var_dump($stats);

if(save_json($db_tagged_file,$blocks)) echo "[+] Saved tagged blocks\n";
if(save_json($db_stats_file,$stats)) echo "[+] Saved stats\n";


?>