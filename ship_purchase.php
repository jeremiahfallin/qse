<?php
/*
//
File:			ship_purchase.php
Objective:		file to manage purchasing of all ships - replaces ship_build.php/bm_ships.php purchase code
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	29 December 2003	Date Modified:	2 July 2004

Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

require_once("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

require_once("includes/fleet_funcs.inc.php");
require_once("includes/ship_purchase_funcs.inc.php");

db(__FILE__,__LINE__,"select count(ship_id) as shipsowned from ${db_name}_ships where login_id = '$user[login_id]'");
$numships = dbr();
if(!$numships['shipsowned'] && isset($sudden_death) && $user['login_id'] != 1 && $user['last_login'] != 0)
{
	print_page("Sudden Death","<p>You have no ship, and this game is in <b>Sudden Death</b>. As such you are out of the game.</p>");
}

if(!isset($x_totalcost)) {$x_totalcost = 0;}
if(!isset($x_totaltech)) {$x_totaltech = 0;}

//load the ship information for the purchase
$ship_types = load_ship_types();
if($mass)
{
	$ship_stats = $ship_types[$mass];
	$take_flag = 1;
}
else
{
	$ship_stats = $ship_types[$ship_type];  //$ship_type passed under $_GET //make this reg_globals off!!!
	$take_flag = 1;
}

$admin_deact = array();
db2(__FILE__,__LINE__,"select ship_type_id from ${db_name}_admin_ships where status = 0");
while($unavailable = dbr2()){
	$admin_deact[] = $unavailable['ship_type_id'];
}

if(in_array($ship_type,$admin_deact)){
	$ship_stats = '';
}

if(empty($ship_stats) && $user['game_login_count'] != '0')
{
	print_page("Error","<p>Admin has set the game up so that this ship is not available for purchase. If tampering with urls, we strongly suggest stopping! Cheating is not something to be taken lightly and controls will be put in place to report all suspicious instances!</p>");
}
if(!isset($ship_stats['config']))
{
	$ship_stats['config'] = "";
}

//prevent url tampering and buying wrong ship from any shipyard and alter return links
$returntext = Check_Shipyard($ship_stats);

$rs = "<br /><br /><a href=location.php>Return to Star System</a>";




/*
// Beginning of Bulk Ship Purchasing------------------------------------------------------------------
*/


