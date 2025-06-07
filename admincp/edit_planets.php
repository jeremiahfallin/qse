<?php




include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>Edit Planets</h3>';
$out .= '<p>This page will allow you to edit planets in the game.</p>';
$out .= '<p>However, this feature is only in it\'s planning stage at present. Please continue to edit the database by hand (or using phpMyAdmin) until it is complete.</p>';

print_page('Edit Planets',$out);
?>