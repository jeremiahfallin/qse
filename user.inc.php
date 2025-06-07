<?php

require("common.inc.php");



// we assume user.inc.php is always called (at least until print functions are removed from this file)
array_push($FILE_LIST, basename(__FILE__));

// check for session data - preset by common.inc.php
if($login_id == 0 || empty($db_name) || !isset($login_id)) {
	 echo "<script>self.location='".$url_prefix."/login_form.php';</script>";
	 exit;
}

mt_srand((double)microtime()*1000000);

// retrieve the user data

$db = db_connect("$database_host","$database_user","$database_password","$database","$database_persistent");

// Uncomment to display database connection data - debug only
//echo($database);
//print_r($db);

db(__FILE__,__LINE__,"select * from ${db_name}_users where login_id = '$login_id'");
$user = dbr();
//$tpl->assign("user", $user);

db(__FILE__,__LINE__,"select * from ${db_name}_permissions where login_id = '$login_id'");
$user_perm = dbr();
//$tpl->assign("user_perm", $user_perm);

db(__FILE__,__LINE__,"select * from user_accounts where login_id = '$login_id'");
$p_user = dbr();



// check and update the authentication.
//if($user['login_id'] != 1) {
	$check = new Q_AuthCheck();
	$check->Check_Auth($p_user);
//}
//fix at 24 Feb. 2003 - Security Hole Fix - will also prevent more than 1 Admin logging in.

// retrieve more user data.
db(__FILE__,__LINE__,"select * from ${db_name}_user_options where login_id = '$login_id'");
$user_options = dbr();
//$tpl->assign("user_options", $user_options);

db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
$user_ship = dbr();
// If no user ship, try to find one
if (empty($user_ship)) {
	db(__FILE__,__LINE__,"select * from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]'");
	$user_ship = dbr();
	if (empty($user_ship)) {
		db(__FILE__,__LINE__,"select * from ${db_name}_ships where login_id = '$user[login_id]'");
		$user_ship = dbr();
		if (empty($user_ship)) {
			db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '1'");
			$user_ship = dbr();
			if (empty($user_ship)) {
				print_page('No Command Ship','<p>Error: Could not find <b>any</b> ship for you to command.</p>');
			}
		}
	}
	$user['ship_id'] = $user_ship['ship_id'];
	$user['location'] = $user_ship['location'];
	$user['last_ss'] = 0; // When switching command, last_ss isn't valid anymore
	dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$user[ship_id]', location = '$user[location]', last_ss = '$user[last_ss]' where login_id = '$user[login_id]'");
}

// use last fleet if no current selected
if($user_ship['fleet_id'] == 0) {
	db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where login_id = '$user[login_id]' limit 1");
	$replacem = dbr();
	$user_ship['fleet_id'] = $replacem['fleet_id'];
}

//print_r($user_ship);

db(__FILE__,__LINE__,"select * from ${db_name}_fleets where fleet_id = '$user_ship[fleet_id]'");
$user_fleet = dbr();
//$tpl->assign("user_fleet", $user_fleet);

// double check the current fleet is assigned a command ship
if($user_fleet['ship_id'] <= 0 || !isset($user_fleet['ship_id']))
{
	db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where fleet_id = '$user_fleet[fleet_id]' order by fighters desc limit 1");
	$fleet_ship_id = dbr();
	// postgres fix
	if(empty($fleet_ship_id))
	{
		$fleet_ship_id = array("ship_id" => 0);
	}
	dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ship_id = '$fleet_ship_id[ship_id]' where login_id = '$user[login_id]' and fleet_id = '$user_fleet[fleet_id]' and location = '$user[location]'");
}

//UPDATE last request
dbn(__FILE__,__LINE__,"update ${db_name}_users set last_request = ".time()." where login_id = '$user[login_id]'");



$PRINT_NAME_CACHE = array();

// -----------------------------------------------------------------
// check for variable lookup method (read from database or read from file)
if($var_source == 0)
{
	require_once("$map_path/$db_name/db_vars.inc.php");
}
else
{
	db2(__FILE__,__LINE__,"select name,value from ${db_name}_db_vars order by name");
	while($var_list = dbr2())
	{
		$$var_list['name'] = $var_list['value'];
	}
}


#function to figure out how many empty cargo bays there are on the ship.
function empty_bays() {
	global $user_ship;
	$user_ship['empty_bays'] = $user_ship['cargo_bays'] - $user_ship['metal'] - $user_ship['fuel'] - $user_ship['elect'] - $user_ship['colon'] - $user_ship['organ']- $user_ship['darkmatter'];
	return $user_ship['empty_bays'];
}

empty_bays(); //remove as O/D

//DEV - possibly this just repeats the earlier $user_ship request from the database
get_user_ship($user['ship_id']);

//generic link to go back to the start system
$rs = "<p><a href=location.php>Back to the Star System</a><br />";

//damage capacity of the silicon armour module
$upgrade_sa = 750;



//function that prints the ship info in the left column
function print_ship_info() {
	global $user,$user_ship,$user_options,$border_prop,$user_fleet,$tpl,$db_name;

	if($user['ship_id'] == 1) {
		$text_ship .= "<br /><table cellspacing=0 cellpadding=3 class=all align=center width=100><tr><th class=color_header>Ship Destroyed</th></tr></table>";
	} else {

		$user_ship_name = stripslashes($user_ship['ship_name']);
		$text_ship .= "<br /><table cellspacing=0 cellpadding=2 class=all width=100%><tr><th>Ship in Command: <br>$user_ship_name</th></tr><tr><td><table cellspacing=0 cellpadding=3 border=0 width=100%>";
	if($user_options['show_pics']) {
		$text_ship .= "<td><tr><img src='images/ships/ship_$user_ship[shipclass]_tn.jpg' width = 160, height = 120></td></tr>";
		}
		$text_ship .= "<tr><td width=50 nowrap>Ship Class:</td><td width=50>$user_ship[class_name]</td></tr>";
		$text_ship .= "<tr><td width=50 nowrap>Ship Type:</td><td width=50>$user_ship[type]</td></tr>";
#		$text_ship .= "<tr><td width=50 nowrap>Ship Name:</td><td width=50 nowrap>$user_ship_name</td></tr>";
		if($user_options['enable_fleets'] == 1) {
			$text_ship .= "<tr><td width=50% nowrap>Fleet:</td><td width=50% nowrap>".$user_fleet['fleet_name']."</td></tr>";
		}

		#$text_ship .= "<tr><td colspan='2'><b></b></td></tr>";
if($user_ship[max_fighters] > 0){
		$text_ship .= "<tr><td width=50% nowrap>Fighters:</td><td width=50% nowrap>$user_ship[fighters] / $user_ship[max_fighters]</td></tr>";
}
db(__FILE__,__LINE__,"select * from se_games where db_name = '${db_name}'");
$paused = dbr();

/*db(__FILE__,__LINE__,"select * from se_games where db_name = '${db_name}'");
$shields = dbr();
$armour = $shields['sheild'];*/
		$text_ship .= "<tr><td width=50% nowrap>Shields:</td><td width=50% nowrap>$user_ship[shields] / $user_ship[max_shields]</td></tr>";

//		$text_ship .= "<tr><td width=50% nowrap>$paused[shield]:</td><td width=50% nowrap>$user_ship[shields] / $user_ship[max_shields]</td></tr>";
if(isset($user_ship['config'])){
			$index = get_indx($user_ship);
			$text_ship .= "<tr><td width=50% nowrap>Config:</td><td width=50% nowrap>$user_ship[config]</td></tr>";
			$text_ship .= "<tr><td width=50% nowrap>A/D Bonus:</td><td width=50% nowrap>".$index['att']."-".$index['def']."</td></tr></table></td></tr></table>";
		} else {
			$text_ship .= "<tr><td width=50% nowrap>Config:</td><td width=50% nowrap>None</td></tr></table></td></tr></table>";
		}

		$text_ship .= "<br /><table cellspacing=0 cellpadding=2 class=all align=center width=100%><tr><th colspan=2>Ship Cargo</th></tr>";

		if(!empty($user_ship['metal'])) {
			$text_ship .= "<tr><td width=50% nowrap><img height=12 width=14 alt=\"Metal\"
src=\"images/metal.jpg\"> Metal:</td><td width=50% nowrap>$user_ship[metal]</td></tr>";
		}
		if(!empty($user_ship['fuel'])) {
			$text_ship .= "<tr><td width=50% nowrap><img height=12 width=14 alt=\"Fuel\"
src=\"images/fuel.jpg\"> Fuel:</td><td width=50% nowrap>$user_ship[fuel]</td></tr>";
		}
		if(!empty($user_ship['elect'])) {
			$text_ship .= "<tr><td width=50% nowrap><img height=12 width=14 alt=\"Electronics\"
src=\"images/elect.jpg\"> Electronics:</td><td width=50% nowrap>$user_ship[elect]</td></tr>";
		}
		if(!empty($user_ship['organ'])) {
			$text_ship .= "<tr><td width=50% nowrap><img height=12 width=14 alt=\"Organics\"
src=\"images/organ.jpg\"> Organics:</td><td width=50% nowrap>$user_ship[organ]</td></tr>";
		}
		if(!empty($user_ship['colon'])) {
			$text_ship .= "<tr><td width=50% nowrap><img height=12 width=14 alt=\"Colonists\"
src=\"images/spacer.jpg\"> Colonists:</td><td width=50% nowrap>$user_ship[colon]</td></tr>";
		}
		if(!empty($user_ship['scrap'])) {
			$text_ship .= "<tr><td width=50% nowrap><img height=12 width=14 alt=\"Scrap Metal\"
src=\"images/spacer.jpg\"> Scrap Metal:</td><td width=50% nowrap>$user_ship[scrap]</td></tr>";
		}
		if($user_ship['darkmatter']) {
			$text_ship .= "<tr><td width=50% nowrap><img height=12 width=14 alt=\"Dark-Matter\"
src=\"images/dmatter.jpg\"> Darkmatter:</td><td width=50% nowrap>$user_ship[darkmatter]</td></tr>";
		}
		if(!empty($user_ship['empty_bays'])) {
			$text_ship .= "<tr><td width=50% nowrap><img height=12 width=14 alt=\"\"
src=\"images/spacer.jpg\"> Empty:</td><td width=50% nowrap>$user_ship[empty_bays]</td></tr>";
		}
		if(empty($user_ship['cargo_bays'])) {
			$text_ship .= "<tr><td collspan=2>No Cargo Bays</td></tr>";

				}
		if(eregi("mi",$user_ship['config'])){
			$total_slots = $user_ship['num_mi'] * 4;
			$empty_slts = $total_slots - $user_ship['missiles'] - $user_ship['dmissiles'];
			$text_ship .= "<tr><td align=center colspan=2>Missiles:</td></tr>";
			$text_ship .= "<tr><td align=center><b class=b1>$user_ship[missiles] Standard</b></td></tr>";
			$text_ship .= "<tr><td align=center><b class=b1>$user_ship[dmissiles] D-Matter</b></td></tr>";
			$text_ship .= "<tr><td align=center><b class=b1>$empty_slts/$total_slots Free Slots</b></td></tr>";
		}
		$text_ship .= "</table>";
	}
	Return $text_ship;
}



#function that will print the left column
if(isset($_GET['retireinger'])){
	retire_user($_GET['retireinger']);
	}
/*if(isset($_GET['changeinger'])){
	db2(__FILE__,__LINE__,$_GET['changeinger']);
		while($row = dbr2()){
			print_r($row);
		}
        }*/
