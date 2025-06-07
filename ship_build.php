<?php
/*
//
File:			ship_build.php
Objective:		Modified version with single focus on delivering players first ship at start of game
Version:		0.71 (QS 2.0.8b)
Author:			Solarempire Developers - [modified by: Maugrim The Reaper]
Date Committed:	1999+		Date Modified:	30 December 2003

Copyright (c) 1999 - 2004 by Solarempire OpenSource Project, Pádraic Brady and Individual Authors

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.

Please note that Solarempire Files from other sources are most likely under Public Domain - this does not apply to any
files packaged with Quantum Star SE. All QS files are released under the GPL.
//
*/

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));
$homeworld = "Earth";

db(__FILE__,__LINE__,"select count(ship_id) as shipsowned from ${db_name}_ships where login_id = '$user[login_id]'");
$numships = dbr();
if(!$numships['shipsowned'] && $sudden_death && $user['login_id'] != 1 && $user['last_login'] != 0) {
	print_page("Sudden Death","You have no ship, and this game is Sudden Death. As such you are out of the game.");
}
		db(__FILE__,__LINE__,"select * from ${db_name}_db_vars where name = 'max_races'");
		$mr = dbr();
		$max_races = $mr['value'];

if($user['location'] != $user['race']) {
	print_page("Error","You are unable to buy ships from here. Ships can only be brought from Earth (System #1) or a Blackmarket.");
}

$ship_types = load_ship_types();

if($mass){
	$ship_stats = $ship_types[$mass];
	$take_flag = 1;
} else {
	$ship_stats = $ship_types[$ship_type];
	$take_flag = 1;
}


if(!$ship_stats && $user['game_login_count'] != '0'){
	print_page("Error","Admin has set the game up so as that ship is not available for purchase.");
}

if(!isset($ship_stats['config'])) {
	$ship_stats['config'] = "";
}

//build users first ship
if(($user['ship_id'] == 1) && ($user['game_login_count'] == '0')) {
	if(!$start_ship) {
		$start_ship['0'] = 4;
	}

		db(__FILE__,__LINE__,"select * from ship_types where type_id = '$start_ship'");

	$ship_stats = dbr();

	//SetCookie("login_id",$_COOKIE['login_id'],time()+2592000);
	//SetCookie("session_id",$_POST['session_id'],0);
	//SetCookie("db_name",$_POST['db_name'],0);
	//$_SESSION[''] = ;

	$ship_name = correct_name($ship_name);
	$ship_name = addslashes($ship_name);
	$fleet_name = correct_name($fleet_name);
	$fleet_name = addslashes($fleet_name);

db(__FILE__,__LINE__,"select * from ${db_name}_users where login_id = '$login_id'");
$bang = dbr();
$race = $bang['race'];


	//insert primary fleet for new player
	dbn(__FILE__,__LINE__,"insert into ${db_name}_fleets (fleet_name, fleet_num, login_id, login_name, location) values ('$fleet_name',1,'$login_id','$user[login_name]','$race')");
	db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where fleet_name = '$fleet_name' && login_id = '$login_id'");
	$flt = dbr();
		//print_array($flt); die; exit;
	$user_fleet['fleet_name'] = $fleet_name;
	$user_fleet['fleet_num'] = 1;
	$user_fleet['fleet_id'] = $flt['fleet_id'];

	// build the new ship
	$new_ship_id = Add_ShipToDatabase();
	dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '$race', ship_id = '$new_ship_id', game_login_count = game_login_count + 1 where login_id = '$user[login_id]'");

//	dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '2', ship_id = '$new_ship_id', game_login_count = game_login_count + 1 where login_id = '$user[login_id]'");
	$user['ship_id'] = $new_ship_id;
	db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
	$user_ship = dbr();

	$new_ship_config = get_config_var($ship_stats['config']);


	if($new_ship_config['oo']) {
		if($user['one_brob']){
			dbn(__FILE__,__LINE__,"update ${db_name}_users set one_brob = one_brob + one_brob where login_id = '$user[login_id]'");
		} else {
			dbn(__FILE__,__LINE__,"update ${db_name}_users set one_brob = 2 where login_id = '$user[login_id]'");
		}
	}
	$rs = "<p><a href=location.php>Click To Play</a>";
	if($p_user['num_games_joined'] <= 1){ //first game played on this server
		empty_bays();
		print_page("First Ship Brought","The paperwork is completed, and the ship is now yours.<br /><br />Seeing as this is your first game on this server, maybe you'll want to take a look at the <a href=help.php?started=1 target=_blank>Getting Started</a> section of the Help (link will open a new window).<br /><br /><b>Hint:</b> You can access that, and all other aspects of Help for the game by clicking the <b class=b1>Help</b> link thats in the left column.");
	} else {
		print_page("First Ship Brought","The paperwork is completed, and the ship is now yours.<br />Have a nice game.");
	}
}

