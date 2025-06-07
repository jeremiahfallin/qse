<?php
include_once("includes/nocache.inc.php");

require_once("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

require_once("includes/planet_funcs.inc.php");
require_once("includes/upgrade_funcs.inc.php");





sudden_death_check($user);

mt_srand((double)microtime()*1000000);

$ship_config = get_config($user['ship_id']);

db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = '$planet_id'");
$planet = dbr();

$planet_pass = $planet['pass'];
$planet_loc = $planet['location'];
$has_pass = 0;
$low_access = 0;

#these two are also harboured in the add_planetary.php script (i didn't think they were used regularly enough to warrent becoming truely global vars) - Moriarty {{warrant && truly - really Mori...the mind boggles :) }}
$shield_gen_cost = 50000;
$research_fac_cost = 100000;
$wormhole_gen_cost = 550000;
$wormhole_gen_tech = 3000;


//---------------------------------------------------------------------------------------------------------//
// This entire segment performs *many* checks before a player can access to planet - confusing, eh? :)     //
//---------------------------------------------------------------------------------------------------------//

if($user['location'] != $planet['location'])
{
	print_page("Planet","That planet is not in this system.");
}

// it just gets better! where is $passwd coming from?

if(!$passwd && ($planet['owner_id'] != $user['login_id']))
{
	$low_access = 1;
}

if($user['login_id'] == 1 && $planet['planet_type'] < 0)
{
	print_page("No Admins","Admins are not allowed to do anything to random planets.");
}

if($planet['owner_id'] == $user['login_id'])
{
	$has_pass = 1;
}
elseif($planet['fighters'] == 0 || $user['login_id'] == 1)
{
	$has_pass = 1;
	$low_access = 1;
}

if($planet['barren'] == 1 && $user['login_id'] != 1)
{
	print_page("Barren World","Due to the phenomenal levels of radiation that stream from <b class=b1>Barren Worlds</b> such as this one, you cannot land here. Your sensors are also unable to penetrate the haze of radioactive material present in the planets atmosphere.");
}


if($p_pass && !$passwd)
{
	$passwd = $p_pass;
}
else
{
	SetCookie("p_pass",$passwd,time()+600);
}


if($passwd)
{
	if(($passwd == $planet_pass) && !preg_match("/^0$/",$planet_pass))
	{
		$has_pass = 1;
	}
	else
	{
		SetCookie("p_pass");
		print_page("Access Denied","The password you entered was not correct");
	}
}
elseif($user['on_planet'] == $planet_id && $planet['fighters'] > 0 && $user['login_id'] != 1 && !preg_match("/^0$/",$planet_pass))
{
	get_var('Access Denied','planet.php','The owner has set a password on this planet. You must enter the password to continue.','passwd',"");
}

if($want_access && !$passwd && $planet_pass)
{
	get_var('Access Denied','planet.php','The owner has set a password on this planet. You must enter the password to continue.','passwd',"");
}

//---------------------------------------------------------------------------------------------------------//
// And the confusion comes to an end... :)																   //
//---------------------------------------------------------------------------------------------------------//


if($passwd2 && $change_pass && $has_pass && valid_input($passwd2))
{
	SetCookie("p_pass");
	if($passwd2 == -1)
	{
		$passwd2 = 0;//geez Mori default is zero not null...<shakes head>
	}
	dbn(__FILE__,__LINE__,"update ${db_name}_planets set pass = '$passwd2' where planet_id = $planet_id");
	$planet_pass = $passwd2;
	$passwd = $passwd2;
	print_page("Password Changed","The password was changed successfuly.<p><a href='planet.php?planet_id=$planet_id&passwd=$passwd2'>Back to planet</a>");
}
elseif($passwd2 && $change_pass && $has_pass)
{
	print_page("Password Changed","That password is invalid. Only user normal letters and numbers, and no spaces.<p><a href='javascript:back()'>Try Again</a>");
}
elseif($change_pass && !$passwd2)
{
	get_var('Planet Password','planet.php','What would you like the password to be?<br />Setting the pass to "-1" means no password.','passwd2',"");
}

// This code must spend more bytes on validating this damn password than on the actual planet functions!!!

if(!isset($planet_pass) && (($planet['clan_id'] == $user['clan_id'] && $user['clan_id'] > 0) || ($planet['owner_id'] == $user['login_id'])))
{
	$has_pass = 1;
}

if($has_pass) {
	$user['on_planet'] = $planet_id;
}

//ensure $amount is rounded, and an integer. (be great if $amount was EVER used, feeling a little irate... :( )
if($amount) {
	$amount = round($amount);
	settype($amount, "integer");
}

// why need to be on planet? Can't our players simply use a state of the art sub-space communicator?
// retort: Doh, if there are two planets in the system - we need to be sure which one we're accesssing.
if($user['on_planet'] > 2)
{
	$planet_id = $user['on_planet'];
}


// can't even land? Oh come on... <delete this too, Maug!>
if ($user['turns_run'] < $turns_before_planet_attack && $user['login_id'] != 1) {
	print_page("No landing","Cannot land, create or attack a planet within the first <b class=b1>$turns_before_planet_attack turns </b>of your account.");
}

if($user['on_planet'] != $planet_id) {
	db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = '$planet_id'");
	$planet = dbr();
	if($planet[owner_id] == 1) {
		print_page("Attacking Planet","You may not attack admin planets.");
	} elseif($ship_config[so]) {
		print_page("Attacking Planet","Your ship is not equipped to attack planets, it can only attack ships.");
	} elseif($user[location] != $planet[location]) {
		print_page("Attacking Planet","That planet is not in this star system.");
	} elseif((($user[login_id] != $planet[owner_id]) && ($planet[fighters] > 0) && ($user[clan_id] != $planet[clan_id]) && ($user[login_id] != 1 || $planet[unigen] != 2)) || ($planet[owner_id] == 0 && $planet[fighters] > 0 && $planet[unigen] == 1)) {
//	} elseif($planet[owner_id] == 0 && $planet[fighters] > 0 && $planet[unigen] == 1) {

		db(__FILE__,__LINE__,attack_planet_check($db_name,$user));
		$attackable_planet=dbr();
		if(!empty($attackable_planet) && ($attackable_planet['planet_id'] != $planet['planet_id'])) {
			report_illegal_planet_attack($planet,$attackable_planet);
			print_page('Attacking Planet',illegal_planet_attack_warning($planet));
		} elseif ($flag_planet_attack == 0) {
			print_page("Attacking Planet","The admin has disabled planet attacks.");
		} elseif($sure != 'yes') {
			get_var('Attacking Planet','planet.php',
				"Are you sure you want to attack planet <b class=b1>$planet[planet_name]</b> with ship <b class=b1>$user_ship[ship_name]</b>?",
				'sure','yes');
		} elseif($user[turns] < $planet_attack_turn_cost) {
			print_page("Attacking Planet","You don't have the <b>$planet_attack_turn_cost</b> turns to use for the attack.");
		} else {

			#determine ship bonuses
			$attacker = $user_ship;
//			$user_attack_bonus = $attacker['pc'] + $attacker['ot'] + $attacker['ewa'];
//			$user_defense_bonus = $attacker['sa'] + $attacker['dt'] + $attacker['ewd'];
			$user_attack_bonus = CheckShip_ForAttack($user_ship['ship_id']);
			$user_defense_bonus = CheckShip_ForDefence($user_ship['ship_id']);

			charge_turns($planet_attack_turn_cost);

			$attack_damage = round($user_ship[fighters] * .65);
			$attack_damage += mt_rand(round(-$user_ship[fighters] * .15),round($user_ship[fighters] * .15) + 1);
			$attack_damage = $attack_damage + $user_attack_bonus; //apply attack bonus
			$counter_damage = round($planet[fighters] * .75);
			$counter_damage += mt_rand(round(-$planet[fighters] * .15),round($planet[fighters] * .15) + 1);
			if($counter_damage < $user_defense_bonus) {// apply defense bonus
				$counter_damage = 0;
			} else {
				$counter_damage = $counter_damage - $user_defense_bonus;
			}

			$attack_message = "The <b class=b1>$user_ship[ship_name]</b> attacked your planet "
				. "<b class=b1>$planet[planet_name]</b> with <b>$user_ship[fighters]</b> fighters.<br />"
				. "The <b class=b1>$user_ship[ship_name]</b> did <b>$attack_damage</b> damage and "
				. "took <b>$counter_damage</b> damage.";

			#user looses ship whilst attacking
			$planet[login_id] = $planet[owner_id];
			if(do_damage($counter_damage,$planet,$user,$user_ship)) {
				post_news("<b class=b1>$user[login_name]</b>\'s $user_ship[class_name] was destroyed while attacking the planet <b class=b1>$planet[planet_name].</b>");
				if ($planet[owner_id] != 0) {
					send_message($planet[owner_id],"$attack_message<br />The <b class=b1>$user_ship[ship_name]</b> was destroyed.",1);
				}
				if($planet[fighters] <= $attack_damage){ #planet gets taken out none-the-less
					$attack_damage = $planet[fighters];
					$darn = 1;
				}
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighters = fighters - '$attack_damage' where planet_id = $planet[planet_id]");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$attack_damage' where login_id = '$planet[owner_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$attack_damage' where login_id = '$user[login_id]'");

				db(__FILE__,__LINE__,"select * from ${db_name}_users where login_id = '$user[login_id]'");
				$user = dbr();
				$old_ship = $user_ship;
				db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
				$user_ship = dbr();
				empty_bays();
				if($user[ship_id] == 1) { #now dead
					$output_str .= "<br />You destroyed <b>$attack_damage</b> fighters, and took <b>$counter_damage</b> damage.";
					$output_str .= "<br /><b class=b1>$planet[planet_name]</b> successfully blew your Escape Pod to smitherines.";
				// $output_str .= "<br />You are out of the game for $hours_after_death hours.";
				} elseif($user_ship[shipclass] == 2) {#Now in an ep
					$output_str .= "<br />You destroyed <b>$attack_damage</b> fighters, and took <b>$counter_damage</b> damage.";
					$output_str .= "<br />The planet <b class=b1>$planet[planet_name]</b> destroyed your <b class=b1>$old_ship[class_name].</b>";
					$output_str .= "<br />You ejected in an Escape Pod.";
				} else {#got another ship
					$output_str .= "<br />You destroyed <b>$attack_damage</b> fighters, and took <b>$counter_damage</b> damage.";
					$output_str .= "<br />The planet <b class=b1>$planet[planet_name]</b> destroyed your <b class=b1>$old_ship[class_name].</b>";
					$output_str .= "<br />You are now in command of the <b class=b1>$user_ship[ship_name]</b>.";
				}
/*				if($darn==1){
					$output_str .= "<br /><br />However you did still manage to take out all the planets fighters in the attack.";
				}*/
				print_page("Attack Failed",$output_str);

				#user takes out all planets defences
			} elseif($attack_damage >= $planet[fighters]) {
				if ($planet[owner_id] != 0) {
					send_message($planet[owner_id],"$attack_message<br />All fighters on planet <b class=b1>$planet[planet_name]</b> were destroyed.",1);
				}
				$output_str .= "<P>You took <b>$counter_damage </b>hits and destroyed all <b>$planet[fighters]</b> fighters.<br />";
				db2(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = $user_ship[ship_id]");
				$temp_ship = dbr2();
				$temp_figs = $user_ship[fighters] - $temp_ship[fighters];
				dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$planet[fighters]' where login_id = '$planet[owner_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$planet[fighters]', on_planet = '$planet_id' where login_id = '$user[login_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighters = 0 where planet_id = '$planet[planet_id]'");
				$planet[fighters] = 0;
				db2(__FILE__,__LINE__,"select * from ${db_name}_users where login_id = '$user[login_id]'");
				$user = dbr2();
				db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
				$user_ship = dbr();

				// If there are still planets with hostile fighters, the user cannot claim the planet
				db(__FILE__,__LINE__,attack_planet_check($db_name,$user));
				$attackable_planet=dbr();
				if(!empty($attackable_planet)) {
					print_page("Attack Successful","$output_str<br />Before you can claim this planet, "
						. "you must defeat all hostile fighters on other planets in this system.");
				}

			#nothing dies.
			} else {
				if ($planet[owner_id] != 0) {
					send_message($planet[owner_id],$attack_message,1);
				}
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighters = fighters - '$attack_damage' where planet_id = '$planet[planet_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_lost = fighters_lost + '$attack_damage' where login_id = '$planet[owner_id]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_users set fighters_killed = fighters_killed + '$attack_damage' where login_id = '$user[login_id]'");
				// message
				db(__FILE__,__LINE__,"select * from ${db_name}_users where login_id = '$user[login_id]'");
				$user = dbr();
				db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
				$user_ship = dbr();
				print_page("Attack Failed","You took <b>$counter_damage </b>hits and took out <b>$attack_damage</b> fighters.");
			}
		}
	} else {
		dbn(__FILE__,__LINE__,"update ${db_name}_users set on_planet = '$planet_id' where login_id = '$user[login_id]'");
		$user[on_planet] = $planet_id;
	}
}
$rs = "<p><a href=planet.php?planet_id=$planet_id>Return to Planet</a><br />";



if((isset($destroy) && ($user[on_planet] != 1))) {
	db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = $user[on_planet]");
	if($user[login_id] == $planet[owner_id] || ($user[clan_id] == $planets[clan_id] && $user[clan_id] > 0)) {
		if($user[terra_imploder] <= 0){
			$output_str.= "The destruction of a planet requires the use of a Terra Imploder to irradiate the surface with radiation and make the planet barren.";
		} elseif($sure != 'yes') {
			get_var('Irradiate Planet','planet.php','Are you sure you want to irradiate this planet using a Terra Imploder?','sure','yes');
		} else {
			if($user[login_id] > 1){
				dbn(__FILE__,__LINE__,"update ${db_name}_users set on_planet = 0, terra_imploder = terra_imploder - 1 where login_id = $user[login_id]");
				$terra_txt = " with a Terra Imploder";
				$terra_txt2 = "<br /><br />You have used 1 Terra Implosion Device.";
			} else {
				dbn(__FILE__,__LINE__,"update ${db_name}_users set on_planet = 0 where login_id = $user[login_id]");
			}
			get_star();
			if($star[art_wh] > 0 && $planet[wormhole] > 0) {
				dbn(__FILE__,__LINE__,"update ${db_name}_stars set wormhole = 0, art_wh = 0, wh_login = 0, wh_clanid = 0 where star_id = '$star[wormhole]'");
				dbn(__FILE__,__LINE__,"update ${db_name}_stars set wormhole = 0, art_wh = 0, wh_login = 0, wh_clanid = 0 where star_id = '$star[star_id]'");###note###
			}
			$new_barren = mt_rand(1,7);
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set owner_id = 0, planet_img = $new_barren, owner_name = 'Barren World', fighters = 0, fighter_set = 0, cash = 0, tax_rate = 0, clan_id = 0, alloc_fight = 0, pass = 0, shield_gen = 0, shield_charge = 0, launch_pad = 0, missile = 0, tech = 0, research_fac = 0, wormhole_gen = 0, wormhole = 0, barren = 1 where planet_id = $user[on_planet]");

			post_news(addslashes("<b class=b1>$user[login_name]</b> irradiated the planet <b class=b1>$planet[planet_name]</b>".$terra_txt.". This is now a <font color=orange>Barren World</font>"));
			$rs = '<p><a href=location.php>Back to the Star System</a><br />';
			print_page("Planet Irradiated","Planet <b class=b1>$planet[planet_name]</b> is now barren. Life on this world will be a living hell for any colonists who survived the event.".$terra_txt2);
		}
	}else{
		print_page("Unable to comply.","You cannot irradiate this planet, as you do not own it, nor does a clan-mate.");
	}
}


if(isset($claim)) {
	if($user[clan_id]) {
		$clan_id = $user[clan_id];
	} else {
		$clan_id = -1;
	}

	db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = $user[on_planet]");
	$planet = dbr();

	db(__FILE__,__LINE__,attack_planet_check($db_name,$user));
	$attackable_planet = dbr();

	if($planet[owner_id] == $user[login_id]) {
		$output_str .= "<br />You can not claim a planet from yourself.<p>";
	} elseif($planet[fighters] != 0 && $planet[unigen] == 1){
		$output_str .= "This Planet has fighters on.  You cannot claim a planet with fighters on.";
	} elseif(($user[clan_id] == $planet[clan_id] || $planet[fighters] != 0) && $user[signed_up] > (time() - ($min_before_transfer * 86400))) {
		$output_str .= "<br />You can not transfer a planet before the min_before_transfer time is up.<p>";
	} elseif(!empty($attackable_planet)) {
		$rs = '<p><a href=location.php>Back to the Star System</a><br />';
		report_illegal_planet_claim($planet,$attackable_planet);
		print_page('Claim Planet',illegal_planet_claim_warning($planet));
	} elseif($planet[research_fac] > 0 && $flag_bmrkt == 1 && $sure != 'yes') {
		get_var('Claim Planet','planet.php','<b class=b1>Warning.</b><br />Claiming this planet will result in the destruction of the research centre by its present occupants.<br /><br />Are you sure you want to claim this planet and have the research centre destroyed in the process?','sure','yes');
	} else {
		send_message($planet[owner_id],"<b class=b1>$user[login_name]</b> claimed the planet <b class=b1>$planet[planet_name]</b> from you.");
		post_news("<b class=b1>$user[login_name]</b> has claimed the planet <b class=b1>$planet[planet_name]</b>.");
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set clan_id = '$clan_id', owner_id = '$user[login_id]', owner_name = '$user[login_name]', fighter_set = 0, research_fac = 0, wormhole_gen = 0, wormhole = 0, unigen = 0 where planet_id = '$planet[planet_id]'");
		get_star();
		if($star[art_wh] > 0 && $planet[wormhole] > 0) {
			dbn(__FILE__,__LINE__,"update ${db_name}_stars set wormhole = 0, art_wh = 0, wh_login = 0, wh_clanid = 0 where star_id = '$star[wormhole]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_stars set wormhole = 0, art_wh = 0, wh_login = 0, wh_clanid = 0 where star_id = '$star[star_id]'");
		}
		if($low_access) {
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set pass = 0 where planet_id = '$user[on_planet]'");
			$planet_pass = 0;
		}
	}
}












// replacement for loading materials
// see ship_loading.inc.php for functions used here

if(isset($_GET['metal']) && $_GET['metal'] == 0)
{
	$error_str = Load_Resource_Form("load", "metal", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Loading",$error_str);
}
if(isset($_GET['fuel']) && $_GET['fuel'] == 0)
{
	$error_str = Load_Resource_Form("load", "fuel", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Loading",$error_str);
}
if(isset($_GET['elect']) && $_GET['elect'] == 0)
{
	$error_str = Load_Resource_Form("load", "elect", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Loading",$error_str);
}
if(isset($_GET['organ']) && $_GET['organ'] == 0)
{
	$error_str = Load_Resource_Form("load", "organ", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Loading",$error_str);
}
if(isset($_GET['colon']) && $_GET['colon'] == 0)
{
	$error_str = Load_Resource_Form("load", "colon", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Loading",$error_str);
}
if(isset($_GET['fighters']) && $_GET['fighters'] == 0)
{
	$error_str = Load_Resource_Form("load", "fighters", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Loading",$error_str);
}
if(isset($_GET['darkmatter']) && $_GET['darkmatter'] == 0)
{
	$error_str = Load_Resource_Form("load", "darkmatter", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Loading",$error_str);
}
if(isset($_POST['load_resource']) && $_POST['load_resource'] == 1)
{
	$result = Load_Resource($_POST['fill_option'], $_POST['cap_option'], $_POST['resource'], '1', "planet.php?planet_id=$planet[planet_id]", $_POST['user_amount'], $planet['planet_id']);
	print_page("Planet - Loading",$result['text']);
}

if(isset($_GET['metal']) && $_GET['metal'] == 1)
{
	$error_str .= Unload_Resource_Form("unload", "metal", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Unloading",$error_str);
}
if(isset($_GET['fuel']) && $_GET['fuel'] == 1)
{
	$error_str .= Unload_Resource_Form("unload", "fuel", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Unloading",$error_str);
}
if(isset($_GET['elect']) && $_GET['elect'] == 1)
{
	$error_str .= Unload_Resource_Form("unload", "elect", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Unloading",$error_str);
}
if(isset($_GET['organ']) && $_GET['organ'] == 1)
{
	$error_str .= Unload_Resource_Form("unload", "organ", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Unloading",$error_str);
}
if(isset($_GET['colon']) && $_GET['colon'] == 1)
{
	$error_str .= Unload_Resource_Form("unload", "colon", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Unloading",$error_str);
}
if(isset($_GET['fighters']) && $_GET['fighters'] == 1)
{
	$error_str .= Unload_Resource_Form("unload", "fighters", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Unloading",$error_str);
}
if(isset($_GET['darkmatter']) && $_GET['darkmatter'] == 1)
{
	$error_str .= Unload_Resource_Form("unload", "darkmatter", "planet.php?planet_id=$planet[planet_id]");
	print_page("Planet - Unloading",$error_str);
}
if(isset($_POST['unload_resource']) && $_POST['unload_resource'] == 1)
{
	$result = Unload_Resource($_POST['fill_option'], $_POST['cap_option'], $_POST['resource'], '1', "planet.php?planet_id=$planet[planet_id]", $_POST['user_amount'], $_POST['user_credits'], $planet['planet_id']);
	print_page("Planet - Unloading",$result['text']);
}

















if($autoshift){
	#get ship information/see if there is enough capacity etc.
	db(__FILE__,__LINE__,"select count(ship_id), sum(cargo_bays-metal-fuel-elect-organ-colon-darkmatter) from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && (cargo_bays-metal-fuel-elect-organ-colon-darkmatter) > 0");
	$ship_count = dbr();
	$colonist_cap = $ship_count[1];			#total cargo capacity of fleet in system
	$colonist = $ship_count[1];				#total cargo capacity of fleet in system
	$ship_count = $ship_count[0];			#number of ships in system that have cargo capacity
	db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && config REGEXP 'ws'");					#ensure there is a transverser with the ws upgrade
	$lead = dbr();

	#figure out what the user is dealing in.
	if($autoshift == 1){
		$tech_mat = "colon";
		$text_mat = "Colonists";
	} elseif($autoshift == 2){
		$tech_mat = "metal";
		$text_mat = "Metal";
	} elseif($autoshift == 3){
		$tech_mat = "fuel";
		$text_mat = "Fuel";
	} elseif($autoshift == 4){
		$tech_mat = "elect";
		$text_mat = "Electronics";
	} elseif($autoshift == 5){
		$tech_mat = "organ";
		$text_mat = "Organics";
	} elseif($autoshift == 6){
		$tech_mat = "darkmatter";
		$text_mat = "Dark-Matter";
	}
db4(__FILE__,__LINE__,"select * from races where race_ID = $user[race] and era = $era");
$home = dbr4();
$homeworld = $home['planet_name'];

	if(!isset($ship_count)) {#ensure there is some cargo cap
		$output_str .= "You do not have any ships with free cargo capacity.<p>";
	} elseif($user['turns'] < 2) {#ensure there is some cargo cap
		$output_str.= "Your turn count is less than 2. Thats not going to get you anywhere and back!";
	} elseif(!isset($lead['ship_id'])) { #ensure there is a transverer with the ws upgrade
		$output_str .= "You do not have a Transverser in this system that has the <b>Wormhole Stabiliser</b> Upgrade. <br />This is required to allow for Autoshifting of anything.<p>";
	} elseif(!isset($dest_system)){ #get the user to select a system from where the colonists are to come from
		$new_page = "Please select a planet from which the $text_mat is going to come:";
		db2(__FILE__,__LINE__,"select planet_id,planet_name from ${db_name}_planets where owner_id = '$user[login_id]' && location != '$user[location]' && ((colon - (alloc_fight + alloc_organ + alloc_elect) > 0 && $autoshift = 1) || ($autoshift > 1 && $tech_mat > 0))"); #gets users planet other than ones in the present system.
		$other_sys = dbr2();

		if(!isset($other_sys['planet_id']) && $autoshift > 1){ #determine if there is a suitable target.
			$output_str .= "You have no planets with a supply of $text_mat.<p>";
		} else {
			$new_page .= "<form action=planet.php method=post name=autoshifting>";
			$new_page .= "<select name=dest_system>";

			if($autoshift==1){
				$new_page .= "<option value=-1>$homeworld</option>";
			}

			while ($other_sys) {
				$new_page .= "<option value=$other_sys[planet_id]>$other_sys[planet_name]</option>";
				$other_sys = dbr2();
			}
			$new_page .= "</select>";
			$new_page .= "<input type=hidden name=autoshift value='$autoshift'>";
			$new_page .= "<input type=hidden name=planet_id value=$planet_id>";
			$new_page .= "<p><input type=submit value='Submit'></form>";
			print_page("Autoshift",$new_page);
		}
	} else { #user has selected destination.


		if($dest_system == -1){ #user is getting the colonists from Sol. Thus needs to pay, and there is an infinite source.
			$turn = round(get_star_dist($user[location],$user[race])/2 +1)*2;	#do maths to work out turn cost to get there
			if($user[turns] < $turn) { #ensure user has enough turns to get there
				$output_str .= "You do not have enough turns.<br />It requires <b>$turn</b> turns just to get to <b class=b1>Sol</b> and back. Thats before ship loading.<p>";
			} else { #main autoshifting bit for taking colonists from Sol
				#determine if player can afford the costs, and if they can, then do the processing
				$c_c = $cost_colonist * $colonist;
				if($user[cash] < $c_c || $user[turns] < $turn + $ship_count){
					if($cost_colonist > 0){
						$colonist = floor($user[cash] / $cost_colonist);
					}
					if($colonist_cap > $colonist || $user[turns] < $turn + $ship_count){
						$free_turns = $user[turns] - $turn;
						$bays_used = 0;
						$count_quick = 0;

						db2(__FILE__,__LINE__,"select sum(cargo_bays-metal-fuel-elect-organ-colon-darkmatter),ship_id from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && (cargo_bays-metal-fuel-elect-organ-colon-darkmatter) > 0 group by ship_id order by (cargo_bays-metal-fuel-elect-organ-colon-darkmatter) desc");
						$quick_ship = dbr2();
						while($quick_ship && $bays_used < $colonist && $free_turns > $count_quick){
							$bays_used += $quick_ship[0];
							$count_quick++;
							$quick_ship = dbr2();
						}
						$ship_count = $count_quick;
						$colonist_cap = $bays_used;
						if($colonist > $colonist_cap){
							$colonist = $colonist_cap;
						}
					}
				}
				$turn += $ship_count;
				$c_c = $colonist * $cost_colonist;
				if($sure != 'yes') { #ensure the user wants to carry out the autoshift.
					get_var('Autoshift','planet.php',"Are you sure you want to autoshift using the following stats:
					<br /><b>$ship_count</b> ship(s).
					<br /><b>$colonist</b> Total Colonist Capacity.
					<p>From <b class=b1>$home[planet_name]</b>(<b>#$user[race]</b>) to the planet <b class=b1>$planet[planet_name]</b>(#<b>$planet[location]</b>).
					<p>At a total cost of <b>$turn</b> turns and <b>$c_c</b> Credits?",'sure','');
				} else { #update the game cos the user does want to do the autoshifting.
					dbn(__FILE__,__LINE__,"update ${db_name}_planets set colon = colon + '$colonist' where planet_id = '$planet_id'");
					charge_turns($turn);
					take_cash($c_c);
					$output_str .= "Transportation of <b>$colonists</b> Colonists Complete.<p>";
				}
			}

		} else { #user is getting the materials from a system other than Sol. Thus different maths and stuff needs to be done as there is a finite number of materials, but no cash cost.

			db(__FILE__,__LINE__,"select location,owner_id,planet_name,$tech_mat,planet_id,alloc_fight,alloc_elect,alloc_organ from ${db_name}_planets where planet_id = '$dest_system'");
			$from_sys=dbr();
			$turn = round(get_star_dist($user['location'],$from_sys['location'])/1.8 +1)*2; #work out turn cost
			#echo $turns_can_use = floor(($user['turns']- $turn) * 1.35);
			if($user['turns'] < $turn) { #ensure user has enough turns to get there
				$output_str .= "You do not have enough turns.<br />It requires <b>$turn</b> turns to get to <b class=b1>$from_sys[planet_name](#<b>$from_sys[location]</b>)</b> and back.<p>";
			} elseif(!isset($from_sys)){
				$output_str .= "That planet is not a viable target.<p>";
			} elseif($from_sys['owner_id'] != $user['login_id']){
				$output_str .= "An aspiring pirate I see. <br />That planet is not yours to take from.<p>";
			} elseif(($from_sys['colon'] - ($from_sys['alloc_elect'] + $from_sys['alloc_fight'] + 	$from_sys['alloc_organ'])) < 1 && $autoshift == 1){
				$output_str .= "The planet you are getting the colonists from does not have any colonists available to transport.<br />It is only possible to transport Idle colonists.<p>";
			} elseif($autoshift > 1 && $from_sys[$tech_mat] <= 0){
				$output_str .= "That planet does not have any $text_mat available.<p>";
			} else { #main autoshifting bit for taking materials from target planet
				if($autoshift == 1){
					$available = $from_sys['colon'] - ($from_sys['alloc_elect'] + $from_sys['alloc_fight'] + $from_sys['alloc_organ']);
				} else {
					$available = $from_sys[$tech_mat];
				}

				if($available >= $colonist_cap){ #there are more goods on target planet than there is cargo capacity.
					$col_to_take = $colonist_cap;
				} else { #got more goods capacity than goods on planet.
					$col_to_take = $available;
				}

				if($sure != 'yes') { #ensure the user wants to carry out the autoshift.
					get_var('Autoshift','planet.php',"Are you sure you want to autoshift using the folling stats:
					<p>Shift <b>$col_to_take</b> $text_mat from $from_sys[planet_name](#<b>$from_sys[location]</b>) to the planet <b class=b1>$planet[planet_name]</b>(#<b>$planet[location]</b>) using <b>$turn</b> turns?",'sure','');
				} else { #update the game as the user does want to do the autoshifting.
					dbn(__FILE__,__LINE__,"update ${db_name}_planets set $tech_mat = $tech_mat + '$col_to_take' where planet_id = '$planet_id'");				#give goods to recieving planet
					dbn(__FILE__,__LINE__,"update ${db_name}_planets set $tech_mat = $tech_mat - '$col_to_take' where planet_id = '$from_sys[planet_id]'");	#take goods from sending planet.
					charge_turns($turn);
					$output_str .= "Transportation of <b>$col_to_take</b> $text_mat Complete.<p>";
				}
			}
		}
	}

#take or leave a physical resource using 1 ship.
} elseif(isset($mineral_alloc)){

	if(conditions($user,$planet)) { #ensure user is allowed to play with this sort of stuff.
		$output_str .= "Items may not be left on this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
	} else {

		#ensure all are rounded & valid
		$process[fighters] = round($set_fighters);
		$process[colon] = round($set_colon);
		$process[metal] = round($set_metal);
		$process[fuel] = round($set_fuel);
		$process[elect] = round($set_elect);
		$process[organ] = round($set_organ);
		$process[darkmatter] = round($set_darkmatter);

		foreach($process as $key => $set_to){
			settype($process[$key], "integer");

			if($set_to >= 0 && $set_to != $planet[$key] && (($user_ship['max_fighters'] > 0 && $key == "fighters") || ($user_ship[cargo_bays] > 0 && $key != "fighters"))) { #ensure valid for continuation to avoid wasting processing time/power

				$old_ent = $planet[$key]; #to ensure a user only gets charged for things that are actually changed.

				if($user[turns] < 1){
					$output_str .= "You need 1 turn per resource you are planning on transfering (metal, fuel, elect, colon, organ, darkmatter, fighters).";
				} else {
					if($set_to > $user_ship[$key] + $planet[$key]){ #ensure user doesn't go over the limit.
						$set_to = $user_ship[$key] + $planet[$key];
					}

					if($set_to > $planet[$key]){ #user putting onto planet.
						$take_from_user = $set_to - $planet[$key];
						$user_ship[$key] -= $take_from_user;
						dbn(__FILE__,__LINE__,"update ${db_name}_ships set $key = $key - '$take_from_user' where ship_id = $user_ship[ship_id]");
						$planet[$key] = $set_to;
						dbn(__FILE__,__LINE__,"update ${db_name}_planets set $key = '$set_to' where planet_id = $user[on_planet]");
					} else { #taking from planet.

						$give_to_user = $planet[$key] - $set_to; #ensure no ship limits are broken.
						if($give_to_user > $user_ship['max_fighters'] && $key == "fighters"){
							$give_to_user = $user_ship['max_fighters'] - $user_ship['fighters'];
						} elseif($give_to_user > empty_bays() && $key != "fighters"){
							$give_to_user = empty_bays();
						}
						$set_to = $planet[$key] - $give_to_user;

						$user_ship[$key] += $give_to_user;
						dbn(__FILE__,__LINE__,"update ${db_name}_ships set $key = $key + '$give_to_user' where ship_id = $user_ship[ship_id]");
						$planet[$key] = $set_to;
						dbn(__FILE__,__LINE__,"update ${db_name}_planets set $key = '$set_to' where planet_id = $user[on_planet]");
					}
					empty_bays(); #ensure things are kept up to date.

					if($old_ent != $set_to){ #charge the user 1 turn per resource transfered.
						charge_turns(1);
						if($type == 1 && $set_to < $old_ent){#if colonists have been messed with.
							$planet['alloc_fight'] = 0;
							$planet['alloc_elect'] = 0;
							$planet['alloc_organ'] = 0;
							dbn(__FILE__,__LINE__,"update ${db_name}_planets set alloc_fight = 0, alloc_elect=0, alloc_organ=0 where planet_id = '$user[on_planet]'");
							$out .= "<p>As you took away some colonists, any remaining colonists assigned to production duties have become idle. Yes, this is a definite cop out by the developers but it will be fixed at some stage in the near future.";
						}
					}
				}
			}
		}
	}

#=============
#this code allows users to take and leave charges at the shield generator
#=============

/*if(isset($shield)) {
	get_planet();
	if($shield == 0) { // Take
	if(conditions($user,$planet)) {
		$output_str .= "Shields may not be taken from this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
	} elseif($amount < 1) {
			$def = $planet[shield_charge];
			if($def > ($user_ship[max_shields] - $user_ship[shields])) {
				$def = $user_ship[max_shields] - $user_ship[shields];
			}
			get_var('Charge Shields','planet.php','How many shield charges do you want to take?','amount',$def);
		} else {
			if($amount > $planet[shield_charge]) { // has on planet
				$output_str .= "There are not <b>$amount</b> Shield Charges on this planet.<p>";
			} elseif($amount > ($user_ship[max_shields] - $user_ship[shields])) { // can have that many
				$output_str .= "Your ship can not hold that many Shields.<p>";
			} else {
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set shield_charge = shield_charge - $amount where planet_id = $user[on_planet]");
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = shields + $amount where ship_id = $user[ship_id]");
				$user_ship[shields] += $amount;
			}
		}
	} elseif($shield == 1) { // Leave
	if(conditions($user,$planet)) {
		$output_str .= "Shields may not be left on this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
		} elseif($amount < 1) {
			$def = $user_ship[shields];
			if($def > ($planet[shield_gen] * 1000) - $planet[shield_charge]) {
				$def = ($planet[shield_gen] * 1000) - $planet[shield_charge];
			}
			get_var('Leave shields','planet.php','How many shields do you want to leave?','amount',$def);
		} else {
			if($amount > $user_ship[shields]) { // has on ship
				$output_str .= "There is not <b>$amount</b> shields on your ship.<p>";
		} elseif(($amount + $planet[shield_charge]) > ($planet[shield_gen] * 1000)) { // no more space on planet
		} else {
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set shield_charge = shield_charge + $amount where planet_id = $user[on_planet]");
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = shields - $amount where ship_id = $user[ship_id]");
				$user_ship[shields] -= $amount;
			}
		}
	}*/

#dump the fleet
} elseif(isset($do_all) && isset($type)){
	#first, determine what is to be dealt with.
	if($type == 0){
		$tech_mat = "fighters";
		$text_mat = "Fighters";
	} elseif($type == 1){
		$tech_mat = "colon";
		$text_mat = "Colonists";
	} elseif($type == 2){
		$tech_mat = "metal";
		$text_mat = "Metal";
	} elseif($type == 3){
		$tech_mat = "fuel";
		$text_mat = "Fuel";
	} elseif($type == 4){
		$tech_mat = "elect";
		$text_mat = "Electronics";
	} elseif($type == 5){
		$tech_mat = "organ";
		$text_mat = "Organics";
	} elseif($type == 6){
		$tech_mat = "darkmatter";
		$text_mat = "Dark-Matter";
	}

	if($user['turns'] < 1){
		$output_str .= "This action will expend at least 1 turn.";
	} elseif($do_all == 2){ #leave goods
		#get ship info of ships that are valid.
		db(__FILE__,__LINE__,"select sum($tech_mat) as goods, count(ship_id) as ship_count from ${db_name}_ships where login_id = '$user[login_id]' && location = '$planet_loc' && $tech_mat > 0");
		$results = dbr();

		if(count($results['ship_count'] <= 1))
		{
			print_page("Error","You should not use the Load/Empty Fleet functions for a single vessel. Please return to the planet menu and use the Load/Empty Ship function instead.");
		}

		$turn_cost = ceil($results['ship_count'] * 0.3);//originally .75, reduced as it wasted turns too much

		if(!isset($results['goods'])) { #ships empty?
			$output_str .=  "You have no <b class=b1>$text_mat</b> in any ships in this system.";
		} elseif($user[turns] < $turn_cost) { #enough turns?
			$output_str .= "It will cost <b>$turn_cost</b> turns to perform this action.<br />At present you do not posses that many turns.";
			unset($results);
		} elseif(conditions($user,$planet)) { #check to see if been in game for long enough
			$output_str .= "$text_mat may not be left on this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
		} elseif($sure != "yes") { #confirmation
			get_var("Leave all $text_mat",'planet.php',"Are you sure you want to leave all the <b class=b1>$text_mat</b>(<b>$results[goods]</b>) from the <b>$results[ship_count]</b> ships with it on in this system onto the planet below, at a cost of <b>$turn_cost</b> turns?",'sure','yes');
		} else {
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set $tech_mat = $tech_mat + '$results[goods]' where planet_id = '$user[on_planet]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set $tech_mat = 0 where login_id = '$user[login_id]' && location = '$planet_loc' && $tech_mat > 0");
			charge_turns($turn_cost);
			echo "oisfdgoaihgoiahfg[oisrahgpdsifhgapwogprawihgawroihg";
			$user_ship[$tech_mat] = 0;
			empty_bays();
			$output_str .= "<b>$results[ship_count]</b> ships unloaded at a cost of <b>$turn_cost</b> turns.";
		}
	} elseif($do_all == 1){ #taking the goods

		$taken = 0; //goods taken from planet so far.
		$ship_counter = 0;

		if(conditions($user,$planet)) {#been in game long enough?
			$output_str .= "Fighters may not be left on this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
		} elseif($planet[$tech_mat] < 1) { #can't take stuff if there isn't any to take
			$output_str.= "This planet has no <b class=b1>$text_mat</b> on it.";
		} elseif($type == 0){ #fighters
			db2(__FILE__,__LINE__,"select ship_id,(max_fighters - fighters) as free,ship_name from ${db_name}_ships where login_id = '$user[login_id]' && location = '$planet_loc' && (max_fighters - fighters) > 0 order by free desc");
		} elseif($type != 0) {
			db2(__FILE__,__LINE__,"select ship_id, (cargo_bays - metal - fuel - elect - organ - colon - darkmatter) as free, ship_name from ${db_name}_ships where login_id = '$user[login_id]' && location = '$planet_loc' && (cargo_bays - metal - fuel - elect - organ - colon - darkmatter) > 0 order by free desc");
		}
		$ships = dbr2();

		//added feb 2004 - prevent user using this function for one ship only - these functions will all be replaced in future
		if(count($ships <= 1))
		{
			print_page("Error","You should not use the Load/Empty Fleet functions for a single vessel. Please return to the planet menu and use the Load/Empty Ship function instead.");
		}

		if(!isset($ships[ship_id])){ #check to see if there are any ships
			$output_str.= "There are no ships that have any space free for <b class=b1>$text_mat</b>.";
		} else {

			while($ships) {
				//planet can load ship w/ spare fighters maybe.
				if($ships['free'] < ($planet[$tech_mat] - $taken)) {
					$ship_counter++;
					if($sure == "yes"){ #only run during the real thing.
						dbn(__FILE__,__LINE__,"update ${db_name}_ships set $tech_mat = $tech_mat + $ships[free] where ship_id = '$ships[ship_id]'");
						$out .= "<br /><b class=b1>$ships[ship_name]</b>s bays were supplemented by <b>$ships[free]</b> <b class=b1>$text_mat</b> to maximum capacity.";
						if($ships['ship_id'] == $user_ship['ship_id'] && $type == 0){ #update user ship
							$user_ship['fighters'] = $user_ship['max_fighters'];
						} elseif($ships['ship_id'] == $user_ship['ship_id'] && $type > 0){ #update user ship
							$user_ship[$tech_mat] += $ships['free'];
							$user_ship['empty_bays'] -= $ships['free'];
						}
					}
					$taken += $ships['free'];

					#ensure user has enough turns, or stop the loop where the user is.
					if($user['turns'] == ceil($ship_counter * 0.75)){
						$turns_txt = "You do not have enough turns to load your whole fleet.<br />But you do have enough to perform this limited action:<p>";
						$out .=  "<br />You did not have enough turns to continue with the operations any further.";
						unset($ships);
						break;
					}

				//planet will run out of fighters.
				} elseif($ships['free'] >= ($planet[$tech_mat] - $taken)) {
					$ship_counter++;
					$t868 = $ships[$tech_mat] + ($planet[$tech_mat] - $taken);
					if($sure == "yes"){ #only run during the real thing.
						dbn(__FILE__,__LINE__,"update ${db_name}_ships set $tech_mat = '$t868' where ship_id = '$ships[ship_id]'");

						$out .= "<br /><b class=b1>$ships[ship_name]</b>s bays were supplemented by <b>$t868</b> <b class=b1>$text_mat</b>";

						if($ships['ship_id'] == $user_ship['ship_id'] && $type == 0){ #update user ship
							$user_ship['fighters'] = $t868;
						}elseif($ships['ship_id'] == $user_ship['ship_id'] && $type > 0){ #update user ship
							$user_ship[$tech_mat] = $t868;
							empty_bays();
						}
					}
					$taken += $t868 - $ships[$tech_mat];
					unset($ships);
					break;
				}
				$ships = dbr2();
			} #end loop of ships

			$turn_cost = ceil($ship_counter * 0.75);

			if($user[turns] < $turn_cost) {
				$output_str.= "You need <b>5</b> turns to load all ships in a system.";
			} elseif($sure != "yes") {
				get_var('Load all ships','planet.php',$turns_txt."Are you sure you want to load <b>$ship_counter</b> ships in this system with <b>$taken</b> <b class=b1>$text_mat</b> at a cost of about <b>$turn_cost</b> turns?",'sure','yes');
			} else {
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set $tech_mat = $tech_mat - $taken where planet_id = '$user[on_planet]'");
				charge_turns($turn_cost);
				if($type == 1){ #colonists
					$planet['alloc_fight'] = 0;
					$planet['alloc_elect'] = 0;
					$planet['alloc_organ'] = 0;
					dbn(__FILE__,__LINE__,"update ${db_name}_planets set alloc_fight = 0, alloc_elect=0, alloc_organ=0 where planet_id = '$user[on_planet]'");
					$out .= "<p>As you took away some colonists, any remaining colonists assigned to production duties have become idle.";
				}

				print_page("$text_mat Loaded","<b>$ship_counter</b> ships had their bays augmented by a total of <b>$taken</b> <b class=b1>$text_mat</b> from the planet <b class=b1>$planet[planet_name]</b>:<br />".$out."<p>The total turn cost was <b>$turn_cost</b>.");
			}
		}
	}


} elseif($all_shield) { // Charge all shields on all ships in system.
	$taken = 0; //Shields taken from planet so far.
	$ship_counter = 0;
	if($sure != "yes") {
		get_var('Charge all ships','planet.php',"Are you sure you want to charge all the Ships in this system with <b class=b1>shields</b>?",'sure','yes');
	} elseif(conditions($user,$planet)) {
		$output_str .= "Shields may not be taken from this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
	} elseif($user[turns] < 3) {
		print_page("Error","You need <b>3</b> turns to charge all ships in a system.");
	} elseif($planet[shield_charge] < 1) {
		print_page("Error","This planet has no shield charges on it.");
	} else {
		db2(__FILE__,__LINE__,"select ship_id,shields,max_shields,ship_name from ${db_name}_ships where login_id = '$user[login_id]' && location = '$planet_loc' && max_shields > 0 && shields < max_shields");
		while($ships = dbr2()) {
			//planet can charge ship w/ spare shields maybe.
			$free = $ships[max_shields] - $ships[shields];
			if($free <= ($planet[shield_charge] - $taken)) {
				$ship_counter++;
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = max_shields where ship_id = '$ships[ship_id]'");
				$out .= "<br /><b class=b1>$ships[ship_name]</b> had its shields increased by <b>$free</b> to full.";
				if($ships[ship_id] == $user_ship[ship_id]){
					$user_ship[shields] = $user_ship[max_shields];
				}
				$taken += $free;
			//planet will run out of shields.
			} elseif($free >= ($planet[shield_charge] - $taken)) {
				$ship_counter++;
				$t868 = $ships[shields] + ($planet[shield_charge] - $taken);
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = '$t868' where ship_id = '$ships[ship_id]'");
				if($ships[ship_id] == $user_ship[ship_id]){
					$user_ship[shields] = $t868;
				}
				$taken += $t868 - $ships[shields];
				$out .= "<br /><b class=b1>$ships[ship_name]</b>s shields were charged to <b>$t868</b> shields.";
				break;
			}
			if(($planet[shield_charge] - $taken) < 1){
				break;
			}
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set shield_charge = shield_charge - $taken where planet_id = '$user[on_planet]'");
		if($ship_counter > 0){
				charge_turns(3);
			print_page("Shields Charged","<b>$ship_counter</b> ships had their shields charged by the planet <b class=b1>$planet[planet_name]</b>:<br />".$out);
		} else {
			print_page("No Ships","No ships where charged as all ships in this system have full shields.");
		}
	}
} elseif(isset($assinging)) {
	#ensure all are rounded & valid
	$num_pop_set_1 = round($num_pop_set_1);
	settype($num_pop_set_1, "integer");

	$num_pop_set_2 = round($num_pop_set_2);
	settype($num_pop_set_2, "integer");

	$num_pop_set_3 = round($num_pop_set_3);
	settype($num_pop_set_3, "integer");

	$set_tax_rate = round($set_tax_rate);
	settype($set_tax_rate, "integer");


	if($num_pop_set_1 >= 0 && $num_pop_set_1 != $planet['alloc_fight']) { // Fighters
		if($num_pop_set_1 > idle_colonists() + $planet['alloc_fight']){ #ensure user doesn't go over the limit.
			$num_pop_set_1 = idle_colonists() + $planet['alloc_fight'];
		}
		$planet['alloc_fight'] = $num_pop_set_1;
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set alloc_fight = $num_pop_set_1 where planet_id = $user[on_planet]");

	}

	if($num_pop_set_2 >= 0 && $num_pop_set_2 != $planet['alloc_elect']) { // Electronics
		if($num_pop_set_2 > idle_colonists() + $planet['alloc_elect']){ #ensure user doesn't go over the limit.
			$num_pop_set_2 = idle_colonists() + $planet['alloc_elect'];
		}
		$planet['alloc_elect'] = $num_pop_set_2;
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set alloc_elect = $num_pop_set_2 where planet_id = $user[on_planet]");

	}

	if($num_pop_set_3 >= 0 && $num_pop_set_3 != $planet['alloc_organ']) { // Organics
		if($num_pop_set_3 > idle_colonists() + $planet['alloc_organ']){ #ensure user doesn't go over the limit.
			$num_pop_set_3 = idle_colonists() + $planet['alloc_organ'];
		}
		$planet['alloc_organ'] = $num_pop_set_2;
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set alloc_organ = $num_pop_set_3 where planet_id = $user[on_planet]");

	}

	if($set_tax_rate >= 0 && $set_tax_rate <= 20) { // tax rate within boundaries
		$planet['tax_rate'] = $num_pop_set_2;
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set tax_rate = $set_tax_rate where planet_id = $user[on_planet]");
	}

} elseif(isset($monetary)){
	#ensure all are rounded & valid
	$set_cash = round($set_cash);
	settype($set_cash, "integer");

	if($set_cash >= 0 && $set_cash != $planet['cash']){ #cash dispensary
		if($set_cash > $user['cash'] + $planet['cash']){ #ensure user doesn't go over the limit.
			$set_cash = $user['cash'] + $planet['cash'];
		}

		if($set_cash > $planet['cash']){ #user putting money onto planet.
			$take_from_user = $set_cash - $planet['cash'];
			take_cash($take_from_user);
			$planet['cash'] = $set_cash;
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set cash = $set_cash where planet_id = $user[on_planet]");
		} else { #taking money from planet.
			$give_to_user = $planet['cash'] - $set_cash;
			give_cash($give_to_user);
			$planet['cash'] = $set_cash;
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set cash = $set_cash where planet_id = $user[on_planet]");
		}
	}

	if(isset($set_tech) && $flag_bmrkt != 0){ #tech units
		$set_tech = round($set_tech);
		settype($set_tech, "integer");

		if($set_tech >= 0 && $set_tech != $planet['tech']){
			if($set_tech > $user['tech'] + $planet['tech']){ #ensure user doesn't go over the limit.
				$set_tech = $user['tech'] + $planet['tech'];
			}

			if($set_tech > $planet['tech']){ #user putting money onto planet.
				$take_from_user = $set_tech - $planet['tech'];
				take_tech($take_from_user);
				$planet['tech'] = $set_tech;
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set tech = $set_tech where planet_id = $user[on_planet]");
			} else { #taking money from planet.
				$give_to_user = $planet['tech'] - $set_tech;
				give_tech($give_to_user);
				$planet['tech'] = $set_tech;
				dbn(__FILE__,__LINE__,"update ${db_name}_planets set tech = $set_tech where planet_id = $user[on_planet]");
			}
		}
	}

} elseif(isset($mil) && ($mil == 1 || $mil == 2)){ #offensive/defensive planet.
	get_planet();
	$flag = 1;

	db(__FILE__,__LINE__,"select login_id from ${db_name}_ships where location = '$planet[location]' && (clan_id != '$planet[clan_id]' && clan_id > 0) && login_id != '$user[login_id]'");
	$ships = dbr();
	db(__FILE__,__LINE__,"select * from ${db_name}_planets where fighters > 0 && owner_id != '$user[login_id]' && (clan_id != '$user[clan_id]' && clan_id != 0) && location = '$user[location]' order by fighter_set desc, fighters desc limit 1");
	$planet_check = dbr();

	if($ships || $planet_check){
		$flag = 0;
	}

	if($flag == 1) {
		dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighter_set = $mil where planet_id = $user[on_planet]");
	} else {
		$output_str .= "This planet is unable to turn hostile if there are enemy ships or planets in the system.<p>";
	}

} elseif(isset($mil) && $mil == 0) {
	dbn(__FILE__,__LINE__,"update ${db_name}_planets set fighter_set = $mil where planet_id = $user[on_planet]");
}



if($rename){
	if($user[login_id] != $planet[owner_id]){
		$text .= "This planet is not yours to re-name.";
	} elseif($name_to) {
		$name_to = correct_name($name_to);
		if(!$name_to || strlen($name_to) < 3) {
			$rs = "<p><a href=javascript:history.back()>Try Again</a>";
			print_page("Invalid Name","That is not a valid name. Must have more than three characters.");
		}
		#$stuff = addslashes($name_to);
		#echo eregi_replace("'","",$name_to);
		db(__FILE__,__LINE__,"select planet_name from ${db_name}_planets");
		$name_list = dbr();
		#prevent usage of identical names
		while($name_list) {
			if($name_to == $name_list[planet_name]) {
				$rs = "<p><a href=javascript:history.back()>Try Again</a>";
				print_page("Invalid Name","This name is already in use. Planet names must be unique, i.e. two planets with the same name cannot co-exist in Q-S SE.");
			}
			$name_list = dbr();
		}
		$text .= "Planet re-named from <b class=b1>$planet[planet_name]</b> to <b class=b1>$name_to</b>.";
			dbn(__FILE__,__LINE__,"update ${db_name}_planets set planet_name = '$name_to' where planet_id = '$planet[planet_id]'");
		post_news("<b class=b1>$user[login_name]</b> renamed the planet <b class=b1>$planet[planet_name]</b> to <b class=b1>$name_to</b>.");
	} else {
		$text .= "Enter a new name for the planet (30 Characters Max):";
		$text .= "<form method=post action=planet.php>";
		$text .= "<input type=text name=name_to size=30 value=\"$planet[planet_name]\">";
		$text .= "<input type=hidden name=rename value=1>";
		$text .= "<input type=hidden name=planet_id value=$planet[planet_id]>";
		$text .= "<p><input type=submit value=Rename></form><p>";
	}
	print_page("Rename Planet",$text);
}


get_planet();















#put any messages into a "message" box.
$messages .= $output_str;

#sets the largest span distance. Allows for quicker manipulation of the page.
$span_dist = 2;

$output_str = "<br /><table width=100%><tr><td width='225' valign=top><table class=all>";

#determine if user can re-name planet.
if($planet['owner_id'] == $user['login_id'] || $has_pass = 1 && $user['login_id'] == 1 || ($user['clan_id'] == $planet['clan_id'] && $user['clan_id'] > 0)) {
	$r_name_txt .= " - <a href=planet.php?planet_id=$planet_id&rename=1>Rename</a>";
}

// begin printing of page
$output_str .= quick_row("Planet Name","<b class=b1>$planet[planet_name]</b>$r_name_txt");


#print no name if the owner has none
if($planet['owner_id'] == 0){
	$output_str.= quick_row("Owned by","No-one");
} else {
	$output_str .= quick_row("Owned by","Owned by: <b class=b1>".print_name(array('login_id' =>$planet['owner_id']))."</b>");
}

#show the planetary picture
if($user_options['show_pics']){
	if($planet['barren'] == 1){
	$type = "b";
	} else {
	$type = "p";
	}
		$output_str .= "<tr><td colspan=$span_dist><div align=\"center\"><img src=images/planets/$type".$planet['planet_img'].".jpg border=0></div></td></tr>";
}

if(!empty($messages)){
	$output_str.= "<tr><td colspan=$span_dist><div align=\"center\">$messages</div></td></tr>";
}
#$output_str.= "</table><p>";


#players may only view planetary data when they have the planet as their own.
if($planet['owner_id'] == $user['login_id'] || $has_pass = 1 && $user['login_id'] == 1 || ($user['clan_id'] == $planet['clan_id'] && $user['clan_id'] > 0)) {

	#$output_str .= "<table>";

	if(!$planet_pass) {
		$output_str .= quick_row("No Password","<a href='planet.php?change_pass=1&planet_id=$planet_id'>Set one</a>");
	} elseif($low_access) {
		$output_str .= quick_row("Password Set","<a href='planet.php?change_pass=1&planet_id=$planet_id'>Change it.</a>");
	} else {
		$output_str .= quick_row("Password is '$planet_pass'","<a href='planet.php?change_pass=1&planet_id=$planet_id'>Change it.</a>");
	}

	#show the claim link for clan mates.
	if($planet[owner_id] != $user[login_id]){
		$output_str .= quick_row("<a href=planet.php?planet_id=$planet_id&claim=1>Claim $planet[planet_name]</a>","");
	}

	$output_str .= "</table><p><br /><h3>Monetary</h3><table><form name=monetary_set_form method=post action=planet.php><input type=hidden name=planet_id value='$planet_id'><input type=hidden name=monetary value='1'>";

	$output_str .= quick_row("Planet Cash","<input type=text name=set_cash value=$planet[cash] size=8>");

	if($flag_bmrkt != 0){
			$output_str .= quick_row("Planet Tech. Units","<input type=text name=set_tech value=$planet[tech] size=8>");
	}
	$output_str.= "<tr><td><input type=submit value='   Set   '></td><td><input type=reset value=' Reset '></td></tr></form>";


	$output_str .= "</table>";
	$output_str .= "</td><td valign=top><h3>Physical Goods</h3><table><form name=mineral_set_form method=post action=planet.php><input type=hidden name=planet_id value='$planet_id'><input type=hidden name=mineral_alloc value='1'>";

	#determine fighter status
	if($planet[fighter_set] == 0) {
		$fig_str .= "<a href=planet.php?planet_id=$planet_id&mil=1>Presently<br />Passive</a>";
	} elseif($planet[fighter_set] == 1){
		if($planet[super_hostile] == 1){
			$fig_str .= "<a href=planet.php?planet_id=$planet_id&mil=2>Presently<br />Hostile</a>";
		} else {
			$fig_str .= "<a href=planet.php?planet_id=$planet_id&mil=0>Presently<br />Hostile</a>";
		}
	} elseif($planet[fighter_set] == 2){
		$fig_str .= "<a href=planet.php?planet_id=$planet_id&mil=0>Presently<br />Super Hostile</a>";
	}

	//------------------------------------------
	// Old method of managing single ships added
	// Shorter Fleet links added to save space
	//------------------------------------------

	/*$output_str .= make_row(array("Fighters","<input type=text name=set_fighters value='$planet[fighters]' size=8>","<a href=planet.php?planet_id=$planet_id&do_all=1&type=0>Load</a><b>/</b><a href=planet.php?planet_id=$planet_id&do_all=2&type=0>Empty</a> <b>Fleet</b>","<a href=planet.php?planet_id=$planet_id&fighters=0>Load</a><b>/</b><a href=planet.php?planet_id=$planet_id&fighters=1>Empty</a> <b>Ship</b>",$fig_str));*/

$output_str .= make_row(array("Fighters","<input type=text name=set_fighters value='$planet[fighters]' size=8>","<a href=planet.php?planet_id=$planet_id&fighters=1>Empty</a> <b>Ship(s)</b>","<a href=planet.php?planet_id=$planet_id&fighters=0>Load</a> <b>Ship(s)</b>",$fig_str));

	/*$output_str .= make_row(array("Colonists","<input type=text name=set_colon value='$planet[colon]' size=8>","<a href=planet.php?planet_id=$planet_id&do_all=1&type=1>Load</a><b>/</b><a href=planet.php?planet_id=$planet_id&do_all=2&type=1>Empty</a> <b>Fleet</b>","<a href=planet.php?planet_id=$planet_id&colon=0>Load</a><b>/</b><a href=planet.php?planet_id=$planet_id&colon=1>Empty</a> <b>Ship</b>","<a href=planet.php?planet_id=$planet_id&autoshift=1>AutoShift</a>"));*/

$output_str .= make_row(array("Colonists","<input type=text name=set_colon value='$planet[colon]' size=8>","<a href=planet.php?planet_id=$planet_id&colon=1>Empty</a> <b>Ship(s)</b>","<a href=planet.php?planet_id=$planet_id&colon=0>Load</a> <b>Ship(s)</b>","<a href=planet.php?planet_id=$planet_id&autoshift=1>AutoShift</a>"));

$output_str .= make_row(array("Metal","<input type=text name=set_metal value='$planet[metal]' size=8>","<a href=planet.php?planet_id=$planet_id&metal=1>Empty</a> <b>Ship(s)</b>","<a href=planet.php?planet_id=$planet_id&metal=0>Load</a> <b>Ship(s)</b>","<a href=planet.php?planet_id=$planet_id&autoshift=2>AutoShift</a>"));

$output_str .= make_row(array("Fuel","<input type=text name=set_fuel value='$planet[fuel]' size=8>","<a href=planet.php?planet_id=$planet_id&fuel=1>Empty</a> <b>Ship(s)</b>","<a href=planet.php?planet_id=$planet_id&fuel=0>Load</a> <b>Ship(s)</b>","<a href=planet.php?planet_id=$planet_id&autoshift=3>AutoShift</a>"));

$output_str .= make_row(array("Electronics","<input type=text name=set_elect value='$planet[elect]' size=8>","<a href=planet.php?planet_id=$planet_id&elect=1>Empty</a> <b>Ship(s)</b>","<a href=planet.php?planet_id=$planet_id&elect=0>Load</a> <b>Ship(s)</b>","<a href=planet.php?planet_id=$planet_id&autoshift=4>AutoShift</a>"));

	/*$output_str .= make_row(array("Fuel","<input type=text name=set_fuel value='$planet[fuel]' size=8>","<a href=planet.php?planet_id=$planet_id&do_all=1&type=3>Load</a><b>/</b><a href=planet.php?planet_id=$planet_id&do_all=2&type=3>Empty</a> <b>Fleet</b>","<a href=planet.php?planet_id=$planet_id&fuel=0>Load</a><b>/</b><a href=planet.php?planet_id=$planet_id&fuel=1>Empty</a> <b>Ship</b>","<a href=planet.php?planet_id=$planet_id&autoshift=3>AutoShift</a>"));

	$output_str .= make_row(array("Electronics","<input type=text name=set_elect value='$planet[elect]' size=8>","<a href=planet.php?planet_id=$planet_id&do_all=1&type=4>Load</a><b>/</b><a href=planet.php?planet_id=$planet_id&do_all=2&type=4>Empty</a> <b>Fleet</b>","<a href=planet.php?planet_id=$planet_id&elect=0>Load</a><b>/</b><a href=planet.php?planet_id=$planet_id&elect=1>Empty</a> <b>Ship</b>","<a href=planet.php?planet_id=$planet_id&autoshift=4>AutoShift</a>"));*/

$output_str .= make_row(array("Organics","<input type=text name=set_organ value='$planet[organ]' size=8>","<a href=planet.php?planet_id=$planet_id&organ=1>Empty</a> <b>Ship(s)</b>","<a href=planet.php?planet_id=$planet_id&organ=0>Load</a> <b>Ship(s)</b>","<a href=planet.php?planet_id=$planet_id&autoshift=5>AutoShift</a>"));


if($flag_dmatter == 1)
{
$output_str .= make_row(array("Darkmatter","<input type=text name=set_darkmatter value='$planet[darkmatter]' size=8>","<a href=planet.php?planet_id=$planet_id&darkmatter=1>Empty</a> <b>Ship(s)</b>","<a href=planet.php?planet_id=$planet_id&darkmatter=0>Load</a> <b>Ship(s)</b>","<a href=planet.php?planet_id=$planet_id&autoshift=6>AutoShift</a>"));
}


	/*$output_str .= make_row(array("Organics","<input type=text name=set_organ value='$planet[organ]' size=8>","<a href=planet.php?planet_id=$planet_id&do_all=1&type=5>Load</a><b>/</b><a href=planet.php?planet_id=$planet_id&do_all=2&type=5>Empty</a> <b>Fleet</b>","<a href=planet.php?planet_id=$planet_id&organ=0>Load</a><b>/</b><a href=planet.php?planet_id=$planet_id&organ=1>Empty</a> <b>Ship</b>","<a href=planet.php?planet_id=$planet_id&autoshift=5>AutoShift</a>"));

//DARKMATTER ADDITION
	$output_str .= make_row(array("Dark-Matter","<input type=text name=set_darkmatter value='$planet[darkmatter]' size=8>","<a href=planet.php?planet_id=$planet_id&do_all=1&type=6>Load</a><b>/</b><a href=planet.php?planet_id=$planet_id&do_all=2&type=6>Empty</a> <b>Fleet</b>","<a href=planet.php?planet_id=$planet_id&darkmatter=0>Load</a><b>/</b><a href=planet.php?planet_id=$planet_id&darkmatter=1>Empty</a> <b>Ship</b>","<a href=planet.php?planet_id=$planet_id&autoshift=6>AutoShift</a>"));*/

	//$output_str .= " - <a href=planet.php?planet_id=$planet_id&fighters=2>Build</a>";
	$output_str .= "<tr><td><input type=submit value='   Set   '></td><td><input type=reset value=' Reset '></td></tr></form></table><p><br /><table>";



// ---------------------------------------------------------------------------------------
// Table of Production Changes Variables - added by Trade (tradelair.com) (mod:MTR Feb 03)
// ---------------------------------------------------------------------------------------
// please note that for Quantum Star, elect production takes priority over fighters
// for stats, material used up for elect to be deducted from usage for fighters

// calculate change in electronics (e_)
db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name='planet_elect'");
$planet_elect = dbr(); //select elect per production resource group 10
unset($Prod_units);
$Prod_units = floor($planet[alloc_elect]/500);//limit min units for cols
if($planet[fuel] < ($Prod_units * 10)) {
	$Prod_units = $planet[fuel] / 10;//limit min units for fuel
} elseif($planet[metal] < ($Prod_units * 10)) {
	$Prod_units = $planet[metal] / 10;//limit min units for metal
}
$e_f_used = $Prod_units * 10;//fuel used is base unit by 10
$e_m_used = $Prod_units * 10;//metal used is base unit by 10
#$e_col_used = $Prod_units * 500;//cols used is base unit by 500
$E_Prod_units_pre = round($Prod_units * $planet_elect[value]);//elect produced is base unit by admin var
// note the produced elect are used later in fighter production, so elect production change MAY be negative


// calculate change in fighters (f_)
db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name='planet_fighters'");
$planet_fighters = dbr(); //select fighters per production resource group
unset($Prod_units);
$f_avail = $planet[fuel] - $e_f_used;
$m_avail = $planet[metal] - $e_m_used;
$e_avail = $planet[elect] + $E_Prod_units_pre;
$Prod_units = floor($planet[alloc_fight]/100);//limit min units for cols
if($f_avail < ($Prod_units)) {
	$Prod_units = $f_avail;//limit min units for fuel
} elseif($m_avail < ($Prod_units)) {
	$Prod_units = $m_avail;//limit min units for metal
} elseif($e_avail < ($Prod_units)) {
	$Prod_units = $e_avail;//limit min units for elect
}
$f_f_used = $Prod_units;//fuel used is base unit by 1
$f_m_used = $Prod_units;//metal used is base unit by 1
$f_e_used = $Prod_units;//elect used is base unit by 1
#$f_col_used = $Prod_units * 100;//cols used is base unit by 100
$F_Prod_units = round($Prod_units * $planet_fighters[value]);//fighters produced is base unit by admin var
if($F_Prod_units > 0) {$f_sign = " Made Per DM";}else{$f_sign = "";}
if($F_Prod_units < 0){$F_Prod_units = 0;}
if($f_m_used < 0){$f_m_used = 0;} else {$f_m_used = $f_m_used - $f_m_used - $f_m_used;}
if($f_f_used < 0){$f_f_used = 0;} else {$f_f_used = $f_f_used - $f_f_used - $f_f_used;}
if($f_e_used < 0){$f_e_used = 0;} else {$f_e_used = $f_e_used - $f_e_used - $f_e_used;}
if($E_Prod_units_pre != $f_e_used){
$E_Prod_units_final = $E_Prod_units_pre + $f_e_used;
} else {
$E_Prod_units_final = $E_Prod_units_pre + $f_e_used;
}
if($E_Prod_units_final > 0) {$e_sign = " Made Per DM";}else{$e_sign = "";}//calc net change in elect
if($E_Prod_units_final < 0){$E_Prod_units_final = 0;}
if($e_f_used < 0){$e_f_used = 0;} else {$e_f_used = $e_f_used - $e_f_used - $e_f_used;}
if($e_m_used < 0){$e_m_used = 0;} else {$e_m_used = $e_m_used - $e_m_used - $e_m_used;}

// calculate change in organics
db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name='planet_organ'");
$planet_organ = dbr();
unset($Prod_units);
$Prod_units=floor($planet[alloc_organ]/$planet_organ[value]);
$O_Prod_units = round($Prod_units);
if($O_Prod_units > 0) {$o_sign = " Made Per DM";}else{$o_sign = "";}

/*
	$reproduction_factor = 0.01 / ($planet['tax_rate'] / 2);
	dbn(__FILE__,__LINE__,"update ${db_name}_planets set colon = colon + ((colon * (0.01 / (tax_rate / 2))) / '$day_maint_count')");
*/

// calculate change in colonists
if($planet['tax_rate'] == 0)
{
	$reproduction_factor = 0.01; // avoid a division by zero error
}
else
{
	$reproduction_factor = 0.01 / ($planet['tax_rate'] / 2);
}
$colonist_change_over_one_day = ($planet['colon'] - $planet['alloc_organ'] - $planet['alloc_elect'] - $planet['alloc_fight']) * $reproduction_factor;
$PColonists = round($colonist_change_over_one_day / $day_maint_count);
if($PColonists > 0) {$c_sign = " Reproduced Per DM";}else{$c_sign="";}


/*$Idle=idle_colonists();
if($planet[tax_rate] == 20){$a_number = 0.85;}
elseif($planet[tax_rate] == 19){$a_number = 0.865;}
elseif($planet[tax_rate] == 18){$a_number = 0.88;}
elseif($planet[tax_rate] == 17){$a_number = 0.895;}
elseif($planet[tax_rate] == 16){$a_number = 0.91;}
elseif($planet[tax_rate] == 15){$a_number = 0.925;}
elseif($planet[tax_rate] == 14){$a_number = 0.94;}
elseif($planet[tax_rate] == 13){$a_number = 0.955;}
elseif($planet[tax_rate] == 12){$a_number = 0.97;}
elseif($planet[tax_rate] == 11){$a_number = 0.985;}
elseif($planet[tax_rate] == 10){$a_number = 1.00;}
elseif($planet[tax_rate] == 9){$a_number = 1.01;}
elseif($planet[tax_rate] == 8){$a_number = 1.02;}
elseif($planet[tax_rate] == 7){$a_number = 1.03;}
elseif($planet[tax_rate] == 6){$a_number = 1.04;}
elseif($planet[tax_rate] == 5){$a_number = 1.05;}
elseif($planet[tax_rate] == 4){$a_number = 1.06;}
elseif($planet[tax_rate] == 3){$a_number = 1.07;}
elseif($planet[tax_rate] == 2){$a_number = 1.08;}
elseif($planet[tax_rate] == 1){$a_number = 1.09;}
else {$a_number = 1.10;}
$PColonists = round((($Idle*$a_number+($planet[colon]-$Idle))-$planet[colon]) / $day_maint_count);
if($PColonists > 0) {$c_sign = "+";}else{$c_sign="";}*/


// ---------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------



	$output_str .= "<tr><td><h3>Colonist Allocation</h3></tr></td><form name=pop_set_form method=post action=planet.php><input type=hidden name=planet_id value='$planet_id'><input type=hidden name=assinging value='1'>";

	$output_str .= make_row(array("Tax Rate","<input type=text name=set_tax_rate value='$planet[tax_rate]' size=6>%"));
	$output_str .= "<br />";
	$output_str .= make_row(array("<b class=b1>Production Unit</b>","<b class=b1>Assigned</b>","<b class=b1>Net Production Change</b>","<b class=b1>Metal Used</b>","<b class=b1>Fuel Used</b>","<b class=b1>Elect Used</b>"));
	$output_str .= make_row(array("To produce Fighters","<input type=text name=num_pop_set_1 value='$planet[alloc_fight]' size=8>","<b>".$F_Prod_units.$f_sign."</b>",($f_m_used * -1),($f_f_used * -1),($f_e_used * -1)));
	$output_str .= make_row(array("To produce Electronics","<input type=text name=num_pop_set_2 value='$planet[alloc_elect]' size=8>","<b>".$E_Prod_units_final.$e_sign."</b>",($e_m_used * -1),($e_f_used * -1)));
	$output_str .= make_row(array("To produce Organics","<input type=text name=num_pop_set_3 value='$planet[alloc_organ]' size=8>","<b>".$O_Prod_units.$o_sign."</b>"));
	$output_str .= make_row(array("Idle Colonists",idle_colonists(),"<b>".$PColonists.$c_sign."</b>"));

	$output_str.= "<tr><td><input type=submit value='   Set   '></td><td><input type=reset value=' Reset '></td></tr></form>";

	$output_str .= "</table><br />";

	$output_str .= "<i>Please note that production change in electronics also subtracts newly produced electronics used in generating fighters!</i><br />";

	$output_str .= "</td></tr></table>";


	#launch pad creation.
	if(!$planet[launch_pad]){
		$output_str .= "<p><a href=add_planetary.php?planet_id=$planet_id&launch_pad=1>Build Missile Launch Pad</a> - <b>100000</b> Credits, 200 Electronics, 100 Metal, 100 Fuel";

	} elseif($planet[launch_pad] -1 > 1){ #counting down
		$left = $planet[launch_pad] -1;
		$output_str .= "<p><b>$left</b> hours until <b class=b1>Missile Launch Pad </b>is ready.";

	} elseif($planet[missile] == 0){ #missile construction
		$output_str .= "<p>This planet has a <b class=b1>Missile Launch Pad</b>:";
		$output_str .= "<br /><a href=add_planetary.php?planet_id=$planet_id&missile=1>Construct Omega Missile</a> - <b>100000</b> Credits, 50 Electronics, 200 Metal, 100 Fuel, " . max(10, $planet_attack_turn_cost) . " Turns";

	} else { #launch missile
		$output_str .= "<p>This planet has a <b class=b1>Missile Launch Pad</b> with <b>1</b> <b class=b1>Omega Missile<b>:";
		$output_str .= "<br /><a href=add_planetary.php?planet_id=$planet_id&launch_missile=-1>LAUNCH Omega Missile</a>";
	}

	if($planet[super_hostile] != 1)
		{
		db(__FILE__,__LINE__,"select count(*) as total from ${db_name}_planets where owner_id = $planet[owner_id] and super_hostile = 1");
		$raw_num = dbr();
		$num_sh_planets = $raw_num[0];
		$price = pow(2,$num_sh_planets) * 1000000;
		$output_str .= "<p><a href=add_planetary.php?planet_id=$planet_id&super_hostile=1>Make Super Hostile Weapons</a> - <b>" . number_format($price) . "</b> Credits";
		}

	if($planet[wormhole_gen] == 1) {
		get_star();
		if($star[wormhole] == 0) {
			$output_str .= "<p>This planet has a <b class=b1>Wormhole Generator</b>:";
			$output_str .= "<br /><a href=add_planetary.php?planet_id=$planet_id&generate_wh=1>Generate Wormhole</a> - 150000 Credits, 1200 Dark-Matter, 1000 Electronics, 200 Turns";
		} elseif ($star[wormhole] != 0 && $star[art_wh] != 0 && $planet[wormhole] != 0) {
			$output_str .= "<p>This planet has a <b class=b1>Wormhole Generator</b>:";
			$output_str .= "<br />A wormhole to SS <b>$star[wormhole]</b> is currently open. - <a href=add_planetary.php?planet_id=$planet_id&generate_wh=3>Collapse Wormhole</a>";
		} elseif($star[wormhole] != 0 && $star[art_wh] != 0 && $planet[wormhole] == 0) {
			$output_str .= "<p>This planet has a <b class=b1>Wormhole Generator</b>:";
			$output_str .= "<p>A wormhole to SS #<b>$star[wormhole]</b> is currently open. <br />However it is controlled by another <b class=b1>Generator</b> which you do not own. <br />This prevents you from generating another Wormhole.";
		}

	}

	if ($flag_bmrkt != 0 && $planet[research_fac] == 0) {
		$output_str .= "<p><a href=add_planetary.php?planet_id=$planet_id&research_fac=1>Build Research Facility</a> - <b>$research_fac_cost</b>";
	} elseif ($planet[wormhole_gen] == 0 && $flag_dmatter == 1) {
		$output_str .= "<p><a href=add_planetary.php?planet_id=$planet_id&wormhole_gen=1>Build Wormhole Generator</a> - <b>$wormhole_gen_cost</b> Credits and <b>$wormhole_gen_tech</b> Tech. Units";
	}

	if(!$planet[shield_gen]){
		$output_str .= "<br /><a href=add_planetary.php?planet_id=$planet_id&shield_gen=1>Build Shield Generator</a> - <b>$shield_gen_cost</b>";
	} else {
		$t545 = $planet[shield_gen]*1000;
		$output_str .= "<p>Shield Charges: <b>$planet[shield_charge]</b> / <b>$t545</b> - <a href=planet.php?planet_id=$planet_id&all_shield=1>Charge All</a>";
	}


	db(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = $user[on_planet]");
	$planet = dbr();

		if(($user[login_id] == $planet[owner_id] || $user[clan_id] == $planet[clan_id]) && ($user[terra_imploder] > 0)) {
			$output_str .= "<br /><a href=planet.php?planet_id=$planet_id&destroy=1>Destroy $planet[planet_name]</a>";
		}


#only show the "claim" link to someone who doesn't own the planet.
} else {
	$output_str .= "<a href=planet.php?planet_id=$planet_id&claim=1>Claim $planet[planet_name]</a>";
}
$rs = "<p><a href=location.php>Takeoff</a>";

print_page("Planet",$output_str);
?>