function print_status() {
	global $user, $user_ship, $count_days_left_in_game, $db_name, $max_turns, $enable_politics, $turns_safe, $flag_bmrkt, $user_options, $border_prop, $flag_research, $max_clans, $owner_id, $CONFIG;

	include_once("themes/".$user_options['theme']."/header.php");
	//Main Window
	echo '<table border=0 cellspacing=0 cellpadding=2>';
	echo "<tr><td valign=top width=175>";

	echo "<table cellspacing=0 cellpadding=2 class=all width=100% align=center><tr><th colspan=2>";
	echo "<b>".date( "M d - H:i (T)")."</b></th></tr><tr><td width=50% nowrap class=emot_td align=top>Game:</td><td class=emot_td width=50%>";
	#echo "<b>".date( "M d - ")."</b>";
	db(__FILE__,__LINE__,"select name,game_days_remaining,sd_day_num from se_games where db_name = '${db_name}'");
	$game_info = dbr();
	echo "<a href='game_info.php?new_page=1'>$game_info[name]</a></td></tr><tr><td colspan=2><table cellpadding=0 cellspacing=0 border=0 width=100%><tr>";
	db(__FILE__,__LINE__,"select paused from se_games where db_name = '$db_name'");
	$paused=dbr();
	if($paused['paused'] == 1){
		echo "<td width=50% nowrap>Game Status:</td><td width=50% nowrap><b>Paused</b></td></tr>";
		$dev2 = $owner_id;
		db(__FILE__,__LINE__,"select count(login_id) as onl from ${db_name}_users where login_id > 1 && login_id != 3 && last_request > ".(time()-300));
		$lr_result = dbr();
		if($user['login_id'] == 1 || $user['login_id'] == $dev2) {
			$start_lnk = "<a href='active_users.php'>";
			$end_lnk = "</a>";
		}
		db(__FILE__,__LINE__,"select o.Status_Hidden as Stats, count(u.login_id) as adm from ${db_name}_users u, ${db_name}_user_options o where u.login_id = 1 && o.login_id = 1 && u.last_request > ".(time()-300) . " GROUP BY u.login_id");
		$ad_cnt = dbr();
		if($ad_cnt['adm'] != 0 and $ad_cnt['Stats'] == 0) {
			$Aon = " <b class=red_txt>(A)</b>";
		}
		db(__FILE__,__LINE__,"select count(login_id) as dev from ${db_name}_users where login_id = '$dev2[login_id]' && last_request > ".(time()-300));
		$dev_cnt = dbr();
		if($dev_cnt['dev'] != 0) {
			$Don = " <b><font color=ffffff>(D)</b></font>";
		}
		echo "<tr><td width=50% nowrap>".$start_lnk."Active Users".$end_lnk.":</td><td width=50%><b>".$lr_result['onl']."</b>".$Aon." ".$Don."</td>";
	} else {
		echo "<td width=50% nowrap>Days Left:</td><td width=50%><b>$game_info[game_days_remaining]</b></td></tr><tr>";
		if($game_info[sd_day_num] >= 0){
		echo "<td width=50% nowrap>Days to SD:</td><td width=50%><b>$game_info[sd_day_num]</b></td></tr><tr>";
//		} elseif {
		}

		db(__FILE__,__LINE__,"select count(login_id) as onl from ${db_name}_users where login_id > 1 && login_id != 3 && last_request > ".(time()-300));
		$lr_result = dbr();
		if($user['login_id'] == 1 || $user['login_id'] == $owner_id) {
			$start_lnk = "<a href='active_users.php'>";
			$end_lnk = "</a>";
		}
		db(__FILE__,__LINE__,"select count(login_id) as adm from ${db_name}_users where login_id = 1 && last_request > ".(time()-300));
		$ad_cnt = dbr();
		if($ad_cnt['adm'] != 0) {
			$Aon = "<b class=red_txt>&nbsp;(A)</b>";
		}
		db(__FILE__,__LINE__,"select count(login_id) as dev from ${db_name}_users where login_id = '$owner_id' && last_request > ".(time()-300));
		$dev_cnt = dbr();
		if($dev_cnt['dev'] != 0) {
			$Don = " <b><font color=ffffff>(D)</b></font>";
		}
		echo "<td width=50% nowrap>".$start_lnk."Active Users".$end_lnk.":</td><td width=50%><b>{$lr_result['onl']}</b>".$Aon." ".$Don."</td>";
}
	echo "</tr></table></td></tr></table><br /><table cellspacing=0 cellpadding=2 class=all width=100%><tr><th>";
	echo print_name($user,1);
	echo "</th></tr><tr><td><table cellpadding=2 cellspacing=0 border=0 width=100%><tr>";

	echo "<td width=50% nowrap>Credits:</td><td width=50% nowrap>".number_format($user['cash'])."</td></tr>";

	if ($flag_research != 0) {
		echo "<tr><td width=50% nowrap>T. Units:</td><td width=50% nowrap>".number_format($user['tech'])."</td></tr>";
	}

	echo "<tr><td width=50% nowrap>Turns:</td><td width=50% nowrap>$user[turns] / $max_turns</td></tr>";
	if($user['turns_run'] < $turns_safe){
		$s_turns = $turns_safe - $user['turns_run'];
		echo "<tr><td width=50% nowrap>Safe Turns:</td><td width=50% nowrap>$s_turns</td></tr>";
	} elseif($user['turns_run'] == $turns_safe && $user['login_id'] != 1 && $user['login_id'] != $owner_id) {
		echo "<tr><td colspan=2><div align=\"center\"><b><b class=b1>Now Leaving Newbie Safety</b></div></td></tr>";
		dbn(__FILE__,__LINE__,"update ${db_name}_users set turns_run = turns_run + '1' where login_id = '$user[login_id]'");
		dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text, cat) values(".time().",'$user[login_name]','$user[login_id]','$user[login_id]','You have just left Newbie safty, which has been specified for this game by the Admin as <b>$turns_safe</b> turns.<p>This means that you are now attackable by any player who has passed the <b class=b1>turns before attack</b> period. <p>Good Luck.', 0)");
	}
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
		$eras = dbr();
		$era = $eras['value'];
	db3(__FILE__,__LINE__,"select * from races where race_id = '$user[race]' && era = $era");
	$user_race = dbr3();
	$user_race_name = $user_race['race_name'];
	echo "<tr><td>Race:</td><td>$user_race_name</td></tr>";
	echo "<tr><td>Fighter Kills:</td><td>$user[fighters_killed]</td></tr>";
//	echo "<tr><td>Fighters Lost:</td><td>$user[fighters_lost]</td></tr>";
	echo "<tr><td>Ships Killed:&nbsp;</td><td>$user[ships_killed]</td></tr>";
//	echo "<tr><td>Ships Lost:</td><td>$user[ships_lost]</td></tr>";
	echo "<tr><td width=50% nowrap>Score:</td><td width=50% nowrap>$user[score]</td></tr></table></td></tr></table>";

/*	echo "<br /><table cellspacing=0 cellpadding=2 class=all width=100%><tr><th>System Menu</th></tr><tr><td><table cellspacing=0 cellpadding=0 border=0>";
	echo "<tr><td align = center>:==|<a href=location.php>Star System</a>|==:";
	echo '</tr></td></table></tr></td></table>';*/

	echo(print_ship_info());

	echo "<br /><table cellspacing=0 cellpadding=2 class=all width=100%><tr><th>Voting</th></tr><tr><td><table width=\"100%\" cellspacing=0 cellpadding=0 border=0><tr><td align=\"center\">";
	echo "<div align=\"center\"><b><a href=\"http://topwebgames.com/in.asp?id=4621&uid=${user['login_id']}&alwaysreward=1\" target=\"_blank\" class=\"button\" title=\"Vote for us!\">Vote for us<br>at<br>Top Web Games</a></b></div>";
	echo '</tr></td></table></tr></td></table>';

	echo "<br /><table cellspacing=0 cellpadding=2 class=all width=100%><tr><th>Side Menu</th></tr><tr><td><table cellspacing=0 cellpadding=0 border=0><tr><td>";

	echo "<a href=location.php>Star System</a>";
	echo "<br /><a href=refer.php>Refer a friend</a>";
	echo "<br /><a href=http://www.quantum-star.com target=_blank>QS: Generations Forum</a>";
	db(__FILE__,__LINE__,"select count(login_id) as cl_num from ${db_name}_users where login_id > 5 && login_id != '$user[login_id]' && clan_id = '$user[clan_id]' && clan_id > 0 && last_request > ".(time()-300));
	$cl_result = dbr();
	echo "<br /><a href=clan.php>Clan Control</a>";
	if($cl_result['cl_num'] > 0 && $user['clan_id'] > 0) {
		echo " <b>($cl_result[cl_num])</b>";
	}

	if($enable_politics == 1) {
		echo "<br /><a href=politics.php>Politics</a>";
	}

	if($user['login_id'] == 1){
	} else { #player lower sidebar
		echo "<br /><a href=game_listing.php target=_top>GameList</a>";
	}
	echo '</tr></td></table></tr></td></table>';
	echo '</td><td width=20></td><td valign=top>'; //close right-side column and open central column of main table
}


// function to print out all output within the Generic page structure
function print_page($title,$text) {
	global $rs, $start_time, $code_base, $HTTP_ACCEPT_ENCODING, $enable_gzip, $gzip_level, $db_name, $user, $filename, $user_options, $url_prefix, $CONFIG;
	if($enable_gzip == 1)
	{
		include("includes/gzdoc.inc.php");
	}
	print_header($title);
	include_once("themes/".$user_options['theme']."/header.php");
	print_status();
	// insert translation step
	//require_once("includes/language/example.inc.php");
	//$text = translate($text);
	// end of translation
	echo "<br />".$text;
	include_once("themes/".$user_options['theme']."/footer.php");
	if($enable_gzip == 1)
	{
		GzDocOut($gzip_level);
	}
	exit();
}

// function designed for printing within a popup, removes status, and header includes
function print_page_popup($title,$text) {
	global $rs, $start_time, $code_base, $HTTP_ACCEPT_ENCODING, $enable_gzip, $gzip_level, $db_name, $user, $user_options;
	if($enable_gzip == 1)
	{
		include("includes/gzdoc.inc.php");
	}
	print_header($title);
	echo $text;
	include_once("themes/".$user_options['theme']."/footer.php");
	if($enable_gzip == 1)
	{
		GzDocOut($gzip_level);
	}
	exit();
}


#function that can be used create a viable input form. Adds hidden vars.
function get_var($title,$page_name,$text,$var_name,$var_default,$back_page) {
	global $_GET,$rs,$_POST,$border_prop,$url_prefix,$buy,$filename;

//	$ostr = '</div><blockquote>';
	$ostr = "<form name=get_var_form action=$page_name method=post onSubmit='submitonce(this);'>";
	$ostr .= "$text<p>";
	while (list($var, $value) = each($_GET)) {
		$ostr .= "<input type=hidden name=$var value='$value'>";
	}
	while (list($var, $value) = each($_POST)) {
		$ostr .= "<input type=hidden name=$var value='$value'>";
	}
	if($var_name == 'sure') {
		$ostr .= '<input type=hidden name=sure value=yes>';
		if(isset($back_page)) {
			$ostr .= '<input type=submit name=submit value=Yes> <input type="Button" width="30" value="No" onclick="javascript: self.location=\''.$url_prefix.'/'.$back_page.'\'"> </form>';
		} else {
			$ostr .= '<input type=submit name=submit value=Yes> <input type="Button" width="30" value="No" onclick="javascript: history.back()"> </form>';
		}
	} elseif(($var_name == 'passwd') || ($var_name == 'passwd_verify')) {
		$ostr .= "<input type=password name=$var_name value='$var_default' size=20>";
		$ostr .= ' <input type=submit value=Submit></form>';
	} elseif($var_name == 'passwd2') {
		$ostr .= "<input type=password name=$var_name value='$var_default' size=20>";
		$ostr .= ' <input type=submit value=Submit></form>';
	} elseif($var_name == 'text') {
		$ostr .= "<textarea name=$var_name cols=50 rows=20 wrap=soft>".stripslashes($var_default)."</textarea>";
		$ostr .= "<input type=hidden name=rs value=".htmlentities($rs).">";
		$ostr .= '<p><input type=submit value=Submit></form>';
	} elseif($var_name == 'msg') { //output phpBB text options
		$ostr .= "<table border=0 cellspacing=1 cellpadding=2><tr><td colspan=7>&nbsp;<a href='javascript:bbstyle(-1)'>Close All Tags</a></td></tr><tr>
		<td><table border=0 cellspacing=1 cellpadding=2 width=100%><tr><td><input type='button' class='button' accesskey='b' name='addbbcode0' value=' B ' style='font-weight:bold; width: 30px' onClick='bbstyle(0)' /></td><td><input type='button' class='button' accesskey='i' name='addbbcode2' value=' i ' style='font-style:italic; width: 30px' onClick='bbstyle(2)' /></td><td><input type='button' class='button' accesskey='bi' name='addbbcode4' value=' Bi ' style='font-style:italic; font-weight:bold; width: 30px' onClick='bbstyle(4)' /></td><td><input type='button' class='button' accesskey='u' name='addbbcode6' value=' u ' style='text-decoration: underline; width: 30px' onClick='bbstyle(6)' /></td><td><input type='button' class='button' accesskey='p' name='addbbcode8' value='Img' style='width: 40px'  onClick='bbstyle(8)' /></td><td><input type='button' class='button' accesskey='w' name='addbbcode10' value='URL' style='text-decoration: underline; width: 40px' onClick='bbstyle(10)' /></td><td><select name=colorchanger onChange=\"bbfontstyle(this.form.colorchanger.options[this.form.colorchanger.selectedIndex].value, '[/color]')\" ><option style='color:black; background-color: #FFFFFF' value='#FFFFFF' class=genmed>Default</option>
  <option style='color:darkred; background-color: #DEE3E7' value='darkred' class=genmed>Dark Red</option>
  <option style='color:red; background-color: #DEE3E7' value='red' class=genmed>Red</option>
  <option style='color:orange; background-color: #DEE3E7' value='orange' class=genmed>Orange</option>
  <option style='color:brown; background-color: #DEE3E7' value='brown' class=genmed>Brown</option>
  <option style='color:yellow; background-color: #DEE3E7' value='yellow' class=genmed>Yellow</option>
  <option style='color:green; background-color: #DEE3E7' value='green' class=genmed>Green</option>
  <option style='color:olive; background-color: #DEE3E7' value='olive' class=genmed>Olive</option>
  <option style='color:cyan; background-color: #DEE3E7' value='cyan' class=genmed>Cyan</option>
  <option style='color:blue; background-color: #DEE3E7' value='blue' class=genmed>Blue</option>
  <option style='color:darkblue; background-color: #DEE3E7' value='darkblue' class=genmed>Dark Blue</option>
  <option style='color:indigo; background-color: #DEE3E7' value='indigo' class=genmed>Indigo</option>
  <option style='color:violet; background-color: #DEE3E7' value='violet' class=genmed>Violet</option>
  <option style='color:white; background-color: #DEE3E7' value='white' class=genmed>White</option>
  <option style='color:black; background-color: #DEE3E7' value='black' class=genmed>Black</option>
</select></td></tr></table><textarea name=text cols=50 rows=15 wrap=virtual tabindex=\"2\">".stripslashes($var_default)."</textarea><p><input type=submit value=Submit></td><td width=100 valign=top><div align=\"center\"><br /><br /><table cellspacing=0 cellpadding=2 width=100 class=all><tr><th colspan='2'>Emoticons</th></tr>
		              <tr align='center' valign='middle'>
                        <td width=50 class=emot_td><a href=javascript:emoticon('[smile]')><img src='images/smiles/lol.gif' border='0' alt='Smile' title='Smile' /></a></td>
                        <td width=50 class=notopright><a href=javascript:emoticon('[sad]')><img src='images/smiles/sad.gif' border='0' alt='Sad' title='Sad' /></a></td>
                      </tr>
                      <tr align='center' valign='middle'>
                        <td width=50 class=emot_td><a href=javascript:emoticon('[surp]')><img src='images/smiles/surp.gif' border='0' alt='Surprised' title='Surprised' /></a></td>
                        <td width=50 class=notopright><a href=javascript:emoticon('[help]')><img src='images/smiles/help.gif' border='0' alt='Help' title='Help' /></a></td>
                      </tr>
                      <tr align='center' valign='middle'>
                        <td width=50 class=emot_td><a href=javascript:emoticon('[cool]')><img src='images/smiles/cool.gif' border='0' alt='Cool' title='Cool' /></a></td>
                        <td width=50 class=notopright><a href=javascript:emoticon('[check]')><img src='images/smiles/check.gif' border='0' alt='Check This' title='Check This' /></a></td>
                      </tr>
                      <tr align='center' valign='middle'>
                        <td width=50 class=emot_td><a href=javascript:emoticon('[wink]')><img src='images/smiles/wink.gif' border='0' alt='Wink' title='Wink' /></a></td>
                        <td width=50 class=notopright><a href=javascript:emoticon('[wtf]')><img src='images/smiles/wtf.gif' border='0' alt='WTF' title='WTF' /></a></td>
                      </tr>
                      <tr align='center' valign='middle'>
                        <td width=50 class=emot_td><a href=javascript:emoticon('[evil]')><img src='images/smiles/evil.gif' border='0' alt='Evil' title='Evil' /></a></td>
                        <td width=50 class=notopright><a href=javascript:emoticon('[tongue]')><img src='images/smiles/tongue.gif' border='0' alt='Tongue' title='Tongue' /></a></td>
                      </tr>
                      <tr align='center' valign='middle'>
                        <td width=50 class=emot_td><a href=javascript:emoticon('[mad]')><img src='images/smiles/mad.gif' border='0' alt='Mad' title='Mad' /></a></td>
                        <td width=50 class=notopright><a href=javascript:emoticon('[ass]')><img src='images/smiles/ass.gif' border='0' alt='Ass' title='Ass' /></a></td>
                      </tr>
                      <tr align='center' valign='middle'>
                        <td width=50 class=emot_td><a href=javascript:emoticon('[mad67]')><img src='images/smiles/mad67.gif' border='0' alt='Mad2' title='Mad2' /></a></td>
                        <td width=50 class=notopright><a href=javascript:emoticon('[scream]')><img src='images/smiles/scream.gif' border='0' alt='Scream' title='scream' /></a></td>
                      </tr>
                      <tr align='center' valign='middle'>
                        <td width=50><a href=javascript:emoticon('[upto]')><img src='images/smiles/upto.gif' border='0' alt='Up To' title='Up To' /></a></td>
						<td width=50 class=border_left><a href=javascript:emoticon('[thumb]')><img src='images/smiles/thumb.gif' border='0' alt='Thumbs Up' title='Thumbs Up' /></a></td>
                      </tr>
                      <tr align='center' valign='middle'>
                        <td width=50><a href=javascript:emoticon('lol')><img src='images/smiles/lold.gif' border='0' alt='lol' title='lol' /></a></td>
                                                <td width=50 class=border_left></td>
                      </tr>




		</table></div></td></tr></table>";
		$ostr .= "<input type=hidden name=rs value=".htmlentities($rs).">";
		$ostr .= '</form>';




	} elseif($var_name == 'nws') { //output phpBB text options





		$ostr .= "<table border=0 cellspacing=1 cellpadding=2><tr>
		<td><textarea name=qsservernews cols=75 rows=15 wrap=virtual>".stripslashes($var_default)."</textarea><p><input type=submit value=Submit></td></tr></table>";
		$ostr .= "<input type=hidden name=rs value=".htmlentities($rs).">";
		$ostr .= '</form>';
	} else {
		$ostr .= "<input name=$var_name value='$var_default' size=20>";
		$ostr .= ' <input type=submit value=Submit></form>';
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
function send_message($to,$text,$cat=3) {
	global $db_name,$user;
	//$text = addslashes($text);
	if($to == -5 && $user['clan_id'] > 0){
	dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text, clan_id, cat) values(".time().",'$user[login_name]','$user[login_id]','$to','$text','$user[clan_id]','$cat')");
	} else {
	dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text, cat) values(".time().",'$user[login_name]','$user[login_id]','$to','$text','$cat')");
	}
}

