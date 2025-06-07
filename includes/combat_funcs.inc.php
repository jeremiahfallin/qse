<?php

function Generate_Attack($uship, $tship, $enemy_fleet_id) {
	global $user, $db_name, $user_fleet, $space_attack_turn_cost, $turns_before_attack, $turns_safe, $sure, $sudden_death, $alternate_bounty_sys, $output;

	$target_config = get_config($tship['ship_id']);
	$ship_config = get_config($uship['ship_id']);

	db(__FILE__,__LINE__,"select * from ${db_name}_users where login_id = '$tship[login_id]'");
	$target = dbr();

	if($user['turns'] < $space_attack_turn_cost) {
		if(empty($output)) {$output = "No combat has taken place as yet.";}
		print_page("Not enough Turns!","Sorry, you can't attack because you have less than <b>$space_attack_turn_cost</b> turns to use.<br /><h4>Combat Results up to time of this message</h4>$output");
	} elseif(($user['location'] != $tship['location'])) {
		if(empty($output)) {$output = "No combat has taken place as yet.";}
		print_page("Targeted Enemy not present!","The enemy fleet is no longer there! Try again when you are in the same system as it.<br /><h4>Combat Results up to time of this message</h4>$output");
	} elseif($user['turns_run'] < $turns_before_attack && $user['login_id'] != 1) {
		if(empty($output)) {$output = "No combat has taken place as yet.";}
		print_page("Still in safe turns!"," You can't attack during the first <b>$turns_before_attack</b> turns of having your account.<br /><h4>Combat Results up to time of this message</h4>$output");
	} elseif($target['turns_run'] < $turns_safe && $user['login_id'] != 1) {
		if(empty($output)) {$output = "No combat has taken place as yet.";}
		print_page("Enemy in safe turns!"," This Fleet is still under the inital <b>$turns_safe</b> turns protection period.<br /><h4>Combat Results up to time of this message</h4>$output");
	//} elseif($ship_config['na']) {
	//	if(empty($output)) {$output = "No combat has taken place as yet.";}
	//	print_page("Ship cannot attack!"," Your ship doesn't have the ability to attack.<br /><h4>Combat Results up to //  time of this message</h4>$output");
	} elseif($ship_config['po']) {
		if(empty($output)) {$output = "No combat has taken place as yet.";}
		print_page("Ship cannot attack"," Your ship doesn't have the ability to attack other ships.<br /><h4>Combat Results up to time of this message</h4>$output");
	} elseif($target['login_id'] == $user['login_id']) {
		if(empty($output)) {$output = "No combat has taken place as yet.";}
		print_page("Target is yourself!"," You may not attack yourself.<br /><h4>Combat Results up to time of this message</h4>$output");
	} elseif($user['ship_id'] == 1) {
		if(empty($output)) {$output = "No combat has taken place as yet.";}
		print_page("No ship left to attack!"," A <b class=b1>Ship Destroyed</b> may not attack, as it doesn't exist in the first place.<br /><h4>Combat Results up to time of this message</h4>$output");
	} elseif(($user['clan_id'] == $target['clan_id']) && $user['clan_id'] > 0) {
		if(empty($output)) {$output = "No combat has taken place as yet.";}
		print_page("Cannot attack clan-mate!","You may not attack a clan member.<br /><h4>Combat Results up to time of this message</h4>$output");
	} else {
		charge_turns($space_attack_turn_cost);

		$user_attack_bonus = CheckShip_ForAttack($uship['ship_id']);
		$user_defense_bonus = CheckShip_ForDefence($uship['ship_id']);
		$target_attack_bonus = CheckShip_ForAttack($tship['ship_id']);
		$target_defense_bonus = CheckShip_ForDefence($tship['ship_id']);


		// Generate attack Damage (includes advanced upgrades)
		if ((eregi("fr",$tship['config'])) && !(eregi("bs",$uship['config']))){
			$attack_damage = round($uship['fighters'] * .55);
		} elseif(eregi("bs",$uship['config'])) {
			$attack_damage = round($uship['fighters'] * .75);
		} else {
			$attack_damage = round($uship['fighters'] * .65);
		}
		$attack_damage += mt_rand(round(-$uship['fighters'] * .15),round($uship['fighters'] * .15) + 1);


		// prevent negative damage
		if(($attack_damage + $user_attack_bonus) > ($target_defense_bonus))
		{
			$attack_damage = $attack_damage + $user_attack_bonus - $target_defense_bonus;
		}
		else
		{
			$attack_damage = 0;
		}

		// generate counter damage
		if (eregi("fr",$tship['config']) || eregi("hs",$tship['config'])){
			$counter_damage = round($tship['fighters'] * .85);
		} else {
			$counter_damage = round($tship['fighters'] * .75);
		}
		$counter_damage += mt_rand(round(-$tship['fighters'] * .15),round($tship['fighters'] * .15) + 1);

		// prevent negative damage
		if(($counter_damage + $target_attack_bonus) > ($user_defense_bonus))
		{
			$counter_damage = $counter_damage + $target_attack_bonus - $user_defense_bonus;
		}
		else
		{
			$counter_damage = 0;
		}

		// protect the admin
		if ($target['login_id'] == 1)
		{
			$attack_damage = 0;
		}
		if ($user['login_id'] == 1)
		{
			$counter_damage = 0;
		}

		//-----------------------------------------------------------------------------------------
		// Deal damage against the enemy target ship
		//-----------------------------------------------------------------------------------------

		$store_output .= Attack_Ship($attack_damage, $user, $target, $uship, $tship);

		//-----------------------------------------------------------------------------------------
		// Deal counter-damage against the player ship
		//-----------------------------------------------------------------------------------------

		$store_output .= Attack_Ship($counter_damage, $target, $user, $tship, $uship);
	}
	$result = array();
	$result['stats'] = $store_output;
	//$result['flag'] = $attack_flag;
	//$result['enemy_fleet'] = $enemy_fleet_id;
	return $result;
}

