<?php
include_once("includes/nocache.inc.php");
require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

#Message stuff
define( 'MESSAGE_CAT_MISC', 0 );
define( 'MESSAGE_CAT_PLANET_LOG', 1 );
define( 'MESSAGE_CAT_SHIP_LOG', 2 );
define( 'MESSAGE_CAT_MESSAGE', 3 );
define( 'MESSAGE_CAT_ADMIN_ONLY', 100 );  // Categories above this are for the admin only
define( 'MESSAGE_CAT_ADMIN_ALERT', 101 );
// Order of this array is the order used in the message summary display
$MESSAGE_CAT_NAME = array(
	MESSAGE_CAT_ADMIN_ALERT	=> "Alerts",
	MESSAGE_CAT_MESSAGE	=> "Messages",
	MESSAGE_CAT_SHIP_LOG	=> "Ship Logs",
	MESSAGE_CAT_PLANET_LOG	=> "Planet Logs",
	MESSAGE_CAT_MISC	=> "Misc" );

#Deleted single message
if($killmsg) {
    dbn(__FILE__,__LINE__,"delete from ${db_name}_messages where message_id = '$killmsg' && login_id = '$user[login_id]'");
	$error_str .= "Message Deleted.<p>";
}

#Delete all messages in one category
if(isset($killcat)) {
	if($sure != 'yes') {
		db(__FILE__,__LINE__,"select count(message_id) from ${db_name}_messages where login_id = '$user[login_id]' and cat = '$killcat'");
		$count_mess = dbr();
		get_var('Delete Messages','mpage.php',
			"Are you sure you want delete all <b>$count_mess[0]</b> messages in the <b>$MESSAGE_CAT_NAME[$killcat]</b> category?",
			'sure','yes');
	} else {
		dbn(__FILE__,__LINE__,"delete from ${db_name}_messages where login_id = $user[login_id] and cat = '$killcat'");
		$error_str .= "All Messages in the <b>$MESSAGE_CAT_NAME[$killcat]</b> Category Deleted.<p>";
	}
}

#Delete all messages
if($killallmsg) {
  if($sure != 'yes') {
	db(__FILE__,__LINE__,"select count(message_id) from ${db_name}_messages where login_id = '$user[login_id]'");
	$count_mess = dbr();
    get_var('Delete Messages','mpage.php','Are you sure you want delete all <b>'.$count_mess[0].'</b> messages?','sure','yes');
  } else {
	dbn(__FILE__,__LINE__,"delete from ${db_name}_messages where login_id = $user[login_id]");
	$error_str .= "All Messages Deleted.<p>";
  }
}

//Delete selected messages
if($clear_messages) {
	if(!$del_mess){
	    $error_str .= "No messages selected to be deleted.<p>";
	} else {
		$q_m = 0;
		$temp656 = $del_mess;
		while ($var = each($temp656)) {
		  dbn(__FILE__,__LINE__,"delete from ${db_name}_messages where message_id = '$var[value]' && login_id = '$user[login_id]'");
		  $q_m++;
		}
		$error_str .= "<b>$q_m</b> Message(s) Deleted.<p>";
	}
}

$original_last_access_inbox = $user['last_access_inbox'];
$user['last_access_inbox'] = time();
dbn(__FILE__,__LINE__,"update ${db_name}_users set last_access_inbox = $user[last_access_inbox] where login_id = $user[login_id]");




function cprint_message($messages) {
	global $allow_signatures, $user_options;
	$error_str .= "<br /><table cellspacing=0 cellpadding=2 class=all width=650 align=center><tr><th style='font-size: 8pt; text-align: left;'>";
	$error_str .= date( "M d - H:i",$messages['timestamp']);
	$error_str .= " - ";
	$error_str .= print_name($messages);
	$error_str .= "</th></tr><tr><td width=100%>";

	$messages['text'] = stripslashes($messages['text']);
	//$m_text = print_name($messages).": ".$messages[text]; #what does this line do? its un-used?!
	$error_str .= "<blockquote>";
	$error_str .= "$messages[text]";
	if (($allow_signatures == 1) && ($user_options['show_sigs'] == 1)) {
		$error_str .= "<br />$messages[sig]";
	}
	$error_str .= "</blockquote>";
	$error_str .= "</td></tr><tr><td width=100% style='border-top: $border_prop;'><a href=message.php?target=$messages[sender_id]&reply_to=$messages[message_id]>Reply</a> - <a href=message.php?forward=$messages[message_id]>Forward</a> - <a href=diary.php?log_ent=$messages[message_id]>Log</a>";
	$error_str .= " - <a href=mpage.php?killmsg=$messages[message_id]>Delete</a> - <input type=checkbox name=del_mess[$messages[message_id]] value=$messages[message_id]>";
	$error_str .= "</td></tr></table>";

	return $error_str;
}


$error_str .= "<a href=message.php>Send Message</a><br />";

if(!isset($cat)){
	$error_str .= "<br /><a href=mpage.php?killallmsg=1>Delete All Messages in All Categories</a><br /><br />";
	$error_str .= "Choose a catagory.<br /><br />";

	$error_str .= "<table class=all>";

	$error_str .= make_row(array("<b>Category</b>", "<b>Messages</b>", ""));

	db(__FILE__,__LINE__,"select cat, count(message_id) as msgs, max(timestamp) as maxtime "
		. "from ${db_name}_messages where login_id = $user[login_id] group by cat");
	while ($dbr=dbr()) {
		$catarray[$dbr['cat']] = $dbr['msgs'];
		if ( $dbr['maxtime'] > $original_last_access_inbox ) {
			$catarray[$dbr['cat']] .= " <font color=ff0000>New</font>";
		}
	}

	foreach ($MESSAGE_CAT_NAME as $id => $name) {
		if (($id < MESSAGE_CAT_ADMIN_ONLY) or (($id > MESSAGE_CAT_ADMIN_ONLY) and ($user['login_id'] == 1))) {
			$error_str .= make_row( array( "<a href=mpage.php?cat=$id>$name</a>",
				isset($catarray[$id]) ? $catarray[$id] : 0,
				"<a href=mpage.php?killcat=$id>Delete All</a>"));
		}
	}

	$error_str .= "</table>";
}else{
	$error_str .= "<form method=post action=mpage.php name=messag_form><input type=hidden name=clear_messages value=1>";
	$error_str .= "<br /><a href=mpage.php?killcat=$cat>Delete All Messages in This Category</a><br />";
	db(__FILE__,__LINE__,"select m.message_id, m.timestamp, m.text, m.sender_id, m.sender_id as login_id, u.sig from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' && m.cat = $cat && m.sender_id = u.login_id order by timestamp desc");
	while($message = dbr()){
		$error_str .= cprint_message($message);
	}
	$error_str .= "<br /><a href=javascript:TickAll(\"messag_form\")>Invert Message Selection</a>";
	$error_str .= " - <input type=submit value='Delete Selected'></form>";
	$error_str .= "<a href=mpage.php>Back to Message Summary</a>";
}

print_page("Messages",$error_str);
?>
