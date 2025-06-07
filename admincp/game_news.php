<?php






include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}


if($text == '') {
	get_var('Post Game News',$_SERVER['PHP_SELF'],'What do you want to post in News?','text','');
} else {
	$text = '<b class="b1">Admin</b> posts: <blockquote><font color="lime">'.addslashes($text).'</font></blockquote><br />';
	$save_login_id = $login_id;
	$login_id = -1;
	post_news($text);
	$login_id = $save_login_id;
	$out .= '<h3>Post Game News</h3>';
	$out .= '<p>News Posted.</p>';
	print_page('Post Game News',$out);
}
?>