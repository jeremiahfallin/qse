<?php
/*
//
File:			hourly_maint.php
Objective:		Rewritten version of the hourly maintenance file, handles twice-hourly game updates
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	29 June 2004		Date Modified:	n/a

Copyright (c) 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

require_once("common.inc.php");
require_once('includes/clan_funcs.inc.php');
require_once('includes/random_events.inc.php');

//prevent non authorised users from accessing maints
if($server_passwd != $adminpass || !isset($adminpass))
{
	echo("A maintenance request to hourly_maint.php made from IP ".$_SERVER['REMOTE_ADDR']." was refused as a false Server Administrator password was used. This occurence has been reported to the Server Administrator.");
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
db4(__FILE__,__LINE__,"select * from se_games where paused = 0 and status >= 1");
//db4(__FILE__,__LINE__,"select * from se_games where status >= 1 and paused = 0");
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

	// here's number 1 on our hitlist - another non-sensical variable name switch
	$r_e = $random_events;
if($flag_trading = 1) {
	$num_indices = mysql_query("SELECT port_id FROM ${db_name}_ports");
	$ct = 1;
	while($id = mysql_fetch_array($num_indices)) {
		$metal_index = rand((100 - $uv_port_variance),(100 + $uv_port_variance));
		$fuel_index = rand((100 - $uv_port_variance),(100 + $uv_port_variance));
		$organ_index = rand((100 - $uv_port_variance),(100 + $uv_port_variance));
		$elect_index = rand((100 - $uv_port_variance),(100 + $uv_port_variance));
		dbn(__FILE__,__LINE__,"UPDATE ${db_name}_ports SET metal_cap = '$metal_index', fuel_cap = '$fuel_index', organ_cap = '$organ_index', elect_cap = '$elect_index' WHERE port_id = $id[0]");
		$ct++;
	}
	$ct--;
	print("$ct Trading Indices altered.<br>");
}// done update
//----------------------- aliens code ----------------test area
// randomly selects a system , if an empty slot.. builds a planet
// doesnt care who is there
// artemis

if($flag_aliens == 1 and rand(0, 10) == 0) { // rand implanted, so a alien planet isn't made every maint...
// find a system - process planets
	echo "<br /> <b>--Creating Alien Planets--</b> <br />";
	db(__FILE__,__LINE__,"SELECT *  FROM ${db_name}_stars WHERE star_id > 0 && planetary_slots > 0 && sys_type = 0 && event_random = 0 &&  art_wh = 0" );
	$systems = dbr();

	while($systems = dbr()){
    	$star_array[] = $systems[star_id];// array of available systems
	}
	$rand_sys_id = $star_array[rand(0, count($star_array) - 1)];// get one
	db(__FILE__,__LINE__,"SELECT COUNT(planet_id) AS plnt_cnt FROM ${db_name}_planets WHERE location = '$rand_sys_id'");
	$plnt = dbr();

	db(__FILE__,__LINE__,"SELECT * FROM ${db_name}_stars WHERE star_id = '$rand_sys_id'");
	$star = dbr();

	db(__FILE__,__LINE__,"SELECT COUNT(owner_id) FROM ${db_name}_planets where owner_id = 3");
	$number_planets = dbr();
	echo "There are $number_planets[0] of $alien_max_planets alien planets.";
	if($plnt[plnt_cnt] < $star[planetary_slots] and $number_planets[0] < $alien_max_planets and rand(0, 10)) { // check if there are open slots
		$star_name = $star[star_name];// named after the system
		$planet_name = $star_name." (".rand(100,999)."-".rand(100,999).")";//make it different for planet
		$owner_name = "The Aliens";// my alien friend
		$fighters = rand($alien_res_min,$alien_res_max) * 50;// set fighters
		$colons = rand($alien_res_min / 2,$alien_res_max / 2);
		// build the new planet
		$q_string = "INSERT INTO ${db_name}_planets (";
		$q_string = $q_string .
"planet_name ,planet_type ,location ,owner_id ,owner_name ,fighters ,colon ,fortress_level ,fighter_set ,cash ,tax_rate ,clan_id ,metal ,fuel ,elect ,organ ,alloc_fight ,alloc_elect ,alloc_organ ,pass ,planet_img ,shield_gen ,shield_charge ,launch_pad ,missile ,tech ,research_fac ,darkmatter ,wormhole_gen ,wormhole ,
barren ,pop_limit";
		$q_string = $q_string . ") VALUES (";
		$q_string = $q_string . "'$planet_name' ,'0' ,'$rand_sys_id' ,'3' ,'$owner_name' ,'$fighters' ,'.$colons.' ,'1' ,'1' ,'".rand($alien_res_min * 5,$alien_res_max * 5)."','5' ,'-1' ,'".rand($alien_res_min * 2,$alien_res_max * 3)."' ,'".rand($alien_res_min * 2,$alien_res_max * 3)."' ,'".rand($alien_res_min * 2,$alien_res_max * 2)."' ,'".rand($alien_res_min * 2,$alien_res_max * 2)."' ,'".($colons * .3)."' ,'".($colons * .7)."','0','0','1','9' ,'0' ,'0' ,'0' ,'10000' ,'1' ,'0' ,'0' ,'0' ,'0' ,'18000000')";
		dbn(__FILE__,__LINE__,$q_string);
		echo "\t --Created a alien planet.";

		post_news("<b class=b1>$owner_name</b> created the planet <b class=b1>$planet_name.</b>");

	}// end slots if

}// end aliens if

	/*
	// The "quick maintenance" procedures do some general tidying of the database structure as well as more
	// strategic management of player activities in home systems like "Sol".
	*/

	echo("<u>Quick Maintenance Procedures:</u><br />");

	// remove clans that have no members
	dbn(__FILE__,__LINE__,"delete from ${db_name}_clans where members = 0");
	echo("- Empty clans have been removed from the database<br />");
	// set missile launch pads 1hr nearer completion
	dbn(__FILE__,__LINE__,"update ${db_name}_planets set launch_pad = launch_pad - .5 where launch_pad > 1");
	echo("- Missile Launch Pads updated by 0.5 hours<br />");
	// update the scores
	echo("- ");
	score_func(0,1);
	update_clans();
	echo("- Clan scores have been updated.<br />");
	// fetch the number of star systems available in the current game
	db(__FILE__,__LINE__,"select num_stars from se_games where db_name = '$db_name'");
	$num_ss = dbr();

	// Create powerups if needed
	db(__FILE__,__LINE__,"select count(id) as numpups from ${db_name}_powerups");
	$dbr = dbr();
	$num_pups = $dbr['numpups'];

	db(__FILE__,__LINE__,"select count(login_id) as numusers from ${db_name}_users where login_id > 5");
	$dbr = dbr();
	$num_users = $dbr['numusers'];

	// Allow more pups for each user in the game and a larger universe
	$pups_for_users = $num_users > 10 ? intval($num_users/2) : 5;
	$pups_for_stars = intval(ceil($uv_num_stars/100));
	$max_pups = $pups_for_users + $pups_for_stars;
	if ( $num_pups < $max_pups ) {
		// Create one powerup per hourly maint so users can't scour the universe constantly gobbling them up
		Create_Powerup();
	} else {
		echo("- Already at max powerups<br />");
	}


	/*
	// Fransie's bilkos maint!!! NO SHIPS!!!
	// made from old bilkos maint!
	*/

