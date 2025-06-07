<?php
/*
//
File:			bm_upgrades.php
Objective:		File to handle upgrade purchases from a blackmarket
Version:		2.1.30
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	22 August 2002		Date Modified:	18 March 2003

Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

// NOTE: edit all buy blocks for number of possible upgrades - NB

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

$filename = "bm_upgrades.php";

sudden_death_check($user);

$amount = round($amount);
settype($amount, "integer");

$rs = "";
if($from_0 == 1){
 	$rs = "<p><a href=\"black_market.php?bmrkt_id=$bmrkt_id\">Return to Blackmarket</a></p>";
}
$rs .= "<p><a href=\"location.php\">Close Contact</a></p>";
$return_link = "<a href=\"$filename?bmrkt_id=$bmrkt_id\">Return to Blackmarket</a>";


db(__FILE__,__LINE__,"select * from ${db_name}_bmrkt where location = '$user[location]' && (bmrkt_type = 2 || bmrkt_type = 0) && bmrkt_id = '$bmrkt_id'");
$bmrkt = dbr();

if (empty($bmrkt))
{
	print_page("Blackmarket","<p>You may not contact a blackmarket that is not in the same system as you are in. Stop playing with the URL's.</p>");
}
elseif($user['ship_id'] ==1 && $user['login_id'] !=1)
{
	print_page("Error","<p>The local Pirates who operate this service have refused you entry. How can you be a Captain with no ship!!!</p>");
}
elseif($flag_research != 1)
{
	print_page("Error","<p>Admin in his/her near infinite wisdom has disabled the Blackmarket.</p>");
}

db(__FILE__,__LINE__,"select config from ${db_name}_ships where ship_id = '$user[ship_id]'");
$old_config = dbr();

//prices for tech support units/dark-matter at BMs
$dmatter_buy = $buy_darkmatter;
$dmatter_sell = $dmatter_buy - round(($dmatter_buy/100)*20);

$old_config = $user_ship['config'];

db(__FILE__,__LINE__,"select upgrades from ${db_name}_ships where ship_id = '$user[ship_id]'");
$upgrade_pods = dbr();



// checks what type of upgrade is being selected for purchase (Basic or Normal)
if(isset($buy))
{
	if($buy > 0 && $buy < 4)  // the three basic upgrades
	{
		$error_str .= Purchase_BasicUpgrade($buy);
	}
	elseif($buy > 3) //all other possible upgrades
	{
		$error_str .= Purchase_Upgrade($buy);
	}
}



//Dark-Matter Trading
if(isset($dmatter))
{
	if($amount < 1) //user to enter amount of Dark-Matter to deal in.
	{
		if($dmatter == 0)
		{
			$def = floor($user['cash'] / $dmatter_buy);
			if($def > $user_ship['empty_bays'])
			{
				$def = $user_ship['empty_bays'];
			}
			if ($user_ship['empty_bays'] > 0)
			{
				get_var("Buy Dark-Matter",$filename,"<p>How much Dark-Matter do you want to buy?</p>","amount",$def);
			}
			else
			{
				$error_str .= "<p>You have no cargo capacity left on this ship. As such you cannot buy anything.</p>";
			}
		}
		elseif ($user_ship['darkmatter'])
		{
			get_var("Sell Dark-Matter",$filename,"How much Dark-Matter do you want to sell?","amount",$user_ship['darkmatter']);
		}
		else
		{
			$error_str .= "<p>You have no Dark-Matter to sell.</p>";
		}
	}
	else //amount to deal in has been set
	{
		if($dmatter == 0) //Dark-Matter buy
		{
			if(($amount * $dmatter_buy > $user['cash']) && $user['login_id'] != 1)
			{
				$error_str .= "<p>You cannot afford that much Dark-Matter.</p>";
			}
			elseif($amount > $user_ship['empty_bays'])
			{
				$error_str .= "<p>Your ship can not hold that much Dark-Matter.</p>";
			}
			else
			{
				dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash - $amount * $dmatter_buy where login_id = $user[login_id] && login_id != 1");
				if($login_id != 1){$user[cash] -= $amount * $dmatter_buy;}
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set darkmatter = darkmatter + $amount where ship_id = $user[ship_id]");
				$user_ship['darkmatter'] += $amount;
				charge_turns(1);
				$error_str .= "It took 1 Turn to purchase <b>$amount</b> units of Dark-Matter, for the sum of <b>".$amount*$dmatter_buy."</b> Credits.<p>";
			}
		}
		else //Dark-Matter sell
		{
			if($amount > $user_ship['darkmatter'])
			{
				$error_str .= "<p>You do not have that much Dark-Matter.</p>";
			}
			else
			{
				dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + $amount * $dmatter_sell where login_id = $user[login_id]");
				$user[cash] += $amount * $dmatter_sell;
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set darkmatter = darkmatter - $amount where ship_id = $user[ship_id]");
				$user_ship['darkmatter'] -= $amount;
				charge_turns(1);
				$error_str .= "<p>It took 1 Turn to sell <b>$amount</b> units of Dark-Matter, for the sum of <b>".$amount*$dmatter_sell."</b> Credits.</p>";
			}
		}
	}
}
empty_bays(); //corrects empty bay misstatement on buy

$error_str .= "<br />Welcome to the <b class=b1>Advanced and Alien Items Store</b> of this Blackmarket!";
$error_str .= "<br />This ship has <b>$upgrade_pods[0]</b> upgrade Pod(s) available.<br />Each upgrade will use one pod.<br />";
$error_str .= "<br />Now listen up, Captain! Once brought, an <b class=b1>Advanced or Alien Item</b> cannot be sold on. This is a Blackmarket. You commit arbitrage and I'll make you walk the plank! Got me a nice one hidden in a cargo bay on Sol Star Port. You have any idea what genuine Earth Wood costs?<br />";

	// weapons
	$error_str .= "<h3>Weapons:</h3>";
		$upg_text = Fetch_UpgradePurchaseLines(6,2);
	$error_str .= $upg_text['txt'];

	// defences
	$error_str .= "<h3>Defences:</h3>";
		$upg_text = Fetch_UpgradePurchaseLines(7,2);
	$error_str .= $upg_text['txt'];

	// propulsion
	$error_str .= "<h3>Propulsion Upgrades:</h3>";
		$upg_text = Fetch_UpgradePurchaseLines(4,2);
	$error_str .= $upg_text['txt'];

if($visibility_flag == 1) {
	// scanners
	$error_str .= "<h3>Scanners:</h3>";
		$upg_text = Fetch_UpgradePurchaseLines(2,2);
	$error_str .= $upg_text['txt'];

	//cloaking devices
	$error_str .= "<h3>Cloaking Devices:</h3>";
		$upg_text = Fetch_UpgradePurchaseLines(3,2);
	$error_str .= $upg_text['txt'];
}
	// miscellaneous
	$error_str .= "<h3>Miscellaneous:</h3>";
		$upg_text = Fetch_UpgradePurchaseLines(5,2);
	$error_str .= $upg_text['txt'];

// It would be nice to use buy_darkmatter == 0 as an indication that you cannot
// buy/sell dark matter, but buy_darkmatter's min is currently 1, so use that instead.
if($flag_dmatter > 0 && $dmatter_buy > 1)
{
	$error_str .= "<h3>Dark-Matter:</h3>";
	$error_str .= "<a href=\"bm_upgrades.php?bmrkt_id=$bmrkt_id&amp;dmatter=0\">Buy</a> - <b>$dmatter_buy</b><br />";
	$error_str .= "<a href=\"bm_upgrades.php?bmrkt_id=$bmrkt_id&amp;dmatter=1\">Sell</a> - <b>$dmatter_sell</b><br />";
}

$error_str .= "<br /><p><a href=\"help.php?research=1#advitems\" target=\"_blank\">Information about Blackmarket Advanced and Alien Items</a></p>";

print_page("Blackmarket Upgrades",$error_str);

?>
