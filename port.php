<?php
include_once("includes/nocache.inc.php");
$filename = "port.php";
/*
//
File:			port.php
Objective:		Modified port.php file to use the updated standard load/unload functionality
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	12 February 2004		Date Modified:	23 March 2004

Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

require_once("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

sudden_death_check($user);

	db(__FILE__,__LINE__,"select * from ${db_name}_ports where location = '$user[location]'");
	$port = dbr();
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
		$mr = dbr();
		$max_races = $mr['value'];
if (!$port && $user['location'] > $max_races) {
	print_page("Port","<p>You may not sell at a port that is not in the same system as you are in. Stop playing with the URL!</p>");
}

// Page heading + Sub-Menu
$text = Create_PageTitleBlock("StarPort #$port[port_id] in Star System #$user[location]");


//Change Prices if Trading has been enabled
// used only for display purposes since the rewrite

if($port[fuel_cap] != 0 and $flag_trading == 1)
	{
	$fuelIndice = ($port[fuel_cap] / 100);
	}
else
	{
	$fuelIndice = 1;
	}
if($port[metal_cap] != 0 and $flag_trading == 1)
        {
        $metalIndice = ($port[metal_cap] / 100);
        }
else
        {
        $metalIndice = 1;
        }
if($port[organ_cap] != 0 and $flag_trading == 1)
        {
        $organIndice = ($port[organ_cap] / 100);
        }
else
        {
        $organIndice = 1;
        }
if($port[elect_cap] != 0 and $flag_trading == 1)
        {
        $electIndice = ($port[elect_cap] / 100);
        }
else
        {
        $electIndice = 1;
        }

//Maug/Other Devs: Theres a bug in here somewhere, not the below bit, but somewhere else, to do with $port[etc].
/*$fuel_buy = $buy_fuel + $port['fuel'];
$fuel_sell = $buy_fuel - round(($buy_fuel/100)*20) + $port[fuel];
$metal_buy = $buy_metal + $port['metal'];
$metal_sell = $buy_metal - round(($buy_metal/100)*20) + $port[metal];
$elect_buy = $buy_elect + $port['elect'];
$elect_sell = $buy_elect - round(($buy_elect/100)*20) + $port[elect];
$organ_buy = $buy_organ + $port['organ'];
$organ_sell = $buy_organ - round(($buy_organ/100)*20) + $port[organ];*/

//QuickFix version of the above
$buy_fuel = $buy_fuel * $fuelIndice;
$buy_metal = $buy_metal * $metalIndice;
$buy_organ = $buy_organ * $organIndice;
$buy_elect = $buy_elect * $electIndice;
$fuel_buy = round($buy_fuel);
$metal_buy = round($buy_metal);
$elect_buy = round($buy_elect);
$organ_buy = round($buy_organ);

if($flag_trading == 0)
{
	//calculate starport price for buying player's resource
	//typically 20% below starport selling price, the rip-off merchants...:)
	$fuel_sell = round($buy_fuel - (round(($buy_fuel/100)*20)));
	$metal_sell = round($buy_metal - (round(($buy_metal/100)*20)));
	$elect_sell = round($buy_elect - (round(($buy_elect/100)*20)));
	$organ_sell = round($buy_organ - (round(($buy_organ/100)*20)));
} else {
	$fuel_sell = round($buy_fuel);
	$metal_sell = round($buy_metal);
	$elect_sell = round($buy_elect);
	$organ_sell = round($buy_organ);
}

// Colonists sold at ports include a 20% surcharge for transporting them from your HW
$colon_buy = (int)round($cost_colonist * 1.20);;

$rs = "<p><a href=port.php>Return to Starport</a>";

$amount = round($amount);
settype($amount, "integer");


// replace all with a foreach stat loading resource elements from database

// replacement for purchasing materials
// see ship_loading.inc.php for functions used here

