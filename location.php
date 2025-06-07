<?php
include_once("includes/nocache.inc.php");
$filename = "location.php";

/*
//
File:			location.php
Objective:		Main script to handle the general star system home page
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	12 October 2003		Date Modified:	18 December 2003

Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/


require_once("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

include_once("includes/location_funcs.inc.php");
include_once("includes/location.inc.php");

get_star();

$user_ship_config = get_config($user['ship_id']);

$header = "Star System";

sudden_death_check($user);


// Check for on_planet and unset
if(isset($user['on_planet']))
{
	dbn(__FILE__,__LINE__,"update ${db_name}_users set on_planet = 0 where login_id = $user[login_id]");
	$user['on_planet'] = 0;
}

//subspace jump

// subspace scheduled for a rewrite on 1 February 2004 - added functions will account for Fleets and remove defunct references to Towing. By adding the menu to the location screen once a Fleet containing a ship with SubSpace Jump capability is being commanded, it will no longer be necessary to directly command the vessel - should reduce the need to continually switch ship commands.

// point in ref: require thumbnail size ship images for ship-in-command info box - also immediate custom popup with any ships stats. ships to be added to a linkable command popup window - rather than a direct listing in location screen - should help loading times and maintain player focus on fleets while providing a more fleet focused style of viewing a ship listing.

// convert location.php to xhtml standard - also make RG off compliant
// -30 January 04 - add quotes per xhtml standard to prevent misreading by IIS of apparent ASP style % > closing tag in table width values.

if(isset($subspace)) {
	$num_towed_t = array();
	db2(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where location = '$user[location]' && login_id = '$user[login_id]' && fleet_link = '$user_fleet[fleet_id]'");

	while($linked_fleets = dbr2()) {
		db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where fleet_id = '$linked_fleets[fleet_id]' && location = '$user_ship[location]' && ship_id != '$user[ship_id]'");///ANOTHER BUG ON LIMITS
		$num_towedl = dbr();

		$num_towed_t[] = $num_towedl['0'];
	}
	db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where fleet_id = '$user_fleet[fleet_id]' && location = '$user_ship[location]' && ship_id != '$user[ship_id]'");///ANOTHER BUG ON LIMITS
	$num_towedl = dbr();
	$num_towed_t[] = $num_towedl['0'];

	$num_towed = array_sum($num_towed_t);

	$query_temp = "select * from ${db_name}_planets where fighter_set = 2 && fighters > 0 && owner_id != '$user[login_id]' && (clan_id != '$user[clan_id]' && clan_id != 0) && location = '$subspace' order by fighter_set desc, fighters desc limit 1";

        db(__FILE__,__LINE__,$query_temp);$hostiles=dbr();

	db(__FILE__,__LINE__,"select count(star_id) from ${db_name}_stars where star_id > 0");
	$num_ss = dbr();
	$turn = get_star_dist($user['location'],$subspace)/2 +1;
	if(!($user_ship_config['sj'] || $user['login_id']==1)) {
		print_page("Sub-Space","This does not have a Sub-Space Jump Drive.");
	} elseif($subspace == $user['location']) {
		$error_str = "You're already there!";
	} elseif($user['turns'] < $turn) {
		print_page("Sub-Space","You need <b>$turn</b> turns to get that far.");
	} elseif($num_towed > 10 && !($user_ship_config['ws'] || $user['login_id']==1)) {
		print_page("Sub-Space","You can only transport <b>10 </b>ships through subspace, there are <b>$num_towed</b> ships in your fleet.<br /><br />To have unlimited transport capability, purchase and install the <b class=b1>Wormhole Stabiliser</b> Upgrade.");
	} elseif($subspace > $num_ss['0'] || $subspace <= 0 && $flag_nether_reg == 0) {/////
		print_page("Sub-Space","Where are you trying to go? That location doesn't exist. You can only go from Star Systems <b>#1</b> to <b>#$num_ss[0] </b>using any form of transport.");
	} elseif($subspace < 0 && $flag_nether_reg < 0 && $user['location'] > 0 || $subspace > 0 && $flag_nether_reg < 0 && $user['location'] < 0) {/////
		print_page("Sub-Space","Your Sub-Space abilities cannot breach the divide between different universes.");
	} elseif($subspace == 0) {/////
		print_page("Sub-Space","Where are you trying to go? That location doesn't exist. You can only go from Star Systems <b>#1</b> to <b>#$num_ss[0] </b>using any form of transport in a Universe.");
	} elseif($hostiles[fighter_set] == 2){
		print_page("Sub-Space","That System is blocking your access through sub space jumps. you'll have to go there by warping...");
	} else {
		dbn(__FILE__,__LINE__,"update ${db_name}_users set location = $subspace, last_ss = $user[location] where login_id = $user[login_id]");
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set location = $subspace, mine_mode = 0 where ship_id = $user[ship_id]");

		$user_ship['mine_mode'] = 0;
		if($user_ship['ship_id']) {

			dbn(__FILE__,__LINE__,"update ${db_name}_ships set location = '$subspace',mine_mode = '0' where fleet_id = '$user_ship[fleet_id]' && location = '$user[location]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_fleets set fleet_link = 0, location = '$subspace' where fleet_id = '$user_fleet[fleet_id]'");
			db(__FILE__,__LINE__,"select * from ${db_name}_fleets where location = '$user[location]' && login_id = '$user[login_id]' && fleet_link = '$user_fleet[fleet_id]'");
			$fleet_mv = dbr();
			while($fleet_mv) {
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set location = '$subspace',mine_mode = '0' where fleet_id = '$fleet_mv[fleet_id]' && location = '$user[location]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_fleets set location = '$subspace' where fleet_id = '$fleet_mv[fleet_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_fleets set fleet_link = 0 where fleet_link = '$fleet_mv[fleet_id]'");
				$fleet_mv = dbr();
			}


			charge_turns($turn);
			dbn(__FILE__,__LINE__,"update ${db_name}_users set location = $subspace, last_ss = $user[location] where login_id = $user[login_id]");
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set location = $subspace,mine_mode = 0 where ship_id = $user[ship_id]");

			$user['location'] = $subspace;
			$user_ship['location'] = $subspace;
			get_star();

//			hornet_mines();

			//random event stuff
			if ($star['event_random'] > 0 && $user['login_id'] != 1 && $user['ship_id'] > 1) {
				require("includes/random_events.inc.php");
				random_event_checker($star,$user,$autowarp);
			}
		}
	}
}



// Process page location command if given
if(isset($toloc)) {

	// checks
	if($ship_warp_cost < 1){ //warp cost is determined by largest ship in fleet.
		if($user['ship_id'] == 1){ //ship destroyed warp cost in turns.
			$warp_cost = 1;
		} else {
			$warp_cost_t = array();
			db2(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where location = '$user[location]' && login_id = '$user[login_id]' && fleet_link = '$user_fleet[fleet_id]'");

			while($linked_fleets = dbr2()) {
				db(__FILE__,__LINE__,"select move_turn_cost from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && (fleet_id = '$linked_fleets[fleet_id]' || ship_id = '$user[ship_id]') order by move_turn_cost desc limit 1");
				$move_turn_cost_fleet = dbr();
				$warp_cost_t[] = $move_turn_cost_fleet['move_turn_cost']; //set it to warp_cost so can keep generic
			}
			db(__FILE__,__LINE__,"select move_turn_cost from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && (fleet_id = '$user_ship[fleet_id]' || ship_id = '$user[ship_id]') order by move_turn_cost desc limit 1");
			$move_turn_cost_fleet = dbr();
			$warp_cost_t[] = $move_turn_cost_fleet['move_turn_cost']; //set it to warp_cost so can keep generic
			array_multisort($warp_cost_t, SORT_DESC, SORT_NUMERIC);
			$warp_cost = $warp_cost_t[0];
		}
	} else {//warp cost is set by admin
		$warp_cost = $ship_warp_cost; //set to warp_cost so as to keep generic
	}

	$query_temp = "select * from ${db_name}_planets where fighter_set = 2 && fighters > 0 && owner_id != '$user[login_id]' && (clan_id != '$user[clan_id]' && clan_id != 0) && location = '$user[location]' order by fighter_set desc, fighters desc limit 1";
        db(__FILE__,__LINE__,$query_temp);$hostiles=dbr();
	db(__FILE__,__LINE__,"select last_ss from ${db_name}_users where login_id = $user[login_id]");$lastloc = dbr();
	// When last_ss is zero, the user just switched command and last_ss was cleared to zero
	if ($lastloc[0] < 1) {
		$lastloc[0] = $star['link_1'];
	}


	if($user['turns'] < $ship_warp_cost && $ship_warp_cost > 0 && $user['login_id'] > 1) {
		$error_str = "Sorry, you can't move because you have less than <b>$ship_warp_cost</b> turn(s). <br />This is the present turn cost to move between systems, as set by the <b class=b1>Admin</b>.<p>";
	} elseif($ship_warp_cost < 1 && $user['turns'] < $warp_cost && $user['login_id'] > 1) {
		$error_str = "Sorry, you can't move because you have less than <b>$warp_cost</b> turn(s).<br />This is the amount of turns required to move the largest ship in your present fleet.<br />Different ships use different amounts of turns to move between systems. See the help for more information.";
	} elseif($hostiles[fighter_set] == 2 and $toloc != $lastloc[0] and find_designation_id($hostiles[clan_id]) != 3){
		$error_str = "Sorry, but you can only move out of this system to " . $lastloc[0];
	} else {

		$ship_config = get_config($user['ship_id']);
		if($toloc == $star['wormhole']) {
			if($star['art_wh'] < 0) {// protect artificial wormholes from invasion
				if($user['clan_id'] == 0 && $star['wh_login'] != $user['login_id']) {
					$error_str = "It appears that this wormhole is artificial. The <b class=b1>Wormhole Generator</b> controlling it refuses to stabilise the wormhole. You are unable to enter.";
					print_page("Blocked Wormhole",$error_str);
				} elseif($user['clan_id'] != 0 && $star['wh_clanid'] != $user['clan_id']) {
					$error_str = "It appears that this wormhole is artificial. The <b class=b1>Wormhole Generator</b> controlling it refuses to stabilise the wormhole. You are unable to enter.";
					print_page("Blocked Wormhole",$error_str);
				}
			}
			$query_temp_3 = attack_planet_check($db_name,$user);
			db2(__FILE__,__LINE__,$query_temp_3);
			$hostile_sys = dbr2();
		}

		if(!empty($hostile_sys)){
			$error_str .= "It is not possible to get to the wormhole to jump to that system, because the hostile fighters in this system are occupying your arrival site.";
		} elseif($toloc < 0 && $flag_nether_reg == 0) {
			$error_str = "That system does not exist.<p>";
		} elseif($toloc == 0) {
			$error_str = "That system does not exist.<p>";
		} elseif($toloc == $user['location']) {
			$error_str = "You are already there.<p>";
		} elseif(search_links($star,$toloc)) {
			$error_str = "This star system does not have a link to (#<b>$toloc</b>).<p>";
		} else {

			// ------------------------------------------------------------------------------------------------
			// Enable detect_mines() to check system before leaving it, will prevent the move if clusters found
			// ------------------------------------------------------------------------------------------------
			$test101 = round(mt_rand(1,1000));
			if(($flag_bmrkt == 1) && ($flag_mines == 1) && ($user['login_id'] != 1) && ($user['turns_run'] > $turns_before_attack) && ((eregi("gs",$user_ship['config'])) && ($test101 >= 601)) || ((!eregi("gs",$user_ship['config'])) && ($test101 >= 401)) || ((eregi("mw",$user_ship['config'])) && ($test101 >= 701))) {
				detect_mines();//check location for mines (assumed detected before leaving)
			}
			// ------------------------------------------------------------------------------------------------

			charge_turns($warp_cost);

			dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '$toloc', last_ss = $user[location] where login_id = '$user[login_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set location = '$toloc',mine_mode = '0' where ship_id = '$user[ship_id]'");
			$user_ship['mine_mode'] = 0;
			$user_ship['location'] = $toloc;


			if(isset($user_ship['ship_id'])) {

				dbn(__FILE__,__LINE__,"update ${db_name}_ships set location = '$toloc',mine_mode = '0' where fleet_id = '$user_ship[fleet_id]' && location = '$user[location]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_fleets set fleet_link = 0, location = '$toloc' where fleet_id = '$user_fleet[fleet_id]'");
				db(__FILE__,__LINE__,"select * from ${db_name}_fleets where location = '$user[location]' && login_id = '$user[login_id]' && fleet_link = '$user_fleet[fleet_id]'");
				$fleet_mv = dbr();
				while($fleet_mv) {
					dbn(__FILE__,__LINE__,"update ${db_name}_ships set location = '$toloc',mine_mode = '0' where fleet_id = '$fleet_mv[fleet_id]' && location = '$user[location]'");
					dbn(__FILE__,__LINE__,"update ${db_name}_fleets set location = '$toloc' where fleet_id = '$fleet_mv[fleet_id]'");
					dbn(__FILE__,__LINE__,"update ${db_name}_fleets set fleet_link = 0 where fleet_link = '$fleet_mv[fleet_id]'");
					$fleet_mv = dbr();
				}
				$user['location'] = $toloc;

			}
			get_star();

			// not quite finished on ramscooping - at the moment only scoops fuel
			$chk_scoops = Ramscoop();
			if($chk_scoops == 1)
			{
				charge_turns(4); //cost additional 4 turns to move while ramscooping
			}
			// end of ram-scooping check

			//random event stuff
			if ($star['event_random'] > 0 && $user['login_id'] != 1 && $user['ship_id'] > 1) {
				require("includes/random_events.inc.php");
				random_event_checker($star,$user,$autowarp);
			}

			// ------------------------------------------------------
			// Enable detect_mines() to check system upon entering it
			// ----------------------------------------------------------------------------
			$test202 = round(mt_rand(1,1000));
			if(($flag_bmrkt == 1) && ($flag_mines == 1) && ($user['login_id'] != 1)  && ($user['turns_run'] > $turns_before_attack) && ((eregi("gs",$user_ship['config'])) && ($test202 >= 601)) || ((!eregi("gs",$user_ship['config'])) && ($test202 >= 401)) || ((eregi("mw",$user_ship['config'])) && ($test202 >= 701))) {
				detect_mines();//check location for mines (assumed detected before leaving)
			}
			// ----------------------------------------------------------------------------

		}
	}
}


//random event stuff:
if ($star['event_random'] > 0) {
if ($star['event_random'] == 1) {
	$random_str = "<font color=#ff0000><div align=\"center\"><p>There is a <b class=b1>Black Hole</b> in this system.</p>";
	if ($user['login_id'] == 1) {
		$random_str .= "<p>Your status as Admin has prevented you from falling into it.</p>";
	} else {
		$random_str .= "<p><b>You are not allowed to be here!</b></p>";
	}
	$random_str .= "</div></font>";
	$header = "Black Hole";
} elseif ($star['event_random'] == 2) {
	$random_str = "<font color=#00aaaa><div align=\"center\"><p>There is a <b class=b1>Nebula</b> in this system. <a href=help.php?random=1 target=_blank>(help)</a>";
	$random_str .= "<br />Mining of <b>fuel</b> in this system is <b class=b1>tripled</b> (*3).";
	$random_str .= "<br /><b class=b1>Warning! </b>Shields have been <b class=b1>drained</b> and ships left here <b class=b1>may</b> be damaged. <b class=b1>Warning!</div></b></font>";
	$header = "Nebula";
} elseif($star['event_random'] == 4) {
	$random_str = "<div align=\"center\"><br />This system has a recently discovered <b class=b1>metal rich deposit </b>in it.";
	$random_str .= "<p>Mining of <b>metal </b>in this system is <b class=b1>quadrupled</b> (*4).</div>";
	$header = "Metal rush";
} elseif($star['event_random'] == 5) {
	$random_str = "<div align=\"center\"><br />This system is going to go <b class=b1>SuperNova </b>some time soon.";
	$random_str .= "<p><b class=b1>Warning! Warning! </b>";
	$random_str .= "<br />Staying in this system could prove disastrous. When the star in this system explodes, <b class=b1> EVERYTHING</b> in the system will be destroyed.<br />";
	$random_str .= "<b class=b1>Warning! Warning!</div></b><br />";
	$header = "SuperNova";
} elseif($star['event_random'] == 6) {
	$random_str = "<div align=\"center\"><p>This system has recently gone SuperNova. The Supernova remnant is very rich in minerals.";
	$random_str .= "<br /><b class=b1>Warning! Warning! </b>";
	$random_str .= "<br />Mining of <b class=b1>Metal and Fuel </b>in this system is much faster. <b class=b1>However</b>, this system <b class=b1>may</b> shortly turn into a <b class=b1>black hole</b>. There's no saying if/when though. <br />Mine at your own risk.<br />";
	$random_str .= "<b class=b1>Warning! Warning!</div></b><br />";
	$header = "SuperNova Remnant";
} elseif($star['event_random'] == 14) {
	$random_str = "<div align=\"center\"><br />This system has gone SuperNova. The Supernova Remnant is very rich in minerals.";
	$random_str .= "<br />The <font color=lime>- - - Science Institute of Sol - - -</font> have deemed this system safe, and say it will <b class=b1>not</b> turn into a BlackHole.</div>";
	$header = "Safe SuperNova Remnant";
} elseif($star['event_random'] == 10) {
	$random_str .= "<div align=\"center\"><br /><b class=b1>Warning! Warning! </b>";
	$random_str .= "<br />This is going to go SuperNova within <b class=b1>48</b> hours. <br />This is an artificially created SuperNova.<br />";
	$random_str .= "<b class=b1>Warning! Warning!</div></b>";
	$header = "Artificial SuperNova";
} elseif($star['event_random'] == 11) {
	$random_str .= "<div align=\"center\"><br /><b class=b1>Warning! Warning! </b>";
	$random_str .= "<br />This is going to go SuperNova within <b class=b1>24</b> hours. <br />This is an artificially created SuperNova.<br />";
	$random_str .= "<b class=b1>Warning! Warning!</div></b><br />";
	$header = "Artificial SuperNova";
} elseif($star['event_random'] == 12) {
	$random_str .= "<div align=\"center\"><p><b class=b1>Warning! Warning! </b>";
	$random_str .= "<br />There is increased Solar Activity in this System, creating a Solar Storm.<br />This means that all shields on all ships are reduced to zero for the duration of the storm.<br />";
	$random_str .= "<b class=b1>Warning! Warning!</div></b><br />";
	$header = "Solar Storm";
} elseif($star['event_random'] == 7) {
	$random_str .= "<center><p><b class=b1>Warning! Warning! </b>";
	$random_str .= "<br>This is an Alien Controlled system, and as its their home-system, I don't think they'll take kindly to you being here.<br>";
	$random_str .= "<b class=b1>Warning! Warning!</center></b><p>";
	$header = "Alien Homeworld";
} elseif($star['event_random'] == 8) {
	$random_str .= "<center><p><b class=b1>Warning! Warning! </b>";
	$random_str .= "<br>This is an Alien Controlled system. It is one of their Colony Systems.<br>";
	$random_str .= "<b class=b1>Warning! Warning!</center></b><p>";
	$header = "Alien Colony";
} elseif($star['event_random'] == 9) {
	$random_str .= "<center><p><b class=b1>Warning! Warning! </b>";
	$random_str .= "<br>This is an Alien controlled system. It is one of their Frontier Systems.<br>";
	$random_str .= "<b class=b1>Warning! Warning!</center></b><p>";
	$header = "Alien Frontier";
}
}

if($hostile_planets == 1) { //only show a hostile planet page if allowed by Admin, otherwise all systems open.

// Attack Planets:
if($user['login_id'] != 1) {
	$query_temp = attack_planet_check($db_name,$user);
	db2(__FILE__,__LINE__,$query_temp);
	while($planet2 = dbr2()) {
		if($planet2['clan_id'] > 0) {
			db(__FILE__,__LINE__,"select symbol,sym_color from ${db_name}_clans where clan_id = '$planet2[clan_id]'");
			$clan_sym = dbr();
			$error_str .= "<br /><br />";
			if($user_options['show_pics']){
				if($planet['barren'] == 1){
				$type = "b";
				} else {
				$type = "p";
				}
				$error_str .= "<div align=\"center\"><img src=images/planets/$type".$planet2['planet_img'].".jpg border=0></div><br>";
			}
			$error_str .= "<div align=\"center\">This system is guarded by the <b>$planet2[fighters]</b> fighters of <b class=b1>$planet2[planet_name]</b> (<font color=$clan_sym[sym_color]>$clan_sym[symbol]</font>).";
		}else {
			$error_str .= "<br /><br />";
			if($user_options['show_pics']){
				if($planet['barren'] == 1){
				$type = "b";
				} else {
				$type = "p";
				}
				$error_str .= "<div align=\"center\"><img src=images/planets/$type".$planet2['planet_img'].".jpg border=0></div><br>";
			}
			$error_str .= "<div align=\"center\">This system is guarded by the <b>$planet2[fighters]</b> fighters of <b class=b1>$planet2[planet_name]</b>.";
		}

		$error_str .= "<p>";
		if($planet2['pass'] != '0') {
			$error_str .= "<a href=planet.php?planet_id=$planet2[planet_id]&want_access=1>Request Access</a>, ";
		}
		if($flag_planet_attack){
			if($user_ship_config['sv']) {
				$error_str .= "<a href=attack.php?quark=1&planet_num=$planet2[planet_id]>Fire Quark Displacer</a>, ";
			}
			elseif(isset($user_ship_config['sw']) && $enable_superweapons == 1) {
				$error_str .= "<a href=attack.php?terra=1&planet_num=$planet2[planet_id]>Fire Terra Maelstrom</a>, ";
			}
			elseif($user_ship_config['md']){
			$error_str .= "<a href=attack.php?driver=1&planet_num=$planet2[planet_id]>Launch Mass Driver</a>, ";
			}
						elseif($user_ship_config['ob']){
			$error_str .= "<a href=attack.php?orbit=1&planet_num=$planet2[planet_id]>Launch Mass Driver</a>, ";
			}
			$error_str .= "<a href=planet.php?planet_id=$planet2[planet_id]>Attack</a> or Run Away: ";
		} else {
			$error_str .= "Run Away: ";
		}
		if($user['sn_effect'] == 1) {
			$error_str .= "<p><a href=bombs.php?sn_effect=1>Use SuperNova Effector</a><p>";
		}

		// open list
		$error_str .= " |";
		if($planet2[fighter_set] == 1 or(find_designation_id($planet2[clan_id]) == 3 and $planet2[fighter_set] == 2))
			{
		print_link($star['link_1']);
		print_link($star['link_2']);
		print_link($star['link_3']);
		print_link2($star['link_4']);
		print_link($star['link_5']);
		print_link($star['link_6']);
			}
		elseif(find_designation_id($planet2[clan_id]) != 3)
			{
			db(__FILE__,__LINE__,"select last_ss from ${db_name}_users where login_id = '$user[login_id]'");$dbr=dbr();
			// When last_ss is zero, the user just switched command and last_ss was cleared to zero
			print_link($dbr[0] > 0 ? $dbr[0] : $star['link_1']);
			}
			if($autowarp) {
				$path_str = str_replace("+", " ", $autowarp);
				$autowarp_path = array();
				$autowarp_path = explode(" ", $path_str);
				//$error_str .= "<br />Path_str is $path_str";
				$next_sector = array_shift($autowarp_path);
				//$error_str .= " Next Sector is $next_sector";
				if($next_sector && ($next_sector == $star['link_1'] || $next_sector == $star['link_2'] || $next_sector == $star['link_3'] || $next_sector == $star['link_4'] || $next_sector == $star['link_5'] || $next_sector == $star['link_6'])) {
					$temp328 = implode($autowarp_path, "\x2B");
					if($temp328){
						$error_str .= "<br />AutoWarp to Next System: &lt;<a href=location.php?toloc=$next_sector&autowarp=$temp328>$next_sector</a>&gt;";
					} else {
						$error_str .= "<br />AutoWarp to Next System: &lt;<a href=location.php?toloc=$next_sector>$next_sector</a>&gt;";
					}
				}
			}

		$error_str .= "</div>";
		$error_str .= $random_str;
		$rs = "";
		print_page("Hostile Planet",$error_str);
	}
}

}//end hostile_planets if/else stat


if(isset($jettison)) {
	if($sure != 'yes') {
			get_var('Jettison Cargo','location.php','Are you sure you want to Jettison all Cargo in this ship?','sure','yes');
	} else {
		if($user_ship['colon'] > 0){
			$temp = round(rand(0,6));
			if($temp <= 1) {
				$extra_text = "<br />You merciless blighter. Those poor innocent (dead) colonists. What did they ever do to you?";
				$news_text_extra = "Without provocation, <b class=b1>$user[login_name]</b> jettisoned <b>$user_ship[colon]</b> colonists into open space.<br />Reason being: \"Planetlubbers the lot of em. I\'ve seen scurvy with more guts (and the guts of someone with scurvy)\"";
			} elseif($temp <= 2) {
				$extra_text = "<br />Thats just nasty. Those colonists didn't stand a chance";
				$news_text_extra = "Ghastly. <b class=b1>$user[login_name]</b> just jettisoned <b>$user_ship[colon]</b> colonists into deep space.<br />Explaination being \"Bloomin Unions.\"";
			} elseif($temp <= 3) {
				$extra_text = "<br />What will the relatives of those poor (dead) colonists think of you now?";
				$news_text_extra = "<b>$user_ship[colon]</b> (dead) colonists are now floating around in space courtesy of <b class=b1>$user[login_name]</b>.<br />When asked what happened: \"Aye laddy, it twas \'im *pointing to innocent cabin boy*\"";
			} elseif($temp <= 4) {
				$extra_text = "<br />You gonna go out there are sweep up that mess of (dead) colonists you just	made?<br />Thought not.";
				$news_text_extra = "<b class=b1>$user[login_name]</b> just brutally murdered <b>$user_ship[colon]</b> colonists by jettisoning them into space. <br />The excuse: \"But they were already dead guvn\'r\"";
			} elseif($temp <= 5) {
				$extra_text = "<br />Its a good thing your crew is as heartless as you are. Slaughtering colonists like that indeed.";
				$news_text_extra = "No heart <b class=b1>$user[login_name]</b> just lived up to the name by deciding to (and actually doing it too!) jettison <b>$user_ship[colon]</b> colonists into open space. When interviewed: \"I\'m not heartless. Just coldly calculating.\"";
			} elseif($temp <= 6) {
				$extra_text = "<br />You evil dimwit! Killing penniless souls like that has booked you a seat on the next shuttle-bus to HELL! You\'re just lucky your crew had already pre-booked...";
				$news_text_extra = "<b class=b1>$user[login_name]</b> The Terrible has jetissoned <b>$user_ship[colon]</b> colonists into the endless cold of interstellar space. In an intercepted radio transmission he stated that \"every one of them fools had it coming. Took my manservant hours to clean the Persian Rug in my cabin, not to mention the Italian marble tiles. Well, let\'s see them take a leak out there! Citizen protest indeed...I gave them enough mouldy bread to feed an army. The thanks I get...\"";
			}
			dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'$news_text_extra','1')");
//			post_news();
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set metal=0, fuel=0, elect=0, organ=0, colon=0, darkmatter=0 where ship_id = $user_ship[ship_id]");
		$user_ship['metal'] = 0;
		$user_ship['fuel'] = 0;
		$user_ship['elect'] = 0;
		$user_ship['organ'] = 0;
		$user_ship['colon'] = 0;
		$user_ship['darkmatter'] = 0;
		$user_ship['empty_bays'] = empty_bays();
		$error_str .= "Cargo Jettisoned. $extra_text<p>";
	}
}


// This is the start of all output for the location.php page. Note much content is now outsourced to
// location_funcs.inc.php in functions.


unset($rs);

//echo '<div align=\"center\">';
//echo '<table width=100% border=1><tr><td valign=top>';

// --------------------------------------------------------
// display planet limit for SS, star name, warp links, etc.
// --------------------------------------------------------

// get the $star array for this location
get_star();


$error_str = "";

$sys_type_array = GrabSystemType($star['sys_type']);

if($star[event_random] == 1 && $user['login_id'] != 1){
$bh_text .= "<br />
<table cellspacing=\"0\" cellpadding=\"2\" width=\"100%\" class=\"all\" align=\"center\">
	<tr>
		<td style=\"text-align: center;\" rowspan=\"2\">
			<img src=\"images/sys_type/blackhole.gif\" border=\"0\" width=\"65\" height=\"65\" alt=\"".$sys_type_array['name']."\" title=\"".$sys_type_array['name_v']."\">
		</td>
		<td style=\"text-align: center;\">
				<span class=\"h5\">
						Star System (#$user[location])
					<br /><b class=\"b1\">
						$star[star_name]
					</b>
				</span><br /><br />
";
	$bh_text .= "<p>Warning! Warning! System <b>$star[star_id]</b> had a Black Hole in it!<br>";

// print the out the warp links
$bh_text .= "Warp: ";
$bh_text .= "|";
			if($star[link_1]) {
				db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_1]");
				$new_star = dbr3();
				$link_num2 = $new_star['star_name'];
				$bh_text .= " <a href=location.php?toloc=$star[link_1]>(SS #$star[link_1]) - $link_num2</a> |";
			}
			if($star[link_2]) {
				db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_2]");
				$new_star = dbr3();
				$link_num2 = $new_star['star_name'];
				$bh_text .= " <a href=location.php?toloc=$star[link_2]>(SS #$star[link_2]) - $link_num2</a> |";			}
			if($star[link_3]) {
				db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_3]");
				$new_star = dbr3();
				$link_num2 = $new_star['star_name'];
				$bh_text .= " <a href=location.php?toloc=$star[link_3]>(SS #$star[link_3]) - $link_num2</a> |";			}
			if($star[link_4]) {
				db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_4]");
				$new_star = dbr3();
				$link_num2 = $new_star['star_name'];
				$bh_text .= "<br>| <a href=location.php?toloc=$star[link_4]>(SS #$star[link_4]) - $link_num2</a> |";			}
			if($star[link_5]) {
				db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_5]");
				$new_star = dbr3();
				$link_num2 = $new_star['star_name'];
				$bh_text .= " <a href=location.php?toloc=$star[link_5]>(SS #$star[link_5]) - $link_num2</a> |";			}
			if($star[link_6]) {
				db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_6]");
				$new_star = dbr3();
				$link_num2 = $new_star['star_name'];
				$bh_text .= " <a href=location.php?toloc=$star[link_6]>(SS #$star[link_6]) - $link_num2</a> |";			}
if(!isset($autowarp))
{
	$bh_text .= "<br />AutoWarp Failure";
}
else
{
	$bh_text .= "<br />Autowarp Failure";
}


if($star['wormhole'])
{
	$bh_text .= "<br />Wormhole to : Star Chart Failure
		</td>
	</tr>
</table>";
}
else
{
	$bh_text .= "
		</td>
	</tr>
</table>";
$bh_text .= "<br>Failure of Scanner, nothing can be detected in this system";
}
$error_str = $bh_text;
} else {

$error_str .= "<br />
<table cellspacing=\"0\" cellpadding=\"2\" class=\"all\" width=\"100%\" align=\"center\">
	<tr>
		<td style=\"text-align: center;\" rowspan=\"2\">
			<img src=\"images/sys_type/sys_type_".$star['sys_type'].".png\" border=\"0\" width=\"65\" height=\"65\" alt=\"".$sys_type_array['name']."\" title=\"".$sys_type_array['name_v']."\">
		</td>
		<td style=\"text-align: center;\">
				<span class=\"h5\">
						Star System (#$user[location])
					<br /><b class=\"b1\">
						$star[star_name]
					</b>
				</span><br /><br />
";

if($user['location'] == 0)
{
	$error_str .= "<a href=\"location.php?sszero=1\">Leave Star System Zero</a><br />";
}

// print the out the warp links
$error_str .= "Warp: ";
$error_str .= "|";
print_link($star['link_1']);
print_link($star['link_2']);
print_link($star['link_3']);
print_link2($star['link_4']);
print_link($star['link_5']);
print_link($star['link_6']);

if(!isset($autowarp))
{
	$error_str .= "<br />| <a href=\"autowarp.php\">Set AutoWarp</a> |";
}
else
{
	$error_str .= "<br />| <a href=\"autowarp.php\">Set New Autowarp</a> |";
}

if(isset($autowarp))
{
	$path_str = str_replace("+", " ", $autowarp);
	$autowarp_path = array();
	$autowarp_path = explode(" ", $path_str);
	//$error_str .= "<br />Path_str is $path_str";
	$next_sector = array_shift($autowarp_path);
	//$error_str .= " Next Sector is $next_sector";
	if($next_sector == $star['link_1'] || $next_sector == $star['link_2'] || $next_sector ==
		$star['link_3'] || $next_sector == $star['link_4'] || $next_sector == $star['link_5'] || $next_sector == $star['link_6'])
	{
		$autowarp = implode($autowarp_path, "\x2B");
		if($autowarp)
		{
		db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $next_sector");
		$new_star = dbr3();
		$next_sector2 = $new_star['star_name'];

		$error_str .= "<br />| AutoWarp to Next System:<a href=\"location.php?toloc=$next_sector&autowarp=$autowarp\"> SS #$next_sector - $next_sector2</a>&nbsp;&nbsp;|";

		}
		else
		{
		db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $next_sector");
		$new_star = dbr3();
		$next_sector2 = $new_star['star_name'];
		$error_str .= "<br />| AutoWarp to Next System:<a href=\"location.php?toloc=$next_sector\"> SS #$next_sector - $next_sector2</a>&nbsp;&nbsp;|";
		}
	}
}




if($star['wormhole'])
{
	$error_str .= "<br />Wormhole to : <a href=\"location.php?toloc=$star[wormhole]\">$star[wormhole]</a>
		</td>
	</tr>
</table>";
}
else
{
	$error_str .= "
		</td>
	</tr>
</table>";
}









//print mining/post/blackmarket/random event information if applicable
$system_inf = SystemInfo();
$planet_inf = array();

$planet_inf['temp2'] .= $system_inf['fac'];
$error_str .= $system_inf['str'];

//////// defines certain areas ear-marked for change, often defunct in QS 2.2B
db(__FILE__,__LINE__,"select * from ${db_name}_powerups where location = '$user[location]'");
$dbr = dbr();
if(!empty($dbr))
        {
        $error_str .= "<center>A few objects have been found in this secter, however,<br>one of them could be worth looking at.<br><a href=powerup.php?id=$dbr[id]>Gather up the object?</a></center>";
        }

if(isset($random_str)) { ////////// no if else previously
	$error_str .= $random_str; //display random events info
}
$planet_tab = 0;
// print planets - $planet_inf array declared after call to SystemInfo()
db(__FILE__,__LINE__,"select * from ${db_name}_planets where location = '$user[location]' order by planet_name asc, fighters desc");
while($planets = dbr())
{
	$planet_text = PlanetInfo($planets);
	$planet_inf['temp2'] .= $planet_text['temp2'];
	$planet_inf['temp'] .= $planet_text['temp'];
	$planet_tab++;
	}
//determine if user has any planets in the system
if(isset($planet_inf['temp2']))
{
	$planet_str .= $planet_inf['temp2'];
	if($planet_inf['temp'])
	{
		$planet_str .= $planet_inf['temp'];
	}
}
elseif (isset($planet_inf['temp']))
{
	$planet_str .= $planet_inf['temp'];
}

unset($planet_inf);

if(isset($planet_str)) //create table set if planets exist
{
	$error_str .= "<br /><div align=\"center\"><table cellspacing=\"0\" cellpadding=\"2\" style=\"border: 1\" width=\"100%\" align=\"center\">
		<tr>";
$error_str .= "<td width=\"100%\">
				<table cellspacing=\"0\" cellpadding=\"2\" style=\"border: 5px\" width=\"100%\" align=\"center\">
					";
$error_str .= "<tr>".$planet_str."</tr>
				</table>
			</td>";
$error_str .= "		</tr>
	</table>
	</div>";
	}










/*
// Section where ship listings are prepared and printed
// Ship listings are created using four List functions in location_funcs.inc.php
// All tables are structured so formatting preserved in source html page code
*/


