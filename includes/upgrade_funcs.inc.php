<?php
/*
//
File:			upgrade_funcs.inc.php
Objective:		collection of function for calculating effects from Upgrades and their purchase
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	12 October 2003
Last Modified:	11 November 2003

Copyright (c) 2003, 2004 by Pádraic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

// check if ship has a scanner
function CheckShip_ForScanner($id) {
	global $db_name;
	db(__FILE__,__LINE__,"select * from ${db_name}_upgrade_units where ship_id = '$id'");
	$scan = dbr();
	if($scan['stand_scanner'] >= 1 || $scan['adv_scanner'] >= 1 || $scan['grav_scanner'] >= 1 || $scan['subs_array'] >= 1 || $scan['alien_array'] >= 1)
	{
		return 1;
	}
	else
	{
		return 0;
	}
}

// check if ship has cloaking device
function CheckShip_ForCloak($id) {
	global $db_name;
	db(__FILE__,__LINE__,"select * from ${db_name}_upgrade_units where ship_id = '$id'");
	$scan = dbr();
	if($scan['stand_cloak'] >= 1 || $scan['adv_cloak'] >= 1 || $scan['pir_deflect'] >= 1 || $scan['spat_unit'] >= 1 || $scan['dim_flux'] >= 1)
	{
		return 1;
	}
	else
	{
		return 0;
	}
}


function CheckShip_ForDefence($id) {
		global $db_name;
	db(__FILE__,__LINE__,"select * from upgrade_list where type = 7 && damage > 0");
	db2(__FILE__,__LINE__,"select * from ${db_name}_upgrade_units where ship_id = '$id'");
		$ship=dbr2();
$def = 0;
while($upg=dbr()) {

	if($ship[$upg[sql_name]] > 0) {
$def += round($upg[damage] * (mt_rand(90,110) / 100)) * $ship[$upg[sql_name]];
	} else {
$def += 0;
	}
}

return $def;
}

function CheckShip_ForAttack($id) {
		global $db_name;
	db(__FILE__,__LINE__,"select * from upgrade_list where type = 6 && damage > 0");
	db2(__FILE__,__LINE__,"select * from ${db_name}_upgrade_units where ship_id = '$id'");
		$ship=dbr2();
$att = 0;
while($upg=dbr()) {

	if($ship[$upg[sql_name]] > 0) {
$att += round($upg[damage] * (mt_rand(90,110) / 100)) * $ship[$upg[sql_name]];
	} else {
$att += 0;
	}
}

return $att;
}

// check the ships defence score from upgrades
function CheckShipCount_ForUpgrade($sql_name, $id) {
	global $db_name;
	db(__FILE__,__LINE__,"select ".$sql_name." from ${db_name}_upgrade_units where ship_id = '$id'");
	$cnt = dbr();
	return $cnt[$sql_name];
}



// function used when purchasing any weapon/defence/etc upgrades
function Purchase_Upgrade($buy)
{
	global $Upgrade_Cost, $user_ship, $user, $upgrade_pods, $db_name, $sure, $filename, $rs, $bmrkt_id;
	if(isset($bmrkt_id)) {$filename="bm_upgrades.php?bmrkt_id=$bmrkt_id";} else {$filename="upgrade.php";}
	//check for pre-existing scanner/cloaking device
	if($buy > 200 && $buy < 300)
	{
		$cloak_res = CheckShip_ForScanner($user_ship['ship_id']);
		if ($cloak_res == 1){
			print_page("Device Already Present","Your ship is already equipped with a <b class=b1>Scanner</b>. <br />The power relays on your ship are unable to cope with any more.<p>");
		}
	}
	elseif($buy > 100 && $buy < 200)
	{
		$cloak_res = CheckShip_ForCloak($user_ship['ship_id']);
		if ($cloak_res == 1){
			print_page("Device Already Present","Your ship is already equipped with a <b class=b1>Cloaking Device</b>. <br />The power relays on your ship are unable to cope with any more.<p>");
		}
	}

	$upgrade = Fetch_UpgradeDetail($buy);
	$upgrade_count = CheckShipCount_ForUpgrade($upgrade['sql_name'], $user['ship_id']);
	$old_config = $user_ship['config'];
	if($user['cash'] < $upgrade['cost'])
	{
		$error_str .= "You can not afford to buy a <b class=b1>$upgrade[name]</b>. You require more Credits!<br /><br />";
	}
	elseif($upgrade['tech'] > 0 && ($user['tech'] < $upgrade['tech']))
	{
		$error_str .= "You can not afford to buy a <b class=b1>$upgrade[name]</b>. You require more Tech. Units!<br /><br />";
	}
	elseif ($upgrade_count >= $upgrade['lim'] && $upgrade['lim'] != -1)
	{
		$error_str .= "This ship already has $upgrade_count $upgrade[name]s, and is unable to support another.<p>";
	}
	elseif ($upgrade_pods[0] < 1)
	{
		$error_str .= "This ship does not have any upgrade pods available.<p>";
	}
	elseif($sure != 'yes')
	{
		get_var("Buy $upgrade[name]","$filename","Are you sure you want to buy a <b class=b1>$upgrade[name]</b>, for the <b class=b1>$user_ship[ship_name]</b>?",'sure','');
	}
	elseif (($upgrade['cost']) || ($user['login_id'] ==1))
	{
		$error_str .= "<b class=b1>$upgrade[name]</b>, purchased and installed on the <b class=b1>$user_ship[ship_name]</b> for <b>$upgrade[cost]</b> Credits.<p>";
		take_cash($upgrade['cost']);
		if($upgrade['tech'] > 0)
		{
			take_tech($upgrade['tech']);
		}
		if (!eregi($upgrade['abbrev'],$old_config))
		{
			$user_ship['config'] = $old_config.":$upgrade[abbrev]";
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set config = '$user_ship[config]',upgrades = upgrades - 1 where ship_id = '$user[ship_id]'");
		dbn(__FILE__,__LINE__,"update ${db_name}_upgrade_units set ".$upgrade['sql_name']." = ".$upgrade['sql_name']." + 1 where ship_id = '$user[ship_id]'");
		$upgrade_pods[0] = $upgrade_pods[0] - 1;
	}
	return $error_str;
}


// function used when purchasing the three basic upgrades
function Purchase_BasicUpgrade($buy)
{
	global $user, $upgrade_pods, $user_ship, $db_name;
	$upgrade = Fetch_UpgradeDetail($buy);
	if($user['cash'] < $upgrade['cost'])
	{
		$error_str .= "You can not afford any of the Basic Upgrades.<br /><br />";
	}
	elseif($upgrade_pods['0'] < 1)
	{
		$error_str .= "This ship has no upgrade pods left.<br /><br />";
	}
	else
	{
		$error_str .= "You have increased the <b class=b1>$user_ship[ship_name]s</b> $upgrade[name] by <b>$upgrade[inc]</b> for <b>$upgrade[cost]</b> Credits.<br /><br />";
		take_cash($upgrade['cost']);
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set $upgrade[sql_name] = $upgrade[sql_name] + '$upgrade[inc]',upgrades = upgrades - 1 where ship_id = '$user_ship[ship_id]'");
		$upgrade_pods['0'] = $upgrade_pods['0'] - 1;
		$user_ship[$upgrade['sql_name']] += $upgrade['inc'];
		$user_ship[$upgrade['sql_name2']] += $upgrade['inc'];
	}
	return $error_str;
}


// function to enable purchase of multiple upgrades for a ship
function Purchase_MultipleUpgradeForShip($b_buy, $num_up) {
	global $rs, $db_name, $user_ship, $upgrade_pods, $user, $sure;
	settype($num_up, "integer");
	$upgrade = Fetch_UpgradeDetail($b_buy);
	if($num_up < 0)
	{
		print_page("Upgrades","Please select a number of upgrades to purchase and fit to the <b>$user_ship[ship_name]</b>.");
	}
	elseif($num_up > $user_ship['upgrades'])
	{
		print_page("Upgrades","You do not have that many upgrade pods.");
	}
	elseif(($num_up * $upgrade['cost']) > $user['cash'])
	{
		print_page("Upgrades","You do not have enough money for $num_up upgrade pods.");
	}
	elseif($sure != 'yes')
	{
		get_var('Buy Multiple Upgrades','upgrade.php','Are you sure you want to do a Mass Upgrade?','sure','');
	}
	$error_str .= "You have increased the <b class=b1>$user_ship[ship_name]'s</b> $upgrade[name] by <b>".$upgrade['inc'] * $num_up."</b> for <b>".$upgrade['cost'] * $num_up."</b> Credits. <p>";
	take_cash($upgrade['cost'] * $num_up);
	dbn(__FILE__,__LINE__,"update ${db_name}_ships set ".$upgrade['sql_name']." = ".$upgrade['sql_name']." + ('$upgrade[inc]' * '$num_up'), upgrades = upgrades - $num_up where ship_id = '$user_ship[ship_id]'");
	$upgrade_pods['0'] = $upgrade_pods['0'] - $num_up;
	$user_ship[$upgrade['sql_name']] += $upgrade['inc'] * $num_up;
	return $error_str;
}


// Function to fetch name, sql_name, cost & abbreviation/quantity of any upgrade
// Note: config abbrevs are temp only - need a better summary sys (perhaps small js popup? js floating panel?)
// also segregate upgrades into groups of ten - enable auto-add of plugin upgrades for 2.2.5 (dir detect!)

/*
// name:     Full name of component
// cost:	 Credit cost of component
// tech:	 Tech cost of component
// sql_name: SQL fieldname for component in upgrades table
// inc:		 Number/Capacity of Upgrade (basic upgrades only)
// lim:		 Number each ship is limited to carrying, -1 means no limit
// abbrev:	 Abbreviation used appended to config line of ship
*/

