<?php
/*
//
File:			daily_maint.php
Objective:		Rewritten version of the daily maintenance file, handles tri-daily game updates
New Objective:	Surrender game to cyrax if admin hasnt been on for awhile.  Handles just one game.
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com) and Ivan Ikoda (i_ikoda@hotmail.com)
Date Committed:	30 June 2004		Date Modified:	n/a

Copyright (c) 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

require_once("common.inc.php");
require_once("includes/random_events.inc.php");

//prevent non authorised users from accessing maints
if($server_passwd != $adminpass || !isset($adminpass))
{
	echo("A maintenance request to daily_maint.php made from IP ".$_SERVER['REMOTE_ADDR']." was refused as a false Server Administrator password was used. This occurence has been reported to the Server Administrator.");
	exit();
}

// connect to the database
$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);

// attempt to remove the execution time limit for this script - will not work under PHP safe_mode
set_time_limit(0);

// seed the random number generator
mt_srand((double)microtime()*1000000);

// disable any possible passed array using $games through POST or GET methods
$games = array();

/*
// This maintenance file is written on the basis that the "hourly" maintenance occurs once every 30 minutes. It is
// run for all games which are both active and unpaused. More specific handling of maintenance frequency is not
// supported at this time though plans are in place for 2.3.0
*/

// fetch an array of all games for which maintenances should be run
db4(__FILE__,__LINE__,"select * from se_games where paused = 0");

