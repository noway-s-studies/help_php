<?php
error_reporting(E_ALL ^ E_NOTICE);
define('IN_SCRIPT',1);
require 'settings.php';
$id = $_GET['id'];
if (empty($id) || preg_match("/\D/",$id)) {die('Invalid ID, numbers (0-9) only!');}
$lines = file($settings['logfile']);
$found = 0;
$i = 0;
foreach ($lines as $thisline) {
    if (strpos($thisline, $id.'%%') === 0) {
        $thisline = trim($thisline);
        list($id,$added,$url,$count,$name) = explode('%%',$thisline);
        $count = $count + 1;
        $lines[$i]=$id.'%%'.$added.'%%'.$url.'%%'.$count.'%%'.$name."\r\n";
        $found=1;
        break;
    }
    $i++;
}
if ($found != 1) {die('This ID doesn\'t exist!');}

if ($settings['count_unique']==0 || $_COOKIE['ccount_unique']!=$id) {
    $content = implode('', $lines);
    $fp = fopen($settings['logfile'],'w') or die('Can\'t write to log file! Please Change the file permissions (CHMOD to 666 on UNIX machines!)');
    flock($fp, LOCK_EX);
    fputs($fp,$content);
    flock($fp, LOCK_UN);
    fclose($fp);
    header('P3P: CP="NOI NID"');
    setcookie('ccount_unique', $id, time()+60*60*$settings['unique_hours']);
}
Header('Location: '.$url);
exit();
?>
