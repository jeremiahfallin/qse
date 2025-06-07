<?php
/* File optimised */
require_once("common.inc.php");

define('ACP_VER', 'V0.7.2');
//array_push($FILE_LIST, basename(__FILE__));

// check for login_id cookie
if($login_id == 0 || empty($db_name) || !isset($login_id)) {
	 echo "<script>self.location='$url_prefix/login_form.php';</script>";
	 exit;
}

mt_srand((double)microtime()*1000000);

// retrieve the user data
$db = db_connect($database_host,$database_user,$database_password,$database,$database_persistent);
db(__FILE__,__LINE__,"select * from ${db_name}_users where login_id = '${_SESSION['login_id']}'");
$user = dbr();
db(__FILE__,__LINE__,"select * from ${db_name}_permissions where login_id = '${user['login_id']}'");
$user_perm = dbr();
db(__FILE__,__LINE__,"select * from user_accounts where login_id = '${user['login_id']}'");
$p_user = dbr();



	$check = new Q_AuthCheck();
	$check->Check_Auth($p_user);



// retrieve more user data.
db(__FILE__,__LINE__,"select * from ${db_name}_user_options where login_id = '${user['login_id']}'");
$user_options = dbr();

//UPDATE last request
dbn(__FILE__,__LINE__,"update ${db_name}_users set last_request = ".time()." where login_id = '${user[login_id]}'");

$PRINT_NAME_CACHE = array();

// -----------------------------------------------------------------
# check for variable lookup method (read from database or read from file)
if($var_source == 0)
{
	require("$map_path/$db_name/db_vars.inc.php");
}
else
{
	db2(__FILE__,__LINE__,"select name,value from ${db_name}_db_vars order by name");
	while($var_list = dbr2())
	{
		$$var_list['name'] = $var_list['value'];
	}
}

