<?php
/*
//
File:			hw_used_store.php
Objective:		Management of used ship/equipment store, selling/buying of used equipment.
Version:		QS 2.1.0			Prior: 2.0.6
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	7 February 2003		Date Modified:	27 January 2004

Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

include_once("includes/nocache.inc.php");

require_once("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));
require_once("includes/fleet_funcs.inc.php");

//$ADODB_FETCH_MODE = 2;

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

if($user['location'] == $user['race'] || $user['login_id'] == 1){
	$rs = "<br /><Br><a href=hw_used_store.php>Return to $home[used]</a>";
	$rs .= "<br><a href=hw.php>Return to $homeworld</a><br />";

} elseif($user['location'] != $user['race'] && $user['login_id'] != 1) {
	print_page("Wherever you are?","The Used Shop is located in (SS $user[race]), and you must be in that location to access $home[used]");
}


$text .="<br />Welcome to <b class=b1>$home[used]</b>!<br />";

$text .= "<br />Select a catogory to see items presently available. New lots arrive as players submit them. Due to our current state of renovations, we are only offering services to ship owners and producers of excess Tech. Units for the time being. Thank you for visiting $home[used].<br />";
db(__FILE__,__LINE__,"select count(item_id),item_type from ${db_name}_used where active = 1 and race = $user[race] group by item_type");
for($i=1;$i<=5;$i++){
	$count=dbr();
	if($count['item_type']){
		$out[$count['item_type']] = $count[0];
	}
}
for($i=1;$i<=5;$i++){
	if(!$out[$i]){
		$out[$i] = 0;
	}
}
$text .= "<br /><a href=hw_used_store.php?ship_view=1>Ships</a> - (<b>$out[1]</b>)";
$text .= "<br /><a href=hw_used_store.php?view=2>Tech. Units</a> - (<b>$out[2]</b>)";
// Other items yet to be enabled
//$text .= "<br /><a href=hw_used_store.php?view=3>Upgrades</a> - (<b>$out[3]</b>)";
//$text .= "<br /><a href=hw_used_store.php?view=4>Misc</a> - (<b>$out[4]</b>)";
//$text .= "<br /><a href=hw_used_store.php?view=5>Planetary</a> - (<b>$out[5]</b>)";

$text .= "<br /><br /><a href=hw_used_store.php?add_item=1>Add item for sale</a>";





// -----------------------------------------------------------------
// Enables player to alter the prices of ships or tech units.
// -----------------------------------------------------------------

if($alterp != 0) {
	if(isset($itemid)) {
		db(__FILE__,__LINE__,"select owner_id,price,ship_id from ${db_name}_used where item_id = '$itemid' and race = $user[race]");
		$item_info = dbr();
		if($item_info[owner_id] != $user[login_id]) {
			print_page("Nice Try","Perhaps you could try changing one of your own items rather than anothers? Eh?");
		}
		if(!isset($nw_price)) {
			$error_str .= "Enter a new price for this item:";
			$error_str .= "<form name=price_chng action=hw_used_store.php method=post>";
			$error_str .= "<input type=hidden name=alterp value=$alterp>";
			$error_str .= "<input type=hidden name=itemid value=$itemid>";
			$error_str .= "<input name=nw_price value=$item_info[price] size=7>";
			$error_str .= "<input type=submit value=Submit></form>";
			print_page("Change Price?",$error_str);
		} else {
			if($alterp == 1) {
				db(__FILE__,__LINE__,"select shipclass,fighters from ${db_name}_ships where ship_id = '$item_info[ship_id]'");
				$shp_dd = dbr();
					db(__FILE__,__LINE__,"select cost,fighters from ship_types where type_id = '$shp_dd[shipclass]'");
				$chk_cost = dbr();
				$base_cst = $chk_cost[cost] - ($chk_cost[fighters] * 100);	//cost of ship subtracting fighter cost
				$fghtr_cst = $shp_dd[fighters] * 100; //cost of fighters being added
				$chk_min = ($base_cst + $fghtr_cst) * 0.3; //min of 30% of joint default cost
				if($nw_price < $chk_min) {
					print_page("Minimum Price Breached","Your ship's price is restricted to a minimum level. This minimum is calculated as 30% of: the ship cost as per the ship listing (i.e. The listing cost minus the included fighters, e.g. Merchant Freighter = 10000 - 7000 = 3000 credits) plus the cost of all added fighters. The resulting minimum for your current ship is <b>$chk_min</b> Credits.<br /><p><a href=javascript:history.back()>Try Again</a>");
				}
			}
			dbn(__FILE__,__LINE__,"update ${db_name}_used set price = ".$nw_price." where item_id = ".$itemid);
			$error_str .= "Price has been changed!";
			print_page("Price Changed",$error_str);
		}
	} else {
		print_page("Error","Please choose a valid ship from the list!");
	}
}








// -----------------------------------------------------------------
// Enables mass purchasing of tech units
// -----------------------------------------------------------------

if($mass_tech) {

	//test if player is in sudden death with no ships - Sudden Death
	db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '$user[login_id]'");
	$numships = dbr();
	if(!$numships[0] && $sudden_death && $user[login_id] != 1 && $user[last_login] != 0) {
		print_page("Sudden Death","You have no ship, and this game is Sudden Death. As such you are out of the game. We look forward to seeing you again when the game resets. Thank you for playing Solar Empire.");
	}

	if(!$mass_list){
			print_page("Buy Tech","You must select at least one Tech group to purchase.");
	}
	$math = 0;
	$temp789 = $mass_list;
	while ($var = each($temp789)) {
		db(__FILE__,__LINE__,"select * from ${db_name}_used where item_id = '$var[value]' and race = $user[race]");
		$target_item = dbr(); //collect all info on target item
		$math++;
		if($target_item[owner_id] == $user[login_id]) {
		print_page("Purchase Tech","You cannot repurchase your own Tech. You may retrieve it using the options for Personal Stock on Sale");
		break;
		}
	}
	if($sure != "yes") {
		$text = "Are you sure you want to buy <b>$math</b> group(s) of Tech. Units?";
		$text .= "<form name=purch_tech action=hw_used_store.php method=post>";
		$temp987 = $mass_list;
		$i=0;
		while ($var = each($temp987)) {
			$text .= "<input type=hidden name=mass_list[$i] value='$var[value]'>";
			$i++;
		}
		$text .= '<input type=hidden name=mass_tech value=1>';
		$text .= '<input type=hidden name=sure value=yes>';
		$text .= '<input type=hidden name=math value=$math>';
		$text .= '<input type=submit name=submit value=Yes> <input type="Button" width="30" value="No" onclick="javascript: history.back()"> </form>';
		print_page("Sure?",$text);
	} else {
		$temp879 = $mass_list;//here is where we edit the ships pre-existing details for the new owner

		//add up all the individual prices for a total
		$cash_cost = 0;
		while ($var = each($temp879)) {
			db(__FILE__,__LINE__,"select price from ${db_name}_used where item_id = '$var[value]'");
			$itm_var = dbr();
			$cash_cost = $cash_cost + $itm_var[price];
		}

		//check user can afford total price
		if($user[cash] < $cash_cost) {
			print_page("Not enough credits!","You can't afford to purchase all of this Tech! You need more Credits to meet the total price of <b>$cash_cost</b> Credits.");
		}

		//transfer Tech to new owner, transfer cash less commission to prior owner
		$temp798 = $mass_list;
		while ($var = each($temp798)) {
			db(__FILE__,__LINE__,"select * from ${db_name}_used where item_id = '$var[value]'");
			$itm_buy = dbr();
			take_cash($itm_buy['price']);
			$itm_payment = $itm_buy['price'] * .95;
			dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + '$itm_payment' where login_id = '$itm_buy[owner_id]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_used set item_code = '$user[login_id]' where item_id = '$var[value]'");
			dbn(__FILE__,__LINE__,"update ${db_name}_users set tech = tech + '$itm_buy[units]' where login_id = '$user[login_id]'");
			$user['tech'] += $itm_buy['units'];
			$user['cash'] -= $itm_payment;
		}

		db2(__FILE__,__LINE__,"select distinct owner_id from ${db_name}_used where item_code = '$user[login_id]'");
		$mess_id = dbr2();

		while($mess_id) {
			db(__FILE__,__LINE__,"select sum(price) as tprice from ${db_name}_used where owner_id = '$mess_id[owner_id]' && item_code = '$user[login_id]'");
			$tch_csh = dbr();
			$csh_t = $tch_csh['tprice'] * .95;
			db(__FILE__,__LINE__,"select sum(units) from ${db_name}_used where owner_id = '$mess_id[owner_id]' && item_code = '$user[login_id]'");
			$tch_cnt = dbr();
			send_message($mess_id['owner_id'],addslashes("<b class=b1> $home[used]</b><br /><br />We are pleased to notify you of the sale of<b> $tch_cnt[0] </b>of your Tech. Units submitted to our store, by <b class=b1>$user[login_name]</b>. After charging commission of 5%, the total payable amount due to you is<b> $csh_t </b>Credits. These monies, if they have not yet arrived, will be credited to your account very soon.<br /><br />Thank you for dealing with $home[used]!"));
			$mess_id = dbr2();
		}
		dbn(__FILE__,__LINE__,"delete from ${db_name}_used where item_code = '$user[login_id]'");

		$text = "Tech. Units  have been successfully purchased!";
		$text .= "<br /><p>Your payment of <b>$cash_cost</b> Credits has been accepted, and your Tech. Units are transferred.<br />";
		$text .= "<p>Thank you for dealing with <b class=b1>$home[used]</b>.";
	}
}





// -----------------------------------------------------------------
// Enables mass purchasing of ships of a similar class from listings
// -----------------------------------------------------------------

if($mass_ship) {

	//test if player is in sudden death with no ships
	db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '$user[login_id]'");
	$numships = dbr();
	if(!$numships[0] && $sudden_death && $user[login_id] != 1 && $user[last_login] != 0) {
		print_page("Sudden Death","You have no ship, and this game is Sudden Death. As such you are out of the game. We look forward to seeing you again when the game resets. Thank you for playing Quantum-Star SE.");
	}
	db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '$user[login_id]'");
	$numships = dbr();
	if(!$mass_list){
		print_page("Buy Ships","You must select at least one ship to purchase.");
	}

	$math = 0;
	$temp789 = $mass_list;
	while ($var = each($temp789)) {
		db(__FILE__,__LINE__,"select * from ${db_name}_used where item_id = '$var[value]' and race = $user[race]");
		$target_item = dbr(); //collect all info on target item
		db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$target_item[ship_id]'");
		$target_ship = dbr(); //collect all info on target ship
		$math++;
		if($target_ship['login_id'] == $user['login_id']) {
		print_page("Add Ship","You cannot repurchase your own ships. You may only wait for a sale by another or accept a scrap offering (15% of price). Please see HELP for a more detailed explanation.");
		break;
		} elseif($user['ship_id'] == $var['value']) {
		print_page("Add Ship","You may not purchase the ship that you are commanding!"); //This is too much...lol
		break;
		}
	}
	if ($numships[0]+$math > $max_ships) { //check to ensure they are not trying to buy too many ships
		$error_str = "You already own <b>$numships[0]</b> ship(s). The Admin has set the max number of ships players may have to <b>$max_ships</b>.";
		print_page("Ship Limit Reached",$error_str);
	}
	if (!isset($original_num)) {
		$original_num = $math;
		$num_purchased = 0;
	}
	$sure = "Yes";
	if(!$sure) {
		$text .= "<form name=purch_ships action=hw_used_store.php method=post>";
		$temp987 = $mass_list;
		$i=0;
		unset($cash_cost);
		while ($var = each($temp987)) {
			$text .= "<input type=hidden name=mass_list[$i] value='$var[value]'>";
			$i++;
			db(__FILE__,__LINE__,"select * from ${db_name}_used where item_id = '$var[value]'");
			$itm_var = dbr();
			$cash_cost += $itm_var['price'];
		}
		$text = "Are you sure you want to buy <b>$math</b> ship(s) for a combined total of <b>$cash_cost</b> Credits?";
		$text .= '<input type=hidden name=mass_ship value=1>';
		$text .= '<input type=hidden name=sure value=Yes>';
		$text .= '<input type=hidden name=math value=$math>';
		$text .= "<input type=hidden name=original_num value=\"$original_num\">";
		$text .= "<input type=hidden name=num_purchased value=\"$num_purchased\">";
		$text .= '<input type=submit name=submit value=Yes> <input type="Button" width="30" value="No" onclick="javascript: history.back()"> </form>';
		print_page("Sure?",$text);
	}elseif(!isset($ship_name)) {
		$text = "Are you sure you want to buy <b>$math</b> ship(s) for a combined total of <b>$cash_cost</b> Credits?";
		$text = "Please enter a name for your new ships. $home[used] wishes to apologise as this name will be applied to all vessels regardless of type.<br /><br />When naming your new ships they will be given a number after the name you have entered. (3-25 Characters)";
		$text .= "<form name=ship_naming action=hw_used_store.php method=post>";
		$temp897 = $mass_list;
		$i=0;
		while ($var = each($temp897)) {
			$text .= "<input type=hidden name=mass_list[$i] value='$var[value]'>";
			$i++;
		}
		$text .= "<input type=hidden name=mass_ship value=1>";
		$text .= "<input type=hidden name=sure value=yes>";
		$text .= '<input type=hidden name=math value=$math>';
		$text .= "<input type=hidden name=original_num value=\"$original_num\">";
		$text .= "<input type=hidden name=num_purchased value=\"$num_purchased\">";
		$text .= "<input type=hidden name=descr value=$descr>";
		$text .= "<input name=ship_name size=25>";
		$text .= "<input type=submit value=Submit></form>";
		$text .= "<script>document.ship_naming.ship_name.focus();</script>";
		print_page("Sure?",$text);
	} elseif (strlen($ship_name) < 3) {
		$rs .= "<p><a href=javascript:history.back()>Try Again</a>";
		print_page("Error","Ship name must be at least three characters.");
	} else {
		//here is where we edit the ships pre-existing details for the new owner

		//add up all the individual prices for a total
		unset($cash_cost);
		$temp879 = $mass_list;
		while ($var = each($temp879)) {
			db(__FILE__,__LINE__,"select * from ${db_name}_used where item_id = '$var[value]'");
			$itm_var = dbr();
			$cash_cost += $itm_var['price'];
		}
		//check user can afford total price
		if($user['cash'] < $cash_cost) {
			print_page("Not Enough Credits!","You can't afford to purchase all of these ships! You need more Credits to meet the total price of <b>$cash_cost</b> Credits.");
		}

		// remove special characters, e.g. apostrophes, from name
		$ship_name = correct_name($ship_name);
		$ship_name = addslashes($ship_name);

		//transfer ship to new owner, transfer cash less commission to prior owner
		$temp798 = $mass_list;
		$array_offset = 0;
		while ($var = each($temp798)) {

			$ship_name_sql = $original_num > 1 ? sprintf( "%s %02u", $ship_name, $num_purchased + 1 ) : $ship_name;

			db(__FILE__,__LINE__,"select * from ${db_name}_used where item_id = '$var[value]'");
			$itm_buy = dbr();

			db(__FILE__,__LINE__,"select ship_categ from ${db_name}_ships where ship_id = '$itm_buy[ship_id]'");
			$categ = dbr();

///			echo(" GGG->>".$fleetnum);

			if(isset($fleetnum)) {
				unset($user_fleet);
				$user_fleet = Check_ToCreateFleet($fleetnum);
				unset($fleetnum);
			}
			Check_FleetMax(); // check fleet max/war max are not passed

			$array_offset++; //move offset pointer forward by 1 (used to determine split off point in event a fleet must be created to hold additional ships beyond the current fleet limit.

			take_cash($itm_buy['price']);
			$itm_payment = round($itm_buy['price'] * .95);
			$cash_total += $itm_buy['price'];

			// pay the seller
			dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + '$itm_payment' where login_id = '$itm_buy[owner_id]'");
			db(__FILE__,__LINE__,"select class_name from ${db_name}_ships where ship_id = '$itm_buy[ship_id]'");
			$class_n = dbr();
			send_message($itm_buy['owner_id'],addslashes("<b class=b1>$home[used]</b><br />We are pleased to notify you of the sale of a ".$class_n['class_name']." submitted to our store. After 5% commission, the amount paid to you is <b>".round($itm_buy['price'] * .95)."</b> Credits. Thank you for dealing with $home[used]!"));

			// remove users escape pod, if any
			dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where login_id = '$user[login_id]' && class_name REGEXP 'Escape'");

			//update the ships owner info
			$q_string = "update {$db_name}_ships set login_id = '$user[login_id]', login_name = '$user[login_name]', clan_id = '$user[clan_id]', location = '$user[location]', ship_name = '$ship_name_sql', fleet_id = '$user_fleet[fleet_id]' where ship_id = '$itm_buy[ship_id]'";
			dbn(__FILE__,__LINE__,$q_string);

			// removal of item from the sale list
			dbn(__FILE__,__LINE__,"delete from ${db_name}_used where item_id = '$var[value]'");

			//puts the user into the newest ship
			dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$itm_buy[ship_id]' where login_id = '".$user['login_id']."'");
			$user['ship_id'] = $itm_buy['ship_id'];
			db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
			$user_ship = dbr();
			empty_bays();
			$num_purchased++;
		}
		$text = "<p>Used ships have been successfully purchased!</p><p>Your payment of <b>$cash_total</b> Credits has been accepted, and your ships now await you in $homeworld orbit.</p><p>Thank you for dealing with <b class=b1>$home[used]</b>.</p>";
	}
}




// -----------------------------------------------------------------
// mass scrapping, i.e. Finbarr's pays 20% of minimum sale price to
// player if needed, perhaps if player really needs cash quickly.
// This is really a last resort!
// -----------------------------------------------------------------

if($mass_ship_scrap) {

	//test if player is in sudden death with no ships
	db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '$user[login_id]'");
	$numships = dbr();
	if(!$numships[0] && $sudden_death && $user[login_id] != 1 && $user[last_login] != 0) {
		print_page("Sudden Death","You have no ship, and this game is Sudden Death. As such you are out of the game. We look forward to seeing you again when the game resets. Thank you for playing Solar Empire.");
	}

	if(!$mass_scrap){
			print_page("Scrap Ships","You must select at least one ship to avail of $home[used] Scrap Fee option.");
	}
	$math = 0;
	$temp789 = $mass_scrap;
	while ($var = each($temp789)) {
		db(__FILE__,__LINE__,"select * from ${db_name}_used where item_id = '$var[value]'");
		$target_item = dbr(); //collect all info on target item
		db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$target_item[ship_id]'");
		$target_ship = dbr(); //collect all info on target ship
		$math++;
		if($target_item['owner_id'] != $user['login_id']) {
		print_page("Scrap Ship","You cannot scrap a ship you don't own! Choose your own ship.");
		break;
		} elseif($user['ship_id'] == $var['value']) {
		print_page("Scrap Ship","You may not scrap the ship that you are commanding!"); //This is too much...lol
		break;
		}
	}
	if($sure != "yes") {
		$temp879 = $mass_scrap;
		unset($cash_cost);
		while ($var = each($temp879)) {
			db(__FILE__,__LINE__,"select price,ship_id from ${db_name}_used where item_id = '$var[value]'");
			$itm_var = dbr();
			db(__FILE__,__LINE__,"select fighters,ship_id,shipclass from ${db_name}_ships where ship_id = '$itm_var[ship_id]'");
			$shp_add = dbr();
			db(__FILE__,__LINE__,"select cost,fighters from ship_types where type_id = '$shp_add[shipclass]'");
			$chk_cost = dbr();
			$base_cst = $chk_cost[cost] - ($chk_cost[fighters] * 100);	//cost of ship subtracting fighter cost
			$fghtr_cst = $shp_add[fighters] * 100; //cost of fighters being added
			$chk_min = ($base_cst + $fghtr_cst) * 0.3; //min of 30% of joint default cost
			$cash_cost = $cash_cost + $chk_min;
		}
		$scrap_proceeds = $cash_cost * 0.2;
		$text = "<b class=b1>$home[used] Scrap Fee</b> will provide you with 20% of the minimum price for the ships you have chosen. e.g. if minimum price for a HM is 15,000 - you would get a 3000 credit Scrap Fee from $home[used].<br /><br />The total scrap proceeds from your <b>$math</b> ships will be <b>$scrap_proceeds</b> Credits. Are you sure you want to scrap these <b>$math</b> ship(s)?";
		$text .= "<form name=scrap_ships action=hw_used_store.php method=post>";
		$temp987 = $mass_scrap;
		$i=0;
		while ($var = each($temp987)) {
			$text .= "<input type=hidden name=mass_scrap[$i] value='$var[value]'>";
			$i++;
		}
		$text .= '<input type=hidden name=mass_ship_scrap value=1>';
		$text .= '<input type=hidden name=sure value=yes>';
		$text .= '<input type=hidden name=math value=$math>';
		$text .= "<input type=hidden name=scrap_proceeds value='$scrap_proceeds'>";
		$text .= '<input type=submit name=submit value=Yes> <input type="Button" width="30" value="No" onclick="javascript: history.back()"> </form>';
		print_page("Sure?",$text);
	} else {
		$temp879 = $mass_scrap;//here is where we edit the ships pre-existing details for the new owner

		//delete scrapped ships
		while ($var = each($temp879)) {
			db(__FILE__,__LINE__,"select price,ship_id from ${db_name}_used where item_id = '$var[value]'");
			$itm_var = dbr();
			db(__FILE__,__LINE__,"select fighters,ship_id,shipclass from ${db_name}_ships where ship_id = '$itm_var[ship_id]'");
			$shp_add = dbr();
			dbn(__FILE__,__LINE__,"delete from ${db_name}_used where item_id = '$var[value]'");
			dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where ship_id = '$shp_add[ship_id]'");
		}
		give_cash($scrap_proceeds);
		$text = "Used ships have been successfully scrapped!";
		$text .= "<br /><p>The total proceeds of <b>$scrap_proceeds</b> Credits has been paid to your account. Your ship will be recycled for metal and electronics.<br />";
		$text .= "<p>Thank you for dealing with <b class=b1>$home[used]</b>.";
	}
}



// -----------------------------------------------------------------
// allows player to retrieve their tech units. unlike ships, there is
// no ban on retrieving tech units
// -----------------------------------------------------------------

if($mass_tech_retrieve) {

	//test if player is in sudden death with no ships
	db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '$user[login_id]'");
	$numships = dbr();
	if(!$numships[0] && $sudden_death && $user[login_id] != 1 && $user[last_login] != 0) {
		print_page("Sudden Death","You have no ship, and this game is Sudden Death. As such you are out of the game. We look forward to seeing you again when the game resets. Thank you for playing Solar Empire.");
	}

	if(!$mass_retrieval){
			print_page("Retrieve Tech. Units","You must select at least one Tech. group to avail of $home[used] Retrieval option.");
	}
	$math = 0;
	$temp789 = $mass_retrieval;
	while ($var = each($temp789)) {
		db(__FILE__,__LINE__,"select * from ${db_name}_used where item_id = '$var[value]'");
		$target_item = dbr(); //collect all info on target item
		$math++;
		if($target_item[owner_id] != $user[login_id]) {
		print_page("Retrieve Tech. Units","You cannot retrieve Tech. Units you do not own.");
		break;
		}
	}
	if($sure != "yes") {
		$text = "$home[used] allows the retrieval of Tech. Units you have put on sale. Are you sure you want to retrieve these <b>$math</b> group(s) of Tech. Units?";
		$text .= "<form name=retrieve_units action=hw_used_store.php method=post>";
		$temp987 = $mass_retrieval;
		$i=0;
		while ($var = each($temp987)) {
			$text .= "<input type=hidden name=mass_retrieval[$i] value='$var[value]'>";
			$i++;
		}
		$text .= '<input type=hidden name=mass_tech_retrieve value=1>';
		$text .= '<input type=hidden name=sure value=yes>';
		$text .= '<input type=hidden name=math value=$math>';
		$text .= '<input type=submit name=submit value=Yes> <input type="Button" width="30" value="No" onclick="javascript: history.back()"> </form>';
		print_page("Sure?",$text);
	} else {
		$temp879 = $mass_retrieval;

		//return tech units to player and delete entry in Finbarr's
		unset($tech_units);
		while ($var = each($temp879)) {
			db(__FILE__,__LINE__,"select units from ${db_name}_used where item_id = '$var[value]'");
			$itm_var = dbr();
			$tech_units += $itm_var[units];
			dbn(__FILE__,__LINE__,"delete from ${db_name}_used where item_id = '$var[value]'");
		}
		give_tech($tech_units);
		$text = "Tech. Units have been successfully retrieved!";
		$text .= "<br /><p>Your retrieved amount of <b>$tech_units</b> Tech. Units have been transferred back to you.<br />";
		$text .= "<p>Thank you for dealing with <b class=b1>$home[used]</b>.";
	}
}


// -----------------------------------------------------------------
// ship view creates a break down of all ships at Finbarr's into
// categories based on ship class. allows players to skip ship types
// they don't want in unlike the previous listing
// -----------------------------------------------------------------
if($ship_view == 1) {
	$text ="<br /><b class=b1>$home[used]</b><br />";
	$text .= "<br />Welcome to the Ship Division. The number of ships per Ship Class is available below. We would like you to note that that only Classes containing ships are displayed. Those not displayed simply contain no used ships at present. Credit Disk at the ready?<br />";
	// select all ship_class ids - distinct function only provides 1 id per class rather than an id for each ship!
	db2(__FILE__,__LINE__,"select distinct ship_type from ${db_name}_used where active = 1 && ship_type > 2 order by ship_type");
	$shp_types = dbr2();
	db(__FILE__,__LINE__,"select count(item_id) from ${db_name}_used where ship_type > 2 and race = $user[race]");
	$num = dbr();
	if($num[0] > 0) {
		// build the menu
		while($shp_types) {
			db(__FILE__,__LINE__,"select count(item_id),item_name,ship_type from ${db_name}_used where active = 1 && ship_type = '$shp_types[0]' and race = $user[race] group by ship_type");
			$shp_num = dbr();
			db(__FILE__,__LINE__,"select item_name from ${db_name}_used where active = 1 && ship_type = '$shp_types[0]' and race = $user[race] limit 1");
			$shp_inf = dbr();
			if($shp_num[0] > 0) {
				$text .= "<br /><a href=hw_used_store.php?view=1&class_id=$shp_num[ship_type]>$shp_inf[item_name]</a></b> - (<b>$shp_num[0]</b>)";
			}
			$shp_types = dbr2();
		}
	} else {
		$text .= "<br />There are no ships currently available. You may try at a later date.";
	$text .= "<br /><br /><a href=hw_used_store.php?add_item=1>Add item for sale</a>";

	}

}

// -----------------------------------------------------------------
// this creates the tech units listing, but also a separated listing
// of each individual ship class. Does not list classes with no ships
// Also separates out items entered by the player, so they can change
// prices, scrap ships or retrieve tech units
// -----------------------------------------------------------------

if($view){ //Show all items in a particular catagory.
	$text .= "<br /><br /><b><u>Current Stock:</b></u><p>";
	if($view == 1) { //ships
		if(!isset($class_id)) {
			print_page("Error","You must choose a ship class to view from the Ship menu! No url short-cutting!");
		}
		db2(__FILE__,__LINE__,"select item_name,config,timestamp,price,owner_id,item_id,ship_id from ${db_name}_used where race = $user[race] && item_type = $view && ship_type = $class_id && active=1 order by timestamp asc, owner_id desc");
		$typo = "Config";
		$typo2 = "config";
		$text .= "<form method=post action=hw_used_store.php name=buy_used><input type=hidden name=mass_ship value=1>";
		$typo3 = make_table(array("Item Name","$typo","Date Added","Current Price","Sold by", "Fighters", "Cargo Bays","Purchase"));
		$typo5 = "<br /><b><u>Personal Stock on Sale:</b></u><br /><br />Personal Stock you have on sale can avail of $home[used] Scrap Scheme whereby you will be paid 20% of each ships minimum allowable sale price. This option yields a far lower profit than selling to another player but is a method of gaining some quick cash in an emergency or otherwise. This is entirely optional. You may also alter the selling price of your ships.<br /><form method=post action=hw_used_store.php name=scrap_used><input type=hidden name=mass_ship_scrap value=1>".make_table(array("Item Name","$typo","Date Added","Current Price","Sold by", "Fighters", "Cargo Bays","Scrap"));
	}elseif($view == 2) { //tech units
		db2(__FILE__,__LINE__,"select item_name,units,timestamp,price,owner_id,item_id from ${db_name}_used where race = $user[race] && item_type = $view && active=1 order by timestamp asc, item_name asc");
		$typo = "Units Available";
		$typo2 = "units";
		$text .= "<form method=post action=hw_used_store.php name=buy_used><input type=hidden name=mass_tech value=1>";
		$typo3 = make_table(array("Item Name","$typo","Date Added","Current Price","Sold by","Purchase"));
		$typo5 = "<br /><b><u>Personal Stock on Sale:</b></u><br /><br />Personal Stock of Tech. Units you have on sale may be retrieved at will. Unlike ships, there is no maximum on the amount you may hold. You may also alter the selling price of your Tech. Units.<br /><form method=post action=hw_used_store.php name=retrieve_used><input type=hidden name=mass_tech_retrieve value=1>".make_table(array("Item Name","$typo","Date Added","Current Price","Sold by","Retrieve"));
	}
	$items=dbr2();

	if(!$items){
		$text .= "None at present - Perhaps you could try later?";
	} else {
		$text .= $typo3;
		while($items) {
			db(__FILE__,__LINE__,"select ship_id from ${db_name}_used where item_id = '$items[item_id]'");
			$s_id = dbr();
			db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$s_id[ship_id]'");
			$s_inf = dbr();
			$items_time = date( "M d - H:i",$items['timestamp']);
			if($items['owner_id'] > 0){
				db(__FILE__,__LINE__,"select login_name,login_id,clan_sym,clan_sym_color from ${db_name}_users where login_id = $items[owner_id]");
				$owner=dbr();
				$items_own = print_name($owner);
			}
			$items[$typo] = "<div align=\"center\">$items[$typo]</div>";
			$items['price'] = number_format($items['price']);
			$items_pur = "<div align=\"center\"><input type=checkbox name=mass_list[$items[item_id]] value=$items[item_id]></div>";
			if($view == 1) { //ships
				if($items['owner_id'] != $user['login_id']) {
					$typo4 = make_row(array("<b class=b1>$items[item_name]</b>","$items[$typo2]","$items_time","$items[price]","$items_own","$s_inf[fighters]","$s_inf[cargo_bays]","$items_pur"));
				}
			}elseif($view == 2) { //tech units
				if($items['owner_id'] != $user['login_id']) {
					$typo4 = make_row(array("$items[item_name]","$items[$typo2]","$items_time","$items[price]","$items_own","$items_pur"));
				}
			}
			$text .= $typo4;
			$items = dbr2();
		}
		if($view == 1) {
			$text .= "</table>";
			$text .= "<br /><a href=javascript:TickAll(\"buy_used\")>Invert Ship Selection</a> - ";
			$text .= "<input type=submit value='Buy Ships'></form><p><br />";
		} else {
			$text .= "</table>";
			$text .= "<br /><a href=javascript:TickAll(\"buy_used\")>Invert Tech Selection</a> - ";
			$text .= "<input type=submit value='Buy Tech'></form><p><br />";
		}

		// Section to separate players own items from those on sale by others
		$text .= $typo5;
		if($view == 1) { //ships
			if(!isset($class_id)) {
				print_page("Error","You must choose a ship class to view from the Ship menu! No url short-cutting!");
			}
			db2(__FILE__,__LINE__,"select item_name,config,timestamp,price,owner_id,item_id,ship_id from ${db_name}_used where item_type = $view && ship_type = $class_id && active=1 && owner_id = '$user[login_id]' order by timestamp asc, owner_id desc");
		}elseif($view == 2) { //tech units
			db2(__FILE__,__LINE__,"select item_name,units,timestamp,price,owner_id,item_id from ${db_name}_used where item_type = $view && active=1 && owner_id = '$user[login_id]' order by timestamp asc, item_name asc");
		}
		$items = dbr2();
		while($items) {
			db(__FILE__,__LINE__,"select ship_id from ${db_name}_used where item_id = '$items[item_id]'");
			$s_id = dbr();
			db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$s_id[ship_id]'");
			$s_inf = dbr();
			$items_time = date( "M d - H:i",$items[timestamp]);
			if($items[owner_id] > 0){
				db(__FILE__,__LINE__,"select login_name,login_id,clan_sym,clan_sym_color from ${db_name}_users where login_id = $items[owner_id]");
				$owner=dbr();
				$items_own = print_name($owner);
			}
			$items[$typo] = "<div align=\"center\">$items[$typo]</div>";
			$items[price] = number_format($items[price]);
			$items_scrap = "<div align=\"center\"><input type=checkbox name=mass_scrap[$items[item_id]] value=$items[item_id]></div>"; //adds checkboxes for ship scrap form
			$items_retrieve = "<div align=\"center\"><input type=checkbox name=mass_retrieval[$items[item_id]] value=$items[item_id]></div>"; //adds checkboxes for tech retrieval form
			if($view == 1) { //ships
				if($items[owner_id] == $user[login_id]) {
					$typo4 = make_row(array("<b class=b1>$items[item_name]</b>","$items[$typo2]","$items_time","$items[price]","$items_own","$s_inf[fighters]","$s_inf[cargo_bays]","$items_scrap","<a href=hw_used_store.php?alterp=1&itemid=$items[item_id]>Change Price</a>"));
				}
			}elseif($view == 2) { //tech units
				if($items[owner_id] == $user[login_id]) {
					$typo4 = make_row(array("$items[item_name]","$items[$typo2]","$items_time","$items[price]","$items_own","$items_retrieve","<a href=hw_used_store.php?alterp=2&itemid=$items[item_id]>Change Price</a>"));
				}
			}
			$text .= $typo4;
			$items = dbr2();
		}
		if($view == 1) {
			$text .= "</table>";
			$text .= "<br /><a href=javascript:TickAll(\"scrap_used\")>Invert Ship Selection</a> - ";
			$text .= "<input type=submit value='Scrap Ships'></form><p>";
		} else {
			$text .= "</table>";
			$text .= "<br /><a href=javascript:TickAll(\"retrieve_used\")>Invert Tech Selection</a> - ";
			$text .= "<input type=submit value='Retrieve Tech'></form><p>";
		}
	}
}


// -----------------------------------------------------------------
// 	allows the adding of ships or tech units to finbarr's. it is
// important that the mass adding option be used only for ships of
// the same class, otherwise the computing of min prices is trouble
// -----------------------------------------------------------------

if($add_item == 1) {

	$text = "<br />Welcome to <b class=b1>$home[used]</b><br />";
	$text .= "<br />Welcome to the store! You can use this section to add items for sale to the public. At present we are only accepting used ships and Tech. Support Units, but later other forms of equipment will be capable of being added. Prices are set by you, and can also be altered at a later date if you feel a cheaper price will give a better chance of a successful sale.";
	$text .= "<br /><br /><a href=hw_used_store.php?add_item=2>Add Used Ships to <b>Sale List</b></a>";
	$text .= "<br /><a href=hw_used_store.php?add_tech=1>Add Tech. Units to <b>Sale List</b></a>";

}elseif($add_item == 2) {

	$text = "Welcome to <b class=b1>$home[used] Ship Sales Division</b>. <br /><br />This area manages the second hand sales of ships to the known galaxy. We currently charge commission of <b>5%</b>, but hey that means you get <b>95%</b>! This is a good deal, I'll have you know! <br /><br />One word of warning, in accordance with Sol Authority Directive 8907-942-AH, any ships released to our custody may not be reclaimed. If you have an emergency situation, we can however offer you a scrap deal on your vessel (if remaining unsold) of <b>20%</b> of the minimum selling price. This is a good deal, I'll have you know! Ship prices are set by you, subject to a minimum price. Prices can also be altered at a later date if you feel a cheaper price will give a better chance of a successful sale. Just remember that minimum price!<br /><br />Note: Please only add groups of ships of the SAME type at any one time. If you try adding a mixture of HMs and Skirmishers at once, the pricing system will miscalculate the minimum allowable price.";

	$target = $user[login_id];
	db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '$target'");
	$ship_count = dbr();

	$ADODB_FETCH_MODE = 2;

	if($user_options[show_abbr_ship_class] == 1){ //abbriviate class names
		$class_temp_var = "class_name_abbr";
	} else {
		$class_temp_var = "class_name";
	}

	if($sort_ships){
		if($sorted_ships==1){
			$going = "asc";
			$sorted_ships=2;
		} else {
			$going = "desc";
			$sorted_ships=1;
		}

		db(__FILE__,__LINE__,"select ship_name,$class_temp_var,location,fighters,shields,config,ship_id,login_id from ${db_name}_ships where login_id = '$target' && location = $user[location] order by '$sort_ships' $going");
	} else {
		db(__FILE__,__LINE__,"select ship_name,$class_temp_var,location,fighters,shields,config,ship_id,login_id from ${db_name}_ships where login_id = '$target' && location = $user[location] order by fighters desc,ship_name asc");
	}

	$clan_ship = dbr();
	if($clan_ship) {

		if($clan_ship[login_id] == $user[login_id] && $ship_count[0] > 1) {
			$text .= "<form method=post action=hw_used_store.php name=sell_used><input type=hidden name=add_ship value=1><input type=submit value='Add Ships'><br /><br />";
			$text .= make_table(array("<a href=hw_used_store.php?target=$target&sort_ships=ship_name&sorted_ships=$sorted_ships&add_item=2>Ship Name","<a href=hw_used_store.php?target=$target&sort_ships=class_name_abbr&sorted_ships=$sorted_ships&add_item=2>Ship Class</a>","<a href=hw_used_store.php?target=$target&sort_ships=location&sorted_ships=$sorted_ships&add_item=2>Location</a>","<a href=hw_used_store.php?target=$target&sort_ships=fighters&sorted_ships=$sorted_ships&add_item=2>Fighters</a>","<a href=hw_used_store.php?target=$target&sort_ships=shields&sorted_ships=$sorted_ships&add_item=2>Shields</a>","<a href=hw_used_store.php?target=$target&sort_ships=config&sorted_ships=$sorted_ships&add_item=2>Specials</a>","Add Ship"));
		} else {
			$text .= make_table(array("<a href=hw_used_store.php?target=$target&sort_ships=ship_name&sorted_ships=$sorted_ships&add_item=2>Ship Name","<a href=hw_used_store.php?target=$target&sort_ships=class_name_abbr&sorted_ships=$sorted_ships&add_item=2>Ship Class</a>","<a href=hw_used_store.php?target=$target&sort_ships=location&sorted_ships=$sorted_ships&add_item=2>Location</a>","<a href=hw_used_store.php?target=$target&sort_ships=fighters&sorted_ships=$sorted_ships&add_item=2>Fighters</a>","<a href=hw_used_store.php?target=$target&sort_ships=shields&sorted_ships=$sorted_ships&add_item=2>Shields</a>","<a href=hw_used_store.php?target=$target&sort_ships=config&sorted_ships=$sorted_ships&add_item=2>Specials</a>"));
		}

		//loop through ships
		while($clan_ship) {
			if(!$clan_ship['config']){
				$clan_ship['config'] = "None";
			}
			if($clan_ship['ship_id'] == $user['ship_id']){
				$clan_ship['ship_id'] = "";
			} elseif($clan_ship['login_id'] == $user['login_id'] && $ship_count[0] > 1) {
				$clan_ship['ship_id'] = "<div align=\"center\"><input type=checkbox name=expl[$clan_ship[ship_id]] value=$clan_ship[ship_id]></div>";
			} else {
				$clan_ship['ship_id'] = "";
			}

			$clan_ship['login_id'] = NULL;
			$clan_ship['login_name']="<b class=b1>$clan_ship[login_name]</b>";
			$text .= make_hash_row($clan_ship);
			//"- <a href=location.php?command=$ship_id>Command</a>"
			$clan_ship = dbr();
		}
		$text .= "</table><br />";
		if($ship_count[0] > 1 && $target == $user[login_id]) {
			$text .= "<input type=submit value='Add Ships'> - <a href=javascript:TickAll(\"sell_used\")>Invert Ship Selection</a></form><p>";
		}
	}
}


// -----------------------------------------------------------------
// because ships are more complicated than simple tech unit amount, this
// ensures the added ships remain stored intact on the DB and hidden from
// players
// -----------------------------------------------------------------]

if($add_ship) {
	if(!$expl){
		print_page("Add Ships","You must select at least one ship to add.");
	}
	$math = 0;
	$temp444 = $expl;
	while ($var = each($temp444)) {
		db(__FILE__,__LINE__,"select login_id from ${db_name}_ships where ship_id = '$var[value]'");
		$target_ship = dbr();
		$math++;
		if($target_ship[login_id] != $user[login_id]) {
			print_page("Add Ship","You can only add one of your own ships!");
			break;
		} elseif($user[ship_id] == $var[value]) {
			print_page("Add Ship","You may not add the ship that you are commanding.");
			break;
		}
	}
	if($sure != "yes") {
		$ostr .= "Are you sure you want to add <b>$math</b> ship(s)?";
		$ostr .= "<form name=add_ships action=hw_used_store.php method=post>";
		$temp444 = $expl;
		$i=0;
		while ($var = each($temp444)) {
			$ostr .= "<input type=hidden name=expl[$i] value='$var[value]'>";
			$i++;
		}
		$ostr .= '<input type=hidden name=add_ship value=1>';
		$ostr .= '<input type=hidden name=sure value=yes>';
		$ostr .= '<input type=hidden name=math value=$math>';
		$ostr .= '<input type=submit name=submit value=Yes> <input type="Button" width="30" value="No" onclick="javascript: history.back()"> </form>';
		print_page("Sure?",$ostr);
	}elseif($set_price < 1) {
		unset($min_price);
		$temp999 = $expl;
		while($var = each($temp999)) {
			db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$expl[0]'");
			$shp_add = dbr();
			db(__FILE__,__LINE__,"select cost,fighters from ship_types where type_id = '$shp_add[shipclass]'");
			$chk_cost = dbr();
			$base_cst = $chk_cost[cost] - ($chk_cost[fighters] * 100);	//cost of ship subtracting fighter cost
			$fghtr_cst = $shp_add[fighters] * 100; //cost of fighters being added
			$chk_min = ($base_cst + $fghtr_cst) * 0.3; //min of 30% of joint default cost
			if($min_price < $chk_min) {
				$min_price = $chk_min;
			}
		}
		$error_str .= "Enter price you wish to charge for each ship being put up for sale. Assuming all of your ships are of the same class (as suggested), the absolute minimum price is shown in the text box below. Please note that minimum prices take fighters into account, and the below minimum is based on the ship with the most fighters in that ship class. To set lower prices for other ships with less fighters, add them separately or alter the prices after adding them.";
		$error_str .= "<form name=mass_sell action=hw_used_store.php method=post>";
		$temp444 = $expl;
		$i=0;
		while ($var = each($temp444)) {
			$error_str .= "<input type=hidden name=expl[$i] value='$var[value]'>";
			$i++;
		}
		$error_str .= "<input type=hidden name=add_ship value=1>";
		$error_str .= "<input type=hidden name=sure value=yes>";
		$error_str .= '<input type=hidden name=math value=$math>';
		$error_str .= "<input type=hidden name=descr value=$descr>";
		$error_str .= "<input name=set_price value=$min_price size=7>";
		$error_str .= "<input type=submit value=Submit></form>";
		print_page("Sure?",$error_str);
	} else {
		$temp444 = $expl;///here where we add the ship to the used_ship table for MySQL
		while ($var = each($temp444)) {
			db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$var[value]'");
			$shp_add = dbr();
				db(__FILE__,__LINE__,"select cost,fighters from ship_types where type_id = '$shp_add[shipclass]'");
			$chk_cost = dbr();
			$base_cst = $chk_cost['cost'] - ($chk_cost['fighters'] * 100);	//cost of ship subtracting fighter cost
			$fghtr_cst = $shp_add['fighters'] * 100; //cost of fighters being added
			$chk_min = ($base_cst + $fghtr_cst) * 0.3; //min of 30% of joint default cost
			if($set_price < $chk_min) {
				print_page("Minimum Price Breached","Your ship's price is restricted to a minimum level. This minimum is calculated as 30% of: the ship cost as per the ship listing (i.e. The listing cost minus the included fighters, e.g. Merchant Freighter = 10000 - 7000 = 3000 credits) plus the cost of all added fighters. The resulting minimum for your current ship is <b>$chk_min</b> Credits.<br /><p><a href=javascript:history.back()>Try Again</a>");
			}
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set login_id = 0, login_name = 'Finbarr', clan_id = -1, location = 0, fleet_id = 0 where ship_id = '$var[value]'");
			$shp_add[class_name] = "$shp_add[class_name]";
			dbn(__FILE__,__LINE__,"insert into ${db_name}_used (item_name, item_code, item_type, race, owner_id, price, timestamp, active, config, ship_id, ship_type) values ('$shp_add[class_name]', 'SHIP', '1', '$user[race]', '$shp_add[login_id]', '$set_price', '".time()."', '1', '$shp_add[config]', '$shp_add[ship_id]', '$shp_add[shipclass]')");
		}
		post_news("<b class=b1>$user[login_name]</b> has added <b>$math</b> ship(s) to <b class=b1>".addslashes($home['used'])."</b>.");
		print_page("Ships Added to Used Store","<b>$math</b> Ships added to <b class=b1>$home[used]</b>.<br />");
	}
}

// -----------------------------------------------------------------
// adds tech amounts to Finbarr's
// -----------------------------------------------------------------

if($add_tech) {
	if(!$tech_amount) {
		$txt .= "<form action=hw_used_store.php><input type=hidden name=add_tech value=yes>";
		$txt .= "Amount of Technical Support Units to add for sale:<br /><br />";
		$txt .= "<input type=text name=tech_amount size=6>";
		$txt .= "<br /><br />Enter price you wish to charge for the <b>$tech_amount</b> Tech. Units being put up for sale.<br /><br />";
		$txt .= "<input name=set_price value=0 size=9>";
		$txt .= "<input type=submit value='Add'></form>";
		print_page("Tech. Unit Info.",$txt);
	}elseif($sure != 'yes') {
		get_var('Add Tech. Units','hw_used_store.php',"Are you sure you want to add $tech_amount Tech. Units to <b class=b1>$home[used]</b> for sale?",'sure','yes');
	} else {
		settype($tech_amount, "integer");
		if ($tech_amount <= 0) {
			print_page("Store Error","You can't add negative or 0 Tech. Units<br />");
		} elseif ($user[tech] < $tech_amount) {
			print_page("Store Error","You don't have that many Tech. Units<br />");
		} else {
			take_tech($tech_amount);
			insert_history($user[login_id],"Added $tech_amount to Used Item Store");
			post_news("<b class=b1>$user[login_name]</b> has added <b>Tech. Units</b> for sale at <b class=b1>".addslashes($home['used'])."</b>.");
			dbn(__FILE__,__LINE__,"insert into ${db_name}_used (item_name, item_code, item_type, race, owner_id, price, timestamp, active, units) values ('Tech. Units', 'TECH', '2', $user[race], '$user[login_id]', '$set_price', '".time()."', '1', '$tech_amount')");
			print_page("Transfer Complete","You added <b>$tech_amount</b> Tech. Units to <b class=b1>$home[used]</b>.");
		}
	}
}


print_page("$home[used]",$text);

?>
