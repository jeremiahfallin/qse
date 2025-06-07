<?php






include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

//reset signup times
if(!isset($_POST['sure'])){
	$out .= 'Are you sure you want to reset players signup times?';
	get_var('Reset Signup Times',$_SERVER['PHP_SELF'],$out,'sure','yes','index.php');
} elseif(( isset($_POST['sure']) ) && ( $_POST['sure'] == 'yes' )) {
	$out .= '<p>Signup times reset.</p>';
	dbn(__FILE__,__LINE__,"update ${db_name}_users set joined_game = UNIX_TIMESTAMP() where login_id > 5");

	post_news('Signup Times Reset');
	insert_history($user['login_id'],'Reset Signup Times.');
}
print_page('Reset Game',$out);
?>