#bilkos auction house:

# Get a var:
$bil_seconds = $bilkos_time * 3600;


dbn(__FILE__,__LINE__,"delete from ${db_name}_bilkos where timestamp <=".time()."- ($bil_seconds * 2) && bidder_id = 0 && active=1");


db(__FILE__,__LINE__,"select bidder_id,item_name,item_id from ${db_name}_bilkos where timestamp <= ".time()." - $bil_seconds && active = 1 && bidder_id > 0");

while($lots = dbr()){
	dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'Bilkos','$lots[bidder_id]','$lots[bidder_id]','You have successfully won lot #<b>$lots[item_id]</b> (<b class=b1>$lots[item_name]</b>). <p>You should come to the Auction House in <b class=b1>Sol</b> to collect your goods.')");
	dbn(__FILE__,__LINE__,"update ${db_name}_bilkos set active=0 where item_id = '$lots[item_id]'");
}


$turnip = rand(1, 100);
unset( $i_type, $i_code, $i_name, $i_price, $i_descr );
if($turnip > 90){ #planetary.
	$i_type = 5;
	if(($turnip > 93) || !$enable_superweapons){
		$i_code = rand(0, 5) + 4;
		$i_name = "Shield Gen Lvl <b>$i_code</b>";
		$i_price = $i_code * $i_code * 1000;
		$i_descr = "A level <b>$i_code</b> Shield Generater for a planet (Normal lvl is 3). <br>Increases Shield Capacity, and Shield Generation Rate. Can be used as an upgrade, or a new Generator.";
	} else {
		$i_code = "MLPad";
		$i_name = "Missile Launch Pad";
		$i_price = 100000;
		$i_descr = "Missile Launch Pad. Used once. No build time necassary, just install and go.";
	}
} elseif($turnip > 78){ #misc - turns. also included tech units where applicable...
	$i_type = 4;
	$i_code = rand(max(50,intval($hourly_turns/2)), max(200,min(1000,$hourly_turns*4,$max_turns)));
 	$i_name = "Turns <b>$i_code</b>";
	$i_price = $i_code * 345;
	$i_descr = "<b>$i_code</b> turns that can be used for whatever you want.";
} elseif($turnip > 65){ #equipment
	$i_type = 2;
	if($turnip > 68){
		if ($flag_bomb) {
			$i_code="warpack";
			$i_name="WarPack";
			$i_price=$cost_bomb*4;
			$i_descr="A collection of 2 Alpha bombs and 4 Gamma Bombs, all in one package.";
		}
	} else {
		if ($flag_bomb >= 2) {
			$i_code="deltabomb";
			$i_name="Delta Bomb";
			$i_price=10*$cost_bomb;
			$i_descr="One Bomb that will nullify all shields on all ships in the system AND then do <b>2000</b> damage to each of the ships!.<br><br>Note: Player may only own one Delta Bomb at a time!";
		}
	}
} else { # upgrades
	$i_type = 3;
	if($turnip > 61){
		$i_code="fig1500";
		$i_name="1500 Fighter Bays";
		$i_price=75000;
		$i_descr="Capable of fitting 1500 fighters into one upgrade pod this is a must for the war-hungry.";
	} elseif($turnip > 55){
		$i_code="attack_pack";
		$i_name="Attack Pack";
		$i_price=40000;
		$i_descr="Increases a ships shield capacity by 200 and fighter capacity by 700, all with one upgrade.";
	} elseif($turnip > 47){
		$i_code="fig500";
		$i_name="500 Fighter Bays";
		$i_price=15000;
		$i_descr="This Nifty little upgrade allows you to squeeze 500 fighters into one upgrade pod.";
	} elseif($turnip > 42){
		$i_code="upbs";
		$i_name="Battleship Conversion";
		$i_price=20000;
		$i_descr="Enables a ship to do more damage when attacking, and increases shields per hour by <b>50%</b>.<br>(already installed on normal battleships).";
	} elseif($turnip > 40) {
		$i_code="up2";
		$i_name="Terra Maelstrom (Upgrade)";
		$i_price=10000000;
		$i_descr="The only upgrade for the Brobdingnagian (Can only be used on Brobdingnagians). Rare, but extremely potent, this replaces the Quark Disrupter with a weapon that is capable of crippling planets.<br>Get it while its available.";
	} elseif($turnip > 36) {
                $i_code="car1500";
                $i_name="1500 Cargo Bays";
                $i_price=75000;
                $i_descr="Capable of fitting 1500 Cargo Bays into one upgrade pod, this would do your ship wonders, if you need that extra shopping space.";
        } elseif($turnip > 28) {
                $i_code="car500";
                $i_name="500 Cargo Bays";
                $i_price=15000;
                $i_descr="Capable of fitting 500 Cargo Bays into one upgrade pod, for those quick transfers of colonists I guess.";
        } elseif($turnip > 24) {
                $i_code="she1500";
                $i_name="1500 Sheild Capacitors";
                $i_price=75000;
                $i_descr="Capable of fitting 1500 Sheild Capacitors into one upgrade pod, for those who want their prized ships to stay shiny, i guess.";
        } elseif($turnip > 16) {
                $i_code="she500";
                $i_name="500 Sheild Capacitors";
                $i_price=15000;
                $i_descr="Capable of fitting 500 Sheild Capacitors into one upgrade pod, for those who want to have a better chance of staying alive!.";
        } elseif($turnip > 10) {
                $i_code="upgrade";
                $i_name="5 Upgrade Pods";
                $i_price=1000000;
                $i_descr="Refits your ship with 5 shiny new upgrade pods! Now you can buy that HUGE 3 pod Plasmas screen TV, along with the 2 pod surround sound systems!!!";
/*
        } elseif($turnip > 8) {
                $i_code="Orbit";
                $i_name="Orbital Bombardment Targeting System";
                $i_price=5000000;
                $i_descr="Refits your ship with a new Targeting System, that allows a ship to pinpoint planet side targets to a hair, think what you can do with some eggs, eh?";
*/
        }
}

