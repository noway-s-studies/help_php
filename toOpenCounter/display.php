<?php
error_reporting(E_ALL ^ E_NOTICE);
define('IN_SCRIPT',1);
require 'settings.php';
echo "var ccount_link = new Array();\n";
$lines = file($settings['logfile']);
foreach ($lines as $thisline) {
    $thisline = trim($thisline);
    list($id,$added,$url,$count,$linkname)=explode('%%',$thisline);
    echo "ccount_link[$id]=$count;\n";
}
echo '
	function ccount_display(id) {
		document.write(ccount_link[id]);
	}
';
exit();
?>
