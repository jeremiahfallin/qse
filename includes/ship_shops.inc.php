<?php
/*
//
File:			ship_shops.inc.php
Objective:		self-adjusting include file to display shipyard shiplisting for Earth/Blackmarkets and Alien Shipyards
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	29 December 2003	Date Modified:	30 December 2003

Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/


/* This file operates by:

1. Checking whether player is at Earth, a Blackmarket, or an Alien Shipyard
2. Pulling all ships purchasable from the correct location from the database (as defined by purchase_loc)
3. Displaying purchasable ships according to category (as defined by ship_categ)
4. In the actual purchase action - the purchase script will recheck player purchase location to ensure ship ordered was correctly made available, this will also block any possible url tampering with purchase urls such as altering ship_ids passed.

SHIP_CATEG nums:

0:		Default Vessels - only available through QS functions when player dead, ship destroyed, etc.
1:		Scout Ships
2:		Freighters/Miners
3:		Warships
4:		Frigates (War/Special)
5:		Modular/User-defined Ships
6:		Exotic Ships (e.g. Transverser)
7:		Transport Vessels
8:		Special Function Battle oriented Ships (e.g. Mine-Layer)
9:		Raiders
10:		Flag Ships (falls under 1 variant only rule)

PURCHASE_LOC nums:

1:			Earth
2:			Blackmarket
3:			Alien Shipyard
4:			Currently dosent exsist
5:			Currently dosent exsist
6:			Currently dosent exsist
7:			Purchasable from all three locations

Location of player relies on a variety of checks - see opening function call defined at bottom of page to see methods used.

*/

//require_once("user.inc.php"); //pre-called by including script???
//echo("Working!"); //Ignore above two lines!



// first grab the shipyard visited and the ships available to purchase at that location ignoring Default ships
db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'Homeworld'");
$races = dbr();
$race = $races['value'];
db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
$races2 = dbr();
$era = $races2['value'];

$ship_shop_inuse = Grab_Player_Shipyard();
if($ship_shop_inuse == 1)
{
	if($user['login_id'] != 1){
	db(__FILE__,__LINE__,"select * from ship_types where purchase_loc = 1 and ship_categ > 0 and auction = 0 and era = $era and (race = $user[race] or race = '0') order by type_id");
	} elseif ($user['login_id'] == 1){
	db(__FILE__,__LINE__,"select * from ship_types where purchase_loc = 1 and ship_categ > 0 and auction = 0 and era = $era and (race = $user[location] or race = '0') order by type_id");
	}
}
elseif($ship_shop_inuse == 2)
{
	db(__FILE__,__LINE__,"select * from ship_types where (purchase_loc = 2 or purchase_loc = 7) and ship_categ > 0 and auction = 0 and era = $era order by type_id");
	$append_bmid = $bmrkt_id;
}
elseif($ship_shop_inuse == 3)
{
	db(__FILE__,__LINE__,"select * from ship_types where (purchase_loc = 3 or purchase_loc = 7) and ship_categ > 0 and auction = 0 and era = $era order by type_id");
	$append_asid = $asyrd_id;
}

$admin_deact = array();
db2(__FILE__,__LINE__,"select ship_type_id from ${db_name}_admin_ships where status = 0");
while($unavailable = dbr2()){
	$admin_deact[] = $unavailable['ship_type_id'];
}

while($shipsavailable = dbr()) {
	if(!in_array($shipsavailable['type_id'],$admin_deact)){
		if($shipsavailable['ship_categ'] == 1) {
			$scout_ships .= add_shiptopurchase($shipsavailable);
		} elseif($shipsavailable['ship_categ'] == 2) {
			$freighter_ships .= add_shiptopurchase($shipsavailable);
		} elseif($shipsavailable['ship_categ'] == 3) {
			$warship_ships .= add_shiptopurchase($shipsavailable);
		} elseif($shipsavailable['ship_categ'] == 4) {
			$frigate_ships .= add_shiptopurchase($shipsavailable);
		} elseif($shipsavailable['ship_categ'] == 5) {
			$modular_ships .= add_shiptopurchase($shipsavailable);
		} elseif($shipsavailable['ship_categ'] == 6) {
			$exotic_ships .= add_shiptopurchase($shipsavailable);
		} elseif($shipsavailable['ship_categ'] == 7) {
			$transport_ships .= add_shiptopurchase($shipsavailable);
		} elseif($shipsavailable['ship_categ'] == 8) {
			$special_ships .= add_shiptopurchase($shipsavailable);
		} elseif($shipsavailable['ship_categ'] == 9) {
			$raider_ships .= add_shiptopurchase($shipsavailable);
		} elseif($shipsavailable['ship_categ'] == 10) {
			$flag_ships .= add_shiptopurchase($shipsavailable);
		}
	}
}

// third generate the page structure to display the information

$ship_cats = array('scout_ships' => 'Scout Ships','freighter_ships' => 'Freighters and Mining Vessels','warship_ships' => 'Warships',/*'frigate_ships' => 'Frigate Class Ships','modular_ships' => 'Modular Vessels',*/'exotic_ships' => 'Exotic Ships','transport_ships' => 'Transport Vessels','special_ships' => 'Special Function War-Ships','raider_ships' => 'Raider Class Ships','flag_ships' => 'Flag Ships');