$error_str .= "
<br />
<table cellspacing=\"0\" cellpadding=\"2\" class=\"all\" width=\"100%\" align=\"center\">
	<tr>
		<th colspan=\"2\">
			Fleet Ships
		</th>
	</tr>
	<tr>
		<td width=\"100%\">
			<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
				<tr>
					<td nowrap colspan=\"10\">
";

// count all ships
db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '$user[login_id]' and location='$user[location]'");
$count=dbr();
// count only BM Miners (for Ramscooping) - this is now deprecated 17/4/2004
db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '$user[login_id]' and location='$user[location]' && (shipclass = 301 || shipclass = 302)");
$countr = dbr();

$error_str .= "
						<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" width=\"100%\">
							<tr>
								<td colspan=\"10\">
									You have $count[0] ship(s) in this system.<br />
								</td>
							</tr>
						</table>
					</td>
				</tr>
";




// Full listing mode is practically deprecated at this stage - it's also the source of much of the scaleability issues
// with a large player base - from the summary listing, player can call up a fleet listing showing all ships instead.
// But it revert back to summary with next refresh. It's simply not worth keeping as an option - too resource hungry.

// reset default listing mode
if($user['show_user_ships'] == 1)
{
	dbn(__FILE__,__LINE__,"update ${db_name}_users set show_user_ships = 0 where login_id = '$user[login_id]'");
	$user['show_user_ships'] = 0;
}
if($user['show_enemy_ships'] == 1)
{
	dbn(__FILE__,__LINE__,"update ${db_name}_users set show_enemy_ships = 0 where login_id = '$user[login_id]'");
	$user['show_enemy_ships'] = 0;
}