if(isset($_GET['metal']) && $_GET['metal'] == 0)
{
	$text .= Load_Resource_Form("buy", "metal", "port.php");
	print_page("Starport - Purchases",$text);
}
if(isset($_GET['fuel']) && $_GET['fuel'] == 0)
{
	$text .= Load_Resource_Form("buy", "fuel", "port.php");
	print_page("Starport - Purchases",$text);
}
if(isset($_GET['elect']) && $_GET['elect'] == 0)
{
	$text .= Load_Resource_Form("buy", "elect", "port.php");
	print_page("Starport - Purchases",$text);
}
if(isset($_GET['organ']) && $_GET['organ'] == 0)
{
	$text .= Load_Resource_Form("buy", "organ", "port.php");
	print_page("Starport - Purchases",$text);
}
if(isset($_GET['colon']) && $_GET['colon'] == 0)
{
        $text .= Load_Resource_Form("buy", "colon", "port.php");
        print_page("Starport - Purchases",$text);
}

// repalcement for selling materials
// see ship_loading.inc.php for functions used here

if(isset($_GET['metal']) && $_GET['metal'] == 1)
{
	$text .= Unload_Resource_Form("sell", "metal", "port.php");
	print_page("Starport - Sales",$text);
}
if(isset($_GET['fuel']) && $_GET['fuel'] == 1)
{
	$text .= Unload_Resource_Form("sell", "fuel", "port.php");
	print_page("Starport - Sales",$text);
}
if(isset($_GET['elect']) && $_GET['elect'] == 1)
{
	$text .= Unload_Resource_Form("sell", "elect", "port.php");
	print_page("Starport - Sales",$text);
}
if(isset($_GET['organ']) && $_GET['organ'] == 1)
{
	$text .= Unload_Resource_Form("sell", "organ", "port.php");
	print_page("Starport - Sales",$text);
}

if(isset($_POST['load_resource']) && $_POST['load_resource'] == 1)
{
	$result = Load_Resource($_POST['fill_option'], $_POST['cap_option'], $_POST['resource'], 0, "port.php", $_POST['user_amount'], $_POST['user_credits']);
	print_page("Starport - Sales",$text.$result['text']);
}

if(isset($_POST['unload_resource']) && $_POST['unload_resource'] == 1)
{
	$result = Unload_Resource($_POST['fill_option'], $_POST['cap_option'], $_POST['resource'], 0, "port.php", $_POST['user_amount'], $_POST['user_credits']);
	print_page("Starport - Sales",$text.$result['text']);
}





// The vast majority of the following code is deprecated. Most has been rewritten as standard load/unload resource functions now used within StarPorts and Planets. Certain areas however are pending a rewrite and so remain for the present. -March 2004







