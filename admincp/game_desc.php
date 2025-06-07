<?php





include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out = '<h3>Change Game Description</h3>';
if($_POST['descr'] != 2){
	db(__FILE__,__LINE__,"select description from se_games where db_name = '$db_name'");
	$present_desc = dbr();
	$out .= '<p>Please enter some words that explain the game.</p><p>Note: HTML is enabled, but does not use the message codes.</p><p>(Leave empty if you don\'t want to use it)</p>';
	$out .= '<form name="get_var_form" action="'.$_SERVER['PHP_SELF'].'" method="post">';
	$out .= '<input type="hidden" name="descr" value="2" />';
	$out .= '<textarea name="new_descr" cols="50" rows="20" wrap="soft">'.$present_desc[0].'</textarea>';
	$out .= '<p><input type="submit" value="Change"></form>';
} else {
	$new_descr = stripslashes($_POST['new_descr']);
	$new_descr = addslashes($new_descr);
	dbn(__FILE__,__LINE__,"update se_games set description = '$new_descr' where db_name = '$db_name'");
	$out .= 'The description of the game has been changed.';
}
print_page('Change Description',$out);
?>