if($mass)
{
	settype($num, "integer");
	if($ship_stats['ship_categ'] == 10)
	{
		// check to ensure are only able to bulk buy certain ships
		print_page("Cannot mass purchase FlagShips!","This Shipyard does not offer facilities for mass purchasing of any Flag Ships! Remember that you are only allowed one at any one time (unless of course you manage to steal one in a Raid!).");
	}
	elseif ($num <= 0) // create form and auto add max ships player can purchase
	{
		$t7676 = $max_ships - $numships['shipsowned'];
		if($ship_stats['tcost'] < 1)
		{
			if(($t7676 * $ship_stats['cost']) > $user['cash'])
			{
				$t7676 = floor($user['cash'] / $ship_stats['cost']);
			}
		}
		else
		{
			if(($t7676*$ship_stats['cost'] > $user['cash']) && ($t7676*$ship_stats['tcost'] < $user['tech']))
			{
				$t7676 = floor($user['cash'] / $ship_stats['cost']);
			}
			elseif(($t7676*$ship_stats['cost'] < $user['cash']) && ($t7676*$ship_stats['tcost'] > $user['tech']))
			{
				$t7676 = floor($user['tech'] / $ship_stats['tcost']);
			}
			elseif(($t7676*$ship_stats['cost'] < $user['cash']) && ($t7676*$ship_stats['tcost'] < $user['tech']))
			{
				$t76a = floor($user['tech'] / $ship_stats['tcost']);
				$t76b = floor($user['cash'] / $ship_stats['cost']);
				$t7676 = min($t76a,$t76b);
			}
		}
		$error_str .= "<p>Enter Number of <b class=b1>$ship_stats[type]</b>s to purchase:";
		$error_str .= "<form name=\"submitOnce\" action=\"ship_purchase.php\" method=\"post\" onSubmit=\"submitonce(this);\">";
		$error_str .= "<input type=\"hidden\" name=\"mass\" value=\"$mass\" />";
		if(isset($bmrkt_id))
			{
			$error_str .= "<input type=\"hidden\" name=\"bmrkt_id\" value=\"$bmrkt_id\" />";
			}
		elseif(isset($asyrd_id))
			{
			$error_str .= "<input type=\"hidden\" name=\"asyrd_id\" value=\"$asyrd_id\" />";
			}
		$error_str .= "<input name=\"num\" value=\"$t7676\" size=\"3\" />";
		$error_str .= "&nbsp;<input type=\"submit\" name=\"Submit\" value=\"Submit\" /></form></p>";
	}
	elseif(isset($num_purchased) || $num_purchased >= 1)
	{
		if(!isset($original_num)) {
			$original_num = $num;
		}
		$num = $original_num - $num_purchased; //remaining ships to be added, after new fleet needed to be made
		//unset($num_purchased);
	}
	elseif (($numships['shipsowned'] + $num) > $max_ships)  //prevent purchase of too many ships
	{
		$error_str = "You already own <b>$numships[shipsowned]</b> ship(s). The Admin has set the maximum number of ships players may own to <b>$max_ships</b>.";
	}
	elseif($user['cash'] < ($ship_stats['cost'] * $num)) //check to see if the user can afford them
	{
		$error_str = "You can't afford <b>$num</b> <b class=b1>$ship_stats[name]</b>s. You require more cash to complete this transaction.";
	}
	elseif(($user['tech'] < ($ship_stats['tcost'] * $num)) && $ship_stats['tcost'] > 0) //check to see if the user can afford them
	{
		$error_str = "You can't afford <b>$num</b> <b class=b1>$ship_stats[name]</b>s. You require more Tech. Support Units to complete this transaction.";
	}
	elseif(!isset($ship_name))
	{
		$rs = $returntext['mass'];
		get_var('Name your new ships:','ship_purchase.php',"Your fleet presently consists of <b>$numships[shipsowned]</b> ship(s).<br />When naming your new ships they will be given a number after the name you have entered. (3-25 Characters)",'ship_name','');
	}
	elseif (strlen($ship_name) < 3)
	{
		$rs .= "<p><a href=javascript:history.back()>Try Again</a></p>";
		print_page("Error","Ship name must be at least three characters!");
	}
	else
	{
		if(!isset($original_num)) {
			$original_num = $num;
		}
	}

	$ship_name = correct_name($ship_name);
	$quotes = $ship_name;

	// remove old escape pods
	if($user_ship['ship_categ'] == 0 && $user_fleet['fleet_num'] == 0)
	{
		dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where login_id = '$user[login_id]' && class_name REGEXP 'Escape'");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_fleets where login_id = '$user[login_id]' && fleet_num = 0 && fleet_name = 'Escape Pod'");
		unset($user_fleet);
	}


		// this if statement is usually invoked by an empty user_fleet array - typical of deletion of an escape pod
		// after deletion of escape pod and escape pod's fleet, it's necessary to reset the fleet array so player has a
		// fleet.
		if(empty($user_fleet))
		{
			db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where login_id = '$user[login_id]' order by fleet_num asc");
			while($replacem = dbr()) {
				db2(__FILE__,__LINE__,"select count(ship_id) as num from ${db_name}_ships where fleet_id = '$replacem[fleet_id]'");
				$chk_num = dbr2();
				if($chk_num['num'] < 1)
				{
					dbn(__FILE__,__LINE__,"update ${db_name}_fleets set location = '$user[location]' where login_id = '$user[location]' and fleet_id = '$replacem[fleet_id]'");
					db2(__FILE__,__LINE__,"select * from ${db_name}_fleets where fleet_id = '$replacem[fleet_id]' and login_id = '$user[login_id]'");
					$user_fleet = dbr2();
					break;
				}
			}
		}

	// $h is a simple count marker - passed by a hidden input field in fleet_create form to ensure mass purchase picks up exactly where interrupted by a request to create a new holding fleet for ships
	// These $h checks are a bit paranoid come to think of it...:)
	if(!isset($h) || $h <= 0 || $h == NULL || empty($h)) {$h = 1;}

	for($s=$h;$s<=$original_num;$s++)
	{
		if(isset($fleetnum))
		{
			$user_fleet = Check_ToCreateFleet($fleetnum);
			unset($fleetnum);
		}
		Check_FleetMax(); // check fleet max/war max are not passed
		if ($s < 10)
		{
			$ship_name_sql = $ship_name." 0".$s;
		}
		else
		{
			$ship_name_sql = $ship_name." ".$s;
		}
		$num_purchased = $num_purchased + 1;
		$new_ship_id = Add_ShipToDatabase();
		// If fleet has no command ship - better rectify that - otherwise player will command empty vacuum!
		if($user_fleet['ship_id'] <= 0 || !isset($user_fleet['ship_id']))
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ship_id = '$new_ship_id' where login_id = '$user[login_id]' and fleet_id = '$user_fleet[fleet_id]' and location = '$user[location]'");
			// put player in new ship
			dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$new_ship_id' where login_id = '".$user['login_id']."'");
			// update user arrays
			db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
			$user_ship = dbr();
			$user['ship_id'] = $new_ship_id;
			$user_fleet['ship_id'] = $new_ship_id;
		}
		$update = Update_DatabaseForMassPurchase();
		$x_totalcost = $update['x_totalcost'];
		$x_totaltech = $update['x_totaltech'];
		$error_str2 = $update['text'];

	}

	$rs = $returntext['mass'];
	print_page("Bulk Purchase of Ships",$error_str.$error_str2);
}

