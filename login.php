<?php
/*
//
File:			login.php
Objective:		Process used to authenticate user for server account plus each game on server
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	5 October 2003	Date Modified:	6 October 2003

Copyright (c) 2003, 2004 by Pádraic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

ob_start();

// In an effort to keep files more concise and easier to work with, much in-file code has migrated to includes files
require_once("common.inc.php");
echo "<link rel=\"stylesheet\" href=\"themes/red_Tempest/style.css\">";
// connect to database
//$link = mysql_connect("$database_host","$database_user","$database_password");
//mysql_select_db($database);
$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);
//clan_id clan_sym clan_sym_color
// Code controlling specific game entry checks

if(isset($_GET['game_db']) || isset($_POST['game_db']))
{
	if(!empty($_GET['game_db'])) {
		$db_name = $_GET['game_db'];
	}
	elseif(!empty($_POST['game_db']))
	{
		$db_name = $_POST['game_db'];
	}
	$auth = new Q_Authenticate();
	$auth->AuthUserGame();
}


// Code controlling logging player into server account

//$user = GrabUser($_POST['l_name']);
//$authent = AuthUser($user, $_POST['passwd']);

// possible object code
$auth = new Q_Authenticate();
$user = $auth->GrabUser($_POST['l_name']);

// check challenge/response validity for login

$authent = $auth->AuthUser($user, $_POST['response']);

//print_r($user);
//echo("<br>----------<br>".$authent);
//exit();

if($user['last_login'] == 0 && $authent == 1)
{
	print_header("The Quantum Star SE Story");
	echo "<br><a href=game_listing.php>Skip Story</a><br>";
	$temp_var = include("story.inc.php");
	echo "<center><b>The Quantum Star SE Story</b></center><br><br>".$temp_var['The_Solar_Empire_Story'];
	echo "<br><br><a href=game_listing.php>Skip Story</a><br>";
	require_once("footer.php");
	unset($authent);
	exit();
}
elseif ($authent == 1)
{
	//unset($authent);
	echo "<script>self.location='$url_prefix/login_form.php';</script><noscript>You cannot login without JavaScript. Please enable Javascript, or get a browser that supports it.</noscript>";
}

ob_end_flush();

?>