// NOTE: to be transferred to database table ASAP for 2.1.24 - add additional fields (type,level,seller) to enable
// auto-ordering and inclusion in pages to be sold.

function Fetch_MassUpgradePurchaseSelection($seller=1) {
	global $db_name;
	if($seller == 1) {$filename = "upgrade.php";} else {$filename = "bm_upgrades.php";}
	db(__FILE__,__LINE__,"select * from upgrade_list where type = 1 && seller = '$seller' order by item_id");
	$error_str .= "<form method=GET action=$filename>";
	$error_str .= "<select name=b_buy>";
	while($Upgrade = dbr()) {
		$error_str .= "<option value=$Upgrade[item_id]> + $Upgrade[inc] $Upgrade[name]";
	}
	$error_str .= "</select>";
	$error_str .= " - <input type='text' size='3' name='num_up'>";
	$error_str .= "<br /><br /><input type=submit value=Submit></form><br /><br />";
	return $error_str;
}


function Fetch_BasicUpgradePurchaseLines($seller=1) {
	global $db_name;
	if($seller == 1) {$filename = "upgrade.php";} else {$filename = "bm_upgrades.php";}
db2(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
$eras = dbr2();
$era = $eras['value'];
	db(__FILE__,__LINE__,"select * from upgrade_list where type = 1 && seller = '$seller' && era = $era order by item_id");
	$upgrade_array = array();
	$upgrade_array['txt'] .= make_table(array("Item Name","Capacity Added","Cost","Tech Cost"),"75%");
	while($Upgrade = dbr())
	{
		$upgrade_array['txt'] .= make_row(array("$Upgrade[name]", "$Upgrade[info]", "$Upgrade[cost]", "$Upgrade[tech]", "<a href=$filename?buy=$Upgrade[item_id]>Purchase</a>", "<a href=$filename?mass_upgrade_type=$Upgrade[item_id]>Multiple Ships</a>", "<a href='javascript:alert(\"Multiple Ships: Enables upgrades to be added to multiple ships at the same time.\\n\\nNo more need to add these ship by ship!\")'>?</a>"));
	}
	$upgrade_array['txt'] .= "
		</table>";
	return $upgrade_array;
}





function Fetch_UpgradePurchaseLines($type, $seller=1) {
	global $db_name, $bmrkt_id, $user;
	if($seller == 1) {$filename = "upgrade.php";} else {$filename = "bm_upgrades.php"; $add = "&bmrkt_id=$bmrkt_id";}
	db(__FILE__,__LINE__,"select * from ${db_name}_db_vars where name = 'era'");
	$era2 = dbr();
	$era = $era2['value'];
	db2(__FILE__,__LINE__,"select * from ${db_name}_db_vars where name = 'races'");
	$races2 = dbr2();
	$races = $races2['value'];
$test = db(__FILE__,__LINE__,"select * from upgrade_list where type = '$type' && seller = '$seller' && era = '$era' && (race = '$user[race]' || race = '0') order by damage");
$test2 = $upgrade_array = array();
	$test;
	$test2;
	$upgrade_array['txt'] .= make_table(array("Item Name","Information","Cost","Tech Cost"),"75%");
	while($Upgrade = dbr())
	{
		$upgrade_array['txt'] .= make_row(array("$Upgrade[name]","$Upgrade[info]","$Upgrade[cost]","$Upgrade[tech]","<a href=$filename?buy=$Upgrade[item_id]$add>Purchase</a>"));
	}
	$upgrade_array['txt'] .= "
		</table>";
	return $upgrade_array;
}


// function to fetch cost/ number limit/ etc.
function Fetch_UpgradeDetail($buy)
{
	global $db_name;
	db(__FILE__,__LINE__,"select * from upgrade_list where item_id = '$buy'");
	$upgrade_array = dbr();
	return $upgrade_array;
}



?>