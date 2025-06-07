<?php




include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out = '<h3>View Cash Scale</h3>'
	. '<p><b><font color="yellow">Note: This cash scale is inaccurate!</font></b></p>';
$out .= SlidingScale(1);
print_page("View Cash Scale",$out);
?>