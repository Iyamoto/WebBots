<?php
/*
Module 2 - Analysis
*/
require_once '..\libs\web_bots.php';
require_once '..\libs\taxonomy.php';
echo "\n[+] Started\n";

$db_in_file = 'avito.gz';
$db_out_file = 'analysis.gz';
if(file_exists($db_in_file)){
	$data = load_json($db_in_file);
}
$size = count($data);
echo "[+] Read $size elements\n";

$untagged_blocks = 0;
for($i=0;$i<$size;$i++){
	$notag = true;
	foreach($taxonomy as $category=>$marks){
		foreach($marks as $mark){
			if(mb_stristr($data[$i]['clear_text'], $mark)) { 
				$data[$i]['tags'][] = $category;
				$notag = false;
				break;
			}
		}
	}
	if($notag) {
		$data[$i]['tags'][] = 'NA';
		$untagged_blocks++;
	}	
}
echo "[i] Blocks without tags: $untagged_blocks\n";

if(save_json($db_out_file,$data)) echo "[+] Saved\n";


?>