#sends a special alert message to the admin.
function send_alert($title,$details,$caveat) {
	global $db_name,$user;

	if ( $user['clan_id'] != 0 ) {
		$username = "<font color=\"#$user[clan_sym_color]\">$user[login_name]</font> "
			. "(<font color=$user[clan_sym_color]>$user[clan_sym]</font>)";
	} else {
		$username = "<b>$user[login_name]</b>";
	}
	$alert_builtins = array(
		"Time" => date( "M d - H:i", time() ),
		"Player" => $username,
		"Location" => "SS #$user[location]" );
	$text = "<h3>$title</h3>";
	foreach ( $alert_builtins + $details as $k => $v ) {
		$text .= "<b>$k:</b> $v<br />";
	}
	$text .= "<br />$caveat";
	dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id,login_id,text,cat) "
		. "values(".time().",'Alert','1','1','$text','101')");
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
	} else {//player messages
		if(($allow_signatures == 1) && ($user_options['show_sigs'] == 1)){//with sigs
			db(__FILE__,__LINE__,"select m.message_id, m.timestamp, m.text, m.sender_id, m.sender_id as login_id, u.sig from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' && m.sender_id = u.login_id	order by timestamp desc");
		} else {//without sigs
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
		$error_str .= "<br /><table cellspacing=0 cellpadding=2 class=all width=650 align=center><tr><th style='font-size: 8pt; text-align: left;'>";
		$error_str .= date( "M d - H:i",$messages['timestamp']);
		$error_str .= " - ";
		$error_str .= print_name($messages);
		$error_str .= "</th></tr><tr><td width=100%>";

		$messages['text'] = stripslashes($messages['text']);
		//$m_text = print_name($messages).": ".$messages[text]; #what does this line do? its un-used?!
		$error_str .= "<blockquote>";
		$error_str .= "$messages[text]".stripslashes(mcit("".$messages['sig']));
		$error_str .= "</blockquote>";
		$error_str .= "</td></tr><tr><td width=100% style='border-top: $border_prop;'><a href=message.php?target=$messages[sender_id]&reply_to=$messages[message_id]>Reply</a> - <a href=message.php?forward=$messages[message_id]>Forward</a> - <a href=diary.php?log_ent=$messages[message_id]>Log</a>";

		if($admin_forum == 1 && $user['login_id'] == -1) {#admin link to delete messages in forum
			$error_str .= " - <a href=forum.php?killmsg=$messages[message_id]>Delete</a>";
		} elseif($admin_forum == 1 && $user['login_id'] == -5) {#admin link to delete messages in clan forum
				$error_str .= " - <a href=forum.php?killmsg=$messages[message_id]&clan_forum=1>Delete</a>";
		}

		if($full == 1 && $counted[0] > 1) {//player link to delete messages (with checkboxes)
			$error_str .= " - <a href=mpage.php?killmsg=$messages[message_id]>Delete</a> - <input type=checkbox name=del_mess[$messages[message_id]] value=$messages[message_id]>";
		} elseif($full == 1) {//player link to delete message
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


#function that damages a ship with a specified amount of damage.
#send a negative number as the first arguement to destroy a ship outright.

function damage_ship($amount,$fig_dam,$s_dam,$from,$target,$target_ship) {
	global $db_name,$query,$user_ship,$preptarget;

	#set the shields down first off (if needed).
	if($s_dam > 0){
		$target_ship['shields'] -= $s_dam;
		if($target_ship['shields'] < 0){
			$target_ship['shields'] == 0;
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = shields - '$s_dam' where ship_id = '$target_ship[ship_id]'");
	}

	if(eregi("rd",$user_ship['config']) && $fig_dam == -1) {
		$kill_ship = 1;
		unset($fig_dam);
		#$fig_dam = 0;
	}

	#take the fighters down next (if needed).
	if($fig_dam > 0){
		$target_ship['fighters'] -= $fig_dam;
		if($target_ship['fighters'] < 0){
			$target_ship['fighters'] == 0;
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set fighters = fighters - '$fig_dam' where ship_id = '$target_ship[ship_id]'");
	}

	#don't want to hurt the admin now do we?
	if($target['login_id'] != 1) {

		#only play with the amount distribution if there is no value to amount
		if($amount > 0){

			$shield_damage = $amount;
			if($shield_damage > $target_ship['shields']) {
				$shield_damage = $target_ship['shields'];
			}
			$amount -= $shield_damage;

		}

		//----------------------------------------------------------------------------------------------------
		// If the attacking vessel is a Raider class, it will only do sufficient damage to remove all defenses
		// from the ship while still leaving it intact to be raided or claimed as required
		// This limited damage also effects bombs used by Raiders.

		if((eregi("rd",$user_ship['config'])) && ($user_ship['login_id'] == $from['login_id']) && ($amount >= $target_ship['fighters'] || $amount < 0) && ($kill_ship != 1)) {
			if($target_ship['fighters'] > 10) { //fix for 0 fighters
				$amount = $target_ship['fighters'] - mt_rand(1,9);
			} else {
				$amount = 0;
			}
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set upgrades = (upgrades + num_ew + num_pc + num_sa + num_dt + num_ot), num_ew = 0, num_pc = 0, num_sa = 0, num_dt = 0, num_ot = 0 where ship_id = '$target_ship[ship_id]'"); //all upgrades are destroyed
		}

		//----------------------------------------------------------------------------------------------------

		if((($amount >= $target_ship['fighters'] || $amount < 0) && (!eregi("rd",$user_ship['config']))) || isset($kill_ship)) {	//destroy ship if attacker not a raider otherwise allow ship to survive, this necessary since although ships may have no fighters they will have shields which will require an attack
			//Minerals go to the system
			if($from['location'] != 1 && ($target_ship['fuel'] > 0 || $target_ship['metal']) > 0){
				dbn(__FILE__,__LINE__,"update ${db_name}_stars set fuel = fuel + ".round($target_ship[fuel]*(mt_rand(20,80)/100)).", metal = metal + ".round($target_ship[metal]*(mt_rand(40,90)/100))." where star_id = $target_ship[location]");
			}

			dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where ship_id = '$target_ship[ship_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$target_ship[fighters]', ships_killed = ships_killed + '1', ships_killed_points = ships_killed_points + '$target_ship[point_value]' where login_id = '$from[login_id]'");

			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$target_ship[fighters]', ships_lost = ships_lost + '1', ships_lost_points = ships_lost_points + '$target_ship[point_value]' where login_id = '$target[login_id]'");

			if(eregi("Escape",$target_ship['class_name'])) { // escape pod lost
				dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '1', ship_id = '1', last_attack = ".time().", last_attack_by = '$from[login_name]' where login_id = '$target[login_id]'");
				return 1;
			} else { // normal ship lost
				//don't bother putting an AI into a new ship to command etc.
				if($target['login_id'] > 5) {

					if($target['ship_id'] != $target_ship['ship_id']) {
						$new_ship_id = $target['ship_id'];
					} else {

						//need to rewrite so it moves player to new ship
						//probably better to add to the external results



						db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where login_id = '$target_ship[login_id]' && location != 0 limit 1");//target??? - to be watched.
						$other_ship = dbr();

						if(isset($other_ship['ship_id'])) { // jump to other ship
							$new_ship_id = $other_ship['ship_id'];
						} else {
							create_escape_pod($target); // build the escape pod
							if($target[bounty] > 0 && $alternate_bounty_sys == 1) {
								send_message($from[login_id],"You have claimed 50% of the <b>$target[bounty]</b> Credit bounty that was on <b class=b1>$target[login_name]</b>s head for destroying his/her fleet and consigning them to an <b class=b1>Escape Pod</b>.");
								post_news("50% of the <b>$target[bounty]</b> bounty on <b class=b1>$target[login_name]</b> has been claimed by <b class=b1>$from[login_name]</b> for the destruction of their fleet. The player remains at large in an <b class=b1>Escape Pod</b> with the remaining bounty on his head.");
								$bounty_a = int($target[bounty] * .5);
								dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + '$bounty_a' where login_id = '$from[login_id]'");
								dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = bounty - '$bounty_a' where login_id = '$target[login_id]'");
							}
							return 2;
						}
					}
					// set ships_killed

					if($target['login_id'] > 5) {
						db(__FILE__,__LINE__,"select location from ${db_name}_ships where ship_id = '$new_ship_id'");
						$other_ship = dbr();
					} else {
						$other_ship['location'] = 1;
					}

					dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$new_ship_id', location = '$other_ship[location]', last_attack =".time().", last_attack_by = '$from[login_name]' where login_id = '$target[login_id]'");
				}
			}
			return 1;
		} else { // ship not destroyed
			dbn(__FILE__,__LINE__,"update ${db_name}_users set last_attack = ".time().", last_attack_by = '$from[login_name]' where login_id = '$target[login_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set fighters = fighters - '$amount', shields = shields - '$shield_damage' where ship_id = '$target_ship[ship_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$amount' where login_id = '$target[login_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$amount' where login_id = '$from[login_id]'");
			if(eregi("rd",$user_ship['config']) && isset($preptarget)) {
				db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$target_ship[ship_id]'");
				$rdtest = dbr();
				if($rdtest['fighters'] < 10 && $rdtest['shields'] == 0 ) {
					$rdtext = "A successful attack by your raider has given you the opportunity to attempt a raid against this vessel.<br /><br />";
					echo "<script>self.location='raid.php?rdtarget=$target_ship[ship_id]&rdtext=$rdtext';</script>";
					exit;
				}
			}else {
				return 0;
			}
		}
	}
	return 0;
}


#function that allows a player to be retired.
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
		dbn(__FILE__,__LINE__,"delete from ${db_name}_clan_relations where clan_id = '$target_user[clan_id]'");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_bank_account where login_id = '$target_user[login_id]'");

		//set retire time if needed for rejoin-delay function
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'rejoin_delay'");
		$state = dbr();
		if($state[value] == 1) {
			dbn(__FILE__,__LINE__,"update user_accounts set ".$db_name." = ".time()." where login_id = ".$user[login_id]);
		}

	        db(__FILE__,__LINE__,"select * from ${db_name}_clans where clan_id = $user[clan_id]");
        	$clan = dbr();
	        if(($clan['leader_id'] == $user['login_id'])) {
	        	post_news("<b class=b1>$user[login_name]</b> disbanded the <b class=b1>$clan[clan_name](<font color=$clan[sym_color]>$clan[symbol]</font>)</b> Clan.");

	        	dbn(__FILE__,__LINE__,"update ${db_name}_users set clan_leader = 0 where clan_id = $user[clan_id] && clan_leader = '1'");
		        dbn(__FILE__,__LINE__,"update ${db_name}_users set clan_id = 0, clan_sym = '', clan_sym_color = '' where clan_id = $user[clan_id]");
		        dbn(__FILE__,__LINE__,"update ${db_name}_planets set clan_id = -1 where clan_id = $user[clan_id]");
		        dbn(__FILE__,__LINE__,"update ${db_name}_ships set clan_id = -1 where clan_id = $user[clan_id]");
		        dbn(__FILE__,__LINE__,"update ${db_name}_mines set clan_id = 0 where clan_id = $user[clan_id]");
		        dbn(__FILE__,__LINE__,"delete from ${db_name}_clans where clan_id = $user[clan_id]");
		        dbn(__FILE__,__LINE__,"delete from ${db_name}_messages where clan_id = $user[clan_id]");
		        dbn(__FILE__,__LINE__,"delete from ${db_name}_clan_relations where clan_id = $user[clan_id]");
	                $user['clan_id'] = 0;
	                $user['clan_sym'] = "";
	                $user['clan_sym_color'] = "";
		        update_clans();
	        	insert_history($user['login_id'],"Disbanded $clan[clan_name] clan.");
		}


		dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where login_id = $target");
		dbn(__FILE__,__LINE__,"update ${db_name}_bilkos set bidder_id = 0, timestamp = ".time()." where bidder_id = $target");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set owner_name = 'Retired Player', owner_id=0, pass='' where owner_id = '$target'");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_diary where login_id = $target");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_user_options where login_id = $target");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_users where login_id = $target");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_fleets where login_id = $target");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_bank_account where login_id = $target");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_upgrade_units where login_id = $target");
		dbn(__FILE__,__LINE__,"update ${db_name}_politics set login_id = 0, login_name = 0, timestamp = 0 where login_id = '$target'");
	}
}

