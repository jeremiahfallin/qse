<?php
/*
//
File:			ship_purchase_funcs.inc.php
Objective:		ship purchase functions - replaces ship_build.php/bm_ships.php purchase code
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	29 December 2003	Date Modified:	3 April 2004

Copyright (c) 2003, 2004 by Pádraic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

array_push($FILE_LIST, basename(__FILE__));

function Add_ShipToDatabase() {
	global $ship_name_sql, $login_id, $user, $ship_stats, $user_fleet, $db_name, $returntext;
			$q_string = "insert into ${db_name}_ships (";
			$q_string = $q_string . "ship_name, login_id, login_name, clan_id, location, ship_categ, type, shipclass, class_name, class_name_abbr, fighters, max_fighters, max_shields, cargo_bays, mine_rate_metal, mine_rate_fuel, mine_rate_darkmatter, mine_rate, config, size, upgrades, move_turn_cost, point_value, num_ot, num_dt, shield_1, shield_2, shield_3, shield_4, shield_5, shield_charge, max_charge, shield_prts, fleet_id, timestamp";
			$q_string = $q_string . ") values(";
			$q_string = $q_string . "'$ship_name_sql', '$user[login_id]', '$user[login_name]', '$user[clan_id]', '$user[location]', $ship_stats[ship_categ], '$ship_stats[type]', '$ship_stats[type_id]', '$ship_stats[name]', '$ship_stats[class_abbr]', '$ship_stats[fighters]', '$ship_stats[max_fighters]', '$ship_stats[max_shields]', '$ship_stats[cargo_bays]', '$ship_stats[mine_rate_metal]', '$ship_stats[mine_rate_fuel]', '$ship_stats[mine_rate_darkmatter]', '$ship_stats[mine_rate]', '$ship_stats[config]', '$ship_stats[size]', '$ship_stats[upgrades]', '$ship_stats[move_turn_cost]', $ship_stats[point_value], '$ship_stats[num_ot]', '$ship_stats[num_dt]', '$ship_stats[shield_1]', '$ship_stats[shield_2]', '$ship_stats[shield_3]', '$ship_stats[shield_4]', '$ship_stats[shield_5]', '$ship_stats[shield_charge]', '$ship_stats[max_charge]', '$ship_stats[shield_prts]', '$user_fleet[fleet_id]', '".time()."')";
			// echo $q_string;
			dbn(__FILE__,__LINE__,$q_string);

			db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where login_id = '$user[login_id]' order by timestamp desc, ship_id desc limit 1");
			$new_id = dbr();
			$new_ship_id = $new_id['ship_id'];

			Add_ShipUpgrades($new_ship_id, $ship_stats);

			//add bm visit to DB
			if($returntext['enable_bmfine_cnt'] == 1 && isset($returntext))
			{
				$penalty = (int)($ship_stats['max_fighters'] / 3000) * 4;
				bm_visit($penalty);
			}

			return $new_ship_id;
}

function Check_Shipyard($ship_stats) {
	global $user, $bmrkt_id, $asyrd_id, $db_name, $flag_research;
	$RS_text = array();


db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
$races2 = dbr();
$era = $races2['value'];
db3(__FILE__,__LINE__,"select * from races where race_ID = $user[race] && era = $era");
$home = dbr3();
$homeworld = $home['planet_name'];
db4(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
$mx = dbr4();
$max_races = $mx['value'];


		if($user['location'] == $user['race'] || ($user['login_id'] == 1 && $user['location'] <= $max_races))
		{
		$shipyard = 1;
		$RS_text['mass'] = "<p><a href=hw.php>Back to $homeworld.</a><br /><a href=hw.php?ship_shop=1>Return to Shipyard</a><br /><br /><a href=location.php>Return to Star System</a>";
		$RS_text['single'] = "<p><a href=hw.php?ship_shop=1>Return to Shipyard</a>";
		}
	elseif (isset($bmrkt_id))
	{
		$shipyard = 2;
		$RS_text['mass'] = "<p><a href=bm_ships.php?bmrkt_id=$bmrkt_id>Back to O'Reillys Shipbuilders.</a>";
		$RS_text['single'] = "<p><a href=bm_ships.php?bmrkt_id=$bmrkt_id>Return to O'Reillys Shipbuilders.</a>";
		$RS_text['enable_bmfine_cnt'] = 1;
		db(__FILE__,__LINE__,"select * from ${db_name}_bmrkt where location = '$user[location]' && bmrkt_id = '$bmrkt_id' order by bmrkt_type asc");
		$bmrkt = dbr();
		if (!$bmrkt)
		{
			print_page("Port","You may not contact a blackmarket that is not in the same system as you are in. Stop playing with the URL's'");
		}
		elseif($user['ship_id'] ==1 && $user['login_id'] != 1)
		{
			print_page("Error","You are unable to contact the blackmarket here, as you are not a real Starship Captain. You have no ship!");
		}
		elseif($flag_research == 0 && $user['login_id'] != 1)
		{
			print_page("Error","Admin has disabled blackmarkets for the duration of the current game.");
		}
	}
	elseif (isset($asyrd_id))
	{
		$shipyard = 3;
		$RS_text['mass'] = "<p><a href=shipyard.php?asyrd_id=$asyrd_id>Back to Alien Shipyards.</a>";
                $RS_text['single'] = "<p><a href=shipyard.php?asyrd_id=$asyrd_id>Return to Alien Shipyards.</a>";
                $RS_text['enable_bmfine_cnt'] = 1;
                db(__FILE__,__LINE__,"select * from ${db_name}_shipyards where location = '$user[location]' && asyrd_id = '$asyrd_id'");
                $asyrd = dbr();
                if (!$asyrd)
                {
                        print_page("Shipyard","You may not contact an Alien Shipyard that is not in the same system as you are in. Stop playing with the URL's'");
                }
                elseif($user['ship_id'] == 1 && $user['login_id'] != 1)
                {
                        print_page("Error","You are unable to contact a Alien Shipyard here, as you are not a real Starship Captain. You have no ship!");
                }
	}
	else
	{
		print_page("Illegal Request","It does not appear you are situated at Earth or a Blackmarket. For this reason buying a ship is impossible. Please note that URL tampering is considered cheating and reported instances will be investigated and appropriate action (like being banned!) will be taken by Admins.");
	}
	if($shipyard == 1 && ($ship_stats['purchase_loc'] == 2 || $ship_stats['purchase_loc'] == 3 || $ship_stats['purchase_loc'] == 5))
	{
		print_page("Illegal Request","The requested ship cannot be purchased from this Planet! Please note that URL tampering is considered cheating and reported instances will be investigated and appropriate action (like being banned!) will be taken by Admins.");
	}
	elseif($shipyard == 2 && ($ship_stats['purchase_loc'] == 1 || $ship_stats['purchase_loc'] == 3 || $ship_stats['purchase_loc'] == 6))
	{
		print_page("Illegal Request","The requested ship cannot be purchased from Blackmarkets! Please note that URL tampering is considered cheating and reported instances will be investigated and appropriate action (like being banned!) will be taken by Admins.");
	}


	// Alien Shipyard still to be added when enabled in code

	return $RS_text;
}


// function which will generate a list of all available upgrades (including plugins) and apply any default
// values for a newly purchased ship

function Add_ShipUpgrades($new_ship_id, $ship_stats)
{
	global	$db_name, $login_id;
	dbn(__FILE__,__LINE__,"insert into ${db_name}_upgrade_units (ship_id, login_id, fleet_id) values ('$new_ship_id','$login_id','0')");
	db(__FILE__,__LINE__,"select sql_name from upgrade_list where type != 1 AND type != 5 AND type != 4");
	while($upg = dbr()) {
		db2(__FILE__,__LINE__,"select ".$upg['sql_name']." from default_upgrade_loads where ship_id = '$ship_stats[type_id]'");
		$defaults = dbr2();
		//print_r($defaults);
		if($defaults[$upg['sql_name']] != 0)
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_upgrade_units set ".$upg['sql_name']." = '".$defaults[$upg['sql_name']]."' where ship_id = '$new_ship_id'");
			//print("Updated!!! - $new_ship_id - $ship_stats[type_id]"); flush();
			//exit();
		}
	}
	db(__FILE__,__LINE__,"select ramscoop from default_upgrade_loads where ship_id = '$ship_stats[type_id]'");
	$rscoop = dbr();
	if($rscoop['ramscoop'] == 1)
	{
		dbn(__FILE__,__LINE__,"update ${db_name}_upgrade_units set ramscoop = 1 where ship_id = '$new_ship_id'");
	}
}

// function to remove credits,
function Update_DatabaseForMassPurchase() {
	global $user, $db_name, $user_ship, $user_fleet,$x_totalcost, $x_totaltech, $x_shipname1, $x_shipname2, $x_shipsowned, $quotes, $numships, $num_purchased, $new_ship_id, $ship_stats;
	// puts the user into the newest ship, but only if they are in a EP, or ship destroyed.
	if($user_ship['ship_categ'] == 0) // not likely to happen - but on the safe side its a second catch all for escape pods if the primary check somehow fails
	{
		dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$new_ship_id' where login_id = '$user[login_id]'");
		$user['ship_id'] = $new_ship_id;
		dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ship_id = '$new_ship_id' where login_id = '$user[login_id]' and location = '$user[location]' and fleet_id = '$user_fleet[fleet_id]'");
		$user_fleet['ship_id'] = $new_ship_id;
		db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
		$user_ship = dbr();
		empty_bays();
	}
	$text = array();
	$text['x_totalcost'] = $x_totalcost + $ship_stats['cost'];
	$text['x_shipname1'] = $quotes." 01";
	$text['x_shipname2'] = $quotes." $num";
	db(__FILE__,__LINE__,"select count(ship_id) as numships from ${db_name}_ships where login_id = '$user[login_id]'");
	$tot = dbr();
	$text['x_shipsowned'] = $tot['numships'];
	if($ship_stats['tcost'] > 0)
	{
		$text['x_totaltech'] = $x_totaltech + $ship_stats['tcost'];
		take_tech($ship_stats['tcost']);
		$tech_add = " and <b>$text[x_totaltech]</b> Tech. Support Units";
	}
	take_cash($ship_stats['cost']);
	$text['text'] = "<p><b>$num_purchased</b> <b class=b1>$ship_stats[name]</b>s brought for a total price of <b>$text[x_totalcost]</b> Credits $tech_add.<br />The ships have been named:<br /><br /><b>$text[x_shipname1]</b>...<b>$text[x_shipname2]</b> consecutively.<br /><br />All your fleets combined now consist of a total of <b>$text[x_shipsowned]</b> ships.</p>";
	return $text;
}

?>