// Print admin pages structure
function print_menu() {
	GLOBAL $file_name;
	// Create the Menu object and the menu tree
	$menu00  = new HTML_TreeMenuXL();

	// Set general node properties
	$nodeProperties = array();

	// Creating menu nodes
	$node = new HTML_TreeNodeXL('Admin Home', 'index.php', $nodeProperties);

	$node0 = new HTML_TreeNodeXL('<b>Configuration Variables</b>', '', $nodeProperties);
	  	$node0->addItem(new HTML_TreeNodeXL('All Variables', 'admin_vars.php', $nodeProperties));
		$node0->addItem(new HTML_TreeNodeXL('Universe Generation', 'admin_vars.php?type_var=3', $nodeProperties));
		$node0->addItem(new HTML_TreeNodeXL('General Game Options', 'admin_vars.php?type_var=1', $nodeProperties));
		$node0->addItem(new HTML_TreeNodeXL('Gameplay', 'admin_vars.php?type_var=2', $nodeProperties));
		$node0->addItem(new HTML_TreeNodeXL('Gameplay II', 'admin_vars.php?type_var=8', $nodeProperties));
		$node0->addItem(new HTML_TreeNodeXL('Mining, Material and Production', 'admin_vars.php?type_var=4', $nodeProperties));
		$node0->addItem(new HTML_TreeNodeXL('Weaponry Options', 'admin_vars.php?type_var=5', $nodeProperties));
		$node0->addItem(new HTML_TreeNodeXL('Player Attacking Variables', 'admin_vars.php?type_var=6', $nodeProperties));
		$node0->addItem(new HTML_TreeNodeXL('Blackmarket Module Options', 'admin_vars.php?type_var=7', $nodeProperties));
		$node0->addItem(new HTML_TreeNodeXL('Homeworlds', 'admin_vars.php?type_var=9', $nodeProperties));

	$node1 = new HTML_TreeNodeXL('<b>Game Controls</b>', '', $nodeProperties);
		$node1->addItem(new HTML_TreeNodeXL('Generate New Universe', 'unigen.php?build_universe=1&process=1', $nodeProperties));
		$node1->addItem(new HTML_TreeNodeXL('Manipulate Star Links', 'cut_link.php', $nodeProperties));
		$node1->addItem(new HTML_TreeNodeXL('Regenerate Universe Maps', 'admin_regen_maps.php', $nodeProperties));
		$node1->addItem(new HTML_TreeNodeXL('Ship Types', 'ship_types.php', $nodeProperties));
		$node1->addItem(new HTML_TreeNodeXL('Change Days Until Reset', 'days_reset.php', $nodeProperties));
		$node1->addItem(new HTML_TreeNodeXL('Change Days Until SD', 'days_sd.php', $nodeProperties));
		$node1->addItem(new HTML_TreeNodeXL('Trigger Apocolypse', 'trigger_apocolypse.php', $nodeProperties));
		$node1->addItem(new HTML_TreeNodeXL('Reset Game', 'reset_game.php', $nodeProperties));

	$node2 = new HTML_TreeNodeXL('<b>Communications</b>', '', $nodeProperties);
		$node2->addItem(new HTML_TreeNodeXL('Change Admin Name', 'admin_name.php', $nodeProperties));
		$node2->addItem(new HTML_TreeNodeXL('Change Admin E-mail', 'admin_email.php', $nodeProperties));
		$node2->addItem(new HTML_TreeNodeXL('Change Stated Difficulty', 'change_dif.php', $nodeProperties));
		$node2->addItem(new HTML_TreeNodeXL('Change Game Description', 'game_desc.php', $nodeProperties));
		$node2->addItem(new HTML_TreeNodeXL('Change Intro Message', 'intro_msg.php', $nodeProperties));
		$node2->addItem(new HTML_TreeNodeXL('Message All Players', 'message.php?target=-4', $nodeProperties));
		$node2->addItem(new HTML_TreeNodeXL('Post In-game News', 'game_news.php', $nodeProperties));
		$node2->addItem(new HTML_TreeNodeXL('Post Server News', 'post_qs_news.php?qs_post_news=1', $nodeProperties));

	$node3 = new HTML_TreeNodeXL('<b>Player Controls</b>', '', $nodeProperties);
		$node3->addItem(new HTML_TreeNodeXL('Multi Scanner', 'multiscan.php', $nodeProperties));
		$node3->addItem(new HTML_TreeNodeXL('Ban Player', 'ban_control.php', $nodeProperties));
		$node3->addItem(new HTML_TreeNodeXL('List Online Players', '../active_users.php', $nodeProperties));
		$node3->addItem(new HTML_TreeNodeXL('Update Scores', 'upd_scores.php', $nodeProperties));
		$node3->addItem(new HTML_TreeNodeXL('Give Money to All', 'give_money.php', $nodeProperties));
		$node3->addItem(new HTML_TreeNodeXL('Reset Signup Times', 'reset_signup.php', $nodeProperties));

	$node4 = new HTML_TreeNodeXL('<b>Reports</b>', '', $nodeProperties);
		$node4->addItem(new HTML_TreeNodeXL('Player Planets', 'planets.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('UniGen Planets', 'planets2.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Ports', 'ports.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Blackmarkets', 'bmkrt.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Alien Shipyards', 'shipyards.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Star Systems', 'systems.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Random Events', 'random_events.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Powerups', 'powerups.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Fleets', 'fleets.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Ships', 'ships.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Transfers', 'transfers.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Won Bilkos Items', 'bilkos.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Players', 'players.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Clans', 'clans.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Relations', 'relations.php', $nodeProperties));
		$node4->addItem(new HTML_TreeNodeXL('Current Cash Scale', 'cash_scale.php', $nodeProperties));

//	$node6 = new HTML_TreeNodeXL('Adodb PerfMon', 'adodb_perfmon.php?op=menu', $nodeProperties);
	$node7 = new HTML_TreeNodeXL('Back to Star System', '../location.php', $nodeProperties);

	// Adding nodes to menu
	$menu00->addItem($node);
	$menu00->addItem($node0);
	$menu00->addItem($node1);
	$menu00->addItem($node2);
	$menu00->addItem($node3);
	$menu00->addItem($node4);
	$menu00->addItem($node);
//	$menu00->addItem($node6);
	$menu00->addItem($node7);

	// Generating menu style
	$file_name = (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') ? basename($_SERVER['PHP_SELF']).'?'.$_SERVER['QUERY_STRING'] : basename($_SERVER['PHP_SELF']);
	$admin010 = &new HTML_TreeMenu_DHTMLXL($menu00, array('images'=>'./lib/TMimages','linkSelectKey'=>$file_name,'autostyles'=>array('smalltextBold', 'smallitalic', 'smalltext', 'xsmalltext')));

	echo '<table cellspacing="8" cellpadding="0" width="100%" align="center" border="0"><tr><td width="200" valign="top">';
	echo '<table cellspacing="0" cellpadding="0" width="200" align="center" border="0" class="all"><tr><th valign="top">Menu</th></tr><tr><td>';
	$admin010->printMenu();
	echo '</td></tr></table></td><td valign="top">';
}

// function to print out all output within the Generic page structure
function print_page($title,$text,$text2='') {
	global $rs, $start_time, $code_base, $HTTP_ACCEPT_ENCODING, $enable_gzip, $gzip_level, $db_name, $user, $filename, $user_options, $CONFIG;

	if($enable_gzip == 1) {		include('../includes/gzdoc.inc.php');		}

	print_header($title);
	include_once('../themes/'.$user_options['theme'].'/header.php');
	print_menu();
	// insert translation step
	//require_once("includes/language/example.inc.php");
	//$text = translate($text);
	// end of translation
	echo '<table cellspacing="0" cellpadding="0" width="100%" align="center" border="0" class="all"><tr><th valign="top">AdminCP '.ACP_VER.'</th></tr><tr><td valign="top">';
	echo $text;
	if($text2 != '') {
		echo '</td></tr></table></td><td width="200" valign="top">'.$text2;
	}
	echo '<p><a href="#top">Back to Top</a></p></td></tr></table>';
	include_once('../themes/'.$user_options['theme'].'/footer.php');

	if($enable_gzip == 1) {		GzDocOut($gzip_level);	}

	exit();
}

// function designed for printing within a popup, removes status, and header includes
function print_page_popup($title,$text) {
	global $rs, $start_time, $code_base, $HTTP_ACCEPT_ENCODING, $enable_gzip, $gzip_level, $db_name, $user, $user_options;
	if($enable_gzip == 1)
	{
		include('../includes/gzdoc.inc.php');
	}
	print_header($title);
	echo $text;
	include_once('themes/'.$user_options['theme'].'/footer.php');
	if($enable_gzip == 1)
	{
		GzDocOut($gzip_level);
	}
	exit();
}


#function that can be used create a viable input form. Adds hidden vars.
function get_var($title,$page_name,$text,$var_name,$var_default,$back_page=null) {
	global $rs,$border_prop,$url_prefix,$buy,$filename;

//	$ostr = '</div><blockquote>';
	$ostr = '<form name="get_var_form" action="'.$page_name.'" method="post" onSubmit="submitonce(this);">';
	$ostr .= '<h3>'.$title.'</h3><p>'.$text.'</p>';
	while (list($var, $value) = each($_GET)) {
		$ostr .= '<input type="hidden" name="'.$var.'" value="'.$value.'" />';
	}
	while (list($var, $value) = each($_POST)) {
		$ostr .= '<input type="hidden" name="'.$var.'" value="'.$value.'" />';
	}
	if($var_name == 'sure') {
		$ostr .= '<input type="hidden" name="sure" value="yes" />';
		if(isset($back_page)) {
			$ostr .= '<input type="submit" name="submit" value="Yes" /> <input type="Button" width="30" value="No" onclick="javascript: self.location=\''.$url_prefix.'/'.$back_page.'\'" /> </form>';
		} else {
			$ostr .= '<input type="submit" name="submit" value="Yes" /> <input type="Button" width="30" value="No" onclick="javascript: history.back()" /> </form>';
		}
	} elseif(($var_name == 'passwd') || ($var_name == 'passwd_verify')) {
		$ostr .= '<input type="password" name="'.$var_name.'" value="'.$var_default.'" size="20" />';
		$ostr .= '<input type="submit" value="Submit" /></form>';
	} elseif($var_name == 'passwd2') {
		$ostr .= '<input type="password" name="'.$var_name.'" value="'.$var_default.'" size="20" />';
		$ostr .= '<input type="submit" value="Submit" /></form>';
	} elseif($var_name == 'text') {
		$ostr .= '<textarea name="'.$var_name.'" cols="50" rows="20" wrap="soft">'.stripslashes($var_default).'</textarea>';
		$ostr .= '<input type="hidden" name="rs" value="'.htmlentities($rs).'" />';
		$ostr .= '<p><input type="submit" value="Submit" /></p></form>';
	} elseif($var_name == 'msg') { //output phpBB text options
		$ostr .= '<table border="0" cellspacing="1" cellpadding="2"><tr><td colspan="7">&nbsp;<a href="javascript:bbstyle(-1)">Close All Tags</a></td></tr><tr>
		<td><table border="0" cellspacing="1" cellpadding="2" width="100%"><tr><td><input type="button" class="button" accesskey="b" name="addbbcode0" value=" B " style="font-weight:bold; width: 30px" onClick="bbstyle(0)" /></td><td><input type="button" class="button" accesskey="i" name="addbbcode2" value=" i " style="font-style:italic; width: 30px" onClick="bbstyle(2)" /></td><td><input type="button" class="button" accesskey="bi" name="addbbcode4" value=" Bi " style="font-style:italic; font-weight:bold; width: 30px" onClick="bbstyle(4)" /></td><td><input type="button" class="button" accesskey="u" name="addbbcode6" value=" u " style="text-decoration: underline; width: 30px" onClick="bbstyle(6)" /></td><td><input type="button" class="button" accesskey="p" name="addbbcode8" value="Img" style="width: 40px"  onClick="bbstyle(8)" /></td><td><input type="button" class="button" accesskey="w" name="addbbcode10" value="URL" style="text-decoration: underline; width: 40px" onClick="bbstyle(10)" /></td><td><select name="colorchanger" onChange="bbfontstyle(this.form.colorchanger.options[this.form.colorchanger.selectedIndex].value, \'[/color]\')"><option style="color:black; background-color: #FFFFFF" value="#FFFFFF" class="genmed">Default</option>
  <option style="color:darkred; background-color: #DEE3E7" value="darkred" class="genmed">Dark Red</option>
  <option style="color:red; background-color: #DEE3E7" value="red" class="genmed">Red</option>
  <option style="color:orange; background-color: #DEE3E7" value="orange" class="genmed">Orange</option>
  <option style="color:brown; background-color: #DEE3E7" value="brown" class="genmed">Brown</option>
  <option style="color:yellow; background-color: #DEE3E7" value="yellow" class="genmed">Yellow</option>
  <option style="color:green; background-color: #DEE3E7" value="green" class="genmed">Green</option>
  <option style="color:olive; background-color: #DEE3E7" value="olive" class="genmed">Olive</option>
  <option style="color:cyan; background-color: #DEE3E7" value="cyan" class="genmed">Cyan</option>
  <option style="color:blue; background-color: #DEE3E7" value="blue" class="genmed">Blue</option>
  <option style="color:darkblue; background-color: #DEE3E7" value="darkblue" class="genmed">Dark Blue</option>
  <option style="color:indigo; background-color: #DEE3E7" value="indigo" class="genmed">Indigo</option>
  <option style="color:violet; background-color: #DEE3E7" value="violet" class="genmed">Violet</option>
  <option style="color:white; background-color: #DEE3E7" value="white" class="genmed">White</option>
  <option style="color:black; background-color: #DEE3E7" value="black" class="genmed">Black</option>
</select></td></tr></table><textarea name="text" cols="50" rows="15" wrap="virtual">'.stripslashes($var_default).'</textarea><p><input type="submit" value="Submit"></td><td width="100" valign="top"><div align="center"><br /><br /><table cellspacing="0" cellpadding="2" width="100" class="all"><tr><th colspan="2">Emoticons</th></tr>
		              <tr align="center" valign="middle">
                        <td width="50" class="emot_td"><a href="javascript:emoticon(\'[smile]\')"><img src="images/smiles/lol.gif" border="0" alt="Smile" title="Smile" /></a></td>
                        <td width="50" class="notopright"><a href="javascript:emoticon(\'[sad]\')"><img src="images/smiles/sad.gif" border="0" alt="Sad" title="Sad" /></a></td>
                      </tr>
                      <tr align="center" valign="middle">
                        <td width="50" class="emot_td"><a href="javascript:emoticon(\'[surp]\')"><img src="images/smiles/surp.gif" border="0" alt="Surprised" title="Surprised" /></a></td>
                        <td width="50" class="notopright"><a href="javascript:emoticon(\'[help]\')"><img src="images/smiles/help.gif" border="0" alt="Help" title="Help" /></a></td>
                      </tr>
                      <tr align="center" valign="middle">
                        <td width="50" class="emot_td"><a href="javascript:emoticon(\'[cool]\')"><img src="images/smiles/cool.gif" border="0" alt="Cool" title="Cool" /></a></td>
                        <td width="50" class="notopright"><a href="javascript:emoticon(\'[check]\')"><img src="images/smiles/check.gif" border="0" alt="Check This" title="Check This" /></a></td>
                      </tr>
                      <tr align="center" valign="middle">
                        <td width="50" class="emot_td"><a href="javascript:emoticon(\'[wink]\')"><img src="images/smiles/wink.gif" border="0" alt="Wink" title="Wink" /></a></td>
                        <td width="50" class="notopright"><a href="javascript:emoticon(\'[wtf]\')"><img src="images/smiles/wtf.gif" border="0" alt="WTF" title="WTF" /></a></td>
                      </tr>
                      <tr align="center" valign="middle">
                        <td width="50" class="emot_td"><a href="javascript:emoticon(\'[evil]\')"><img src="images/smiles/evil.gif" border="0" alt="Evil" title="Evil" /></a></td>
                        <td width="50" class="notopright"><a href="javascript:emoticon(\'[tongue]\')"><img src="images/smiles/tongue.gif" border="0" alt="Tongue" title="Tongue" /></a></td>
                      </tr>
                      <tr align="center" valign="middle">
                        <td width="50" class="emot_td"><a href="javascript:emoticon(\'[mad]\')"><img src="images/smiles/mad.gif" border="0" alt="Mad" title="Mad" /></a></td>
                        <td width="50" class="notopright"><a href="javascript:emoticon(\'[ass]\')"><img src="images/smiles/ass.gif" border="0" alt="Ass" title="Ass" /></a></td>
                      </tr>
                      <tr align="center" valign="middle">
                        <td width="50" class="emot_td"><a href="javascript:emoticon(\'[mad67]\')"><img src="images/smiles/mad67.gif" border="0" alt="Mad2" title="Mad2" /></a></td>
                        <td width="50" class="notopright"><a href="javascript:emoticon(\'[scream]\')"><img src="images/smiles/scream.gif" border="0" alt="Scream" title="scream" /></a></td>
                      </tr>
                      <tr align="center" valign="middle">
                        <td width="50"><a href="javascript:emoticon(\'[upto]\')"><img src="images/smiles/upto.gif" border="0" alt="Up To" title="Up To" /></a></td>
												<td width="50" class="border_left"><a href="javascript:emoticon(\'[thumb]\')"><img src="images/smiles/thumb.gif" border="0" alt="Thumbs Up" title="Thumbs Up" /></a></td>
                      </tr></table></div></td></tr></table>';
		$ostr .= '<input type="hidden" name="rs" value="'.htmlentities($rs).'">';
		$ostr .= '</form>';
	} elseif($var_name == 'nws') { //output phpBB text options
		$ostr .= '<table border="0" cellspacing="1" cellpadding="2"><tr>
		<td><textarea name="qsservernews" cols="75" rows="15" wrap="virtual">'.stripslashes($var_default).'</textarea><p><input type="submit" value="Submit" /></p></td></tr></table>';
		$ostr .= '<input type="hidden" name="rs" value="'.htmlentities($rs).'" />';
		$ostr .= '</form>';
	} else {
		$ostr .= '<input name="'.$var_name.'" value="'.$var_default.'" size="20" />';
		$ostr .= '<input type="submit" value="Submit" /></form>';
	}
	if($var_name != 'sure') {
		$ostr .= "<script> document.get_var_form.$var_name.focus(); </script>";
	} else {
		$ostr .= "<script> document.get_var_form.submit.focus(); </script>";
	}
	print_page($title,$ostr);
}


#function that charges turns for something. Admin is exempt.
function charge_turns($amount) {
	global $db_name,$user;
	if($user['login_id'] != 1) {
		$amount = round($amount);
		//if($user[login_id] !=1) {return 0;}
		dbn(__FILE__,__LINE__,"update ${db_name}_users set turns = turns - ".$amount.",turns_run = turns_run + ".$amount." where login_id = '".$user['login_id']."'");
		$user['turns'] -= $amount;
		$user['turns_run'] += $amount;
	}
}


#function that can give a user cash. Admin is exempt.
function give_cash($amount) {
	global $db_name,$user;
	//if ($user['login_id'] != 1) {
		dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + '$amount' where login_id = '$user[login_id]'");
		$user['cash'] += $amount;
	//}
}

#function takes cash from a player. Admin is exempt.
function take_cash($amount) {
	global $db_name,$user;
	//if ($user['login_id'] != 1) {
		dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash - '$amount' where login_id = '".$user[login_id]."'");
		$user['cash'] -= $amount;
	//}
}

#take tech support units from a player. Admin is exempt.
function take_tech($amount) {
	global $db_name,$user;
	if ($user['login_id'] != 1) {
		dbn(__FILE__,__LINE__,"update ${db_name}_users set tech = tech - '$amount' where login_id = '$user[login_id]'");
		$user['tech'] -= $amount;
	}
}

#Give tech support units to a player. Admin is exempt.
function give_tech($amount) {
	global $db_name,$user;
	if ($user['login_id'] != 1) {
		dbn(__FILE__,__LINE__,"update ${db_name}_users set tech = tech + '$amount' where login_id = '$user[login_id]'");
		$user['tech'] += $amount;
	}
}

#allows a message to be sent to a user.
function send_message($to,$text) {
	global $db_name,$user;
	//$text = addslashes($text);
	if($to == -5 && $user['clan_id'] > 0){
	dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text, clan_id) values(".time().",'$user[login_name]','$user[login_id]','$to','$text','$user[clan_id]')");
	} else {
	dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'$user[login_name]','$user[login_id]','$to','$text')");
	}
}

#function that lists all messages. Used for printing the forums, as well as private messages.
function print_messages($full) {
	global $db_name, $user, $error_str, $user_options, $last_time, $last_clan_time, $prevdays, $prevdays_c, $admin_forum, $allow_signatures, $find_last, $border_prop;
	if($user['login_id'] < 0 && isset($prevdays)) {#show forum for a previous time frame
	$forum_secs = $user_options['forum_back'] * 3600;
	if(($allow_signatures == 1) && ($user_options['show_sigs'] == 1)){#show with sigs
		db(__FILE__,__LINE__,"select m.*, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.login_id, u.sig from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' and timestamp > '".($last_time - $forum_secs)."' and timestamp <= $last_time and m.sender_id = u.login_id order by timestamp desc");
	} else { #show without sigs
		db(__FILE__,__LINE__,"select m.*, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.login_id from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' and timestamp > '".($last_time - $forum_secs)."' and timestamp <= $last_time and m.sender_id = u.login_id order by timestamp desc");
	}
		$last_time -= $forum_secs;
	} elseif($user['login_id'] < 0 && isset($prevdays_c)) {#show clan forum for a previous time frame
		$forum_secs = $user_options['forum_back'] * 3600;
		if(($allow_signatures == 1) && ($user_options['show_sigs'] == 1)){#show with sigs
			db(__FILE__,__LINE__,"select m.*, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.login_id, u.sig from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' and timestamp > '".($last_clan_time - $forum_secs)."' and timestamp <= $last_clan_time and m.sender_id = u.login_id && m.clan_id = $user[clan_id] order by timestamp desc");
		} else { #show without sigs
			db(__FILE__,__LINE__,"select m.*, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.login_id from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' and timestamp > '".($last_clan_time - $forum_secs)."' and timestamp <= $last_clan_time and m.sender_id = u.login_id && m.clan_id = $user[clan_id] order by timestamp desc");
		}
		$last_clan_time -= $forum_secs;
	}elseif($user['login_id'] < 0 && $find_last) { #finds posts up to when the user last accessed the forum
		if(($allow_signatures == 1) && ($user_options['show_sigs'] == 1)){#shows sigs
			db(__FILE__,__LINE__,"select m.*, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.login_id, u.sig from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' and timestamp > '".$last_time."' and m.sender_id = u.login_id order by timestamp desc");
		} else {#doesn't show sigs.
			db(__FILE__,__LINE__,"select m.*, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.login_id from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' and timestamp > '".$last_time."' and m.sender_id = u.login_id order by timestamp desc");
		}
	} elseif($user['login_id'] == -5) {#show clan forum
		$forum_secs = $user_options['forum_back'] * 3600;
		if(($allow_signatures == 1) && ($user_options['show_sigs'] == 1)){#with sigs
				db(__FILE__,__LINE__,"select m.*, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.login_id, u.sig from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' and timestamp > '".(time() - $forum_secs)."' and m.sender_id = u.login_id && m.clan_id = $user[clan_id] order by timestamp desc");
		} else {#without sigs
				db(__FILE__,__LINE__,"select m.*, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.login_id from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' and timestamp > '".(time() - $forum_secs)."' and m.sender_id = u.login_id && m.clan_id = $user[clan_id] order by timestamp desc");
		}
		$last_clan_time = (time() - $forum_secs);
	} elseif($user['login_id'] == -1) {#show plain old forum back to however many hows.
		$forum_secs = $user_options['forum_back'] * 3600;
		if(($allow_signatures == 1) && ($user_options['show_sigs'] == 1)){#with sigs
			db(__FILE__,__LINE__,"select m.*, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.login_id, u.sig from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' and timestamp > '".(time() - $forum_secs)."' and m.sender_id = u.login_id order by timestamp desc");
		} else {#without sigs
			db(__FILE__,__LINE__,"select m.*, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.login_id from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' and timestamp > '".(time() - $forum_secs)."' and m.sender_id = u.login_id order by timestamp desc");
		}
		$last_time = (time() - $forum_secs);
	} else {#player messages
		if(($allow_signatures == 1) && ($user_options['show_sigs'] == 1)){#with sigs
			db(__FILE__,__LINE__,"select m.message_id, m.timestamp, m.text, m.sender_id, m.sender_id as login_id, u.sig from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' && m.sender_id = u.login_id	order by timestamp desc");
		} else {#without sigs
			db(__FILE__,__LINE__,"select message_id, timestamp, text, sender_id, sender_id as login_id from ${db_name}_messages where login_id = '$user[login_id]' order by timestamp desc");
		}
	}
	$messages = dbr();
	db2(__FILE__,__LINE__,"select count(message_id) from ${db_name}_messages where login_id = '$user[login_id]'");
	$counted = dbr2();

	if(isset($messages) && $full == 1 && $counted[0] > 1){
		$error_str .= "<br /><a href=mpage.php?killallmsg=1>Delete All Messages</a>";
		$error_str .= "<form method=post action=mpage.php name=messag_form><input type=hidden name=clear_messages value=1>";
	}
	while($messages) {
#	if(!isset($show_marked) && ($messages[marked] == '0')) { #show unmarked messages.
		$error_str .= "<br /><table cellspacing=0 cellpadding=2 class=all width=650 align=center><tr><th style='font-size: 8pt; text-align: left;'>";
		$error_str .= date( "M d - H:i",$messages['timestamp']);
		$error_str .= " - ";
		$error_str .= print_name($messages);
		$error_str .= "</th></tr><tr><td width=100%>";

		$messages['text'] = stripslashes($messages['text']);
		#$m_text = print_name($messages).": ".$messages[text]; #what does this line do? its un-used?!
		$error_str .= "<blockquote>";
		$error_str .= "$messages[text]".stripslashes(mcit("".$messages['sig']));
		$error_str .= "</blockquote>";
		$error_str .= "</td></tr><tr><td width=100% style='border-top: $border_prop;'><a href=message.php?target=$messages[sender_id]&reply_to=$messages[message_id]>Reply</a> - <a href=message.php?forward=$messages[message_id]>Forward</a> - <a href=diary.php?log_ent=$messages[message_id]>Log</a>";

		if($admin_forum == 1 && $user['login_id'] == -1) {#admin link to delete messages in forum
			$error_str .= " - <a href=forum.php?killmsg=$messages[message_id]>Delete</a>";
		} elseif($admin_forum == 1 && $user['login_id'] == -5) {#admin link to delete messages in clan forum
				$error_str .= " - <a href=forum.php?killmsg=$messages[message_id]&clan_forum=1>Delete</a>";
		}

		if($full == 1 && $counted[0] > 1) {#player link to delete messages (with checkboxes)
			$error_str .= " - <a href=mpage.php?killmsg=$messages[message_id]>Delete</a> - <input type=checkbox name=del_mess[$messages[message_id]] value=$messages[message_id]>";
		} elseif($full == 1) {#player link to delete message
			$error_str .= " - <a href=mpage.php?killmsg=$messages[message_id]>Delete</a>";
		}
		$error_str .= "</td></tr></table>";
		#$error_str .= "</blockquote>";
		$messages = dbr();
	}
	if($user['login_id'] == -1) {
		db(__FILE__,__LINE__,"select count(message_id) from ${db_name}_messages where login_id = -1 && timestamp < '$last_time'");
		$num_mes_prev = dbr();
		if(!empty($num_mes_prev['0'])){
			$error_str .= "<p><a href=forum.php?last_time=$last_time&prevdays=yes>Previous $user_options[forum_back] Hours</a>";
		} else {
			$error_str .= "<p>End of Forum";
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_users set last_access_forum='".time()."' where login_id='$user_options[login_id]'");
		$user['last_access_forum'] = time();
	} elseif($user['login_id'] == -5){
		db(__FILE__,__LINE__,"select count(message_id) from ${db_name}_messages where login_id = -5 && timestamp < '$last_clan_time' && clan_id = '$user[clan_id]'");
		$num_mes_prev_c = dbr();
		if(!empty($num_mes_prev_c['0'])){
			$error_str .= "<p><a href=forum.php?clan_forum=1&last_clan_time=$last_clan_time&prevdays_c=yes>Previous $user_options[forum_back] Hours</a>";
		} else {
			$error_str .= "<p>End of Clan Forum";
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_users set last_access_clan_forum='".time()."' where login_id='$user_options[login_id]'");
		$user['last_access_clan_form'] = time();
	}

	if($full == 1 && $counted['0'] > 1) {
	#	$error_str .= "<a href=location.php?killallmsg=1>Delete All Messages</a> - ";
		$error_str .= "<a href=javascript:TickAll(\"messag_form\")>Invert Message Selection</a>";
		$error_str .= " - <input type=submit value='Delete Selected'></form>";
	}
	if($admin_forum == 1 && $target == -1) {
		$error_str .= "<p><a href=forum.php?killallmsg=1>Delete All Forum Messages</a>";
	}
}

//function that allows a player to be retired.
function retire_user($target) {
	global $user,$db_name;
	if($target < 6) {
		print_page("Retire","Unable to retire this Player.");
	}
	if(($target == $user['login_id']) || ($user['login_id'] == 1)) {
		db(__FILE__,__LINE__,"select login_name,v_clan_id from ${db_name}_users where login_id = '$target'");
		$target_user = dbr();
		db2(__FILE__,__LINE__,"select * from ${db_name}_leeches where login_id = '$target_user[login_id]'");
		while($leech = dbr2()) {
			db(__FILE__,__LINE__,"update ${db_name}_ships set leech = leech - 1 where ship_id = '$leech[ship_id]'");
		}

		post_news("<b class=b1>$target_user[login_name]</b> Retired from the Game.");

		dbn(__FILE__,__LINE__,"delete from ${db_name}_mines where v_clan_id = '$target_user[v_clan_id]'");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_leeches where login_id = '$target_user[login_id]'");

		//set retire time if needed for rejoin-delay function
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'rejoin_delay'");
		$state = dbr();
		if($state[value] == 1) {
			dbn(__FILE__,__LINE__,"update user_accounts set ".$db_name." = ".time()." where login_id = ".$user[login_id]);
		}

		dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where login_id = $target");
		dbn(__FILE__,__LINE__,"update ${db_name}_bilkos set bidder_id = 0, timestamp = ".time()." where bidder_id = $target");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set owner_name = 'Retired Player', owner_id=0, pass='' where owner_id = '$target'");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_diary where login_id = $target");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_user_options where login_id = $target");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_users where login_id = $target");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_bank_account where login_id = $target");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_fleets where login_id = $target");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_upgrade_units where login_id = $target");
		dbn(__FILE__,__LINE__,"update ${db_name}_politics set login_id = 0, login_name = 0, timestamp = 0 where login_id = '$target'");
	}
}

