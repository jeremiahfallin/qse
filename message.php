<?php
include_once("includes/nocache.inc.php");

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

// message checks
if($target==-2 && (!isset($clan_id) || $clan_id <= 0)) {
  print_page("Send Clan Message","No such clan.");
} elseif($target == -2 && $clan_id != $user[clan_id]) {
  print_page("Send Clan Message","You can only send clan messages to your own clan.");
} elseif($target == -2 && $user[clan_id] < 1) {
  print_page("Send Clan Message","You can may not send a message to this clan.");
} elseif($target == -4 && $user[login_id] != 1) {
  print_page("Send Mass Message","You are not the Admin.");
} elseif($target == -5 && $user[clan_id] < 1 && $user[login_id] != 1) {
  print_page("Clan Forum","You are not in a clan.");
} elseif($target == -1 && $user[turns] < $cost_to_POST) {
  print_page("Send Message","You don't have any turns to use.");
} elseif($text == '') {
	if($target > 0){
		db(__FILE__,__LINE__,"select login_name,login_id,clan_sym,clan_sym_color from ${db_name}_users where login_id = $target");
		$rec = dbr();
		$rec2=$rec[0]; //-tradelair
		$rec = print_name($rec);
	} elseif($target== -1){
		$rec = "the Forum";
	} elseif($target== -2){
		$rec = "your Clan";
	} elseif($target== -3){
		$rec = "the Bug Board";
	} elseif($target== -4){
		$rec = "All the Players";
	} elseif($target== -5){
		$rec = "your Clan Forum";
	} else { #user has not specified a target, so list all the players who can be messaged, and get message.
		db(__FILE__,__LINE__,"select login_name,login_id,clan_sym from ${db_name}_users where login_id != '$user[login_id]' && (login_id > 5) order by login_name asc");
		$dest = dbr();

		if(!$dest){
			print_page("Send Message","There is no-one in the game to send a message to.");
		}

		$mess_str .= "<select name=\"target\" tabindex=\"1\">";
		$mess_str .= "<option value=1>~The Admin~</option>";
		while($dest){
			if($dest[clan_sym]) {
				$sym_txt = " ($dest[clan_sym])</option>";
			} else {
				$sym_txt = "</option>";
			}

			$mess_str .= "<option value=$dest[login_id]> $dest[login_name]".$sym_txt;
			$dest = dbr();
		}
		$mess_str .= "</select><br />";

		if(isset($forward)) {
			db(__FILE__,__LINE__,"select text from ${db_name}_messages where message_id = '$forward' && (login_id = '-1' || login_id='$user[login_id]' || (login_id = -5 && clan_id = $user[clan_id]))");
			$forward_message = dbr();
			$forward_message = " \n\n\n\n\n\n------Original Text------\n\n".strip_tags($forward_message['text']);
		} else {
			$forward_message = NULL;
		}

		get_var('Send Message','message.php',"Select the player you would like to send the message to:<br /><br /> $mess_str<br /><br />Enter your message below:",'msg', $forward_message);
	}


	if(isset($reply_to)){ //reply to with original text
		$rs .= "<br /><a href=mpage.php>Back to Message Page</a>";
		if($user[login_id] == -1){
			db(__FILE__,__LINE__,"select text from ${db_name}_messages where message_id = '$reply_to' && login_id = '-1'");
		} else {
			db(__FILE__,__LINE__,"select text from ${db_name}_messages where message_id = '$reply_to' && (login_id = -1 || login_id=$user[login_id] || (login_id = -5 && clan_id = $user[clan_id]))");
		}
		$reply_to = dbr();
		$reply_to[text] = stripslashes($reply_to[text]);

		// ** tradelair   -V
		$start=strpos($reply_to[text],"-----Original");
		$end=strpos($reply_to[text],"-----Original",$start+1);
		if($end>0){$reply_insert=substr($reply_to[text],0,$end )."\n*** The rest of the original reply was removed ***\n";
		}else{ $reply_insert=$reply_to[text];}
		$reply_insert=" \n\n\n-----Original Message from $rec2 ------\n".Strip_tags($reply_insert)."\n-----------------\n";
		get_var('Send Message','message.php',"<br />What is your reply?",'msg',$reply_insert);
        //** tradelair  -^

	} else { //no original text
		get_var('Send Message','message.php',"What is your message to <b class=b1>$rec</b>?",'msg','');
	}
} else {
  if($target==-1) {
	charge_turns($cost_to_POST);
	$text = Clean_Text($text);
  }
  $text = mcit($text);
  $text = addslashes($text);
if($user[login_id] != 1) {
  $text = stripslashes($text);
} else {
    if(eregi("\[nocolor\]",$text)) {
      $text = eregi_replace("\[nocolor\]","",$text);
    } else {
      db(__FILE__,__LINE__,"select * from ${db_name}_db_vars where name = 'message_colour'");
	  $colour = dbr();
	  if($colour[value] == 1) {
		$text = "<font color=yellow>".$text."</font>";
	  } elseif ($colour[value] == 2) {
		$text = "<font color=aqua>".$text."</font>";
	  } elseif ($colour[value] == 3) {
		$text = "<font color=lime>".$text."</font>";
	  } elseif ($colour[value] == 4) {
		$text = "<font color=red>".$text."</font>";
	  }
    }
  }

#send message
	if($target==-2) {
		db2(__FILE__,__LINE__,"select login_id from ${db_name}_users where clan_id='$clan_id'");
		$target_member = dbr2();
		while($target_member) {
			send_message($target_member[login_id],$text);
			$target_member = dbr2();
		}
		$error_str = "Message sent to your clan.";
	} elseif($target==-4) {
  		$text = "Message to <b class=b1>all players</b> from <b class=b1>Admin</b>:<br />".$text;
		db2(__FILE__,__LINE__,"select login_id from ${db_name}_users");
		while($players = dbr2()) {
			send_message($players[login_id],$text);
		}
		$error_str = "Message sent to all players.";

	} elseif($target==-5 && $user[bounty] > 0 && $user[login_id] == 1) {
		$temp_4323 = $user[clan_id];
		$user[clan_id] = $user[bounty];
		send_message($target,$text);
		$user[clan_id] = $temp_4323;
		$error_str = "Message sent.";
	} else {
		send_message($target,$text);
		$error_str = "Message sent.";
	}
}

if($target == -1) {
  $error_str .= "<br /><br /><a href='forum.php'>Back to Forum</a>";
} elseif($target == -2) {
  $error_str .= "<br /><br /><a href='clan.php'>Back to Clan Control</a>";
} elseif($target == -5) {
  $error_str .= "<br /><br /><a href='forum.php?clan_forum=1'>Back to Clan
Forum</a>";
} else {
	$error_str .= "<br /><br /><a href='mpage.php'>Back to Messages Page</a>";
}

# -4 is used to send messages to all players.
// print page
print_page("Send Message",$error_str);
?>