#enable buy all for fleet
if($buy_all && ($user[ship_id]!=1 || $user[login_id] ==1)) {

	if($type == 0){
		$tech_mat = "metal";
		$text_mat = "Metal";
		$cost_mat = $metal_buy;
	} elseif($type == 1){
		$tech_mat = "fuel";
		$text_mat = "Fuel";
		$cost_mat = $fuel_buy;
	} elseif($type == 2){
		$tech_mat = "elect";
		$text_mat = "Electronics";
		$cost_mat = $elect_buy;
	} elseif($type == 3){
		$tech_mat = "organ";
		$text_mat = "Organics";
		$cost_mat = $organ_buy;
	}

	// modified load fighter code to help standardise all loading/purchase materials procedures in SE
	$taken = 0; //material purchased so far
	$ship_counter = 0;
	db(__FILE__,__LINE__,"select sum(cargo_bays-metal-fuel-elect-organ-darkmatter-colon), count(ship_id) from ${db_name}_ships where location = '$user[location]' && login_id='$user[login_id]' && cargo_bays > 0 && (metal+fuel+elect+organ+darkmatter+colon) < cargo_bays");
	$maths=dbr();
		if($user[cash] < $cost_mat){
		print_page("Failed","You don't have enough money for one $text_mat unit, let alone more.<br />Come back when you can afford it!");
		} elseif(!$maths[0]) {
			print_page("Failed","This operation failed as there are no ships that have Cargo Bays empty in this system.");
		} elseif($sure != "yes") {
		get_var('Load all ships','port.php',"There are <b>$maths[0]</b> empty Cargo Bays in <b>$maths[1]</b> ships in this system. <br />Do you want to fill as many as you can afford to fill with $text_mat?",'sure','yes');
		} else {
			db2(__FILE__,__LINE__,"select * from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && cargo_bays > 0 && (metal+fuel+elect+organ+darkmatter+colon) < cargo_bays order by cargo_bays desc");
			while($ships = dbr2()) {
				//player can load ship.
				$free = $ships[cargo_bays] - $ships[metal] - $ships[fuel] - $ships[organ] - $ships[colon] - $ships[darkmatter] - $ships[elect];
				if($user[cash] >= ($free * $cost_mat)) {
					$ship_counter++;
					dbn(__FILE__,__LINE__,"update ${db_name}_ships set $tech_mat = $tech_mat + $free where ship_id = '$ships[ship_id]'");
					dbn(__FILE__,__LINE__,"update ${db_name}_ports set $tech_mat = $tech_mat - $free where port_id = '$port[port_id]'");
					$text .= "<br /><b class=b1>$ships[ship_name]</b> had its $text_mat cargo increased by <b>$free</b> to maximum capacity.";
					if($ships[ship_id] == $user_ship[ship_id]){
						$user_ship[$tech_mat] = $user_ship[$tech_mat] + $user_ship[$free];
					}
					$taken += $free;
					take_cash($free*$cost_mat);
				//player will run out of cash.
				} else {
					$ship_counter++;
					$t868 = $ships[$tech_mat] + floor($user[cash]/$cost_mat);
					dbn(__FILE__,__LINE__,"update ${db_name}_ships set ".$tech_mat." = '$t868' where ship_id = '$ships[ship_id]'");
					dbn(__FILE__,__LINE__,"update ${db_name}_ports set ".$tech_mat." = ".$tech_mat." - ('$t868' - ".$ships[$tech_mat].") where port_id = '$port[port_id]'");
					if($ships[ship_id] == $user_ship[ship_id]){
						$user_ship[$tech_mat] = $t868;
					}
					$taken += $t868 - $ships[$tech_mat];
					$q_m = ($t868 - $ships[$tech_mat]) *$cost_mat;
					$text .= "<br /><b class=b1>$ships[ship_name]</b>'s $text_mat was increased to <b>$t868</b>.";
					take_cash($q_m);
					break;
				}
			}
		if($ship_counter > 0){
			$cost=$taken*$cost_mat;
			print_page("$text_mat Loaded","<b>$ship_counter</b> ships had their Cargo Bays filled with $text_mat from Sol.<br />Total Purchased $text_mat = <b>$taken</b>; Cost = <b>$cost</b><p>More Detailed Statistics :".$text);
		} else {
			print_page("No Ships","No ships where loaded as all ships in this system are already full of Cargo.");
		}
	}
}