function print_name($user) {
	global $db_name,$user_options,$PRINT_NAME_CACHE;
	if(empty($PRINT_NAME_CACHE[$user['login_id']])) {
		if ($user['login_id']) {
			db3(__FILE__,__LINE__,"select u.login_id,u.login_name,u.clan_id,u.v_clan_id,u.clan_sym_color,u.clan_sym, pu.aim, pu.icq from ${db_name}_users u, user_accounts pu where u.login_id = $user[login_id] && pu.login_id = u.login_id");
			$user = dbr3();
		}

		$temp_str = "<a href=\"../player_info.php?target=$user[login_id]\" class=\"nobold\">$user[login_name]</a>";

		#determine if user wants to see relation symbols.
		if($user_options['show_rel_sym'] != 0){
			#determine if user has clan sig
			if ($user['clan_id'] != 0 && $user['clan_sym']) {
				$temp_str .= " (<font color=$user[clan_sym_color]>$user[clan_sym]</font>)";
//				$temp_str .= find_designation($user['clan_id']); #prints relation from function
			}else{
//				$temp_str .= find_designation($user['v_clan_id']); #prints relation from function
			}

		}else{
			#determine if user has clan sig
			if ($user['clan_id'] != 0 && $user['clan_sym']) {
				$temp_str .= " (<font color=$user[clan_sym_color]>$user[clan_sym]</font>)";
			}
		}

		#determine if user has aim
		if($user['aim'] != '' && $user_options['show_aim'] == 1){
			$temp_str .= " - <a href=\"aim:goim?screenname=".urlencode($user['aim'])."&message=Hi+".urlencode($user['aim'])."+Are+you+there?\"><img src=images/aim.gif border=0></a>";
		}
		#determine if user has icq
		if($user['icq'] != 0 && $user_options['show_icq'] == 1){
			$temp_str .= " - <a href=\"http://wwp.mirabilis.com/$user[icq]\" TARGET=\"_blank\"><IMG SRC=\"http://wwp.icq.com/scripts/online.dll?icq=$user[icq]&img=5\" BORDER=0></a>";
		}
		return $temp_str;
	} else {
		return $PRINT_NAME_CACHE[$user['login_id']];
	}
}

