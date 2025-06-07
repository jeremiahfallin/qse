<?php
include_once("includes/nocache.inc.php");
$filename = "forum_clan.php";
/*
//
File:			forum_clan.php
Objective:		Rewritten version of the clan forum
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	2 March 2004		Date Modified:	29 April 2004

Copyright (c) 2003, 2004 by Pádraic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

// set the Return String $rs to diplay an exit link to the location page
$rs = "<p><br /><a href=\"location.php\">Return to Star System</a></p>";

require_once("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

//check for incoming message text
if(isset($_POST['text']))
{
	$forum_text = $_POST['text'];
}

if($user['clan_id'] <= 0 && $user['login_id'] != 1)
{
	print_page("Error!","<p>You may not access this area unless you are the member of a clan.</p>");
}
elseif(isset($_POST['clanid']) && $user['clan_id'] != $_POST['clanid'] && $user['login_id'] != 1)
{
	print_page("Error!","<p>You may not access this area unless you are the member of this clan.</p>");
}


if(!isset($_POST['clanid']))
{
	db(__FILE__,__LINE__,"select * from ${db_name}_clans where clan_id = '$user[clan_id]'");
	$clan = dbr();
	if(empty($clan))
	{
		print_page("Error!","<p>You have not chosen a clan forum to view - and since you are not a member of a clan there is nothing here you can see.</p>");
	}
}
elseif($user['login_id'] == 1 && isset($_POST['clanid']))
{
	db(__FILE__,__LINE__,"select * from ${db_name}_clans where clan_id = '$_POST[clanid]'");
	$clan = dbr();
	if(empty($clan))
	{
		print_page("Error!","<p>This clan does not exist. Please select a clan from the drop down list on the previous menu.</p>");
	}
}
else
{
	print_page("Clan Forums","<p>Sorry, but clan forums are private and non-members may not access a clan's internal messaging.</p>");
}


// Page heading + Sub-Menu
$text = Create_PageTitleBlock("$clan[clan_name] (<span style=\"color: $clan[sym_color];\">$clan[symbol]</span>) Forum",array("View Clan Messages" => "forum_clan.php", "Post New Message" => "forum_clan.php?new=1"));

// check if player/admin has requested to search the forum
if(isset($_GET['search']))
{
	$text .= "Please enter your search term for Clan Messages:<br /><br />";
	$text .= "<form method=\"post\" action=\"forum_clan.php\">";
	$text .= "<input type=\"text\" name=\"term\" size=\"20\" />";
	$text .= "<br /><input type=\"submit\" value=\"Search\" /></form><br /><br />";
	print_page("Forum Search",$text);
}

// check if player wishes to post a new message
if(isset($_GET['new']))
{
	$text .= RequestMessage_GameForum("forum_clan.php?new_p=1","What is your message to the $clan[clan_name] Forum?",'msg','');
	print_page("Post New Message",$text);
}


// check if player wishes to reply to a message, i.e. on same subject
if(isset($_GET['reply']))
{
	db(__FILE__,__LINE__,"select subject from ${db_name}_clan_forum where message_id = '$reply' and clan_id = '$user[clan_id]'");
	$reply_subj = dbr();
	$reply_subj['subject'] = eregi_replace("Re: ", "", $reply_subj['subject']);
	$text .= RequestMessage_GameForum("forum_clan.php?new_p=1&amp;reply_to=$reply","<p>What is your message to the $clan[clan_name] Forum?</p>",'msg','',"Re: ".$reply_subj['subject']);
	print_page("Post Reply Message",$text);
}


// check if player/admin wishes to edit a post
if(isset($_GET['edit']))
{
	db(__FILE__,__LINE__,"select message_id, subject, text, login_id from ${db_name}_clan_forum where message_id = '$_GET[edit]' and clan_id = '$user[clan_id]'");
	$edit_subj = dbr();
	if($user['login_id'] == $edit_subj['login_id'])
	{
		$edit_subj['text'] = strip_tags($edit_subj['text']);
		$text .= RequestMessage_GameForum("forum_clan.php?new_p=1&amp;edit_notime=$edit_subj[message_id]","Please edit your message and re-submit.",'msg',"$edit_subj[text]","$edit_subj[subject]");
		print_page("Edit Message",$text);
	}
	else
	{
		print_page("Message Error",$text."You have no authority to edit this Forum Message.");
	}
}


// check if a new message is being submitted and store if yes
if(isset($_GET['new_p']) && isset($forum_text) && $forum_text != "")
{
	$forum_text = Clean_Text($forum_text);
	if(!isset($_GET['edit_notime']))
	{
		$forum_text = mcit($forum_text);
	}
	$forum_text = addslashes($forum_text);
	$forum_text = stripslashes($forum_text);
	if(isset($_POST['subject']) && $_POST['subject'] != "")
	{
		$ssubject = $_POST['subject'];
	}
	else
	{
		$ssubject = "&nbsp;";
	}
	if(isset($_GET['edit_notime']))
	{
		dbn(__FILE__,__LINE__,"update ${db_name}_clan_forum set text = '$forum_text' where message_id = '$_GET[edit_notime]' and login_id = '$user[login_id]' and clan_id = '$user[clan_id]'");
	}
	else
	{
		dbn(__FILE__,__LINE__,"insert into ${db_name}_clan_forum (sender_name, timestamp, login_id, clan_id, subject, text, reply_to) values('$user[login_name]', '".time()."', '$user[login_id]', '$user[clan_id]', '$ssubject', '$forum_text', '$_GET[reply_to]')");
	}
	print_page("Message Posted",$text."Your Message to the Forum has been posted.");
}
elseif(isset($_GET['new_p']) && $forum_text == "")
{
	print_page("Message Error",$text."The Forum Message you submitted contained no text! Empty messages will not be posted to the Forum.");
}



// check if player or admin deleting a message
if(isset($_GET['killmesg']) || isset($_POST['killmesg']))
{
	if(isset($_GET['killmesg']))
	{
		$mid = $_GET['killmesg'];
	}
	else
	{
		$mid = $_POST['killmesg'];
	}
	db(__FILE__,__LINE__,"select login_id from ${db_name}_clan_forum where message_id = '$mid'");
	$lid = dbr();
	if($user['login_id'] == $lid['login_id'] || $user_perm['forum'] == 1)
	{
		if(!isset($sure))
		{
			get_var('Delete Message','forum_clan.php',$text."Are you certain you want to delete this Forum Message?",'sure','yes');
		}
		else
		{
			dbn(__FILE__,__LINE__,"delete from ${db_name}_clan_forum where message_id = '$_POST[killmesg]'");
			print_page("Message Deleted",$text."The Forum Message was deleted.");
		}
	}
	else
	{
		print_page("Error",$text."You have no authority to delete this post.");
	}
}

// check if admin wishes to delete all messages
if((isset($_GET['killallmesg']) || isset($_POST['killallmesg'])) && $user_perm['forum'] == 1)
{
	if(!isset($sure))
	{
		get_var('Delete Message','forum_clan.php',$text."Are you certain you want to delete <em>all</em> Forum Messages?",'sure','yes');
	}
	else
	{
		dbn(__FILE__,__LINE__,"delete * ${db_name}_clan_forum");
		print_page("All Messages Deleted",$text."All Forum Messages have been deleted.");
	}
}


// check for any search terms submitted
if(isset($_POST['term'])) {
	$text .= Forum_Search($_POST['term']);
	print_page("Forum Search Results",$text);
}


//Add message to diary.
if(isset($_GET['log_ent'])){
	db(__FILE__,__LINE__,"select count(entry_id) as ents from ${db_name}_diary where login_id = '$user[login_id]'");
	$ents = dbr();
	if($ents['ents'] >= 200)
	{
		print_page("Diary Full",$text."Your Diary is full. You will need to delete entries before logging any more messages.");
	}
	db(__FILE__,__LINE__,"select text, login_id from ${db_name}_clan_forum where message_id = '$_GET[log_ent]' and clan_id = '$user[clan_id]'");
	$f_message = dbr();
	$message_author = addslashes(stripslashes(print_name($f_message)));
	$message_text = addslashes(stripslashes($f_message['text']));
	dbn(__FILE__,__LINE__,"insert into ${db_name}_diary (timestamp,login_id, entry, topic) values(".time().",'$user[login_id]','$message_author - $message_text','messages')");
	print_page("Diary Updated",$text."The message has been logged in your Diary.<br /><br />");
}


// perform default forum view action if no other action chosen
$text .= "<br />
<table cellspacing=\"0\" cellpadding=\"0\" width=\"650\">
	<tr>
		<td>
";
$text .= PrintMessages_GameForum("",$_GET['read_back'] ,$_GET['last_increment']);
$text .= "
		</td>
	</tr>
</table>
";
$text .= "End of Game Forum";
print_page("In-Game Forum",$text);














// functions

function RequestMessage_GameForum($page_name,$text,$var_name,$var_default,$ssubject,$message_id) {
	global $rs, $border_prop, $url_prefix, $buy, $user;

	$ostr = "<form name=\"get_var_form\" action=\"$page_name\" method=\"post\" onSubmit=\"submitonce(this);\">";
	$ostr .= "$text<br /><br />";
	while (list($var, $value) = each($_GET)) {
		$ostr .= "<input type=\"hidden\" name=\"$var\" value=\"$value\" />";
	}
	while (list($var, $value) = each($_POST)) {
		$ostr .= "<input type=\"hidden\" name=\"$var\" value=\"$value\" />";
	}
	$subject_line = "<div style=\"font-size: 10pt;\">Subject:</div> <input type=\"text\" size=\"40\" maxlength=\"25\" value=\"$ssubject\" id=\"subject\" name=\"subject\" /><br />";
	if($var_name == 'msg') { //output phpBB text options
		$ostr .= "
				<table border=\"0\" cellspacing=\"1\" cellpadding=\"2\">
					<tr>
						<td colspan=\"7\">
							<div style=\"text-align: left;\">$subject_line<br />
						</td>
					</tr>
					<tr>
						<td>
							<table border=\"0\" cellspacing=\"1\" cellpadding=\"2\" width=\"100%\">
								<tr>
									<td>
										<input type='button' class='button' accesskey='b' name='addbbcode0' value=' B ' style='font-weight:bold; width: 30px' onClick='bbstyle(0)' />
									</td>
									<td>
										<input type='button' class='button' accesskey='i' name='addbbcode2' value=' i ' style='font-style:italic; width: 30px' onClick='bbstyle(2)' />
									</td>
									<td>
										<input type='button' class='button' accesskey='bi' name='addbbcode4' value=' Bi ' style='font-style:italic; font-weight:bold; width: 30px' onClick='bbstyle(4)' />
									</td>
									<td>
										<input type='button' class='button' accesskey='u' name='addbbcode6' value=' u ' style='text-decoration: underline; width: 30px' onClick='bbstyle(6)' />
									</td>
									<td>
										<input type='button' class='button' accesskey='p' name='addbbcode8' value='Img' style='width: 40px'  onClick='bbstyle(8)' />
									</td>
									<td>
										<input type='button' class='button' accesskey='w' name='addbbcode10' value='URL' style='text-decoration: underline; width: 40px' onClick='bbstyle(10)' />
									</td>
									<td>
	<select name=\"colorchanger\" onChange=\"bbfontstyle(this.form.colorchanger.options[this.form.colorchanger.selectedIndex].value, '[/color]')\" >
		<option style='color:black; background-color: #FFFFFF' value='#FFFFFF' class=\"genmed\">Default</option>
		<option style='color:darkred; background-color: #DEE3E7' value='darkred' class=\"genmed\">Dark Red</option>
		<option style='color:red; background-color: #DEE3E7' value='red' class=\"genmed\">Red</option>
		<option style='color:orange; background-color: #DEE3E7' value='orange' class=\"genmed\">Orange</option>
		<option style='color:brown; background-color: #DEE3E7' value='brown' class=\"genmed\">Brown</option>
		<option style='color:yellow; background-color: #DEE3E7' value='yellow' class=\"genmed\">Yellow</option>
		<option style='color:green; background-color: #DEE3E7' value='green' class=\"genmed\">Green</option>
		<option style='color:olive; background-color: #DEE3E7' value='olive' class=\"genmed\">Olive</option>
		<option style='color:cyan; background-color: #DEE3E7' value='cyan' class=\"genmed\">Cyan</option>
		<option style='color:blue; background-color: #DEE3E7' value='blue' class=\"genmed\">Blue</option>
		<option style='color:darkblue; background-color: #DEE3E7' value='darkblue' class=\"genmed\">Dark Blue</option>
		<option style='color:indigo; background-color: #DEE3E7' value='indigo' class=\"genmed\">Indigo</option>
		<option style='color:violet; background-color: #DEE3E7' value='violet' class=\"genmed\">Violet</option>
		<option style='color:white; background-color: #DEE3E7' value='white' class=\"genmed\">White</option>
		<option style='color:black; background-color: #DEE3E7' value='black' class=\"genmed\">Black</option>
	</select>
									</td>
									<td>
										<a href=\"javascript:bbstyle(-1)\">Close Tags</a>
									</td>
								</tr>
							</table>
							<textarea name=\"text\" cols=\"55\" rows=\"16\" wrap=\"virtual\">".stripslashes($var_default)."</textarea>
						</td>
						<td width=\"100\" valign=\"top\">
							<div align=\"center\"><br /><br />
							<table cellspacing=\"0\" cellpadding=\"2\" width=\"100\" class=\"all\">
								<tr>
									<th colspan=\"2\">Emoticons</th>
								</tr>
								<tr align='center' valign='middle'>
                        <td width=\"50\" class=\"emot_td\"><a href=\"javascript:emoticon('[smile]')\"><img src='images/smiles/lol.gif' border='0' alt='Smile' title='Smile' /></a></td>
                        <td width=\"50\" class=\"notopright\"><a href=\"javascript:emoticon('[sad]')\"><img src='images/smiles/sad.gif' border='0' alt='Sad' title='Sad' /></a></td>
								</tr>
								<tr align='center' valign='middle'>
                        <td width=\"50\" class=\"emot_td\"><a href=\"javascript:emoticon('[surp]')\"><img src='images/smiles/surp.gif' border='0' alt='Surprised' title='Surprised' /></a></td>
                        <td width=\"50\" class=\"notopright\"><a href=\"javascript:emoticon('[help]')\"><img src='images/smiles/help.gif' border='0' alt='Help' title='Help' /></a></td>
								</tr>
								<tr align='center' valign='middle'>
                        <td width=\"50\" class=\"emot_td\"><a href=\"javascript:emoticon('[cool]')\"><img src='images/smiles/cool.gif' border='0' alt='Cool' title='Cool' /></a></td>
                        <td width=\"50\" class=\"notopright\"><a href=\"javascript:emoticon('[check]')\"><img src='images/smiles/check.gif' border='0' alt='Check This' title='Check This' /></a></td>
								</tr>
								<tr align='center' valign='middle'>
                        <td width=\"50\" class=\"emot_td\"><a href=\"javascript:emoticon('[wink]')\"><img src='images/smiles/wink.gif' border='0' alt='Wink' title='Wink' /></a></td>
                        <td width=\"50\" class=\"notopright\"><a href=\"javascript:emoticon('[wtf]')\"><img src='images/smiles/wtf.gif' border='0' alt='WTF' title='WTF' /></a></td>
								</tr>
								<tr align='center' valign='middle'>
                        <td width=\"50\" class=\"emot_td\"><a href=\"javascript:emoticon('[evil]')\"><img src='images/smiles/evil.gif' border='0' alt='Evil' title='Evil' /></a></td>
                        <td width=\"50\" class=\"notopright\"><a href=\"javascript:emoticon('[tongue]')\"><img src='images/smiles/tongue.gif' border='0' alt='Tongue' title='Tongue' /></a></td>
								</tr>
								<tr align='center' valign='middle'>
                        <td width=\"50\" class=\"emot_td\"><a href=\"javascript:emoticon('[mad]')\"><img src='images/smiles/mad.gif' border='0' alt='Mad' title='Mad' /></a></td>
                        <td width=\"50\" class=\"notopright\"><a href=\"javascript:emoticon('[ass]')\"><img src='images/smiles/ass.gif' border='0' alt='Ass' title='Ass' /></a></td>
								</tr>
								<tr align='center' valign='middle'>
                        <td width=\"50\" class=\"emot_td\"><a href=\"javascript:emoticon('[mad67]')\"><img src='images/smiles/mad67.gif' border='0' alt='Mad2' title='Mad2' /></a></td>
                        <td width=\"50\" class=\"notopright\"><a href=\"javascript:emoticon('[scream]')\"><img src='images/smiles/scream.gif' border='0' alt='Scream' title='scream' /></a></td>
								</tr>
								<tr align='center' valign='middle'>
                        <td width=\"50\"><a href=\"javascript:emoticon('[upto]')\"><img src='images/smiles/upto.gif' border='0' alt='Up To' title='Up To' /></a></td>
						<td width=\"50\" class=\"border_left\"><a href=\"javascript:emoticon('[thumb]')\"><img src='images/smiles/thumb.gif' border='0' alt='Thumbs Up' title='Thumbs Up' /></a></td>
								</tr>
                                                                <tr align='center' valign='middle'>
                        <td width=\"50\"><a href=\"javascript:emoticon('lol')\"><img src='images/smiles/lold.gif' border='0' alt='lol' title='lol' /></a></td>                                                <td width=\"50\" class=\"border_left\"></td>
                                                                </tr>

							</table>
							<table cellspacing=\"0\" cellpadding=\"2\" width=\"100\">
								<tr>
									<td width=\"50\" style=\"text-align: center;\">
										<br />
										<input type=\"submit\" value=\"Submit\" /><br /><br />
										<input type=\"reset\" value=\"Reset\" /><br />
									</td>
								</tr>
							</table>
		</table></div></td></tr>
						</table>";

		$ostr .= "<input type=\"hidden\" name=\"rs\" value=\"".htmlentities($rs)."\" />";
		$ostr .= '</form>';

	}
	$ostr .= "<script> document.get_var_form.$var_name.focus(); </script>";
	return $ostr;
}







function PrintMessages_GameForum($back_hours, $read_back=0, $last_increment) {
	global $db_name, $user, $user_perm, $user_options, $allow_signatures, $clan, $PRINT_NAME_CACHE;
	$back_hours = $user_options['forum_back'];
	if($read_back > 0)
	{
		db(__FILE__,__LINE__,"select * from ${db_name}_clan_forum where timestamp >= (".time()." - (($back_hours + $last_increment) * 3600)) and timestamp <= (".time()." - ($last_increment * 3600)) and clan_id = '$user[clan_id]' order by timestamp desc");
		$last_increment = $last_increment + $back_hours;
	}
	elseif($read_back == -1)
	{
		db(__FILE__,__LINE__,"select * from ${db_name}_clan_forum where timestamp >= '$user[last_access_clan_forum]' and clan_id = '$user[clan_id]' order by timestamp desc");
		$last_increment = "";
		$read_back = "";
	}
	else
	{
		db(__FILE__,__LINE__,"select * from ${db_name}_clan_forum where timestamp >= (".time()." - (".$back_hours." * 3600)) and clan_id = '$user[clan_id]' order by timestamp desc");
		$last_increment = $back_hours;
		$read_back = 1;
	}
	while($f_msgs = dbr()) {
		$author = print_name($f_msgs);
		$author .= "<br />";
		if($user['login_id'] == $clan['leader_id'])
		{
			$author .= "<br /><span style=\"color: orange;\">Clan Leader</span>";
		}
		else
		{
			$author .= "<br /><span style=\"color: blue;\">Clan Member</span>";
		}
		if($f_msgs['reply_to'] > 0)
		{
			$rlink = "<a href=\"#".$f_msgs['reply_to']."\">View Message Replied To</a>&nbsp;-";
		}
		else
		{
			unset($rlink);
		}
		if(($allow_signatures == 1) && ($user_options['show_sigs'] == 1))
		{
			db2(__FILE__,__LINE__,"select sig from ${db_name}_users where login_id = '$f_msgs[login_id]'");
			$signat = dbr2(); $sig1 = $signat['sig'];
		}
		$text .= "
			<a id=\"$f_msgs[message_id]\" name=\"$f_msgs[message_id]\"></a>
			<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" class=\"menu_border\">
				<tr>
					<td rowspan=\"3\">
						<table cellspacing=\"0\" cellpadding=\"2\" width=\"80\">
							<tr>
								<td style=\"text-align: center;\">
									$author<br />
								</td>
							<tr>
						</table>
					</td>
					<td>
						<table cellspacing=\"0\" cellpadding=\"0\" width=\"570\" class=\"border_left\">
							<tr>
								<td>
									<table cellspacing=\"0\" cellpadding=\"2\" width=\"100%\">
										<tr>
											<th class=\"forumhead\" style=\"text-align: left;\">
												$f_msgs[subject]
											</th>
											<th class=\"forumhead\" style=\"text-align: right;\">
												".date("F j, Y, g:i a",$f_msgs[timestamp])."
											</th>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table cellspacing=\"0\" cellpadding=\"2\" width=\"100%\">
										<tr>
											<td>
												$f_msgs[text]
												<br /><br />
												$sig1
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table cellspacing=\"0\" cellpadding=\"2\" width=\"100%\" class=\"border_top\">
										<tr>
											<td style=\"text-align: center;\">
		";
		if($user['login_id'] != $f_msgs['login_id'])
		{
			$text .= "
												-&nbsp;<a href=\"message.php?target=$f_msgs[login_id]\">Message Author</a>&nbsp;
			";
		}
		$text .= "
												-&nbsp;<a href=\"forum_clan.php?reply=$f_msgs[message_id]\">Reply</a>&nbsp;
												-&nbsp;<a href=\"javascript:void();\">Forward</a>&nbsp;
												-&nbsp;<a href=\"forum_clan.php?log_ent=$f_msgs[message_id]\">Log</a>&nbsp;
		";
		if($f_msgs['login_id'] == $user['login_id'] || $user_perm['forum'] == 1)
		{
			$text .= "
												-&nbsp;<a href=\"forum_clan.php?edit=$f_msgs[message_id]\">Edit</a>&nbsp;
												-&nbsp;<a href=\"forum_clan.php?killmesg=$f_msgs[message_id]\">Delete</a>&nbsp;
			";
		}
		$text .= "
												-&nbsp;
												$rlink
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<br />
		";
	}
	$text .= "<a href=\"forum_clan.php?read_back=$read_back&amp;last_increment=$last_increment\">Messages from previous $back_hours Hours</a><br /><br />";
	dbn(__FILE__,__LINE__,"update ${db_name}_users set last_access_clan_forum='".time()."' where login_id='$user_options[login_id]'");
	return $text;
}


function Forum_Search($phrase){
	global $db_name, $user, $user_perm, $user_options, $PRINT_NAME_CACHE;
	db(__FILE__,__LINE__,"select * from ${db_name}_clan_forum where text like '%$phrase%' or subject like '%$phrase%' and clan_id = '$user[clan_id]' order by timestamp desc");
	$result_pre = dbr();
	if(empty($result_pre)) {
		$output = "<form method=\"post\" action=\"forum_clan.php\" name=\"search_form\">";
		$output .= "Forum Search: <input type=\"text\" name=\"term\" size=\"20\" value=\"$phrase\" />";
		$output .= " - <input type=submit value=Search></form><p>";
		$output .= "<br />Forum Messages containing the term <b class=b1>$phrase</b> could not be found. Please try another phrase if applicable.<br /><br />";
	} else {
		$output = "<form method=\"post\" action=\"forum_clan.php\" name=\"search_form\">";
		$output .= "New Search: <input type=\"text\" name=\"term\" size=\"20\" value=\"$phrase\" />";
		$output .= " - <input type=\"submit\" value=\"Search\" /></form>";
		$output .= "<br />Shown below are the results for your search using terms (<b class=b1>$phrase</b>):<br />";
		$output .= "
			<br />
				<table cellspacing=\"0\" cellpadding=\"0\" width=\"650\">
					<tr>
						<td>
		";
		$patterns = array ("/a/","/b/","/c/","/d/","/e/","/f/","/g/","/h/","/i/","/j/","/k/","/l/","/m/","/n/", "/o/","/p/","/q/","/r/","/s/","/t/","/u/","/v/","/w/","/x/","/y/","/z/");
		$replace = array ("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		db(__FILE__,__LINE__,"select * from ${db_name}_clan_forum where text like '%$phrase%' or subject like '%$phrase%' and clan_id = '$user[clan_id]' order by timestamp desc");
		while($result = dbr()) {
			$string = preg_replace ($patterns, $replace, $phrase);
			$result['text'] = eregi_replace($phrase, "<span style=\"color: lime; background-color: blue;\">&nbsp;$string&nbsp;</span>", $result['text']);
			$result['subject'] = eregi_replace($phrase, "<span style=\"color: lime; background-color: blue;\">&nbsp;$string&nbsp;</span>", $result['subject']);
			$author = print_name($result);
			$author .= "<br />";
			if($user['login_id'] == $clan['leader_id'])
			{
				$author .= "<br /><span style=\"color: green;\">Clan Leader</span>";
			}
			else
			{
				$author .= "<br /><span style=\"color: blue;\">Clan Member</span>";
			}
			if($result['reply_to'] > 0)
			{
				$rlink = "<br /><a href=\"#".$result['reply_to']."\">View Message Replied To</a>";
			} else {unset($rlink);}
			$output .= "
				<a id=\"$result[message_id]\" name=\"$result[message_id]\"></a>
				<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" class=\"menu_border\">
					<tr>
						<td rowspan=\"3\">
							<table cellspacing=\"0\" cellpadding=\"2\" width=\"80\">
								<tr>
									<td style=\"text-align: center;\">
										$author<br />
									</td>
								<tr>
							</table>
						</td>
						<td>
							<table cellspacing=\"0\" cellpadding=\"0\" width=\"570\" class=\"border_left\">
								<tr>
									<td>
										<table cellspacing=\"0\" cellpadding=\"2\" width=\"100%\">
											<tr>
												<th class=\"forumhead\" style=\"text-align: left;\">
													$result[subject]
												</th>
												<th class=\"forumhead\" style=\"text-align: right;\">
													".date("F j, Y, g:i a",$result[timestamp])."
												</th>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td>
										<table cellspacing=\"0\" cellpadding=\"2\" width=\"100%\">
											<tr>
												<td>
													$result[text]
													<br /><br />
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td>
										<table cellspacing=\"0\" cellpadding=\"2\" width=\"100%\" class=\"border_top\">
											<tr>
												<td style=\"text-align: center;\">
													-&nbsp;<a href=\"message.php?target=$result[login_id]\">Message Author</a>&nbsp;
													-&nbsp;<a href=\"forum_clan.php?reply=$result[message_id]\">Reply</a>&nbsp;
													-&nbsp;<a href=\"javascript:void();\">Forward</a>&nbsp;
													-&nbsp;<a href=\"forum_clan.php?log_ent=$result[message_id]\">Log</a>&nbsp;
			";
			if($result['login_id'] == $user['login_id'] || $user_perm['forum'] == 1)
			{
				$output .= "
													-&nbsp;<a href=\"forum_clan.php?edit=$result[message_id]\">Edit</a>&nbsp;
													-&nbsp;<a href=\"forum_clan.php?killmesg=$result[message_id]\">Delete</a>&nbsp;
				";
			}
			$output .= "
													-&nbsp;
													$rlink
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<br />
			";
		}
		$text .= "
						</td>
					</tr>
				</table>
		";
	}
	return $output;
}



























?>
