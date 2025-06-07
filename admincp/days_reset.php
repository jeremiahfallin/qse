<?php
include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out = '<h3>Change days left til reset</h3>';
	db(__FILE__,__LINE__,"select paused from se_games where db_name = '$db_name'");
	$paused=dbr();
if($paused['paused'] == 1){
	if($game_length_days != 2){
		db(__FILE__,__LINE__,"select game_length_days from se_games where db_name = '$db_name'");
		$game_length_days = dbr();
		$out .= 'Note to Admin: Never use unless game is paused.<br>It will screw up the game time if set after unpause of game.';
		$out .= '<form name="get_var_form" action="'.$_SERVER['PHP_SELF'].'" method="post">';
		$out .= '<input type="hidden" name="game_length_days" value="2" />';
		$out .= '<input type="text" name="new_days" value="'.$game_length_days[0].'" size="30" />';
		$out .= '<br /><input type="submit" value="Change" /></form>';
	} else {
		dbn(__FILE__,__LINE__,"update se_games set game_length_days = '$_POST[new_days]' where db_name = '$db_name'");
				dbn(__FILE__,__LINE__,"update se_games set game_days_remaining = '$_POST[new_days]' where db_name = '$db_name'");
	$out .= 'Days left til reset has been changed to: <br /><b>'.$_POST['new_days'].'</b>.';
}
} elseif($paused['paused'] == 0){
$out = 'Currently the game is running';
$out = 'You cannot change the days left til reset while the game is <b>running</b>.';
}
print_page('Days left til reset',$out);

?>