if (isset($i_code)) {
	echo("- Added $i_name to Bilkos<br />");
	dbn(__FILE__,__LINE__,"insert into ${db_name}_bilkos "
		. "(timestamp,item_type,item_code,item_name,going_price,descr,active) "
		. "values(".time().",'$i_type','$i_code','$i_name','$i_price','$i_descr',1)");
}

	/*
	// Controversial as ever, this is the updated "Sol Scatter" function for Fleets (thanks to Hades)
	// Typically this will restrict access to the primary Sol system (and all alternate "Home" systems added
	// in future versions. It operates by monitoring the time a fleet spends at a home system. Occupation
	// of a home system for more than 1 hour will result in Fleets being scattered at random across the
	// Universe. This is meant primarily as a deterrant rather than strict punishment and all players should
	// be aware that home systems can only be occupied for a limited time. A message is sent as a warning half
	// an hour prior to the "scatter" effect.
	*/

	if($keep_sol_clear == 1)
	{
		// delete scatter switch from fleets no longer located in Sol
		Delete_Scatter(1);
		// scatter remaining fleets located in Sol where scatter switch was set in a prior maintenance
		Sol_Scatter(1);
		 //set the scatter switch for any new fleets now located Sol but not at a previous maintenance
		Add_Scatter(1);
	}

	// update ship and planetary shield charges
	Update_Shields();

	/*
	// Random Event are next for maintenance
	*/

	echo("<br /><u>Random Events Updates</u><br />");

	// DEV: Random Events are a little vague where implemented - need a far more segregated approach for easier
	// development and problem tracking

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
//			$user[location] = 1;
//			$user[ship_id] = 1;
			}
			}
			echo "<br />SN remnant in $bh_sys[star_id] to blackhole<br />";
		echo("- Supernova Remnants to Black Holes updated<br />");
	}
}
	if($r_e >= 2) // entire section handles the effects of Nebulae on ships
	{
		db(__FILE__,__LINE__,"select star_id from ${db_name}_stars where event_random = '2' || event_random = '12'");
		while($to_do = dbr())
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = 0 where location = '$to_do[star_id]' and login_id != '1'");
		}
		// DEV: this section was riddled with misstated db()s and dbr()s which ignored the loop order rule for
		// using separately numbered db and dbr functions
		// probably this was a lay over from the ancient Perl script I converted - since this section is now
		// fixed and re-activated, the results are likely unpredictable without large scale testing
		// A random events rewrote is in the offing I think, it badly needs some updating and new material!
		db(__FILE__,__LINE__,"select s.fighters,s.ship_id,s.login_id,s.ship_name,s.location,s.login_name,s.class_name from ${db_name}_ships s, ${db_name}_users u, ${db_name}_stars star where s.location = star.star_id and star.event_random = '2' and u.login_id = s.login_id and s.ship_categ != 0 and u.turns_run > '$turns_safe' and u.login_id != '1'");
		while($neb_ships = dbr())
		{
			// Multiple nebulae that span multiple SSs can pop up every DM. Chances are high that players
			// will encounter them. Plus, all mining ships currently have a default of 10 fighters and
			// an unupgraded max of 50 fighters. Taking 1-10 fighters per hourly maint is too damaging.
			// Instead, we have a 75% chance of taking no fighters, a 20% chance of taking 1, and
			// a 5% chance of taking 2.
			$kill_rand = rand(1, 100);
			$fig_kill = $kill_rand <= 75 ? 0 : ($kill_rand > 95 ? 2 : 1);
			if ($fig_kill > $neb_ships['fighters'])
			{
				dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where ship_id = '$neb_ships[ship_id]'");
				db2(__FILE__,__LINE__,"select ship_id,location,ship_name from ${db_name}_ships where login_id = '$neb_ships[login_id]'");
				$other = dbr2();
				if(!empty($other))
				{

					// DEV: this update seems to be aimed at shifting command - needs to be updated for the new Fleet
					// structure where the command ship is a property of the fleet not the user per se
					dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = $other[ship_id], location = '$other[location]' where login_id = '$neb_ships[login_id]'");
					dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'Nebulae','$neb_ships[login_id]','$neb_ships[login_id]','The nebulae in Star System #<b>$neb_ships[location]</b> did <b>$fig_kill</b> damage to your <b class=b1>$neb_ships[ship_name]</b>, destroying it.<br />Command was transfered to the <b class=b1>$other[ship_name]</b>.','2')");
					dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'<b class=b1>$neb_ships[login_name]</b> lost a <b class=b1>$neb_ships[class_name]</b> to a nebulae.','$neb_ships[login_id]')");

				}
				else
				{

					// DEV: related to shifting command - this is creating an escape pod where no further user ships
					// exist - this also needs updating for the new Fleet structure
					$rand_star = $neb_ships['location'] - 1;
					dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'Nebulae','$neb_ships[login_id]','$neb_ships[login_id]','The nebulae in Star System #<b>$neb_ships[location]</b> did <b>$fig_kill</b> damage to your <b class=b1>$neb_ships[ship_name]</b>, destroying it.<br />You ejected in an escape pod.', '2')");
					dbn(__FILE__,__LINE__,"insert into ${db_name}_ships (ship_name, login_id, login_name, shipclass, class_name, location, point_value) values('Escape Pod','$neb_ships[login_id]','$neb_ships[login_name]',2,'Escape Pod','$rand_star',5)");
					db2(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where login_id = '$neb_ships[login_id]'");
					$ship_id = dbr2();
					dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '$rand_star', ship_id ='$ship_id[ship_id]' where login_id = '$neb_ships[login_id]'");

				}
			}
			elseif ($fig_kill > 0)
			{
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set fighters = fighters - $fig_kill where ship_id = $neb_ships[ship_id]");
				dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_id,login_id,text,cat) values(".time().",'$neb_ships[login_id]','$neb_ships[login_id]','The nebulae in Star System #<b>$neb_ships[location]</b> did <b>$fig_kill</b> damage to your <b class=b1>$neb_ships[ship_name]</b>.','2')");
			}
		}
		echo("- Nebulae effects on ships have been updated.<br />");
	}


	// Next up is Mining. There are long recognised issues with the older code so hopefully this update will
	// solve most of these - especially the problems with negative resources appearing in star systems

	// With a little luck this section will perform all mining activities with a quarter the code by using the new
	// resource table and some variable replacements if any specific resource name instances such as in SQL queries
	// and tracking arrays.

	// Random events concerning mining may not be operable at the present time

	echo("<br /><u>Mining Updates</u><br />");

	db(__FILE__,__LINE__,"select * from ${db_name}_resources where resource_categ = 0");
	while($resources = dbr())
	{
		echo("- $resources[name] Mining being updated...<br />");
		$sqlname = $resources['sql_name'];
		$name = $resources['name'];
		$star_res = "star_".$sqlname;
		if($sqlname == "metal")
		{
			$re_mining = 4; //the random event number for Metal Mining Rush
			$re_mining_alt = 6; //another random event - possibly supernova remnants with 4X mining speed
			$re_mr_speed = 4; //the multiplier applied to the mining rate for metal events
		}
		elseif($sqlname == "fuel")
		{
			$re_mining = 2; //the random event number for Nebulae
			$re_mining_alt = 6;
			$re_mr_speed = 3;
		}
		elseif($sqlname == "darkmatter") //darkmatter using similar settings for fuel random events by default
		{
			$re_mining = 2; //the random event number for Nebulae
			$re_mining_alt = 6;
			$re_mr_speed = 3;
		}

		db2(__FILE__,__LINE__,"select s.ship_id, s.location, s.mine_rate_$sqlname, s.cargo_bays, s.metal, s.fuel, s.elect, s.organ, s.colon, s.darkmatter, u.turns_run, star.event_random,star.".$sqlname." as ".$star_res." from ${db_name}_stars star, ${db_name}_ships s, ${db_name}_users u where s.mine_mode = ".$resources['resource_id']." and u.login_id = s.login_id and star.star_id = s.location and s.location != 1 and star.".$sqlname." > 0 and (s.cargo_bays - s.metal - s.fuel - s.elect - s.organ - s.colon - s.darkmatter) > 0 and s.mine_rate > 0 group by s.ship_id");
		while($ship = dbr2())
		{
			if(!is_array($tracker))
			{
				$tracker = array();
			}
			if(!is_array($tracker[$sqlname]))
			{
				$tracker[$sqlname] = array();
			}
			$n = 0;
			if(!empty($tracker[$sqlname][$ship['location']]))
			{
				$ship[$star_res] = $tracker[$sqlname][$ship['location']];
			}
			else
			{
				$tracker[$sqlname][$ship['location']] = $ship[$star_res];
			}

			// negative checks
			if($ship[$star_res] < 0)
			{
				$ship[$star_res] = 0;
			}
			if($tracker[$sqlname][$ship['location']] < 0)
			{
				$tracker[$sqlname][$ship['location']] = 0;
			}

			// random event effects
			if($ship['event_random'] == $re_mining || $ship['event_random'] == $re_mining_alt)
			{
				if(rand() > .75)
				{
					$n = $ship['mine_rate_'.$sqlname] + 1;
				}
				elseif(rand() < .25)
				{
					$n = $ship['mine_rate_'.$sqlname] - 1;
				}
				else
				{
					$n = $ship['mine_rate_'.$sqlname];
				}
				$n = $n * $re_mr_speed;

			} else {

				if(rand() > .75)
				{
					$n = $ship['mine_rate_'.$sqlname] + 1;
				}
				elseif(rand() < .25)
				{
					$n = $ship['mine_rate_'.$sqlname] - 1;
				}
				else
				{
					$n = $ship['mine_rate_'.$sqlname];
				}

				if($ship[$star_res] < $n)
				{
					$n = $ship[$star_res];
				}
			}

			// only commit to updating mining if a) the calculated possible mining rate >0 and b) the resource available >0

			if($n > 0 && $ship[$star_res] > 0)
			{
				$free_cargo = $ship['cargo_bays'] - ($ship['metal'] + $ship['fuel'] + $ship['elect'] + $ship['organ'] + $ship['colon'] + $ship['darkmatter']);
				if($free_cargo < $n)
				{
					$n = $free_cargo;
				}
				if($n < 0)
				{
					$n = 0;
				}

				dbn(__FILE__,__LINE__,"update ${db_name}_ships set ".$sqlname." = ".$sqlname." + '$n' where ship_id = $ship[ship_id]");
				if($ship['event_random'] != $re_mining)
				{
					dbn(__FILE__,__LINE__,"update ${db_name}_stars set ".$sqlname." = ".$sqlname." - '$n' where star_id = '$ship[location]'");
					$future_result = $tracker[$sqlname][$ship['location']] - $n;
					if($future_result < 1)
					{
							$tracker[$sqlname][$ship['location']] = 0;
					}
					else
					{
							$tracker[$sqlname][$ship['location']] -= $n;
					}
				}
			}
		}
		echo("- ...$resources[name] Mining updates completed<br />");
	}

	// Update turns for all players
	echo("<br /><u>Updating of Turns</u><br />");
	dbn(__FILE__,__LINE__,"update ${db_name}_users set turns = turns + '$hourly_turns'");
	dbn(__FILE__,__LINE__,"update ${db_name}_users set turns = '$max_turns' where turns > '$max_turns'");
	echo("- Turns for all players have been updated<br />");

	//DEV: Because of the issues with Bilkos, this version of the hourly maintenance contains no supporting code
	// for Bilkos Auctions pending a rewrite

	// Additional Random Event Updates for Events
	echo("<br /><u>Additional Random Event updates for Special Star System Events</u><br />");

	// ensure the random events var is set, otherwise could get a divide by 0 error
	// All mining rushes and solar storm should be removed prior to the generation of new events
	if($r_e > 0 && isset($r_e))
	{
		echo("- Random Event level currently set at <b>$r_e</b><br />");

		// If any nebula has been mined to below the minimum value, reset it back to the minimum value
		// All nebulas regenerate fuel faster, so give them a bonus amount now instead of waiting for the DM
		$neb_fuel_bonus = $rr_fuel_chance_max < 500 ? 500 : $rr_fuel_chance_max;
		$neb_min_fuel = nebula_min_fuel();
		dbn(__FILE__,__LINE__,"update ${db_name}_stars set fuel = '$neb_min_fuel' where event_random = 2 and fuel < '$neb_min_fuel'");
		dbn(__FILE__,__LINE__,"update ${db_name}_stars set fuel = fuel + '$neb_fuel_bonus' where event_random = 2");

		// If any mining rush has been mined to below the minimum value, reset it back to the minimum value
		// All mining rushes regenerate metal faster, so give them a bonus amount now instead of waiting for the DM
		$rush_metal_bonus = $rr_metal_chance_max < 500 ? 500 : $rr_metal_chance_max;
		$rush_min_metal = miningrush_min_metal();
		dbn(__FILE__,__LINE__,"update ${db_name}_stars set metal = '$rush_min_metal' where event_random = 4 and metal < '$rush_min_metal'");
		dbn(__FILE__,__LINE__,"update ${db_name}_stars set metal = metal + '$rush_metal_bonus' where event_random = 4");

		// remove mining rush
		db(__FILE__,__LINE__,"select star_id from ${db_name}_stars where event_random = '4' limit 1");
		$star_var = dbr();
		if(!empty($star_var))
		{
			$temp = round(1000 / ($r_e * $num_ss['num_stars'])) + 4;
			$temp2 = rand(0, $temp);
			if($temp2 == 0)
			{
				dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = 0 where star_id = $star_var[star_id]");
				dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'The rich metal deposits in Star System <b class=b1>#$star_var[star_id]</b> have been exhausted. Mining rates in that system have returned to normal.','-10')");
				echo("- Mining rush in SS#$star_var[star_id] removed<br />");
			}
		}


		// removal of Solar Storm
		db(__FILE__,__LINE__,"select star_id,star_name from ${db_name}_stars where event_random = '12'");
		while($star_var = dbr())
		{
			$temp = round(1800 / ($r_e * $num_ss['num_stars'])) + 4;
			$temp2 = rand(0, $temp);
			if($temp2 == 0)
			{
				dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = 0 where star_id = $star_var[star_id]");
				dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'The Solar Activity in the <b class=b1>$star_var[star_name]</b> system (#<b>$star_var[star_id]</b>) has fallen back to normal levels - meaning the Solar Storm has abated.','-10')");
				echo("- Solar Storm in SS#$star_var[star_id] removed<br />");
			}
		}
	}

	// Solar Storm Generation
	if ($r_e > 1 && isset($r_e))
	{
		$temp = round(2000 / ($r_e * $num_ss['num_stars'])) + 3;
		$chance = rand(0, $temp);
		if ($chance < 2)
		{
			$to_go = rand(1, $num_ss['num_stars']);
			db(__FILE__,__LINE__,"select event_random, star_name from ${db_name}_stars where star_id = '$to_go'");
			$is_it = dbr();
			if ($is_it['event_random'] == 0)
			{
				dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = 12 where star_id = '$to_go'");
				dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'Due to increased Solar Activity in the <b class=b1>$is_it[star_name]</b> system (#<b>$to_go</b>), a Solar Storm is now in progress.','-10')");
				echo("- Solar Storm added to SS#$to_go<br />");
			}
		}
	}

	// Update research and tech units
	Update_TechUnits();

	//DEV: Leech actions and updates have been removed as Leeches are currently unavailable

	//DEV: Politics code has also been removed as a non-functional area in QS

	// finish counting time taken to run this maintenance loop.
	$total_time = time() - $start_time;

	// print to news that maint was run, and how long it took.
	dbn(__FILE__,__LINE__,"insert into ${db_name}_news_maint (timestamp, headline, login_id) values (".time().",'Maintenance run for this game in <b>$total_time</b> seconds.','1')");

	echo("<br />Hourly Maintenance completed in <b>$total_time</b> seconds.<br /><br /><hr />");

}