// create the confirmation end options
function Include_ContinueConfirm($target) {
	global $_GET, $_POST, $output;
	$request_confirm = "<form name=cont_combat action=ship_combat.php method=post onSubmit='submitonce(this);'>";
	while (list($var, $value) = each($_GET)) {
		$request_confirm .= "<input type=hidden name=$var value='$value'>";
	}
	while (list($var, $value) = each($_POST)) {
		$request_confirm .= "<input type=hidden name=$var value='$value'>";
	}
	$request_confirm .= "<input type=hidden name=target value='$target'>";
	$request_confirm .= "<input type=hidden name=att_sure value='yes'>";
	$request_confirm .= "<input type=hidden name=ufleetid value='$ufleet_id'>";
	if(isset($output)) {
		$forward = urlencode($output);
		$request_confirm .= "<input type=hidden name=forward value='$forward'>";
	}
	$request_confirm .= "<input type=submit name=submit value=\"Continue Combat\"> <input type=\"Button\" width=\"30\" value=\"Disengage\" onclick=\"javascript: self.location='location.php'\"> </form>";
	return $request_confirm;
}








function Include_FirstConfirm($target) {
	global $_GET, $_POST;
	$request_confirm = "<form name=start_combat action=ship_combat.php method=post onSubmit='submitonce(this);'>";
	while (list($var, $value) = each($_GET)) {
		$request_confirm .= "<input type=hidden name=$var value='$value'>";
	}
	while (list($var, $value) = each($_POST)) {
		$request_confirm .= "<input type=hidden name=$var value='$value'>";
	}
	$request_confirm .= "<input type=hidden name=target value='$target'>";
	$request_confirm .= "<input type=hidden name=att_sure value='yes'>";
	$request_confirm .= "<input type=hidden name=ufleetid value='$ufleet_id'>";
	$request_confirm .= "<input type=submit name=submit value=\"Start Combat\"> <input type=\"Button\" width=\"30\" value=\"Disengage\" onclick=\"javascript: self.location='location.php'\"> </form>";
	return $request_confirm;
}










