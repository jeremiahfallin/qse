<?php
$filename = "add_planetary.php";
/*
A script that contains the code for some of the planetary functions.
Created:
By: Moriarty
Date: 14/6/02
*/

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = '$planet_id'");
$planet = dbr();

$shield_gen_cost = 50000;
$research_fac_cost = 100000;
$wormhole_gen_cost = 550000;
$wormhole_gen_tech = 3000;
$header = "Error";

if($user['location'] != $planet['location']) {
	$out = "That planet is not in this system.";
} elseif($planet['owner_id'] != $user['login_id'] and $user[login_id] != 1) {
	$out = "Only the owner may authorise that action.";
// build missile
} elseif($missile) {
	$header = "Omega Missile Construction";
	$turn_cost = max(10, $planet_attack_turn_cost);
	if($user['cash'] < 100000 || $planet['elect'] < 50 || $planet['metal'] < 200 || $planet['fuel'] < 100) { #134800
		$out .= "You can not afford a <b class=b1>Omega Missile</b>.";
	} elseif($enable_superweapons == 0){
		$out .= "Admin has disabled the ability to use these weapons, as such, building a missile is pointless.";
	} elseif($user['turns'] < $turn_cost){
		$out .= "You to do not have enough turns to build a <b class=b1>Omega Missile</b>.";
	} elseif($planet['missile'] != 0) {
		$out .= "You already have a <b class=b1>Omega Missile</b> on this planet.";
	} elseif($sure != 'yes') {
		get_var('Build Omega Missile','add_planetary.php','Are you sure you want to build a <b class=b1>Omega Missile</b>?','sure','yes');
	} else {
		take_cash(100000);
		charge_turns($turn_cost);
		$out .= "Your new <b class=b1>Omega Missile</b> is ready, and your planet has been debited the materials needed to construct the Missile.";
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set missile = '-1', elect = elect - 50, metal=metal-200,fuel=fuel-100 where planet_id = $planet[planet_id]");
	}

// Fires missile
}elseif($launch_missile){
	$header = "Launch Omega Missile";

	if($user['turns'] < 5){
		$out .= "You will require at least <b>5</b> turns to launch the missile, no matter the desination.";
	} elseif($enable_superweapons == 0){
		$out .= "Admin has disabled the ability to use these weapons.";
	} elseif(!$planet['missile']){
		$out .= "You do not have a missile on this planet.";
	} elseif(!$destination){
		if($user['clan_id'] >0){
			db(__FILE__,__LINE__,"select planet_name,planet_id from ${db_name}_planets where clan_id != '$user[clan_id]' && owner_id != 1 && planet_id != $planet_id && location != $planet[location] && owner_id != $user[login_id]");
		} else {
			db(__FILE__,__LINE__,"select planet_name,planet_id from ${db_name}_planets where owner_id != 1 && planet_id != $planet_id && location != $planet[location] && owner_id != $user[login_id]");
		}
		$enemy_planets=dbr();
		if(!$enemy_planets){
			$out .= "There are no planets in the game that you can fire the missile at.";
		} else {
			$out .= "Select a planet to fire the missile at:";
			$out .= "<form method=post action=add_planetary.php name=missile_form>";
			$out .= "<input type=hidden name=launch_missile value=-1>";
			$out .= "<input type=hidden name=planet_id value=$planet_id>";
			$out .= "<select name=destination>";
			while($enemy_planets){
				$out .= "<option value=$enemy_planets[planet_id]> $enemy_planets[planet_name] ";
				$enemy_planets=dbr();
			}
		$out .= "</select>";
		$out .= "<p><input type=submit value=Launch></form><p>";
		}
	} else {
		db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = '$destination'");
		$target_planet = dbr();
		$turns = get_star_dist($user['location'],$target_planet['location']);
		if($turns < 12) {
			$turns = 5;
		} else {
			$turns = $turns -5;
		}
		$fuel = $turns * 20;

		if($target_planet['location'] == $planet['location']){
			$out .= "Missiles may not be fired at planets in the same system as the launching planet due to fallout concerns.";
		} elseif(($target_planet['clan_id'] == $user['clan_id'] && $user['clan_id'] >0) || $target_planet['owner_id'] == $user['login_id'] || $target_planet['owner_id'] == 1) {
			$out .= "That planet is an invalid target.";
		} elseif($user['turns'] < $turns){
			$out .= "You do not have enough turns to launch your missile at that planet.<br />You have <b>$user[turns]</b> and require <b>$turns</b>.";
		} elseif($planet['fuel'] < $fuel){
			$out .= "You require more fuel on the planet to launch at that target.<br />You have <b>$planet[fuel]</b> and require <b>$fuel</b>.";
		} elseif($sure != 'yes') {
			get_var('Launch Confirmation','add_planetary.php',"Please <b>Confirm</b> that you want to fire your <b class=b1>Omega Missile</b> at the planet <b class=b1>$target_planet[planet_name].<p>This destination will require <b>$turns</b> Turns, and <b>$fuel</b> Fuel to prep for Launch?",'sure','');
		} else {
			charge_turns($turns);
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set missile=0, fuel=fuel-'$fuel' where planet_id = '$planet_id'");
			$out .= "Counting Down to Launch:<p>T Minus: 5... 4... 3... 2... 1...	<b class=b1>Liftoff</b>.";
			$out .= "<p>The <b class=b1>Omega Missile</b> has been successfully launched, with the target <b class=b1>$target_planet[planet_name]</b> as its destination.";
			$out .= "<br /><br /><br /><br />Missile has struck the planet <b class=b1>$target_planet[planet_name]</b>. Damage report follows:";
			// fighters destroyed
			if($target_planet['fighters'] > 1000){
				$damage_done = round($target_planet['fighters'] /100 * 4);
				$damage_done += round(rand(-$damage_done * .15,$damage_done * .15));
				$out .= "<p>The missile destroyed <b>$damage_done</b> fighters.";
			} else {
				$damage_done = $target_planet['fighters'];
				$out .= "<p>The missile took out all <b>$damage_done</b> fighters that were on the planet.";
			}

			if(!$annihil){
				// colonists killed
				if($target_planet['colon'] > 3000){
					$colon_killed = round($target_planet[colon] /100 * 4);

					$colon_killed += round(rand(-$colon_killed * .15,$colon_killed * .15));
					$out .= "<br />The missile also killed <b>$colon_killed</b> colonists.";
				} elseif($target_planet['colon'] > 0) {
					$colon_killed = $target_planet['colon'];
					$out .= "<br />The missile also killed all <b>$colon_killed</b> colonists that were on the planet.";
				} else {
					$colon_killed = 0;
					$out .= "<br />The Missile failed to kill any Colonists as there were none on the planet.";
				}
				$out .= "<p>Damage report ends.";

				// prepare ratios so damage is properly distributed and assigned colos reset
				$col_fight = $target_planet['alloc_fight'] / $target_planet['colon'];
				$col_organ = $target_planet['alloc_organ'] / $target_planet['colon'];
				$col_elect = $target_planet['alloc_elect'] / $target_planet['colon'];
				$new_colon = $target_planet['colon'] - $colon_killed;
				$final_fight = round($col_fight * $new_colon);
				$final_organ = round($col_organ * $new_colon);
				$final_elect = round($col_elect * $new_colon);
				// $final_colon = $new_colon - $final_fight - $final_organ - $final_elect;

				dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighters = fighters - '$damage_done', colon='$new_colon', alloc_fight='$final_fight',alloc_elect='$final_elect',alloc_organ='$final_organ' where planet_id = '$destination'");
				send_message($target_planet['owner_id'],"<b class=b1>$user[login_name]</b> launched an Omega Missile at your planet <b class=b1>$target_planet[planet_name]</b> (system #<b>$target_planet[location]</b>) taking out <b>$damage_done</b> fighters, and <b>$colon_killed</b> colonists.");
			}

			post_news("<b class=b1>$user[login_name]</b> launched an <b class=b1>Omega Missile</b> at the planet <b class=b1>$target_planet[planet_name]</b>.");
		}
	}

// build a launch pad
} elseif($launch_pad) {
	$header = "Launch Pad Construction";
	if($user['cash'] < 100000 || $planet['elect'] < 200 || $planet['metal'] < 100 || $planet['fuel'] < 100) { #146000
		$out .= "You can not afford a <b class=b1>Missile Launch Pad</b>.";
	} elseif($enable_superweapons == 0){
		$out .= "Admin has disabled the ability to use these weapons, as such, building a missile pad is pointless.";
	} elseif($planet['launch_pad'] != 0) {
		$out .= "You already have a <b class=b1>Missile Launch Pad</b> on this planet.";
	} elseif($sure != 'yes') {
		get_var('Build Missile Launch Pad','add_planetary.php','Are you sure you want to build a <b class=b1>Missile Launch Pad</b>?','sure','yes');
	} else {
		take_cash(100000);
		$out .= "Construction of the <b class=b1>Missile Launch Pad</b> is under way.<br />It will be completed in <b>24hrs</b>.<br />Your planet has been debited the materials needed to construct the Pad.";
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set launch_pad = '25', elect = elect - 200, metal=metal-100,fuel=fuel-100 where planet_id = $planet[planet_id]");
	}

// build a research facility
}elseif ($flag_research == 1 && isset($research_fac)) {
	$header = "Research Facility";

	// check to see how many research centres the user has.
	db(__FILE__,__LINE__,"select count(planet_id) from ${db_name}_planets where research_fac = 1 && owner_id = $user[login_id]");
	$num_research = dbr();
	if($user['cash'] < $research_fac_cost) {
		$out .= "You can not afford a <b class=b1>Research Facility</b>.";
	} elseif($planet['research_fac'] != 0) {
		$out .= "You already have a <b class=b1>Research Facility</b> on this planet.";
	} elseif($num_research['0'] > 1) {
		$out .= "You may only own two research centres at a time.";
	} elseif($sure != 'yes') {
		get_var('Buy Research Facility','add_planetary.php','Are you sure you want to purchase a <b class=b1>Research Facility</b>?','sure','yes');
	} else {
		take_cash($research_fac_cost);
		$out .= "You have purhcased and installed a <b class=b1>Research Facility</b> on the planet <b class=b1>$planet[planet_name]</b> at the cost of <b>$research_fac_cost</b> Credits.<br />You are only allowed two research centres at a time, with a maximum of 1 per planet.";
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set research_fac = '1' where planet_id = $planet[planet_id]");
	}

}elseif ($flag_bmrkt == 1 && isset($wormhole_gen)) {
	$header = "Wormhole Generator";

	// check to see how many research centres the user has.
	db(__FILE__,__LINE__,"select count(planet_id) from ${db_name}_planets where wormhole_gen = 1 && owner_id = $user[login_id]");
	$num_research = dbr();
	if($user['cash'] < $wormhole_gen_cost || $user['tech'] < $wormhole_gen_tech) {
		$out .= "You can not afford a <b class=b1>Wormhole Generator</b>.";
	} elseif($planet['wormhole_gen'] != 0) {
		$out .= "You already have a <b class=b1>Wormhole Generator</b> on this planet.";
	} elseif($num_research['0'] > 0) {
		$out .= "You may only own one wormhole generator at a time.";
	} elseif($sure != 'yes') {
		get_var('Buy Wormhole Generator','add_planetary.php','Are you sure you want to purchase a <b class=b1>Wormhole Generator</b>?','sure','yes');
	} else {
		take_cash($wormhole_gen_cost);
		take_tech($wormhole_gen_tech);
		$out .= "You have purhcased and installed a <b class=b1>Wormhole Generator</b> on this planet, <b class=b1>$planet[planet_name]</b> at the cost of <b>$wormhole_gen_cost</b> Credits and <b>$wormhole_gen_tech</b> Technical Support Units. <br />You are only allowed one wormhole generator at a time.";
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set wormhole_gen = '1' where planet_id = $planet[planet_id]");
	}

// generate a wormhole
} elseif($generate_wh == 1) {
	$header = "Wormhole Generation";
	db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = '$planet[location]'");
	$star_inf = dbr();
	if($user['cash'] < 150000 || $user['turns'] < 200 || $planet['elect'] < 1000 || $planet['darkmatter'] < 1200) {
		$out .= "You can not afford to generate a <b class=b1>Wormhole</b>.";
	} elseif($star_inf['wormhole'] != 0) {
		$out .= "A wormhole has already been established in this star system.";
	} elseif($planet['wormhole_gen'] == 0) {
		$out .= "You require a Wormhole Generator to create artificial wormholes.";
	} elseif($sure != 'yes') {
		get_var('Generate Wormhole','add_planetary.php','Are you sure you want to generate an <b class=b1>Artificial Wormhole</b>?','sure','yes');
	} else {
		$out .= "Enter your proposed location of the <b class=b1>Artificial Wormhole</b>'s opposite end outside this star system:<br /><br />The distance between these two points determine the additional Dark-Matter required to keep the portal operational each day.<br /><br />";
		$out .= "<form name=wormhole_form action=add_planetary.php method=post>";
		$out .= "<input type=hidden name=planet_id value=$planet[planet_id]>";
		$out .= "<input type=hidden name=generate_wh value=2>";
		$out .= "<input name=wh_loc size=5>";
		$out .= "<br /><p><input type=submit value=Submit></form>";
	}
} elseif($generate_wh == 2) {
	$header = "Wormhole Generation";
	db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = '$planet[location]'");
	$star_inf = dbr();
	if($user['cash'] < 150000 || $user['turns'] < 200 || $planet['elect'] < 1000 || $planet['darkmatter'] < 1200) {
		$out .= "You can not afford to generate a <b class=b1>Wormhole</b>.";
	} elseif($star_inf['wormhole'] != 0) {
		$out .= "A wormhole already exists in this star system.";
	} elseif(!isset($wh_loc)) {
		$out .= "Any wormhole requires a location for its far end!";
	} elseif($star_inf['star_id'] == $wh_loc) {
		$out .= "The wormhole requires two different star systems.";
	} elseif(($wh_loc <= $max_races) || ($wh_loc > $uv_num_stars)) {
		$out .= "You cannot open wormholes to this location.";
	} elseif($planet['wormhole_gen'] == 0) {
		$out .= "You require a Wormhole Generator to create artificial wormholes.";
	} elseif($sure != 'yes') {
		get_var('Generate Wormhole','add_planetary.php',
			"Are you completely certain you want to generate an <b class=b1>Artificial Wormhole</b> "
			. "between star system <b>$star_inf[star_id]</b> and <b>$wh_loc</b>?",'sure','yes');
	} else {
		$dm_cost = get_star_dist($star_inf['star_id'],$wh_loc);
		take_cash(150000);
		charge_turns(200);
		$user['cash'] -= 150000;
		$user['turns'] -= 200;
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set elect = elect - 1000, darkmatter = darkmatter - 1200, wormhole = '$wh_loc' where planet_id = $planet[planet_id]");
		dbn(__FILE__,__LINE__,"update ${db_name}_stars set wormhole = '$wh_loc', art_wh = '$dm_cost', wh_login = '$user[login_id]', wh_clanid = '$user[clan_id]' where star_id = '$star_inf[star_id]'");
		dbn(__FILE__,__LINE__,"update ${db_name}_stars set wormhole = '$star_inf[star_id]', art_wh = '-1', wh_login = '$user[login_id]', wh_clanid = '$user[clan_id]' where star_id = '$wh_loc'");
		$out .= "Your <b class=b1>Artificial Wormhole</b> has been generated between star systems <b>$star_inf[star_id]</b> and <b>$wh_loc</b>. It will cost <b>$dm_cost</b> Dark-Matter per daily maint to remain stable. You can safely collapse your wormhole when needed to prevent an unstable and possibly dangerous space-time fluctuation, caused by Dark-Matter not being available to maintain its stability.";
	}

} elseif($generate_wh == 3) {
	$header = "Wormhole Collapse";
	db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = '$planet[location]'");
	$star_inf = dbr();
	$wh_loc = $star_inf['wormhole'];
	if($star_inf['wormhole'] == 0) {
		$out .= "There is no wormhole in this star system.";
	} elseif($planet['wormhole_gen'] == 0) {
		$out .= "You require a Wormhole Generator to collapse artificial wormholes.";
	} elseif($planet['wormhole'] == 0) {
		$out .= "This Planet has generated no wormhole. All wormholes can only be collapsed from their own planetary Generator.";
	} elseif($star_inf['art_wh'] < 0) {
		$out .= "This wormhole can only be collapsed from its primary source location.";
	} elseif($star_inf['art_wh'] > 0 && $planet['wormhole'] == $star_inf['wormhole'] && $sure != 'yes') {
		get_var('Collapse Wormhole','add_planetary.php','Are you completely certain you want to collapse the <b class=b1>Artificial Wormhole</b> in this star system?','sure','yes');
	} else {
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set wormhole = 0 where planet_id = '$planet[planet_id]'");
		dbn(__FILE__,__LINE__,"update ${db_name}_stars set wormhole = 0, art_wh = 0, wh_login = 0, wh_clanid = 0 where star_id = '$star_inf[star_id]'");
		dbn(__FILE__,__LINE__,"update ${db_name}_stars set wormhole = 0, art_wh = 0, wh_login = 0, wh_clanid = 0 where star_id = '$wh_loc'");
		$out .= "Your <b class=b1>Artificial Wormhole</b> has been collapsed between star systems <b>$star_inf[star_id]</b> and <b>$wh_loc</b>. You may generate another if required.";
	}

// build a shield generator
}elseif($shield_gen) {
	$header = "Shield Generator Construction";
	if($user['cash'] < $shield_gen_cost) {
		$out .= "You can not afford a <b class=b1>Shield Generator</b>.";
	} elseif($planet['shield_gen'] != 0) {
		$out .= "You already have a <b class=b1>Shield Generator</b> on this planet.";
	} elseif($sure != 'yes') {
		get_var('Buy Shield Generator','add_planetary.php','Are you sure you want to purchase a <b class=b1>Shield Generator</b>?','sure','yes');
	} else {
		take_cash($shield_gen_cost);
		$out .= "You have purhcased and installed a <b class=b1>Shield Generator</b> on the planet <b class=b1>$planet[planet_name]</b> at the cost of <b>$shield_gen_cost</b> Credits.";
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set shield_gen = '3' where planet_id = $planet[planet_id]");
	}

}elseif($super_hostile) {
        $header = "Super Hostile Weapon Construction";

        for($i=0;$i<$max_races;$i++) {
        $home_num = $i+1;
        $sol_dist = 0;
        $sol_dist = get_warp_dist($user['location'],$home_num);
        if( $sol_dist <= $banned_mine_dist and $user[login_id] != 1) {
                        $error_str = "You cannot create Super Hostile Weapons within <b>$banned_mine_dist</b> warp jumps of a Homeworld System! You are currently <b>$sol_dist</b> warp jumps from the Homeworld System #$home_num.";
                        print_page("$m_name",$error_str);
                        }
                }
        db(__FILE__,__LINE__,"select count(*) as total from ${db_name}_planets where owner_id = $planet[owner_id] and super_hostile = 1");
        $raw_num = dbr();
        $num_sh_planets = $raw_num[0];
	$price = pow(2,$num_sh_planets) * 1000000;
        if($user['cash'] < $price) {
                $out .= "You can not afford <b class=b1>Super Hostile Weapons</b>.";
        } elseif($planet['super_hostile'] != 0) {
                $out .= "You already have <b class=b1>Super Hostile Weapons</b> on this planet.";
        } elseif($sure != 'yes') {
                get_var('Buy Super Hostile Weapons','add_planetary.php','Are you sure you want to purchase <b class=b1>Super Hostile Weapons</b> for <b>' . number_format($price) . ' credits?','sure','yes');
        } else {
                take_cash($price);
                $out .= "You have purhcased and installed <b class=b1>Super Hostile Weapons</b> on the planet <b class=b1>$planet[planet_name]</b> at the cost of <b>$price</b> Credits.";
                dbn(__FILE__,__LINE__,"update ${db_name}_planets set super_hostile = '1' where planet_id = $planet[planet_id]");
        }


} else {
	$out = "You shouldn't be at this page without a reason";
}

print_page($header,$out."<p><a href='planet.php?planet_id=$planet_id'>Back to The Planet</a>");
?>