//get distance between stars
function get_star_dist($s1,$s2) {
	global $db_name;
	if(!isset($s1) || !isset($s2)){
		return 0;
	}
	db(__FILE__,__LINE__,"select x_loc,y_loc from ${db_name}_stars where star_id = '$s1' || star_id = '$s2'");
	$star1 = dbr();
	$star2 = dbr();
	$dist = round(sqrt(abs(($star1['x_loc'] - $star2['x_loc'])*2) + abs(($star1['y_loc'] - $star2['y_loc'])*2)));
	return $dist;
}

//get configuration of ship
function get_config($the_ship) {
	global $db_name;

	db(__FILE__,__LINE__,"select config from ${db_name}_ships where ship_id = '$the_ship'");
	$this_config = dbr();
	$this_config = $this_config[0];
	return get_config_var($this_config);
}

function get_config_var($this_config) {
	$values = array();

	if(eregi("ls",$this_config)){$values[ls]=1;}
	if(eregi("hs",$this_config)){$values[hs]=1;}
	if(eregi("oo",$this_config)){$values[oo]=1;}
	if(eregi("na",$this_config)){$values[na]=1;}
	if(eregi("po",$this_config)){$values[po]=1;}
	if(eregi("so",$this_config)){$values[so]=1;}
	if(eregi("nt",$this_config)){$values[nt]=1;}
	if(eregi("br",$this_config)){$values[br]=1;}
	if(eregi("tw",$this_config)){$values[tw]=1;}
	if(eregi("sj",$this_config)){$values[sj]=1;}
	if(eregi("sw",$this_config)){$values[sw]=1;}
	if(eregi("sv",$this_config)){$values[sv]=1;}
	if(eregi("sc",$this_config)){$values[sc]=1;}
	if(eregi("sh",$this_config)){$values[sh]=1;}
	if(eregi("rd",$this_config)){$values[rd]=1;}
	if(eregi("ws",$this_config)){$values[ws]=1;}
	if(eregi("ps",$this_config)){$values[ps]=1;}
	if(eregi("ot",$this_config)){$values[ot]=1;}
	if(eregi("dt",$this_config)){$values[dt]=1;}
	if(eregi("pc",$this_config)){$values[pc]=1;}
	if(eregi("sa",$this_config)){$values[sa]=1;}
	if(eregi("ew",$this_config)){$values[ew]=1;}
	if(eregi("gs",$this_config)){$values[gs]=1;}
	if(eregi("mi",$this_config)){$values[mi]=1;}

	return $values;
}

