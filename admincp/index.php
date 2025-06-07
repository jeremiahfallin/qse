<?php



include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>Admin Index</h3>';
$out .= '<p>Welcome, Admin, to your new and improved control panel.</p>'
	. '<p>Please find on the left a selection of your most powerful tools to aid your god-like dominance of the universe.</p>';

//(un)pause
if(isset($pause) && $pause==1){
	$out .= '<p>Game Paused.</p>';
	dbn(__FILE__,__LINE__,"update se_games set paused = '1' where db_name = '$db_name'");
	post_news('Game Paused');
} elseif(isset($pause) && $pause==2){
	post_news('Game Un-Paused');
	$out .= '<p>Game Un-paused.</p>';
	db2(__FILE__,__LINE__,"select admin_name, name from se_games where db_name = '$db_name'");
	$descr = dbr2();
$bleh1 = "$descr[name] has unpaused";
$bleh2 = "The admin of $descr[name], $descr[admin_name], has decided, in his infinte wisdom, to unpause $descr[name] .\nGood luck, and enjoy the game!";
	mail_users($bleh1,$bleh2);
	dbn(__FILE__,__LINE__,"update se_games set paused = '0' where db_name = '$db_name'");
}

if((isset($_GET['rejoin_on']) || isset($_GET['rejoin_off'])) && isset($_GET['control'])) {
	if($_GET['rejoin_on'] == 2 && $_GET['control'] == 2){
		$out .= '<p><b class="b1">Rejoin Delay is now ACTIVE in this game</b></p><p>The below field will store the games login times for each player account. If a player chooses to retire they will be forced to wait 24hrs before rejoining the game. This option is aimed at reducing the occurence of players joining a game, exploring the universe, retiring and then rejoining with normal turns. This tactic is a form of cheating.</p>';
		dbn(__FILE__,__LINE__,"ALTER TABLE `user_accounts` ADD `$db_name` INT(11) DEFAULT '0' NOT NULL");
		dbn(__FILE__,__LINE__,"update {$db_name}_db_vars set value = 1 where name = 'rejoin_delay'");
		$out .= '<p>Field <b>'.$db_name.'</b> added to user_accounts table.</p>';
		$out .= '<p>Field now added. You may turn off this delay on rejoining after retiring through the admin vars for the game or from the Admin Menu.';
	} elseif($_GET['rejoin_off'] == 2 && $_GET['control'] == 2) {
		$out = '<p><b class="b1">Rejoin Delay is now INACTIVE in this game</b></p><p>The below field has been deleted</p>';
		dbn(__FILE__,__LINE__,"ALTER TABLE `user_accounts` DROP `$db_name`");
		dbn(__FILE__,__LINE__,"update {$db_name}_db_vars set value = 0 where name = 'rejoin_delay'");
		$out .= 'Field <b>'.$db_name.'</b> dropped from user_accounts table.<br />';
		$out .= '<br />Field now removed. To enable Rejoin-Delay, please use the Admin Menu to do so. This will allow the necessary fields be added automatically to the database.';
	}
}

$out2 .= '<table cellspacing="0" cellpadding="2" width="150" border="0" class="all"><tr><th valign="top">Toggles</th></tr><tr><td>';
db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'rejoin_delay'");
$rejoin = dbr();
if($rejoin['value'] == 0) {
	$out2 .= '<a href="'.$_SERVER['PHP_SELF'].'?rejoin_on=2&control=2" class="page_menu">Enable Rejoin-Delay</a>';
} else {
	$out2 .= '<a href="'.$_SERVER['PHP_SELF'].'?rejoin_off=2&control=2" class="page_menu">Disable Rejoin-Delay</a>';
}
$out2 .= '</td></tr><tr><td align="center">';

db(__FILE__,__LINE__,"select paused from se_games where db_name = '$db_name'");
$paused=dbr();
if($paused[0] == 1){
	$out2 .= '<a href="'.$_SERVER['PHP_SELF'].'?pause=2" class="page_menu">Un-Pause Game</a>';
} else {
	$out2 .= '<a href="'.$_SERVER['PHP_SELF'].'?pause=1" class="page_menu">Pause Game</a>';
}
$out2 .= '</td></tr></table>';

print_page('Admin',$out,$out2);
?>