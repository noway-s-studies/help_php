<?php
/* Az admin jelszava */
$settings['apass']='123456';

/* A click.php elérési útvonala */
$settings['click_url']='http://www.index.hu/click.php';

/* Egyedi? 
/* 1 = Egy gépen többszöri megnyitást is csak egyszer számolja, 
/* 0 = Minden megnyitást számol (ha többször nézi meg a levelet többször számol)   */
$settings['count_unique']=1;

/* Number of hours a visitor is considered as "unique" */
$settings['unique_hours']=24;

/* A "log" fájl neve */
$settings['logfile']='clicks.txt';

/* Utolsó ID sorszáma */
$settings['idfile']='ids.txt';

if (!defined('IN_SCRIPT')) {die('Invalid attempt!');}
$settings['verzija']='1.2';
?>
