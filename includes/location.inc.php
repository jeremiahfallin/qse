<?php
/*
//
File:			location.inc.php
Objective:		Contains all code blocks from location.php not specifically dealing with the layout or navigation
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	5 February 2004		Date Modified:	n/a

Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

// retire
if(isset($retire))
{
	if($sure != 'yes')
	{
		get_var('Retire','location.php','<p><b class=b1>Warning!</b> This will permanently remove your account from this game. You should also be aware that if this game is run using a rejoin-delay period, you will be unable to rejoin this game for a further 24hrs.<br /><br />Are you sure you want to retire?</p>','sure','yes');
	}
	else
	{
	if ($user['clan_id'] > 0)
	{
		db(__FILE__,__LINE__,"select leader_id,members from ${db_name}_clans where clan_id = $user[clan_id]");
		$clan = dbr();
		if($clan['members'] > 1 && $user['login_id'] == $clan['leader_id'] && !isset($what_to_do))
		{
				$new_page = "<p>Before you retire you must first select whether you want your clan to be disbanded, or assign a new leader to it:</p>";
				$new_page .= "<form action=\"location.php\" method=\"post\" name=\"retiring\">";
				while (list($var, $value) = each($_POST))
				{
					$new_page .= "<input type=\"hidden\" name=\"$var\" value=\"$value\">";
				}
				$new_page .= "<p>Disband Clan <input type=\"radio\" name=\"what_to_do\" value=\"1\" checked> / Assign New Clan Leader<input type=\"radio\" name=\"what_to_do\" value=\"2\"><br /><br /><input type=\"submit\" value=\"Submit!\"></form>";
				print_page("Retiring",$new_page);
		}
		elseif($clan['members'] < 2 || $what_to_do == 1)
		{

			dbn(__FILE__,__LINE__,"update ${db_name}_clans set members = members - 1 where clan_id = $user[clan_id]");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set clan_id = 0 where clan_id = $user[clan_id]");
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set clan_id = -1 where clan_id = $user[clan_id]");
			dbn(__FILE__,__LINE__,"delete from ${db_name}_clans where clan_id = $user[clan_id]");
			dbn(__FILE__,__LINE__,"delete from ${db_name}_messages where clan_id = $user[clan_id]");
			dbn(__FILE__,__LINE__,"update ${db_name}_mines set clan_id = 0 where clan_id = $user[clan_id]");//revert mines to individuals relation settings
		}
		elseif($what_to_do == 2 && !isset($leader_id))
		{
			$new_page = "<p>Please select which of the below you would like to be the new clan leader:</p>";
			$new_page .= "<form action=\"location.php\" method=\"post\" name=\"retiring2\">";

			db2(__FILE__,__LINE__,"select login_id,login_name from ${db_name}_users where clan_id = '$user[clan_id]' && login_id != '$user[login_id]'");
			$new_page .= "<select name=\"leader_id\">";
			while ($member_name = dbr2()) {
				$new_page .= "<option value=\"$member_name[login_id]\">$member_name[login_name]</option>";
			}
			$new_page .= "</select>";
			while (list($var, $value) = each($_POST)) {
				$new_page .= "<input type=\"hidden\" name=\"$var\" value=\"$value\" />";
			}
			$new_page .= "<br /><br /><input type=\"submit\" value=\"Submit!\" /></form>";
			print_page("Assign New Clan Leader",$new_page);
		}
		else
		{
				//dbn(__FILE__,__LINE__,"update ${db_name}_clans set leader_id = $leader_id where clan_id = $user['clan_id']");
		}
	}

	// note: for QS replace with print_page function !!!
	retire_user($user['login_id']);
	$rs = "<br /><br /><a href=\"game_listing.php\">Go to Game List</a>";
	dbn(__FILE__,__LINE__,"update ${db_name}_clans set members = members - 1 where clan_id = $user[clan_id]");
	print_header("Account Removed");
	insert_history($user['login_id'],"Retired From Game");
	echo "You have been removed from the Game.";
	print_footer();
	exit();
	}
}



//enables control over links between fleets, please note that once command is shifted to another fleet and that fleet moves all prior links with the new commanding fleet are removed
if(isset($_POST['chng_lnks']))
{
	if(isset($_POST['lnks']))
	{
		dbn(__FILE__,__LINE__,"update ${db_name}_fleets set fleet_link = 0 where fleet_link = '$user_fleet[fleet_id]' && login_id = '$user[login_id]' && location = '$user_ship[location]'");
		while ($var = each($_POST['lnks']))
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_fleets set fleet_link = '$user_fleet[fleet_id]' where fleet_id = '$var[value]' && location = '$user[location]'");
			// this confirmation will appear in upper right of mini fleet links box
			$lnk_confirm = "<tr><td align=\"right\"><i>Links Updated</i></td></tr>";
		}
	}
	else
	{
		dbn(__FILE__,__LINE__,"update ${db_name}_fleets set fleet_link = 0 where fleet_link = '$user_fleet[fleet_id]' && login_id = '$user[login_id]' && location = '$user_ship[location]'");
	}
}


// command a different ship

// 4-Feb-2004 : This code is now considered deprecated. In light of a focus shift from ships to fleets, a new fleet section for this purpose is available below. For compatibility reasons with as yet unchanged files - this functionality will remain available, though users are encouraged to use it as infrequently as possible
if(isset($_GET['command'])) {
	if($_GET['command'] == 0 || $_GET['command'] == 1)
	{
		print_page("Error","<p>It is not possible to command this ship.</p>");
	}
	db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$_GET[command]'");
	$temp_ship = dbr();
	if($temp_ship['login_id'] == $user['login_id'])
	{
		$error_str .= "<p>Command Transfered.</p>";
		dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$_GET[command]', location = '$temp_ship[location]', last_ss = '0' where login_id = '$user[login_id]'");
		$user['ship_id'] = $_GET['command'];
		//update data
		db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
		$user_ship = dbr();
		db(__FILE__,__LINE__,"select * from ${db_name}_fleets where fleet_id = '$user_ship[fleet_id]'");
		$user_fleet = dbr();
		$user['location'] = $temp_ship['location'];
		$user['last_ss'] = 0; // When switching command, last_ss isn't valid anymore
		get_star();
		$user_ship = $temp_ship;
		$user_ship['empty_bays'] = empty_bays();
		$user_ship_config = get_config($user['ship_id']);
	}
}


/* the update command code for the QS Fleet System - each fleet is designated a command vessel which the user may
command - command vessels may be set manually through each fleet listing, or set automatically when the command is
given. Once a command vessel is initially designated, it can only be changed manually. Automatic designations use the
ship with the most fighters in a fleet to pass command of the fleet to. Ship by Ship commanding - as exists in the
older SE code is still possible - but may be over-ridden by the auto designation inherent in more recent code, or not.
The uncertainty and possible conflict between the two systems means some support for the older SE code will persist
for a short intermediary period - though the urls for such will rarely be directly visible to the player.*/