// now loop through all games selected
while($games = dbr4())
{

	// store the start time of execution of the current loop
	$start_time = time();

	// store the current game iterations db_name value
	$db_name = $games['db_name'];

	// echo the current details in plain text
	echo("
		<u>Current Iteration:</u><br />
		Database: $database<br />
		db_name: $db_name<br /><br />
	");

	// fetch a list of all Admin variables for this game currently in effect
	if($var_source == 0)
	{
		require("$map_path/$db_name/db_vars.inc.php");
	}
	else
	{
		db(__FILE__,__LINE__,"select name, value from ${db_name}_db_vars order by name");
		while($var_list = dbr())
		{
			$$var_list['name'] = $var_list['value'];
		}
	}

	// send news item that daily maintenance is running
	dbn(__FILE__,__LINE__,"insert into ${db_name}_news_maint (timestamp, headline, login_id) values (".time().",'Daily Maintenance Running...','1')");

	echo("<u>Tidy-Up Maintenance Procedures:</u><br />");

	// retire all game players who have not logged in over the previous 5 days
	Delete_Inactive_Players();

	// a quick check to ensure anyone with negative cash is captured
	dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = 0 where cash < 0");

	// move blackmarkets around at random
	Move_Blackmarkets();

	// move alien shipyards around at random
	Move_Shipyards();

	// update blackmarket usage fines, and outlaw status of players who have refused to pay fine
	Update_Fines();

	echo("<br /><u>General Cleanup Operations</u><br />");

	// delete all unused mine clusters
	if ($flag_mines > 0)
	{
		dbn(__FILE__,__LINE__,"delete from ${db_name}_mines where cluster <= 0");
		echo("- Used Mine clusters deleted</u><br />");
	}

	// delete older forum posts, clan posts, news items and player histories
	dbn(__FILE__,__LINE__,"delete from ${db_name}_news where timestamp < ".time()."-604800 && login_id != -1");
	dbn(__FILE__,__LINE__,"delete from ${db_name}_messages where timestamp < ".time()."-604800 && login_id = -1");
	dbn(__FILE__,__LINE__,"delete from ${db_name}_messages where timestamp < ".time()."-259200 && login_id = -5");
	dbn(__FILE__,__LINE__,"delete from user_history where timestamp < ".time()."-1814400");
	echo("- Old forum posts, clan posts, news and player histories deleted<br />");

	// update the interest on bounties (5%)
	dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = bounty * 1.05 where bounty > 0");
	echo("- Interest on Bounties updated<br />");


	// Planet Production
	echo("<br /><u>Planetary Production, Colonist Procedures, SS Regeneration</u><br />");

	// number of fighters to be generated per resource bunch
	$fighters_per_build = $planet_fighters;
	// number of electronics per resource bunch
	$elect_per_build = $planet_elect;
	// number of organics per resource bunch
	$organ_per_build = $planet_organ;


	db(__FILE__,__LINE__,"select p.*, u.planet_report from ${db_name}_planets p, ${db_name}_user_options u where u.login_id = p.owner_id");
	while($planet = dbr())
	{
		unset($out);
		unset($out_fighter);
		unset($out_elect);
		unset($out_organ);

		// update electronics production
		$out_elect = Produce_Electronics($planet);

		// update the planet data so that the new electronics can be immediately utilised in Fighter production
		// Just-In-Time Production :)
		db2(__FILE__,__LINE__,"select p.*, u.planet_report from ${db_name}_planets p, ${db_name}_user_options u where p.planet_id = $planet[planet_id] && u.login_id = p.owner_id");
		$planet2 = dbr2();

		// update fighter production
		$out_fighter = Produce_Fighters($planet2);
		// update organics production
		$out_organ = Produce_Organics($planet);

		// If this planet has a worm hole up, consume the required dark matter
		if ($planet['wormhole'] > 0) {
			db2(__FILE__,__LINE__,"select art_wh from ${db_name}_stars where star_id = $planet[location]");
			$wh_star = dbr2();
			$dm_cost = $wh_star['art_wh'] > 0 ? $wh_star['art_wh'] : 1;
			if ($planet['darkmatter'] < $dm_cost) {
				dbn(__FILE__,__LINE__,"update ${db_name}_stars set wormhole = 0, art_wh = 0, wh_login = 0, wh_clanid = 0 where star_id = '$planet[location]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_stars set wormhole = 0, art_wh = 0, wh_login = 0, wh_clanid = 0 where star_id = '$planet[wormhole]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set darkmatter = 0, wormhole = 0, tech = 0, research_fac = 0, wormhole_gen = 0 where planet_id = $planet[planet_id]");
				$collapsed_msg = "The artificial wormhole on planet <b class=b1>$planet[planet_name]</b> <b><font color=\"#FF0000\">collapsed</font></b> due to inadequate Darkmatter.<br />"
					. "The <b>Research Facility</b> and all planetary <b>Tech. Units</b> were <b><font color=\"#FF0000\">destroyed</font></b> by the collapse.";
				dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id,login_id,text,cat) "
					. "values(".time().",'Maint','$planet[owner_id]','$planet[owner_id]','$collapsed_msg','1')");
				$collapsed_news = "Scientific research on planet <b class=b1>$planet[planet_name]</b> was neglected causing a devastating explosion.";
				dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) "
					. "values (".time().",'$collapsed_news','$planet[owner_id]')");
			} else {
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set darkmatter = darkmatter - $dm_cost where planet_id = $planet[planet_id]");
			}
		}

		if($planet['planet_type'] >= 0)
		{
			unset($out_tax);
			unset($out_inc);
			unset($temp_var_alpha);

			// Confirm if something got manufactured.
			if(is_string($out_elect) || is_string($out_fighter) || is_string($out_organ))
			{
				$temp_var_alpha = 1;
				$out = "Manufacturing: <ul>".$out_organ.$out_elect.$out_fighter."</ul>";
			}
			else
			{
				$out = "Manufacturing: <b>Nothing</b><br />";
			}

$planet_colon = ($planet['colon'] - ($planet['alloc_elect'] - $planet['alloc_fight'] - $planet['alloc_organ']));
			// Tax all Planet Colonists (at 1 Days tax divided by number of daily maints)
			unset($t_cash);
			$t_cash = round(($planet['colon'] * $planet['tax_rate'] * 0.01)/$day_maint_count);
			if(!empty($t_cash))
			{
				$out_tax = "<br /><b>$planet[colon]</b> Colonists Taxed @<b>$planet[tax_rate]</b>%<br /><b>$t_cash</b> Income.";
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set cash = cash + '$t_cash' where planet_id = '$planet[planet_id]'");
			}
	$planet_colon = ($planet['colon'] - ($planet['alloc_elect'] + $planet['alloc_fight'] + $planet['alloc_organ']));
			unset($t_inc);
			$t_inc = round(($planet_colon * (0.01 / ($planet['tax_rate'] / 2))) / $day_maint_count);
			if($t_inc > 0) {
				$out_inc = "<b>$planet_colon</b> Colonists Reproduced<br /><b>$t_inc</b> More Colonists Created.";
	dbn(__FILE__,__LINE__,"update ${db_name}_planets set colon = colon + $t_inc where planet_id = '$planet[planet_id]'");
			} elseif($t_inc < 0) {
				$t_inc = $t_inc * -1;
				$out_inc = "<b>$planet_colon</b> Colonists Reproduced<br /><b style=\'font-weight: bold; color: red;\'>$t_inc</b> Colonists Lost.";
			}


			if(isset($t_cash) || isset($t_inc))
			{
				$out .= "Colonists: <ul>".$out_inc.$out_tax."</ul>";
			}
			else
			{
				$out .= "Colonists: <b>Nothing</b>";
			}

			// print out report, but only when player would like one (as specified by user options).
			if($planet['planet_report'] == 2 || $planet['planet_report'] == 1)

			{
				dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'Maint','$planet[owner_id]','$planet[owner_id]','Your planet <b class=b1>$planet[planet_name]</b> has been processed, and the following has changed:<p>$out','1')");
			}
		}
	}

	echo("- Planet production of electronics, fighters and organics completed<br />");

	// Colonist Reproduction
	// Colonists do not now reduce at higher tax rates (to be revisited in 2.3.0). Additionally reproduction rate is
	// now assessed on all colonists not just idle ones, however the reproduction rate has been decreased by a factor
	// of 10 with a further reduction likely during future rebalancing of gameplay.

	echo("- Colony taxation completed<br />");

	// random resource regeneration
	$metal_chance = $rr_metal_chance;
	$metal_chance_min = $rr_metal_chance_min;
	$metal_chance_max = $rr_metal_chance_max;
	$fuel_chance = $rr_fuel_chance;
	$fuel_chance_min = $rr_fuel_chance_min;
	$fuel_chance_max = $rr_fuel_chance_max;

	db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
	$mr = dbr();
	$max_races = $mr['value'];

	db(__FILE__,__LINE__,"select count(star_id) as num_stars from ${db_name}_stars");
	$num_stars = dbr();
	for($ct = $max_races + 1;$ct <= $num_stars['num_stars'];$ct++)
	{
		if(rand(0,100) < $metal_chance)
		{
			 $temp = mt_rand(0, ($metal_chance_max - $metal_chance_min)) + $metal_chance_min;
			 dbn(__FILE__,__LINE__,"update ${db_name}_stars set metal = metal + $temp where star_id = $ct");
		}
		if(rand(0,100) < $fuel_chance)
		{
			 $temp = mt_rand(0, ($fuel_chance_max - $fuel_chance_min)) + $fuel_chance_min;
			 dbn(__FILE__,__LINE__,"update ${db_name}_stars set fuel = fuel + $temp where star_id = $ct");
		}
	}
	echo("- Random Resource regeneration of Star Systems completed<br />");


	// Days left in game counter
	// DEV: seems to be updating the old method of tracking days from SE
	dbn(__FILE__,__LINE__,"update ${db_name}_db_vars set value = value - 1 where name = 'count_days_left_in_game' and value > 0");
	echo("<br /><u>Game counter updated</u><br />");


	// Random Events Updating
	$rand = $random_events;

	echo("<br /><u>Random Event Updates</u><br />");

	// Make nebulas (fransie mod, v1, v2 will be more advance ;) )

	// basicly destroies all nebulas, and creates new ones...
	// creating is simple. it selects random amount of stars between 2, and squareroot on max stars(so max for 100 stars would be 10)
	//	and then goes in a loop. in the loop, it randomly selects a star, and turn all stars around it into nebula stars, up to a 100 radius, if they
	//	aren't already set to another event. i am a genius, am i!

	// V2 will be more advanced, instead of deleteing all the nebulas, it will select a random star around it, and turn all stars around it in a
	//	N star radius(yea, star raidus, as in warps...), so having the effect that a nebula spans over many systems, and moves...

	// V3 i have no idea how to make even more advanced... lol. ;)

	dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = 0 where event_random = 2");

	if ($rand > 1) {
		// Up to one Nebula cluster per 500 stars
		$num_nebula_clusters = rand(0, ceil($num_stars['num_stars']/500));
		if ($num_nebula_clusters == 0) {
			echo("- No Nebulas created at this time.<br />");
		} else {
			for ($i=0; $i<$num_nebula_clusters; $i++) {
				db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id > $max_races and event_random = 0 "
					. "order by rand() limit 1");
				$star = dbr();
				// The size of a cluster is between half the min star distance (so we can have a nebula that exists
				// in just one SS) and 2 times the min star distance.
				$nebula_radius = rand(floor($uv_min_star_dist/2), ceil($uv_min_star_dist*2));
				// The complex where clause uses the Pythagorean theorem to select all stars that are
				// within $nebula_radius pixels of $star's x/y location.
				db(__FILE__,__LINE__,"update ${db_name}_stars set event_random = 2 "
					. "where sqrt(pow(x_loc - $star[x_loc], 2) + pow(y_loc - $star[y_loc], 2)) <= '$nebula_radius' "
						. "and event_random = 0 and star_id > '$max_races'");
				echo("- Created Nebula at SS $star[star_id] covering $nebula_radius pixels.<br />");
			}

			// All SSs with a nebula must have at least a minimum amount of fuel
			$neb_min_fuel = nebula_min_fuel();
			dbn(__FILE__,__LINE__,"update ${db_name}_stars set fuel = '$neb_min_fuel' where event_random = 2 and fuel < '$neb_min_fuel'");
		}
	}


	// Convert Supernova Remnants to Black Holes
	db(__FILE__,__LINE__,"select star_id,t_a from ${db_name}_stars where event_random = '6'");
	$bh_sys = dbr();
	if ($bh_sys)
	{
		if($bh_sys['t_a'] == 1){
		$explodingta = 50;
		} else {
		$explodingta = 10;
		}
		$chance = rand(0,50);
		if ($chance < $explodingta)
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = 1, metal = '0', fuel='0', darkmatter='0', planetary_slots = 0 where star_id = '$bh_sys[star_id]'");
			dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'The <b>SuperNova Remnant</b> in Star System <b class=b1>#$bh_sys[star_id]</b> has collapsed into a <b>Black Hole</b>. Being a slow process, all ships managed to evacuate to Star System #<b>1</b> before the x-ray and gamma emission proved lethal. We expect no further trouble from that system. <font color=lime>- - - Science Institute of Sol - - -</font>','-11')");
			dbn(__FILE__,__LINE__,"delete from ${db_name}_ports where location = '$bh_sys[star_id]'");
			dbn(__FILE__,__LINE__,"delete from ${db_name}_planets where location = '$bh_sys[star_id]'");
			dbn(__FILE__,__LINE__,"delete from ${db_name}_powerups where location = '$bh_sys[star_id]'");

			// move all fleets (changed from moving ships in pre-2.2.0)
			db(__FILE__,__LINE__,"select location,login_id,fleet_id,fleet_name from ${db_name}_fleets where location = '$bh_sys[star_id]'");
			while ($fleet_bh = dbr())
			{
			db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = 1");
			$exploding_sol = dbr3();
			if($exploding_sol['event_random'] == 1){
				dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where fleet_id = '$fleet_bh[fleet_id]'");
				dbn(__FILE__,__LINE__,"delete from ${db_name}_fleets where fleet_id = '$fleet_bh[fleet_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '1', ship_id ='1' where location = '$bh_sys[star_id]'");
			} else {
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set location = '1' where fleet_id = '$fleet_bh[fleet_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_fleets set location = '1' where fleet_id = '$fleet_bh[fleet_id]'");
				dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text,cat) values(".time().",'Black Hole','$fleet_bh[login_id]','$fleet_bh[login_id]','Your Fleet the <b class=b1>$fleet_bh[fleet_name]</b> escaped a black hole forming from a SuperNova Remnant in Star System #<b>$ship_bh[location]</b>. It is now in Star System #<b>1</b>','2')");


			}
			}
			dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '1' where location = '$bh_sys[star_id]'");

			print "<br />SN remnant in $bh_sys[star_id] to blackhole<br />";
		}
		elseif ($chance > $explodingta)
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = '14' where star_id = '$bh_sys[star_id]'");
			dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'After much study, we have decided that the star in system <b>$bh_sys[star_id]</b> will <b class=b1>not</b> become a Blackhole, as it was not massive enough. This system will remain a harmless Super-Nova Remnant, with lots of resources available to exploit. <font color=lime>- - - Science Institute of Sol - - -</font>','-11')");
			print "<br />SN remnant in $bh_sys[star_id] safe<br />";
		}
		echo("- Supernova Remnants to Black Holes updated<br />");
	}

	// Convert Supernovas to Supernova Remnants
	db(__FILE__,__LINE__,"select star_id,event_random from ${db_name}_stars where event_random = 5 or event_random = 11");
	$sn_sys = dbr();
	if ($sn_sys['event_random'] == 5)
	{
		$chance = rand(0, 20);
		if ($chance > 17)
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = 0 where star_id = '$sn_sys[star_id]'");
			dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'The scare about the Supernova in system <b>$sn_sys[star_id]</b> is over. It seems a technician (<b>first class</b>) called <b class=b1>\"Rimmer\"</b> spilt some coffee over an instrument panel causing a false reading. We apologise for any terror caused. <font color=lime>- - - Science Institute of Sol - - -</font>','-11')");
			print "<br />Supernova in $sn_sys[star_id] was a dud.<br />";
		}
		elseif ($chance < 10)
		{
			explode_sn($sn_sys);
		}
	}
	elseif($sn_sys['event_random'] == 11)
	{
		explode_sn($sn_sys);
	}
	echo("- Supernova to Remnant conversion updated<br />");


	// Sets lvl 10 random events to lvl 11.
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = '11' where event_random = '10'");
	echo("- Lvl 10 Random Events upgraded to Lvl 11<br />");


	// Add new Random Events

	if($rand < 1)
	{
		dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = 0 where event_random > 0");
	}
	elseif ($rand > 1)
	{
		$temp = (1000 / ($rand * $num_stars['num_stars'])) + 1;
		$chance = rand(0,$temp);

		// Add a mining rush
		if ($chance == 0)
		{
			db(__FILE__,__LINE__,"select star_id,metal from ${db_name}_stars where star_id > $max_races and event_random = 0 "
				. "order by rand() limit 1");
			$is_it = dbr();
			if (!empty($is_it)) {
				$to_go = $is_it['star_id'];
				$rush_min_metal = miningrush_min_metal();
				if ($is_it['metal'] < $rush_min_metal ) {
					dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = 4, metal = '$rush_min_metal' where star_id = '$to_go'");
				} else {
					dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = 4 where star_id = '$to_go'");
				}
				dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'Breaking news. A huge metal deposit has been found in <b class=b1>system $to_go</b> this deposit seems limitless, but could run out at any time. Metal mining rates for all ships in that system will be <b>quadrupled</b> until the deposit runs out.','-10')");
				echo("- Mining Rush Added to SS $to_go<br />");
			}
		}

		// Set a Star System to go Supernova
		if ($rand > 2)
		{
			$temp = 40 / (round($num_stars['num_stars'] / 100) + 1);
			$chance = rand(0, $temp);
			// supernova!!!!!!!
			if ($chance == 1)
			{
				db(__FILE__,__LINE__,"select star_id from ${db_name}_stars where star_id > $max_races and event_random = 0 "
					. "order by rand() limit 1");
				$is_it = dbr();
				if (!empty($is_it)) {
					$to_go = $is_it['star_id'];
					dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = 5, metal = 0, fuel = 0, darkmatter = 0 where star_id = '$to_go'");
					dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'Scientists report that the star in <b class=b1>system $to_go</b> is most likely going to go <b>Supernova(Explode)</b> in the next 24-72 hours, destroying <b class=b1>EVERYTHING</b> in the system. <font color=lime>- - - Science Institute of Sol - - -</font>','-11')");
					echo("- Supernova created in SS $to_go<br />");
				}
			}
		}
	}

	// manages day counter on servers with multiple occurance of the daily maint during a 24hr period
	if ($day_maint_count > 1)
	{
		// set days remaining in game
		db(__FILE__,__LINE__,"select * from se_games where db_name = '$db_name'");
		$day_info = dbr();
		$day_cnt = $day_info['day_maint_cnt'];
		if ($day_cnt >= $day_maint_count)
		{
			dbn(__FILE__,__LINE__,"update se_games set day_maint_cnt = 1,game_days_remaining = game_days_remaining - 1,sd_day_num = sd_day_num - 1 where db_name = '$db_name'");
			db3(__FILE__,__LINE__,"select * from ${db_name}_db_vars where name = 'intrest'");
			$intrest = dbr3();
			$dm_intrest = (1.00 + ($intrest['value'] / 100));
			dbn(__FILE__,__LINE__,"update ${db_name}_bank_account set deposit = deposit * $dm_intrest where deposit < 10000000");
			echo("Intrest Updated");
		}
		else
		{
			dbn(__FILE__,__LINE__,"update se_games set day_maint_cnt = day_maint_cnt + 1 where db_name = '$db_name'");
		}
	}
	else
	{
		dbn(__FILE__,__LINE__,"update se_games set day_maint_cnt = 1,game_days_remaining = game_days_remaining - 1,sd_day_num = sd_day_num - 1 where db_name = '$db_name'");
		db3(__FILE__,__LINE__,"select * from ${db_name}_db_vars where name = 'intrest'");
		$intrest = dbr3();
		$dm_intrest = (1.00 + ($intrest['value'] / 100));
		dbn(__FILE__,__LINE__,"update ${db_name}_bank_account set deposit = deposit * $dm_intrest where deposit < 10000000");
	}