/* HANDLE USER SHIPS */

// Enables switching between full and summary list modes
if (isset($_GET['show_user_ships']) && $_GET['show_user_ships'] == 1)
{
	$user['show_user_ships'] = 1;
	dbn(__FILE__,__LINE__,"update ${db_name}_users set show_user_ships = 1 where login_id = '$user[login_id]'");
}
elseif (isset($_GET['show_user_ships']) && $_GET['show_user_ships'] == 2)
{
	$user['show_user_ships'] = 0;
	dbn(__FILE__,__LINE__,"update ${db_name}_users set show_user_ships = 0 where login_id = '$user[login_id]'");
}

/* SHOW FULL LIST OF USER SHIPS */
if($user['show_user_ships'] == 1)
{
	db2(__FILE__,__LINE__,"select ship_id,ship_name,class_name,class_name_abbr,config,fighters,fleet_id from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && ship_id != '$user[ship_id]' && ship_id > 1 order by fleet_id,fighters desc,ship_name asc");
	$ships = dbr2();
	if(empty($ships))
	{
		$error_str .= "
				<tr>
					<td>
						<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" width=\"100%\">
							<tr>
								<td>
									<i>You're presently in command of your only ship in this system.</i>
								</td>
							</tr>
		";
	}
	else
	{
		// set the mode option value for full vs summary
		$r_option .= "<a href=\"location.php?show_user_ships=2\">Show Summary</a>";

		//set up display for fleet driven system
		$fleet_name_array = array();

		db(__FILE__,__LINE__,"select * from ${db_name}_fleets where login_id = '$user[login_id]'");
		$all_fleets = dbr();
		while($all_fleets) {
			$fleet_name_array[$all_fleets['fleet_id']] = "Fleet: ".$all_fleets['fleet_name'];
			$all_fleets = dbr();
		}
		$br_txt = "<br />";


		$error_str .= "
				<tr>
					<td>
						<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" width=\"100%\">
		";
		//Loop through all of a players ships in the system.
		while($ships){
			//insert all rows <tr></tr>
			$error_str .= UserShip_FullList($ships);
			$ships = dbr2();
		}
	} // end of looping through all of players' ships.

	unset($ships);

/* SHOW SUMMARY OF USER SHIPS */
} else {

	// updated QS 2.1.30 summary listing by fleet w/ popup ship listing for each
	// by default you have at least one fleet at all times - except when dead :)
	$r_option = "";
	$r_option .= "<a href=\"location.php?show_user_ships=1\">Show All</a>";
	$cloaked_ships = 0;
	$cloaked_figs = 0;
	$error_str .= "
				<tr>
					<td colspan=\"10\">
						<span class=\"red_txt\">Summary of Fleets in Star System #".$user['location'].":</span>
						<br />
					</td>
				</tr>
				<tr>
	";
	$error_str .= "
					<td>
						<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" width=\"100%\">
	";
	db2(__FILE__,__LINE__,"select * from ${db_name}_fleets where location = '$user[location]' and login_id = '$user[login_id]' order by fleet_num asc");
	while($all_fleets = dbr2()) {
		$error_str .= UserShip_SummaryList($all_fleets);
	}
}


