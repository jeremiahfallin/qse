<?php
/*
//
File:			ship_loading.inc.php
Objective:		This file contains a standard loading function for use with resource purchasing/ship loading
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	5 February 2004		Date Modified:	3 August 2004

Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/


/*
$fill_option - Fill Fleet/Fill Current Ship/Fill All Fleets (0,1,2)
$cap_option  - Query Amount/Fill to Capacity/Fill Until Zero Credits (0,1,2)
$resource    - Resource IDs are specified in the upcoming QS _resource table (until then use sql_name)
$fill_type   - Starport/Planet (0,1)
$file        - The file this function is being used in, required for forms etc.
$user_amount - This is set by a form prior to calling the function
$source_id  - ID of the planet in use is required, ignore if loading from a port

Default is to fill the current fleet to full capacity, or until credits run out, whichever occurs first.
It is not intended that the Default be used...but just in case...

This function is called as the result of a user completing a standard form submit, which asks them to set these variables. Example forms for those using this function elsewhere can be found in use for StarPorts and Planets.

This function looks complicated, but it's really quite a simple set of checks and balances to ensure user choices for loading their ships/fleets are valid and allowable, otherwise they are limited to a valid values. Just keep the variables being passed in mind and what limits/choices they embody.

*/


function Load_Resource($fill_option=0, $cap_option=2, $resource, $fill_type, $file, $user_amount, $source_id) {
	global $user, $user_fleet, $db_name, $buy_fuel, $buy_metal, $buy_organ, $buy_elect, $buy_darkmatter, $cost_colonist, $fighter_cost_earth;

	// The max cost in turns for loading a fleet
	$turn_load_cost = 10;

	// First lets find out what resource is being handled - this will be re-written when resource_ids are in effect
	if($resource == "fuel")
	{
		$name = "Fuel";
		$unit_cost = $buy_fuel;
	}
	elseif($resource == "metal")
	{
		$name = "Metal";
		$unit_cost = $buy_metal;
	}
	elseif($resource == "elect")
	{
		$name = "Electronics";
		$unit_cost = $buy_elect;
	}
	elseif($resource == "darkmatter")
	{
		$name = "Darkmatter";
		$unit_cost = $buy_darkmatter;
	}
	elseif($resource == "organ")
	{
		$name = "Organics";
		$unit_cost = $buy_organ;
	}
	elseif($resource == "colon")
	{
		$name = "Colonists";
		$unit_cost = (int)round($cost_colonist * 1.20);  // 20% surcharge for colos not from HW
	}
	elseif($resource == "fighters")
	{
		$name = "Fighters";
		$unit_cost = $fighter_cost_earth;
	}

	if($resource != "fighters")
	{
		$bay_text = "Cargo Bays";
	}
	else
	{
		$bay_text = "Fighter Bays";
	}

	if($fill_type == 1)
	{
		db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = '$source_id'");
		$source = dbr();
	}

	// Second lets figure out what player wants to fill, and how much of that option he can fill in total
	// Note: For single fleet and all fleet operations, we don't know how many ships in each fleet
	//       will be affected, so we check versus the max turns required.
	if($fill_option == 0) // single fleet
	{
		if($fill_type != 1 or ($fill_type == 1 and $user[turns] >= $turn_load_cost))
		{
			if($resource != "fighters")
			{
				db(__FILE__,__LINE__,"select sum(cargo_bays-fuel-metal-darkmatter-colon-elect-organ) as total from ${db_name}_ships where fleet_id = '$user_fleet[fleet_id]'");
				$t_cap = dbr();
			}
			else
			{
				db(__FILE__,__LINE__,"select sum(max_fighters-fighters) as total from ${db_name}_ships where fleet_id = '$user_fleet[fleet_id]'");
				$t_cap = dbr();
			}
		}
		else
		{
		print_page("Not Enough turns", "You do not have enough turns to Load/Unload your ships");
		}
	}
	elseif($fill_option == 1) // single ship
	{
		if($fill_type != 1 or ($fill_type == 1 and $user[turns] >= 1))
		{
			if($resource != "fighters")
			{
				db(__FILE__,__LINE__,"select sum(cargo_bays-fuel-metal-darkmatter-colon-elect-organ) as total from ${db_name}_ships where ship_id = '$user[ship_id]'");
				$t_cap = dbr();
			}
			else
			{
				db(__FILE__,__LINE__,"select sum(max_fighters-fighters) as total from ${db_name}_ships where ship_id = '$user[ship_id]'");
				$t_cap = dbr();
			}
		}
		else
		{
		print_page("Not Enough turns", "You do not have enough turns to Load/Unload your ship");
		}
	}
	elseif($fill_option == 2) // all fleets in system
	{
                db(__FILE__,__LINE__,"select count(fleet_id) as num_fleet from ${db_name}_fleets where location = '$user[location]' and login_id = '$user[login_id]'");
                $num_fleet = dbr();$num_fleet = $num_fleet[0];
                if($fill_type != 1 or ($fill_type == 1 and $user[turns] >= ($turn_load_cost * $num_fleet)))
		{
			db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where location = '$user[location]' and login_id = '$user[login_id]'");
			$t_cap = array();
			// replace with foreach()!
			while($f_fleets = dbr()) {
				db2(__FILE__,__LINE__,"select cargo_bays,fuel,metal,darkmatter,colon,elect,organ,max_fighters,fighters from ${db_name}_ships where fleet_id = '$f_fleets[fleet_id]'");
				while($f_total = dbr2()) {
					if($resource != "fighters")
					{
						$f_fulltotal = $f_total['cargo_bays'] - $f_total['fuel'] - $f_total['metal'] - $f_total['darkmatter'] - $f_total['colon'] - $f_total['elect'] - $f_total['organ'];
						$t_cap['total'] = $t_cap['total'] + $f_fulltotal;
					}
					else
					{
						$f_fulltotal = $f_total['max_fighters'] - $f_total['fighters'];
						$t_cap['total'] = $t_cap['total'] + $f_fulltotal;
					}
				}
			}
		}
		else
		{
		print_page("Not Enough turns", "You do not have enough turns to Load/Unload your ships");
		}
	}
	$t_cap = $t_cap['total'];

	if($t_cap <= 0) //no capacity
	{
		$result['text'] .= "Sorry but you have no free $bay_text available to carry additional $name";
		return $result;
	}

	// Third we need to calculate the maximum capacity we are going to use, applying either the user
	// defined resource amount, or the credit limit - if neither apply we simply use the calculated max
	// capacity from above

	if($cap_option == 0) //user has defined an amount to fill
	{
		// check for negatives,fractions,non-numbers - and disallow
		if($user_amount <= 0 || !is_numeric($user_amount))
		{
			$result['text'] .= "You have entered an amount which either not a valid number greater than zero, or is not an integer at all!";
			return $result;
		}
		$user_amount = (integer) $user_amount;

		// compare user amount against the maximum capacity available
		if($user_amount > $t_cap) //limit user amount to maximum capacity
		{
			$fill_total = $t_cap;
		}
		else                      //else use the user amount
		{
			$fill_total = $user_amount;
		}
	}
	elseif($cap_option == 1)      //user chooses to use the max capacity available
	{
		$fill_total = $t_cap;
	}


	// The $cap_option=2 option is always checked regardless of choice - but players choice is still valid
	// for its own peculiar reason - :)
	// double check this is a *purchase* source, if not revert to *free* source and impose either a cash or source unit
	// limit
	// its up to developers to ensure the $fill_type var is set - in the future there may be varied sources
	if($fill_type == 0)       // e.g. StarPort
	{
		$cash_total = round($user['cash'] / $unit_cost) - 1;
		if($fill_total > $cash_total)    //limit what we can afford to available capacity if applicable
		{
			$fill_total = $cash_total;
		}
	}
	else
	{
		if($source[$resource] < $fill_total) //ensure we limit fill to available source units
		{
			$fill_total = $source[$resource];
		}
	}

	// Fourth! Now that we finally know just how much of the resource is going to be loaded, we need to get it loaded
	// and if applicable pay over any credits in exchange
	$result = array(); //this will hold total results and other specific to be returned in the final message to user
	if($fill_option == 0)  // single fleet
	{
		$ship_count = 0;
		db(__FILE__,__LINE__,"select ship_id, cargo_bays, fuel, metal, organ, elect, colon, darkmatter, max_fighters, fighters from ${db_name}_ships where fleet_id = '$user_fleet[fleet_id]'");
		while($f_ship = dbr()) {
			if($fill_total <= 0)
			{
				break;  // nothing else to transfer...
			}
			if($resource != "fighters")
			{
				$s_cap = $f_ship['cargo_bays'] - $f_ship['fuel'] - $f_ship['metal'] - $f_ship['organ'] - $f_ship['elect'] - $f_ship['colon'] - $f_ship['darkmatter'];
			}
			else
			{
				$s_cap = $f_ship['max_fighters'] - $f_ship['fighters'];
			}
			if($s_cap > $fill_total) //limit final ship_cap to remaining resource awaiting loading
			{
				$s_cap = $fill_total;
			}
			if ($s_cap > 0) {
				$ship_count += 1;
			}
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set ".$resource." = ".$resource." + '$s_cap' where ship_id = '$f_ship[ship_id]'");
			$fill_total -= $s_cap;
			$t_s_cap += $s_cap;
			// check if a payment due!
			if($fill_type == 0)
			{
				$t_cost = $s_cap * $unit_cost;
				$t_t_cost += $t_cost;
				take_cash($t_cost); //deduct payment
			}
			else
			{
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set ".$resource." = ".$resource." - '$s_cap' where planet_id = '$source[planet_id]'");
			}
		}
		if($fill_type == 0)
		{
			$turn_cost = min(5, $ship_count); // Match Sell All from All Ships behavior of 1-5 turns
			charge_turns($turn_cost);
			$result['text'] = "<p>It took <b>$turn_cost</b> Turn(s) to load $t_s_cap units of $name for a total cost of $t_t_cost Credits. The $name was loaded onto $ship_count Ship(s) in 1 Fleet in Star System #$user[location].</p>";
		}
		else
		{
			$turn_cost = min($turn_load_cost, $ship_count); // 1-9 for 1-9 ships, 10 for 10+ ships
			charge_turns($turn_cost);
			$result['text'] = "<p>$t_s_cap units of $name was loaded from the planet $source[name] at a cost of <b>$turn_cost</b> Turn(s). The $name was loaded onto $ship_count Ship(s) in 1 Fleet in Star System #$user[location].</p>";
		}
	}
	elseif($fill_option == 1)  // single ship
	{
		$fleet_count = -1;
		db(__FILE__,__LINE__,"select ship_id, cargo_bays, fuel, metal, organ, elect, colon, darkmatter, max_fighters, fighters from ${db_name}_ships where ship_id = '$user[ship_id]'");
		$f_ship = dbr();
		if($resource != "fighters")
		{
			$s_cap = $f_ship['cargo_bays'] - $f_ship['fuel'] - $f_ship['metal'] - $f_ship['organ'] - $f_ship['elect'] - $f_ship['colon'] - $f_ship['darkmatter'];
		}
		else
		{
			$s_cap = $f_ship['max_fighters'] - $f_ship['fighters'];
		}
		if($s_cap > $fill_total) //limit ship_cap to resource awaiting loading
		{
			$s_cap = $fill_total;
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set ".$resource." = ".$resource." + '$s_cap' where ship_id = '$f_ship[ship_id]'");
		// check if a payment due!
		if($fill_type == 0)
		{
			$t_cost = $s_cap * $unit_cost;
			take_cash($t_cost); //deduct payment
			charge_turns(1);  // Match Sell All behavior of 1 turn
			$result['text'] = "<p>It took <b>1</b> Turn to load $s_cap units of $name into your ship for a total cost of $t_cost Credits.</p>";
		}
		else
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set ".$resource." = ".$resource." - '$s_cap' where planet_id = '$source[planet_id]'");
			charge_turns(1);
			$result['text'] = "<p>$s_cap units of $name was loaded from the planet $source[name] at a cost of <b>1</b> Turn. The $name was loaded onto 1 Ship in 1 Fleet in Star System #$user[location].</p>";
		}
	}
	elseif($fill_option == 2)  // all fleets in the system
	{
		$turn_cost = 0;
		$fleet_count = 0;
		db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where location = '$user[location]' and login_id = '$user[login_id]'");
		while(($fill_total > 0) && ($f_fleets = dbr())) {
			$ships_affected_in_fleet = 0;
			db2(__FILE__,__LINE__,"select ship_id, cargo_bays, fuel, metal, organ, elect, colon, darkmatter, max_fighters, fighters from ${db_name}_ships where fleet_id = '$f_fleets[fleet_id]'");
			while($f_ship = dbr2()) {
				if($fill_total <= 0)
				{
					break;  // nothing else to transfer...
				}
				if($resource != "fighters")
				{
					$s_cap = $f_ship['cargo_bays'] - $f_ship['fuel'] - $f_ship['metal'] - $f_ship['organ'] - $f_ship['elect'] - $f_ship['colon'] - $f_ship['darkmatter'];
				}
				else
				{
					$s_cap = $f_ship['max_fighters'] - $f_ship['fighters'];
				}
				if($s_cap > $fill_total) //limit final ship_cap to remaining resource awaiting loading
				{
					$s_cap = $fill_total;
				}
				if ($s_cap > 0)
				{
					$ship_count += 1;
					$ships_affected_in_fleet += 1;
				}
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set ".$resource." = ".$resource." + '$s_cap' where ship_id = '$f_ship[ship_id]'");
				$fill_total -= $s_cap;
				$t_s_cap += $s_cap;
				// check if a payment due!
				if($fill_type == 0) //source is either a starport or other purchase location
				{
					$t_cost = $s_cap * $unit_cost;
					$t_t_cost = $t_s_cap * $unit_cost;
					take_cash($t_cost); //deduct payment
				}
				else
				{
					dbn(__FILE__,__LINE__,"update ${db_name}_planets set ".$resource." = ".$resource." - '$s_cap' where planet_id = '$source[planet_id]'");
				}
			}
			if ($ships_affected_in_fleet > 0)
			{
				$fleet_count += 1;
				$turn_cost += min($turn_load_cost, $ships_affected_in_fleet); // Each fleet adds 1-9 for 1-9 ships affected or 10 for 10+ ships affected
			}
		}

		//DEV-NOTE: Does the above not make earlier result statements pointless? Check next time...

		if($fill_type == 0)
		{
			$turn_cost = min(5, $ship_count); // Match Sell All from All Ships behavior of 1-5 turns
			charge_turns($turn_cost);
			$result['text'] = "<p>It took <b>$turn_cost</b> Turn(s) to load $t_s_cap units of $name for a total cost of $t_t_cost Credits. The $name was loaded onto $ship_count Ship(s) in $fleet_count Fleet(s) in Star System #$user[location].</p>";
		}
		else
		{
			charge_turns($turn_cost);
			$result['text'] = "<p>$t_s_cap units of $name was loaded from the planet $source[name] at a cost of <b>$turn_cost</b> Turn(s). The $name was loaded onto $ship_count Ship(s) in $fleet_count Fleet(s) in Star System #$user[location].</p>";
		}
	}

	// To keep this page's display accurate, update the user ship in case it was affected by the above
	get_user_ship($user['ship_id']);

	return $result;
}