dbn(__FILE__,__LINE__,"delete from ${db_name}_game_forum where timestamp < (".time()."-604800)");
				echo("- Forums Purged<br />");

	echo("<hr />");


	// finish counting time taken to run this maint.
	$total_time = time() - $start_time;

	dbn(__FILE__,__LINE__,"insert into ${db_name}_news_maint (timestamp, headline, login_id) values (".time().",'...Daily Maintenance Complete in <b>$total_time</b> seconds','1')");


}


// functions used during a daily maintenance

function Delete_Inactive_Players() {
	global $db_name;
	$time = time() - (5 * 86400);
	db(__FILE__,__LINE__,"select clan_id,login_id,login_name,v_clan_id from ${db_name}_users where login_id >= 5 and (joined_game < '$time' and last_request < '$time')");
	while ($users = dbr())
	{
		// determine if player is in a clan
		if ($users['clan_id'] < 1) {
			db2(__FILE__,__LINE__,"select leader_id from ${db_name}_clans where clan_id = $users[clan_id]");
			$clan = dbr2();
			// if player is the leader in a clan, remove the clan.
			if ($clan['leader_id'] == $users['login_id'])
			{
				dbn(__FILE__,__LINE__,"delete from ${db_name}_clan_relations where clan_id = $users[clan_id]");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set clan_id = 0 where clan_id = $users[clan_id]");
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set clan_id = -1 where clan_id = $users[clan_id]");
				dbn(__FILE__,__LINE__,"delete from ${db_name}_clans where clan_id = $users[clan_id]");
			}
			else
			{
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set clan_id = -1 where owner_id = $users[login_id]");
				dbn(__FILE__,__LINE__,"update ${db_name}_clans set members = members - 1 where clan_id = $users[clan_id]");
			}
		}
		dbn(__FILE__,__LINE__,"delete from ${db_name}_clan_relations where v_clan_id = $users[v_clan_id]");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where login_id = $users[login_id]");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_fleets where login_id = $users[login_id]");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_diary where login_id = $users[login_id]");
		dbn(__FILE__,__LINE__,"insert into user_history values ('$users[login_id]','".time()."','$db_name','Was removed from $db_name after 6 days of in-activity.','','')");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_user_options where login_id = $users[login_id]");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_users where login_id = $users[login_id]");
		dbn(__FILE__,__LINE__,"insert into ${db_name}_news_maint (timestamp, headline, login_id) values (".time().",'<b class=b1>$users[login_name]</b> was retired after five days of in-activity.','1')");
	}
	echo("- Inactive Players deleted<br />");
}