#
# performs leech actions per hour - damage, drain or survey
#
db(__FILE__,__LINE__,"select * from ${db_name}_leeches");
echo "-- Perforiming Leech Functions --<br />";
while($leechesr = dbr())
	{
	$leeches[] = $leechesr;
	}

while($i<count($leeches)){
	unset($leech);
	$leech = $leeches[$i];
	$i++;
	echo "\t leech #".$leech[leech_id]."::(task)".$leech[task]."<br \>\n";
	$leeched = 10;
	if($leech[task] == 0){
		db2(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$leech[ship_id]'");

		$target_ship = dbr2();

		if(empty($target_ship) or $target_ship[ship_id] != $leech[ship_id])
			{
			dbn(__FILE__,__LINE__,"delete from ${db_name}_leeches where ship_id = '$leech[ship_id]'");
			}


		db(__FILE__,__LINE__,"select * from ${db_name}_users where login_id = '$leech[login_id]'");

		$event = dbr();
		$attack_damage = $leeched;
		$ran1 = rand(0,10);
		$ran2 = rand(-10,0);
		$attack_damage += $ran1 + $ran2;
		$attack_damage = round($target_ship[fighters] * round($attack_damage / 100));
		if ($attack_damage > $target_ship[fighters]) {
			dbn(__FILE__,__LINE__,"delete from ${db_name}_leeches where ship_id = '$leech[ship_id]'"); ###X###
			dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where ship_id = '$target_ship[ship_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$target_ship[fighters]', ships_killed = ships_killed + 1, ships_killed_points = ships_killed_points + '$target_ship[point_value]' where login_id = '$leech[login_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$target_ship[fighters]', ships_lost = ships_lost + 1, ships_lost_points = ships_lost_points + '$target_ship[point_value]',  last_attack = ".time().", last_attack_by = '$event[login_name]' where login_id = '$target_ship[login_id]'");
			db2(__FILE__,__LINE__,"select * from ${db_name}_ships where login_id = '$target_ship[login_id]' && shipclass !='2' && ship_id != '1'");

			$other = dbr2();
			if($other) {
				dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$other[ship_id]', location = '$other[location]' where login_id = '$other[login_id]'");
				dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'$event[login_name]','$event[login_id]','$target_ship[login_id]','A <b class=b1>Leech</b> damaged your <b class=b1>$target_ship[ship_name]</b> in #<b>$target_ship[location]</b> and did <b>$attack_damage</b> damage, destroying it. Command was transfered to the <b class=b1>$other[ship_name]</b>.','2')");
				dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'<b 	class=b1>$target_ship[login_name]</b> lost a <b class=b1>$target_ship[class_name]</b> to <b class=b1>$leech[login_name]</b> in a surprise attack.','$leech[login_id]')");
			#Lost an EP.
			} elseif($target_ship[shipclass] == 2){
				dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '1', ship_id = '1', where login_id = '$target_ship[login_id]'");
				dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'$event[login_name]','$event[login_id]','$target_ship[login_id]','A <b class=b1>Leech</b> damaged your <b class=b1>$target_ship[ship_name]</b> in #<b>$target_ship[location]</b> and did <b>$attack_damage</b> damage, destroying it.','2')");
				dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'<b class=b1>$target_ship[login_name]</b> just lost an <b class=b1>Escape Pod</b> to a <b class=b1>Leech</b> in a surprise attack.','$leech[login_id]')");
				#do the bounty rounds
				if($users[bounty] > 0) {
					dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'The <b>$users[bounty]</b> bounty on <b class=b1>$users[login_name]</b> has been claimed by <b class=b1>$event[login_name]</b>','$leech[login_id]')");
					dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + '$users[bounty]' where login_id = '$event[login_id]'");
					dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$users[login_id]'");
				}
			#Create Escape Pod
			} else {
				db2(__FILE__,__LINE__,"select num_stars from se_games where db_name = '$db_name'");

				$num_ss = dbr2();
				$num_ss[num_stars] = $num_ss[num_stars] -1;
				$rand_star = rand(0, $num_ss[num_stars]) + 1;
				dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'$event[login_name]','$event[login_id]','$target_ship[login_id]','A <b class=b1>Leech</b> damaged your <b class=b1>$target_ship[ship_name]</b> in #<b>$target_ship[location]</b> and did <b>$attack_damage</b> damage, destroying it. You ejected in an escape pod.','2')");
				dbn(__FILE__,__LINE__,"insert into ${db_name}_ships (ship_name, login_id, login_name, shipclass, class_name, location, point_value) values('Escape Pod','$target_ship[login_id]','$target_ship[login_name]','2','Escape Pod','$rand_star',5)");
				dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'<b class=b1>$target_ship[login_name]</b> lost a <b class=b1>$target_ship[class_name]</b> to a <b class=b1>Leech</b>. Said Player is now floating around in an <b class=b1>Escape Pod</b>. Anyone want a new wall-mount?','$leech[login_id]')");
				db(__FILE__,__LINE__,"select * from ${db_name}_ships where login_id = '$target_ship[login_id]'");

				$ship_id = dbr();
				dbn(__FILE__,__LINE__,"update ${db_name}_users set location = '$rand_star', ship_id ='$ship_id[ship_id]' where login_id = '$target_ship[login_id]'");
			}
		} else { #didn't do enough damage to kill ship.
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set fighters = fighters - '$attack_damage' where ship_id = '$target_ship[ship_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$attack_damage' where login_id = '$event[login_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$attack_damage' where login_id = '$target_ship[login_id]'");
		}
	}elseif($leech[task] == 1){
		db2(__FILE__,__LINE__,"select fighters,shields,ship_id,login_id,ship_name,location,class_name,shipclass,login_name,point_value from ${db_name}_ships where ship_id = '$leech[ship_id]'");

		$target_ship = dbr2();
		if($target_ship[shields] < $leeched){
			dbn(__FILE__,__LINE__,"update ${db_name}_leeches set shield = shield + '$target_ship[shields]' where login_id = '$leech[login_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = 0 where ship_id = '$leech[ship_id]'");
		}else{
			dbn(__FILE__,__LINE__,"update ${db_name}_leeches set shield = shield + '$leeched' where login_id = '$leech[login_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = shields - '$leeched' where ship_id = '$leech[ship_id]'");
		}
	}elseif($leech[task] == 2){
		db2(__FILE__,__LINE__,"select fighters,shields,ship_id,login_id,ship_name,location,class_name,shipclass,login_name,point_value from ${db_name}_ships where ship_id = '$leech[ship_id]'");

		$target_ship = dbr2();
		db(__FILE__,__LINE__,"select count(ship_id) as scount from ${db_name}_ships where location = '$target_ship[location]'");

		$ship_count = dbr();
		db(__FILE__,__LINE__,"select count(planet_id) as pcount from ${db_name}_planets where location = '$target_ship[location]'");

		$planet_count = dbr();
		if($target_ship[fighters] != 0 and $target_ship[sheilds] != 0)
			{
			dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'','$leech[login_id]','$leech[login_id]','Start of Report for Leech #<b>$leech[leech_id]</b><br><br><b class=b1>System Details:</b><br><br>Location: Star System # <b>$target_ship[location]</b><br>Ships in System: <b>$ship_count[scount]</b><br>Planets in System: <b>$planet_count[pcount]</b><br><br><b class=b1>Target Ship Details:</b><br><br>Name: <b>$target_ship[ship_name]</b><br>Class: <b>$target_ship[class_name]</b><br>Owner: <b>$target_ship[login_name]</b><br>Fighters: <b>$target_ship[fighters]</b><br>Shields: <b>$target_ship[shields]</b><br><br>End of Report for Leech # <b>$leech[leech_id]</b>','0')");
			}
	}