/* HANDLE Enemy SHIPS */
if (isset($_GET['show_enemy_ships']) && $_GET['show_enemy_ships'] == 1)
{
	$user['show_enemy_ships'] = 1;
	dbn(__FILE__,__LINE__,"update ${db_name}_users set show_enemy_ships = 1 where login_id = '$user[login_id]'");
}
elseif (isset($_GET['show_enemy_ships']) && $_GET['show_enemy_ships'] == 2)
{
	$user['show_enemy_ships'] = 0;
	dbn(__FILE__,__LINE__,"update ${db_name}_users set show_enemy_ships = 0 where login_id = '$user[login_id]'");
}

$error_str .= "
						</table>
					</td>
				</tr>
			</table>
		</td>
		<td valign=\"top\" align=\"right\" width=\"5%\">
			".$r_option."
		</td>
	</tr>
</table>


<br />


<table cellspacing=\"0\" cellpadding=\"2\" class=\"all\" width=\"100%\" align=\"center\">
	<tr>
		<th colspan=\"8\">
			Enemy/Other Ships
		</th>
	</tr>
	<tr>
		<td width=\"100%\">
";

/* SHOW FULL LIST OF ENEMY SHIPS */
if($user['show_enemy_ships'] == 1){
	db(__FILE__,__LINE__,"select count(ship_id) as total from ${db_name}_ships where location = '$user[location]' and login_id != '$user[login_id]'");
	$shp_count = dbr();
	if(eregi("rd",$user_ship['config'])) { //raiders will show weakest ships at top to allow easier raiding
		db2(__FILE__,__LINE__,"select s.ship_id, s.ship_name, s.login_id, s.fighters, s.class_name,s.class_name_abbr, s.size, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.turns_run,s.fleet_id from ${db_name}_ships s, ${db_name}_users u where s.location = '$user[location]' and s.ship_id > 1 and s.login_id = u.login_id && s.login_id != '$user[login_id]' order by s.fleet_id, s.fighters asc,s.login_name,s.ship_name");
	} else {
		db2(__FILE__,__LINE__,"select s.ship_id, s.ship_name, s.login_id, s.fighters, s.class_name,s.class_name_abbr, s.size, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.turns_run,s.fleet_id from ${db_name}_ships s, ${db_name}_users u where s.location = '$user[location]' and s.ship_id > 1 and s.login_id = u.login_id && s.login_id != '$user[login_id]' order by s.fleet_id, s.fighters desc,s.login_name,s.ship_name");
	}

	$ships = dbr2();

	if($user['turns_run'] < $turns_before_attack && $user['login_id'] != 1){
		$cannot_attack = 0;
	} else {
		$cannot_attack = 1;
	}

	//there are other ships in the system
	if (isset($ships)) {
		$r2_option .= "<a href=\"location.php?show_enemy_ships=2\">Show Summary</a>";

		$list_str .= "
			<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" width=\"100%\">
		";
		$cloak_count = 0;
		$ship_vis_count = 0;
		//unset($marg_clk);
		//unset($marg_cnt);
		//loop through other players ships.
		while($ships)
		{
			if($visibility_flag == 1)
			{
				//echo("SHIP STATES ".$fleet_result[$ships['fleet_id']]);
				if($fleet_result[$ships['fleet_id']] == "YES")
				{
					$list_str .= EnemyShip_FullList($ships);
					$ship_vis_count = $ship_vis_count + 1;
				}
				else
				{
					$cloak_count = $cloak_count + 1;
				}
			}
			else
			{
				$list_str .= EnemyShip_FullList($ships);
				$ship_vis_count = $ship_vis_count + 1;
			}
			$ships = dbr2();
		}
		unset($ships);
		$sum_text = "There are $ship_vis_count detected ship(s) in this system.<br /><br />";
		$error_str .= $sum_text.$list_str;

		if(isset($cloak_count) && (mt_rand(0,100) >= 75)) {
			$chance_meet = $star['sys_type'] * 25;
			$chance_meet = round(100 - $chance_meet + mt_rand(-5, 0));
			$error_str .= "<tr><td colspan=\"10\" align=\"center\"><br /><b class=\"cloak\">::::: Possible Detection of Cloaked Vessels in System ($chance_meet%) :::::</b></td></tr>";
		}
		$error_str .= "
			</table>
		";
	}
	else
	{
		$error_str .= ""; //there are no other ships in this system
	}
/* SHOW SUMMARY OF ENEMY SHIPS	*/
} else {
	$r2_option = "";
	$r2_option .= "<a href=\"location.php?show_enemy_ships=1\">Show All</a>";
	$error_str .= "
			<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" width=\"100%\">
				<tr>
					<td colspan=\"10\">
						<span class=\"bold1\">Summary of Enemy/Other Fleets in Star System #".$user['location'].":</span>
						<br />
					</td>
				</tr>
				<tr>
	";
	$error_str .= "
					<td>
						<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" width=\"100%\">
	";
	db2(__FILE__,__LINE__,"select * from ${db_name}_fleets where location = '$user[location]' and login_id != '$user[login_id]' order by login_name asc, fleet_name asc");
	while($enemy_fleets = dbr2()) {
		db3(__FILE__,__LINE__,"select login_id, turns_run, clan_id from ${db_name}_users where login_id = '$enemy_fleets[login_id]'");
		$enemy = dbr3();
		$error_str .= EnemyShip_SummaryList($enemy_fleets, $enemy);
	}
}

