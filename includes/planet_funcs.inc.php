<?php
/*
//
File:			planet_funcs.inc.php
Objective:		collection of functions for planet management
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	1 February 2004		Date Modified:	n/a

Copyright (c) 2003, 2004 by Pádraic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

# -----------------------
#functions for planets


#work out idle colonist count.
function idle_colonists() {
	global $planet;
	return $planet['colon'] - $planet['alloc_fight'] - $planet['alloc_elect'] - $planet['alloc_organ'];
}


// retrieve the planet
function get_planet() {
	global $user, $planet, $db_name;
	db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = '$user[on_planet]'");
	$planet = dbr();
}

#ensure a user can transfer stuff.
function conditions($user,$planet){
	global $min_before_transfer;
		if($user['signed_up'] > (time() - ($min_before_transfer * 86400)) && ($user['login_id'] != $planet['owner_id'] && $planet['fighters'] != 0) && $user['login_id'] != 1)
		{
			return 1;
		}
		else
		{
			return 0;
		}
}


function report_illegal_planet_attack($planet_attacked,$attackable_planet) {
	send_alert('Suspicious Planet Attack Attempted',
		array( "Attempted to Attack" => "Planet <b class=b1>$planet_attacked[planet_name]</b> with "
				. "$planet_attacked[fighters] " . ($planet_attacked['fighter_set'] ? "hostile" : "passive")
				. " fighters",
			"Only Allowed to Attack" => "Planet <b class=b1>$attackable_planet[planet_name]</b> with "
				. "$attackable_planet[fighters] " . ($attackable_planet['fighter_set'] ? "hostile" : "passive")
				. " fighters" ),
		"This can occur <b><i>legally</i></b> with some infrequent race conditions, but <b><i>repeated</i></b> "
		. "attempts usually indicate a player is using methods outside of the provided game interface to make "
		. "illegal or expedited attacks." );
}


function report_illegal_planet_claim($planet_claimed,$attackable_planet) {
	send_alert('Suspicious Planet Claim Attempted',
		array( "Attempted to Claim" => "Planet <b class=b1>$planet_claimed[planet_name]</b> with "
				. "$planet_claimed[fighters] " . ($planet_claimed['fighter_set'] ? "hostile" : "passive")
				. " fighters",
			"Needed to Attack" => "Planet <b class=b1>$attackable_planet[planet_name]</b> with "
				. "$attackable_planet[fighters] " . ($attackable_planet['fighter_set'] ? "hostile" : "passive")
				. " fighters" ),
		"This can occur <b><i>legally</i></b> with some infrequent race conditions, but <b><i>repeated</i></b> "
		. "attempts usually indicate a player is using methods outside of the provided game interface to make "
		. "illegal or expedited attacks." );
}


function illegal_planet_attack_warning($planet_attacked) {
	return "You may not attack planet <b class=b1>$planet_attacked[planet_name]</b> while other planets "
		. "in this system have more hostile fighters.<br />\n"
		. "Please ensure you are only using the provided interface to initiate your attacks.<br />\n";
}


function illegal_planet_claim_warning($planet_claimed) {
	return "You may not claim planet <b class=b1>$planet_claimed[planet_name]</b> while other planets "
		. "in this system have hostile fighters.<br />\n"
		. "Please ensure you are only using the provided interface to initiate your attacks.<br />\n";
}


// NB: to be rewritten and support fleet attacking!!!

//function used to damage ships once they have attacked a planet.
function do_damage($amount,$from,$target,$target_ship) {
	global $db_name,$error_str,$user,$user_ship,$yesbount;
	if($amount >= 1) {
		if($amount < $target_ship[fighters] + $target_ship[shields]) { #ship not destroyed
			dbn(__FILE__,__LINE__,"update ${db_name}_users set last_attack = ".time().", last_attack_by = '$from[login_name]' where login_id = '$target[login_id]'");
			$shield_damage = $amount;
			if($shield_damage > $target_ship[shields]) {
				$shield_damage = $target_ship[shields];
			}
			$amount -= $shield_damage;
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$amount' where login_id = '$from[login_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$amount' where login_id = '$target[login_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set fighters = fighters - $amount, shields = shields - $shield_damage where ship_id = '$target_ship[ship_id]'");
			return 0;
		} else { #ship destroyed
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$target_ship[fighters]', ships_killed = ships_killed + 1, ships_killed_points = ships_killed_points + $target_ship[point_value] where login_id = '$from[login_id]'");
			if($target_ship[shipclass] == 2) { #escape pod destroyed
				dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where ship_id = $target_ship[ship_id]"); // delete ship record
				dbn(__FILE__,__LINE__,"update ${db_name}_users set location = 1, ships_lost = ships_lost + 1, ships_lost_points = ships_lost_points + $target_ship[point_value], fighters_lost = fighters_lost + '$target_ship[fighters]', ship_id = 1,last_attack = ".time().", last_attack_by = '$from[login_name]' where login_id = '$target[login_id]'");
				$target[ship_id] = 1;
			} else { #normal ship destroyed
				//Minerals go to the system
				dbn(__FILE__,__LINE__,"update ${db_name}_stars set fuel = fuel + ".($target_ship[fuel]*(mt_rand(200,800)/1000)).", metal = metal + ".($target_ship[metal]*(mt_rand(400,900)/1000))." where star_id = $target_ship[location]");
				dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where ship_id = $target_ship[ship_id]"); // delete ship record


				if($target[ship_id] != $target_ship[ship_id]) {
					$new_ship_id = $target[ship_id];
				} else {
					//first check to see if user has other ships in same system. (will command one with most fighters).
					db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where login_id = '$target_ship[login_id]' && location = '$target_ship[location]' order by fighters desc limit 1");
					$other_ship = dbr();

					//if user has not got other ships in same system, will send command ship in a different system (preference being for ships that are not towing. if many not towing the one with the most fighters will be selected).
					if(!$other_ship) {
						db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where login_id = '$target_ship[login_id]' order by fighters desc limit 1");
						$other_ship = dbr();
					}
				}
				if($other_ship){
					$new_ship_id = $other_ship[ship_id];
				} else {
					$temp = create_escape_pod($target);			// build the escape pod
					$new_ship_id = $temp[ship_id];
					// run to random sector?
				}
				db(__FILE__,__LINE__,"select location from ${db_name}_ships where ship_id = '$new_ship_id'");
				$oth_loc = dbr();
				dbn(__FILE__,__LINE__,"update ${db_name}_users set ships_lost = ships_lost + 1, ships_lost_points = ships_lost_points + $target_ship[point_value], location='$oth_loc[location]', ship_id = '$new_ship_id', last_attack =".time().", last_attack_by = '$from[login_name]', fighters_lost = fighters_lost + '$target_ship[fighters]' where login_id = '$target[login_id]'");
				$target[ship_id] = $new_ship_id;
			}
			return 1;
		}
	}
	return 0;
}



//End of planet functions.
// ---------------------------------


?>