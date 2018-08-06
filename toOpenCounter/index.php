<?php
error_reporting(E_ALL ^ E_NOTICE);
define('IN_SCRIPT',1);
require 'settings.php';
session_name('CCOUNT');
if (!session_start()) {
    error('Cannot start a new PHP session. Please contact server administrator or webmaster!');
}
if (empty($_REQUEST['action'])) {
    if (isset($_SESSION['logged']) && $_SESSION['logged'] == "Y") {
        pj_session_regenerate_id();
        mainpage();
    } else {
        login();
    }
} else {
    $action=htmlspecialchars($_REQUEST['action']);
}
if ($action == 'login') {
    checkpassword();
    $_SESSION['logged']='Y';
    pj_session_regenerate_id();
    mainpage();
} elseif ($action == 'save') {
    checklogin();
    savelink();
} elseif ($action == 'edit') {
    checklogin();
    editlink();
} elseif ($action == 'backup') {
    checklogin();
    sendbackup();
}  elseif ($action == 'remove') {
    checklogin();
    removelink();
} elseif ($action == 'reset') {
    checklogin();
    resetlink();
} elseif ($action == 'add') {
    checklogin();
    add();
} elseif ($action == 'restore') {
    checklogin();
    restore();
} elseif ($action == 'logout') {
    logout();
} else {
    login();
}
exit();
function savelink() {
global $settings;
$id=checkid();
$new_url=checkurl($_POST['url']);
$new_name = input($_POST['name']);
if (strlen($name)>40) {
    error('Your link name is too long! Please limit your name to maximum 40 chars!');
}
$new_count = input($_POST['count']);
if (preg_match("/\D/",$new_count)) {
    $new_count = 0;
}
$found=0;
$i=0;
$lines = file($settings['logfile']);
foreach ($lines as $thisline) {
    if (strpos($thisline, $id.'%%') === 0) {
        $thisline = trim($thisline);
        list($id,$added,$url,$count,$name) = explode('%%',$thisline);
        $lines[$i]=$id.'%%'.$added.'%%'.$new_url.'%%'.$new_count.'%%'.$new_name."\r\n";
        $found=1;
        break;
    }
    $i++;
}
if ($found != 1) {error('This ID doesn\'t exist!');}
$content = implode('', $lines);
$fp = @fopen($settings['logfile'],'w') or error('Can\'t write to log file! Please Change the file permissions (CHMOD to 666 on UNIX machines!)');
flock($fp, LOCK_EX);
fputs($fp,$content);
flock($fp, LOCK_UN);
fclose($fp);
mainpage('Changes to link ID '.$id.' have been saved!');
}
function editlink() {
global $settings;
$id=checkid();
$found=0;
$i=0;
$lines = file($settings['logfile']);
foreach ($lines as $thisline) {
    if (strpos($thisline, $id.'%%') === 0) {
        $thisline = trim($thisline);
        list($id,$added,$url,$count,$name) = explode('%%',$thisline);
        $found=1;
        break;
    }
    $i++;
}
if ($found != 1) {
	error('This ID doesn\'t exist!');
	}
printHeader();
?>
<tr>
<td class="vmes">
<form action="index.php" method="POST">
	<p><b>Link szerkesztése (ID: <?php echo $id; ?>)</b></p>
    <table border="0">
        <tr><td>Letöltések száma:<sup>1</sup></td>
            <td><input type="text" name="count" value="<?php echo $count; ?>" size="6"></td>
        </tr>
        <tr><td>Link megnevezése:<sup>2</sup></td>
            <td><input type="text" name="name" value="<?php echo $name; ?>" size="40" maxlength="40"></td>
        </tr>
        <tr><td><b>Link URL:</b><sup>3</sup></td>
            <td><input type="text" name="url" value="<?php echo $url; ?>" size="50"></td>
        </tr>
    </table><br>
    <table border="0">
        <tr><td valign="top"><sup>1</sup></td>
            <td>Itt adható meg, a számláló induló értékét (alapértelmezett: 0).</td>
        </tr>
        <tr><td valign="top"><sup>2</sup></td>
            <td>A fentebbi statisztikai listában szereplõ megnevezést adhatod meg itt.</td>
        </tr>
        <tr><td valign="top"><sup>3</sup></td>
            <td>Használandó link URL-je. </td>
        </tr>
    </table>
    <hr>
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr><td width="33%" align="center"><input type="hidden" name="id" value="<?php echo $id; ?>"><input type="hidden" name="action" value="save"><input type="submit" value=" Változások mentése "></td></td>
            <td width="33%" align="center"><a href="index.php?<?php echo mt_rand(1000,9999); ?>">Változtatás nélkül kilép</a></td>
            <td width="33%" align="center"><a href="index.php?action=logout">Kijelentkezés</a></td></td>
        </tr>
    </table>
</form>
</td>
</tr>
<?php
printFooter();
exit();
}
function sendbackup() {
    global $settings;

    $name = 'ccount_backup_'.date(dmY).'.txt';
    header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . filesize($settings['logfile']));
    header('Content-Disposition: attachment; filename=' . $name);
    readfile($settings['logfile']);
    exit();
}
function restore() {
global $settings;
$ext = strtolower(substr(strrchr($_FILES['backup']['name'], '.'), 1));
if ($ext != 'txt') {
    error('This doesn\'t seem to be the right backup file. CCount backup file should be named <b>'.$settings['logfile'].'</b>!');
}

if (!move_uploaded_file($_FILES['backup']['tmp_name'], $settings['logfile'])) {
    error('There has been an error uploading the backup file! Please make
    sure your log file ('.$settings['logfile'].') is writable by
    PHP scripts. On UNIX machines CHMOD it to 666 (rw-rw-rw-)!');
}
printHeader();
?>
<tr>
<td class="vmes"><p>&nbsp;</p>
<div align="center"><center>
<table width="400" cellpadding="3"> <tr>
<td align="center" class="head">Backup restored: <?php echo $_FILES['backup']['name']; ?></td>
</tr>
<tr>
<td class="dol">
<form>
<p>&nbsp;</p>
<p align="center"><b>Backup successfully restored!</b></p>
<p>Your backup has been successfully restored. If this was a valid CCount backup file your counter should work OK now!</p>
<p>&nbsp;</p>
<p align="center">
<a href="index.php">Click to continue</a></p>
<p>&nbsp;</p>
</td>
</tr> </table>
</div></center>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
</td>
</tr>
<?php
printFooter();
exit();
}
function add() {
global $settings;
$url=checkurl($_POST['url']);
$name = input($_POST['name']);
if (strlen($name)>40) {
    error('A név nem lehet hosszabb 40 karakternél!');
}
$start_from = input($_POST['count']);
if (preg_match("/\D/",$start_from)) {
    $start_from = 0;
}
$previd = file_get_contents($settings['idfile']);
$previd = trim($previd);
$previd++;
$fp = @fopen($settings['idfile'],'w') or error('Can\'t write to the IDs file ('.$settings['idfile'].')! Make sure PHP scripts have permission to write to this file (CHMOD it to 666 on LINUX machines!)');
flock($fp, LOCK_EX);
fputs($fp,$previd);
flock($fp, LOCK_UN);
fclose($fp);
$addline = $previd . '%%' . date('Y/m/d') . '%%' . $url . '%%'. $start_from . '%%' . $name . "\r\n";
$fp = @fopen($settings['logfile'],'a') or error('Can\'t write to the log file ('.$settings['logfile'].')! Make sure PHP scripts have permission to write to this file (CHMOD it to 666 on LINUX machines!)');
flock($fp, LOCK_EX);
fputs($fp,$addline);
flock($fp, LOCK_UN);
fclose($fp);
printHeader();
?>
    <tr>
    	<td class="vmes"><p>&nbsp;</p>
        <div align="center"><center>
<table width="600" cellpadding="3">
	<tr>
        <td align="center" class="head">Link létrehozva</td>
    </tr>
    <tr>
    	<td class="dol">
            <form>
            <p>&nbsp;</p>
            <p align="center"><b>Az új link létrehozva!</b></p>
            <p><b>ID: </b><?php echo($previd); ?></p>
            <p><b>Használható URL (<a href="<?php echo("$settings[click_url]?id=$previd"); ?>" target="_blank">új ablakban</a>): </b><br><font color="#FF0000"><?php echo("$settings[click_url]?id=$previd"); ?></font></p>
            <p><b>Hivatkozási URL (<a href="<?php echo($url); ?>" target="_blank">új ablakban</a>): </b><br><font color="#FF0000"><?php echo($url); ?></font></p>
            <p align="center"><a href="index.php">Vissza</a></p>
            <p>&nbsp;</p>
         </td>
	</tr>
  </table>
</div></center>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
</td>
</tr>
<?php
printFooter();
exit();
}
function resetlink() {
global $settings;
$id=checkid();
$found=0;
$i=0;
$lines = file($settings['logfile']);
foreach ($lines as $thisline) {
    if (strpos($thisline, $id.'%%') === 0) {
        $thisline = trim($thisline);
        list($id,$added,$url,$count,$name) = explode('%%',$thisline);
        $lines[$i]=$id.'%%'.$added.'%%'.$url.'%%0%%'.$name."\r\n";
        $found=1;
        break;
    }
    $i++;
}
if ($found != 1) {error('This ID doesn\'t exist!');}
$content = implode('', $lines);
$fp = @fopen($settings['logfile'],'w') or error('Can\'t write to log file! Please Change the file permissions (CHMOD to 666 on UNIX machines!)');
flock($fp, LOCK_EX);
fputs($fp,$content);
flock($fp, LOCK_UN);
fclose($fp);
mainpage('A linkhez tartozó letöltések száma nullázva! <br>ID: '.$id.' Név:'.$name.'');
}
function removelink() {
global $settings;
$id=checkid();
$found=0;
$i=0;
$lines = file($settings['logfile']);
foreach ($lines as $thisline) {
    if (strpos($thisline, $id.'%%') === 0) {
        unset($lines[$i]);
        $found=1;
        break;
    }
    $i++;
}
if ($found != 1) {error('This ID doesn\'t exist!');}

$content = implode('', $lines);
$fp = @fopen($settings['logfile'],'w') or error('Can\'t write to log file! Please Change the file permissions (CHMOD to 666 on UNIX machines!)');
flock($fp, LOCK_EX);
fputs($fp,$content);
flock($fp, LOCK_UN);
fclose($fp);
if ($found != 1) {
    error('This ID doesn\'t exist!');
}

mainpage('A link sikeresen törölve! <br>ID: '.$id.' Név:'.$name.'');
}
function mainpage($notice='') {
global $settings;
printHeader();
?>
<tr>
<td class="vmes">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
    <tr><td width="33%"><a href="#addlink">Új link készítése</a></td>
        <td width="33%" align="center"><a href="index.php?<?php echo mt_rand(1000,9999); ?>">Állapot frissítése</a></td>
        <td width="33%" align="right"><a href="index.php?action=logout">Kijelentkezés</a></td>
    </tr>
</table>
<hr>
<?php
if ($notice) {
    echo '<p align="center"><font color="#FF0000">'.$notice.'</font></p>';
}
?>
<p><b>Linkek letöltési statisztikája</b></p>
<?php
$lines = array();
$totalclicks = '';
$linewidth = '';
$maxclicks = 0;
$maxid = 0;
$noyet = 0;
$lines = file($settings['logfile']);
if (count($lines) == 0) {
    $noyet = 1;
	}
if ($noyet == 1) {
    echo '<p>Not counting any links. Use the form below to add new links to be counted.</p>';
	}
else {
    $i=0;
    foreach ($lines as $thisline) {
        $thisline = trim($thisline);
        list($id,$added,$url,$count,$linkname)=explode('%%',$thisline);
        $totalclicks += $count;
        if($count > $maxclicks) {
            $maxclicks = $count;
            $maxid=$id;
        }
        $i++;
    }
    $average = $totalclicks/$i;
    $average = number_format($average, 1);
    echo '
    <table border="0" cellspacing="0" cellpadding="2">
    	<tr><td>Linkek összesen:</td>
    		<td><b>'.$i.' db</b></td>
    	</tr><tr><td>Összes letöltés:</td>
    		<td><b>'.$totalclicks.' db</b></td>
    	</tr>
    <tr><td>Letöltési átlag:</td>
    	<td><b>'.$average.'</b></td>
    </tr>
    ';
    if ($maxclicks != 0) {
        echo '
        <tr><td>Legtöbb letöltés:</td>
        	<td><b>'.$maxclicks.'</b> (link ID: <b>'.$maxid.'</b>)</td>
        </tr>
        ';
    }
    echo '
		<tr><td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
    </table>';
}
$maxlinewidth = 200;
if ($noyet == 0) {
    echo '
    <table width="100%" border="1" cellpadding="5" cellspacing="1"><tr>
		<td width="70" align="center" valign="center" class="first">&nbsp;</td>
		<td align="center" valign="center" class="first"> <b>ID</b> </td>
		<td width="70" align="center" valign="center" class="second"> <b>Letöltések</b> </td>
		<td width="70" align="center" valign="center" class="first"> <b>Hozzáadva</b> </td>
		<td align="center" valign="center" class="second"> <b>Link / Megnevezés</b> </td>
		<td width="70" valign="center" class="first"> <b>Graph</b> </td>
    </tr>
    ';
    foreach ($lines as $thisline) {
        $thisline = trim($thisline);
        if (strlen($thisline) < 4) {
            continue;
        }
        list($id,$added,$url,$count,$linkname) = explode('%%',$thisline);

        if ($count == 0 || $maxclicks == 0) {
            $linewidth = 1;
        } else {
            $linewidth = round(($count * $maxlinewidth) / $maxclicks);
            if ($linewidth == 0) {
                $linewidth = 1;
            }
        }
        if (empty($linkname))
        {
            if (strlen($url) > 40)
            {
                $linkname = substr($url, 0, 20);
                $linkname .= '...';
                $linkname .= substr($url, -17);
            }
            else
            {
                $linkname=$url;
            }
        }
        echo '
        <tr><td align="center" valign="center" class="first" nowrap>
				<a href="index.php?action=remove&id='.$id.'" onclick="return doconfirm(\'Biztosan TÖRÖLNI szeretnéd a linket? NEM VISSZAVONHATÓ! &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Bárhol is volt használva (pl.:hírlevél) ezután nem jelenik meg a hivatkozott tartalom! &nbsp; &nbsp;  (ID: '.$id.')\');"><img src="images/delete.gif" height="16" border="0" alt="Link törlése" style="vertical-align:text-bottom"></a>
				<a href="index.php?action=reset&id='.$id.'" onclick="return doconfirm(\'Biztosan NULLÁZNI szeretnéd a linkhez tartozó letöltés számlálót? &nbsp; &nbsp; NEM VISSZAVONHATÓ! &nbsp; &nbsp; (ID: '.$id.')\');"><img src="images/reset.gif" height="16" border="0" alt="Letöltés számláló nullázása" style="vertical-align:text-bottom"></a>
				<a href="index.php?action=edit&id='.$id.'"><img src="images/edit.gif" height="16" border="0" alt="Link szerkesztése" style="vertical-align:text-bottom"></a></td>
			<td align="center" valign="center" class="first"> '.$id.' </td>
			<td valign="center" class="second"> <b>'.$count.'</b> </td>
			<td align="center" valign="center" class="first"> '.$added.' </td>
			<td valign="center" class="second"> <a href="'.$url.'" target="_blank" class="link">'.$linkname.'</a> </td>
			<td valign="center" class="first"> <img src="images/line.gif" height="5" width="'.$linewidth.'" border="1" class="line"> </td>
        </tr>
        ';
    } echo '
    </table><BR>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr><td width="33%" align="center"><img src="images/delete.gif" height="14" width="16" border="0" style="vertical-align:text-bottom">: Link törlése</td>
			<td width="33%" align="center"><img src="images/reset.gif" height="14" width="16" border="0" style="vertical-align:text-bottom">: Számláló nullázása</td>
			<td width="33%" align="center"><img src="images/edit.gif" height="14" width="16" border="0" style="vertical-align:text-bottom">: Link szerkesztése</td>
		</tr>
	</table>
	<p><span class="tip">FIGYELEM:</span> Törlés esetén a belinkelt tartalom nem lesz elérhetõ, pl.: hírlevélben nem lesz tátható</p>
    ';
}

?>
<hr>
<form action="index.php" method="POST">
<p><a name="#addlink"></a><b>Új link készítése</b></p>
<table border="0">
	<tr><td>Számláló kezdeti értéke<sup>1</sup>:</td>
		<td><input type="text" name="count" value="0" size="6"></td>
	</tr>
	<tr><td>Link megnevezése<sup>2</sup>:</td>
		<td><input type="text" name="name" size="40" maxlength="40"></td>
	</tr><tr><td><b>Link URL:</b><sup>3</sup></td>
		<td><input type="text" name="url" value="http://" size="50"></td>
	</tr>
	<tr><td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>
<table border="0">
	<tr><td valign="top"><sup>1</sup></td>
		<td>Itt adható meg, a számláló induló értékét (alapértelmezett: 0).</td>
	</tr>
	<tr><td valign="top"><sup>2</sup></td>
		<td>A fentebbi statisztikai listában szereplõ megnevezést adhatod meg itt.</td>
	</tr>
	<tr><td valign="top"><sup>3</sup></td>
		<td>A link URL-je.".</td>
	</tr>
</table>

<p><input type="hidden" name="action" value="add"><input type="submit" value=" Létrehoz "></p>
</form>
<hr>
<p><b>Biztonsági mentés</b></p>
<p>Adatvesztés elkerülése érdekében lehetõség van a letöltési adatok mentésére késõbbi visszaállításhoz.<br>
<a href="index.php?action=backup">Adatfájl letöltése</a></p>
<p><b>Visszaállítás</b></p>
<form action="index.php" method="POST" enctype="multipart/form-data">
<p><input type="file" name="backup" size="30"><input type="hidden" name="action" value="restore"><br>Itt választhatod ki a korábban lementett adatfájlt. A BETÖLTÉS NEM VONHATÓ VISSZA!</p>
<p><input type="submit" value="Biztonsági mentés betöltése"></p>
</form>
<hr>
<p><b>Használat</b></p>
<p>A használandó URL minta: <font color="#FF0000"><?php echo($settings['click_url']); ?>?id=<b>ID</b></font><br>
Helyttesítsd be a használni kívánt <b>ID</b>-t, pl.: <font color="#FF0000"><?php echo($settings['click_url']); ?>?id=13</font></p>
</td>
</tr>
<?php
printFooter();
exit();
}
function checkurl($url) {
    if (empty($url) || $url == 'http://' || $url == 'https://') {
        error('Please enter URL of the link you wish to add!');
    }
    if (strpos($url, '%%') !== false) {
        error('You cannot use %% in URLs!');
    }
    return $url;
}
function checkid() {
    $id = $_REQUEST['id'] or error('Please enter a link ID number!');
    if (preg_match("/\D/",$id)) {
        error('This is not a valid link ID, use numbers (0-9) only!');
    }
    return $id;
} // END checkid

function checklogin() {
    if (isset($_SESSION['logged']) && $_SESSION['logged'] == 'Y')
    {
        return true;
    }
    else
    {
        error('You are not authorized to view this page!');
    }
}
function checkpassword() {
global $settings;

    if(empty($_POST['pass']))
    {
        error('Please enter your admin password!');
    }
    else
    {
        $pass=htmlspecialchars($_POST['pass']);
    }

    if ($pass != $settings['apass'])
    {
        error('Wrong password!');
    }

}
function logout() {
session_unset();
session_destroy();
global $settings;
printHeader();
?>
<tr>
<td class="vmes"><p>&nbsp;</p>
<div align="center"><center>
<table width="400"> <tr>
<td align="center" class="head">LOGGED OUT</td>
</tr>
<tr>
<td align="center" class="dol">
<p>&nbsp;</p>
<p><b>You have been successfully logged out.</b></p>
<p><a href="index.php">Click here to login again</a></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
</td>
</tr> </table>
</div></center>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
</td>
</tr>
<?php
printFooter();
exit();
} // END logout

function login() {
global $settings;
printHeader();
?>
<tr>
<td class="vmes"><p>&nbsp;</p>
<div align="center"><center>
<table width="400"> <tr>
<td align="center" class="head">Enter admin panel</td>
</tr>
<tr>
<td align="center" class="dol"><form method="POST" action="index.php"><p>&nbsp;<br><b>Please type in your admin password</b><br><br>
<input type="password" name="pass" size="20"><input type="hidden" name="action" value="login"></p>
<p><input type="submit" name="enter" value="Enter admin panel"></p>
</form>
</td>
</tr> </table>
</div></center>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
</td>
</tr>
<?php
printFooter();
exit();
} // END login

function error($myproblem) {
global $settings;
printHeader();
?>
<tr>
<td class="vmes"><p>&nbsp;</p>
<div align="center"><center>
<table width="400">
<tr>
<td align="center" class="head">ERROR</td>
</tr>
<tr>
<td align="center" class="dol">
<p>&nbsp;</p>
<p><b>An error occured:</b></p>
<p><?php echo($myproblem); ?></p>
<p>&nbsp;</p>
<p><a href="index.php">Back to the previous page</a></p>
<p>&nbsp;</p>
</td>
</tr> </table>
</div></center>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
</td>
</tr>
<?php
printFooter();
exit();
} // END error

function input($in, $error = 0) {
    $in = trim($in);

    if ($error && strlen($in) == 0) {
        error($error);
    }

    return htmlspecialchars(stripslashes($in));
}

function printHeader() {
global $settings;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1250">
<link rel="STYLESHEET" type="text/css" href="css/style.css">
<title>PHP letöltés számláló - Admin felület</title>
<script language="Javascript" type="text/javascript"><!--
function doconfirm(message) {
    if (confirm(message)) {return true;}
    else {return false;}
}
//-->
</script>
</head>
<body marginheight="5" topmargin="5">
<div align="center"><center>
<table border="0" width="700" cellpadding="5">
<tr>
<td align="center" class="glava"><font class="header">PHP letöltés számláló <?php echo $settings['verzija']; ?><br>-- Admin felület --</font></td>
</tr>
<?php
}

function pj_session_regenerate_id() {

    if (version_compare(phpversion(),"4.3.3",">=")) {
       session_regenerate_id();
    } else {
        $randlen = 32;
        $randval = '0123456789abcdefghijklmnopqrstuvwxyz';
        $random = '';
        $randval_len = 35;
        for ($i = 1; $i <= $randlen; $i++) {
            $random .= substr($randval, rand(0,$randval_len), 1);
        }

        if (session_id($random)) {
            setcookie(
                session_name('CCOUNT'),
                $random,
                ini_get("session.cookie_lifetime"),
                "/"
            );
            return true;
        } else {
            return false;
        }
    }

}
?>