if(isset($_GET['new_command']))
{
	db(__FILE__,__LINE__,"select fleet_id, ship_id, login_id, location from ${db_name}_fleets where fleet_id = '$_GET[new_command]'");
	$new_comm = dbr();
	if($new_comm['login_id'] != $user['login_id'])
	{
		print_page("Change Fleet Command Error","You do not appear to own this fleet, and so obviously you can't expect me to let you command it. I am an Articifial Intelligence, if you provide me with a new Athlon XP 2900 GHz processor, we may be able to come to some...mutually beneficial arrangement...otherwise quit playing with the url!");
	}
	elseif(empty($new_comm['ship_id']) || $new_comm['ship_id'] == 0)
	{
		db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where fleet_id = '$_GET[new_command]' order by fighters desc limit 1");
		$c_ship = dbr();
		if(!empty($c_ship))
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ship_id = '$c_ship[ship_id]' where fleet_id = '$_GET[new_command]'");
			$new_comm['ship_id'] = $c_ship['ship_id'];
			$new_comm['location'] = $c_ship['location'];
		}
		else
		{
			print_page("Change Fleet Command Error","The Fleet you wish to command does not appear to contain any ships! Please choose a valid Fleet to command.");
		}
	}
	dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$new_comm[ship_id]', location = '$new_comm[location]', last_ss = '0' where login_id = '$user[login_id]'");
	// update pre-fetched arrays from database
	// dev: this is pointless, need to wait for the March print function rewrite before ships are updated onscreen without the user requiring to reload the location page to check some bug hasn;t arisen...
	$user['ship_id'] = $new_comm['ship_id'];
	$user['location'] = $new_comm['location'];
	$user['last_ss'] = 0; // When switching command, last_ss isn't valid anymore
	get_star();
	db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$new_comm[ship_id]' and login_id = '$user[login_id]'");
	$user_ship = dbr();
	$user_ship['empty_bays'] = empty_bays();
	$user_ship_config = get_config($user['ship_id']);
	db(__FILE__,__LINE__,"select * from ${db_name}_fleets where fleet_id = '$new_comm[fleet_id]' and login_id = '$user[login_id]'");
	$user_fleet = dbr();
}



// the location.php mining code is now deprecated - see mining.php for the new location of the code