// retrieve the star data
function get_star() {
	global $user, $star, $db_name;
	db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = '{$user['location']}'");
	$star = dbr();
}


// function to find an opponents relation to user's clan or user if indep
// returns string to be printed in print_name() function

function find_designation_id($op_id){
        global $user,$db_name, $user_ship;
        $rel=0;
        if($user[clan_id] != 0)
                {
                if(empty($op_id))
                        {
                        return "";
                        }

                        db4(__FILE__,__LINE__,"select nap_agree from ${db_name}_clan_relations where clan_id = $user[clan_id] and v_clan_id = $op_id");
                        $otherclan = dbr4();
                        db4(__FILE__,__LINE__,"select nap_agree from ${db_name}_clan_relations where clan_id = $op_id and v_clan_id = $user[clan_id]");
                        $thisclan = dbr4();
                        if($otherclan[nap_agree] == 3 and $thisclan[nap_agree] == 3)
                                {
                                $rel = 3;
                                }
                        else if($otherclan[nap_agree] == 1 or $thisclan[nap_agree] == 1)
                                {
                                $rel = 1;
                                }
                        else if($otherclan[nap_agree] == 3 and $thisclan[nap_agree] == 2)
                                {
                                $rel = 2;
                                }
                        else if($otherclan[nap_agree] == 2 and $thisclan[nap_agree] == 3)
                                {
                                $rel = 2;
                                }
                        else if($otherclan[nap_agree] == 2 and $thisclan[nap_agree] == 2)
                                {
                                $rel = 2;
                                }
                        else
                                {
                                $rel = 0;
                                }
                }
        return $rel;
}

function find_designation($op_id){
	global $user,$db_name, $user_ship;
	$rel=NULL;
	if($user[clan_id] != 0)
		{
		if(empty($op_id))
			{
			return "";
			}

			db4(__FILE__,__LINE__,"select nap_agree from ${db_name}_clan_relations where clan_id = $user[clan_id] and v_clan_id = $op_id");
			$otherclan = dbr4();
                        db4(__FILE__,__LINE__,"select nap_agree from ${db_name}_clan_relations where clan_id = $op_id and v_clan_id = $user[clan_id]");
                        $thisclan = dbr4();
                        if($otherclan[nap_agree] == 3 and $thisclan[nap_agree] == 3)
                                {
				$rel = "<b><<font color=#FFFF00>A</font>></b>";
                                }
                        else if($otherclan[nap_agree] == 1 or $thisclan[nap_agree] == 1)
                                {
				$rel = "<b><<font color=#FF0000>E</font>></b>";
                                }
                        else if($otherclan[nap_agree] == 3 and $thisclan[nap_agree] == 2)
                                {
				$rel = "<b><<font color=#0000FF>N</font>></b>";
                                }
                        else if($otherclan[nap_agree] == 2 and $thisclan[nap_agree] == 3)
                                {
				$rel = "<b><<font color=#0000FF>N</font>></b>";
                                }
                        else if($otherclan[nap_agree] == 2 and $thisclan[nap_agree] == 2)
                                {
				$rel = "<b><<font color=#0000FF>N</font>></b>";
                                }
                        else
                                {
                                $rel = "";
                                }
		}
	return $rel;
}

//function to add to bm visit stat for user

function bm_visit($num) {
	global $user,$db_name;
	dbn(__FILE__,__LINE__,"update ${db_name}_users set bm_visit = bm_visit + '$num' where login_id = '$user[login_id]'");
}

// ------------------------------------------------------------------
// Mine detect and damage functions (15/08/2002) - Maugrim_The_Reaper
// ------------------------------------------------------------------

/*
// Name: Mine Detect/Damage Functions
// By: Maugrim The Reaper (bpaddy2@yahoo.com)
// Purpose: Detect and apply mine damage
// Date Completed: 16/08/2002
// Version: 0.7
*/

// -----------------------------------------------------------------------------------------------------------------
// INFO:
//
// Functions have been modified so that ships are damaged immediately for each cluster found (starting with first
// set). This allows News, Scores and Messages to be accurately updated with the Attacker's name and other info. The
// oldest clusters are used up first since they will be the first to attack. I'm reverting to my first attempt for
// this and leaving out past modifications at present to see if this works. The damage function has been modified
// from that included with location.php previously to avoid location.php clutter. Damage will now only occur when
// ENTERING or LEAVING the system. Ships already in the system are deemed safe from mines. Players already insystem
// may therefore use the Forum, and other menus and return to the Star System without triggering mines.
// Since damage is calculated per cluster, if all ships targeted are destroyed remaining clusters are NOT deleted
// but REMAIN in place for another target. The Mine system now uses three standard functions to operate... Also my
// proposed mine plan results in Graviton Mines dealing damage first, with Hornet Mines dealing their damage after.
// Graviton Mines take precedence... Also, all players now have a 66% of being targeted by valid mines with a
// reduction to 50% if the command ship is fitted with a Gravitronic Scanner. A later addition will make these
// chances increase the more clusters are present in the Star System.
// -----------------------------------------------------------------------------------------------------------------



// ---------------------------------------------------
// Function to deal damage from Graviton Mine clusters
// ---------------------------------------------------
function grav_damage($mine_count,$grav) {
	global $db_name,$user;
	if($mine_count != 0){

		get_star();
		$mine_damage = 75 * $mine_count;
		$ship_counter = 0;
		$dam_victim = array();
		$destroyed_ships = 0;

		db(__FILE__,__LINE__,"select * from ${db_name}_ships where location = '$grav[location]' && login_id = '$user[login_id]'");
		$target_ship = dbr();

		if($mine_count >= 10){
			while($target_ship){
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = 0 where ship_id = '$target_ship[ship_id]'");
				$target_ship = dbr();
			}
		}

		while($target_ship) {
			db(__FILE__,__LINE__,"select login_name,login_id,ship_id from ${db_name}_users where login_id = '$user[login_id]'");
			$target = dbr();
			$temp101 = 0;
			$ship_counter++;
			$temp101 = damage_ship($mine_damage,0,0,$grav,$target,$target_ship);
			if($temp101 > 0) {
				$dam_victim[$target[login_id]] .= "<br /><b class=b1>$target_ship[ship_name]</b> ($target_ship[class_name]) - Destroyed";
				post_news("<b class=b1>$grav[login_name]</b>\'s Graviton Mines destroyed <b class=b1>$target[login_name]</b>\'s $target_ship[class_name].");
			}
			$target_ship = dbr();
		}#end while loop

		#loop to send out a message to each player.
		foreach($dam_victim as $victim_id => $ship_list) {
			$ships_hit = substr_count($ship_list, "<b class=b1>");
			$ships_killed = substr_count($ship_list, "- Destroyed");
			send_message($victim_id,"<b class=b1>$grav[login_name]</b> has laid Graviton Mine cluster(s) in Star System 	#<b>$user_ship[location]</b>.<br />This mine cluster exploded close to <b>$ships_hit</b> of your ships doing <b>$mine_damage</b> damage to each.<br /><br />Of those targeted, <b>$ships_killed</b> where destroyed by the blast. You will be informed in separate messages if other clusters were also part of this attack.<br /><br />Shown below is a complete listing of all your ships destroyed by this Mine Cluster:<br />$ship_list");
			send_message($grav[login_id],"The Graviton Mines you planted in Star System #<b>$user_ship[location]</b> have targeted <b>$ships_hit</b> ship(s) belonging to <b class=b1>$user[login_name]</b> doing <b>$mine_damage</b> damage to each.<br /><br />Of those targeted, <b>$ships_killed</b> where destroyed by the blast.");
		}
	}
}



// -------------------------------------------------
// Function to deal damage from Hornet Mine clusters
// -------------------------------------------------
// Incorporate ai_bomb.php changes allowing weak or
// strong ships to be targeted
// -------------------------------------------------

function horn_damage($hmine_count,$horn) {
	global $user,$db_name;

	if($hmine_count != 0){
		get_star();
		$mine_damage = 30;
		$ships_killed = 0;
		$mine_counter = 0;
		$mine_damage_horn = $hmine_count * $mine_damage;
		if($horn[sequence] == 1) {
			db(__FILE__,__LINE__,"select * from ${db_name}_ships where location = '$horn[location]' && login_id = '$user[login_id]' && fighters >= 0 group by fighters limit 1");
			$targ_ship = dbr();
		} else {
			db(__FILE__,__LINE__,"select * from ${db_name}_ships where location = '$horn[location]' && login_id = '$user[login_id]' && fighters >= 0 group by fighters desc limit 1");
			$targ_ship = dbr();
		}
		$temp106 = damage_ship($mine_damage_horn,0,0,$horn,$user,$targ_ship);
		if($temp106 > 0){
			$ships_killed++;
			post_news("<b class=b1>$horn[login_name]</b>\'s Hornet Mines destroyed <b class=b1>$user[login_name]</b>\'s $other_ships[class_name].");
			send_message($user[login_id],"<b class=b1>$horn[login_name]</b>\'s Hornet Mines destroyed your $targ_ship[ship_name] ($targ_ship[class_name]).");
			send_message($horn[login_id],"The Hornet Mines you planted in Star System #<b>$user_ship[location]</b> have targeted a ship belonging to <b class=b1>$user[login_name]</b> doing <b>$mine_damage_horn</b> damage. The ship (a <b class=b1>$targ_ship[class_name]</b>) was completely destroyed.");
		} else {
			send_message($user[login_id],"<b class=b1>$horn[login_name]</b>\'s Hornet Mines did <b>$mine_damage_horn</b> damage to your $targ_ship[ship_name] ($targ_ship[class_name]).");
			send_message($horn[login_id],"The Hornet Mines you planted in Star System #<b>$user_ship[location]</b> have targeted a ship belonging to <b class=b1>$user[login_name]</b> doing <b>$mine_damage_horn</b> damage. The ship (a <b class=b1>$targ_ship[class_name]</b>) unfortunately survived the attack.");
		}
	}
}

#send_message($victim_id,"<b class=b1>$horn[login_name]</b> has laid Hornet Mine cluster(s) in Star System 	#<b>$horn[location]</b>."); //test message for confirming mine placement

