<?php




include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out = '<h3>Automation Password Rule</h3>';
if($auto_pass != 2){
	db(__FILE__,__LINE__,"select auto_pass from se_games where db_name = '$db_name'");
	$auto_pass = dbr();
	$out .= 'Would you like this game to change password when the a core runs?';
	$out .= '<br>Note: Will only matter if the game is running on auto.';
	$out .= '<br>0 means keep password the same';
	$out .= '<br>1 means change the password when one of the core\'s run.';
	$out .= '<form name="get_var_form" action="'.$_SERVER['PHP_SELF'].'" method="post">';
	$out .= '<input type="hidden" name="auto_pass" value="2" />';
	$out .= '<input type="text" name="new_auto_pass" value="'.$auto_pass[0].'" size="10" />';
	$out .= '<br /><input type="submit" value="Change" /></form>';
} else {
	if ($new_auto_pass >= 2){
	$out .='Sorry, you cannot have a number that big.  0 or 1 only.';
	} else {
	dbn(__FILE__,__LINE__,"update se_games set auto_pass = '$_POST[new_auto_pass]' where db_name = '$db_name'");
	if($new_auto_pass == 0){
	$out .= 'Automation password rule is <b>'.$_POST['new_auto_pass'].'</b>.<br>The game will not change the password when the core runs';
	} elseif($new_auto_pass == 1) {
	$out .= 'Automation password rule is <b>'.$_POST['new_auto_pass'].'</b>.<br>The game will change the password when the core runs';
	}
}
}
print_page('Change Automation Password Rule',$out);
?>