// function to damage a particular ship with a specified amount of damage
// damage is calculated externally and fed to this function
function Inflict_ShipDamage($damage, $attacker, $defender, $attack_ship, $defend_ship, $raid_flag) {
	global $db_name, $user;

	// update defender ship
	db(__FILE__,__LINE__,"select ship_id from ${db_name}_users where login_id = '$defender[login_id]'");
	$update = dbr();
	$defender['ship_id'] = $update['ship_id'];

	//no point in inflicting zero damage, eh? And remember the admin is invincible!
	if($damage > 0 || $defender['login_id'] != 1)
	{

		// step one: reduce damage by available shields
		// shields to be rewritten per early 2003 dev notes
		if($defend_ship['shields'] > 0)
		{
			$shield_damage = $damage;
			if($shield_damage > $defend_ship['shields'])
			{
				$shield_damage = $defend_ship['shields'];
				$damage -= $shield_damage;
			}
			elseif($shield_damage <= $defend_ship['shields'])
			{
				$damage = 0;
			}
		}
		// at this stage we may have two values, $shield_damage (for damage to shield) and $damage, excess damage to fighters

		//step two: ensure raiders do not destroy the ship entirely, we only need it to be weakened for raiding
		if($raid_flag == 1 && ($damage >= $defend_ship['fighters'] || $damage < 0))
		{
			if($defend_ship['fighters'] > 10)
			{
				//fix for 0 fighters
				$damage = $defend_ship['fighters'] - mt_rand(1,9);
			}
			else
			{
				$damage = 0;
			}
			//all offense/defense upgrades are destroyed during raid
			db(__FILE__,__LINE__,"select sql_name from upgrade_list where type = 6 or type = 7");
			$defend_weapons = dbr();
			$return_slot_count = 0;
			foreach($defend_weapons as $key=>$val)
			{
				db(__FILE__,__LINE__,"select ".$val." from ${db_name}_upgrade_units where ship_id = '$defend_ship[ship_id]'");
				$upg_cnt = dbr();
				$return_slot_count += $upg_cnt[$val];
				dbn(__FILE__,__LINE__,"update ${db_name}_upgrade_units set ".$val." = 0 where ship_id = '$defend_ship[ship_id]'");
			}
			// add back upgrade slots for each upgrade destroyed
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set upgrades = upgrades + '$return_slot_count' where ship_id = '$defend_ship[ship_id]'");
		}


		//step three: inflict damage to fighters and destroy ship if necessary (not destroyed if being raided!)
		if($damage > $defend_ship['fighters'] && $raid_flag != 1)
		{

			//since no raiding and damage is obviously too much for ship to survive - we'll destroy it
			dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where ship_id = '$defend_ship[ship_id]'");
			//update attackers stats
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$defend_ship[fighters]', ships_killed = ships_killed + '1', ships_killed_points = ships_killed_points + '$defend_ship[point_value]' where login_id = '$attacker[login_id]'");
			//update defenders stats
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$defend_ship[fighters]', ships_lost = ships_lost + '1', ships_lost_points = ships_lost_points + '$defend_ship[point_value]' where login_id = '$defender[login_id]'");

			//perform special actions like switching command/or creating escape pod if no ships left
			if($defend_ship['ship_categ'] == 0)
			{
				//ship_categ value of 0 reserved for escape pod and ship destroyed - in this case only an escape pod could possibly have been attacked - player just lost their final refuge :)
				dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '1', ship_id = '1', last_attack = ".time().", last_attack_by = '$attacker[login_name]' where login_id = '$defender[login_id]'");
				//delete all player fleets
				dbn(__FILE__,__LINE__,"delete from ${db_name}_fleets where login_id = '$defender[login_id]'");

				//need code to completely pay off bounty

				return 2;
			}
			else
			{

				//otherwise a ship other than escape pod was destroyed
				//switch ship if defender's command ship was destroyed or escape pod if no ships left
				if($defend_ship['ship_id'] == $defender['ship_id'])
				{
					Combat_SwitchShips($user,$defender,$defend_ship,$attacker);
				}
				return 1;
			}
		}
		else
		{
			//ship not destroyed - just damaged
			dbn(__FILE__,__LINE__,"update ${db_name}_users set last_attack = ".time().", last_attack_by = '$attacker[login_name]' where login_id = '$defender[login_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set fighters = fighters - '$damage', shields = shields - '$shield_damage' where ship_id = '$defend_ship[ship_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$damage' where login_id = '$defender[login_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$damage' where login_id = '$attacker[login_id]'");

			//need code to commit a raid attempt if enabled
			if($raid_flag == 1)
			{
				//do raid
				return 3;
			}
			return 0;
		}
	}
}