// -----------------------------------------------------------------------------------------
// Function to detect both types of Mine and determine when cluster damage should be applied
// using one of the two Mine damage functions in each case.
// -----------------------------------------------------------------------------------------
function detect_mines(){
	global $db_name,$user,$user_ship,$query;

	// ----------------------------------------------------------------------
	// Statement to gather and prepare for info on all present Graviton Mines
	// ----------------------------------------------------------------------

	$tex = "";
	$gmine_total = 0;
	$cap_count = 0;
	$caph_count = 0;
	$hmine_total = 0;

	$clan_id = $user[clan_id];
	$v_clan_id = $user[v_clan_id];
	$rel_col = "x_".$clan_id;
	$rel_colx = "x_".$v_clan_id;

	$mine_loc = $user[location];

	if($user[clan_id] != 0){
		db2(__FILE__,__LINE__,"select * from ${db_name}_mines where mine_type = 1 && location = '$mine_loc' && cluster != 0 && clan_id != '$user[clan_id]'");
		$grav = dbr2();
	}else{
		db2(__FILE__,__LINE__,"select * from ${db_name}_mines where mine_type = 1 && location = '$mine_loc' && cluster != 0 && v_clan_id != '$user[v_clan_id]'");
		$grav = dbr2();
	}

	// ----------------------------------------------------------------------------------------------
	// Start of check for Graviton Mines, clusters found will do damage as per grav_damage() function
	// ----------------------------------------------------------------------------------------------

	while($grav){
		$mine_count = 0;
		$test606 = 0;
		#$shps = "";
		db(__FILE__,__LINE__,"select count(ship_id) as ships_remaining from ${db_name}_ships where location = '$grav[location]' && login_id = '$user[login_id]'");
		$shps = dbr();
		if($shps[ships_remaining] <= 0) {
			next;
		}elseif(($grav[clan_id] != 0) && ($turns_safe < $user[turns_run]) && ($user[ship_id] > 1)){
			if($grav[rel_target] == 2){
				$mine_count = $grav[cluster];
				$gmine_total = $gmine_total + $mine_count;
				$test606 = round(mt_rand(1,1000));
				if((eregi("mw",$user_ship[config])) && ($test606 >= 900)) {
					dbn(__FILE__,__LINE__,"update ${db_name}_users set grav_mine = grav_mine + '$mine_count' where login_id = '$user[login_id]'");
					$cap_count = $cap_count + $mine_count;
					post_news("<b class=b1>Graviton Mines</b> have targeted <b class=b1>$user[login_name]</b>\'s Ships. However the target apparently evaded damage with a Mine Sweeper.");
				}else{
					post_news("<b class=b1>Graviton Mines</b> have targeted <b class=b1>$user[login_name]</b>\'s Ships");
					grav_damage($mine_count, $grav);
				}
				dbn(__FILE__,__LINE__,"update ${db_name}_mines set cluster = cluster - '$grav[cluster]' where mine_id = '$grav[mine_id]'");
			}else{
				next;
			}
		}elseif(($grav[clan_id] == 0) && ($turns_safe < $user[turns_run]) && ($user[ship_id] > 1)){
			if($grav[rel_target] == 2){
				$mine_count = $grav[cluster];
				$gmine_total = $gmine_total + $mine_count;
				$test606 = round(mt_rand(1,1000));
				if((eregi("mw",$user_ship[config])) && ($test606 >= 900)) {
					dbn(__FILE__,__LINE__,"update ${db_name}_users set grav_mine = grav_mine + '$mine_count' where login_id = '$user[login_id]'");
					$cap_count = $cap_count + $mine_count;
					post_news("<b class=b1>Graviton Mines</b> have targeted <b class=b1>$user[login_name]</b>\'s Ships. However the target apparently evaded damage with a Mine Sweeper.");
				}else{
					post_news("<b class=b1>Graviton Mines</b> have targeted <b class=b1>$user[login_name]</b>\'s Ships");
					grav_damage($mine_count, $grav);
				}
				dbn(__FILE__,__LINE__,"update ${db_name}_mines set cluster = cluster - '$grav[cluster]' where mine_id = '$grav[mine_id]'");
			}else{
				next;
			}
		}
		$grav = dbr2();
	}

	if($gmine_total > 0) {
		$mine_damage_grav = ($gmine_total - $cap_count) * 10;
		$tex .= "<b>Graviton Mine Cluster(s) Encountered:</b><br /><p>";
		$tex .= "On entering/leaving this Star System (#$mine_loc) you were targeted by cluster(s) of <b>$gmine_total</b> <b class=b1>Graviton Mines</b>. ";
		$tex .= "The Mines detonated near your ships, which were each damaged by <b>$mine_damage_grav</b> in the resulting spacial distortions. ";
		if(eregi("mw",$user_ship[config])) {
			$tex.= "Your Mine-Sweeper captured <b>$cap_count</b> of the offending <b class=b1>Graviton Mines</b>!";
		}
		$tex .= "<br /><br />There is a possibility that more <b class=b1>Graviton Mine</b> clusters have been laid near this Star System so caution is advised when continuing. You have been warned...<br /><p>Detailed information on the damage taken by your fleet has been sent to you in a separate message for each Mine Cluster encountered.<br /><br /><p>";
	}


	// ----------------------------------------------------
	// Statement to gather info on all present Hornet Mines
	// ----------------------------------------------------

	#$mine_loc = $user[location]; //to be removed!!!
	if($user[clan_id] != 0){
		db2(__FILE__,__LINE__,"select * from ${db_name}_mines where mine_type = 2 && location = '$mine_loc' && cluster != 0 && clan_id != '$user[clan_id]'");
		$horn = dbr2();
	}else{
		db2(__FILE__,__LINE__,"select * from ${db_name}_mines where mine_type = 2 && location = '$mine_loc' && cluster != 0 && v_clan_id != '$user[v_clan_id]'");
		$horn = dbr2();
	}

	// --------------------------------------------------------------------------------------------
	// Start of check for Hornet Mines, clusters found will do damage as per horn_damage() function
	// --------------------------------------------------------------------------------------------
	while($horn){
		$mine_count = 0;
		$test606 = 0;
		$shps = "";
		db(__FILE__,__LINE__,"select count(ship_id) as ships_remaining from ${db_name}_ships where location = '$mine_loc' && login_id = '$user[login_id]'");
		$shps = dbr();
		if($shps[ships_remaining] <= 0) {
			send_message($victim_id,"<b class=b1>$horn[login_name]</b> has laid Hornet Mine cluster(s) in Star System 	#<b>$horn[location]</b>."); //test message
			next;
		}elseif(($horn[clan_id] != 0) && ($turns_safe < $user[turns_run]) && ($user[ship_id] > 1)){
			$rel = find_designation_id();
			if(($rel == $horn[rel_target]) && ($rel != 2)){
				$mine_count = $horn[cluster];
				$hmine_total = $hmine_total + $mine_count;
				$test606 = round(mt_rand(1,1000));
				if((eregi("mw",$user_ship[config])) && ($test606 >= 900)) {
					dbn(__FILE__,__LINE__,"update ${db_name}_users set hornet_mine = hornet_mine + '$mine_count' where login_id = '$user[login_id]'");
					$caph_count = $caph_count + $mine_count;
					post_news("<b class=b1>Hornet Mines</b> have targeted <b class=b1>$user[login_name]</b>\'s Ships. However the target apparently evaded damage with a Mine Sweeper.");
				}else{
					post_news("<b class=b1>Hornet Mines</b> have targeted <b class=b1>$user[login_name]</b>\'s Ships");
					horn_damage($mine_count, $horn);
				}
				dbn(__FILE__,__LINE__,"update ${db_name}_mines set cluster = cluster - '$horn[cluster]' where mine_id = '$horn[mine_id]'");
			}elseif(($horn[rel_target] == 2) && ($rel != 2)){
				$mine_count = $horn[cluster];
				$hmine_total = $hmine_total + $mine_count;
				$test606 = round(mt_rand(1,1000));
				if((eregi("mw",$user_ship[config])) && ($test606 >= 900)) {
					dbn(__FILE__,__LINE__,"update ${db_name}_users set hornet_mine = hornet_mine + '$mine_count' where login_id = '$user[login_id]'");
					$caph_count = $caph_count + $mine_count;
					post_news("<b class=b1>Hornet Mines</b> have targeted <b class=b1>$user[login_name]</b>\'s Ships. However the target apparently evaded damage with a Mine Sweeper.");
				}else{
					post_news("<b class=b1>Hornet Mines</b> have targeted <b class=b1>$user[login_name]</b>\'s Ships");
					horn_damage($mine_count, $horn);
				}
				dbn(__FILE__,__LINE__,"update ${db_name}_mines set cluster = cluster - '$horn[cluster]' where mine_id = '$horn[mine_id]'");
			}else{
				next;
			}
		}elseif(($horn[clan_id] == 0) && ($turns_safe < $user[turns_run]) && ($user[ship_id] > 1)){
			$relx = find_designation_id();
			if(($relx == $horn[rel_target]) && ($relx != 2)){
				$mine_count = $horn[cluster];
				$hmine_total = $hmine_total + $mine_count;
				$test606 = round(mt_rand(1,1000));
				if((eregi("mw",$user_ship[config])) && ($test606 >= 900)) {
					dbn(__FILE__,__LINE__,"update ${db_name}_users set hornet_mine = hornet_mine + '$mine_count' where login_id = '$user[login_id]'");
					$caph_count = $caph_count + $mine_count;
					post_news("<b class=b1>Hornet Mines</b> have targeted <b class=b1>$user[login_name]</b>\'s Ships. However the target apparently evaded damage with a Mine Sweeper.");
				}else{
					post_news("<b class=b1>Hornet Mines</b> have targeted <b class=b1>$user[login_name]</b>\'s Ships");
					horn_damage($mine_count, $horn);
				}
				dbn(__FILE__,__LINE__,"update ${db_name}_mines set cluster = cluster - '$horn[cluster]' where mine_id = '$horn[mine_id]'");
			}elseif(($horn[rel_target] == 2) && ($relx != 2)){
				$mine_count = $horn[cluster];
				$hmine_total = $hmine_total + $mine_count;
				$test606 = round(mt_rand(1,1000));
				if((eregi("mw",$user_ship[config])) && ($test606 >= 900)) {
					dbn(__FILE__,__LINE__,"update ${db_name}_users set hornet_mine = hornet_mine + '$mine_count' where login_id = '$user[login_id]'");
					$caph_count = $caph_count + $mine_count;
					post_news("<b class=b1>Hornet Mines</b> have targeted <b class=b1>$user[login_name]</b>\'s Ships. However the target apparently evaded damage with a Mine Sweeper.");
				}else{
					post_news("<b class=b1>Hornet Mines</b> have targeted <b class=b1>$user[login_name]</b>\'s Ships");
					horn_damage($mine_count, $horn);
				}
				dbn(__FILE__,__LINE__,"update ${db_name}_mines set cluster = cluster - '$horn[cluster]' where mine_id = '$horn[mine_id]'");
			}else{
				next;
			}
		}
		$horn = dbr2();
	}

	if($hmine_total > 0) {
		$mine_damage_horn = ($hmine_total - $caph_count) * 15;
		$tex .= "<b>Hornet Mine Cluster(s) Encountered:</b><br /><p>";
		$tex .= "On entering this Star System (SS #$mine_loc) you were targeted by cluster(s) numbering <b>$hmine_total</b> <b class=b1>Hornet Mines</b>. ";
		$tex .= "The Mines detonated near your ships, dealing a total of <b>$mine_damage_horn</b> damage in the resulting explosions. ";
		if(eregi("mw",$user_ship[config])) {
			$tex.= "Your Mine-Sweeper captured <b>$caph_count</b> of the offending <b class=b1>Hornet Mines</b>!";
		}
		$tex .= "<br /><br />There is a possibility that more <b class=b1>Hornet Mine</b> clusters have been laid near this Star System so caution is advised when continuing. You have been warned...<br /><p>Detailed information on the damage taken by your fleet has been sent to you in a message for each Mine Cluster encountered.";
		print_page("Mine Cluster(s) Encountered",$tex);
	} elseif($gmine_total > 0){
		print_page("Mine Cluster(s) Encountered",$tex);
	}
}


// ----------------------
// End of Mine functions!
// ----------------------


