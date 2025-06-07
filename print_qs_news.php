<?php
//require("common.inc.php");

$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);

	#db("select * from user_accounts where login_id = '$login_id'");
	#$p_user = dbr();

db(__FILE__,__LINE__,"select * from qsse_home_news order by timestamp desc limit 3");
while($news_posts = dbr()) {
echo '
<table style="border : thin solid #444444;" cellspacing="0" cellpadding="2" width=100%>
	<tr>
		<td BGCOLOR="#444444" align=left style=>
			<b class=b1>'.$news_posts['admin_name'].'</b>
		</td>
		<td BGCOLOR="#444444" align=right>
			'.date("H:i - F j, Y", $news_posts['timestamp']).'
		</td>
	</tr>
	<tr>
		<td colspan=2>
			'.stripslashes($news_posts['text']).'
		</td>
	</tr>';
	if(($p_user['login_id'] == 1 || (OWNER_ID != 0 && $p_user['login_id'] == OWNER_ID)) && $logged_in == 1) {
		echo "
		<tr>
			<td align=right colspan=2>
			<a href='login_form.php?delete=$news_posts[news_id]'>Delete</a>
			</td>
		</tr>";
	}
echo '
</table>
<br>';
}


?>