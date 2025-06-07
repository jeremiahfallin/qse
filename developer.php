<?php
// page for the person running the server to look at the statistics for a game.
// get login restriction code from index.php on own servers at uni.

require_once("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

// only the server admin may use this page!
//if($user['login_id'] != 5 || !isset($user['login_id']) || OWNER_ID == 0){
if($user['login_id'] != 5){
	print_page("Error","Where you going?  Your not the server developer, so scram!");
}


$out_str = "";


#list all the dev options
$out_str .= "<a href=developer.php?send_message=1>Message People</a><br />";
$out_str .= "<a href=developer.php?server_details=1>Give Server Details</a><br />";
$out_str .= "<a href=developer.php?email=1>Send a Newsletter</a><br />";
$out_str .= "<a href=developer.php?multi=1>Multi Check</a><br />";
$out_str .= "<a href=developer.php?ban=1>Ban Player</a><br />";
$out_str .= "<a href=developer.php?port=1>List All Ports</a><br />";
$out_str .= "<a href=developer.php?uplanets=1>List all Uni Planets</a><br />";
$out_str .= "<a href=developer.php?pplanets=1>List all Player Planets</a><br />";
$out_str .= "<a href=developer.php?bm=1>List all BlackMarkets</a><br />";
$out_str .= "<a href=developer.php?pause=1>Pause/Unpause Game</a><br />";
$out_str .= "<a href=developer.php?vars=1>Game Vars</a><br />";

// developer sends a message
if(isset($send_message)){
	if(empty($text) || !isset($target)){
		$mess_str = "\n<select name=target>";
		$mess_str .= "\n<option value=-1> All Admins";
		$mess_str .= "\n<option value=-2> All Players in all games";
		$mess_str .= "\n<option value=-3> All Game forums";

		// loop through the games.
		db(__FILE__,__LINE__,"select game_id, name from se_games");
		while($dest = dbr(1)){
			$mess_str .= "\n<option value=$dest[game_id]> Players in '$dest[name]'";
		}
		$mess_str .= "\n</select><br />";

		get_var('Send Message','developer.php',"Select the group of people you would like to send the message to:<br /><br /> $mess_str<br /><br />Enter your message below (note: HTML is useable. Message codes are not):",'text',"");

	// one of the pre-defined destinations.
	} else{
		if($target == -1){
			$send_to = "All the Admins";
		} elseif($target == -2){
			$send_to = "all the players in all the games";
		} elseif($target == -3){
			$send_to = "all the game forums";
		} else {
			$send_to = "all players";
		}

		db(__FILE__,__LINE__,"select game_id, db_name from se_games");
		while($dest = dbr(1)){

			// message only to recipients of this one game, or all players in all games
			if(($target > 0 && $dest['game_id'] == $target) || $target == -2){
				$out_str .= "<p>".message_all_players($text,$dest['db_name'], $send_to,"<font color=lime>The Server Operator</font>");

			} elseif($target == -1 || $target == -3){//all admins or all forums
				if($target == -1){
					$dest_id = 1;
					$extra_txt = "Message to <b class=b1>All Admins</b> from <font color=lime>The Server Operator</font>:<p> ".$text;
				} else {
					$dest_id = -1;
					$extra_txt = "Message to <b class=b1>All Game Forums</b> from <font color=lime>The Server Operator</font>:<p> ".$text;
				}
				dbn(__FILE__,__LINE__,"insert into {$dest['db_name']}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'$user[login_name]','$user[login_id]','$dest_id','$extra_txt')");
			}
		}
	}


// show stats for the server
} elseif(isset($server_details)) {
	$out_str .= "<br />Generic Server information";
	db(__FILE__,__LINE__,"select count(login_id),sum(login_count),sum(num_games_joined) from user_accounts where login_id > 5");
	$serv1 = dbr();

	db(__FILE__,__LINE__,"select count(game_id) from se_games where status = 1");
	$serv2 = dbr();

	$out_str .= make_table(array("",""));
	$out_str .= quick_row("Total Games:","$serv2[0]");
	$out_str .= quick_row("Total Account:","$serv1[0]");
	$out_str .= quick_row("Total Logins:","$serv1[1]");
	$out_str .= quick_row("Avg. Logins/Player:",number_format($serv1[1]/$serv1[0],2));
	$out_str .= quick_row("Avg. Games Joined/Player:",number_format($serv1[2]/$serv1[0],2));
	$out_str .= "</table><br /><br /><br />";


	$out_str .= "Game Details:";
	// loop once per game
	db2(__FILE__,__LINE__,"select * from se_games order by name");
	while ($game = dbr2()){
		$db_name = $game['db_name'];
		db(__FILE__,__LINE__,"select count(login_id),sum(cash),sum(turns),sum(turns_run),sum(ships_killed), sum(fighters_lost) as lost_fighters, sum(fighters_killed) as killed_fighters from ${db_name}_users where login_id > 5");
		$ct = dbr();
		db(__FILE__,__LINE__,"select count(login_id) from ${db_name}_users where ship_id != 1 && login_id > 5");
		$ct2 = dbr();
		db(__FILE__,__LINE__,"select count(login_id),sum(fighters) from ${db_name}_ships where login_id > 5");
		$ct3 = dbr();
		db(__FILE__,__LINE__,"select count(planet_id),sum(fighters),sum(colon),sum(elect),sum(organ),sum(metal),sum(fuel) from ${db_name}_planets where owner_id != 1 && planet_type >=0");
		$ct4 = dbr();
		db(__FILE__,__LINE__,"select count(clan_id),sum(members) from ${db_name}_clans where leader_id !=1> 0");
		$ct5 = dbr();
		db(__FILE__,__LINE__,"select count(news_id) from ${db_name}_news");
		$ct6 = dbr();
		db(__FILE__,__LINE__,"select count(message_id) from ${db_name}_messages where login_id = -1");
		$forum_POSTs = dbr();
		db(__FILE__,__LINE__,"select count(message_id) from ${db_name}_messages where login_id > 1");
		$player_mess = dbr();
		db(__FILE__,__LINE__,"select count(message_id) from ${db_name}_messages where login_id = -5");
		$clan_forum_POSTs = dbr();
		$out_str .= "<table border=0 cellpadding=5><tr valign=top><td colspan=3>";
		$out_str .= make_table(array("",""));
		$out_str .= quick_row("Game Name:","$game[name]");
		$out_str .= quick_row("Game ID:","$game[game_id]");
		$out_str .= quick_row("db_name: ","$game[db_name]");
		$out_str .= quick_row("Paused: ","$game[paused]");
		$out_str .= quick_row("Status: ","$game[status]");
		$out_str .= quick_row("","");
		$out_str .= quick_row("","");
		$out_str .= quick_row("Admin Name:","$game[admin_name]");
		$out_str .= quick_row("Description:","$game[description]");
		$out_str .= quick_row("Intro Message:","$game[intro_message]");
		$out_str .= quick_row("Num Stars:","$game[num_stars]");
		$out_str .= quick_row("","");
		$out_str .= "</table></td></tr><tr><td>";

		$out_str .= make_table(array("",""));
		$out_str .= quick_row("News Posts","$ct6[0]");
		$out_str .= quick_row("Forum Posts","$forum_POSTs[0]");
		$out_str .= quick_row("Player Messages","$player_mess[0]");
		$out_str .= quick_row("Clan Forum Posts","$clan_forum_POSTs[0]");
		$out_str .= "</table><br />";

		$out_str .= make_table(array("",""));
		$out_str .= quick_row("Players","<b>".($ct[0])."</b>");
		$out_str .= quick_row("Players Alive",calc_perc($ct2[0],$ct[0]));
		$out_str .= quick_row("Cash",number_format($ct[1]));
		$out_str .= quick_row("Cash Average",number_format(round(($ct[1] * 100/$ct[0]) / 100)));
		$out_str .= quick_row("Turns",$ct[2]);
		$out_str .= quick_row("Turns Average",number_format($ct[2]/$ct[0]),2);
		$out_str .= quick_row("Turns Run",$ct[3]);
		$out_str .= quick_row("Turns Run Average",number_format($ct[3]/$ct[0]),2);
		$out_str .= "</table></td><td>";
		// new grid

		$out_str .= make_table(array("",""));
		$out_str .= quick_row("Ships","<b>$ct3[0]</b>");
		$out_str .= quick_row("Ships Average",round($ct3[0]/$ct[0]));
		$out_str .= quick_row("Fighters",$ct3[1]);
		$out_str .= quick_row("Avg. Fighters/Ship",round(($ct3[1] * 100/$ct3[0]) / 100));
		$out_str .= "</table><br />";

		$out_str .= make_table(array("",""));
		$out_str .= quick_row("Planets","<b>$ct4[0]</b>");
		$out_str .= quick_row("Planets Average",number_format($ct4[0]/$ct[0],3));
		$out_str .= quick_row("Planet Colonists","<b>$ct4[2]</b>");
		$out_str .= quick_row("Planet Metal","<b>$ct4[5]</b>");
		$out_str .= quick_row("Planet Fuel","<b>$ct4[6]</b>");
		$out_str .= quick_row("Planet Electronics","<b>$ct4[3]</b>");
		$out_str .= quick_row("Planet Organics","<b>$ct4[4]</b>");
		$out_str .= quick_row("Planet Fighters",$ct4[1]);
		if($ct4[1] > 0){
			$out_str .= quick_row("Fighters Average",number_format(($ct4[1] * 100/$ct4[0]) / 100,2));
		} else {
			$out_str .= quick_row("Fighters Average","0%)");
		}
		$out_str .= "</table></td><td>";
		// new grid

		$out_str .= make_table(array("",""));
		$out_str .= quick_row("Kills",$ct[4]);
		$out_str .= quick_row("Kills Average",round(($ct[4] * 100/$ct[0]) / 100));
		$out_str .= quick_row("Fighters Killed",$ct['killed_fighters']);
		$out_str .= quick_row("Fighters Killed Average",round(($ct['killed_fighters'] * 100/$ct[0]) / 100));
		$out_str .= quick_row("Fighters Lost",$ct['lost_fighters']);
		$out_str .= quick_row("Fighters Lost Average",round(($ct['lost_fighters'] * 100/$ct[0]) / 100));

		$out_str .= "</table><br />";
		$out_str .= make_table(array("",""));
		$out_str .= quick_row("Clans","<b>$ct5[0]</b>");
		$out_str .= quick_row("Total Clan <br />Membership",$ct5[1]);
		if($ct5[1] > 0){
			$out_str .= quick_row("Average Clan <br />Membership",round(($ct5[1] * 100/$ct5[0]) / 100));
		}
		$out_str .= "</table><br /><br />";
		$out_str .= "</table><br /><br />";
	}
//Send a newsletter
}elseif(isset($email)) {

if($done) {

$bleh = $subject;
$bleh2 = $content;
email_users($bleh,$bleh2);
$out_str .= 'Newsletter Sent';

} else {

$out_str .= '<p></p><br>
Newsletter:
<form name=form1 method=post action=developer.php>
<table border=1>
<tr><td>Email Subject:</td><td><input type=text name=subject></td></tr>
<tr><td>Email Content:</td><td><textarea name=content cols=60 rows=30>Type content for email here</textarea></td></tr>
<tr><td><input type=submit value=Send!></td></tr>
</table>
<input type=hidden name=email value=1>
<input type=hidden name=done value=1>
</form>';

}
}


//Multi Check
elseif(isset($multi)) {
//Moriarty's Multi Checker
$pass_check = array(); //store for all passwords
$dup_pass = array(); //Store for duplicate passwords
$ip_check = array(); //store for all passwords
$ip_pass = array(); //Store for duplicate passwords
db2(__FILE__,__LINE__,"select a.login_name,a.login_id,a.passwd,a.last_ip,a.email_address,s.login_id from user_accounts a, ${db_name}_users s where a.login_id > 5 && s.login_id = a.login_id order by last_ip");
	while($m_check = dbr2()){
		$x_pa = $pass_check;
		//This loop finds same pass's
		while ($var = each($x_pa)) {
			if ($var['value'] == $m_check['passwd']) {	//A duplicate password
				$dup_pass[$m_check['login_id']] = $m_check['passwd'];
				$dup_pass[$var[key]] = $var['value'];
			}
		}
		$pass_check[$m_check['login_id']] = $m_check['passwd'];

		$x_ip = $ip_check;
		//this loop finds same IP's
		if($m_check['last_ip']){
			while ($var = each($ip_check)) {
				if ($var['value'] == $m_check[last_ip]) {	//A duplicate IP
					$dup_ip[$m_check['login_id']] = $m_check[last_ip];
					$dup_ip[$var[key]] = $var['value'];
				}
			}
		} elseif(!$m_check['last_ip']){
			$dup_ip[$m_check['login_id']] = $m_check['last_ip'];
		}
		$ip_check[$m_check['login_id']] = $m_check['last_ip'];
	}

$out = '<p>The methods for finding <b>Multi\'s</b> cannot be disclosed, as releasing such information would nullify the advantage.<br />However rest assured, that when it says <b class=b1>Definite Multi</b> that its something like 70-90% correct.<br /><br />Note also that this program is really only basic at present, though it does do its job. More checks will be implemented in the future.</p>';
$out .= '<p>This Page does not show which Mutli\'s are related at present.</p>';

//Definate Multi check.

if($dup_pass && $dup_ip){
	$out .= '<p><b>Definite Multi\'s (<i>70-90% Certainty</i>)</b>';
	$t_pa = $dup_pass;
	while ($d_p = each($t_pa)) {
		$t_ip = $dup_ip;
		while ($d_i = each($t_ip)) {
			if($d_p[key] == $d_i[key] && $d_i['value']){
				db(__FILE__,__LINE__,"select login_name from ${db_name}_users where login_id = '$d_i[key]'");
				$ret = dbr();
				$out1 .= '<br /><a href="../player_info.php?target='.$d_i[key].'"><b class="b1">'.$ret['login_name'].'</b></a>';
			} elseif($d_p[key] == $d_i[key] && !$d_i['value']) {
				db(__FILE__,__LINE__,"select login_name from ${db_name}_users where login_id = '$d_i[key]'");
				$ret = dbr();
				db(__FILE__,__LINE__,"select login_name,login_id from user_accounts where login_id != '$d_p[key]' && passwd='$d_p[value]'");
				$sec_ret = dbr();
				$out3 .= '<br /><a href="player_info.php?target='.$d_i[key].'"><b class="b1">'.$ret['login_name'].'</b></a> (not Logged in yet).';
				$out3 .= '<br /><a href="player_info.php?target='.$sec_ret[login_id].'"><b class="b1">'.$sec_ret['login_name'].'</b></a>';
			}
		}
	}
}
if(!$out1){
	$out1 = '<br /><b class="b1"><i>No Definite Multiple Accounts</i></b>';
}
$out = $out.$out1;

$out .= '<p><b>Likely Multi\'s</b> <i>(peeps who could be a multi, however one of the two accounts hasn\'t logged in yet)</i></p>';

if(!$out3){
	$out3 = '<br /><b class=b1><i>No Likely Multiple Accounts</i></b>';
}
$out = $out.$out3;

//Same IP Check
if($dup_ip){
	$t_ip = $dup_ip;
	$out .= '<p><br /><b>Players with identical IPs</b>:';
	while ($d_i = each($t_ip)) {
		db(__FILE__,__LINE__,"select login_name from ${db_name}_users where login_id = '$d_i[key]'");
		$ret = dbr();
		$out2 .= '<br /><a href="../player_info.php?target='.$d_i[key].'"><b class="b1">'.$ret['login_name'].'</b></a>';
	}
}
if(!$out1){
	$out2 = '<br />No Peeps with same IP.';
}
$out = $out.$out2;

$out_str = $out;
}
//ban player from game.
elseif(isset($ban)) {
if($ban == 2){ //show ban a player page.
	$max_time = 168;
	if(!isset($ban_target) || $ban_target < 1 || !$ban_time){
		db(__FILE__,__LINE__,"select login_name,login_id from ${db_name}_users where banned_time <= ".time()." && banned_time != -1 && login_id > 5 order by login_name");
		$out .= '<h3>Ban Control</h3>';
		$out .= '<p>Notes:<br />Number of hours you may ban a player for is limited to '.$max_time.', which is 1 week (7 days).<br />Setting a ban-time of -1 means the ban time will last until the game is reset.<br />You may reset a ban period at any time from this page.</p><form action="'.$_SERVER['PHP_SELF'].'" method="post" name="ban_form">';
		$out .= '<p>Select Player to Ban: <br /><br />';
		$out .= '<select name="ban_target">';
		$out .= '<option value="0">Select player... </option>';
		while($list_em = dbr()) {
			$out .= '<option value="'.$list_em['login_id'].'">'.$list_em['login_name'].'</option>';
		}
		$out .= '</select></p>';
		$out .= '<p>Enter the number of hours you would like the player to be banned for:</p><input type="text" name="ban_time" size="3" /> hours';
		$out .= '<input type="hidden" name="ban" value="2" /><p>Please give the reason you are banning this player (do not use apostrophes or quotation marks).</p><textarea name="ban_reason" cols="50" rows="5" wrap="soft"></textarea><br /><br /><input type="submit" value="Ban" /></form>';
	} elseif ($ban_target > 0){
		db(__FILE__,__LINE__,"select login_name from ${db_name}_users where login_id = '$ban_target'");
		$ban_info = dbr();
		if($ban_time > $max_time || $ban_time < -1){
			$out .= '<h3>Ban Control</h3>';
			$out .= 'Maximum period of time a player may be banned for is <b>'.$max_time.'</b> hours.<br />Or set to -1 to ban for the rest of the game.';
		} elseif(!$sure){
			$rs='';
			get_var('Ban Control',$_SERVER['PHP_SELF'],'Are you sure you want to ban <b class=b1>'.$ban_info['login_name'].'</b> for <b>'.$ban_time.'</b> hours?','sure','yes');
		} else {
			$out .= '<h3>Ban Control</h3>';
			insert_history($ban_target,'Was Banned from the game for '.$ban_time.' hours');
			if(empty($ban_reason)){
				$ban_reason = 'No Reason.';
			}

			if($ban_time > 0){
				$ban_time = time() + round($ban_time * 3600);
			}

			dbn(__FILE__,__LINE__,"update ${db_name}_users set banned_time = '$ban_time', banned_reason = '$ban_reason' where login_id = '$ban_target'");
			if($ban_time > 0){
				$time_text = date( 'D jS M - H:i',$ban_time);
			} else {
				$time_text = 'it resets';
			}
			post_news('<b class=b1>'.$ban_info['login_name'].'</b> has been banned from the game until '.$time_text.' by the Admin. <br />The reason being:<br />'.$ban_reason);
			$out = '<b class=b1>'.$ban_info['login_name'].'</b> has been banned from the game until '.$time_text.'.<br /><br />';
		}
	}
} elseif($unban){
	db(__FILE__,__LINE__,"select login_name from ${db_name}_users where login_id = $unban");
	$ban_info = dbr();
	$out .= '<h3>Ban Control</h3>';
	insert_history($unban,'Was Un-Banned from the game');
	dbn(__FILE__,__LINE__,"update ${db_name}_users set banned_time = '0', banned_reason = '' where login_id = '$unban'");
	$out .= '<b class=b1>'.$ban_info['login_name'].'</b> was un-banned.<br /><br />';
	post_news('<b class=b1>'.$ban_info['login_name'].'</b> was un-banned by the Admin');
}

//list players who are presently banned
db(__FILE__,__LINE__,"select login_name, login_id, banned_time, banned_reason from ${db_name}_users where banned_time = -1 || banned_time > ".time()." order by banned_time desc");
$b_t1_out .= 'Listing Banned Players:';
$b_t1_out .= make_table(array('Login Name','Banned until','Reason',''));
while($list_banned = dbr()){
	if($list_banned['banned_time'] != -1){
		$temp_343 = date( 'D jS M - H:i',$list_banned['banned_time']);
	} else {
		$temp_343 = 'End of Game';
	}
	$b_t_out .= make_row(array(print_name($list_banned),$temp_343,$list_banned['banned_reason'],'<a href='.$_SERVER['PHP_SELF'].'?ban=1&unban='.$list_banned['login_id'].'>Un-Ban</a>'));
}

$out .= '<h3>Ban Control</h3>';
$out .= '<a href='.$_SERVER['PHP_SELF'].'?ban=2>Ban a player</a><br /><br />';
if(empty($b_t_out)){
	$out .= '<br /><br />No players presently banned.<br />';
} else {
	$out .= $b_t1_out.$b_t_out.'</table>';
}
$out_str = $out;
}
//List Ports
elseif(isset($port)) {
$out .= '<h3>List All Ports</h3>';
$ADODB_FETCH_MODE = 2;
if(isset($sort_port)){
		if($sorted==1){
			$going = "asc";
			$sorted=2;
		} else {
			$going = "desc";
			$sorted=1;
		}
		db(__FILE__,__LINE__,"select port_id,location from ${db_name}_ports");
	} else {
		db(__FILE__,__LINE__,"select port_id,location from ${db_name}_ports");
	}

	$clan_port = dbr(1);
	if($clan_port) {
		$out .= $rs.make_table(array("<a href=$PHP_SELF?sort_ports=port_id&sorted=$sorted>Port ID</a>","<a href=$PHP_SELF?sort_ports=location&sorted=$sorted>Location</a>"));
		while($clan_port) {
			$clan_port[port_id] = "<b class=b1>$clan_port[port_id]</b>";
			$out .= make_hash_row($clan_port);
			$clan_port = dbr(1);
		}
		$error_str .= "</table>";
		print_page("Ports",$out);
	} else {
	$out .= 'No ports exist.  I suggest you run universe generation.';
}
$out_str = $out;
}
//List Uni Planets
elseif(isset($uplanets)) {
$out .= '<h3>List All UniGen Planets</h3>';
$ADODB_FETCH_MODE = 2;
if(isset($sort_planets)){
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	db(__FILE__,__LINE__,"select planet_name,location,fighters,colon,cash,metal,fuel,elect,organ from ${db_name}_planets where location != 1 && planet_type >= 0 && unigen = 1 order by '$sort_planets' $going");
} else {
	db(__FILE__,__LINE__,"select planet_name,location,fighters,colon,cash,metal,fuel,elect,organ from ${db_name}_planets where location != 1 && planet_type >= 0 && unigen = 1 order by owner_name asc, fighters desc, planet_name asc");
}

$clan_planet = dbr();
if($clan_planet) {
	$out .= $rs.make_table(array("<a href=$PHP_SELF?sort_planets=planet_name&sorted=$sorted>Planet Name</a>","<a href=$PHP_SELF?sort_planets=location&sorted=$sorted>Location</a>","<a href=$PHP_SELF?sort_planets=fighters&sorted=$sorted>Fighters</a>","<a href=$PHP_SELF?sort_planets=colon&sorted=$sorted>Colonists</a>","<a href=$PHP_SELF?sort_planets=cash&sorted=$sorted>Cash</a>","<a href=$PHP_SELF?sort_planets=metal&sorted=$sorted>Metal</a>","<a href=$PHP_SELF?sort_planets=fuel&sorted=$sorted>Fuel</a>","<a href=$PHP_SELF?sort_planets=elect&sorted=$sorted>Electronics</a>","<a href=$PHP_SELF?sort_planets=organ&sorted=$sorted>Organics</a>"));
	while($clan_planet) {
		$clan_planet['owner_name'] = "<b class=b1>$clan_planet[owner_name]</b>";
		$out .= make_hash_row($clan_planet);
		$clan_planet = dbr(1); //remove 1?
	}
	$out .= "</table>";
	print_page("Planet List",$out);
} else {
	$out .= 'No planets exist';
}
$out_str = $out;
}
//List Player Planets
elseif(isset($pplanets)) {
$out .= '<h3>List All Planets</h3>';
$ADODB_FETCH_MODE = 2;
if(isset($sort_planets)){
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	db(__FILE__,__LINE__,"select owner_name,planet_name,location,fighters,colon,cash,metal,fuel,elect,organ from ${db_name}_planets where location != 1 && planet_type >= 0 && unigen = 0 order by '$sort_planets' $going");
} else {
	db(__FILE__,__LINE__,"select owner_name,planet_name,location,fighters,colon,cash,metal,fuel,elect,organ from ${db_name}_planets where location != 1 && planet_type >= 0 && unigen = 0 order by owner_name asc, fighters desc, planet_name asc");
}

$clan_planet = dbr();
if($clan_planet) {
	$out .= $rs.make_table(array("<a href=$PHP_SELF?sort_planets=owner_name&sorted=$sorted>Planet Owner</a>","<a href=$PHP_SELF?sort_planets=planet_name&sorted=$sorted>Planet Name</a>","<a href=$PHP_SELF?sort_planets=location&sorted=$sorted>Location</a>","<a href=$PHP_SELF?sort_planets=fighters&sorted=$sorted>Fighters</a>","<a href=$PHP_SELF?sort_planets=colon&sorted=$sorted>Colonists</a>","<a href=$PHP_SELF?sort_planets=cash&sorted=$sorted>Cash</a>","<a href=$PHP_SELF?sort_planets=metal&sorted=$sorted>Metal</a>","<a href=$PHP_SELF?sort_planets=fuel&sorted=$sorted>Fuel</a>","<a href=$PHP_SELF?sort_planets=elect&sorted=$sorted>Electronics</a>","<a href=$PHP_SELF?sort_planets=organ&sorted=$sorted>Organics</a>"));
	while($clan_planet) {
		$clan_planet['owner_name'] = "<b class=b1>$clan_planet[owner_name]</b>";
		$out .= make_hash_row($clan_planet);
		$clan_planet = dbr(1); //remove 1?
	}
	$out .= "</table>";
	print_page("Planet List",$out);
} else {
	$out .= 'No planets exist';
}
$out_str = $out;
}
//List BlackMarket
elseif(isset($bm)) {
$out .= '<h3>List All blackmarkets</h3>';
$ADODB_FETCH_MODE = 2;
if(isset($sort_bmrkts)){
		if($sorted==1){
			$going = "asc";
			$sorted=2;
		} else {
			$going = "desc";
			$sorted=1;
		}
		db(__FILE__,__LINE__,"select bmrkt_id location tech_variance bmrkt_type bm_name from ${db_name}_bmrkt");
	} else {
		db(__FILE__,__LINE__,"select bmrkt_id,location,tech_variance,bmrkt_type,bm_name from ${db_name}_bmrkt");
	}

	$clan_bmrkt = dbr(1);
	if($clan_bmrkt) {
		$out .= $rs.make_table(array("<a href=$PHP_SELF?sort_bmrkts=bmrkt_id&sorted=$sorted>Black Market ID</a>","<a href=$PHP_SELF?sort_bmrkts=location&sorted=$sorted>Location</a>","<a href=$PHP_SELF?sort_bmrkts=tech_variance&sorted=$sorted>Tech Variance</a>","<a href=$PHP_SELF?sort_bmrkts=bmrkt_type&sorted=$sorted>Black Market Type</a>","<a href=$PHP_SELF?sort_bmrkts=bm_name&sorted=$sorted>Black Market Name</a>"));
		while($clan_bmrkt) {
			$clan_bmrkt[bmrkt_id] = "<b class=b1>$clan_bmrkt[bmrkt_id]</b>";
			$out .= make_hash_row($clan_bmrkt);
			$clan_bmrkt = dbr(1);
		}
		$error_str .= "</table>";
		print_page("Black Markets List",$out);
	} else {
	$out .= 'There are no Blackmarkets.';
	}
$out_str = $out;
}
//(Un-)Paused Status
elseif(isset($pause)) {
$out = '<h3>Game running status</h3>';
if($pause != 2){
	db(__FILE__,__LINE__,"select paused from se_games where db_name = '$db_name'");
	$pause = dbr();
	$out .= 'Game running status';
	$out .= '<form name="get_var_form" action="'.$_SERVER['PHP_SELF'].'" method="post">';
	$out .= '<input type="hidden" name="pause" value="2" />';
	$out .= '<input type="text" name="new_pause" value="'.$pause[0].'" size="30" />';
	$out .= '<br /><input type="submit" value="Change" /></form>';
} else {
	dbn(__FILE__,__LINE__,"update se_games set paused = '$_POST[new_pause]' where db_name = '$db_name'");
	$out .= 'Game Status changed to: <br /><b>'.$_POST['new_pause'].'</b>.';
	$out .= '<br>News Updated';
if($_POST['new_pause'] == 0){
	post_news('Game Un-Paused');
}elseif($_POST['new_pause'] == 1){
	post_news('Game Paused');
}
}
$out_str = $out;
}

elseif(isset($vars)) {
	unset($out);
	$out .= '<form action="'.$_SERVER['PHP_SELF'].'" name="get_var_form" method="post">';
	$out .= '<input type="hidden" name="save_vars" value="1" />';
	$out .= '<input type="submit" value="Submit Vars" />';
	$out .= '<p>Note: Only variables that are within range will be saved.</p>';
	if(isset($type_var)) {
		db(__FILE__,__LINE__,"select * from ${db_name}_db_vars where name != 'rejoin_delay' && type = '${_GET['type_var']}' order by name");
		$db_var = dbr();
		db2(__FILE__,__LINE__,"select count(value) from ${db_name}_db_vars where name != 'rejoin_delay' && type = '${_GET['type_var']}' order by name");
		$db_var_c = dbr2();
	} else {
		db(__FILE__,__LINE__,"select * from ${db_name}_db_vars where name != 'rejoin_delay' order by name");
		$db_var = dbr();
		db2(__FILE__,__LINE__,"select count(value) from ${db_name}_db_vars where name != 'rejoin_delay' order by name");
		$db_var_c = dbr2();
	}
	$row_c = round($db_var_c[0] * .5);
	$out .= '<table border="0" cellspacing="10" width="100%"><tr><td width="50%" valign="top">';
	$ct5 = 0;
	while($db_var) {
		if($ct5 == $row_c) {
			$out .= '</td><td width="50%" valign="top">';
		}
		$out .= '<table border="2" cellspacing="1" width="100%"><tr bgcolor="#333333"><td width="250"><b><font color="#AAAAEE">'.$db_var['name'].'</font></b> ( '.$db_var['min'].' .. '.$db_var['max'].' )</td><td align="right"><input type="text" name="'.$db_var['name'].'" value="'.$db_var['value'].'" size="8" /></td></tr><tr bgcolor="#555555"><td colspan="2"><blockquote>'.$db_var['descript'].'</blockquote></td></tr></table><br />';
		$ct5 += 1;
		$db_var = dbr();
	}
	$out .= '</td></tr></table>';
	$out .= '<p><input type="submit" value="Submit Vars" />';
	$out .= '<br /></form>';
if(isset($_POST['save_vars'])) {
	unset($out);
	foreach($_POST as $var => $value){
		if($var == 'save_vars') {
			continue;
		}
		// update the var, and be sure to only update it if the new range is in value. Otherwise leave it alone.
		dbn(__FILE__,__LINE__,"update ${db_name}_db_vars set value = '$value' where name = '$var' && '$value' >= min && '$value' <= max");
	}
	if(($var_source == 0 ) && ( isset($var_source) )) {
		//save the changed variables to files.
		require_once('admincp/includes/build_vars.php');
	}
	insert_history($user['login_id'],'Updated Game Vars');
	$out .= '<p>Admin variables update successfully</p>';
	$out .= '<p><a href="index.php">Back to Admin Index</a></p>';
	$rs = '<p><a href="location.php">Back to Star System</a></p>';
}
$out_str = $out;
}

print_page("Server Admin",$out_str);
?>