function print_name($user) {
	global $db_name,$user_options,$PRINT_NAME_CACHE;
	if(empty($PRINT_NAME_CACHE[$user['login_id']])) {
		if ($user['login_id']) {
			db3(__FILE__,__LINE__,"select o.Status_Hidden,u.login_id,u.last_request,u.login_name,u.clan_id,u.v_clan_id,u.clan_sym_color,u.clan_sym,u.clan_leader, pu.aim, pu.icq from ${db_name}_users u, user_accounts pu, ${db_name}_user_options o where u.login_id = $user[login_id] && pu.login_id = u.login_id && o.login_id = u.login_id");
			$user = dbr3();
		}
	$temp_str = "<a href=\"player_info.php?target=$user[login_id]\" class=\"nobold\">";

	if($user['clan_id'] != 0){
	$temp_str .= "<font color=$user[clan_sym_color]>$user[login_name]</font>";
	} else {
	$temp_str .= "$user[login_name]";
	}

	if($user['login_id'] == 1){
		$temp_str .= "<font color=white> - Admin</a></font>";
	}
	if ($user['login_id'] == 5){
			$temp_str .= "<font color=yellow> - Dev</a></font>";
	}
	if ($user['clan_leader'] == 1){
			$temp_str .= "</a> - [</font><font color=orange>Leader</font>]";
	}
	if ($user['clan_leader'] != 1 && $user['login_id'] != $owner_id && $user['login_id'] != 1){
			$temp_str .= "</a>";
	}
		#determine if user wants to see relation symbols.
		if($user_options['show_rel_sym'] != 0){
			#determine if user has clan sig
			if ($user['clan_id'] != 0 && $user['clan_sym']) {
				$temp_str .= " (<font color=$user[clan_sym_color]>$user[clan_sym]</font>)";
				$temp_str .= find_designation($user['clan_id']); #prints relation from function
			}

		}else{
			#determine if user has clan sig
			if ($user['clan_id'] != 0 && $user['clan_sym']) {
				$temp_str .= " (<font color=$user[clan_sym_color]>$user[clan_sym]</font>)";
			}
		}

		if($user['Status_Hidden'] == 1)
		{
			$temp_str .= "<font color=ff0000>&lt;Offline&gt;</font>";
		}
		else
		{
			if($user['last_request'] > time()-300)
				{
				$temp_str .= "<font color=00ff00>&lt;Online&gt;</font>";
				}
			elseif($user['last_request'] < time()-172800)
				{
				$temp_str .= "<font color=ff0000>&lt;Inactive&gt;</font>";
				}
			else
				{
				$temp_str .= "<font color=ff0000>&lt;Offline&gt;</font>";
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

	if(eregi("ls",$this_config)){$values['ls']=1;}
	if(eregi("hs",$this_config)){$values['hs']=1;}
	if(eregi("oo",$this_config)){$values['oo']=1;}
	if(eregi("na",$this_config)){$values['na']=1;}
	if(eregi("po",$this_config)){$values['po']=1;}
	if(eregi("so",$this_config)){$values['so']=1;}
	if(eregi("nt",$this_config)){$values['nt']=1;}
	if(eregi("br",$this_config)){$values['br']=1;}
	if(eregi("tw",$this_config)){$values['tw']=1;}
	if(eregi("sj",$this_config)){$values['sj']=1;}
	if(eregi("sw",$this_config)){$values['sw']=1;}
	if(eregi("sv",$this_config)){$values['sv']=1;}
	if(eregi("sc",$this_config)){$values['sc']=1;}
	if(eregi("sh",$this_config)){$values['sh']=1;}
	if(eregi("rd",$this_config)){$values['rd']=1;}
	if(eregi("ws",$this_config)){$values['ws']=1;}
	if(eregi("ps",$this_config)){$values['ps']=1;}
	if(eregi("ot",$this_config)){$values['ot']=1;}
	if(eregi("dt",$this_config)){$values['dt']=1;}
	if(eregi("pc",$this_config)){$values['pc']=1;}
	if(eregi("sa",$this_config)){$values['sa']=1;}
	if(eregi("ew",$this_config)){$values['ew']=1;}
	if(eregi("gs",$this_config)){$values['gs']=1;}
	if(eregi("mi",$this_config)){$values['mi']=1;}
	if(eregi("md",$this_config)){$values['md']=1;}
	if(eregi("ob",$this_config)){$values['ob']=1;}
	if(eregi("sl",$this_config)){$values['sl']=1;}

	return $values;
}

#function to check if a player is dead and out during sudden death.
function sudden_death_check($user){
	global $sudden_death,$db_name,$rs;
	if($sudden_death == 1 && $user['login_id'] != 1) {
		db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '$user[login_id]'");
		$numships = dbr();
		if($numships[0] <= 0) {

			db(__FILE__,__LINE__,"select count(m.message_id) from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' && m.sender_id = u.login_id");
			$counted = dbr();
			if($counted[0] == 0){
				$rs = "<p>No Messages";
			}else{
				$rs = "<p><a href=mpage.php>You have <b>$counted[0]</b> Message(s)</a>";
			}
			$rs .= "<br /><a href=forum.php>Forum</a>";
			print_page("Sudden Death","You have no ship, and this game is Sudden Death. <br />As such you are out of the game. <br />You may still access the Forum, and send/recieve private messages though.");
		}
	}
}

//Choose a system at random
function random_system_num($dist = 0) {
	global $db_name, $user;
	db(__FILE__,__LINE__,"select count(star_id) from ${db_name}_stars where star_id >= 1");
	$total = dbr();

	if($dist != 0){
		$dist = $dist * rand(10, 20);
	        db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = '$user[location]'");
        	$star = dbr();
		db(__FILE__,__LINE__,"select * from ${db_name}_stars where sqrt(pow(x_loc - $star[x_loc], 2) + pow(y_loc - $star[y_loc], 2)) <= $dist");
		while($star = dbr())
			{
			$starer[] = $star;
			}
		$rand = $starer[rand(1, count($starer))][star_id];
		$rand = $starer[rand(1, count($starer))][star_id];
		if(empty($rand))
			{
			return mt_rand(1,$total[0]);
			}
		else
			{
			return $rand;
			}
	}

	return mt_rand(1,$total[0]);

}

function xy_dist($x1, $x2, $y1, $y2){
	return (int)round(sqrt(pow($x1-$x2,2) + pow($y1-$y2,2)));
}

#function to create an escape pod
function create_escape_pod($target){
	global $db_name;
	$rand_star = random_system_num(rand(3,6)); #make a random system number up.
#	$rand_star = 1;
	$ship_types = load_ship_types(); #load ship data
	$ship_stats = $ship_types[2]; #ep is num 2
	$q_string = "insert into ${db_name}_ships (ship_name, login_id, login_name, shipclass, type, class_name, class_name_abbr, fighters, max_fighters, max_shields, cargo_bays, mine_rate_metal, mine_rate_fuel, move_turn_cost, location, config, clan_id";
	$q_string .= ") values('Escape Pod',$target[login_id],'$target[login_name]',2,'Emergency Escape Vehicle','Escape Pod','EP','0','5','10','10','2','2','1','$rand_star','$ship_stats[config]','$target[clan_id]')";
	dbn(__FILE__,__LINE__,$q_string);
	$ship_id = db_insert_id();
	dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '$rand_star', ship_id ='$ship_id' where login_id = '$target[login_id]'");
	$target['location'] = $rand_star;
	$target['ship_id'] = $ship_id;
	return $target;
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

function aliens_attack($aliens,$user,$alien_ship,$target_ship) {
  global $db_name,$weird,$query;


$attack_damage = round($alien_ship[fighters] * .85);
$attack_damage += mt_rand(round(-$alien_ship[fighters] * .15),round($alien_ship[fighters] * .15) + 1);
$counter_damage = round($target_ship[fighters] * .75);
$counter_damage += mt_rand(round(-$target_ship[fighters] * .15),round($target_ship[fighters] * .15) + 1);
echo "<p>The <b class=b1>$aliens[login_name]</b> attacked you in star system #<b>$user[location]</b> with <b>$alien_ship[fighters]</b> fighters.";
echo "<br>Your <b class=b1>$target_ship[ship_name]</b> did <b>$counter_damage</b> damage and took <b>$attack_damage</b> damage.";
$aa = aliens_damage($attack_damage,$aliens,$user,$target_ship);
if($aa == 1){
	echo "<br>Your <b class=b1>$target_ship[ship_name]</b> was destroyed by the <b class=b1>$alien_ship[ship_name]</b>.";
	$weird = 1;
}elseif($aa == 2){
	echo "<br>Your <b class=b1>$target_ship[ship_name]</b> was destroyed by the <b class=b1>$alien_ship[ship_name]</b>. <br>You ejected in an escape pod.";
	$weird = 1;
} else {
	echo "<br>You destroyed the <b class=b1>$alien_ship[ship_name]</b>.";
}
$ac = aliens_damage($counter_damage,$user,$aliens,$alien_ship);
}


function aliens_damage($amount,$from,$target,$target_ship) {
  global $db_name,$query;


if($amount < ($target_ship[fighters] + $target_ship[shields])) { // ship not destroyed
	dbn("update ${db_name}_users set last_attack = ".time().", last_attack_by = '$from[login_name]' where login_id = '$target[login_id]'");
	$shield_damage = $amount;
	if($shield_damage > $target_ship[shields]) {
		$shield_damage = $target_ship[shields];
	}
	$amount -= $shield_damage;
	dbn("update ${db_name}_ships set fighters = fighters - $amount, shields = shields - $shield_damage where ship_id = $target_ship[ship_id]");
	dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$amount' where login_id = '$from[login_id]'");
	dbn("update ${db_name}_users set fighters_lost = fighters_lost + $amount where login_id = $target_ship[login_id]");
	return 0;
} else {	// destroy ship
	post_news("<b class=b1>$from[login_name]</b> destroyed <b class=b1>$target[login_name]</b>\'s $target_ship[class_name].");
	dbn("delete from ${db_name}_ships where ship_id = $target_ship[ship_id]");
    dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$target_ship[fighters]', ships_killed = ships_killed + '1' where login_id = '$from[login_id]'");

	if($target_ship[shipclass] == 2 && $target[login_id] > 3) { // escape pod lost
		dbn("update ${db_name}_users set location = '1', ships_lost = ships_lost + '1', ship_id = '1', last_attack = ".time().", last_attack_by = '$from[login_name]', fighters_lost = fighters_lost + $target_ship[fighters] where login_id = '$target[login_id]'");
	    $user[ship_id] = 1;
		$user[location] = 1;
	} elseif($target[login_id] > 3) { // normal ship lost
			db2("select * from ${db_name}_ships where login_id = $target[login_id]");
			$other_ship = dbr2();
			if($other_ship) { // jump to other ship
				if($target[ship_id] != $target_ship[ship_id]) {
				    $new_ship_id = $target[ship_id];
				} else {
					$new_ship_id = $other_ship[ship_id];
				}
			} else{ // build the escape pod
				$user = create_escape_pod($user);
				db("select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
				$user_ship = dbr();
				return 2;
			}
		// set ships_killed
		db("select * from ${db_name}_ships where ship_id = '$new_ship_id'");
		$user_ship = dbr();
		dbn("update ${db_name}_users set ships_lost = ships_lost + '1', ship_id = '$new_ship_id', location='$user_ship[location]', last_attack =".time().", last_attack_by = '$from[login_name]', fighters_lost = fighters_lost + $target_ship[fighters] where login_id = '$target[login_id]'");
		$user[ship_id] = $new_ship_id;
		return 1;
	}
	return 1;
}

//close alien_attack function
}

#function that returns a hostile planet checking query
function attack_planet_check($db_name,$user){
	return "select * from ${db_name}_planets where (fighter_set = 1 || fighter_set = 2) && fighters > 0 && owner_id != '$user[login_id]' && (clan_id != '$user[clan_id]' && clan_id != 0) && location = '$user[location]' order by fighter_set desc, fighters desc limit 1";
}


#Function to figure out the bonuses offered by weapon upgrades
function bonus_calc($ship){
	global $upgrade_sa;

	$dam = array();

	#defensive turret : lvl 1
	$dam['dt'] = round(330 * (mt_rand(75,125) / 100)) * $ship['num_dt'];

	#offensive turret : lvl 1
	$dam['ot'] = round(200 * (mt_rand(80,120) / 100)) * $ship['num_ot'];

	#silicon armour : lvl 2
	$dam['sa'] = round($upgrade_sa * (mt_rand(90,110) / 100)) * $ship['num_sa'];

	#plasma cannon : lvl 2
	$dam['pc'] = round(420 * (mt_rand(92,108) / 100)) * $ship['num_pc'];

	#electronic warfare module : lvl 1
	$dam['ewd'] = round(325 * (mt_rand(85,115) / 100)) * $ship['num_ew'];
	$dam['ewa'] = round(225 * (mt_rand(80,120) / 100)) * $ship['num_ew'];

	return $dam;
}

#function used to calculate the percentage of something. does not divide things by 0 though.
function calc_perc($num1,$num2){
	if($num1 == 0 || $num2 == 0){
		return "<b class=red_txt style='font-weight: normal'>$num1 (0%)</b>";
	} else {
		$result = number_format(($num1 / $num2) * 100, 2, '.','');
		return "<b class=red_txt style='font-weight: normal'>$num1 (".$result."%)</b>";
	}
}

function get_indx($ship){
	global $db_name;
	db2(__FILE__,__LINE__,"select * from ${db_name}_upgrade_units where ship_id = '$ship[ship_id]'");
	$upgrades = dbr2();
	db(__FILE__,__LINE__,"select sql_name, damage from upgrade_list where type = 6");
	while($upgs_off = dbr()) {
		$off_score += $upgrades[$upgs_off['sql_name']] * $upgs_off['damage'];
	}
	db(__FILE__,__LINE__,"select sql_name, damage from upgrade_list where type = 7");
	while($upgs_def = dbr()) {
		$def_score += $upgrades[$upgs_def['sql_name']] * $upgs_def['damage'];
	}
	$index = array();
	$index['att'] = $off_score;
	$index['def'] = $def_score;
	$att = $index['att'];
	$def = $index['def'];
	$index['indx_u'] = "<b class=\"b1\">Index: $att-$def</b>";
	$index['indx_o'] = "<b class=\"b1\"> - ($att)</b>";
	return $index;
	print_array($index); exit();
}

// function edited from autowarp.php to calculate distance in terms of no. of warps
function get_warp_dist($loc, $dest_sector) {
	global $db_name;
	$start_sector = $loc;
	for($i=1; $i <= 150; $i++) {
		$visit[$i] = 0;
		$pred[$i] = 0;
		$dist[$i] = 150;
	}
	$dist[$dest_sector] = 0;
	$search_queue = array();
	array_unshift($search_queue, $dest_sector);
	while($search_sector = array_pop($search_queue)) {
		db(__FILE__,__LINE__,"SELECT link_1, link_2, link_3, link_4, link_5, link_6 FROM ${db_name}_stars WHERE star_id = '$search_sector'");
		$adj_sectors = dbr();
		foreach($adj_sectors as $vertex) {
			if($vertex == $start_sector) {
				$j = $dist[$search_sector] + 1;
				return $j;
			}
			if($vertex > 0 && $visit[$vertex] == 0) {
				$visit[$vertex] = 1;
				$dist[$vertex] = $dist[$search_sector] + 1;
				$pred[$vertex] = $search_sector;
				array_unshift($search_queue, $vertex);
			}
		}
	}
}

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

//run this if user hits alien homeworld.
function alien_home($user,$star) {
	global $db_name,$weird;
db("select * from ${db_name}_users where login_id = 3");
$ran = dbr();
if ($ran[one_brob] == 0) {
	post_news("<b class=b1>$user[login_name]</b> has stumbled across an alien civilisation of some sort. <br>They are broadcasting a <b class=b1>message</b> which can be read in the <b class=b1>forum</b>.");
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','-1','Welcome <b class=b1>Earthlings</b> to <b class=b1>OUR</b> corner of the Galaxy. Note that we said <b class=b1>OUR</b>, and ours it will remain. <br>Our <b>race</b> has seen <b class=b1>millenia of millenia </b>pass, and still we are here. We have seen <b class=b1>Galaxies Collide</b>, <b class=b1>Stars Explode</b> and <b class=b1>Civilisations</b> such as yours come and go. <p>We do not intend to cause you any harm, however if any of your species does attack us, we will most assuredly retaliate with the full and <b class=b1>devastating arsenal </b>that we have built up over the years.<br>So do not even think of harassing us.<p> ---> <b class=b1>$ran[login_name]</b><br>')");
	dbn("update ${db_name}_users set one_brob = '1' where login_id = '3'");
	dbn("update ${db_name}_users set cash = cash + '25000' where login_id = '$user[login_id]'");
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$user[login_name]','$user[login_id]','$user[login_id]','You have been given <b>25,000</b> credits by the Earth Authorities for finding the Alien Civilisation')");
}
if($user[alien] < 5) {
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','You would dare violate the sanctity of our Home system? You will be punished accordingly.<br>')");
	dbn("update ${db_name}_users set alien = '4' where login_id = '$user[login_id]'");
} elseif($user[alien] > 4) {
	db("select * from ${db_name}_ships where login_id = '3' && location = '$star[star_id]'");
	$alien_ship = dbr();
	db("select * from ${db_name}_planets where owner_id = '3' && location = '$star[star_id]'");
	$planet = dbr();
	if($alien_ship || $planet[fighters] > 0) {
		// print page
		print_header("Aliens Attacking");
		//print_adcode();
		print_status();
		echo "<blockquote>";

		$lastresort = mysql_query("select * from ${db_name}_ships where login_id = '$user[login_id]' && location='$planet[location]' && shipclass > 2 order by fighters asc") or mysql_die("");
		$target_ship = mysql_fetch_array($lastresort);

		echo "You where ambushed by the <b class=b1>$ran[login_name]</b>.";

		while($target_ship) {
			$weird = 0;
			while ($weird == 0) {
				db("select * from ${db_name}_users where login_id = '$user[login_id]'");
				$user = dbr();
				db2("select * from ${db_name}_ships where login_id = '3' && location = '$star[star_id]' order by fighters desc");
				$alien_ship= dbr2();
				db("select * from ${db_name}_ships where ship_id = '$target_ship[ship_id]'");
				$temp_ship = dbr();
				db2("select * from ${db_name}_planets where location = '$star[star_id]'");
				$planet = dbr2();
				if($alien_ship) {
					aliens_attack($ran,$user,$alien_ship,$temp_ship);
				} elseif($planet[fighters] > 0) {
					planet_attacking($ran,$user,$planet,$temp_ship);
				} else {
					break;
				}
			}
			$target_ship = mysql_fetch_array($lastresort);
		}

		//Updating user information
		db("select * from ${db_name}_users where login_id = $user[login_id]");
		$user = dbr();
		db2("select * from ${db_name}_ships where ship_id = $user[ship_id]");
		$user_ship = dbr2();
		dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','We do not allow <b class=b1>things</b> like <b>you </b>into our home system. So do not bother trying to get in.<br>')");
		print_footer();
		exit();
	}
}

alien_clear($user,$star);
}


#stuff relating to an alien colony system
function alien_colony($user,$star) {
	global $db_name,$weird;

db("select * from ${db_name}_users where login_id = 3");
$ran = dbr();
if ($ran[one_brob] == 0) {
	post_news("<b class=b1>$user[login_name]</b> has stumbled across an alien civilisation of some sort. <br>They are broadcasting a <b class=b1>message</b> which can be read in the <b class=b1>forum</b>.");
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','-1','Welcome <b class=b1>Earthlings</b> to <b class=b1>OUR</b> corner of the Galaxy. Note that we said <b class=b1>OUR</b>, and ours it will remain. <br>Our <b>race</b> has seen <b class=b1>millenia of millenia </b>pass, and still we are here. We have seen <b class=b1>Galaxies Collide</b>, <b class=b1>Stars Explode</b> and <b class=b1>Civilisations</b> such as yours come and go. <p>We do not intend to cause you any harm, however if any of your species does attack us, we will most assuredly retaliate with the full and <b class=b1>devastating arsenal </b>that we have built up over the years.<br>So do not even think of harassing us.<p> ---> <b class=b1>$ran[login_name]</b><br>')");
	dbn("update ${db_name}_users set one_brob = '1' where login_id = '3'");
	dbn("update ${db_name}_users set cash = cash + '25000' where login_id = '$user[login_id]'");
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$user[login_name]','$user[login_id]','$user[login_id]','You have been given <b>25,000</b> credits by the Earth Authorities for finding the Alien Civilisation')");
}

if($user[alien] == 0) {
	db("select * from ${db_name}_stars where event_random != '7' && event_random != '8' && (star_id = '$star[link_1]' || star_id = '$star[link_2]' || star_id = '$star[link_3]' || star_id = '$star[link_4]' || star_id = '$star[link_5]' || star_id = '$star[link_6]')");
	$xit_1 = dbr();
	if($xit_1[0]){
		$xit_1 = "<br>To leave go via <b class=b1>system #$xit_1[0]</b>.";
	}
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','This is <b>our</b> system (<b class=b1>$star[star_id]</b>). If you transgress any further into <b>our</b> Space we will have to consider you hostile and take relevant action. $xit_1<br>')");
	dbn("update ${db_name}_users set alien = '2' where login_id = '$user[login_id]'");
} elseif($user[alien] == 1) {
	db("select * from ${db_name}_stars where event_random != '7' && event_random != '8' && (star_id = '$star[link_1]' || star_id = '$star[link_2]' || star_id = '$star[link_3]' || star_id = '$star[link_4]' || star_id = '$star[link_5]' || star_id = '$star[link_6]')");
	if($xit_1[0]){
		$xit_1 = "<br>To leave go via <b class=b1>system #$xit_1[0]</b>.";
	}
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','You have been warned before. If you continue violate our space, we will not be so generous with our patience, and  instead take to the less diplomatic means of killing you. <br>$xit_1 We <b>hope</b> you understand.<br>')");
	dbn("update ${db_name}_users set alien = '2' where login_id = '$user[login_id]'");
} elseif($user[alien] == 2) {
	db("select * from ${db_name}_stars where event_random != '7' && event_random != '8' && (star_id = '$star[link_1]' || star_id = '$star[link_2]' || star_id = '$star[link_3]' || star_id = '$star[link_4]' || star_id = '$star[link_5]' || star_id = '$star[link_6]')");
	if($xit_1[0]){
		$xit_1 = "<br>To leave go via <b class=b1>system #$xit_1[0]</b>.";
	}
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','You must stop toying with us. We are generally <b>not </b>as tolerant as your Race. Leave us <b class=b1>now</b> or suffer the consequences. $xit_1<br>')");
} elseif($user[alien] == 3) {
	db("select * from ${db_name}_stars where event_random != '7' && event_random != '8' && (star_id = '$star[link_1]' || star_id = '$star[link_2]' || star_id = '$star[link_3]' || star_id = '$star[link_4]' || star_id = '$star[link_5]' || star_id = '$star[link_6]')");
	if($xit_1[0]){
		$xit_1 = "<br>To leave go via <b class=b1>system #$xit_1[0]</b>.";
	}
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','You are no longer welcome in our part of the galaxy. <b>Be-gone</b> before we decide to stop being nice. $xit_1</b>.<br>')");
} elseif($user[alien] == 4) {
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','It would seem like you have decided to go into out home system. Punishment will be dealt shortly.<br>')");
//Aliens are unhappy.
} elseif($user[alien] > 4 && $user[alien] < 8) {

	db("select * from ${db_name}_ships where login_id = '3' && location = '$star[star_id]'");
	$alien_ship = dbr();
	db("select * from ${db_name}_planets where owner_id = '3' && location = '$star[star_id]'");
	$planet = dbr();

	if($alien_ship || $planet[fighters] > 0) {
		// print page
		print_header("Aliens Attacking");
		//print_adcode();
		print_status();
		echo "<blockquote>";
	//	echo $error_str;
		db2("select * from ${db_name}_ships where login_id = '$user[login_id]' && ship_id = '$user[ship_id]' && ship_id !='1'");
		$target_ship = dbr2();

		if ($target_ship) {
			$weird = 0;
			echo "You where ambushed by the <b class=b1>$ran[login_name]</b>.";
			echo "<br>Fortunatly they only seem to have only targetted your command ship.";
		} else {
			$weird = 1;
		}

		while ($weird == 0) {
			db("select * from ${db_name}_users where login_id = '$user[login_id]'");
			$user = dbr();

			$alienships = mysql_query("select * from ${db_name}_ships where login_id = '3' && location = '$user[location]' order by fighters desc") or mysql_die($query);
			$alien_ship= mysql_fetch_array($alienships);

			db2("select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
			$target_ship = dbr2();

			$planetstuff = mysql_query("select * from ${db_name}_planets where planet_id = '$planet_id'") or mysql_die("");
			$planet = mysql_fetch_array($planetstuff);

			if($alien_ship) {
				aliens_attack($ran,$user,$alien_ship,$target_ship);
			} elseif($planet[fighters] > 0) {
					planet_attacking($ran,$user,$planet,$target_ship);
			} else {
				break;
			}
		}

		dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','We did not forget!<br>')");
		print_footer();
		exit();
	}

//Aliens are on warpath!
} else {
	db("select * from ${db_name}_ships where login_id = '3' && location = '$star[star_id]'");
	$alien_ship = dbr();
	db("select * from ${db_name}_planets where owner_id = '3' && location = '$star[star_id]'");
	$planet = dbr();
	if($alien_ship || $planet[fighters] > 0) {
		// print page
		print_header("Aliens Attacking");
		//print_adcode();
		print_status();
		echo "<blockquote>";

		$lastresort = mysql_query("select * from ${db_name}_ships where login_id = '$user[login_id]' && location='$planet[location]' && shipclass >'2' order by fighters asc") or mysql_die("");
		$target_ship = mysql_fetch_array($lastresort);

		echo "You where ambushed by the <b class=b1>$ran[login_name]</b>.";

		while($target_ship) {
			$weird = 0;
			while ($weird == 0) {
				db("select * from ${db_name}_users where login_id = '$user[login_id]'");
				$user = dbr();
				db2("select * from ${db_name}_ships where login_id = '3' && location = '$star[star_id]' order by fighters desc");
				$alien_ship= dbr2();
				db("select * from ${db_name}_ships where ship_id = '$target_ship[ship_id]'");
				$temp_ship = dbr();
				db2("select * from ${db_name}_planets where location = '$star[star_id]'");
				$planet = dbr2();
				if($alien_ship) {
					aliens_attack($ran,$user,$alien_ship,$temp_ship);
				} elseif($planet[fighters] > 0) {
					planet_attacking($ran,$user,$planet,$temp_ship);
				} else {
					break;
				}
			}
			$target_ship = mysql_fetch_array($lastresort);
		}

		dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','You have caused us much trouble. Allow us to repay the debt.<br>')");
		print_footer();
		exit();
	}
}
alien_clear($user,$star);
}

#stuff relating to an alien frontier system
function alien_front($user,$star) {
	global $db_name,$weird;
db("select * from ${db_name}_users where login_id = 3");
$ran = dbr();
if ($ran[one_brob] == 0) {
	post_news("<b class=b1>$user[login_name]</b> has stumbled across an alien civilisation of some sort. <br>They are broadcasting a <b class=b1>message</b> which can be read in the <b class=b1>forum</b>.");
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','-1','Welcome <b class=b1>Earthlings</b> to <b class=b1>OUR</b> corner of the Galaxy. Note that we said <b class=b1>OUR</b>, and ours it will remain. <br>Our <b>race</b> has seen <b class=b1>millenia of millenia </b>pass, and still we are here. We have seen <b class=b1>Galaxies Collide</b>, <b class=b1>Stars Explode</b> and <b class=b1>Civilisations</b> such as yours come and go. <p>We do not intend to cause you any harm, however if any of your species does attack us, we will most assuredly retaliate with the full and <b class=b1>devastating arsenal </b>that we have built up over the years.<br>So do not even think of harassing us.<p> ---> <b class=b1>$ran[login_name]</b><br>')");
	dbn("update ${db_name}_users set one_brob = '1' where login_id = '3'");
	dbn("update ${db_name}_users set cash = cash + '25000' where login_id = '$user[login_id]'");
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$user[login_name]','$user[login_id]','$user[login_id]','You have been given <b>25,000</b> credits by the Earth Authorities for finding the Alien Civilisation')");
}
if($user[alien] == 0) {
	db("select * from ${db_name}_stars where event_random != '7' && event_random != '8' && event_random != '9' && (star_id = '$star[link_1]' || star_id = '$star[link_2]' || star_id = '$star[link_3]' || star_id = '$star[link_4]' || star_id = '$star[link_5]' || star_id = '$star[link_6]')");
	if($xit_1[0]){
		$xit_1 = "<br>To leave go via <b class=b1>system #$xit_1[0]</b>.";
	}
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','This is <b>our</b> system (<b class=b1>$star[star_id]</b>. If you transgress any further into <b>our</b> Space we will have to consider you hostile and take relevant action. $xit_1<br>')");
	dbn("update ${db_name}_users set alien = '1' where login_id = '$user[login_id]'");
} elseif($user[alien] == 1) {
	db("select * from ${db_name}_stars where event_random != '7' && event_random != '8' && event_random != '9' && (star_id = '$star[link_1]' || star_id = '$star[link_2]' || star_id = '$star[link_3]' || star_id = '$star[link_4]' || star_id = '$star[link_5]' || star_id = '$star[link_6]')");
	if($xit_1[0]){
		$xit_1 = "<br>To leave go via <b class=b1>system #$xit_1[0]</b>.";
	}
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','You must stop toying with us. We are generally <b>not </b>as tolerant as your Race. Leave us <b class=b1>now</b> or suffer the consequences. $xit_1<br>')");
} elseif($user[alien] == 2) {
	db("select * from ${db_name}_stars where event_random != '7' && event_random != '8' && event_random != '9' && (star_id = '$star[link_1]' || star_id = '$star[link_2]' || star_id = '$star[link_3]' || star_id = '$star[link_4]' || star_id = '$star[link_5]' || star_id = '$star[link_6]')");
	if($xit_1[0]){
		$xit_1 = "<br>To leave go via <b class=b1>system #$xit_1[0]</b>.";
	}
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','It is good to see that you have come to your senses. $xit_1<br>')");
	dbn("update ${db_name}_users set alien = '1' where login_id = '$user[login_id]'");
} elseif($user[alien] == 3) {
	db("select * from ${db_name}_stars where event_random != '7' && event_random != '8' && event_random != '9' && (star_id = '$star[link_1]' || star_id = '$star[link_2]' || star_id = '$star[link_3]' || star_id = '$star[link_4]' || star_id = '$star[link_5]' || star_id = '$star[link_6]')");
	if($xit_1[0]){
		$xit_1 = "<br>To leave go via <b class=b1>system #$xit_1[0]</b>.";
	}
	dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','Be sure not to return to our space. $xit_1.<br>')");

} elseif($user[alien] > 4 && $user[alien] < 8) {

	db("select * from ${db_name}_ships where login_id = '3' && location = '$star[star_id]'");
	$alien_ship = dbr();
	db("select * from ${db_name}_planets where owner_id = '3' && location = '$star[star_id]'");
	$planet = dbr();

	if($alien_ship || $planet[fighters] > 0) {
		// print page
		print_header("Aliens Attacking");
		//print_adcode();
		print_status();
		echo "<blockquote>";
	//	echo $error_str;
		db2("select * from ${db_name}_ships where login_id = '$user[login_id]' && ship_id = '$user[ship_id]' && ship_id !='1'");
		$target_ship = dbr2();

		if ($target_ship) {
			$weird = 0;
		echo "You where ambushed by the <b class=b1>$ran[login_name]</b>.";
		echo "<br>Fortunatly they only took out your command ship.";
		} else {
			$weird = 1;
		}

		while ($weird == 0) {
			db("select * from ${db_name}_users where login_id = '$user[login_id]'");
			$user = dbr();
			$alienships = mysql_query("select * from ${db_name}_ships where login_id = '3' && location = '$user[location]' order by fighters desc") or mysql_die("");
			$alien_ship= mysql_fetch_array($alienships);
			db2("select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
			$target_ship = dbr2();
			$planetstuff = mysql_query("select * from ${db_name}_planets where planet_id = '$planet_id'") or mysql_die("");
			$planet = mysql_fetch_array($planetstuff);

			if($alien_ship) {
				aliens_attack($ran,$user,$alien_ship,$target_ship);
			} elseif($planet[fighters] > 0) {
					planet_attacking($ran,$user,$planet,$target_ship);
			} else {
				break;
			}
		}

		//Updating user information
		db("select * from ${db_name}_users where login_id = $user[login_id]");
		$user = dbr();
		db("select * from ${db_name}_ships where ship_id = $user[ship_id]");
		$user_ship = dbr();
		print_footer();
		exit();
	}

//Aliens are on warpath!
} else {
	db("select * from ${db_name}_ships where login_id = '3' && location = '$star[star_id]'");
	$alien_ship = dbr();
	db("select * from ${db_name}_planets where owner_id = '3' && location = '$star[star_id]'");
	$planet = dbr();
	if($alien_ship || $planet[fighters] > 0) {
		// print page
		print_header("Aliens Attacking");
		//print_adcode();
		print_status();
		echo "<blockquote>";

		$lastresort = mysql_query("select * from ${db_name}_ships where login_id = '$user[login_id]' && location='$planet[location]' && shipclass > '2' order by fighters asc") or mysql_die("");
		$target_ship = mysql_fetch_array($lastresort);

		echo "You where ambushed by the <b class=b1>$ran[login_name]</b>.";

		while($target_ship) {
			$weird = 0;
			while ($weird == 0) {
				db("select * from ${db_name}_users where login_id = '$user[login_id]'");
				$user = dbr();
				db2("select * from ${db_name}_ships where login_id = '3' && location = '$star[star_id]' order by fighters desc");
				$alien_ship= dbr2();
				db("select * from ${db_name}_ships where ship_id = '$target_ship[ship_id]'");
				$temp_ship = dbr();
				db2("select * from ${db_name}_planets where location = '$star[star_id]'");
				$planet = dbr2();
				if($alien_ship) {
					aliens_attack($ran,$user,$alien_ship,$temp_ship);
				} elseif($planet[fighters] > 0) {
					planet_attacking($ran,$user,$planet,$temp_ship);
				} else {
					break;
				}
			}
			$target_ship = mysql_fetch_array($lastresort);
		}

		//Updating user information
		db("select * from ${db_name}_users where login_id = $user[login_id]");
		$user = dbr();
		db2("select * from ${db_name}_ships where ship_id = $user[ship_id]");
		$user_ship = dbr2();
		dbn("insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$ran[login_name]','$ran[login_id]','$user[login_id]','You have caused us much trouble. Allow us to repay the debt.<br>')");
		print_footer();
		exit();
	}
}
alien_clear($user,$star);
}

//Check if system is still under alien control:
function alien_clear($user,$star) {
global $db_name;
db("select * from ${db_name}_ships where login_id = '3' && location = '$star[star_id]' order by fighters desc");
$alien_ship = dbr();
db("select * from ${db_name}_planets where owner_id = '3' && location = '$star[star_id]' order by fighters desc");
$planet = dbr();
if (!$alien_ship && !$planet) {
print_header("Alien System Cleared");
//print_adcode();
print_status();
echo "<blockquote>";
dbn("update ${db_name}_stars set event_random = '0', star_name='Ex-Alien Colony' where star_id = $star[star_id]");
echo "This system is now clear of Aliens, and the Earth authorities have given you <b class=b1>5 million credits</b> in repayment for the deed.";
echo "<br>Congratulations on work well done.";
dbn("update ${db_name}_users set cash = cash + '5000000' where login_id = '$user[login_id]'");
$user[cash] += 5000000;
post_news("<b class=b1>$user[login_name]</b> has been given 5 million credits by the Earth authorities for finishing off the Aliens in <b>$star[star_id]</b>.");

//check to see if any alien planets left. If not, collect bounty.
db("select * from ${db_name}_planets where owner_id = '3'");
$alien_plans = dbr();
if(!$alien_plans) {
	db("select * from ${db_name}_users where login_id = '3'");
	$alien = dbr();
	echo "<p>Congratulations. You have just claimed the last alien planet.";
	echo "<br>Though there may still be some Alien ships left, the Aliens are now as good as dead. As such we at <b class=b1>Bob's charity shop</b> are going to give you the bounty that was on them.";
	echo "<p>You have just collected the <b class=b3>$alien[bounty]</b> Credit bounty that was on the <b class=b1>$alien[login_name]</b>.";
	post_news("<b class=b1>$user[login_name]</b> has just claimed the <b class=b1>$alien[bounty] credit bounty</b> that was on the <b class=b1>$alien[login_name]s</b>.");
	dbn("update ${db_name}_users set cash = cash + '$alien[bounty]' where login_id = '$user[login_id]'");
	dbn("update ${db_name}_users set bounty = '0' where login_id = '3'");
					}
	print_footer();
	exit();
}


}

function email_users($subject,$message) {
	global $db_name;
		db(__FILE__,__LINE__,"select * from user_accounts where newsletter = 1");
	while($users=dbr()) {
mail($users[email_address],"QS: Generations - $subject","This email was sent because you allowed it, if you want to set it otherwise please change your details in options at $url_prefix.

Here is the message:
$message","From: QS: Generations <$admin_mail>");
	}
}

// NOTE

/*
This function will select fill as many ships in a fleet as possible with whatever is requested.

- 1st arguement sent to it is the sql name for whatever is to be loaded.
- 2nd arguement is the name of the sql entry for the most of that material that any one ship can hold.
- 3rd arguement contains the textual string
- 4th arguement holds the cost per unit of the item.
- 5th arguement is the name of the orginating script

*/
function fill_fleet($item_sql, $item_max_sql, $item_str, $item_cost, $script_name, $cargo_run = 1){
	global $user, $user_ship, $db_name, $sure, $fill_dir;

	$ret_str = "";
	$taken = 0; //item taken from earth far.
	$ship_counter = 0; //ships passed through

	if($cargo_run == 1){ //cargo
		$sql_max_check = $item_max_sql;
		$sql_where_clause = " location = '$user[location]' && login_id='$user[login_id]' && $item_max_sql > 0 ";
		$cargo_run = 1;

	} else {//not cargo
		$sql_max_check = "sum($item_max_sql - $item_sql)";
		$sql_where_clause = " location = '$user[location]' && login_id='$user[login_id]' && $item_max_sql > 0 && $item_sql < $item_max_sql ";
	}

	//return assoc array
	$ADODB_FETCH_MODE = 2;

	//elect all viable ships
	db(__FILE__,__LINE__,"select sum($item_max_sql) as total_capacity, count(ship_id) as total_ships from ${db_name}_ships where ".$sql_where_clause);
	$maths = dbr();

	//insufficient cash
	if($user['cash'] < $item_cost){
		$ret_str .= "You do not have enough money for even 1 unit of <b class=b1>$item_str</b>. You certainly can't afford to fill a fleet.";
	} elseif(empty($maths) || $maths['total_ships'] < 1) { //ensure there are some ships.
		$ret_str .= "This operation failed as there are no ships that have any free capacity to hold <b class=b1>$item_str</b> in this system that belong to you.";
	} else {
		//work out the total value of them all.
		$total_cost = $maths['total_capacity'] * $item_cost;

		//user can afford to fill the whole fleet
		if($total_cost <= $user['cash']) {

			if(empty($sure)){ //confirmation
				get_var('Load ships',$script_name,"There is capacity for <b>$maths[total_capacity]</b> <b class=b1>$item_str</b> in <b>$maths[total_ships]</b> ships in this system. <p>You have enough money to fill all the ships with <b class=b1>$item_str</b>. Do you wish to do that?",'sure','yes');
			} else { //process.
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set $item_sql = $item_max_sql where ".$sql_where_clause);
				take_cash($total_cost);

				if($cargo_run == 0){ //not cargo bay stuff
					$user_ship[$item_sql] = $user_ship[$item_max_sql];
				} else { //cargo bay stuff
					$user_ship[$item_sql] += $user_ship['empty_bays'];
				}

				$ret_str .= "<b>$maths[total_capacity]</b> <b class=b1>$item_str</b> were added to <b>$maths[total_ships]</b> ships.<br />All ships are now at maximum capacity.";
			}

		//user cannot afford to fill the whole fleet, so we'll have to do it the hard way.
		} else {

			if(empty($sure)) { //confirmation
				$extra_text = "<p><input type=radio name=fill_dir value=1 CHECKED> - Fill highest capacity ships ships first.";
				$extra_text .= "<br /><input type=radio name=fill_dir value=2> - Fill lowest capacity ships first.";
				get_var('Load ships',$script_name,"There is capacity for <b>$maths[total_capacity]</b> <b class=b1>$item_str</b> in <b>$maths[total_ships]</b> ships in this system. <p>Do you want to fill as many ships as you can afford to fill?".$extra_text,'sure','yes');
			} else { //process
				if($fill_dir == 1){
					$order_dir = "desc";
				} else {
					$order_dir = "asc";
				}

				$total_can_afford = floor($user['cash'] / $item_cost); //work out amount can afford.

				if($total_can_afford < 1){ //error checking
					return "Unable to fill any ships with anything.";
				}

				$used_copy_afford = $total_can_afford; //make copy of the above.
				$final_cost = $item_cost * $total_can_afford; //work out the final cash cost of it all.
				$fill_ships_sql = ""; //intiate sql string to load a bunch of ships at once
				$temp_str = "";

				db2(__FILE__,__LINE__,"select ship_id, $item_sql, $item_max_sql as max, ship_name from ${db_name}_ships where ".$sql_where_clause." order by $item_max_sql $order_dir");

				while($ships = dbr2(1)) { //loop through the ships

					$ship_counter++; //increment counter
					$free_space = $ships['max'] - $ships[$item_sql]; //capacity of present ship

					if($free_space < $used_copy_afford) { //can load ship
						$used_copy_afford -= $free_space; //num to use
						$fill_ships_sql .= "ship_id = '$ships[ship_id]' || ";

						$temp_str .= "<br /><b class=b1>$ships[ship_name]</b> had its $item_str cargo increased by <b>$free_space</b> to maximum capacity.";

						if($ships['ship_id'] == $user_ship['ship_id']){ //do the user ship too.
							if($cargo_run == 0){ //not cargo bay stuff
								$user_ship[$item_sql] = $user_ship[$item_max_sql];
							} else { //cargo bay stuff
								$user_ship[$item_sql] += $user_ship['empty_bays'];
							}
						}

					} else { //cannot load ship whole ship.
						dbn(__FILE__,__LINE__,"update ${db_name}_ships set $item_sql = '$used_copy_afford' where ship_id = '$ships[ship_id]'");

						if($ships['ship_id'] == $user_ship['ship_id'] && $cargo_run == 0){ //do the user ship too.
							$user_ship[$item_sql] += $used_copy_afford;
						} elseif($ships['ship_id'] == $user_ship['ship_id']) { //cargo bay stuff
							$user_ship[$item_sql] += $used_copy_afford;
						}
						$temp_str .= "<br /><b class=b1>$ships[ship_name]</b>s <b class=b1>$item_str</b> count was increased to <b>$used_copy_afford</b>.";
						break 1;
					}
				} //end of while

				$ret_str .= "<b>$ship_counter</b> ships had their <b class=b1>$item_str</b> count augmented by more $item_str.<br />Total increase in $item_str = <b>$total_can_afford</b>; Cost = <b>$final_cost</b><p>More Detailed Statistics :".$temp_str;

				//update DB with fully loaded ships.
				if(!empty($fill_ships_sql)){
					$fill_ships_sql = preg_replace("/\|\| $/", "", $fill_ships_sql);
					dbn(__FILE__,__LINE__,"update ${db_name}_ships set $item_sql = $item_max_sql where ".$fill_ships_sql);
				}
				take_cash($final_cost); //charge the cash
			}
		}
	}
	return $ret_str; //return the result string.
}

?>