//Choose a system at random
function random_system_num() {
	global $db_name;
	db(__FILE__,__LINE__,"select count(star_id) from ${db_name}_stars where star_id >= 1");
	$total = dbr();

	return mt_rand(1,$total[0]);
}

/*
function, written entirely by Jonathan "Moriarty" using modern Information Retrieval/Extraction (IR/IE) principles for find data within a database, and order it.

Uses stop words, and ranked displaying of results, amoung other things.

This Function was written for a different program initially, and transfered to Solar Empire with a couple of variations to get it working with the DB structure.
As such it is excempt from the Open Source License. However you may use if for you own purposes so long as this comment is attached.
#Created: 12/12/01
#Adapted for SE: 18/2/02
#Disclaimer: This search engine is provided as is. No warranty, and its probably your own fault if it breaks.
#Enjoy!
*/

function search_the_db($term,$can_do_stop,$db_to_search,$search_page,$field_in_db){
	global $db_name,$user;

	#split the entered terms into seperate components
	$alpha_1231 = preg_split ("/\s/", trim($term));
	$beta_4543 = count($alpha_1231);
	$new_search = trim($term);

	#remove stopwords unless otherwise specified
	if(!$can_do_stop && $beta_4543 > 1) {

		#dump the stoplist into an array.
		$stoplist = array('a','b','by','c','d','e','e.g.','eg','f','for','from','g','h','h.','had','has','have','i','ie','i.e.','if','in','is','it','it\'d','it\'ll','it\'s','its','j','k','l','m','my','n','no','o','of','oh','ok','okay','or','p','q','r','s','see','so','t','that','the','these','they','this','those','to','too','u','v','w','which','who','why','will','with','would','x','y','you','your','z');

		#remove stopwords from entered text
		$amount = count($stoplist);
		$stop_count = 0;
		$stop_words_removed=0;

		#while loop that does searching for stopwords
		while ($stop_count < $amount){
			if(preg_match("/^".preg_quote($stoplist[$stop_count])."\s/",$new_search) || preg_match("/(?i)\s".preg_quote($stoplist[$stop_count])."$/",$new_search) || preg_match("/(?i)\s".preg_quote($stoplist[$stop_count])."\s/",$new_search)){
				$new_search = preg_replace("/(?i)^".preg_quote($stoplist[$stop_count])."\s/","",$new_search);
				$new_search = preg_replace("/(?i)\s".preg_quote($stoplist[$stop_count])."$/","",$new_search);
				$new_search = preg_replace("/(?i)\s".preg_quote($stoplist[$stop_count])."\s/"," ",$new_search);
				$stop_list_list[$stop_words_removed] = $stoplist[$stop_count];
				$stop_words_removed++; #stopwords removed from entered term.
			}
			$stop_count++; #total stopwords in stopword list;
		}
		#puts commas in list of removed stopwords
		$count = 0;

		while($count < $stop_words_removed){
			if($count != $stop_words_removed-1){
				$stop_str .= $stop_list_list[$count].", ";
			} else {
				$stop_str .= $stop_list_list[$count];
			} #end if
			$count++;
		} #end while
	} #end stopword removal

	#Split the remaining search into seperate terms
	$keywords = preg_split ("/\s/", $new_search);
	$num_terms = count($keywords);

	#if a user enters only stop words
	if($num_terms < 1){
		$stop_count = 0;
		$stop_words_removed=0;
		$new_search = trim($term);
	}

	#create the sql query
	$sql_query = ""; #clear query text
	$c1 = 0; #clear counter
	foreach($keywords as $value){
		#used to create a lowercase set of search terms.
		$keywords_2[$c1] = strtolower($value);

		#add to sql_query text.
		if($c1 > 0){ #determine if already got an entry in query. If so, then need an ||.
			$sql_query .= "|| ${field_in_db} REGEXP '$value'";
		} else {
			$sql_query .= " ${field_in_db} REGEXP '$value'";
		}
		if($db_to_search == "diary"){
			$sql_query .= "&& login_id = '$user[login_id]'";
		}

		$c1++;
	} #end foreach of search terms

	#the mysql query which finds the results
	db(__FILE__,__LINE__,"select timestamp,${field_in_db} from ${db_name}_${db_to_search} where".$sql_query." order by timestamp desc");
	$news = dbr();

	$primary_counter = 0; #used to ensure goes around for each keyword;

	#loop through all results found
	while($news){
		#ensure array entry is clear, and enter initial data.
		$search_results_text[$primary_counter] = $news[$field_in_db];
		$search_results_timestamp[$primary_counter] = $news['timestamp'];
		$search_results_finds[$primary_counter] = 0;
		$search_results_score[$primary_counter] = 0;

		#loop through the lowercase search terms (as substr_count is case dependent).
		foreach($keywords_2 as $value){
			if(preg_match("/".preg_quote($value)."/i",$search_results_text[$primary_counter])) {
				$search_results_finds[$primary_counter]++;
				$search_results_score[$primary_counter] += (substr_count(strtolower($search_results_text[$primary_counter]), $value) -1) * 2;
				$search_results_text[$primary_counter] = eregi_replace("$value","<font color=lime>$value</font>",$search_results_text[$primary_counter]);
			}
		}

		$primary_counter++;
		$news = dbr();
	} #end term list while


	#determine if any results were found.
	if (!empty($search_results_text)) { #results found
		#nifty little function allows many arrays to be sorted together, and keeps information in them in the same place in relation to each other. Excellent for search tech.
		array_multisort($search_results_finds, SORT_DESC, SORT_NUMERIC, $search_results_score, SORT_DESC, SORT_NUMERIC, $search_results_timestamp, SORT_DESC, SORT_NUMERIC, $search_results_text);

		$ret_str = "<form method=post action='$filename' name=search_form>";
		$ret_str .= "New Search: <input type=text name=term size=20 value='$term'>";
		$ret_str .= " - <input type=submit value=Search></form><p>";
		$ret_str .= "<br />Shown below are the results for your search using terms (<b class=b1>$term</b>) in ranked, then chronological order:<br /><br />";

		#stopwords where removed
		if ($stop_words_removed > 0){
			$stop_search_txt = urlencode($term);
			$ret_str .= "<i>Stop-words</i> were removed from your search. These consisted of: <b>".$stop_str."</b>.<br />Click <a href=${search_page}.php?term=$stop_search_txt&can_do_stop=1>here</a> to run a search with the stop-words included.<p>";
		}
		$num = 0; #keep track of where in the arrays the system is.

		#make table for output
		$ret_str .= make_table(array("",""));

		#cycle through the results for the final output.
		while($var = each($search_results_text)) {
			if($num == 0){#first result, determine how many of the keywords where found.
				$keep_track = $search_results_finds[$num];
				$k_temp_tracker = count($keywords);

				if($k_temp_tracker > $keep_track){ #only some of the words where found.
					$follow_through = 2;
				} else { #all keywords make an appearance in result.
					$follow_through = 1;
				}
			}

			#if all keywords are found, then cycle through only results that have all keywords in them, otherwise cycle through all results.
			if($follow_through == 1 && $search_results_finds[$num] != $keep_track){
				break;
			}

			$ret_str .= quick_row("<b>".date("M d - H:i",$search_results_timestamp[$num]),$var[1]);
			$num++;
		}
		#end table, then return results.
		$ret_str .= "</table><br />";
		return $ret_str;

	} else { #no results found
		return "<br />No entries of <b class=b1>$term</b> were found. Please broaden your search.<br /><br />";
	}

} #end search_the_db function