// function to put player in command of another ship if current destroyed in combat
function Combat_SwitchShips(&$user,$defender,$defend_ship,$attacker) {
	global $db_name, $alternate_bounty_sys, $user_fleet;

	// check for alternate ships in current fleet
	//$nf_flag simply accesses each fleet switch option after the prior fails - set to 1 when a switch is a success
	$nf_flag = 0;
	db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where fleet_id = '$defend_ship[fleet_id]' and location = '$defend_ship[location]' and ship_id != '$defend_ship[ship_id]' order by fighters desc limit 1");
	$newship = dbr();
	if(!empty($newship))
	{
		// set new user ship
		dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$newship[ship_id]', last_attack = ".time().", last_attack_by = '$attacker[login_name]' where login_id = '$defender[login_id]'");
		// set fleet command ship to user ship
		dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ship_id = '$newship[ship_id]' where login_id = '$defender[login_id]' and fleet_id = '$defend_ship[fleet_id]'");
		if($defender['login_id'] == $user['login_id'])
		{
			$user['ship_id'] = $newship['ship_id'];
		}
	}
	else
	{
		// first of all since our current fleet is empty lets keep useless db queries to a minimum and unset ship-id
		dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ship_id = 0 where fleet_id = '$defend_ship[fleet_id]' and login_id = '$defender[login_id]'");

		// apparently no ships from the same fleet exist - try another fleet in same location
		db2(__FILE__,__LINE__,"select fleet_id,ship_id from ${db_name}_fleets where login_id = '$defender[login_id]' and location = '$defend_ship[location]' and ship_id != 0 and fleet_id != '$defend_ship[fleet_id]'");
		$newfleet = dbr2();
		if(!empty($newfleet))
		{
			//echo("A BUG - THIS IS WRONG AS NO SHIPS IN TESTING PRESENT AT THIS LOC!!!<p>");
			while($newfleet) {
				db(__FILE__,__LINE__,"select count(ship_id) as ships from ${db_name}_ships where fleet_id = '$newfleet[fleet_id]'");
				$countships = dbr();
				if($countships['ships'] > 0)
				{
					dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$newfleet[ship_id]', last_attack = ".time().", last_attack_by = '$attacker[login_name]' where login_id = '$defender[login_id]'");
					if($defender['login_id'] == $user['login_id'])
					{
						$user['ship_id'] = $newfleet['ship_id'];
					}
					$nf_flag = 1;
					break;
				}
				$newfleet = dbr2();
			}
		}


		if($nf_flag == 0)
		{
			//echo("no luck here!!! - checking fleets in other location!<p>");
			// what? no fleets in same location! - Try somewhere else then...
			db2(__FILE__,__LINE__,"select fleet_id, ship_id, location from ${db_name}_fleets where login_id = '$defender[login_id]' and ship_id != 0 and fleet_id != '$defend_ship[fleet_id]'");
			$newfleet = dbr2();
			if(!empty($newfleet))
			{
				while($newfleet) {
					$ct++;
					//print_r($newfleet); //echo("---$ct<p>");
					db3(__FILE__,__LINE__,"select count(ship_id) as ships from ${db_name}_ships where fleet_id = '$newfleet[fleet_id]'");
					$countships = dbr3();
					if($countships['ships'] > 0)
					{
						dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$newfleet[ship_id]', last_attack = ".time().", last_attack_by = '$attacker[login_name]', location = '$newfleet[location]' where login_id = '$defender[login_id]'");
						if($defender['login_id'] == $user['login_id'])
						{
							$user['ship_id'] = $newfleet['ship_id'];
							$user['location'] = $newfleet['location'];
						}
						//echo("COMMAND PASSED BUT STILL THE EP PERSISTS!!!<P>");
						$nf_flag = 1;
						break;
					}
					$newfleet = dbr2();
				}
			}
		}

		//echo("nf_flag = ".$nf_flag."<p>");

		if($nf_flag == 0)
		{
			//echo("heading for an escape pod!!!<p>");
			// no fleets anywhere!!! In this case player will need an Escape Pod!
			Provide_EscapePod($defender);
			// check if bounty to be paid over for destruction of all players fleets
			if($defender['bounty'] > 0 && $alternate_bounty_sys == 1) {
				send_message($attacker['login_id'],"You have claimed 50% of the <b>$defender[bounty]</b> Credit bounty that was on <b class=b1>$defender[login_name]</b>s head for destroying his/her fleet and consigning them to an <b class=b1>Escape Pod</b>.");
				post_news("50% of the <b>$defender[bounty]</b> bounty on <b class=b1>$defender[login_name]</b> has been claimed by <b class=b1>$from[login_name]</b> for the destruction of all their fleets. The player remains at large in an <b class=b1>Escape Pod</b> with the remaining bounty on his head.");
				$bounty_a = round($defender['bounty'] * .5) - 1; //idiot error in here before that prevented bounty payment
				dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + '$bounty_a' where login_id = '$attacker[login_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = bounty - '$bounty_a' where login_id = '$defender[login_id]'");
			}
		}
	}
}