// set right panel and contents for enemy ships panel
// need to make one set of table tags optional - the full
// listing uses a slightly different table structure
if($user['show_enemy_ships'] == 0)
{
	$error_str .= "
						</table>
					</td>
				</tr>
			</table>
	";
}
$error_str .= "
		</td>
		<td valign=\"top\" align=\"right\" width=\"5%\">
			".$r2_option."
		</td>
	</tr>
</table>
";
}








































































//Last 5 News Headlines (if enabled)
$error_str .= Display_News($show_news);

//what's this for - Not a clue! - "If it's not broken, don't fix it..."
$error_str .= "<p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p>";










































////////////////////////////////////////////////////////////////////////////////////////////////
/*
// This section closes the mid-page column and opens a new column for the Right-Hand Menu Column
*/

$error_str .= "
</td>
<td width=\"20\">
</td>
<td width=\"202\" valign=\"top\">
";

////////////////////////////////////////////////////////////////////////////////////////////////


$error_str .= "<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" class=\"all\" width=\"200\">";
$error_str .= "<tr><th>Universe Map</th></tr>";



if($user_options['show_minimap']){
	$error_str .= "<tr><td border=\"0\"><a href=\"map.php\" target=\"_blank\"><img src=\"maps/$db_name/sm$user[location].png\" border=\"0\" width=\"196\" height=\"196\" alt=\"Map of systems around $user[location]\"></a></td></tr></table>";
} else {
	$error_str .= "<tr><td border=\"0\" style=\"text-align: center\"><a href=\"map.php\" target=\"_new\">Full Universe Map</a></td></tr></table>";
}


