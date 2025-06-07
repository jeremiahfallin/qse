<?php
/*
//
File:			common.inc.php
Objective:		To be employed as a general startup process - load include files, initiate database connection, etc.
Version:		QS 2.2 Beta
Author:			Maugrim The Reaper (maugrimtr)
Date Committed:	~1999 SE		Date Modified:	29 January 2004

Solar Empire is a Public Domain project. Modifications released under GNU License.
Modifications are Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

// NOTE: this array is fed by a statement at the top of all files. Basically it will hold all files used in the execution of a particular function. This array of filename is used by the translation system to determine what sets of translations to use (translations are set up for each file separately, rather than calling all at once).
$FILE_LIST = array();

require_once("qlib/config/config.inc.php");
require_once("qlib/db_funcs.inc.php");
require_once("includes/common_funcs.inc.php"); // replacement file for common.inc.php functions
require_once("includes/print_funcs.inc.php");
require_once("qlib/auth.class.php");
require_once("includes/ship_loading.inc.php");
require_once("includes/upgrade_funcs.inc.php");

// Start the session
session_start();

// store the PHP SESSID for easy access
$_sid = session_id();

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

// this function is required here since the version used by AdminCP is of a different format
function print_header($title) {
	global $server_name,$user_options,$PHP_SELF;

	if($user_options['theme'] && $user_options['show_pics'] == 1) {
		$style = "themes/".$user_options['theme']."/style_p.css";
	} elseif($user_options['theme'] && $user_options['show_pics'] == 0){
		$style = "themes/".$user_options['theme']."/style.css";
	} else {
		$style = "themes/red_Tempest/style_p.css";
	}

//header HTML code, plus meta tags and call to the style sheet.
?>
<html>
<head>
<title>[ QS: Generations - <?php echo $title;?> ]</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta http-equiv="content-language" content="en-gb">
<meta http-equiv="content-style-type" content="text/css">
<meta name="resource-type" content="document">
<meta name="distribution" content="global">
<meta	name="description" content="highly competitive, web based, space combat game, turn based, BBG">
<meta	name="keywords" content="game, space, combat, strategy, competitive, BBG, online, turn, based">
<link rel="stylesheet" href="<?php echo $style ?>">
<script language="JavaScript1.2" src="javascript_lib.js" type="text/javascript"></script>
<?php
	echo "\n</head>\n<body text=#FFFFFF>"; //prevents use of HEADER command! Use ob_start/ob_end_flush to fix
} //end of print header function.

?>