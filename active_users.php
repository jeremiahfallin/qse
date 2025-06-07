<?php
include_once("includes/nocache.inc.php");
$filename = "active_users.php";

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));


$dev2 = $owner_id;
if($user['login_id'] == 1 || $user['login_id'] == $dev2) {
/*$out .= "<script>setTimeout('self.location.reload()',5000);</script>";*/
//$out .= "Page reloads every 5 seconds.<br /><br />";
$out = "Users that have logged in within the past 5 mins:";
$out .= "<br />Time Loaded: <b>".date("H:i:s (M d)")."</b><br /><a href=active_users.php>Reload</a>";
db(__FILE__,__LINE__,"select last_request,login_name,login_id,clan_sym,clan_sym_color,clan_id from ${db_name}_users where last_request > ".(time()-300)." && login_id > 1 and login_id != 3 order by last_request desc");
$player = dbr();
if(!$player){
	$out .= "<p>There are no active users.";
} else {
	$out .= "<p><table>";
	$out .= "<tr bgcolor='#555555'><td>Login Name</td><td>Last Request</td></tr>";
	while ($player) {
	  $out .= "<tr bgcolor='#333333'><td>".print_name($player)."</td><td>".date( "H:i:s (M d)",$player['last_request'])."</td><td> - <a href=message.php?target=$player[login_id]>Message Player</a><br /></td></tr>";
	  $player = dbr();
	}
	$out .= "</table>";
}
$rs = "<p><a href=admin.php>Back to Admin Page</a>";
} else {
  print_page("Active Users","Only Admin can view active accounts.");
}
print_page("Active Users",$out);
?>