/*
** Each black market has a bm_move counter with it. Each DM, bm_move is decremented by one.
** When bm_move reaches zero, the BM moves to a new location and bm_move is reset to a high
** number that is roughly 1.5-2.5 days of real time.
*/
function Move_Blackmarkets() {
	global $db_name, $uv_num_stars, $day_maint_count, $flag_bmrkt, $max_races;

	echo( "<br /><u>Blackmarket Movement</u><br />" );
	if ( $flag_bmrkt > 0 ) {
		$min_stay = intval( floor( $day_maint_count * 1.5 ) );  // 1.5 days
		$max_stay = intval( ceil( $day_maint_count * 2.5 ) );   // 2.5 days
		echo( "- Blackmarkets move every $min_stay to $max_stay DMs.<br />" );

		$bm_locs = array();
		db( __FILE__, __LINE__, "select location from ${db_name}_bmrkt" );
		while ( $bm = dbr() ) {
			$bm_locs[$bm['location']] = TRUE;
		}
		// Pretend a BM exists in every black hole to prevent moving them there
		db( __FILE__, __LINE__, "select star_id from ${db_name}_stars where event_random = 1" );
		while ( $star = dbr() ) {
			$bm_locs[$star['star_id']] = TRUE;
		}

		dbn( __FILE__, __LINE__, "update ${db_name}_bmrkt set bm_move = bm_move - 1 where bm_move > 0" );

		db( __FILE__, __LINE__, "select * from ${db_name}_bmrkt" );
		while ( $bm = dbr() ) {
			if ( $bm['bm_move'] <= 0 ) {
				unset( $new_loc );
				for ( $failsafe = 0; $failsafe <= 50; $failsafe++ ) {
					$try_loc = rand( $max_races + 1, $uv_num_stars );
					if ( ! $bm_locs[$try_loc] ) {
						$new_loc = $try_loc;
						break;
					}
				}
				if ( isset( $new_loc ) ) {
					$bm_locs[$new_loc] = TRUE;
					$new_stay = rand( $min_stay, $max_stay );
					dbn( __FILE__, __LINE__, "update ${db_name}_bmrkt set location = $new_loc, bm_move = $new_stay "
						. "where bmrkt_id = $bm[bmrkt_id]" );
					echo( "- BM at $bm[location] moved to $new_loc for $new_stay DMs.<br />" );
				} else {
					echo( "- BM at $bm[location] needs to be moved, but unable to find a free system for it!<br />" );
				}
			} else {
				// Uncomment below to help debug unmoving BMs
				//echo( "- BM at $bm[location] still has $bm[bm_move] DMs before moving.<br />" );
			}
		}
		echo( "- Blackmarket movement complete.<br />" );
	} else {
		echo( "- Blackmarkets are disabled.<br />" );
	}
}


