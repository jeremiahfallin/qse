<?php




include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>Change stated difficulty</h3>';

if(!isset($set_dif)){
	$out = '<p>This will have no effect upon the game itself, but will serve simply to inform new joiners to the game what to expect.</p>
	<form action="'.$_SERVER['PHP_SELF'].'" name="get_dif_form" method="post">
	<input type="radio" name="set_dif" value="1" />Beginner
	<br /><input type="radio" name="set_dif" value="2" />Beginner -> Intermediate
	<br /><input type="radio" name="set_dif" value="3" />Intermediate
	<br /><input type="radio" name="set_dif" value="4" />Intermediate - > Advanced
	<br /><input type="radio" name="set_dif" value="5" />Advanced
	<br /><input type="radio" name="set_dif" value="6" />All Skill Levels
	<p><input type="submit" value="Submit" /></form>';
} else {
	dbn(__FILE__,__LINE__,"update se_games set difficulty = '$set_dif' where db_name = '$db_name'");
	$out .= '<p>Difficulty Level has been updated.</p>';
}
print_page('Select Difficulty',$out);
?>