#end while loop
}


/*
// Functions employed in the hourly maintenance script
*/

// black_hole function by Hades to manage sol scatter - modified for sol scatter by Maugrim
function Sol_Scatter($loc = 1) {
	global $db_name;
	$fleet_scatter_count = 0;
	// Get fleets in system
	db2(__FILE__,__LINE__,"select fleet_id, login_id, login_name, fleet_name, ship_id from ${db_name}_fleets where location = $loc and scatter = 1");
	while($fleet = dbr2()) {
		// Set fleet location
		$rand_star = safe_rand_star($loc);
		dbn(__FILE__,__LINE__,"update ${db_name}_fleets set location = $rand_star, scatter = 0, ramscoop = 0, fleet_link = 0 where fleet_id = $fleet[fleet_id]");
		db(__FILE__,__LINE__,"select ship_id from ${db_name}_users where login_id = '$fleet[login_id]'");
		$comm_ship = dbr();
		// Get ships in current fleet
		$ADODB_FETCH_MODE = 2;
		db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where fleet_id = $fleet[fleet_id]");
		//DEV: fleet by fleet would be faster - though more troublesome for checking user's command ship
		while($f_ship = dbr()) {
			// Set ship location same as fleet
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set location = $rand_star where ship_id = $f_ship[ship_id]");
			// ensure command ship locations are updated for a user
			if($f_ship['ship_id'] == $comm_ship['ship_id']) {
				dbn(__FILE__,__LINE__,"update ${db_name}_users set location = $rand_star where login_id = $fleet[login_id]");
			}
		}
		dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$fleet[login_name]','$fleet[login_id]','$fleet[login_id]','Your Fleet <b class=b1>$fleet[fleet_name]</b> has been moved to Star System #<b>$rand_star</b> from Star System#<b>$loc</b>. <br />This is because the Admin wants to keep Star Systen #<b>1</b> (Sol) clear of squatters.','2')");
		$fleet_scatter_count += 1;
	}
	echo("- <b>$fleet_scatter_count</b> Fleets located in Sol for more than one prior maintenance have been scattered to random locations.<br />");
}

