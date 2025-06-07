<?php




include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out = '<h3>Teams</h3>';
if($teams != 2){
	db(__FILE__,__LINE__,"select teams from se_games where db_name = '$db_name'");
	$teams = dbr();
	$out .= 'Eventually, Admins will have the choice of selecting up to 4 Factions.';
	$out .= 'Would you like this game to use teams?';
	$out .= '<br>0 means this game will use standard clans';
	$out .= '<br>1 means this game will run two teams';
	$out .= '<form name="get_var_form" action="'.$_SERVER['PHP_SELF'].'" method="post">';
	$out .= '<input type="hidden" name="teams" value="2" />';
	$out .= '<input type="text" name="new_teams" value="'.$teams[0].'" size="10" />';
	$out .= '<br /><input type="submit" value="Change" /></form>';
} else {
	if ($new_teams >= 2){
	$out .='Sorry, you cannot have a number that big.  0 or 1 only.';
	} else {
	dbn(__FILE__,__LINE__,"update se_games set teams = '$_POST[new_teams]' where db_name = '$db_name'");
	$out .='Clan\'s deleted.<br>';
	dbn(__FILE__,__LINE__,"delete from ${db_name}_clans");
	if($new_teams == 1){
	$out .= 'Teams are set at <b>'.$_POST['new_teams'].'</b>.<br>There are now two teams in this game.';
	dbn(__FILE__,__LINE__,"INSERT INTO ${db_name}_clans VALUES ('1', 'Legions of Chaos', 'united', '1', '0', '1', 'LoC', 'FF0000', '0', '0')");
	dbn(__FILE__,__LINE__,"INSERT INTO ${db_name}_clans VALUES ('2', 'Forces for Order', 'united', '1', '0', '1', 'FfO', 'FFFF00', '0', '0')");
	$out .='<br>The teams have been created.';
	} elseif($new_teams == 0) {
	$out .= 'Teams are set at <b>'.$_POST['new_teams'].'</b>.<br>The game is using the standard clan format.';
	}
}
}
print_page('Teams',$out);
?>