// In post 2.2 versions, ship resources will be kept to a separate database table - plugins will have the ability to add new resources - and all resource based game elements will be adjusted to automatically account for such.


// step two in getting this unified function working - we need the input form


function Load_Resource_Form($action, $resource, $file) {
	global $user, $user_fleet, $db_name, $buy_fuel, $buy_metal, $buy_organ, $buy_elect, $buy_darkmatter, $cost_colonist;

	if($resource == "fuel")
	{
		$name = "Fuel";
		$unit_cost = $buy_fuel;
	}
	elseif($resource == "metal")
	{
		$name = "Metal";
		$unit_cost = $buy_metal;
	}
	elseif($resource == "elect")
	{
		$name = "Electronics";
		$unit_cost = $buy_elect;
	}
	elseif($resource == "darkmatter")
	{
		$name = "Darkmatter";
		$unit_cost = $buy_darkmatter;
	}
	elseif($resource == "organ")
	{
		$name = "Organics";
		$unit_cost = $buy_organ;
	}
	elseif($resource == "colon")
	{
		$name = "Colonists";
		$unit_cost = (int)round($cost_colonist * 1.20);  // 20% surcharge for colos not from HW
	}
	elseif($resource == "fighters")
	{
		$name = "Fighters";
		$unit_cost = $fighter_cost_earth;
	}
	if($action == "buy")
	{
		$action2 = "Buy";
	}
	else {
		$action2 = "Load";
	}

	$form = "
		<form action=\"$file\" method=\"post\" id=\"load_resource_form\" name=\"load_resource_form\">
		<input type=\"hidden\" id=\"resource\" name=\"resource\" value=\"$resource\" />
		<input type=\"hidden\" id=\"load_resource\" name=\"load_resource\" value=\"1\" />
		<p style=\"background-color: #222222\">
			Please complete the options below in order to $action $name. Please note that if your choice cannot be completed, it will be automatically adjusted to the closest possible alternative. For example, if you choose to load 3000 fuel from a planet with just 1000, your amount will be adjusted to 1000. Or if you choose to load the full capacity of all ships with fuel from a port and you do not have enough credits, the function will automatically adjust your request and only load as much fuel as you can afford. This automatic adjusting along with the options below give you very simple, yet exact control over all resource loading.
		</p>
	";
	if($action == "buy")
	{
		$form .= "
		<p>
			You may purchase $name at $unit_cost per unit. It requires up to 5 Turns to load the cargo into your Ship(s).
		</p>
		<p class=\"h6\">
			Step 1: Select the range of ships to be effected.
		</p>
		<div>
			<input type=\"radio\" id=\"fill_option\" name=\"fill_option\" value=\"0\" />Current Fleet in Command<br />
			<input type=\"radio\" id=\"fill_option\" name=\"fill_option\" value=\"1\" checked=\"checked\" />Current Ship in Command<br />
			<input type=\"radio\" id=\"fill_option\" name=\"fill_option\" value=\"2\" />All Fleets in Star System
		</div>
		<p class=\"h6\">
			Step 2: Select the action you wish to take and submit the order.
		</p>
		<div>
			<input type=\"radio\" id=\"cap_option\" name=\"cap_option\" value=\"0\" checked=\"checked\" />$action2 the following amount of $name:<br />
			<input type=\"text\" id=\"user_amount\" name=\"user_amount\" value=\"\" size=\"10\" /><br /><br />
			<input type=\"radio\" id=\"cap_option\" name=\"cap_option\" value=\"1\" />$action2 Full Capacity of Ship/Fleet(s) in $name
		</div>
		<div>
			<br /><br /><input type=\"submit\" name=\"submit_load_resource_form\" value=\"Confirm!\" />
		</div>
		</form>
		";
	}
	elseif ($action == "load")
	{
		$form .= "
		<p>
			All ship to/from planet transfers cost up to 10 Turns per Fleet affected or 1 Turn for a single ship.
		</p>
		<p class=\"h6\">
			Step 1: Select the range of ships to be effected.
		</p>
		<div>
			<input type=\"radio\" id=\"fill_option\" name=\"fill_option\" value=\"0\" />Current Fleet in Command<br />
			<input type=\"radio\" id=\"fill_option\" name=\"fill_option\" value=\"1\" checked=\"checked\" />Current Ship in Command<br />
			<input type=\"radio\" id=\"fill_option\" name=\"fill_option\" value=\"2\" />All Fleets in Star System
		</div>
		<p class=\"h6\">
			Step 2: Select the action you wish to take and submit the order.
		</p>
		<div>
			<input type=\"radio\" id=\"cap_option\" name=\"cap_option\" value=\"0\" checked=\"checked\" />$action2 the following amount of $name:<br />
			<input type=\"text\" id=\"user_amount\" name=\"user_amount\" value=\"\" size=\"10\" /><br /><br />
			<input type=\"radio\" id=\"cap_option\" name=\"cap_option\" value=\"1\" />$action2 Full Capacity of Ship/Fleet(s) with $name
		</div>
		<div>
			<br /><br /><input type=\"submit\" name=\"submit_load_resource_form\" value=\"Confirm!\" />
		</div>
		</form>
		";
	}
	return $form;
}

