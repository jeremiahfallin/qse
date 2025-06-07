<?php




include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out = '<h3>Change Admin Name</h3>';
if($admin_name != 2){
	db(__FILE__,__LINE__,"select admin_name from se_games where db_name = '$db_name'");
	$admin_name = dbr();
	$out .= 'Please enter New Admin\'s Name:';
	$out .= '<form name="get_var_form" action="'.$_SERVER['PHP_SELF'].'" method="post">';
	$out .= '<input type="hidden" name="admin_name" value="2" />';
	$out .= '<input type="text" name="new_name" value="'.$admin_name[0].'" size="30" />';
	$out .= '<br /><input type="submit" value="Change" /></form>';
} else {
		dbn(__FILE__,__LINE__,"update se_games set admin_name = '$_POST[new_name]' where db_name = '$db_name'");
		dbn(__FILE__,__LINE__,"update ${db_name}_users set login_name = '$_POST[new_name]' where login_id = '1'");
	$out .= 'Admin\'s Name has been changed to: <br /><b>'.$_POST['new_name'].'</b>.';
}
print_page('Change Admin Name',$out);
?>