#user wants to sell all
if(isset($sell_all)) {
	$elect_sold = 0;
	$fuel_sold = 0;
	$metal_sold = 0;
	$organ_sold = 0;

	if(isset($all_ships)) {#all being sold from all ships
		$sold_worth = 0;
		$ship_count = 0;
		db(__FILE__,__LINE__,"select elect,fuel,metal,organ,ship_id from ${db_name}_ships where location = $user[location] and login_id = $user[login_id]");
		while ($current_ship = dbr()) {
			$sold_worth += (($current_ship[elect] * $elect_sell) + ($current_ship[fuel] * $fuel_sell) + ($current_ship[metal] * $metal_sell) + ($current_ship[organ] * $organ_sell));
			$elect_sold = $elect_sold + $current_ship[elect];
			$fuel_sold = $fuel_sold + $current_ship[fuel];
			$metal_sold = $metal_sold + $current_ship[metal];
			$organ_sold = $organ_sold + $current_ship[organ];
			if($current_ship[elect] || $current_ship[metal] || $current_ship[organ] || $current_ship[fuel]) {
				$ship_count++;
			}
		}
		$turn_cost = min(5, $ship_count);
		if ($sold_worth < 1) {
			print_page("Port","You do not have any cargo in any ships in this star system.");
		} elseif ($user[turns] < $turn_cost) {
			print_page("Port","You do not have enough turns to trade using this method. You will have to sell everything manually.");
		} elseif($sure != 'yes') {
			get_var('Sell all cargo',$filename,"Are you sure you want to sell all cargo from all your ships currently in this star system with cargo (<b>$ship_count</b> of them)?<p>This will cost you <b>$turn_cost</b> turns and will generate revenues of about <b>$sold_worth</b> Credits.",'sure','yes');
		} else {
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set elect = 0, metal = 0, fuel = 0, organ = 0 where location = '$user[location]' && login_id = '$user[login_id]' && cargo_bays > 0");
			dbn(__FILE__,__LINE__,"update ${db_name}_ports set elect = elect + '$elect_sold', metal = metal + '$metal_sold', fuel = fuel + '$fuel_sold', organ = organ + '$organ_sold' where port_id = '$port[port_id]'");
			charge_turns($turn_cost);
			$text .= "All cargo from all ships in this star system sold.";
			$text .= "<p>Metal Sold: <b>$metal_sold</b><br />Fuel Sold: <b>$fuel_sold</b><br />Electronics Sold: <b>$elect_sold</b><br />Organics Sold: <b>$organ_sold</b>";
			$text .= "<p>Total goods sold: <b>";
			$total_goods = $metal_sold + $fuel_sold + $elect_sold + $organ_sold;
			$text .= "$total_goods</b>";
			$text .= "<br />Total income: <b>$sold_worth</b>.";
			$text .= "<br />From <b>$ship_count</b> ship(s).<p>";
		}

	} else { #all being sold from just the present ship.
		$sold_worth = (($user_ship[elect] * $elect_sell) + ($user_ship[fuel] * $fuel_sell) + ($user_ship[metal] * $metal_sell) + ($user_ship[organ] * $organ_sell));
		if ($user[turns] < 1) {
			print_page("Port","You do not have enough turns to trade using this method. You will have to sell everything manually.");
		} elseif ($sold_worth < 1) {
			print_page("Port","This ship has no cargo that can be sold. Try a selling from a different ship.");
		} elseif($sure != 'yes') {
			get_var('Sell all cargo',$filename,"Are you sure you want to sell all cargo from this ship?<p>This will cost you <b>1</b> turn, and add <b>$sold_worth</b> Credits to your funds.",'sure','yes');
		} else {
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set elect = 0, metal = 0, fuel = 0, organ = 0 where ship_id = '$user[ship_id]'");
			$elect_sold = $elect_sold + $user_ship[elect];
			$fuel_sold = $fuel_sold + $user_ship[fuel];
			$metal_sold = $metal_sold + $user_ship[metal];
			$organ_sold = $organ_sold + $user_ship[organ];
#			if ($user_ship[metal] > 0) { $text .= "You sold $user_ship[metal] units of metal.<p>"; }
#			if ($user_ship[fuel] > 0) { $text .= "You sold $user_ship[fuel] units of fuel.<p>";
#			if ($user_ship[elect] > 0) { $text .= "You sold $user_ship[elect] units of electronics.<p>";
	 		charge_turns(1);
			$text .= "All cargo from this ship sold.<p>";
			$text .= "<p>Metal Sold: <b>$metal_sold</b><br />Fuel Sold: <b>$fuel_sold</b><br />Electronics Sold: <b>$elect_sold</b><br />Organics Sold: <b>$organ_sold</b>";
			$text .= "<p>Total goods sold: <b>";
			$total_goods = $metal_sold + $fuel_sold + $elect_sold + $organ_sold;
			$text .= "$total_goods</b>";
			$text .= "<br />Total income: <b>$sold_worth</b>.<p>";
		}
	}
	dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + $sold_worth where login_id = $user[login_id]");
	$user[cash] += $sold_worth;
	$user_ship[metal] = 0;
	$user_ship[fuel] = 0;
	$user_ship[elect] = 0;
	$user_ship[organ] = 0;
#	$user_ship[colon] = 0;
}