/*
** Each alien shipyard has a move counter with it. Each DM, move is decremented by one.
** When move reaches zero, the shipyard moves to a new location and move is reset to a high
** number that is roughly 1.5-2.5 days of real time.
*/
function Move_Shipyards() {
	global $db_name, $uv_num_stars, $day_maint_count, $max_races;

	echo( "<br /><u>Alien Shipyard Movement</u><br />" );
	$min_stay = intval( floor( $day_maint_count * 1.5 ) );  // 1.5 days
	$max_stay = intval( ceil( $day_maint_count * 2.5 ) );   // 2.5 days
	echo( "- Shipyards move every $min_stay to $max_stay DMs.<br />" );

	$yard_locs = array();
	db( __FILE__, __LINE__, "select location from ${db_name}_shipyards" );
	while ( $yard = dbr() ) {
		$yard_locs[$yard['location']] = TRUE;
	}
	// Pretend a shipyard exists in every black hole to prevent moving them there
	db( __FILE__, __LINE__, "select star_id from ${db_name}_stars where event_random = 1" );
	while ( $star = dbr() ) {
		$yard_locs[$star['star_id']] = TRUE;
	}

	dbn( __FILE__, __LINE__, "update ${db_name}_shipyards set move = move - 1 where move > 0" );

	db( __FILE__, __LINE__, "select * from ${db_name}_shipyards" );
	while ( $yard = dbr() ) {
		if ( $yard['move'] <= 0 ) {
			unset( $new_loc );
			for ( $failsafe = 0; $failsafe <= 50; $failsafe++ ) {
				$try_loc = rand( $max_races + 1, $uv_num_stars );
				if ( ! $yard_locs[$try_loc] ) {
					$new_loc = $try_loc;
					break;
				}
			}
			if ( isset( $new_loc ) ) {
				$yard_locs[$new_loc] = TRUE;
				$new_stay = rand( $min_stay, $max_stay );
				dbn( __FILE__, __LINE__, "update ${db_name}_shipyards set location = $new_loc, move = $new_stay "
					. "where asyrd_id = $yard[asyrd_id]" );
				echo( "- Shipyard at $yard[location] moved to $new_loc for $new_stay DMs.<br />" );
			} else {
				echo( "- Shipyard at $yard[location] needs to be moved, but unable to find a free system for it!<br />" );
			}
		} else {
			// Uncomment below to help debug unmoving shipyards
			//echo( "- Shipyard at $yard[location] still has $yard[move] DMs before moving.<br />" );
		}
	}
	echo( "- Shipyard movement complete.<br />" );
}