foreach($ship_cats as $key=>$val) {
		$text .= "<h4>$val available:</h4>";
		//$$key refers to $scout_ship etc. strings defined in add_shiptopurchase function
		if(!isset($$key))
		{
			$text .= "<b>None</b><br /><br />";
		}
//		else
//		{
			$text .= make_table(array(/*"Ship Name","Abbrv.","Cash Cost","Tech. Cost"*/));
			$text .= stripslashes($$key);
			$text .= "</table>";
//		}
}



// Note no intro or conclusion required - added by individual files that include_once this script.



// Functions //

function Grab_Player_Shipyard() {
	global $user, $bmrkt_id, $asyrd_id, $db_name;
	//Note: For more decentralised Earth-like starting points - need to change this
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
		$mr = dbr();
		$max_races = $mr['value'];
	if($user['location'] == $user['race'] || ($user['login_id'] == 1 && $user['location'] <= $max_races))
	{
		$shipyard = 1;
	}
	elseif(isset($bmrkt_id))
	{
		$shipyard = 2;
	}
	elseif(isset($asyrd_id))
	{
		$shipyard = 3;
	}

	return $shipyard;
}

function add_shiptopurchase($ship) {
	global $user_options, $append_bmid, $append_asid;
	if($user_options['allow_popups']==2)
	{
		$link = "<a href=\"javascript:modelesswin('ship_info.php?type=$ship[type_id]',300,600)\">";
	}
	elseif($user_options['allow_popups']==1)
	{
		$link = "<a href=\"javascript:popUp('ship_info.php?type=$ship[type_id]',300,600)\">";
	}
	else
	{
		$link = "<a href=\"help.php?ship_info=1&amp;shipno=$ship[type_id]\" target=\"_blank\">";
	}
	if(isset($append_bmid))
	{
		$append = "&amp;bmrkt_id=$append_bmid";
	}
	elseif(isset($append_asid))
	{
		$append = "&amp;asyrd_id=$append_asid";
	}
	else
	{
		$append = NULL;
	}

	if ($ship[upgrades] >= 1){
$boo = "<b>$ship[upgrades]</b> Upgrade Pods<br>";
}
elseif ($ship[upgrades] == 0){
$boo = "";
}
if ($ship[cargo_bays] >= 1){
$bang = "Max Cargo: <b>$ship[cargo_bays]<br></b>";
}
elseif ($ship[cargo_bays] == 0){
$bang = "";
}

if ($ship[mine_rate_fuel] >= 1 || $ship[mine_rate_metal] >= 1){
	if ($ship[mine_rate_fuel] >= 1){
	$fuel = " Fuel: <b>$ship[mine_rate_fuel]</b>";
	}
	elseif ($ship[mine_rate_fuel] == 0){
	$fuel = "";
	}
	if ($ship[mine_rate_metal] >= 1){
	$metal = " Metal: <b>$ship[mine_rate_metal]</b>";
	}
	elseif ($ship[mine_rate_metal] == 0){
	$metal = "";
	}
$mr = "Mining Rate: $fuel $metal</b><br>";
}
elseif ($ship[mine_rate_fuel] >= 0 && $ship[mine_rate_metal] >= 0){
$mr = "<br>";
}
if ($ship[fighters] <= 0){
$fig = "";
} elseif ($ship[fighters] >= 0){
$fig = "Fighters: <b>$ship[fighters]</b><br>";
}
if($ship[max_fighters] <= 0){
$m_fig = "";
} elseif($ship[max_fighters] >= 0){
$m_fig = "Max Fighters: <b>$ship[max_fighters]</b><br>";
}
if($ship_warp_cost == 0){
$shipwarp = "Ship Warp Cost: <b>$ship[move_turn_cost]</b><br>";
} else {
$shipwarp = "";
}
if($user_options['show_pics']){
$ship_img = "<td width=\"30%\"><img src='images/ships/ship_$ship[type_id]_tn.jpg' width = 160, height = 120></img></td>";
} else {
$ship_img = "";
}
//    <td width=\"30%\"><img src='/images/ships/ship_$ship[type_id]_tn.jpg' width=160 height=120></img></td>
//$boo = ['type_id'];
//	$export = make_row(array("$link $ship[name]</a>","$ship[class_abbr]","<b>$ship[cost]</b>","<b>$ship[tcost]</b>","$link ?</a>","<a href=\"ship_purchase.php?ship_type=$ship[type_id]$append\">Purchase</a>","<a href=\"ship_purchase.php?mass=$ship[type_id]$append\">Mass Purchase</a>"));
	$pretty_ship_cost = number_format($ship['cost']);
	$export = make_row(array("<table width='80%' height=\"108\" border=\"0\">
  <tr>
    $ship_img
    <td width=\"47%\"><p>$link $ship[name]</a><br>
        Ship type: <b>$ship[type]</b></br><br>
		$fig
        $m_fig
		Max Shields: <b>$ship[max_shields]</b><br>
		$shipwarp
		Config: <b>$ship[config]<br></b>
		$bang
		$mr
		$boo
        Descrip: $ship[descr]</p>
      </td>
    <td width=\"23%\"><p>Price:<b> $pretty_ship_cost</b><br>
        &amp;<b><br>
        $ship[tcost]</b> Tech<br>
        <br>
        <a href=\"ship_purchase.php?ship_type=$ship[type_id]$append\">Purchase</a>
      </p>
      <p><a href=\"ship_purchase.php?mass=$ship[type_id]$append\">Mass Purchase</a></p></td>
  </tr>
</table>"));
	return $export;
}

?>