<?php
//Init
$db_dir ='..'.DIRECTORY_SEPARATOR.'db';
$taxonomy_file = $db_dir.DIRECTORY_SEPARATOR.'taxonomy.csv';

//Load dictionary from the file
$data = csv2array($taxonomy_file);
if(!$data) exit('Cant read taxonomy');
$taxonomy = load_taxonomy($data);

function load_taxonomy($array){
    foreach($array as $elements){
        $category = $elements[0];
        for($i=1;$i<sizeof($elements);$i++){
            $data[$category][]=$elements[$i];
        }
    }
    return $data;
}

?>