function Update_Fines() {
	global $db_name, $flag_sa, $flag_fines;
		echo("<br /><u>Updating of Fines/Outlaw Status</u><br />");
		if ($flag_sa > 0 && $flag_fines > 0)
		{
		//-----------------------------------
		// Set as overdue all fines 24hrs old
		//-----------------------------------
		dbn(__FILE__,__LINE__,"update ${db_name}_fines set overdue = overdue + 1 where overdue > 0 and paid = 0");
		echo("- Fines > 24hrs set as overdue<br />");
		//----------------------
		// Find out who is fined
		//----------------------
		$test404 = rand(0, 10);
		if ($test404 >= 9)
		{// 1 in 5 chance a check made
			db(__FILE__,__LINE__,"select * from ${db_name}_users where outlaw = 0 and overdue = 0 and bm_visit > 0 && login_id != 1");
			while ($user_test = dbr())
			{
				$test505 = rand(0, 10);
				if ($test505 > 9)
				{// 1 in 5+ chance a player will be fined (combined probability of 1 in 25+)
					$fine_amount = rand(0, (20 + $user_test['bm_visit'])) * 5000;
					$bounty = $fine_amount * 8;
					dbn(__FILE__,__LINE__,"insert into ${db_name}_fines (login_id, login_name, amount, bounty, overdue) values ('$user_test[login_id]', '$user_test[login_name]', '$fine_amount', '$bounty', '1')");
					dbn(__FILE__,__LINE__,"update ${db_name}_users set overdue = 1 where login_id = '$user_test[login_id]'");
					dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text,cat) values(".time().",'<b class=b1>Sol Authority</b>','$user_test[login_id]','$user_test[login_id]','<b>Sol Authority Fine Notice</b><br /><p>This is to inform you that you have been found guilty of dealing with certain known criminal elements within the Blackmarket trade. You are hereby fined the amount of <b>$fine_amount</b> Credits which is to be paid in at the Sol Authority Fine Office on Earth. You are reminded that failure to pay within the next 24hrs will leave the Authority with no choice but to class you as an \"Outlaw\" with a bounty of 3 times your imposed fine placed on your head.','0')");
				}
			}
		}
		echo("- New fines updated<br />");
		//-------------------------------------------
		// Make whoever is overdue by 24hrs an Outlaw
		//-------------------------------------------
		db(__FILE__,__LINE__,"select * from ${db_name}_fines where overdue >= '$day_maint_count'");
		while ($outlaws = dbr())
		{
			$bounty_final = $outlaws['bounty'] * 10;
			dbn(__FILE__,__LINE__,"update ${db_name}_users set outlaw = 1 where login_id = '$outlaws[login_id]'");
			dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'<b class=b1>Sol Authority</b>','$outlaws[login_id]','$outlaws[login_id]','<b>Sol Authority Fine Notice</b><br /><p>This is to inform you that you are overdue in paying a recent fine. In accordance with the laws of the Sol Authority you are hereby found guilty of defaulting on your fine. As a result the Sol Authority has declared you \"<b>Outlawed</b>\" with a bounty of <b>$bounty_final</b> being placed on your head.<br /><br />May the Clans refuse you refuge, Outlaw! We will await news of your demise...','0')");
			dbn(__FILE__,__LINE__,"update ${db_name}_fines set paid = 1, overdue = 0 where fine_id = $outlaws[fine_id]");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set overdue = 0 where login_id = '$outlaws[login_id]'");
			dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'<b class=b1>$outlaws[login_name]</b> has been outlawed by the <b>Sol Authority</b>. You are ordered to destroy him on sight and offer him no refuge. A bounty has been placed on his/her head of <b>$bounty_final</b>','1')");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = bounty + '$bounty_final' where login_id = '$outlaws[login_id]'");
			db(__FILE__,__LINE__,"select bounty from ${db_name}_users where login_id = '$outlaws[login_id]'");

			$bounty_total = dbr();
			dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text, cat) values(".time().",'Bobs Charity Shop','$outlaws[login_id]','$outlaws[login_id]','The <b class=b1>Sol Authority</b> has put <b>$bounty_final</b> on your head, making your bounty <b>$bounty_total[bounty]</b> Credits. I hate dealing with those politicians. Sorry mate, but business is business.', '0')");
		}
		echo("- Outlaws updated<br />");
		//------------------
		// Delete paid Fines
		//------------------
		dbn(__FILE__,__LINE__,"delete from ${db_name}_fines where paid = 1");
		echo("- Paid fines deleted<br />");
	}
}