// function to provide a player with an escape pod
function Provide_EscapePod($defender) {
	global $db_name;
	$rand_star = random_system_num();
	// create a fleet for the escape pod (special num 0)
	dbn(__FILE__,__LINE__,"insert into ${db_name}_fleets (fleet_name, fleet_num, login_id, login_name, location) values ('Escape Pod','0','$defender[login_id]','$defender[login_name]','$rand_star')");
	db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where login_id = '$defender[login_id]' and fleet_num = 0");
	$flt = dbr();
	// create the escape pod
	$ship_id = Add_EP_ToDatabase($flt,$defender,$rand_star);
	dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '$rand_star', ship_id ='$ship_id', last_attack = ".time().", last_attack_by = '$attacker[login_name]' where login_id = '$defender[login_id]'");
	dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ship_id = '$ship_id' where login_id = '$defender[login_id]' and fleet_id = '$flt[fleet_id]'");
}







// three guesses for what this does...
function Add_EP_ToDatabase($flt,$user,$rand_star) {
	global $db_name;
	db(__FILE__,__LINE__,"select * from ship_types where ship_categ = 0 and type = 'Escape Pod'");
	$ship_stats = dbr();
	$ship_name_sql = "Escape Pod";
		$q_string = "insert into ${db_name}_ships (";
		$q_string = $q_string . "ship_name,login_id,login_name,clan_id,ship_categ,shipclass,class_name,class_name_abbr,fighters,max_fighters,max_shields,cargo_bays,mine_rate_metal,mine_rate_fuel,config,size,upgrades,move_turn_cost,disp_rank,point_value,num_ot,num_dt,shield_1,shield_2,shield_3,shield_4,shield_5,shield_charge,max_charge,shield_prts,fleet_id, timestamp, location";
		$q_string = $q_string . ") values(";
		$q_string = $q_string . "'$ship_name_sql','$user[login_id]','$user[login_name]','$user[clan_id]',$ship_stats[ship_categ],'$ship_stats[type_id]','$ship_stats[name]','$ship_stats[class_abbr]','$ship_stats[fighters]','$ship_stats[max_fighters]','$ship_stats[max_shields]','$ship_stats[cargo_bays]','$ship_stats[mine_rate_metal]','$ship_stats[mine_rate_fuel]','$ship_stats[config]','$ship_stats[size]','$ship_stats[upgrades]','$ship_stats[move_turn_cost]','$disp_rank',$ship_stats[point_value],'$ship_stats[num_ot]','$ship_stats[num_dt]','$ship_stats[shield_1]','$ship_stats[shield_2]','$ship_stats[shield_3]','$ship_stats[shield_4]','$ship_stats[shield_5]','$ship_stats[shield_charge]','$ship_stats[max_charge]','$ship_stats[shield_prts]','$flt[fleet_id]','".time()."','$rand_star')";
	dbn(__FILE__,__LINE__,$q_string);
	db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where login_id = '$user[login_id]' order by timestamp desc limit 1");
	$new_id = dbr();
	$new_ship_id = $new_id['ship_id'];
	return $new_ship_id;
}