// May I deface the comment, Mori?...lol
// Here follows a simple search function with the headline table saved as TEXT not BLOB
// Written/Modified by Maugrim_The_Reaper 5/10/2002

function search_news($phrase, $type){ //Maugrim's Cheaper Version...LOL...no search limits.
	global $db_name;
	if($type == 1) {
		$db_table = "news";
	} else {
		$db_table = "news_maint";
	}
		db(__FILE__,__LINE__,"select * from ${db_name}_".$db_table." where headline like '%$phrase%' order by timestamp desc");
		$result = dbr();
	if(empty($result)) {
		$output = "<form method=post action='".$db_table.".php' name=search_form>";
		$output .= "New Search: <input type=text name=term size=20 value='$phrase'>";
		$output .= " - <input type=submit value=Search></form><p>";
		$output .= "<br />Headlines containing the term <b class=b1>$phrase</b> could not be found. Please try another phrase if applicable.<br /><br />";
	} else {
		$output = "<form method=post action='".$db_table.".php' name=search_form>";
		$output .= "New Search: <input type=text name=term size=20 value='$phrase'>";
		$output .= " - <input type=submit value=Search></form><p>";
		$output .= "<br />Shown below are the results for your search using terms (<b class=b1>$phrase</b>):<br /><br />";
		$output .= make_table(array("",""));
		$patterns = array ("/a/","/b/","/c/","/d/","/e/","/f/","/g/","/h/","/i/","/j/","/k/","/l/","/m/","/n/", "/o/","/p/","/q/","/r/","/s/","/t/","/u/","/v/","/w/","/x/","/y/","/z/");
		$replace = array ("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		while($result) {
			$headline = $result[headline];
			$string = preg_replace ($patterns, $replace, $phrase);
			$headline = eregi_replace($phrase, "<font color=lime>$string</font>", $headline);
			$output .= quick_row("<b>".date("M d - H:i", $result[timestamp]), $headline);
			$result = dbr();
		}
		$output .= "</table><br />";
	}
	return $output;
} //end of search_news() function

