<?php
include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out = '<h3>Game Automation</h3>';
if($game_auto != 2){
	db(__FILE__,__LINE__,"select game_auto from se_games where db_name = '$db_name'");
	$game_auto = dbr();
	$out .= 'Would you like this game Cyrax controlled?';
//	$out .= '<br>Note: Will only matter if the game is running on auto.';
	$out .= '<br>0 means no';
	$out .= '<br>1 means yes';
	$out .= '<form name="get_var_form" action="'.$_SERVER['PHP_SELF'].'" method="post">';
	$out .= '<input type="hidden" name="game_auto" value="2" />';
	$out .= '<input type="text" name="new_game_auto" value="'.$game_auto[0].'" size="10" />';
	$out .= '<br /><input type="submit" value="Change" /></form>';
} else {
	if ($new_game_auto >= 2){
	$out .='Sorry, you cannot have a number that big.  0 or 1 only.';
	} else {
	dbn(__FILE__,__LINE__,"update se_games set game_auto = '$_POST[new_game_auto]' where db_name = '$db_name'");
	$out .= 'Game Automation is set to: <b>'.$_POST['new_game_auto'].'</b>.<br>';
//	if($new_game_auto == 0){
//	$out .= 'Automation password rule is <b>'.$_POST['new_game_auto'].'</b>.<br>The game will not change the password when the core runs';
	/*} else {*/
	}
}
print_page('Game Automation',$out);
?>