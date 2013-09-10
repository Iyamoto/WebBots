<?php
//Init
$db_dir ='..'.DIRECTORY_SEPARATOR.'db';
$taxonomy_file = $db_dir.DIRECTORY_SEPARATOR.'taxonomy.csv';
//Load dictionary from the file
//$taxonomy = load_taxonomy(taxonomy_file);
$taxonomy['Спиннинги'][]='спиннинг';
$taxonomy['Спиннинги'][]='спининг';
$taxonomy['Блесны'][]='блесн';
$taxonomy['Блесны'][]='воблер';
$taxonomy['Блесны'][]='джиг';
$taxonomy['Рюкзаки'][]='рюкзак';
$taxonomy['Катушки'][]='катушк';
$taxonomy['Ножи'][]='нож';
$taxonomy['Ботинки'][]='ботин';
$taxonomy['Ботинки'][]='берц';
$taxonomy['Куртки'][]='куртк';
$taxonomy['Куртки'][]='ветровк';
?>
