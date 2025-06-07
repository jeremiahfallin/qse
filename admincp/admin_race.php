<?php




include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
		$mr = dbr();
		$max_races = $mr['value'];

$out = '<h3>Change Admin Race</h3>';
if($admin_race != 2){
	db(__FILE__,__LINE__,"select race from ${db_name}_users where login_id = '1'");
	$admin_race = dbr();
	$out .= 'Please enter the Admins race';
	$out .= '<form name="get_var_form" action="'.$_SERVER['PHP_SELF'].'" method="post">';
	$out .= '<input type="hidden" name="admin_race" value="2" />';
	$out .= '<input type="text" name="new_race" value="'.$admin_race[0].'" size="30" />';
	$out .= '<br /><input type="submit" value="Change" /></form>';
} else {
	if($new_race < 1){
		$out .= '<br>Are you mad?  The number has to be one or larger!';
	} elseif ($new_race < $max_race){
		$out .= '<br>You cant enter a number that big!</br>';
	} else {
	dbn(__FILE__,__LINE__,"update ${db_name}_users set race = '$_POST[new_race]' where login_id = '1'");
	$out .= 'Admin\'s Race has been changed to: <br /><b>'.$_POST['new_race'].'</b>.';
	}
}
print_page('Change Admin Race',$out);
?>