// Not even sure if this is operational - only occuring through use of supernova effector and even then not a great
// effect
// SuperNova Going bang.
function explode_sn($sn_sys)
{
	global $db_name, $max_races, $day_maint_count, $rr_fuel_chance_min, $rr_fuel_chance_max,
		$rr_metal_chance_min, $rr_metal_chance_max;

	// Add a random amount between the min and max regenerated in 12 days (min of 50k, max of 500k)
	$plus_fuel = max(50000, min(500000, rand($rr_fuel_chance_min * $day_maint_count * 12,
		$rr_fuel_chance_max * $day_maint_count * 12)));
	$plus_metal = max(50000, min(500000, rand($rr_metal_chance_min * $day_maint_count * 12,
		$rr_metal_chance_max * $day_maint_count * 12)));
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = 6, metal = metal + '$plus_metal', fuel = fuel + '$plus_fuel' where star_id = '$sn_sys[star_id]'");
	dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'The star in <b class=b1>system $sn_sys[star_id]</b> has exploded destroying everything in the system, and leaving a <b class=b1>Supernova Remnant</b> which is extremly rich in metals and fuel. All adjoining systems have also recieved generous quantities of minerals. We believe the SuperNova Remnant will turn into a <b>Blackhole</b> over due course.<font color=lime>- - - Science Institute of Sol - - -</font>','-11')");

	// take out non-eps.
	db2(__FILE__,__LINE__,"select * from ${db_name}_ships where location = '$sn_sys[star_id]' && login_id !='1'");
	while ($ship_sn = dbr2()) {
		if ($ship_sn['shipclass'] != 2) {
			dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'SuperNova','$ship_sn[login_id]','$ship_sn[login_id]','Your ship the <b class=b1>$ship_sn[ship_name]</b> was destroyed by an exploding star (<b>Supernova</b>) in system #<b>$ship_sn[location]</b>', '2')");
			dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where ship_id = '$ship_sn[ship_id]'");
			dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'$ship_sn[login_name] lost a $ship_sn[class_name] to the SuperNova in system #<b>$ship_sn[location]<b>','$ship_sn[login_id]')");
		} else {
			dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'SuperNova','$ship_sn[login_id]','$ship_sn[login_id]','Your <b>Escape Pod</b> was near a star when it exploded. Fortunatly is was not hurt by the explosion, but was flung to a different system. Some well-wisher then brought you to system #1.', '2')");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '1' where ship_id = '$ship_sn[ship_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set location = '1' where login_id = '$ship_sn[login_id]'");
		}
	}

	// take out non admin planets
	db2(__FILE__,__LINE__,"select * from ${db_name}_planets where location = '$sn_sys[star_id]' && owner_id != '1'");

	while ($planet_sn = dbr2()) {
		dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'SuperNova','$planet_sn[owner_id]','$planet_sn[owner_id]','Your planet (<b class=b1>$planet_sn[planet_name]</b>) was obliterated by an exploding star (<b>Supernova</b>) in system #<b>$planet_sn[location]. It no longer excists, nor does anything that was on it.', '1')");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_planets where planet_id = $planet_sn[planet_id]");
		dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'The planet $planet_sn[planet_name] was totally destroyed by the SuperNova in system #<b>$planet_sn[location]<b>','$planet_sn[owner_id]')");
	}

	// move users to sol or next ship
	db2(__FILE__,__LINE__,"select * from ${db_name}_users where location = '$sn_sys[star_id]' && login_id > '3' && ship_id != 1");

	while($users = dbr2()) {
		db3(__FILE__,__LINE__,"select * from ${db_name}_ships where login_id = '$users[login_id]' && login_id != 1");

		if($other = dbr3()) {
			dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$other[ship_id]', location = '$other[location]' where login_id = '$other[login_id]'");
			dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'SuperNova','$users[login_id]','$users[login_id]','Command was transfered to the <b class=b1>$other[ship_name]</b>.', '2')");
		} else {
			dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'SuperNova','$users[login_id]','$users[login_id]','You ejected in an escape pod.', '2')");
			dbn(__FILE__,__LINE__,"insert into ${db_name}_ships (ship_name, login_id, login_name, shipclass, class_name, location, point_value) values('Escape Pod','$users[login_id]','$users[login_name]',2,'Escape Pod','1',5)");

			db4(__FILE__,__LINE__,"select * from ${db_name}_ships where login_id = '$users[login_id]'");

			$ship_id = dbr4();
			dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '1', ship_id ='$ship_id[ship_id]' where login_id = '$users[login_id]'");
		}
	}


	db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = '$sn_sys[star_id]'");
	$link = dbr();
	for ($i = 1; $i <= 6; $i++) {
		$sql_name = "link_$i";
		if ($link[$sql_name] > $max_races) {
			// Add a random amount between the min and max regenerated in 3 days (min of 10k, max of 100k)
			$plus_fuel = max(10000, min(100000, rand($rr_fuel_chance_min * $day_maint_count * 3,
				$rr_fuel_chance_max * $day_maint_count * 3)));
			$plus_metal = max(10000, min(100000, rand($rr_metal_chance_min * $day_maint_count * 3,
				$rr_metal_chance_max * $day_maint_count * 3)));
			dbn(__FILE__,__LINE__,"update ${db_name}_stars set fuel= fuel +'$plus_fuel', metal= metal+'$plus_metal' "
				. "where star_id = '$link[$sql_name]'");
		}
	}

	print "<br />Supernova in $sn_sys[star_id] went bang.<br />";
}