// function to remove the scatter switch from any fleets previously located in Sol, who have since moved
function Delete_Scatter($loc = 1) {
	global $db_name;
	dbn(__FILE__,__LINE__,"update ${db_name}_fleets set scatter = 0 where scatter = 1 and location != '$loc'");
	echo("- Fleet scatter values updated to 0 where no longer located in Sol System.<br />");
}

// function to add the scatter switch to ships currently located in Sol for the current maintenance
function Add_Scatter($loc = 1) {
	global $db_name, $turns_safe;
	//db(__FILE__,__LINE__,"select login_id, login_name, fleet_name, fleet_id from ${db_name}_fleets where scatter = 0 and location = '$loc'");
	db(__FILE__,__LINE__,"select f.login_id, f.login_name, f.fleet_name, f.fleet_id from ${db_name}_fleets f, ${db_name}_users u where f.location = '$loc' and f.scatter = 0 and f.login_id > '3' and u.turns_run > '$turns_safe' and u.login_id = f.login_id group by u.login_id");
	while($fleet = dbr())
	{
		dbn(__FILE__,__LINE__,"update ${db_name}_fleets set scatter = 1 where fleet_id = '$fleet[fleet_id]'");
		dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name,sender_id, login_id, text, cat) values(".time().",'$fleet[login_name]','$fleet[login_id]','$fleet[login_id]','You left at least one of your Fleets ($fleet[fleet_name]) in the Sol System (#<b>1</b>) during the last maintenence. <br /><br />Should the Fleets(s) be in there during the next maintence they will be scattered randomly across the universe.','2')");
	}
	echo("- Fleet scatter values updated to 1 for all ships located in Sol System but not present at last maintenance.<br />");
}