/*
if(isset($mine)) {

	$tempx9x = 1;
	db(__FILE__,__LINE__,"select fighter_set,owner_id,clan_id from ${db_name}_planets where location = '$user[location]'");
	while ($planets = dbr()) {
		if ($planets['fighter_set'] && ($user['login_id'] == $planets['owner_id'] || ($user['clan_id'] == $planets['clan_id'] && $user['clan_id'] > 0)) || ($user_ship_config['ps'])) {
			$tempx9x = 1;
		} elseif (!$planets['fighter_set']) {
			$tempx9x = 1;
		} else {
			$tempx9x = 0;
		}
	}

	if ($tempx9x != 1) {
			$error_str .= "It is not possible to mine in a system where the fighters are set to Hostile.<p>";
	} else {
		if(($mine == 1 && $user_ship['mine_rate_metal'] < 1 && $alternate_play_1 == 1) || ($mine == 1 && eregi("Ram-Scoop",$user_ship['class_name']))){
			$error_str .= "This ship cannot mine metal.";
			$user_ship['mine_mode'] = 0;
		} elseif(($mine == 2 && $user_ship['mine_rate_fuel'] < 1 && $alternate_play_1 == 1) || ($mine == 2 && eregi("Asteroid",$user_ship['class_name']))) {
			$error_str .= "This ship cannot mine fuel.";
			$user_ship['mine_mode'] = 0;
		} elseif($mine == 3 && $user_ship['mine_rate_darkmatter'] < 1) {
			$error_str .= "This ship cannot mine Dark-Matter.";
			$user_ship['mine_mode'] = 0;
		} elseif($user_ship['mine_rate_metal'] < 1 && $user_ship['mine_rate_fuel'] < 1 && $alternate_play_1 == 0){
			$error_str .= "This ship has no mining ability.";
			$user_ship['mine_mode'] = 0;
		} else {
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set mine_mode = '$mine' where ship_id = '$user[ship_id]'");
			$user_ship['mine_mode'] = $mine;
			$error_str .= "Ship Mining";
		}
	}
}

if(isset($mine_all)) {
	$tempx9x = 1;
	db(__FILE__,__LINE__,"select fighter_set,owner_id,clan_id from ${db_name}_planets where location = '$user[location]'");
	while ($planets = dbr()) {
		if ($planets['fighter_set'] && ($user['login_id'] == $planets['owner_id'] || ($user['clan_id'] == $planets['clan_id'] && $user['clan_id'] > 0))) {
			$tempx9x = 1;
		} elseif (!$planets['fighter_set']) {
			$tempx9x = 1;
		} else {
			$tempx9x = 0;
		}
	}

	if ($tempx9x != 1) {
			$error_str .= "It is not possible to mine in a system where the fighters are set to hostile.<p>";
	} else {
		if($alternate_play_1 == 1){ //alternate mining
			if($mine_all == 1){//metal
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set mine_mode = '$mine_all' where mine_rate_metal > 0 && (ship_id = $user[ship_id] || (login_id = '$user[login_id]' && location = '$user[location]'))");
			} elseif ($mine_all == 2) {//fuel
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set mine_mode = '$mine_all' where mine_rate_fuel > 0 && (ship_id = $user[ship_id] || (login_name = '$user[login_name]' && location = '$user[location]'))");
			} else {//dmatter
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set mine_mode = '$mine_all' where mine_rate_darkmatter > 0 && (ship_id = $user[ship_id] || (login_name = '$user[login_name]' && location = '$user[location]'))");
			}
		} else { //normal mining
			if($mine_all < 3) {
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set mine_mode = '$mine_all' where (mine_rate_metal > 0 || mine_rate_fuel > 0) && (ship_id = $user[ship_id] || (login_id = '$user[login_id]' && location = '$user[location]'))");
			} elseif($mine_all == 3) {
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set mine_mode = '$mine_all' where (mine_rate_darkmatter > 0) && (ship_id = $user[ship_id] || (login_id = '$user[login_id]' && location = '$user[location]'))");
			}

		}
		//mass mining (INCLUDE D-M)
		//--------------------------------------------------------------------------------------------------------
		// This section included (uncomment) if specific mining enabled for the twin BM miners as added to above
		//--------------------------------------------------------------------------------------------------------
		//if(($mine_all == 1 && eregi("Ram-Scoop",$user_ship['class_name'])) || ($mine_all == 2 && eregi("Asteroid",$user_ship['class_name'])) || ($mine_all == 3 && $user_ship['mine_rate_darkmatter'] <= 0)) {
			//$error_str .= "This ship has no mining ability of this kind, However any ships in this system that you own that can mine this material, are now mining.";
			//$user_ship['mine_mode'] = 0;
		//} else //link to if statement below (add as admin var later)
		//--------------------------------------------------------------------------------------------------------
		if((($user_ship['mine_rate_metal'] > 0) || ($user_ship['mine_rate_fuel'] > 0) && $mine_all && $alternate_play_1==0) || ($user_ship['mine_rate_metal'] > 0 && $mine_all == 1 && $alternate_play_1==1) ||($user_ship['mine_rate_fuel'] > 0 && $mine_all == 2 && $alternate_play_1==1) || ($user_ship['mine_rate_darkmatter'] > 0 && $mine_all == 3)){
			$user_ship['mine_mode'] = $mine_all;
			$error_str .= "Fleet Mining";
		} else {
			$error_str .= "This ship has no mining ability, However any ships in this system that you own that can mine, are now mining.";
			$user_ship['mine_mode'] = 0;
		}
	}
}
*/