function Produce_Electronics($planet) {
	global $db_name, $elect_per_build;
	// Electronics production. admin_var per 500 colonists, 10 fuel, & 10 metal (assigned).
	if($planet['alloc_elect'] >= 500)
	{
		$elect_amount = round($planet['alloc_elect'] / 500);
		if($planet['fuel'] < $elect_amount * 10)
		{
			$elect_amount = round($planet['fuel'] * 0.1);
		}
		if($planet['metal'] < $elect_amount * 10)
		{
			$elect_amount = round($planet['metal'] * 0.1);
		}
		if($elect_amount > 0)
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set elect = elect + ($elect_amount * ".$elect_per_build."), fuel = fuel - $elect_amount * 10, metal = metal - $elect_amount * 10 where planet_id = ".$planet['planet_id']);
			$elect_used = $elect_amount * 10;
			$elect_made = $elect_amount * $elect_per_build;
			$out_elect = "Fuel Used: <b>$elect_used</b><br />Metal Used: <b>$elect_used</b><br />Colonists Assigned: <b>$planet[alloc_elect]</b><br />Produced: <b>$elect_made</b> <b class=b1>Electronics</b>.";
		}
	}
	return $out_elect;
}


function Produce_Fighters($planet2) {
	global $db_name, $fighters_per_build, $planet;
	// fighter production. admin_var per 100 colonists, fuel,metal,electronics.
	if($planet['alloc_fight'] >= 100) {
		$fighter_amount = round($planet['alloc_fight'] / 100);
		if($planet2['fuel'] < $fighter_amount) {
			$fighter_amount = $planet2['fuel'];
		}
		if($planet2['metal'] < $fighter_amount) {
			$fighter_amount = $planet2['metal'];
		}
		if($planet2['elect'] < $fighter_amount) {
			$fighter_amount = $planet2['elect'];
		}
		if($fighter_amount > 0) {
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighters = fighters + ($fighter_amount * $fighters_per_build), fuel = fuel - $fighter_amount, metal = metal - $fighter_amount, elect = elect - $fighter_amount where planet_id = ".$planet['planet_id']);
			$planet['elect'] -= $fighter_amount;
			$planet['fuel'] -= $fighter_amount;
			$planet['metal'] -= $fighter_amount;
			$temp556 = $fighter_amount * $fighters_per_build;
			$out_fighter = "<p>Fuel Used: <b>$fighter_amount</b><br />Metal Used: <b>$fighter_amount</b><br />Electronics Used: <b>$fighter_amount</b><br />Colonists Assigned: <b>$planet2[alloc_fight]</b><br />Produced: <b>$temp556</b> <b class=b1>Fighters</b>.";
		}
	}
	return $out_fighter;
}


function Produce_Organics($planet) {
	global $db_name, $organ_per_build;
	// Organics production. 1 per 450 colonists assigned.
	if($planet['alloc_organ'] >= $organ_per_build)
	{
		$organ_amount = round($planet['alloc_organ'] / $organ_per_build);
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set organ = organ + $organ_amount where planet_id = $planet[planet_id]");
		$out_organ = "Colonists Assigned: <b>$planet[alloc_organ]</b><br />Produced: <b>$organ_amount</b> <b class=b1>Organics</b>.";
	}
	return $out_organ;
}

?>