#function used to calculate the percentage of something. does not divide things by 0 though.
function calc_perc($num1,$num2){
	if($num1 == 0 || $num2 == 0){
		return "<b class=red_txt style='font-weight: normal'>$num1 (0%)</b>";
	} else {
		$result = number_format(($num1 / $num2) * 100, 2, '.','');
		return "<b class=red_txt style='font-weight: normal'>$num1 (".$result."%)</b>";
	}
}

// get all stats for a given ship
function get_user_ship($ship_id){
	global $db_name, $user_ship;
	db2(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$ship_id'");
	$user_ship = dbr2();

	empty_bays(); //call the empty_bays function

//	return $user_ship;
}

//a function that allows a message to be sent to all players.
function message_all_players($text, $game_db, $recipients, $sender){
	global $user;

	db2(__FILE__,__LINE__,"select login_id from ${game_db}_users");
	while($players = dbr2(1)) {
		dbn(__FILE__,__LINE__,"insert into {$game_db}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'$user[login_name]','$user[login_id]','$players[login_id]','Message to <b class=b1>$recipients</b> from $sender:<br /> $text')");
	}
	return "Message sent to all players in <b>$game_db</b>.";
}
function mail_users($subject,$message) {
	global $db_name;
db(__FILE__,__LINE__,"select * from ${db_name}_users");
	while($users=dbr()) {
		db2(__FILE__,__LINE__,"select * from user_accounts where login_id = $users[login_id]");
		$account=dbr2();
	if($account[newsletter] == 1) {
mail($account[email_address],"QS: Generations - $subject","This email was sent because you allowed it, if you want to set it otherwise please change your details in options at $url_prefix.\n $message","From: $admin_mail");
	}
	}
}

?>