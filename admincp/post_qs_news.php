<?php
include_once("../includes/nocache.inc.php");

require_once('admin.inc.php');

#set_time_limit(120); //a full minute to run it.

if($user['login_id'] != 1) {
	print_page("Admin","Admin access only.");
	exit();
}

$rs = "<p><a href=index.php>Back to Admin Page</a>";

if(isset($qsservernews)) {
	$qsservernews = addslashes($qsservernews);
	//$qsservernews = stripslashes($qsservernews);
	db(__FILE__,__LINE__,"select * from se_games where db_name = '$db_name'");
	$game_inf = dbr();
	dbn(__FILE__,__LINE__,"insert into qsse_home_news (timestamp,admin_name, game_id, login_id, text) values(".time().",'$game_inf[admin_name]','$game_inf[game_id]','$user[login_id]','$qsservernews')");
	print_page("Server News","Your news post has been entered into the Database and should appear on the front login page momentarily.");
}elseif(isset($qs_post_news)) {
	get_var('Post Server News','post_qs_news.php',"<h1>Post Server News</h1>Server News is posted to the main Server Login page. Please use this feature wisely. Admins are granted this feature as a privilage which will be revoked if excessive or offensive news posts are made. The Server Administrator reserves all right to delete Server News as required. Otherwise use this form to add front page news concerning resets, game progress and game specific news. If you are going to post news, ensure it is clearly written. Short and multiple posts are discouraged.",'nws','');
}else {
	print_page("Admin","Illegal Usage of Admin Menu Files. Access reported to Server Administrator.");
}

?>