// print fleet links mini-box
$error_str .= Print_FleetLinks();

$weap_str = "";
if($user['genesis'] > 0) {
	$weap_str .= "<tr><td width=\"50%\" nowrap>Genesis Device (<b>$user[genesis]</b>)</td><td width=\"50%\" align=\"right\"><a href=\"planet_build.php?location=$user[location]\">Deploy</a></td></tr>";
}
if($user['alpha'] > 0) {
	$weap_str .= "<tr><td width=\"50%\" nowrap>Alpha Bomb (<b>$user[alpha]</b>)</td><td width=\"50%\" align=\"right\"><a href=\"bombs.php?alpha=1\">Deploy</a></td></tr>";
}
if($user['gamma'] > 0) {
	$weap_str .= "<tr><td width=\"50%\" nowrap>Gamma Bomb (<b>$user[gamma]</b>)</td><td width=\"50%\" align=\"right\"><a href=\"bombs.php?bomb_type=1\">Deploy</a></td></tr>";
}
if($user['delta'] > 0) {
	$weap_str .= "<tr><td width=\"50%\" nowrap>Delta Bomb (<b>$user[delta]</b>)</td><td width=\"50%\" align=\"right\"><a href=\"bombs.php?bomb_type=2\">Deploy</a></td></tr>";
}
if($user['sn_effect'] > 0) {
	$weap_str .= "<tr><td width=\"50%\" nowrap>SuperNova Effector</td><td width=\"50%\" align=\"right\"><a href=\"bombs.php?sn_effect=1\">Deploy</a></td></tr>";
}