// this function calls upon a function to generate an attack. It also uses the result to create feedback to the player,
// posts to News, and messages to the defender.
function Attack_Ship($attack_damage, $attacker, $defender, $attack_ship, $defend_ship) {
	global $db_name, $sudden_death, $alternate_bounty_sys;

	$store['output'] .= "<br /><b>$attack_damage</b> damage to <b class=b1>$defender[login_name]</b>s $defend_ship[class_name].";
	$result = Inflict_ShipDamage($attack_damage, $attacker, $defender, $attack_ship, $defend_ship);
	//enemy escape pod destroyed
	if($result == 1)
	{
		// escape pod was destroyed
		if($defend_ship['ship_categ'] == 0)
		{
			$store['output'] .= "<p><b class=\"b1\">$attacker[login_name]</b> successfully destroyed <b class=\"b1\">$defender[login_name]</b>s Escape Pod.</p>";
			// check for sudden_death
			if($sudden_death)
			{
				$store['output'] .= "<p>As the game is in Sudden Death (SD), <b class=\"b1\">$defender[login_name]</b> is out of the game permanently.</p>";
				post_news("<b class=\"b1\">$defender[login_name]</b> has been completely killed, and is out of the game permanently.");
			}
			// check for bounties - TODO - stick in function
			if($defender['bounty'] > 0 && $alternate_bounty_sys == 0)
			{
				$store['output'] .= "<p>The <b>$defender[bounty]</b> Credit bounty that was on <b class=\"b1\">$defender[login_name]</b>s head has been claimed by <b class=\"b1\">$attacker[login_name]</b></p>";
				post_news("The <b>$defender[bounty]</b> bounty on <b class=\"b1\">$defender[login_name]</b> has been claimed by <b class=b1>$attacker[login_name]</b>");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + '$defender[bounty]' where login_id = '$attacker[login_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$defender[login_id]'");
			}
			elseif ($defender['bounty'] > 0)
			{
				$store['output'] .= "<p><b class=\"b1\">$attacker[login_name]</b> has claimed the remaining <b>$defender[bounty]</b> Credit bounty that was on <b class=\"b1\">$defender[login_name]</b>s head after the destruction of all their ships.</p>";
				post_news("The <b>$defender[bounty]</b> Credit bounty remaining on <b class=b1>$defender[login_name]</b>\'s head after the destruction of all their ships has been claimed by<b class=\"b1\"> $attacker[login_name]</b>");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + '$defender[bounty]' where login_id = '$attacker[login_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$defender[login_id]'");
			}
		}
		else
		{
			$store['output'] .= "<p><b class=\"b1\">$attacker[login_name]</b> destroyed <b class=\"b1\">$defender[login_name]</b>s $defend_ship[class_name].</p>";
		}
	}
	// enemy ship destroyed
	elseif($result == 2)
	{
		$store['output'] .= "<p><b class=\"b1\">$attacker[login_name]</b> destroyed <b class=\"b1\">$defend_ship[login_name]</b>s $defend_ship_ship[class_name].</p>";
		$store['output'] .= "<p><b class=\"b1\">$defend_ship[login_name]</b> ejected in a Escape Pod which immediately entered SubSpace to an unknown location.</p>";
		if($attacker['location'] == 1)
		{
			post_news("<b class=\"b1\">$attacker[login_name]</b> destroyed <b class=\"b1\">$defender[login_name]</b>s $defend_ship[class_name] in the <b>Sol</b> System.");
		}
		else
		{
			post_news("<b class=b1>$attacker[login_name]</b> destroyed <b class=b1>$defender[login_name]</b>s $defend_ship[class_name].");
		}
	}
	elseif($result == 3)
	{
		//create stuff for post raid!
	}
	return $store['output'];
}

?>