empty_bays();

// print page
$text .= "<table cellspacing=0 cellpadding=2 width=100% align=center><tr><td width=200>";
$text .= "<tr><td>";

$text .= "<tr><td><p><p><p><b class=b1>Metal</b>";
if($alternate_play_1 == 0) { #can't buy metal in this style of play.
	$text .= "<br /><a href=port.php?metal=0>Buy</a> - <b>$metal_buy";
}
$text .= "<br /><a href=port.php?metal=1>Sell</a> - <b>$metal_sell</b>";

$text .= "<p><b class=b1>Fuel</b>";

if($alternate_play_1 == 0) { #can't buy fuel in this style of play.
	$text .= "<br /><a href=port.php?fuel=0>Buy</a> - <b>$fuel_buy";
}
$text .= "<br /><a href=port.php?fuel=1>Sell</a> - <b>$fuel_sell</b>";

$text .= "<p><b class=b1>Electronics</b>";
$text .= "<br /><a href=port.php?elect=0>Buy</a> - <b>$elect_buy";
$text .= "<br /><a href=port.php?elect=1>Sell</a> - <b>$elect_sell</b>";

$text .= "<p><b class=b1>Organics</b>";
$text .= "<br /><a href=port.php?organ=0>Buy</a> - <b>$organ_buy";
$text .= "<br /><a href=port.php?organ=1>Sell</a> - <b>$organ_sell</b>";

$text .= "<p><b class=b1>Colonists</b>";
$text .= "<br /><a href=port.php?colon=0>Buy</a> - <b>$colon_buy";

$text .= "</td>";

$text .= "<td>";
//Starport image - to be updated
$text .= "<img src=images/starport.jpg></img>";
$text .= "<p><a href=port.php?sell_all=1>Sell All</a> / ";
$text .= "<a href=port.php?sell_all=1&all_ships=1>Sell All from All Ships</a>";
$text .= "<br><br /><b>Bilkos Auction House</b> - <a href=dock_bilkos.php>Enter</a>";
$text .= "<br><b>Bounty Store</b> -<!--<a href=dock_bounty.php>Enter</a>-->Closed";
$text .= "<br><b>Equipment Shop</b> - <a href=dock_equip_shop.php>Enter</a>";
//$text .= "<br>Upgrade Station - Closed for business";

$text .= "</tr></td>";



$text .= "</td><td valign=top>";

/*if($flag_trading == 1) {
	$text .= "<blockquote><p><p>"
	."<b>Current Indices:</b><p><p><b class=red_txt>Fuel:</b>  <b>$indices[fuel]</b><p><b class=red_txt>Metal:</b>  <b>$indices[metal]</b><p><b class=red_txt>Electronics:</b>  <b>$indices[elect]</b><p><b class=red_txt>Organics:</b>  <b>$indices[organ]</b><p><i>Indices alter each day, and effect the overall price of material. As well as trading, holding material until the Index improves will also yield a profit.</i>"
	."</blockquote>";
}*/


$text .= "</td></tr></table>";

//$text .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

$rs = "<p><a href=location.php>Back to the Star System</a><br />";

print_page("Port",$text);
?>
