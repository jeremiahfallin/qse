<?php





include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out = '<h3>Change Intro Message</h3>';
if($messag != 2){
	db(__FILE__,__LINE__,"select intro_message from se_games where db_name = '$db_name'");
	$present_mess = dbr();
	$out .= '<p>Please enter a message that all new players will recieve when they join.</p><p>Note: HTML is enabled, but does not use the message codes. <br />(Leave empty if you don\'t want to use it):</p>';
	$out .= '<form name="get_var_form" action="'.$_SERVER['PHP_SELF'].'" method="post">';
	$out .= '<input type="hidden" name="messag" value="2" />';
	$out .= '<textarea name="new_mess" cols="50" rows="20" wrap="soft">'.$present_mess[0].'</textarea>';
	$out .= '<br /><input type="submit" value="Change" /></form>';
} else {
	$new_mess = addslashes($_POST['new_mess']);
	dbn(__FILE__,__LINE__,"update se_games set intro_message = '$new_mess' where db_name = '$db_name'");
	$out .= 'The Intro message has been changed.';
}
print_page('Change Description',$out);
?>