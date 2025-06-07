<?php

ob_start(); //begin buffering of output

require("common.inc.php");

#$link = mysql_connect("$database_host","$database_user","$database_password");
#mysql_select_db(__FILE__,__LINE__,$database);
$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);


#logout from game.
if($logout_single_game == 1 || $comp_logout == 1){
	if($db_name){
		dbn(__FILE__,__LINE__,"update ${db_name}_users set on_planet = 0 where login_id = '$login_id'");
		insert_history($login_id,"Logged Out");
		//Update score
		if($login_id != 1) {
			score_func($login_id,0);
		}
	}

	//SetCookie("p_pass");
	//SetCookie("db_name");

	//delete session data and close
	$_SESSION = array();
	session_destroy();


	if(!$comp_logout && $login_id != 1){
		//Header("Location: ".$url_prefix."/game_listing.php");
		echo "<script>self.location='$url_prefix/game_listing.php';</script><noscript>You cannot login without JavaScript. Please enable Javascript, or get a browser that supports it.</noscript>";
	} elseif($login_id == 1){
		//SetCookie("session_id",0,0);
		//SetCookie("login_id",0,0);


		//delete session data and close
		$_SESSION = array();
		session_destroy();


		//Header("Location: ".$url_prefix."/login_form.php");
		echo "<script>self.location='$url_prefix/index.php';</script><noscript>You cannot login without JavaScript. Please enable Javascript, or get a browser that supports it.</noscript>";
	}
}

#logout from gamelist too.
if($logout_gamelist == 1 || $comp_logout==1){
	if($login_id != 1 && $logout_gamelist != 1) {
		score_func($login_id,0);
	}
	insert_history($login_id,"Logged Out Completely");
	//SetCookie("session_id",0,0);
	//SetCookie("login_id",0,0);
	//SetCookie("p_pass");
	//SetCookie("db_name");

	//delete session data and close
	$_SESSION = array();
	session_destroy();

		$retro = headers_sent();
		echo($retro);
	//Header("Location: ".$url_prefix."/login_form.php");
	echo "<script>self.location='$url_prefix/index.php';</script><noscript>You cannot login without JavaScript. Please enable Javascript, or get a browser that supports it.</noscript>";
}

ob_end_flush(); //output buffer to browser

exit;
?>
