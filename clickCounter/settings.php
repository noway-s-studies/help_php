<?php
/* Az admin jelszava */
$settings['apass']='123456';

/* A click.php el�r�si �tvonala */
$settings['click_url']='http://www.index.hu/click.php';

/* Egyedi? 
/* 1 = Egy g�pen t�bbsz�ri megnyit�st is csak egyszer sz�molja, 
/* 0 = Minden megnyit�st sz�mol (ha t�bbsz�r n�zi meg a levelet t�bbsz�r sz�mol)   */
$settings['count_unique']=1;

/* Number of hours a visitor is considered as "unique" */
$settings['unique_hours']=24;

/* A "log" f�jl neve */
$settings['logfile']='clicks.txt';

/* Utols� ID sorsz�ma */
$settings['idfile']='ids.txt';

if (!defined('IN_SCRIPT')) {die('Invalid attempt!');}
$settings['verzija']='1.2';
?>