// function to update ship shields and planetary shield charges
function Update_Shields() {
	global $db_name, $hourly_shields;
	// time to update shield re-generation of all ships in the game
	dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = shields + '$hourly_shields' where config REGEXP 'fr'");
	dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = shields + '$hourly_shields' * 0.5 where config REGEXP 'bs'");
	dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = shields + '$hourly_shields' * 1.5 where config REGEXP 'sv'");
	dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = shields + '$hourly_shields' * 2 where config REGEXP 'sw'");
	dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = shields + '$hourly_shields' * 0.25 where config REGEXP 'sh'");
	dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = shields + '$hourly_shields'");
	dbn(__FILE__,__LINE__,"update ${db_name}_planets set shield_charge = shield_charge + '$hourly_shields' * shield_gen where shield_gen > 0");
	$al_she = $hourly_shields*3;
	//DEV: need to convert this to use the new ship_categ field
	dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = shields + '$al_she' where shipclass > '200' && shipclass < '300'");
	dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = max_shields where shields > max_shields");
	dbn(__FILE__,__LINE__,"update ${db_name}_planets set shield_charge = shield_charge + '$al_she' where shield_charge < shield_gen * 1000");
	dbn(__FILE__,__LINE__,"update ${db_name}_planets set shield_charge = shield_gen * 1000 where shield_charge > shield_gen * 1000");
	echo("- Shields on all Ships and Planet Shield Charges have been updated.<br />");
}


