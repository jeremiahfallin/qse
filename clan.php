<?php

include_once("includes/nocache.inc.php");

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

include_once("includes/clan_funcs.inc.php");

$filename = 'clan.php';

$ret_str = '<p><a href=location.php>Return to star system</a>';

sudden_death_check($user);


$dev2 = $owner_id;

if(isset($join)) { // Join clan
	if($user['clan_id']) {
		print_page('Join Clan','You are already a member of a clan.');
	}
	db(__FILE__,__LINE__,"select * from ${db_name}_clans where clan_id = $join");
	$clan = dbr();
	if($clan['members'] >= $clan_member_limit && (($user['login_id'] != 1) || ($user['login_id'] == $dev2))) {
		print_page('Join Clan','That clan already has the maximum number of members allowed.');
	}
	if($user['login_id'] == 1 || $user['login_id'] == $dev2) {
		$passwd = $clan['passwd'];
	}
	if($passwd == '') {
		get_var('Join Clan',$filename,'What is the clan password?','passwd','');
	} elseif($clan['passwd'] != $passwd) {
		print_page('Join Clan','The password is incorrect.');
	} else {
		dbn(__FILE__,__LINE__,"update ${db_name}_users set clan_id = $join, clan_sym = '$clan[symbol]', clan_sym_color = '$clan[sym_color]' where login_id = $user[login_id]");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set clan_id = $join where owner_id = $user[login_id]");
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set clan_id = $join where login_id = $user[login_id]");
		dbn(__FILE__,__LINE__,"update ${db_name}_mines set clan_id = $join where login_id = $user[login_id]");
		$user['clan_id'] = $join;
		$user['clan_sym'] = $clan['symbol'];
		$user['clan_sym_color'] = $clan['sym_color'];
		if($user['login_id'] > 1 || $user['login_id'] != $dev2){
			dbn(__FILE__,__LINE__,"update ${db_name}_clans set members = members + 1 where clan_id = '$join'");
			send_message($clan['leader_id'],"<b class=b1>$user[login_name]</b> has joined your clan.");
		}
		insert_history($user['login_id'],"Joined $clan[clan_name] clan.");
	}
}