if((($user['hornet_mine'] > 0) && ($flag_mines == 1) && ($flag_research == 1) && (eregi("ml",$user_ship['config']))) || $user['login_id'] == 1){
	$weap_str .= "<tr><td width=\"50%\" nowrap>Hornet Mines (<b>$user[hornet_mine]</b>)</td><td width=\"50%\" align=\"right\"><a href=\"ai_bombs.php?m_type=2\">Deploy</a></td></tr>";
} elseif ($user['hornet_mine'] > 0 && $flag_mines == 1 && $flag_research == 1) {
	$weap_str .= "<tr><td colspan=\"2\"><b class=red_txt>You own</b> <b>$user[hornet_mine]</b> <b class=\"red_txt\">Hornet Mines</b></td></tr>";
}
if((($user['grav_mine'] > 0) && ($flag_mines == 1) && ($flag_research == 1) && (eregi("ml",$user_ship['config']))) || $user['login_id'] == 1){
	$weap_str .= "<tr><td width=\"50%\" nowrap>Graviton Mines (<b>$user[grav_mine]</b>)</td><td width=\"50%\" align=\"right\"><a href=\"ai_bombs.php?m_type=1\">Deploy</a></td></tr>";
} elseif ($user['grav_mine'] > 0 && $flag_mines == 1 && $flag_research == 1) {
	$weap_str .= "<tr><td colspan=\"2\"><b class=\"red_txt\">You own</b> <b>$user[grav_mine]</b> <b class=\"red_txt\">Graviton Mines</b></td></tr>";
}
if($user['leech'] > 0){
	$weap_str .= "<tr><td width=50% nowrap>Leech (<b>$user[leech]</b>)</td><td width=50% align=right><a href=ai_bombs.php?m_type=3>Deploy</a></td></tr>";
}
if($user['terra_imploder'] > 0) {
	$weap_str .= "<tr><td colspan=\"2\">Terra Imploder (<b>$user[terra_imploder]</b>)</td></tr>";
}

