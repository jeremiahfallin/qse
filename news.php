<?php
require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));


if(!$news_POSTs_show){
$news_POSTs_show = $user_options['news_back'];
}


$rs = '<p><a href=location.php>Back to the Star System</a><br />';

#news search
if($term) {
	$rs = '<p><a href=news.php>Back to News</a><br />';

	$text = search_news($term, 1);
	print_page("News Search",$text);

}

//show all recent Admin News items from last login
if($show_admin_news) {
	$text = '<p><a href=news.php>View full News</a><br /><br />New News Items from Admin:<br /><br />';
	db(__FILE__,__LINE__,"select * from ${db_name}_news where login_id = -1 && timestamp > '$user[last_access_news]' order by timestamp desc LIMIT 10");
	$text .= make_table(array("",""));
	$news = dbr();
	while($news) {
		$text .= quick_row("<b>".date("M d - H:i",$news['timestamp']),stripslashes($news['headline']));
		$news = dbr();
	}
	$text .= "</table><br />";
	dbn(__FILE__,__LINE__,"update ${db_name}_users set last_access_news='".time()."' where login_id='$user[login_id]'");
	$user['last_access_news'] = time();
	print_page("Admin News",$text);
}

		// allows Admin Announcements be flagged for players on main page
		dbn(__FILE__,__LINE__,"update ${db_name}_users set last_access_news='".time()."' where login_id='$user[login_id]'");
		$user['last_access_news'] = time();


$text .= "Search the News:";
db(__FILE__,__LINE__,"select count(*) from ${db_name}_news");
$news_ents = dbr();
$text .= "<form method=post action=news.php>";
$text .= "<input type=text name=term size=20>";
$text .= "<br /><input type=submit value=Search></form><p>";

$text .= "There are <b>$news_ents[0]</b> entries in the news.<p>";

if (!$prev) {
	db(__FILE__,__LINE__,"select * from ${db_name}_news order by timestamp desc LIMIT 0, $news_POSTs_show");
	$news_txt .= "Last <b>$news_POSTs_show</b> posts to the news.</th></tr>";
	$prev2 = $news_POSTs_show;
	$prev = $news_POSTs_show;
} else {
	$prev3 = $prev - $news_POSTs_show;
	$prev2 = $prev + $news_POSTs_show;
	$text .= "<a href=news.php?prev=$prev3>Back to posts $prev3 to $prev</a><p>";
	$news_txt .= "Posts $prev to $prev2 of the news.</th></tr>";
	db(__FILE__,__LINE__,"select * from ${db_name}_news order by timestamp desc LIMIT $prev, $news_POSTs_show");
}

$text .= "<table><tr><th width=75 colspan=2 style='border-top: solid 1px #500099;border-right: solid 1px #500099;border-left: solid 1px #500099;'>".$news_txt."<tr cellpadding=0 border=0><td cellpadding=0 border=0 width=75></td><tr>";

$news = dbr();
while($news) {
	$text .= quick_row("<b>".date("M d - H:i",$news['timestamp']),stripslashes($news['headline']));
	$news = dbr();
}
$text .= "</table><br />";
if($news_ents['0'] > $prev2) {
	$prev3 = $prev2 + $news_POSTs_show;
	$text .= "<p><a href=news.php?prev=$prev2>Posts $prev2 to $prev3</a>";
} else {
	$prev3 = $prev2 - $news_POSTs_show;
	$text .= "This is the end of the news.<br />";
	if($news_ents['0'] > $news_POSTs_show){
		$text .= "<a href=news.php?prev=0>Back to start</a><p>";
	}
}
print_page('News',$text);

?>