<?php
include_once("includes/nocache.inc.php");
/*
//
File:			upgrade.php
Objective:		management of Earth upgrades
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	15 October 2003
Last Modified:	11 November 2003

Copyright (c) 2003, 2004 by Pádraic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

// NOTE: a lot of work done in here - most areas sourced out to functions in upgrade_funcs.inc.php if you're looking for something.



require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

//$filename = "upgrade.php";

sudden_death_check($user);
db2(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
$eras = dbr2();
$era = $eras['value'];
if($user['login_id'] != 1){
db4(__FILE__,__LINE__,"select * from races where race_ID = $user[race] and era = $era");
$home = dbr4();
$homeworld = $home['planet_name'];
} elseif($user['login_id'] == 1){
db4(__FILE__,__LINE__,"select * from races where race_ID = $user[location] and era = $era");
$home = dbr4();
$homeworld = $home['planet_name'];
}
if($user['location'] != $user['race'] && $user['login_id'] != 1)
{
	print_page("Error","<p>You are unable to buy Accessories & Upgrades here.</p>");
}
elseif($user['ship_id'] == 1 && $user['login_id'] != 1)
{
	print_page("Error","<p>You are unable to buy Accessories & Upgrades here, as you do not have a real ship.</p>");
}

db(__FILE__,__LINE__,"select config from ${db_name}_ships where ship_id = $user[ship_id]");
$old_config = dbr();

//increases in capacity:
$fighter_inc = 300;
$shield_inc = 100;
$cargo_inc = 300;

//costs
$basic_cost = 5000;		//cost of the 3 basic upgrades

if($user_ship['size'] < 1)
{
	$user_ship['size'] = 1;
}

#shield unit costs
$shield_1 = 15000;
$shield_2 = 75000;
$shield_3 = 562500;

$rs = "<p><a href=\"upgrade.php\">Return to Accessories & Upgrades Store</a></p>";

///
/// Section to manage upgrading of multiple ships - 5 Sept 2003 - MtR
/// This section is intermediate and ugly - but hey, it works! :)
///

if(isset($mass_upgrade_type) && !isset($upgrade_many)) {

	$target = $user['login_id'];

	if($user_options['show_abbr_ship_class'] == 1){ #abbriviate class names
		$class_temp_var = "class_name_abbr";
	} else {
		$class_temp_var = "class_name";
	}

	$ADODB_FETCH_MODE = 2;
	if($sort_ships){
		if($sorted_ships==1){
			$going = "asc";
			$sorted_ships=2;
		} else {
			$going = "desc";
			$sorted_ships=1;
		}

		$order_by = "order by '$sort_ships' $going";
	} else {
		$order_by = "order by fighters desc,ship_name asc";
	}

	$text .= "<h3>Please select the ships you wish to include in this mass upgrade:</h3>";

	db(__FILE__,__LINE__,"select ship_name,$class_temp_var,max_fighters,max_shields,cargo_bays,config,upgrades,ship_id,login_id from ${db_name}_ships where login_id = '$target' && location = $user[race] && upgrades > 0 $order_by");
	$clan_ship = dbr();
	if($clan_ship) {

		$text .= "<form method=\"post\" action=\"upgrade.php\" name=\"upgrade_fleet\" id=\"upgrade_fleet\"><input type=\"hidden\" name=\"upgrade_many\" value=\"1\" /><input type=\"hidden\" name=\"mass_upgrade_type\" value=\"$mass_upgrade_type\" /><input type=\"submit\" value=\"Upgrade Ships\" /><br /><br />";
		$text .= make_table(array("<a href=\"upgrade.php?mass_upgrade_type=$mass_upgrade_type&amp;sort_ships=ship_name&amp;sorted_ships=$sorted_ships\">Ship Name",
			"<a href=\"upgrade.php?mass_upgrade_type=$mass_upgrade_type&amp;sort_ships=class_name_abbr&amp;sorted_ships=$sorted_ships\">Ship Class</a>",
			"<a href=\"upgrade.php?mass_upgrade_type=$mass_upgrade_type&amp;sort_ships=max_fighters&amp;sorted_ships=$sorted_ships\">Max Fighters</a>",
			"<a href=\"upgrade.php?mass_upgrade_type=$mass_upgrade_type&amp;sort_ships=max_shields&amp;sorted_ships=$sorted_ships\">Max Shields</a>",
			"<a href=\"upgrade.php?mass_upgrade_type=$mass_upgrade_type&amp;sort_ships=cargo_bays&amp;sorted_ships=$sorted_ships\">Cargo Bays</a>",
			"<a href=\"upgrade.php?mass_upgrade_type=$mass_upgrade_type&amp;sort_ships=config&amp;sorted_ships=$sorted_ships\">Specials</a>",
			"<a href=\"upgrade.php?mass_upgrade_type=$mass_upgrade_type&amp;sort_ships=upgrades&amp;sorted_ships=$sorted_ships\">Upgrades</a>",
			"Add Ship"));

		//loop through ships
		while($clan_ship) {
			if(!$clan_ship['config']){
				$clan_ship['config'] = "None";
			}
			$clan_ship['ship_id'] = "<div align=\"center\"><input type=\"checkbox\" name=\"expl[$clan_ship[ship_id]]\" value=\"$clan_ship[ship_id]\" /></div>";
			unset( $clan_ship['login_id'] );
			$clan_ship['ship_name']="<b class=b1>$clan_ship[ship_name]</b>";
			$text .= make_hash_row($clan_ship);
			$clan_ship = dbr(1);
		}
		$text .= "</table><br />";
		$text .= "<input type=\"submit\" value=\"Upgrade Ships\" /> - <a href=\"javascript:TickAll('upgrade_fleet')\">Invert Ship Selection</a></form><p>";
	} else {
		$text .= "<p>No ship at this location have a free upgrade pod.</p>";
	}
	print_page("Upgrading Ships", $text);

} elseif(isset($upgrade_many)) {

	if(!$expl){
			print_page("Upgrading Ships","<p>You must select at least one ship to upgrade.</p>");
	}
	$math = 0;
	$temp444 = $expl;
	while ($var = each($temp444)) {
		db(__FILE__,__LINE__,"select login_id,upgrades from ${db_name}_ships where ship_id = '$var[value]'");
		$target_ship = dbr();
		$math++;
		if($target_ship[login_id] != $user[login_id]) {
			print_page("Upgrading","<p>You can only upgrade one of your own ships!</p>");
		} elseif ($target_ship['upgrades'] < 1) {
			print_page("Upgrading","<p>You cannot upgrade a ship with no free upgrade pods!</p>");
		}
	}
	if($user['cash'] < ($math * $basic_cost)) {
		print_page("Upgrading","<p>It costs <b>".($math * $basic_cost)."</b> to purchase upgrades for all ships chosen. You do not have enough cash to do this. You should return to the last menu and choose a lower number of ships to upgrade.</p>");
	} elseif($sure != "yes") {
	$ostr .= "<p>Are you sure you want to upgrade <b>$math</b> ship(s) for a cost of <b>" . ($math * $basic_cost) . "</b> credits?</p>";
	$ostr .= "<form name=\"upgrade_ships\" action=\"upgrade.php\" method=\"post\">";
	$temp444 = $expl;
	$i=0;
	while ($var = each($temp444)) {
		$ostr .= "<input type=\"hidden\" name=\"expl[$i]\" value=\"$var[value]\" />";
		$i++;
	}
	$ostr .= "<input type=\"hidden\" name=\"upgrade_many\" value=\"1\" />";
	$ostr .= "<input type=\"hidden\" name=\"mass_upgrade_type\" value=\"$mass_upgrade_type\" />";
	$ostr .= "<input type=\"hidden\" name=\"sure\" value=\"yes\" />";
	$ostr .= "<input type=\"submit\" name=\"submit\" value=\"Yes\" /> <input type=\"Button\" width=\"30\" value=\"No\" onclick=\"javascript: history.back()\" /> </form>";
	print_page("Sure?",$ostr);
	}
	else
	{
	$temp444 = $expl;
	$buyf = $mass_upgrade_type;

	if($buyf==1)
	{
		$upg_name = "Fighter Bays";
	}
	elseif($buyf==2)
	{
		$upg_name = "Shield Capacity";
	}
	elseif($buyf==3)
	{
		$upg_name = "Cargo Bays";
	}
	while ($var = each($temp444))
	{
		if($buyf ==1) { //Fighter capacity
				take_cash($basic_cost);
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set max_fighters = max_fighters + '$fighter_inc',upgrades = upgrades - 1 where ship_id = '$var[value]'");
		} elseif($buyf ==2) { //Shield Capacity
			take_cash($basic_cost);
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set max_shields = max_shields + '$shield_inc',upgrades = upgrades - 1 where ship_id = '$var[value]'");
		} elseif($buyf ==3) { //Cargo capacity
			take_cash($basic_cost);
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set cargo_bays = cargo_bays + '$cargo_inc',upgrades = upgrades - 1 where ship_id = '$var[value]'");
		}
	}
	$error_str .= "<p><b>$math</b> Ships Upgraded with $upg_name at a total cost of <b>".($basic_cost * $math)."</b> credits.</p>"
		. "<p>Perform another <a href=\"$PHP_SELF?mass_upgrade_type=$mass_upgrade_type\">$upg_name Multiple Ship Upgrade</a></p>"
		. $out;
	print_page("Upgrading Ships", $error_str);
	}
}
///
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///






// Note: As at QS 2.1.23 - upgrade purchasing governed by upgrade_funcs.inc.php - all repeated code diverted to
// functions - upgrade details stored in separate function, queried and popped into basic purchasing functions.
// Smaller, easier to manage and manipulate. In future divert all standalone variables to a single include
// file - perhaps make available as part of Admin function? - like db_vars.inc.php







db(__FILE__,__LINE__,"select upgrades from ${db_name}_ships where ship_id = $user[ship_id]");
$upgrade_pods = dbr();

//
// checks which type of upgrade is being purchased (basic or other)
// Basic upgrades simply upgrade the NUMBER of a ships stat, e.g. more cargo bays, more fighter bays
// Other upgrades have specific purposes and are referred to by other code within the game.
//

if(isset($buy)) {
	if($buy > 0 && $buy < 4)  // the three basic upgrades
	{
		$error_str .= Purchase_BasicUpgrade($buy);
	}
	elseif($buy > 3) //all other possible upgrades
	{
		$error_str .= Purchase_Upgrade($buy);
	}
}

////////////////////////////////////////////////////////
/// Begin printing upgrade purchase links //////////////
////////////////////////////////////////////////////////


if($b_buy)
{
	$error_str .= Purchase_MultipleUpgradeForShip($b_buy, $num_up);
}

// newer upgrade system will automatically add and make available for purchase any upgrade added to
// the QUANTUM_upgrade_list database table.

$error_str .= "Welcome! You have reached <b class=b1>$home[upgrade]</b><br />";

$error_str .= "<br />This ship has <b>$upgrade_pods[0]</b> <b class=b1>Upgrade Pod</b>(s) remaining.<br />Each upgrade will occupy one of these slots.";

	// basic upgrades
	$error_str .= "<h3>Basic Upgrades:</h3>";
		$basic_upg_txt = Fetch_BasicUpgradePurchaseLines(1);
	$error_str .= $basic_upg_txt['txt'];

	// weapons
	$error_str .= "<h3>Weapons:</h3>";
		$upg_text = Fetch_UpgradePurchaseLines(6,1);
	$error_str .= $upg_text['txt'];

	// defences
	$error_str .= "<h3>Defences:</h3>";
		$upg_text = Fetch_UpgradePurchaseLines(7,1);
	$error_str .= $upg_text['txt'];

	// propulsion
	$error_str .= "<h3>Propulsion Upgrades:</h3>";
		$upg_text = Fetch_UpgradePurchaseLines(4,1);
	$error_str .= $upg_text['txt'];

if($visibility_flag == 1) {
	// scanners
	$error_str .= "<h3>Scanners:</h3>";
		$upg_text = Fetch_UpgradePurchaseLines(2,1);
	$error_str .= $upg_text['txt'];

	//cloaking devices
	$error_str .= "<h3>Cloaking Devices:</h3>";
		$upg_text = Fetch_UpgradePurchaseLines(3,1);
	$error_str .= $upg_text['txt'];
}
	// miscellaneous
	$error_str .= "<h3>Miscellaneous:</h3>";
		$upg_text = Fetch_UpgradePurchaseLines(5,1);
	$error_str .= $upg_text['txt'];

if($user_ship['upgrades'] > 1) {
		$error_str .= "<h3>Mass Upgrades:</h3>Enables certain upgrades to be installed multiple times on your ship.<br />";
		$error_str .= Fetch_MassUpgradePurchaseSelection(1);
}
$error_str .= "<p><a href=\"help.php?upgrades=1\" target=\"_blank\">Information about Accessories & Upgrades</a></p>";
	$rs = "<p><a href=hw.php>Return to $homeworld</a>";

print_page("Accessories & Upgrades",$error_str);
?>