if(isset($weap_str)) {
	$error_str .= "<br /><table cellspacing=\"0\" cellpadding=\"2\" class=\"all\" width=\"100%\"><tr><th colspan=\"2\">Weaponry</th></tr>".$weap_str."</table>";
}

$user_ship_config = get_config($user['ship_id']);

//if(isset($user_ship_config['tw'])) {
//	$error_str .= "<br /><table cellspacing=\"0\" cellpadding=\"2\" class=\"all\" width=\"100%\"><form name=\"transwarp_form\" action=\"location.php\" method=\"post\"><tr><th><a href=\"javascript:alert('Use Transwarp to travel a short distance across the universe without warp links. Limit of 15 light years.\Can tow unlimited number of ships however each additional ship towed adds one turn to Warp cost.\\\t\\t\\t\\tCost: 5+ turns')\">Transwarp Jump</a></th></tr><tr><td><a href=\"location.php?transburst=1\">Burst</a><br />Destination: <input type=\"text\" size=\"3\" maxlength=\"4\" name=\"transwarp\">&nbsp;<input type=\"submit\" value=\"Engage!\"></td></tr></form></table>";
//}

if(isset($user_ship_config['sj']) || $user['login_id']==1) {
	$error_str .= "
		<br />
		<form method=\"post\" action=\"location.php\" name=\"subspace_form\">
		<table cellspacing=\"0\" cellpadding=\"2\" class=\"all\" width=\"100%\">
			<tr>
				<th>
					<a href=\"javascript:alert('Use Sub-Space Jump to travel to anywhere in the Galaxy. Can tow only 10 ships unless a Wormhole Stabiliser is installed.\\\t\\tCost:10+ turns.')\">SubSpace Jump</a>
				</th>
			</tr>
			<tr>
				<td>
					Destination: <input type=\"text\" size=\"3\" maxlength=\"4\" name=\"subspace\" />&nbsp;<input type=\"submit\" name=\"submit\" value=\"Engage!\" />
				</td>
			</tr>
		</table>
		</form>
	";
}

/*//show top 5 players
$error_str .= "<br /><table cellspacing=\"0\" cellpadding=\"2\" class=\"all\" width=\"100%\"><tr><th colspan=\"5\">Top 5 Players</th></tr>";
db(__FILE__,__LINE__,"select cash,login_name,fighters_killed,ships_killed,ships_lost,turns_run,score,login_id from ${db_name}_users where login_id != 1 order by score desc, turns_run desc limit 5");
$player = dbr();
$error_str .= "<tr><td class=\"red_txt\">R</td><td class=\"top5plyrs\">Name</td><td class=\"top5plyrs\">Turns</td><td class=\"top5plyrs\">Score</td></tr>";
$rank=1;
while($player) {
	$error_str .= "<tr><td class=\"top_b\"><b>$rank</b></td><td class=\"top5\">".print_name($player)."</td><td class=\"top5\"><b>$player[turns_run]</b></td><td class=\"top5\"><b>$player[score]</b></td></tr>";
	$rank += 1;
	$player = dbr();
}
$error_str .= "</table>";*/
if(isset($jettison)) {
	if($sure != 'yes') {
			get_var('Jettison Cargo','location.php','Are you sure you want to Jettison all Cargo in this ship?','sure','yes');
	} else {
		if($user_ship['colon'] > 0){
			$temp = round(rand(0,6));
			if($temp <= 1) {
				$extra_text = "<br />You merciless blighter. Those poor innocent (dead) colonists. What did they ever do to you?";
				$news_text_extra = "Without provocation, <b class=b1>$user[login_name]</b> jettisoned <b>$user_ship[colon]</b> colonists into open space.<br />Reason being: \"Planetlubbers the lot of em. I\'ve seen scurvy with more guts (and the guts of someone with scurvy)\"";
			} elseif($temp <= 2) {
				$extra_text = "<br />Thats just nasty. Those colonists didn't stand a chance";
				$news_text_extra = "Ghastly. <b class=b1>$user[login_name]</b> just jettisoned <b>$user_ship[colon]</b> colonists into deep space.<br />Explaination being \"Bloomin Unions.\"";
			} elseif($temp <= 3) {
				$extra_text = "<br />What will the relatives of those poor (dead) colonists think of you now?";
				$news_text_extra = "<b>$user_ship[colon]</b> (dead) colonists are now floating around in space courtesy of <b class=b1>$user[login_name]</b>.<br />When asked what happened: \"Aye laddy, it twas \'im *pointing to innocent cabin boy*\"";
			} elseif($temp <= 4) {
				$extra_text = "<br />You gonna go out there are sweep up that mess of (dead) colonists you just	made?<br />Thought not.";
				$news_text_extra = "<b class=b1>$user[login_name]</b> just brutally murdered <b>$user_ship[colon]</b> colonists by jettisoning them into space. <br />The excuse: \"But they were already dead guvn\'r\"";
			} elseif($temp <= 5) {
				$extra_text = "<br />Its a good thing your crew is as heartless as you are. Slaughtering colonists like that indeed.";
				$news_text_extra = "No heart <b class=b1>$user[login_name]</b> just lived up to the name by deciding to (and actually doing it too!) jettison <b>$user_ship[colon]</b> colonists into open space. When interviewed: \"I\'m not heartless. Just coldly calculating.\"";
			} elseif($temp <= 6) {
				$extra_text = "<br />You evil dimwit! Killing penniless souls like that has booked you a seat on the next shuttle-bus to HELL! You\'re just lucky your crew had already pre-booked...";
				$news_text_extra = "<b class=b1>$user[login_name]</b> The Terrible has jetissoned <b>$user_ship[colon]</b> colonists into the endless cold of interstellar space. In an intercepted radio transmission he stated that \"every one of them fools had it coming. Took my manservant hours to clean the Persian Rug in my cabin, not to mention the Italian marble tiles. Well, let\'s see them take a leak out there! Citizen protest indeed...I gave them enough mouldy bread to feed an army. The thanks I get...\"";
			}
			post_news($news_text_extra);
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set metal=0, fuel=0, elect=0, organ=0, colon=0, darkmatter=0 where ship_id = $user_ship[ship_id]");
		$user_ship['metal'] = 0;
		$user_ship['fuel'] = 0;
		$user_ship['elect'] = 0;
		$user_ship['organ'] = 0;
		$user_ship['colon'] = 0;
		$user_ship['darkmatter'] = 0;
		$user_ship['empty_bays'] = empty_bays();
		$error_str .= "Cargo Jettisoned. $extra_text<p>";
	}
}

if($user_ship['empty_bays'] != $user_ship['cargo_bays']) {
$error_str .= "<br><table cellspacing=0 cellpadding=2 class=all width=100%><tr><th>Emergency Options</th></tr><tr><td><a href=location.php?jettison=1>Jettison Cargo</a></td></tr></table>";
}


$error_str .= '</td></tr></table></div>';

print_page("$header",$error_str);


?>
