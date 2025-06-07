<?php





include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>Give Money to all Players</h3>';
if(!isset($_POST['money_amount'])){
	get_var('Increase Money',$_SERVER['PHP_SELF'],'How much money do you want to give to each player?','money_amount','');
} elseif($_POST['money_amount'] < 1) {
	$out .= "You can't decrease the players money.<br /><br />";
} else {
	settype($_POST['money_amount'], "integer");
	$out .= "Player's money reserves increased by <b>$_POST[money_amount]</b><br />Note: This has NOT sent a message to the players. That is your job.<br /><br />";
	dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + '$_POST[money_amount]' where login_id != 1");
}

print_page('Admin',$out);
?>