/*
// End of Bulk Ship Purchasing------------------------------------------------------------------------
*/

//====================================================================================================

/*
// Beginning of Single Ship Purchasing----------------------------------------------------------------
*/

if (isset($ship_type)){
	$new_ship_config = get_config_var($ship_stats['config']);

	//begin ship checks
	if($user['one_brob'] && $new_ship_config['oo'])
	{
		db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where login_id = '$user[login_id]' and ship_categ = 10");
		$results = dbr();
		if($results)
		{
			print_page("Flagship","You may only own one <b>Flagship</b> class ship at a time.");
		}
		else
		{
			$ship_stats['cost'] = $ship_stats['cost'] * $user['one_brob'];
			if($ship_stats['tcost'] > 0)
			{
				$ship_stats['tcost'] = $ship_stats['tcost'] * $user['one_brob'];
			}
		}
	}
	if ($numships['shipsowned'] >= $max_ships)
	{
		$error_str = "You already own <b>$numships[0]</b> ship(s). The admin has set the maximum number of ships players may have. That limit is <b>$max_ships</b> ships.";
	}
	elseif($ship_type == 1 || $ship_type == 0)
	{
		$error_str = "You may not buy this ship type.";
	}
	elseif($user['cash'] < $ship_stats['cost'])
	{
		$error_str = "You can't afford a <b class=b1>$ship_stats[name]</b>";
	}
	elseif(($user['tech'] < $ship_stats['tcost']) && $ship_stats['tcost'] > 0)
	{
		$error_str = "You don't have enough Tech. Support Units for a <b class=b1>$ship_stats[name]</b>";
	}
	elseif(!isset($ship_name))
	{
		$rs = $returntext['single'];
		get_var('Name your new ship','ship_purchase.php',"Your fleet presently consists of <b>$numships[0]</b> ships.<br />Please enter a name for your new <b class=b1>$ship_stats[name]</b>:(3-25 Char Max)",'ship_name','');
	}
	elseif (strlen($ship_name) < 3)
	{
		$rs .= "<p><a href=javascript:history.back()>Try Again</a>";
		print_page("Error","Ship name must be at least three characters!");
	}
	else
	{

		// remove old escape pods
		if($user_ship['ship_categ'] == 0 && $user_fleet['fleet_num'] == 0)
		{
			dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where login_id = '$user[login_id]' and class_name REGEXP 'Escape'");
			dbn(__FILE__,__LINE__,"delete from ${db_name}_fleets where login_id = '$user[login_id]' and fleet_num = 0 and fleet_name = 'Escape Pod'");
			unset($user_fleet);
		}

		//-Check if fleet to be created, else check if fleet max reached
		if(isset($fleetnum))
		{
			$user_fleet = Check_ToCreateFleet($fleetnum);
			unset($fleetnum);
		}


		// this if statement is usually invoked by an empty user_fleet array - typical of deletion of an escape pod.
		// After deletion of escape pod and escape pod's fleet, it's necessary to reset the fleet array so player has
		// a fleet.
		if(empty($user_fleet))
		{
			db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where login_id = '$user[login_id]' order by fleet_num asc");
			while($replacem = dbr())
			{
				db2(__FILE__,__LINE__,"select count(ship_id) as num from ${db_name}_ships where fleet_id = '$replacem[fleet_id]'");
				$chk_num = dbr2();
				if($chk_num['num'] < 1)
				{
					dbn(__FILE__,__LINE__,"update ${db_name}_fleets set location = '$user[location]' where login_id = '$user[location]' and fleet_id = '$replacem[fleet_id]'");
					db2(__FILE__,__LINE__,"select * from ${db_name}_fleets where fleet_id = '$replacem[fleet_id]' and login_id = '$user[login_id]'");
					$user_fleet = dbr2();
					break;
				}
			}
		}

		Check_FleetMax();
		//--------------------------------------------------------------

		take_cash($ship_stats['cost']);
		if($ship_stats['tcost'] > 0)
		{
			take_tech($ship_stats['tcost']);
		}

		$ship_name = correct_name($ship_name);
		$ship_name_sql = addslashes($ship_name);

		$new_ship_id = Add_ShipToDatabase();
		// If fleet has no command ship - better rectify that - otherwise player will command empty vacuum!
		if($user_fleet['ship_id'] <= 0 || !isset($user_fleet['ship_id']))
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ship_id = '$new_ship_id' where login_id = '$user[login_id]' and fleet_id = '$user_fleet[fleet_id]' and location = '$user[location]'");
			// put player in new ship
			dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$new_ship_id' where login_id = '".$user['login_id']."'");
			db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
			$user_ship = dbr();
			$user['ship_id'] = $new_ship_id;
			$user_fleet['ship_id'] = $new_ship_id;
		}

		empty_bays();
		$new_ship_config = get_config_var($ship_stats['config']);

		if($new_ship_config['oo']) { //change to ship_categ!!!
			if($user['one_brob'])
			{
				dbn(__FILE__,__LINE__,"update ${db_name}_users set one_brob = one_brob + 1 where login_id = '$user[login_id]'");
			}
			else
			{
				dbn(__FILE__,__LINE__,"update ${db_name}_users set one_brob = 2 where login_id = '$user[login_id]'");
			}
			$oo_str .= "A word of warning: You may only own one Flagship Class vessel at a time.<br />Also, each Flagship Class vessel you buy will be twice as expensive as the last time.<br />This is a Galactic Consensus to help keep them out of the hands of reckless types.<br /><br />";
		}
		if($ship_stats['tcost'] > 0)
		{
			$tech_add = "and <b>$ship_stats[tcost]</b> Tech. Support Units";
		}
		$error_str .= "Your payment of <b>$ship_stats[cost]</b> Credits $tech_add has been accepted, and your <b>$ship_stats[name]</b> has been delivered.<br />";
		$error_str .= "<p>May your new <b>$ship_stats[name]</b> bring you strength and your enemies' to ruin!".$oo_str;
	}

	print_page("Ship Purchased",$error_str);

}

/*
// End of Single Ship Purchasing----------------------------------------------------------------------
*/


?>
