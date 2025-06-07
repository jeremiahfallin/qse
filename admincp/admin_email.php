<?php



include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out = '<h3>Change Admin Email Address</h3>';
if($email != 2){
	db(__FILE__,__LINE__,"select admin_email from se_games where db_name = '$db_name'");
	$present_mail = dbr();
	$out .= 'Please enter New Admin E-mail Address:';
	$out .= '<form name="get_var_form" action="'.$_SERVER['PHP_SELF'].'" method="post">';
	$out .= '<input type="hidden" name="email" value="2" />';
	$out .= '<input type="text" name="new_mail" value="'.$present_mail[0].'" size="30" />';
	$out .= '<br /><input type="submit" value="Change" /></form>';
} else {
	if(!ereg("@",$_POST['new_mail']) || !ereg("\.",$_POST['new_mail'])){
		 print_page('Admin Mail','Please Enter a Valid Email Address');
	}
		dbn(__FILE__,__LINE__,"update se_games set admin_email = '$_POST[new_mail]' where db_name = '$db_name'");
	$out .= 'Admins Email Address has been changed to: <br /><b>'.$_POST['new_mail'].'</b>.';
}
print_page('Change Admin Mail',$out);
?>