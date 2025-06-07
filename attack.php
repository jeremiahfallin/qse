<?php
require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

require_once("includes/planet_funcs.inc.php");

sudden_death_check($user);
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
		$mr = dbr();
		$max_races = $mr['value'];
// seed random number generator
mt_srand((double)microtime()*1000000);

unset( $attackable_planet );
if(isset($driver) || isset($orbit) || isset($quark) || isset($terra)) {
	db(__FILE__,__LINE__,attack_planet_check($db_name,$user));
	$attackable_planet=dbr();
}

// Mass Driver - B5 Game - Required md
if(isset($driver)) {
	db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = '$planet_num'");
	$planet_info = dbr();
	$md_turns = $max_turns - min(20,$hourly_turns); // Leave a small amount of turns to move to/from attack SS
	$user_ship_config = get_config($user[ship_id]);
	if($user[turns] < $md_turns) {
		print_page("Mass Driver","Sorry, you can't use the Mass Driver, as you do not have enough turns. <br />To fire this weapon you need <b>$md_turns</b> turns, and you only have <b>$user[turns]</b>.");
	} elseif($user[location] != $planet_info[location]) {
		print_page("Mass Driver","The planet <b class=b1>$planet_info[planet_name]</b> is not in this system.");
	} elseif($user[turns_run] < $turns_before_planet_attack && $user[login_id] != 1) {
		print_page("Mass Driver","You can't attack during the first <b>$turns_before_planet_attack</b> turns of having your account.");
	} elseif($planet_info[owner_id] == 1) {
		print_page("Mass Driver","This is an <b class=b1>Admin </b>planet, and cannot be attacked.");
	} elseif(!$planet_info[fighters]) {
		print_page("Mass Driver","There is no point in attacking this planet with the Mass Driver, as there are no fighters left on it to destroy.<p><a href=planet.php?planet_id=$planet_info[planet_id]>Land</a> on it, and then claim it to make it yours.");
	} elseif(!$user_ship_config[md]) {
		print_page("Mass Driver","This ship does not have a Mass Driver on it.");
	} elseif($enable_superweapons == 0) {
		print_page("Mass Driver","The admin has disabled the use of Mass Drivers.");
	} elseif(!empty($attackable_planet) && ($attackable_planet['planet_id'] != $planet_info['planet_id'])) {
		report_illegal_planet_attack($planet_info,$attackable_planet);
		print_page('Mass Driver',illegal_planet_attack_warning($planet_info));
	} elseif($sure != 'yes') {
		get_var('Mass Driver','attack.php',"Are you sure you want to use the Mass Driver against <b class=b1>$planet_info[planet_name]</b>?<br>",'sure','yes');
	} else {
		charge_turns($md_turns);
		$md_damage = mt_rand(round($planet_info[fighters] * 0.4), round($planet_info[fighters] * 0.6));

		dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighters = fighters - '$md_damage' where planet_id = $planet_num");
		$planet_info[fighters] -= $md_damage;
		post_news("<b class=b1>$user[login_name]</b> fired a Mass Driver at <b class=b1>$planet_info[planet_name]</b>.");
		if($planet_info[fighters] < 1) {
			send_message($planet_info[owner_id],"The <b class=b1>$user_ship[ship_name]</b> fired at your planet <b class=b1>$planet_info[planet_name]</b> with a Mass Driver, doing <b>$md_damage</b> damage, and completely destroying all the planets defences.", 3);
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighters = 0 where planet_id = $planet_num");
			$f_killed = $planet_info[fighters];
			$planet_info[fighters] = 0;
			$out_str .= "You have done <b>$md_damage</b> damage to planet <b class=b1>$planet_info[planet_name]</b>, using <b>$md_turns</b> turns. <p>You completly destroyed all defences on <b class=b1>$planet_info[planet_name]</b>. <br /> - <a href=planet.php?planet_id=$planet_info[planet_id]>Land</a><br />";
		} else {
			send_message($planet_info[owner_id],"The <b class=b1>$user_ship[ship_name]</b> fired at your planet <b class=b1>$planet_info[planet_name]</b> with a Mass Driver, doing <b>$md_damage</b> damage. There was no way for your planetary defences to retaliate.",3);
			$f_killed = $md_damage;
			$out_str .= "You have done <b>$md_damage</b> damage to planet <b class=b1>$planet_info[planet_name]</b>, at a cost of <b>$md_turns</b> turns";
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$f_killed' where login_id = $user[login_id]");
		dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$f_killed' where login_id = $planet_info[owner_id]");
		print_page("Mass Driver",$out_str);
	}
}

// Orbitital Bombardment
if(isset($orbit)) {
	print_page('Orbital Bombardment','<p>Orbital Bombardment is currently disabled and can never be used.</p>');
	db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = '$planet_num'");
	$planet_info = dbr();
	$ob_turns = 75;
	$user_ship_config = get_config($user[ship_id]);
	if($user[turns] < $ob_turns) {
		print_page("Orbitial Bombardment","Sorry, you can't Bombard this planet, as you do not have enough turns. <br />To fire, you need <b>$ob_turns</b> turns, and you only have <b>$user[turns]</b>.");
	} elseif($planet_info[planet_id] <= 2) {
		print_page("Orbitial Bombardment","This weapon may not be fired at <b class=b1>a Homeworld</b>. In fact by trying, you've probably broken a couple of laws. So scram.");
	} elseif($user[location] != $planet_info[location]) {
		print_page("Orbitial Bombardment","The planet <b class=b1>$planet_info[planet_name]</b> is not in this system.");
	} elseif($user[turns_run] < $turns_before_planet_attack && $user[login_id] != 1) {
		print_page("Orbitial Bombardment","You can't attack during the first <b>$turns_before_planet_attack</b> turns of having your account.");
	} elseif($planet_info[owner_id] == 1) {
		print_page("Orbitial Bombardment","This is an <b class=b1>Admin </b>planet, and cannot be attacked.");
	} elseif(!$planet_info[fighters]) {
		print_page("Orbitial Bombardment","There is no point in attacking this planet by Bombardment, as there are no fighters left on it to destroy.<p><a href=planet.php?planet_id=$planet_info[planet_id]>Land</a> on it, and then claim it to make it yours.");
	} elseif(!$user_ship_config[ob]) {
		print_page("Orbitial Bombardment","This ship does not have a Bombardment Centre on it.");
	} elseif(!empty($attackable_planet) && ($attackable_planet['planet_id'] != $planet_info['planet_id'])) {
		report_illegal_planet_attack($planet_info,$attackable_planet);
		print_page('Orbital Bombardment',illegal_planet_attack_warning($planet_info));
	} elseif($sure != 'yes') {
		get_var('Orbitial Bombardment','attack.php',"Are you sure you want to use Orbitial Bombardment against <b class=b1>$planet_info[planet_name]</b>?<br>",'sure','yes');
	}else {
		charge_turns($ob_turns);
		$nnntotalturns = $ob_turns;
		$ob_damage = 0;
		db2(__FILE__,__LINE__,"select ship_id, size from ${db_name}_ships where login_id = {$user['login_id']} and location = {$user['location']}");
		$ob_damage = 0;
		while($shiper = dbr2())
		{
		$user_shiper_config = get_config($shiper['ship_id']);
			$nnturncost = 10 * ($shiper['size'] * 2);
			if($user_shiper_config['ob'] == 1 and $user[turns] >= $nnturncost)
			{
				charge_turns($nnturncost);
				$nnntotalturns = $nnntotalturns + $nnturncost;
				$chance = (rand(0, 1000) / $nnturncost / $shiper['size'] / 10);
				$ob_damage += round(mt_rand(0,500) * $shiper['size'] * $chance);
			}
		}

		dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighters = fighters - '$ob_damage' where planet_id = $planet_num");
		$planet_info[fighters] -= $ob_damage;
		post_news("<b class=b1>$user[login_name]</b> attacked <b class=b1>$planet_info[planet_name]</b>by Orbitial bombardment.");
		if($planet_info[fighters] < 1) {
			send_message($planet_info[owner_id],"The <b class=b1>$user_ship[ship_name]</b> attacked your planet <b class=b1>$planet_info[planet_name]</b> by means of Orbital Bombardment, doing <b>$ob_damage</b> damage, and completely destroying all the planets defences.",3);
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighters = 0 where planet_id = $planet_num");
			$f_killed = $planet_info[fighters];
			$planet_info[fighters] = 0;
			$out_str .= "You have done <b>$ob_damage</b> damage to planet <b class=b1>$planet_info[planet_name]</b>, using <b>$nnntotalturns</b> turns. <p>You completly destroyed all defences on <b class=b1>$planet_info[planet_name]</b>. <br /> - <a href=planet.php?planet_id=$planet_info[planet_id]>Land</a><br />";
		} else {
			send_message($planet_info[owner_id],"The <b class=b1>$user_ship[ship_name]</b> attacked your planet <b class=b1>$planet_info[planet_name]</b> by means of Bombardment, doing <b>$ob_damage</b> damage. There was no way for your planetary defences to retaliate.", 3);
			$f_killed = $ob_damage;
			$out_str .= "You have done <b>$ob_damage</b> damage to planet <b class=b1>$planet_info[planet_name]</b>, at a cost of <b>$nnntotalturns</b> turns";
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$f_killed' where login_id = $user[login_id]");
		dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$f_killed' where login_id = $planet_info[owner_id]");
		print_page("Orbitial Bombardment",$out_str);
	}
}

// quark disrupter.
if(isset($quark)) {
	db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = '$planet_num'");
	$planet_info = dbr();
	$sv_turns = max(30,$planet_attack_turn_cost); // Don't allow cheap quarks if planet attacks are expensive
	$user_ship_config = get_config($user[ship_id]);
	if($user[turns] < $sv_turns) {
		print_page("Quark Displacer","Sorry, you can't use the Quark Displacer, as you do not have enough turns. <br />To fire this weapon you need <b>$sv_turns</b> turns, and you only have <b>$user[turns]</b>.");
	} elseif($user[location] != $planet_info[location]) {
		print_page("Quark Displacer","The planet <b class=b1>$planet_info[planet_name]</b> is not in this system.");
	} elseif($user[turns_run] < $turns_before_planet_attack && $user[login_id] != 1) {
		print_page("Quark Displacer","You can't attack during the first <b>$turns_before_planet_attack</b> turns of having your account.");
	} elseif($planet_info[owner_id] == 1) {
		print_page("Quark Displacer","This is an <b class=b1>Admin </b>planet, and cannot be attacked.");
	} elseif(!$planet_info[fighters]) {
		print_page("Quark Displacer","There is no point in attacking this planet with the Quark Displacer, as there are no fighters left on it to destroy.<p><a href=planet.php?planet_id=$planet_info[planet_id]>Land</a> on it, and then claim it to make it yours.");
	} elseif(!$user_ship_config[sv]) {
		print_page("Quark Displacer","This ship does not have a Quark Displacer on it.");
	} elseif(!empty($attackable_planet) && ($attackable_planet['planet_id'] != $planet_info['planet_id'])) {
		report_illegal_planet_attack($planet_info,$attackable_planet);
		print_page('Quark Displacer',illegal_planet_attack_warning($planet_info));
	} elseif($sure != 'yes') {
		get_var('Quark Displacer','attack.php',"Are you sure you want to use the Quark Displacer against <b class=b1>$planet_info[planet_name]</b>?",'sure','yes');
	}	else {
		charge_turns($sv_turns);

		$sv_damage = mt_rand(750,1500);

		dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighters = fighters - '$sv_damage' where planet_id = $planet_num");
		$planet_info[fighters] -= $sv_damage;
		post_news("<b class=b1>$user[login_name]</b> fired a Quark Displacer at <b class=b1>$planet_info[planet_name]</b>.");
		if($planet_info[fighters] < 1) {
			send_message($planet_info[owner_id],"The <b class=b1>$user_ship[ship_name]</b> fired at your planet <b class=b1>$planet_info[planet_name]</b> with a Quark Displacer, doing <b>$sv_damage</b> damage, and completely destroying all the planets defences.",3);
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighters = 0 where planet_id = $planet_num");
			$f_killed = $planet_info[fighters];
			$planet_info[fighters] = 0;
			$out_str .= "You have done <b>$sv_damage</b> damage to planet <b class=b1>$planet_info[planet_name]</b>, using <b>$sv_turns</b> turns. <p>You completly destroyed all defences on <b class=b1>$planet_info[planet_name]</b>. <br /> - <a href=planet.php?planet_id=$planet_info[planet_id]>Land</a><br />";
		} else {
			send_message($planet_info[owner_id],"The <b class=b1>$user_ship[ship_name]</b> fired at your planet <b class=b1>$planet_info[planet_name]</b> with a Quark Displacer, doing <b>$sv_damage</b> damage. There was no way for your planetary defences to retaliate.",3);
			$f_killed = $sv_damage;
			$out_str .= "You have done <b>$sv_damage</b> damage to planet <b class=b1>$planet_info[planet_name]</b>, at a cost of <b>$sv_turns</b> turns";
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$f_killed' where login_id = $user[login_id]");
		dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$f_killed' where login_id = $planet_info[owner_id]");
		print_page("Quark Disrupter",$out_str);
	}
}

// terra maelstrom
if(isset($terra)) {
	db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = '$planet_num'");
	$planet_info = dbr();
	$sw_turns = 100; //was 50
	$base_percent = 2;
	$user_ship_config = get_config($user[ship_id]);
	if($user[turns] < $sw_turns) {
		print_page("Terra Maelstrom","Sorry, you can't use the Terra Maelstrom, as you do not have enough turns. <br />To fire this weapon you need at <b class=b1>least</b> <b>$sw_turns</b> turns. You have <b>$user[turns]</b>.");
	} elseif($user[location] != $planet_info[location]) {
		print_page("Terra Maelstrom","The planet <b class=b1>$planet_info[planet_name]</b> is not in this system.");
	} elseif($user[turns_run] < $turns_before_planet_attack && $user[login_id] != 1) {
		print_page("Terra Maelstrom","You can't attack planets during the first <b>$turns_before_planet_attack</b> turns of having your account.");
	} elseif($planet_info[owner_id] == 1) {
		print_page("Terra Maelstrom","This is an <b class=b1>Admin</b> planet, and cannot be attacked.");
	} elseif(!$planet_info[fighters]) {
		print_page("Terra Maelstrom","There is no point in attacking this planet with the Terra Maelstrom, as there are no fighters left on it to destroy.<p><a href=planet.php?planet_id=$planet_info[planet_id]>Land</a> on it, and then claim it to make it yours.");
	} elseif($enable_superweapons == 0) {
		print_page("Terra Maelstrom","The admin has disabled the use of Terra Maelstroms.");
	} elseif(!isset($user_ship_config[sw])) {
		print_page("Terra Maelstrom","You can't fire what you don't have...");
	} elseif(!empty($attackable_planet) && ($attackable_planet['planet_id'] != $planet_info['planet_id'])) {
		report_illegal_planet_attack($planet_info,$attackable_planet);
		print_page('Terra Maelstrom',illegal_planet_attack_warning($planet_info));
	}	else {

		// base amount of damage, done for the X turns.
		$sq_damage = mt_rand(4000,6000);

		// if planet has more than that many fighters, use an alternate system:
		if($planet_info[fighters] > $sq_damage && $user[turns] > $sw_turns){

			// work out how many fighters may be killed in one shot (between 55 and 75 percent) as a max.
			$max_fig_kills = round(($planet_info[fighters] / 100) * mt_rand(55,75));

			// work out based on the max fighters that can be killed the num of fighters killed per turn.
			$killed_per_turn = round($max_fig_kills / $max_turns);

			// damage done is based on num turns used times fighters killed per turn used. simple
			$damage_done = round($killed_per_turn * $user[turns]);

			// random factor. allows for an increased randomness in damage done.
			$damage_done += round(mt_rand(-$damage_done * .05,$damage_done * .05));

		}

		// damage done by alternate system isn't as much as using the sure fire method (fixed damage for fixed turns)
		if($sq_damage > $damage_done){
			$sw_damage = $sq_damage;
			$turn_cost = $sw_turns;
		} else { // damage done by alternate method is greater than normal damage done.
			$turn_cost = $user[turns];
			$sw_damage = $damage_done;
		}

		if($sure != 'yes') {
			get_var('Terra Maelstrom','attack.php',"Are you sure you want to use the Terra Maelstrom against the planet <b class=b1>$planet_info[planet_name]</b>?<br /><br />This will use <b>$turn_cost</b> turns. Terra Maelstroms are highly unpredictable weapons capable of destroying between 55% and 75% (with MAX TURNS) of a planets fighters by creating an immense energy flux that basically causes affected fighters to detonate instantaneously. It will use all your turns, but the fewer turns you have below your maximum, the less fighters will be killed.",'sure','yes');
		}


		charge_turns($turn_cost);

		dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighters = fighters - '$sw_damage' where planet_id = $planet_num");
		$planet_info[fighters] -= $sw_damage;
		post_news("<b class=b1>$user[login_name]</b> fired a Terra Maelstrom at <b class=b1>$planet_info[planet_name]</b>.");
		if($planet_info[fighters] < 1) {
			send_message($planet_info[owner_id],"The <b class=b1>$user_ship[ship_name]</b> fired at your planet <b class=b1>$planet_info[planet_name]</b> with a Terra Maelstrom, doing <b>$sw_damage</b> damage, and completely destroying all the planets defences.",3);
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighters = 0 where planet_id = $planet_num");
			$f_killed = $planet_info[fighters];
			$planet_info[fighters] = 0;
			$out_str .= "You have done <b>$sw_damage</b> damage to planet <b class=b1>$planet_info[planet_name]</b>, using <b>$turn_cost</b> turns. <p>You completly destroyed all defences on <b class=b1>$planet_info[planet_name]</b>. <br /> - <a href=planet.php?planet_id=$planet_info[planet_id]>Land</a><br />";

		} else {
			send_message($planet_info[owner_id],"The <b class=b1>$user_ship[ship_name]</b> fired at your planet <b class=b1>$planet_info[planet_name]</b> with a Terra Maelstrom, doing <b>$sw_damage damage</b>. There was no way for your planetary defences to retaliate.",3);
			$f_killed = $sw_damage;
			$planet_info[fighters] -= $sw_damage;
			$out_str .= "You have done <b>$sw_damage</b> damage to planet <b class=b1>$planet_info[planet_name]</b>, at a cost of <b>$turn_cost</b> turns";
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$f_killed' where login_id = $user[login_id]");
		dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$f_killed' where login_id = $planet_info[owner_id]");
		print_page("Terra Maelstrom",$out_str);
	}
}

// ----------------------
// ship to ship attacking
// ----------------------

// determine if attacking is allowed, or attacking in sol is allowed.
if(!$flag_space_attack) {
  print_page("Attack"," The Admin has disabled ship to ship attacks.");
} elseif($flag_sol_attack == 0 && $user[location] <= $max_races && $user[login_id] != 1){
	print_page("Attack","The Admin has disabled all forms of attack in the Homeworld Systems.");
}

if(isset($target)) {
  $target_config = get_config($target);
  $ship_config = get_config($user[ship_id]);
  // Get target records
  db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$target'");
  $target_ship = dbr();
  db(__FILE__,__LINE__,"select * from ${db_name}_users where login_id = '$target_ship[login_id]'");
  $target = dbr();

	// check if there are defending battleships in the same fleet. If yes, they may intercept the attack.
	if (!eregi("oo", $target_ship[config]) && !eregi("bs", $target_ship[config])) { // check if battleship or flagship
		db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where fleet_id = '$target_ship[fleet_id]' && (config LIKE '%oo%' || config LIKE '%bs%') && location = '$target_ship[location]' && login_id = '$target[login_id]'");
		$defender_cnt = dbr();
		$intercept = mt_rand(1, 10);
		if ($defender_cnt[0] > 0 && $intercept > 4) {
			db(__FILE__,__LINE__,"select * from ${db_name}_ships where fleet_id = '$target_ship[fleet_id]' && (config LIKE '%oo%' || config LIKE '%bs%') && location = '$target_ship[location]' && login_id = '$target[login_id]' order by fighters desc limit 1");
			$defends = dbr();
			$target_ship = $defends;
			$error_str .= "Your target is being defended by a Battleship in the same fleet. This Battleship has intercepted your attempted attack!<p>";
		}
	}
	// check and make sure attack is possable
	if($user[turns] < $space_attack_turn_cost) {
		$error_str = "Sorry, you can't attack because you have less than <b>$space_attack_turn_cost</b> turns to use.";
	} elseif(($user[location] != $target_ship[location])) {
		$error_str = "That ship is no longer there. Try again when you are in the same system as it.";
	} elseif($user[turns_run] < $turns_before_attack && $user[login_id] != 1) {
		$error_str = " You can't attack during the first <b>$turns_before_attack</b> turns of having your account.";
	} elseif($target[turns_run] < $turns_safe && $user[login_id] != 1) {
		$error_str = " This ship is still under the inital <b>$turns_safe</b> turns protection period.";
	} elseif($ship_config[na]) {
		$error_str = " Your ship doesn't have the ability to attack.";
	} elseif($ship_config[po]) {
		$error_str = " Your ship doesn't have the ability to attack other ships, just planets";
	} elseif($target[login_id] == $user[login_id]) {
		$error_str = " You may not attack yourself.";
	} elseif($user[ship_id] == 1) {
		$error_str = " A <b class=b1>Ship Destroyed</b> may not attack, as it doesn't excist in the first place.";
	} elseif(($user[clan_id] == $target[clan_id]) && $user[clan_id] > 0) {
		$error_str = "You may not attack a clan member.";
	} elseif($sure != 'yes' && (!$target_config[hs] || $user[login_id] == 1)) {
	  get_var('Attack','attack.php',"Are you sure you want to attack ".print_name($target)."?",'sure','yes');
	} elseif($sure != 'yes' && $target_config[hs]) {
	  get_var('Attack','attack.php',"Are you sure you want to attack a ship when you do not know who it's owner is?",'sure','yes');
	}else {
		charge_turns($space_attack_turn_cost);

		$user_attack_bonus = CheckShip_ForAttack($user_ship['ship_id']);
		$user_defense_bonus = CheckShip_ForDefence($user_ship['ship_id']);
		$target_attack_bonus = CheckShip_ForAttack($target_ship['ship_id']);
		$target_defense_bonus = CheckShip_ForDefence($target_ship['ship_id']);


		// Generate attack Damage (includes advanced upgrades)

		if (eregi("hs",$target_ship[config]) || eregi("fr",$target_ship[config])){
			$attack_damage = round($user_ship[fighters] * .55);
		} elseif(eregi("bs",$user_ship[config]) || eregi("ls",$user_ship[config]) || eregi("hs",$user_ship[config])) {
			$attack_damage = round($user_ship[fighters] * .75);
		} else {
			$attack_damage = round($user_ship[fighters] * .65);
		}

		$attack_damage += mt_rand(round(-$user_ship[fighters] * .15),round($user_ship[fighters] * .15) + 1);

		// prevent negative damage
		if(($attack_damage + $user_attack_bonus) > ($target_defense_bonus)) {
			$attack_damage = $attack_damage + $user_attack_bonus - $target_defense_bonus;
		} else {
			$attack_damage = 0;
		}

		// generate counter damage
		if (eregi("fr",$target_ship[config]) || eregi("hs",$target_ship[config])){
			$counter_damage = round($target_ship[fighters] * .85);
		} else {
			$counter_damage = round($target_ship[fighters] * .75);
		}
		$counter_damage += mt_rand(round(-$target_ship[fighters] * .15),round($target_ship[fighters] * .15) + 1);

		// prevent negative damage
		if(($counter_damage + $target_attack_bonus) > ($user_defense_bonus)) {
			$counter_damage = $counter_damage + $target_attack_bonus - $user_defense_bonus;
		} else {
			$counter_damage = 0;
		}

		// protect the admin
		if ($target[login_id] == 1) {
			$attack_damage = 0;
		}
		if ($user[login_id] == 1) {
			$counter_damage = 0;
		}
		// Send messages to Target (have merged both to 1 message - Maugrim)
		if ($target[login_id] != 2 && $target[login_id] != 3) {
			send_message($target[login_id],"The <b class=b1>$user_ship[ship_name]</b> attacked your <b class=b1>$target_ship[ship_name]</b> with <b>$user_ship[fighters]</b> fighters in star system #<b>$user_ship[location]</b>.<br /><br />Your <b class=b1>$target_ship[ship_name]</b> did <b>$counter_damage</b> damage and took <b>$attack_damage</b> damage.",2);
		}



		// Deal Attacking Damage
		$dead_ship = $target_ship;
		$error_str .= "<br /><b>$attack_damage</b> damage to <b class=b1>$target[login_name]</b>'s $target_ship[class_name].";
		$dam_take = damage_ship($attack_damage,0,0,$user,$target,$target_ship);
		if($dam_take == 1) {
			if($dead_ship[location] == 1){
				post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>\'s $target_ship[class_name] in the <b>Sol</b> system");
			} else {
				post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>\'s $target_ship[class_name].");
			}

		  if($dead_ship[shipclass] == 2) {
		    $error_str .= "<br />You successfully blew <b class=b1>$target[login_name]</b>'s Escape Pod to smitherines.";
			if($sudden_death == 1){
				$error_str .= "<br />As the game is in Sudden Death(SD), <b class=b1>$target[login_name]</b> is out of the game permanently.";
				post_news("<b class=b1>$target[login_name]</b> has been completely killed, and is out of the game permanently.");
			}
		   send_message($target[login_id],"Your <b class=b1>$dead_ship[ship_name]</b> was destroyed by the <b class=b1>$user_ship[ship_name]</b>.",2);
			if($target[bounty] > 0 && $alternate_bounty_sys == 0) {
				$error_str .= "<p>You have claimed the <b>$target[bounty]</b> Credit bounty that was on <b class=b1>$target[login_name]</b>s head.<br />";
				post_news("The <b>$target[bounty]</b> bounty on <b class=b1>$target[login_name]</b> has been claimed by <b class=b1>$user[login_name]</b>");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + $target[bounty] where login_id = '$user[login_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$target[login_id]'");
			} elseif ($target[bounty] > 0) {
				$error_str .= "<p>You have claimed the remaining <b>$target[bounty]</b> Credit bounty that was on <b class=b1>$target[login_name]</b>s head after the destruction of their fleet.<br />";
				post_news("The <b>$target[bounty]</b> Credit bounty remaining on <b class=b1>$target[login_name]</b>s head after the destruction of their fleet has been claimed by <b class=b1>$user[login_name]</b>");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + $target[bounty] where login_id = '$user[login_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$target[login_id]'");
			}
		  } else {
			$error_str .= "<br />You destroyed <b class=b1>$target[login_name]</b>'s $dead_ship[class_name].";
			send_message($target[login_id],"Your <b class=b1>$dead_ship[ship_name]</b> was destroyed by the <b class=b1>$user_ship[ship_name]</b>.",2);
		  }
		} elseif ($dam_take==2){
			$error_str .= "<br /><b class=b1>$user[login_name]</b> destroyed <b class=b1>$target_ship[login_name]</b>'s $dead_ship_ship[class_name].";
			$error_str .= "<br /><b class=b1>$target_ship[login_name]</b> ejected in a Escape Pod.";
			if($user_dead_ship[location] == 1) {
				post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>\'s $target_ship[class_name] in the <b>Sol</b> System.");
			} else {
				post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>\'s $target_ship[class_name].");
			}
			send_message($target[login_id],"Your <b class=b1>$dead_ship[ship_name]</b> was destroyed by the <b class=b1>$user_ship[ship_name]</b>.",2);
		}

		// Deal Counter Damage
        $error_str .= "<br /><b>$counter_damage</b> damage to <b class=b1>$user[login_name]</b>'s $user_ship[class_name].";
		$dead_ship = $user_ship;
		$dam_take = damage_ship($counter_damage,0,0,$target,$user,$user_ship);
		if($dam_take == 1) {
		  post_news("<b class=b1>$target[login_name]</b> destroyed <b class=b1>$user[login_name]</b>\'s $dead_ship[class_name].");
		  send_message($target[login_id],"You destroyed the <b class=b1>$dead_ship[ship_name]</b>.",2);
		  if($dead_ship[shipclass] == 2) {
		    $error_str .= "<br /><b class=b1>$target[login_name]</b> successfully blew your Escape Pod to smitherines.";
			if($sudden_death){
				$error_str .= "<br />As the game is in Sudden Death(SD) you are out of the game permanently.";
				post_news("<b class=b1>$user[login_name]</b> has been completely killed, and is out of the game permanently.");
			}
			if($user[bounty] > 0 && $alternate_bounty_sys == 0) {
				send_message($target[login_id],"<p>You have claimed the <b>$user[bounty]</b> Credit bounty that was on <b class=b1>$user[login_name]s</b> head.<br />",0);
				post_news("The <b>$user[bounty]</b> bounty on <b class=b1>$user[login_name]</b> has been claimed by <b class=b1>$target[login_name]</b>");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + $user[bounty] where login_id = '$target[login_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$user[login_id]'");
			} elseif ($user[bounty] > 0) {
				send_message($target[login_id],"<p>You have claimed the remaining <b>$user[bounty]</b> Credit bounty that was on <b class=b1>$user[login_name]</b>s head after the destruction of their fleet.<br />",0);
				post_news("The <b>$user[bounty]</b> Credit bounty remaining on <b class=b1>$user[login_name]</b>s head after the destruction of their fleet has been claimed by <b class=b1>$target[login_name]</b>");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + $user[bounty] where login_id = '$target[login_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$user[login_id]'");
			}
			// player looses their ship
		  } else {
		    $error_str .= "<br /><b class=b1>$target[login_name]</b> destroyed your $dead_ship[class_name].";
			$dead_ship_check = 56;
		  }
		} elseif ($dam_take==2){
			$error_str .= "<br /><b class=b1>$target[login_name]</b> destroyed your $dead_ship[class_name].";
			$error_str .= "<br />You ejected in a Escape Pod.";
			post_news("<b class=b1>$target[login_name]</b> destroyed <b class=b1>$user[login_name]</b>\'s $dead_ship[class_name].");
			send_message($target[login_id],"You destroyed the <b class=b1>$dead_ship[ship_name]</b>, reducing <b class=b1>$user[login_name]</b> to an Escape pod",2);

		}
	}
} else {
	$error_str = " The game Admin has disabled ship to ship attacks.";
}


// update user stats for status bar
db(__FILE__,__LINE__,"select * from ${db_name}_users where login_id = $user[login_id]");
$user = dbr();
db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = $user[ship_id]");
$user_ship = dbr();
empty_bays();

// print the name of the new users ship, as well as the users new location.
if($dead_ship_check == 56){
	$error_str .= "<br /><br />Your command has been transfered to the <b class=b1>$user_ship[ship_name]</b>($user_ship[class_name]), in system #<b>$user_ship[location]</b>.";
}

print_page("Attack",$error_str);
?>