if(isset($leave)) { // Leave clan
	db(__FILE__,__LINE__,"select leader_id,clan_name from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr();
	if($clan['leader_id'] == $user['login_id']) {
		$error_str .= "The clan leader may not leave the clan.<p>You may assign a new leader and then leave.";
	} else {
	dbn(__FILE__,__LINE__,"update ${db_name}_users set clan_id = 0, clan_sym = '', clan_sym_color = '' where login_id = $user[login_id]");
	dbn(__FILE__,__LINE__,"update ${db_name}_planets set clan_id = -1 where owner_id = $user[login_id]");
	dbn(__FILE__,__LINE__,"update ${db_name}_ships set clan_id = -1 where login_id = $user[login_id]");
	dbn(__FILE__,__LINE__,"update ${db_name}_mines set clan_id = 0 where login_id = $user[login_id]");
	if($user['login_id'] > 1 || $user['login_id'] != $dev2){
		dbn(__FILE__,__LINE__,"update ${db_name}_clans set members = members - 1 where clan_id = '$user[clan_id]'");
		send_message($clan['leader_id'],"<b class=b1>$user[login_name]</b> has left your clan.");
	}
		$user['clan_id'] = 0;
		$user['clan_sym'] = "";
		$user['clan_sym_color'] = "";
	}
	update_clans();
	insert_history($user['login_id'],"Left $clan[clan_name] clan.");
}

if(isset($kick)) { // Kick a clan member
	db(__FILE__,__LINE__,"select leader_id,clan_name from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr();
	db2(__FILE__,__LINE__,"select clan_id,login_name from ${db_name}_users where login_id='$kick'");
	$kick_clan = dbr2();
	if($clan['leader_id'] != $user['login_id'] && $user['login_id'] !=1) {
		$error_str .= "You are not the leader of this clan.<p>";
	} elseif($user['clan_id'] < 1) {
		$error_str .= "You are not in a clan as such.<p>";
	} elseif($kick_clan['clan_id'] != $user['clan_id']) {
		$error_str .= "You can only kick members of your own clan.<p>";
	} elseif($kick == $clan['leader_id']) {
		$error_str .= "You may not Kick the Clan Leader out of the clan.<p>";
	} elseif($kick == 1) {
		$error_str .= "You may not Kick the Admin out of your clan.<p>";
	} elseif($sure != yes) {
		get_var('Kick Clan Member',$filename,'Are you sure you want to kick this clan member out?','sure','yes');
	} else {
		dbn(__FILE__,__LINE__,"update ${db_name}_users set clan_id = 0, clan_sym = '', clan_sym_color = '' where login_id = $kick");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set clan_id = -1 where owner_id = $kick");
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set clan_id = -1 where login_id = $kick");
		dbn(__FILE__,__LINE__,"update ${db_name}_mines set clan_id = 0 where login_id = $kick");
		dbn(__FILE__,__LINE__,"update ${db_name}_clans set members = members - 1 where clan_id = '$kick_clan[clan_id]'");
		$error_str .= "User <b class=b1>$kick_clan[login_name]</b> kicked out of the clan.<p>";
	insert_history($user['login_id'],"Thrown out of $clan[clan_name] clan.");
	}
}

if(isset($disband)) { // Disband clan
	db(__FILE__,__LINE__,"select * from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr();
	if(($clan['leader_id'] != $user['login_id']) && ($user['login_id'] != 1)) {
		$error_str .= "You are not the leader of this clan.<p>";
	} elseif($user['clan_id'] < 1) {
		$error_str .= "You are not in a clan as such.<p>";
	} elseif($sure != yes) {
		get_var('Disband Clan',$filename,'Are you sure you want to disband this clan?','sure','yes');
	} else {
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
	}
	update_clans();
	insert_history($user['login_id'],"Disbanded $clan[clan_name] clan.");
}

if(isset($create)) { //Create a new clan
	db(__FILE__,__LINE__,"select count(clan_id) as numclans from ${db_name}_clans");
	$result_max_clans = dbr();

	if ($result_max_clans['numclans'] >= $max_clans && $user['login_id'] != 1) {
		$error_str .= "The Maximum allowed clans set by the admin has been met.";
	}elseif(!isset($name)) {
		get_var('Create Clan',$filename,'What should the name of your new clan be?','name','');
	} elseif (!isset($symbol)) {
		get_var('Create Clan',$filename,'Please choose a three letter symbol for your clan.<br /><b class=b1>Must</b> have either two or three letters: <br />(only symbols acceptable: ~!@#$%&*_+-=��������׀��)','symbol','');
	} elseif (!isset($preset_color) && !isset($user_color)) {
		// Since $preset_color is for the radio buttons (which always has one selected),
		// use $user_color to allow a custom color to be entered.
		$tempstr = "<form action=clan.php method=post>";
		$tempstr .= "Choose a color for the clan symbol:<p>";

		while (list($var, $value) = each($_POST)) {
			$tempstr .= "<input type=hidden name=$var value='$value'>";
		}
	$tempstr .= "Enter your own: <input type=name name=user_color size=15><p> Or pick one:";
	$tempstr .= "<table><tr><td>";
	$tempstr .= "<input type=radio name=preset_color value='FF0099'><font color=FF0099>FF0099</font><br />";
		$tempstr .= "<input type=radio name=preset_color value='FF0000'><font color=FF0000>FF0000</font><br />";
		$tempstr .= "<input type=radio name=preset_color value='BF00BF'><font color=BF00BF>BF00BF</font><br />";
		$tempstr .= "</td><td>";
		$tempstr .= "<input type=radio name=preset_color value='FFFF00'><font color=FFFF00>FFFF00</font><br />";
		$tempstr .= "<input type=radio name=preset_color value='BFBF00'><font color=BFBF00>BFBF00</font><br />";
		$tempstr .= "<input type=radio name=preset_color value='00FF00'><font color=00FF00>00FF00</font><br />";
		$tempstr .= "</td><td>";
		$tempstr .= "<input type=radio name=preset_color value='00BFBF'><font color=00BFBF>00BFBF</font><br />";
		$tempstr .= "<input type=radio name=preset_color value='F396EC'><font color=F396EC>F396EC</font><br />";
		$tempstr .= "<input type=radio name=preset_color value='C1B8FA'><font color=C1B8FA>C1B8FA</font><br />";
		$tempstr .= "</td><td>";
		$tempstr .= "<input type=radio name=preset_color value='96D2F3'><font color=96D2F3>96D2F3</font><br />";
		$tempstr .= "<input type=radio name=preset_color value='B7C5D9'><font color=B7C5D9>B7C5D9</font><br />";
		$tempstr .= "<input type=radio name=preset_color value='FFFFFF' checked><font color=FFFFFF>FFFFFF</font>";
		$tempstr .= "</td></tr></table>";
	$tempstr .= "<table></td></td>";
	$tempstr .= "<td align=center></table><p>";
		$tempstr.= ' <input type=submit value=Submit></form>';
		print_page('Choose symbol color',$tempstr);
	} elseif (!isset($confirm_color)) {
		// The color confirmation page lets users see their custom color before continuing
		$sym_color = $preset_color;  // Assume preset color -- radio buttons guarantee this is set
		if (isset($user_color)) {
			$user_color = strtoupper(trim($user_color));
			if (strlen($user_color) > 0) {
				if (preg_match('/^[0-9A-F]{6}$/',$user_color)) {
					$sym_color = $user_color;  // User color is valid, so use it
				} else {
					$tempstr = "<form action=clan.php method=post>";
					$tempstr .= "<p>The color you specified ($user_color) is invalid. ";
					$tempstr .= "It must be 6 hexadecimal digits that represent the R-G-B values of a color.<p/>";
					$tempstr .= '<input type=button value=Retry onclick="javascript: history.back();">';
					$tempstr .= '</form>';
					print_page('Invalid symbol color',$tempstr);
				}
			}
		}
		$tempstr = "<form action=clan.php method=post>";
		$tempstr .= "<input type=hidden name=sym_color value='$sym_color'>"; // If confirmed, this will be the color used
		while (list($var, $value) = each($_POST)) {
			$tempstr .= "<input type=hidden name=$var value='$value'>";
		}
		$tempstr .= "<p>You have chosen the color <font color=$sym_color>$sym_color</font> for your clan.<p/>";
		$tempstr .= '<p>Do you want to keep this color?</p>';
		$tempstr .= '<input type=submit name=confirm_color value=Yes>&nbsp;&nbsp;&nbsp;';
		$tempstr .= '<input type=button value=No onclick="javascript: history.back();">';
		$tempstr .= '</form>';
		print_page('Confirm symbol color',$tempstr);
	} elseif($passwd == '') {
		get_var('Create Clan',$filename,'What should the clan password be? (5 Characters Minimum, 25 Max)','passwd','');
	} elseif($passwd_verify == '') {
		get_var('Create Clan',$filename,'Please enter the clan password again.','passwd_verify','');
	} else {
	if(strlen($passwd) < 5) {
			print_page("Create Clan",'The password must be at least 5 characters.');
	}elseif($passwd == $user['passwd']) {
			print_page("Create Clan",'No-way. You may not use the same pass as your user account. Try a different one.');
	}elseif($passwd != $passwd_verify) {
			print_page("Create Clan",'The passwords did not match.');
		}
		$symbol = substr($symbol,0,3);
		if(strlen($symbol) < 2) {
			print_page('Create Clan','Your clan symbol must have at least one character.');
		}
		if(!valid_input($symbol)) {
			print_page('Create Clan','Your clan name may contain only letters, numbers or any of these characters: ~!@#$%&*_+-=��������׀��');
		}
		db(__FILE__,__LINE__,"select symbol from ${db_name}_clans where symbol = '$symbol'");
		if(record_count($query) != 0) {
			print_page('Create Clan','That symbol is already in use.');
		}
		$name = htmlspecialchars($name);
		$name = addslashes($name);
		$sym_color = substr($sym_color,0,6);

		$sym_color = htmlspecialchars($sym_color);
		$sym_color = addslashes($sym_color);
		$symbol = htmlspecialchars($symbol);
		$symbol = addslashes($symbol);
		$passwd = addslashes($passwd);
		$q_string = "insert into ${db_name}_clans (";
		$q_string = $q_string . "clan_name,leader_id,passwd,symbol,sym_color";
		$q_string = $q_string . ") values(";
		$q_string = $q_string . "'$name','$user[login_id]','$passwd','$symbol','$sym_color')";
		dbn(__FILE__,__LINE__,$q_string);

		db(__FILE__,__LINE__,"select clan_id from ${db_name}_clans where leader_id = '$user[login_id]' and passwd = '$passwd' and symbol = '$symbol' and clan_name = '$name'");
		$clan_id1 = dbr();
		$clan_id = $clan_id1['clan_id'];

		dbn(__FILE__,__LINE__,"update ${db_name}_planets set clan_id = $clan_id where owner_id = $user[login_id]");
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set clan_id = $clan_id where login_id = $user[login_id]");
		dbn(__FILE__,__LINE__,"update ${db_name}_mines set clan_id = $clan_id where login_id = $user[login_id]");
		dbn(__FILE__,__LINE__,"update ${db_name}_users set clan_id = $clan_id, clan_sym = '$symbol', clan_sym_color = '$sym_color', clan_leader = '1' where login_id = $user[login_id]");

		$user['clan_id'] = $clan_id;
		$user['clan_sym'] = $symbol;
		$user['clan_sym_color'] = $sym_color;
		post_news("<b class=b1>$user[login_name]</b> created the <b class=b1>$name(<font color=$sym_color>$symbol</font>)</b> Clan.");
		insert_history($user['login_id'],"Created the $name clan.");
	}
}

if($lead_change) { // Assign new leader
	db(__FILE__,__LINE__,"select leader_id from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr();
	if($user['clan_id'] < 1) {
		$error_str .= "You are not in a clan as such.<p>";
	} elseif(($clan['leader_id'] != $user['login_id']) && ($user['login_id'] != 1)) {
		$error_str .= "You are not the leader of this clan.<p>";
	} elseif(!$leader_id) {
	db2(__FILE__,__LINE__,"select login_id,login_name from ${db_name}_users where clan_id = '$user[clan_id]' && login_id != '1' && login_id != '$clan[leader_id]'");
	$member_name = dbr2();
	if($member_name) {
		$ostr .= "<form action=$filename method=post>";
		$ostr .= "Please choose another clan member to be the leader:<p>";
		while (list($var, $value) = each($_GET)) {
			$ostr .= "<input type=hidden name=$var value='$value'>";
		}
		while (list($var, $value) = each($_POST)) {
			$ostr .= "<input type=hidden name=$var value='$value'>";
		}
		$ostr .= "<select name=leader_id>";
		while ($member_name) {
			$ostr .= "<option value=$member_name[login_id]>$member_name[login_name]</option>";
			$member_name = dbr2();
		}
		$ostr .= "</select>";
		$ostr .= ' <input type=submit value=Submit></form>';
		print_page('Choose new clan leader',$ostr);
	} else {
		print_page('Error',"No-one in your clan can become clan leader. That means u're stuck as clan leader.");
	}
	} elseif($sure != yes && $user['login_id'] != 1) {
		get_var('Change Clan Leader',$filename,'Are you sure you want to relinquish leadership of this clan?','sure','yes');
	} else {
		dbn(__FILE__,__LINE__,"update ${db_name}_clans set leader_id = $leader_id where clan_id = $user[clan_id]");
		$clan['leader_id'] = $leader_id;
		$error_str .= "Clan leader changed<p>";
	}
}

if(isset($ranking) || ($user['clan_id'] == 0 && !isset($clan_info))) { // Clan Ranking

	db2(__FILE__,__LINE__,"select * from ${db_name}_clans order by clan_score desc, fighter_kills desc, members desc");
	$clan = dbr2();
	db(__FILE__,__LINE__,"select count(clan_id) from ${db_name}_clans");
	$clan_count = dbr();
	if($clan) {
		$error_str .= "There are <b>$clan_count[0]</b> clans at present. <br />The clan limit for this game is <b>$max_clans</b>.<p>";
		$error_str .= make_table(array("Clan Name","Relation","Members","Score","Fighter Kills"));
		while($clan)
		{
			unset($option);

			if((($user['clan_id'] == 0) && ($clan['members'] < $clan_member_limit)) || $user['login_id'] == 1) {
				$option = "<a href=clan.php?join=$clan[clan_id]>Join</a>";
			} elseif($clan['members'] >= $clan_member_limit) {
				$option = "Full";
			} elseif($clan['clan_id'] == $user['clan_id']) {
				$option = "<a href=clan.php>View</a>";
			}
			$rel = find_designation($clan['clan_id']); // prints relation from function
			$error_str .= make_row(array("<b class=b1>$clan[clan_name]</b>(<b><font color=$clan[sym_color]>$clan[symbol]</font></b>)",
				"$rel",
				"$clan[members]",
				"$clan[clan_score]",
				"$clan[fighter_kills]",
				"<a href=clan.php?clan_info=1&target=$clan[clan_id]>Details</a>",
				$option));
			$clan = dbr2();
		}
		$error_str .= "</table>";
	} else {
		$error_str .= "<br />There are no clans at present. <br />Maximum number of Clans allowed is <b>$max_clans</b>.";
	}
	if(($user['clan_id'] == 0 && $clan_count['0'] < $max_clans) || $user['login_id'] == 1) {
		$error_str .= "<p><a href=clan.php?create=1>Create a new clan</a><br />";
	} elseif($clan_count['0'] >= $max_clans) {
		$error_str .= "<p>The Maximum number of clans(<b>$max_clans</b>) has been reached.";
	} else {
		db(__FILE__,__LINE__,"select clan_name,leader_id,passwd from ${db_name}_clans where clan_id = '$user[clan_id]'");
		$sec_clan = dbr();
		$error_str .= "<p>You are a member of the <b class=b1>$sec_clan[clan_name]</b> clan.";
	}
	print_page("Clan Rankings",$error_str);
}

if(isset($changepass)) {// change password
	db(__FILE__,__LINE__,"select leader_id,passwd from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr();
	$rs = "<a href=clan.php>Back To Clan Control</a>";
	if($user['clan_id'] < 1) {
		print_page("stop that","You are not in a clan as such.<p>");
	} elseif($changepass==1) {
	$temp_str = "Passwords must be minimum of five(5) characters long and a max of 25.";
		$temp_str .= "<table><form action=clan.php method=post><input type=hidden name=changepass value=changed>";
		$temp_str .= "<tr><td align=right>Old Password:</td><td><input type=password name=oldpass></td></tr>";
		$temp_str .= "<tr><td align=right>New Password:</td><td><input type=password name=newpass></td></tr>";
		$temp_str .= "<tr><td align=right>Re-type New Password:</td><td><input type=password name=newpass2></td></tr>";
		$temp_str .= "<tr><td></td><td><input type=Submit value='Change Password'></td></tr></form></table><br />";
		print_page("Change Password",$temp_str);
	} elseif ($changepass == 'changed') {
	if (isset($newpass) && ($newpass == $newpass2)) {
		if(strlen($newpass) < 5) {
				$temp_str = "The password must be at least 5 characters.<br />";
		}elseif($newpass == $user['passwd']) {
			$temp_str = "No-way. You may not use the same pass as your user account. Try a different one.<br />";		} elseif($newpass == $oldpass) {
			$temp_str = "What you wasting my bandwith for? Thats the same as the previous pass. Try somthing else.<br />";
		} elseif($clan['leader_id']!=$user['login_id']) {
			$temp_str = "Only the clan leader can change the password<br />";
			$temp_str .= "<a href='javascript:back()'>Go back</a><br />";
		} elseif ($oldpass == $clan['passwd']) {
			dbn(__FILE__,__LINE__,"update ${db_name}_clans set passwd='$newpass' where clan_id=$user[clan_id]");
			$clan['passwd']='$newpass';
			$temp_str .= "Clan password changed successfully<br />";
		} else {
			$temp_str = "The old password is not correct!<br />";
			$temp_str .= "<a href='javascript:back()'>Go back</a><br />";
		}
	} else {
		$temp_str = "Password mismatch!<br />";
		$temp_str .= "<a href='javascript:back()'>Go back</a><br />";
	}
	print_page("Change Password",$temp_str);
	}
}

if($clan_info == 1 && $target > 0){ // show clan info
	$ADODB_FETCH_MODE = 2;

	$x_link .= "<a href=clan.php>Clan Control</a>";

	if($user['login_id'] == 1 || $user['clan_id'] == $target) { // admin can see all, as can clan members.
		$full = 1;
	} else {
		$full = 0;
	}

	db(__FILE__,__LINE__,"select leader_id from ${db_name}_clans where clan_id = '$target'");
	$chk_l = dbr();
	db(__FILE__,__LINE__,"select login_name from ${db_name}_users where login_id = '$chk_l[leader_id]'");
	$clan_l = dbr();

	// list some statistics about the clan, as user is a member (or admin).
	if($full == 1){
		// planet details
		db(__FILE__,__LINE__,"select sum(fighters) as pfigs, count(planet_id) as planets, count(launch_pad) as lpads, sum(research_fac) as rfac, count(shield_gen) as sgens, sum(shield_charge) as scharge, sum(colon) as colon from ${db_name}_planets where clan_id = '$target'");
		$res1 = dbr();


		// planet percentages
		db(__FILE__,__LINE__,"select sum(fighters) as pfigs, count(planet_id) as planets from ${db_name}_planets where owner_id > '5'");
		$maths1 = dbr();


		// ship detals
		db(__FILE__,__LINE__,"select sum(fighters) as sfigs, sum(max_fighters) as max_figs, count(ship_id) as ships, sum(cargo_bays) as cargo from ${db_name}_ships where clan_id = '$target'");
		$res2 = dbr();

		// used for ship percentages
		db(__FILE__,__LINE__,"select sum(fighters) as sfigs, count(ship_id) as ships from ${db_name}_ships where login_id > '5'");
		$maths2 = dbr();


		// get user detals.
		db(__FILE__,__LINE__,"select count(login_id) as members, sum(cash) as cash, sum(genesis) as gen, sum(terra_imploder) as imploder, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(bounty) as bounty, sum(score) as score, sum(alpha) as alpha, sum(gamma) as gamma, sum(delta) as delta, sum(sn_effect) as sne, sum(tech) as tech, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(turns_run) as trun, sum(turns) as turns from ${db_name}_users where clan_id = '$target'");
		$res3 = dbr();

		// used to calculate percentages
		db(__FILE__,__LINE__,"select count(login_id) as members, sum(cash) as cash, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(bounty) as bounty, sum(score) as score, sum(tech) as tech, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(ships_killed_points) as spkilled, sum(ships_lost_points) as splost, sum(turns_run) as trun, sum(turns) as turns from ${db_name}_users where login_id > '5'");
		$maths3 = dbr();

		$temp_str .= $x_link."<br /><br />"; // link to clan control
	} else {// only partial listing given, so only get small amounts of data.
		db(__FILE__,__LINE__,"select count(login_id) as members, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(turns_run) as trun from ${db_name}_users where clan_id = '$target'");
		$res3 = dbr();

		// for percentages
		db(__FILE__,__LINE__,"select count(login_id) as members, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(turns_run) as trun from ${db_name}_users where login_id > '5'");
		$maths3 = dbr();
	}

	db(__FILE__,__LINE__,"select clan_name, passwd, leader_id, symbol, sym_color from ${db_name}_clans where clan_id = '$target'");
	$cd = dbr();

	if($full == 0){

	$temp_str .= make_table(array("",""));
	$temp_str .= quick_row("Clan Name",$cd['clan_name']);
	$temp_str .= quick_row("Clan Symbol","<font color=#".$cd[sym_color].">$cd[symbol]</font>");
	$temp_str .= quick_row("Member Count",$res3['members']);


		$temp_str .= quick_row("Fighters Killed",calc_perc($res3['fkilled'],$maths3['fkilled']));
		$temp_str .= quick_row("Fighters Lost",calc_perc($res3['flost'],$maths3['flost']));
		$temp_str .= quick_row("Ships Killed",calc_perc($res3['skilled'],$maths3['skilled']));
		$temp_str .= quick_row("Ships Lost",calc_perc($res3['slost'],$maths3['slost']));
		$temp_str .= quick_row("Turns Run",calc_perc($res3['trun'],$maths3['trun']));
		$temp_str .= "</table><br /><br />Below is a listing of the members of the <b class=b1>$cd[clan_name]</b1> clan. ".make_table(array("User","Turns Run","Fighters Killed","Fighters Lost","Ships Killed","Ships Lost"));

		db(__FILE__,__LINE__,"select login_id,turns_run, fighters_killed,fighters_lost, ships_killed, ships_lost from ${db_name}_users where clan_id = '$target'");
		while($clan_members = dbr()){
			$clan_members['login_id'] = print_name($clan_members);
			$clan_members['fighters_killed'] = calc_perc($clan_members['fighters_killed'],$maths3['fkilled']);
			$clan_members['fighters_lost'] = calc_perc($clan_members['fighters_lost'],$maths3['flost']);
			$clan_members['ships_killed'] = calc_perc($clan_members['ships_killed'],$maths3['skilled']);
			$clan_members['ships_lost'] = calc_perc($clan_members['ships_lost'],$maths3['slost']);
			$clan_members['turns_run'] = calc_perc($clan_members['turns_run'],$maths3['trun']);
			$temp_str .= make_hash_row($clan_members);
		}

		$temp_str .= "</table><br />";

	} else {
		$t_figs = $res1['pfigs'] + $res2['sfigs'];
		$t_fcap = $maths1['pfigs'] + $maths2['sfigs'];
	}

	$temp_str .= Print_ClanDetailsFull();

	print_page("Clan Info",$temp_str.$x_link);
}


// print normal page for clan-member
db(__FILE__,__LINE__,"select * from ${db_name}_clans where clan_id = $user[clan_id]");
$clan = dbr();
$clan_name = stripslashes($clan['clan_name']);
$error_str .= "You are a member of the <b class=b1>$clan_name</b>(<font color=$clan[sym_color]>$clan[symbol]</font>) clan.<p>";

if($clan['leader_id'] == $user['login_id']){
	$error_str .= "Password for the clan is <b class=b1>$clan[passwd]</b>.<br /><br />";
}

//return assoc array
$ADODB_FETCH_MODE = 2;

$error_str .= make_table(array("Member","Turns","Cash","Tech Units","Kills","Status"));
db(__FILE__,__LINE__,"select login_name,turns,cash,tech,ships_killed,last_request,login_id from ${db_name}_users where clan_id = $user[clan_id] order by login_name,ships_killed");
while($clan_member = dbr()) {
	if($clan['leader_id'] == $clan_member['login_id']) {
		$clan_member['login_name'] = "(L) ".print_name($clan_member);
	} else {
		$clan_member['login_name'] = print_name($clan_member);
	}
	if($clan_member['last_request'] > (time()-300)){
		$clan_member['last_request'] = "Online";
	} else {
		$clan_member['last_request'] = "N/A";
	}
	$temp_id = $clan_member['login_id'];
	if($clan_member['login_id'] != $user['login_id']){
		$clan_member['login_id'] = "<a href=message.php?target=$clan_member[login_id]>Message</a>";
	} else {
		$clan_member['login_id'] = NULL;
	}
	if(($user['login_id'] == $clan['leader_id'] || $user['login_id'] == 1) &&	($temp_id != $clan['leader_id'] && $temp_id != 1)) {
		$clan_member['login_id'] .= " - <a href=clan.php?kick=$temp_id>Kick</a>";
	}
	$error_str .= make_hash_row($clan_member);
}
$error_str .= "</table>";
$error_str .= "<br />";

// little code to allow users to sort planets asc, desc in a number of criteria
if($sort_planets){
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	db(__FILE__,__LINE__,"select owner_name,planet_name,location,fighters,colon,cash,tech,metal,fuel,elect,organ,darkmatter "
		. "from ${db_name}_planets where clan_id = $user[clan_id] and location != 1 order by '$sort_planets' $going");
} else {
	db(__FILE__,__LINE__,"select owner_name,planet_name,location,fighters,colon,cash,tech,metal,fuel,elect,organ,darkmatter "
		. "from ${db_name}_planets where clan_id = $user[clan_id] and location != 1 order by owner_name asc, fighters desc, planet_name asc");
}

$clan_planet = dbr();
if($clan_planet) {
	if ($flag_dmatter) {
		$error_str .= make_table(array("<a href=$filename?sort_planets=owner_name&sorted=$sorted>Planet Owner</a>",
			"<a href=$filename?sort_planets=planet_name&sorted=$sorted>Planet Name</a>",
			"<a href=$filename?sort_planets=location&sorted=$sorted>Location</a>",
			"<a href=$filename?sort_planets=fighters&sorted=$sorted>Fighters</a>",
			"<a href=$filename?sort_planets=colon&sorted=$sorted>Colonists</a>",
			"<a href=$filename?sort_planets=cash&sorted=$sorted>Cash</a>",
			"<a href=$filename?sort_planets=tech&sorted=$sorted>Tech</a>",
			"<a href=$filename?sort_planets=metal&sorted=$sorted>Metal</a>",
			"<a href=$filename?sort_planets=fuel&sorted=$sorted>Fuel</a>",
			"<a href=$filename?sort_planets=elect&sorted=$sorted>Electronics</a>",
			"<a href=$filename?sort_planets=organ&sorted=$sorted>Organics</a>",
			"<a href=$filename?sort_planets=darkmatter&sorted=$sorted>Darkmatter</a>"));
	} else {
		$error_str .= make_table(array("<a href=$filename?sort_planets=owner_name&sorted=$sorted>Planet Owner</a>",
			"<a href=$filename?sort_planets=planet_name&sorted=$sorted>Planet Name</a>",
			"<a href=$filename?sort_planets=location&sorted=$sorted>Location</a>",
			"<a href=$filename?sort_planets=fighters&sorted=$sorted>Fighters</a>",
			"<a href=$filename?sort_planets=colon&sorted=$sorted>Colonists</a>",
			"<a href=$filename?sort_planets=cash&sorted=$sorted>Cash</a>",
			"<a href=$filename?sort_planets=tech&sorted=$sorted>Tech</a>",
			"<a href=$filename?sort_planets=metal&sorted=$sorted>Metal</a>",
			"<a href=$filename?sort_planets=fuel&sorted=$sorted>Fuel</a>",
			"<a href=$filename?sort_planets=elect&sorted=$sorted>Electronics</a>",
			"<a href=$filename?sort_planets=organ&sorted=$sorted>Organics</a>"));
	}
	while($clan_planet) {
		if (!$flag_dmatter) {
			unset( $clan_planet['darkmatter'] );
		}
		$clan_planet['owner_name'] = "<b class=b1>$clan_planet[owner_name]</b>";
		$error_str .= make_hash_row($clan_planet);
		$clan_planet = dbr();
	}
	$error_str .= "</table><br />";
}


update_clans();
// show all ships, not just other clan members.
if($user_options['show_clan_ships'] || $show_clan_ships){

	// determine if users want to see the abbreviation or not of ship types..
	if($user_options['show_abbr_ship_class'] == 1){ #abbriviate class names
		$class_temp_var = "class_name_abbr";
	} else {
		$class_temp_var = "class_name";
	}

	// little to allow users to list the ships by different means, even asc and desc.
	if($sort_ships){
		if($sorted_ships==1){
			$going = "asc";
			$sorted_ships=2;
		} else {
			$going = "desc";
			$sorted_ships=1;
		}
		db(__FILE__,__LINE__,"select login_name,ship_name,$class_temp_var,location,fighters,shields from ${db_name}_ships where clan_id = '$user[clan_id]' order by '$sort_ships' $going");
	} else {
		db(__FILE__,__LINE__,"select login_name,ship_name,$class_temp_var,location,fighters,shields from ${db_name}_ships where clan_id = '$user[clan_id]' order by login_name asc, fighters desc, ship_name asc");
	}
	$clan_ship = dbr();

	$error_str .= make_table(array("<a href=$filename?sort_ships=login_name&sorted_ships=$sorted_ships&show_clan_ships=1>Ship Owner</a>","<a href=$filename?sort_ships=ship_name&sorted_ships=$sorted_ships&show_clan_ships=1>Ship Name</a>","<a href=$filename?sort_ships=class_name&sorted_ships=$sorted_ships&show_clan_ships=1>Ship Class</a>","<a href=$filename?sort_ships=location&sorted_ships=$sorted_ships&show_clan_ships=1>Location</a>","<a href=$filename?sort_ships=fighters&sorted_ships=$sorted_ships&show_clan_ships=1>Fighters</a>","<a href=$filename?sort_ships=shields&sorted_ships=$sorted_ships&show_clan_ships=1>Shields</a>"));
	while($clan_ship) {
		$clan_ship['login_name']="<b class=b1>$clan_ship[login_name]</b>";
		$error_str .= make_hash_row($clan_ship);
		$clan_ship = dbr();
	}
	$error_str .= "</table><p>";
} else {
	db(__FILE__,__LINE__,"select login_name, count(ship_id) as total, sum(fighters) as fighters from ${db_name}_ships where clan_id = $user[clan_id] group by login_id order by fighters desc, ship_name desc");
	$clan_ship = dbr();
	$error_str .= "<br /><br /><a href=clan.php?show_clan_ships=1>Show All Clan Ships</a><p>";
	while($clan_ship){
		$error_str .= "<b class=b1>$clan_ship[login_name]</b> has <b>$clan_ship[total]</b> Ship(s) w/ <b>$clan_ship[fighters]</b> Total Fighters<br />";
		$clan_ship = dbr();
	}
	$error_str .= "<br /><br />";
}

$error_str .= "<a href=clan.php?ranking=1>Clan Rankings</a>";
$error_str .= "<br /><a href=clan.php?clan_info=1&target=$user[clan_id]>Clan Information</a><br /><br />";

if($user['login_id'] == $clan['leader_id'] || $user['login_id'] == 1) {
		$error_str .= "<a href=clan.php?changepass=1>Change Clan Password</a><br />";
		$error_str .= "<a href=player_relations.php?relations=1>Enter Clan Relations</a><br />";

	if($clan['members'] >1) {
		$error_str .= "<a href=clan.php?lead_change=1>Change Clan Leader</a><br />";
		$error_str .= "<a href=message.php?target=-2&clan_id=$user[clan_id]>Message Clan</a><br />";
	}
	if($user['login_id'] ==1 && $user['login_id'] != $clan['leader_id']) {
		$error_str .= "<a href=clan.php?leave=1>Leave Clan</a><br />";
	}
	$error_str .= "<a href=clan.php?disband=1>Disband Clan</a><br />";
} else {
	if($clan['members'] >1) {
		$error_str .= "<a href=message.php?target=-2&clan_id=$user[clan_id]>Message Clan</a><br />";
	}
	if($user['login_id'] ==1 || $user['login_id'] != $clan['leader_id']) {
		$error_str .= "<a href=clan.php?leave=1>Leave Clan</a><br />";
	}
}
print_page("Clan",$error_str);
?>
