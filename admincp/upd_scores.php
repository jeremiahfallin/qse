<?php
/* File optimised */
include_once('../includes/nocache.inc.php');

include_once('../includes/clan_funcs.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>Update Scores</h3>';

score_func(0,1);
$out .= "Player scores successfully updated.<br /><br />";

update_clans();
$out .= "Clan scores successfully updated.<br /><br />";

insert_history($user['login_id'],"Updated All Player and Clan Scores");


print_page('Admin',$out);
?>