/*
			<input type=\"radio\" id=\"cap_option\" name=\"cap_option\" value=\"2\" />$action2 $name using all Credits available<br />
			<input type=\"radio\" id=\"cap_option\" name=\"cap_option\" value=\"3\" />$action2 all $name from source
*/

//$cap_option  - Query Amount/Fill to Capacity/Fill Until Zero Credits/Fill Max from Source (0,1,2,3)



// function to manage sale and unloading of resources
// this function is largely an inverted form of the Load_Resource function, main difference is that players
// may choose to sell enough of a resource to gain a chosen amount of credits. Big difference from the old non-uniform
// code... :)

function Unload_Resource($fill_option=0, $cap_option, $resource, $fill_type, $file, $user_amount, $user_credits, $source_id) {
	global $user, $user_fleet, $db_name, $buy_fuel, $buy_metal, $buy_organ, $buy_elect, $buy_darkmatter, $flag_trading, $cost_colonist, $fighter_cost_earth;

	$result = array(); //this will hold total results and other specifics to be returned in the final message to user

	// The max cost in turns for unloading a fleet
	$turn_load_cost = 10;

	// First lets find out what resource is being handled - this will be re-written when resource_ids are in effect
	if($resource == "fuel")
	{
		$name = "Fuel";
		$unit_cost = $buy_fuel;
	}
	elseif($resource == "metal")
	{
		$name = "Metal";
		$unit_cost = $buy_metal;
	}
	elseif($resource == "elect")
	{
		$name = "Electronics";
		$unit_cost = $buy_elect;
	}
	elseif($resource == "darkmatter")
	{
		$name = "Darkmatter";
		$unit_cost = $buy_darkmatter;
	}
	elseif($resource == "organ")
	{
		$name = "Organics";
		$unit_cost = $buy_organ;
	}
	elseif($resource == "colon")
	{
		$name = "Colonists";
		$unit_cost = (int)round($cost_colonist * 0.80);  // 20% surcharge for colos not from HW
	}
	elseif($resource == "fighters")
	{
		$name = "Fighters";
		$unit_cost = $fighter_cost_earth;
	}

	if($fill_type == 1) //to a planet
	{
		db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = '$source_id'");
		$source = dbr();
	}
	elseif($fill_type == 0) //to a starport
	{
		db(__FILE__,__LINE__,"select * from ${db_name}_ports where location = '$user[location]'");
		$port = dbr();
		if(empty($port))
		{
			print_page("Error!","There is no Star Port in your current location. You cannot therefore sell any $name here.");
		}
		if($flag_trading == 0)
		{
			//calculate starport price for buying player's resource
			//typically 20% below starport selling price, the rip-off merchants...:)
			$unit_cost = $unit_cost - round(($unit_cost/100)*20);
		}
	}
	else //to nothing whatsoever - cheaters...
	{
		print_page("Error!","<p>A required variable for this function is absent. This function is therefore terminated. Please contact an Administrator for further help. Or give yourself up for URL doping :)</p>");
	}

	// Second lets figure out what player wants to fill, and how much of that option he can fill in total
	// Note: For single fleet and all fleet operations, we don't know how many ships in each fleet
	//       will be affected, so we check versus the max turns required.
	if($fill_option == 0) // single fleet
	{
		if($fill_type != 1 or ($fill_type == 1 and $user[turns] >= $turn_load_cost))
		{
			db(__FILE__,__LINE__,"select sum(".$resource.") as total from ${db_name}_ships where fleet_id = '$user_fleet[fleet_id]'");
			$t_cap = dbr();
		}
		else
		{
			print_page("Not Enough turns", "You do not have enough turns to Load/Unload your ships");
		}
	}
	elseif($fill_option == 1) // single ship
	{
		if($fill_type != 1 or ($fill_type == 1 and $user[turns] >= 1))
		{
			db(__FILE__,__LINE__,"select sum(".$resource.") as total from ${db_name}_ships where ship_id = '$user[ship_id]'");
			$t_cap = dbr();
		}
		else
		{
			print_page("Not Enough turns", "You do not have enough turns to Load/Unload your ship");
		}
	}
	elseif($fill_option == 2) // all fleets in system
	{
		db(__FILE__,__LINE__,"select count(fleet_id) as num_fleet from ${db_name}_fleets where location = '$user[location]' and login_id = '$user[login_id]'");
		$num_fleet = dbr();$num_fleet = $num_fleet[0];
		if($fill_type != 1 or ($fill_type == 1 and $user[turns] >= ($turn_load_cost * $num_fleet)))
		{
			db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where location = '$user[location]' and login_id = '$user[login_id]'");
			$t_cap = array();
			// replace with foreach()!
			while($f_fleets = dbr()) {
				//
				db2(__FILE__,__LINE__,"select ".$resource." from ${db_name}_ships where fleet_id = '$f_fleets[fleet_id]'");
				while($f_total = dbr2()) {
					$f_fulltotal = $f_total[$resource];
					$t_cap['total'] = $t_cap['total'] + $f_fulltotal;
				}
			}
		}
		else
		{
		print_page("Not Enough turns", "You do not have enough turns to Load/Unload your ship");
		}
	}
	$t_cap = $t_cap['total'];

	if($t_cap <= 0) //no capacity
	{
		$result['text'] .= "Sorry but you have no $name on the ship(s) selected.";
		return $result;
	}

	// to here seems fine - just a few modifications

	// Third we need to calculate the maximum amount we are going to use, applying either the user
	// defined resource amount, or the credit limit - if neither apply we simply use the calculated max
	// capacity from above

	if($cap_option == 0) //user has defined an amount to unload
	{
		// check for negatives,fractions,non-numbers - and disallow
		if($user_amount <= 0 || !is_numeric($user_amount))
		{
			$result['text'] .= "You have entered an amount which either not a valid integer greater than zero, or is not an integer at all!";
			return $result;
		}
		$user_amount = (integer) $user_amount; //change fractions to integers if required

		// compare user amount against the maximum resource available
		if($user_amount > $t_cap) //limit user amount to resource available
		{
			$fill_total = $t_cap;
		}
		else                      //else use the user amount
		{
			$fill_total = $user_amount;
		}
	}
	elseif($cap_option == 1)      //user chooses to unload the max resource available
	{
		$fill_total = $t_cap;
	}
	elseif($cap_option == 2)   //user has specified a credit amount to gain - sell just enough to get creditsl
	{
		//grab minimum resource needed to sell to reach credits requested (will give slightly more credits than needed)
		$fill_total = floor(($user_credits/$unit_cost)) + 1;
		if($fill_total > $t_cap)
		{
			$fill_total = $t_cap;
		}
	}

	if($fill_option == 0)  // single fleet
	{
		$ship_count = 0;
		db(__FILE__,__LINE__,"select ship_id, cargo_bays, fuel, metal, organ, elect, colon, darkmatter, max_fighters, fighters from ${db_name}_ships where fleet_id = '$user_fleet[fleet_id]'");
		while($f_ship = dbr()) {
			if($fill_total <= 0)
			{
				break;  // nothing else to transfer...
			}
			$s_cap = $f_ship[$resource];
			if($s_cap > $fill_total) //limit final ship_cap to remaining resource awaiting loading
			{
				$s_cap = $fill_total;
			}
			if ($s_cap > 0) {
				$ship_count += 1;
			}
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set ".$resource." = ".$resource." - '$s_cap' where ship_id = '$f_ship[ship_id]'");
			$fill_total -= $s_cap;
			$t_s_cap += $s_cap;
			// check if a payment due!
			if($fill_type == 0)
			{
				$t_cost = $s_cap * $unit_cost;
				$t_t_cost += $t_cost;
				give_cash($t_cost); //issue payment
			}
			else
			{
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set ".$resource." = ".$resource." + '$s_cap' where planet_id = '$source[planet_id]'");
			}
		}
		if($fill_type == 0)
		{
			$turn_cost = min(5, $ship_count); // Match Sell All from All Ships behavior of 1-5 turns
			charge_turns($turn_cost);
			$result['text'] = "<p>It took <b>$turn_cost</b> Turn(s) to unload $t_s_cap units of $name from $ship_count Ship(s) for a total price of $t_t_cost Credits. The proceeds have been added to your Credits.</p><p>StarPort #$port[port_id] thanks you for your business.</p>";
		}
		else
		{
			$turn_cost = min($turn_load_cost, $ship_count); // 1-9 for 1-9 ships, 10 for 10+ ships
			charge_turns($turn_cost);
			$result['text'] = "<p>$t_s_cap units of $name were unloaded from your ship(s) to Planet $source[name] at a cost of <b>$turn_cost</b> Turn(s). The $name was unloaded from $ship_count Ship(s) in 1 Fleet in Star System #$user[location].</p>";
		}
	}
	elseif($fill_option == 1)  // single ship
	{
		$fleet_count = -1;
		db(__FILE__,__LINE__,"select ship_id, cargo_bays, fuel, metal, organ, elect, colon, darkmatter, fighters from ${db_name}_ships where ship_id = '$user[ship_id]'");
		$f_ship = dbr();
		$s_cap = $f_ship[$resource];
		if($s_cap > $fill_total) //limit ship_cap to resource awaiting loading
		{
			$s_cap = $fill_total;
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set ".$resource." = ".$resource." - '$s_cap' where ship_id = '$f_ship[ship_id]'");
		// check if a payment due!
		if($fill_type == 0)
		{
			$t_cost = $s_cap * $unit_cost;
			give_cash($t_cost); //deduct payment
			charge_turns(1);  // Match Sell All behavior of 1 turn
			$result['text'] = "<p>It took <b>1</b> Turn to unload $s_cap units of $name from your ship for a total price of $t_cost Credits. The proceeds have been added to your Credits.</p><p>StarPort #$port[port_id] thanks you for your business.</p>";
		}
		else
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set ".$resource." = ".$resource." + '$s_cap' where planet_id = '$source[planet_id]'");
			charge_turns(1);
			$result['text'] = "<p>$s_cap units of $name were unloaded from your ship to Planet $source[name] at a cost of <b>1</b> Turn. The $name was unloaded from 1 Ship in 1 Fleet in Star System #$user[location].</p>";
		}
	}
	elseif($fill_option == 2)  // all fleets in the system
	{
		$turn_cost = 0;
		$fleet_count = 0;
		db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where location = '$user[location]' and login_id = '$user[login_id]'");
		while(($fill_total > 0) && ($f_fleets = dbr())) {
			$ships_affected_in_fleet = 0;
			db2(__FILE__,__LINE__,"select ship_id, cargo_bays, fuel, metal, organ, elect, colon, darkmatter, max_fighters, fighters from ${db_name}_ships where fleet_id = '$f_fleets[fleet_id]'");
			while($f_ship = dbr2()) {
				if($fill_total <= 0)
				{
					break;  // nothing else to transfer...
				}
				$s_cap = $f_ship[$resource];
				if($s_cap > $fill_total) //limit final ship_cap to remaining resource awaiting unloading
				{
					$s_cap = $fill_total;
				}
				if ($s_cap > 0)
				{
					$ship_count += 1;
					$ships_affected_in_fleet += 1;
				}
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set ".$resource." = ".$resource." - '$s_cap' where ship_id = '$f_ship[ship_id]'");
				$fill_total -= $s_cap;
				$t_s_cap += $s_cap;
				// check if a payment due!
				if($fill_type == 0) //source is either a starport or other purchase location
				{
					$t_cost = $s_cap * $unit_cost;
					$t_t_cost = $t_s_cap * $unit_cost;
					give_cash($t_cost); //deduct payment
				}
				else
				{
					dbn(__FILE__,__LINE__,"update ${db_name}_planets set ".$resource." = ".$resource." + '$s_cap' where planet_id = '$source[planet_id]'");
				}
			}
			if ($ships_affected_in_fleet > 0)
			{
				$fleet_count += 1;
				$turn_cost += min($turn_load_cost, $ships_affected_in_fleet); // Each fleet adds 1-9 for 1-9 ships affected or 10 for 10+ ships affected
			}
		}
		if($fill_type == 0)
		{
			$turn_cost = min(5, $ship_count); // Match Sell All from All Ships behavior of 1-5 turns
			charge_turns($turn_cost);
			$result['text'] = "<p>It took <b>$turn_cost</b> Turn(s) to unload $t_s_cap units of $name from $ship_count Ship(s) for a total price of $t_t_cost Credits. The proceeds have been added to your Credits.</p><p>StarPort #$port[port_id] thanks you for your business.</p>";
		}
		else
		{
			charge_turns($turn_cost);
			$result['text'] = "<p>$t_s_cap units of $name were unloaded to the planet $source[name] at a cost of <b>$turn_cost</b> Turn(s). The $name was unloaded from $ship_count Ship(s) in $fleet_count Fleet(s) in Star System #$user[location].</p>";
		}
	}

	// To keep this page's display accurate, update the user ship in case it was affected by the above
	get_user_ship($user['ship_id']);

	return $result;
}




