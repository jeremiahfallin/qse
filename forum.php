<?php
include_once("includes/nocache.inc.php");

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

if ($user[login_id] == 1) {
  $admin_forum = 1;
}

#$out .= "<br />";

if($clan_forum){
	if($user[clan_id] < 1 && $user[login_id] != 1){
		print_page("Clan forum","<br />You are not in a clan so can't access this page.");
	}
	if($killmsg && $admin_forum) {
		dbn(__FILE__,__LINE__,"delete from ${db_name}_messages where message_id = '$killmsg' && login_id = '-5'");
	}
	if($look_at && $user[login_id] == 1){ //what's Mori up to???
		dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = '$look_at' where login_id = '1'");
		$user[bounty] = $look_at;
		$user[clan_id] = $look_at;
	}

	$out .= "<blockquote>";
	if($user[login_id] == 1){
		$user[clan_id] = $user[bounty];
		db(__FILE__,__LINE__,"select clan_name,clan_id from ${db_name}_clans order by clan_name");
		$clans=dbr();
		if($clans){
			$selected[$user[bounty]] = " selected";
			$out .= "Please select a clan forum to Monitor";
			$out .= "<form action=forum_clan.php method=post>";
			$out .= "<input type=hidden name=clan_forum value='1'>";
			$out .= "<select name=clanid>";
			while($clans){
				$out .= "<option value=$clans[clan_id]".$selected[$clans['clan_id']].">$clans[clan_name]";
				$clans=dbr();
			}
			$out .= "</select>";
			$out .= " - <input type=submit value=Monitor></form><p>";
		} else {
			$out .= "There are no clans in this game at present.";
			print_page("Clan Forum",$out);
		}
	}
	db(__FILE__,__LINE__,"select clan_name,sym_color from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan_name=dbr();
	$out .= "Welcome to the <font color=$clan_name[sym_color]>$clan_name[clan_name]</font> Clan Forum.";
	$out .= $rs;
	if($user[login_id] == 1){
		$out .= "<a href=message.php?target=-5&clan_id=$user[bounty]>Post to Clan Forum</a><br />";
	} else {
		$out .= "<a href=message.php?target=-5>Post to Clan Forum</a><br />";
	}
	$temp_id = $user[login_id];
	$user[login_id] = -5;
	print_messages(0);
	$user[login_id] = $temp_id;
	$out .= $error_str;
	print_page("Clan Forum",$out);
}


if(isset($killmsg) && $user[login_id] == 1) {
#  db(__FILE__,__LINE__,"select meage from ${db_name}_messages where message_id = $killmsg");
#  $message = dbr();
#  if($message[login_id] == -1 && $user[login_id] == 1) {
    dbn(__FILE__,__LINE__,"delete from ${db_name}_messages where message_id = '$killmsg' && login_id = '-1'");
}

if(isset($killallmsg) && ($user[login_id] == 1)) {
  if($sure != 'yes') {
      get_var('Delete Messages','forum.php','Are you sure you want delete all Forum messages?','sure','yes');
  } else {
    dbn(__FILE__,__LINE__,"delete from ${db_name}_messages where login_id = -1");
  }
}

#$out .= "<blockquote>";

#$out .= "<a href=mc.php>Message Codes Guide.</a>";
if($user[last_access_forum] > 0){
	if(!$find_last){
	$out .= "<br /><a href=forum.php?last_time=$user[last_access_forum]&find_last=1>Show New Posts</a>";
	} else {
	$out .= "<br /><a href=forum.php>Show All Posts</a>";
	}
}
#$out .= $rs;
$out .= "<br /><br /><a href=message.php?target=-1>Post to Forum</a> (cost is $cost_to_POST turns)<p>";
$temp_id = $user[login_id];
$user[login_id] = -1;

print_messages(0);
$out .= $error_str;
$user[login_id] = $temp_id;
print_page("Forum",$out);
?>