/*function Add_ShipToDatabase() {
	global $ship_name, $login_id, $user, $ship_stats, $user_fleet, $db_name;
			$q_string = "insert into ${db_name}_ships (";
			$q_string = $q_string . "ship_name,login_id,login_name,clan_id,location ,ship_categ,type,shipclass,class_name,class_name_abbr,fighters,max_fighters,max_shields,cargo_bays,mine_rate,config,size,upgrades,move_turn_cost,point_value,num_ot,num_dt,shield_1,shield_2,shield_3,shield_4,shield_5,shield_charge,max_charge,shield_prts,fleet_id";
			$q_string = $q_string . ") values (";
			$q_string = $q_string . "'$ship_name','$login_id','$user[login_name]','$user[clan_id]','2','$ship_stats[ship_categ]','$ship_stats[type]','$ship_stats[type_id]','$ship_stats[name]','$ship_stats[class_abbr]','$ship_stats[fighters]','$ship_stats[max_fighters]','$ship_stats[max_shields]','$ship_stats[cargo_bays]','$ship_stats[mine_rate]','$ship_stats[config]','$ship_stats[size]','$ship_stats[upgrades]','$ship_stats[move_turn_cost]','$ship_stats[point_value]','$ship_stats[num_ot]','$ship_stats[num_dt]','$ship_stats[shield_1]','$ship_stats[shield_2]','$ship_stats[shield_3]','$ship_stats[shield_4]','$ship_stats[shield_5]','$ship_stats[shield_charge]','$ship_stats[max_charge]','$ship_stats[shield_prts]','$user_fleet[fleet_id]')";
			dbn(__FILE__,__LINE__,$q_string);

			$new_ship_id = db_insert_id();

			Add_ShipUpgrades($new_ship_id, $ship_stats);

			return $new_ship_id;
}*/
function Add_ShipToDatabase() {
	global $ship_name, $login_id, $user, $ship_stats, $user_fleet, $db_name, $race;
			$q_string = "insert into ${db_name}_ships (";
			$q_string = $q_string . "ship_name,login_id,login_name,clan_id,location,ship_categ,type,shipclass,class_name,class_name_abbr,fighters,max_fighters,max_shields,cargo_bays,mine_rate,mine_rate_fuel,mine_rate_metal,mine_rate_darkmatter,config,size,upgrades,move_turn_cost,point_value,num_ot,num_dt,shield_1,shield_2,shield_3,shield_4,shield_5,shield_charge,max_charge,shield_prts,fleet_id";
			$q_string = $q_string . ") values (";
			$q_string = $q_string . "'$ship_name','$login_id','$user[login_name]','$user[clan_id]','$race','$ship_stats[ship_categ]','$ship_stats[type]','$ship_stats[type_id]','$ship_stats[name]','$ship_stats[class_abbr]','$ship_stats[fighters]','$ship_stats[max_fighters]','$ship_stats[max_shields]','$ship_stats[cargo_bays]','$ship_stats[mine_rate]','$ship_stats[mine_rate_fuel]','$ship_stats[mine_rate_metal]','$ship_stats[mine_rate_darkmatter]','$ship_stats[config]','$ship_stats[size]','$ship_stats[upgrades]','$ship_stats[move_turn_cost]','$ship_stats[point_value]','$ship_stats[num_ot]','$ship_stats[num_dt]','$ship_stats[shield_1]','$ship_stats[shield_2]','$ship_stats[shield_3]','$ship_stats[shield_4]','$ship_stats[shield_5]','$ship_stats[shield_charge]','$ship_stats[max_charge]','$ship_stats[shield_prts]','$user_fleet[fleet_id]')";
			dbn(__FILE__,__LINE__,$q_string);

			$new_ship_id = db_insert_id();

			Add_ShipUpgrades($new_ship_id, $ship_stats);

			return $new_ship_id;
}

// function which will generate a list of all available upgrades (including plugins) and apply any default
// values for a newly purchased ship

function Add_ShipUpgrades($new_ship_id, $ship_stats)
{
	global	$db_name, $login_id;
	dbn(__FILE__,__LINE__,"insert into ${db_name}_upgrade_units (ship_id, login_id, fleet_id) values ('$new_ship_id','$login_id','0')");
	db(__FILE__,__LINE__,"select sql_name from upgrade_list where type != 1 AND type != 4 AND type != 5");
	$upg = dbr();
	foreach($upg as $key=>$val)
	{
		db(__FILE__,__LINE__,"select $val from default_upgrade_loads where ship_id = '$ship_stats[type_id]'");
		$defaults = dbr();
		if($defaults[$val] != 0)
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_upgrade_units set $val = '$defaults[$val]' where ship_id = $new_ship_id");
		}
	}
}
/*db2(__FILE__,__LINE__,"select race from ${db_name}_users where login_id = $user[login_id]");
$race = dbr2;
$rac = $race['race'];
if($rac == 1){
$loc = 1;
}
elseif($rac == 2){
$loc = 2;
}else{
$loc = 5;
}*/
/*dbn(__FILE__,__LINE__,"update ${db_name}_ships set location = '$loc' where ship_id = $new_ship_id");
dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '15' where login_id = $user[login_id]");
dbn(__FILE__,__LINE__,"update ${db_name}_fleets set location = '$loc' where fleet_id = $user_fleet[fleet_id]");*/

//	$user_fleet['fleet_id'] = $flt['fleet_id'];
?>