if(isset($ramfleet)) {
	db(__FILE__,__LINE__,"select ramscoop from ${db_name}_fleets where fleet_id = '$ramfleet' and login_id = '$user[login_id]'");
	$rmscp = dbr();
	if(empty($rmscp))
	{
		print_page("Error!","<p>You cannot issue commands to fleets you do not own or control.</p>");
	}
	elseif($rmscp['ramscoop'] == 0)
	{
		dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ramscoop = 1 where fleet_id = '$ramfleet' and login_id = '$user[login_id]'");
	}
	elseif($rmscp['ramscoop'] == 1)
	{
		dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ramscoop = 0 where fleet_id = '$ramfleet' and login_id = '$user[login_id]'");
	}
}

//temporary bug fx introduced QS v2.0.9
if(isset($sszero)) {
	if($user['ship_id'] == 0) {
		db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where login_id = '$user[login_id]' && location != 0 LIMIT 1");
		$switch_ships = dbr();
		if(isset($switch_ships['ship_id'])) {
			db(__FILE__,__LINE__,"select location from ${db_name}_ships where ship_id = '$switch_ships[ship_id]'");
			$other_ship = dbr();
			dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$switch_ships[ship_id]', location = '$other_ship[location]' where login_id = '$user[login_id]'");
			print_page("Left SS0","You have been relocated to another ship in your fleet.");
		} else {
			create_escape_pod($user['login_id']);
			if($user['bounty'] > 0 && $alternate_bounty_sys == 1) {
				db(__FILE__,__LINE__,"select login_id,login_name from ${db_name}_users where login_name = '$user[last_attack_by]'");
				$winner = dbr();
				send_message($winner['login_id'],"You have claimed 50% of the <b>$user[bounty]</b> Credit bounty that was on <b class=b1>$user[login_name]</b>s head for destroying his/her fleet and consigning them to an <b class=b1>Escape Pod</b>. Unfortunately we had to wait until now since he was sent to Star System 0 and escaped our attentions.");
				post_news("50% of the <b>$user[bounty]</b> bounty on <b class=b1>$user[login_name]</b> has been claimed by <b class=b1>$winner[login_name]</b> for the destruction of their fleet. The player remains at large in an <b class=b1>Escape Pod</b> with the remaining bounty on his head.");
				$bounty_a = int($user['bounty'] * .5);
				dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + '$bounty_a' where login_id = '$winner[login_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = bounty - '$bounty_a' where login_id = '$user[login_id]'");
				print_page("Left SS0","It has been discovered that none of your ships remain and as such you have been consigned to an Escape Pod and conveyed to a random location.");
			}
		}
	}else{
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set location = 1 where ship_id = '$user[ship_id]'");
		dbn(__FILE__,__LINE__,"update ${db_name}_users set location = 1 where login_id = '$user[login_id]'");
		print_page("Leaving SS0","Your ship has been relocated to Star System #1.");
	}
}

/*DEV: another quick fix - delete all empty fleets of user - regardless of location
			db(__FILE__,__LINE__,"select count(fleet_id) from ${db_name}_fleets where login_id = '$user[login_id]'");
			while($_fleet = dbr()) {
			if($_fleet['fleet_id'] > 1){
				db2(__FILE__,__LINE__,"select count(ship_id) as num from ${db_name}_ships where fleet_id = '$_fleet[fleet_id]'");
				$chk_num = dbr2();
				if(empty($chk_num['num']) || $chk_num['num'] <= 0)
				{
					dbn(__FILE__,__LINE__,"delete from ${db_name}_fleets where fleet_id = '$_fleet[fleet_id]'");
				}
			}
			}

*/

?>