function Unload_Resource_Form($action, $resource, $file) {
	global $user, $user_fleet, $db_name, $buy_fuel, $buy_metal, $buy_organ, $buy_elect, $buy_darkmatter, $flag_trading, $cost_colonist;

	if($resource == "fuel")
	{
		$name = "Fuel";
		$unit_cost = $buy_fuel;
	}
	elseif($resource == "metal")
	{
		$name = "Metal";
		$unit_cost = $buy_metal;
	}
	elseif($resource == "elect")
	{
		$name = "Electronics";
		$unit_cost = $buy_elect;
	}
	elseif($resource == "darkmatter")
	{
		$name = "Darkmatter";
		$unit_cost = $buy_darkmatter;
	}
	elseif($resource == "organ")
	{
		$name = "Organics";
		$unit_cost = $buy_organ;
	}
	elseif($resource == "colon")
	{
		$name = "Colonists";
		$unit_cost = (int)round($cost_colonist * 0.80);  // 20% surcharge for colos not from HW
	}
	elseif($resource == "fighters")
	{
		$name = "Fighters";
		$unit_cost = $fighter_cost_earth;
	}
	if($action == "sell")
	{
		$action2 = "Sell";
	}
	else {
		$action2 = "Unload";
	}
	if($flag_trading == 0)
	{
		$unit_cost = $unit_cost - (round(($unit_cost/100)*20));
	}

	$form = "
		<form action=\"$file\" method=\"post\" id=\"unload_resource_form\" name=\"unload_resource_form\">
		<input type=\"hidden\" id=\"resource\" name=\"resource\" value=\"$resource\" />
		<input type=\"hidden\" id=\"unload_resource\" name=\"unload_resource\" value=\"1\" />
		<p style=\"background-color: #222222\">
			Please complete the options below in order to $action $name. Please note that if your choice cannot be completed, it will be automatically adjusted to the closest possible alternative. For example, if you choose to unload 3000 fuel to a planet when your ship selection only holds 1000 as cargo, your amount will be adjusted to 1000. This automatic adjusting along with the options below give you very simple, yet exact control over all resource unloading/selling. You may also choose to sell only enough of a resource to raise a specified number of Credits.
		</p>
	";
	if($action == "sell")
	{
		$form .= "
		<p>
			You may sell $name for $unit_cost per unit. It requires up to 5 Turns to unload the cargo from your Ship(s).
		</p>
		<p class=\"h6\">
			Step 1: Select the range of ships to be effected.
		</p>
		<div>
			<input type=\"radio\" id=\"fill_option\" name=\"fill_option\" value=\"0\" />Current Fleet in Command<br />
			<input type=\"radio\" id=\"fill_option\" name=\"fill_option\" value=\"1\" checked=\"checked\" />Current Ship in Command<br />
			<input type=\"radio\" id=\"fill_option\" name=\"fill_option\" value=\"2\" />All Fleets in Star System
		</div>
		<p class=\"h6\">
			Step 2: Select the action you wish to take and submit the order.
		</p>
		<div>
			<input type=\"radio\" id=\"cap_option\" name=\"cap_option\" value=\"0\" checked=\"checked\" />$action2 the following amount of $name:<br />
			<input type=\"text\" id=\"user_amount\" name=\"user_amount\" value=\"\" size=\"10\" /><br /><br />
			<input type=\"radio\" id=\"cap_option\" name=\"cap_option\" value=\"1\" />$action2 Full Capacity of Ship/Fleet(s) in $name<br /><br />
			<input type=\"radio\" id=\"cap_option\" name=\"cap_option\" value=\"2\" />$action2 enough $name to raise the following number of Credits:<br />
			<input type=\"text\" id=\"user_credits\" name=\"user_credits\" value=\"\" size=\"10\" />
		</div>
		<div>
			<br /><br /><input type=\"submit\" name=\"submit_unload_resource_form\" value=\"Confirm!\" />
		</div>
		</form>
		";
	}
	elseif ($action == "unload")
	{
		$form .= "
		<p>
			All ship to/from planet transfers cost up to 10 Turns per Fleet affected or 1 Turn for a single ship.

		</p>
		<p class=\"h6\">
			Step 1: Select the range of ships to be effected.
		</p>
		<div>
			<input type=\"radio\" id=\"fill_option\" name=\"fill_option\" value=\"0\" />Current Fleet in Command<br />
			<input type=\"radio\" id=\"fill_option\" name=\"fill_option\" value=\"1\" checked=\"checked\" />Current Ship in Command<br />
			<input type=\"radio\" id=\"fill_option\" name=\"fill_option\" value=\"2\" />All Fleets in Star System
		</div>
		<p class=\"h6\">
			Step 2: Select the action you wish to take and submit the order.
		</p>
		<div>
			<input type=\"radio\" id=\"cap_option\" name=\"cap_option\" value=\"0\" checked=\"checked\" />$action2 the following amount of $name:<br />
			<input type=\"text\" id=\"user_amount\" name=\"user_amount\" value=\"\" size=\"10\" /><br /><br />
			<input type=\"radio\" id=\"cap_option\" name=\"cap_option\" value=\"1\" />$action2 Full Capacity of Ship/Fleet(s) with $name
		</div>
		<div>
			<br /><br /><input type=\"submit\" name=\"submit_unload_resource_form\" value=\"Confirm!\" />
		</div>
		</form>
		";
	}
	return $form;
}

?>