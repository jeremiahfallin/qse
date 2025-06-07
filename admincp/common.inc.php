<?php
/*
//
File:			common.inc.php
Objective:		Original SE file for all commonly used functions
Version:		QS 2.2 Beta
Author:			Bryan and other Solar Empire DEVs - modified by Maugrim The Reaper
Date Committed:	~1999 SE		Date Modified:	29 January 2004

Solar Empire is a Public Domain project. Modifications released under GNU License.
Modifications are Copyright (c) 2003, 2004 by Pádraic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

require_once("lib/TreeMenuXL.php");


require_once("../qlib/config/config.inc.php");
require_once("../qlib/db_funcs.inc.php");
require_once("../includes/common_funcs.inc.php"); // replacement file for common.inc.php functions
require_once("../includes/print_funcs.inc.php");
require_once("../qlib/auth.class.php");
//require_once("../includes/ship_loading.inc.php");
//require_once("../includes/upgrade_funcs.inc.php");
//require_once("../qlib/smarty.inc.php");


// NOTE: this array is fed by a statement at the top of all files. Basically it will hold all files used in the execution of a particular function. This array of filename is used by the translation system to determine what sets of translations to use (translations are set up for each file separately, rather than calling all at once).
$FILE_LIST = array();

// Start the session

session_start();

//echo($_SESSION['ytrh']);


// this section checks passed cookie vars, will be some time before QS is register_globals free but login/signup is
// covered and blocks post/GET/etc manipulation

if(!isset($_SESSION['login_name']))
{
	$login_name = "";
}
else
{
	$login_name = $_SESSION['login_name'];
}
if(!isset($_SESSION['login_id']))
{
	$login_id = "";
}
else
{
	$login_id = $_SESSION['login_id'];
}
if(!isset($_SESSION['session_id']))
{
	$session_id = "";
}
else
{
	$session_id = $_SESSION['session_id'];
}
if(!isset($_SESSION['db_name']))
{
	$db_name = "";
}
else
{
	$db_name = $_SESSION['db_name'];
}

function print_header($title) {
	global $server_name,$user_options,$PHP_SELF;

	if($user_options['theme']) {
		$style = "../themes/".$user_options['theme']."/style.css";
	} elseif ($user_options['color_scheme']) {
		$style = "styles/style".$user_options['color_scheme'].".css";
	} else {
		$style = "styles/style1.css";
	}


//header HTML code, plus meta tags and call to the style sheet.
$theme = strlen($user_options['theme']) > 0 ? $user_options['theme'] : "red_Tempest";
?>
<html>
<head>
<title>[ Quantum Star SE AdminCP - <?php echo $title;?> ]</title>
<meta	name="description" content="highly competitive, web based, space combat game, turn based, BBG">
<meta	name="keywords" content="game, space, combat, strategy, competitive, BBG, online, turn, based">
<link rel="stylesheet" href="../themes/<?php echo $theme; ?>/style_p.css">
<script language="JavaScript1.2" src="../javascript_lib.js" type="text/javascript"></script>
<script src="lib/TreeMenu.js" language="JavaScript" type="text/javascript"></script>
<link href="lib/TreeMenu.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_callJS(jsStr) { //v2.0
  return eval(jsStr)
}
//-->
</script>
<style type="text/css">
body {
background-image: url(../images/starfield.gif);
background-attachment: fixed;
}
</style>
<?php
	echo "\n</head>\n<body text=#FFFFFF>"; //prevents use of HEADER command! Use ob_start/ob_end_flush to fix
}



?>