// function to give tech units where relevant
function Update_TechUnits() {
	global $flag_bmrkt, $hourly_tech, $db_name;
	if ($flag_bmrkt == 1) {
		$htech = $hourly_tech;
		echo("<br /><u>Research and Tech Unit updates</u><br />");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set tech = tech + '$htech' where colon < 20000 and research_fac > 0");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set tech = tech + ('$htech' * 1.4) where colon >= 20000 and colon <40000 and research_fac > 0");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set tech = tech + ('$htech' * 1.8) where colon >= 40000 and colon <70000 and research_fac > 0");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set tech = tech + ('$htech' * 2.2) where colon >= 70000 and colon <120000 and research_fac > 0");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set tech = tech + ('$htech' * 2.6) where colon >= 120000 and colon <220000 and research_fac > 0");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set tech = tech + ('$htech' * 3) where colon >= 220000 and colon <400000 and research_fac > 0");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set tech = tech + ('$htech' * 3.4) where colon >= 400000 and colon <750000 and research_fac > 0");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set tech = tech + ('$htech' * 3.8) where colon >= 750000 and colon <1300000 and research_fac > 0");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set tech = tech + ('$htech' * 4.2) where colon >= 1300000 and colon <1500000 and research_fac > 0");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set tech = tech + ('$htech' * 4.6) where colon >= 1500000 and colon <1750000 and research_fac > 0");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set tech = tech + ('$htech' * 5) where colon >= 1750000 and research_fac > 0");
		echo("- Research Facility updates completed<br />");

		// ship tech bonus additions
		db(__FILE__,__LINE__,"select ship_id, login_id, tech_bonus from ${db_name}_ships where tech_bonus != 0");
		while ($bonus = dbr())
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_users set tech = tech + '$bonus[tech_bonus]' where login_id = '$bonus[login_id]'");
		}
		echo("- Tech Bonuses for ships have been given to users effected<br />");
	}else{
		echo("- No research updates performed as $flag_bmrkt is not enabled by the Admin<br />");
	}
}

// Create a random powerup in a random location
function Create_Powerup() {
	global $db_name, $max_races, $hourly_turns, $max_turns, $hourly_tech, $flag_research, $flag_bomb, $uv_planets;

	$pup_templates = array(
		'junk' => array( 'name' => "Junk",
				'amount' => 0,
				'type' => 'cash',
				'table' => "users",
				'user_relation' => "login_id",
				'desc' => "Looks like you found useless junk, too bad."),
       		'turns' => array( 'name' => "Extra Turns",
				'type' => 'turns',
				'table' => "users",
				'user_relation' => "login_id",
				'desc' => "You have found %u extra turns."),
		'cash' => array( 'name' => "Extra Cash",
				'type' => 'cash',
				'table' => "users",
				'user_relation' => "login_id",
				'desc' => "You have found %u cash."),
		'tech' => array( 'name' => "Extra Tech",
				'type' => 'tech',
				'table' => "users",
				'user_relation' => "login_id",
				'desc' => "You have found %u tech units."),
		'alpha' => array( 'name' => "Extra Alpha Bomb",
				'amount' => 1,
				'type' => 'alpha',
				'table' => "users",
				'user_relation' => "login_id",
				'desc' => "Somebody set us up the bomb... luckily it was not armed.  You found an Alpha Bomb."),
		'gamma' => array( 'name' => "Extra Gamma Bomb",
				'amount' => 1,
				'type' => 'gamma',
				'table' => "users",
				'user_relation' => "login_id",
				'desc' => "Somebody set us up the bomb... luckily it was not armed.  You found a Gamma Bomb."),
		'delta' => array( 'name' => "Extra Delta Bomb",
				'amount' => 1,
				'type' => 'delta',
				'table' => "users",
				'user_relation' => "login_id",
				'desc' => "Somebody set us up the bomb... luckily it was not armed.  You found a Delta Bomb."),
		'genesis' => array( 'name' => "Extra Genesis Device",
				'amount' => 1,
				'type' => 'genesis',
				'table' => "users",
				'user_relation' => "login_id",
				'desc' => "You have found the Almighty Genesis Device! Maker of planets!"));

	$pup_rand = rand(1, 1000);
	unset($new_pup);
	if ($pup_rand <= 100) {                            // 10% chance of junk
		$new_pup = $pup_templates['junk'];
	} elseif ($pup_rand <= 350) {                      // 25% chance of cash
		$new_pup = $pup_templates['cash'];
		$new_pup['amount'] = rand(5000, 50000);
	} elseif ($pup_rand <= 550) {                      // 20% chance of turns
		if ($hourly_turns > 0) {
			$new_pup = $pup_templates['turns'];
			$turn_amt = rand($hourly_turns*2, $hourly_turns*10);
			$new_pup['amount'] = $turn_amt > intval($max_turns/2) ? intval($max_turns/2) : $turn_amt;
		}
	} elseif ($pup_rand <= 700) {                      // 15% chance of tech
		if ($flag_research && ($hourly_tech > 0)) {
			$new_pup = $pup_templates['tech'];
			$new_pup['amount'] = rand($hourly_tech*5, $hourly_tech*25);
		}
	} elseif ($pup_rand <= 800) {                      // 10% chance of alpha bomb
		if ($flag_bomb) {
			$new_pup = $pup_templates['alpha'];
		}
	} elseif ($pup_rand <= 860) {                      // 6% chance of gamma bomb
		if ($flag_bomb) {
			$new_pup = $pup_templates['gamma'];
		}
	} elseif ($pup_rand <= 890) {                      // 3% chance of delta bomb
		if ($flag_bomb >= 2) {
			$new_pup = $pup_templates['delta'];
		}
	} elseif ($pup_rand <= 900) {                      // 1% chance of genesis device
		if ($uv_planets >= 0) {
			$new_pup = $pup_templates['genesis'];
		}
	} else {                                           // 10% chance of nothing
	}

	if (isset($new_pup)) {
		// This doesn't check to ensure no powerups are here already, but multiple powerups in one SS work ok
		db(__FILE__,__LINE__,"select star_id from ${db_name}_stars "
			. "where star_id > $max_races and event_random != 1 order by rand() limit 1");
		$dbr = dbr();
		if (!empty($dbr['star_id'])) {
			$new_pup['location'] = $dbr['star_id'];
			$new_pup['desc'] = sprintf($new_pup['desc'], $new_pup['amount']);
		        dbn(__FILE__,__LINE__,"insert into ${db_name}_powerups "
				. "VALUES('NULL', '$new_pup[name]', '$new_pup[amount]', '$new_pup[type]', '$new_pup[table]', "
					. "'$new_pup[user_relation]', '$new_pup[location]', '$new_pup[desc]')");
			echo("- Created a $new_pup[amount] $new_pup[name] powerup at SS $new_pup[location].<br />");
		} else {
			echo("- No location found to create a $new_pup[amount] $new_pup[name] powerup.<br />");
		}
	}
}

?>
