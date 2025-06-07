<?php
/*
//
File:			fleet_funcs.inc.php
Objective:		functions for managing fleets, their creation and various checks to be applied
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	25 January 2004		Date Modified:	27 January 2004

Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/


function Transfer_ClaimedShip() {
	global $db_name, $user, $user_fleet, $ship;
	dbn(__FILE__,__LINE__,"update ${db_name}_ships set login_id = '$user[login_id]', login_name = '$user[login_name]', clan_id = '$user[clan_id]', fleet_id = '$user_fleet[fleet_id]' where ship_id = '$ship[ship_id]'");
}

function Check_FleetMax() {
	// example of a very messy global statement :)
	global $_GET, $_POST, $num, $s, $num_purchased, $bmrkt_id, $asyrd_id, $user, $ship_stats, $fleet_war_max, $max_fleet_size, $user_fleet, $db_name, $fleet_name, $math, $mass, $ship_name, $ship_type, $update, $x_totalcost, $x_totaltech, $original_num, $cltarget, $ship, $categ, $mass_ship, $sure, $descr, $array_offset, $temp798, $cash_total, $mass_transfer, $transfer, $transfer_list, $t_text, $confirm_type;
	if(isset($categ))
	{
		$ship_stats = array(); $ship_stats['ship_categ'] = $categ['ship_categ'];
	}
	elseif(isset($mass_transfer))
	{
		$ship_stats = array(); $ship_stats['ship_categ'] = $transfer['ship_categ'];
	}
	// check for fleet command ship - if none, make a ship the fleet command vessel
	if($user_fleet['ship_id'] == 0)
	{
		db(__FILE__,__LINE__,"select ship_id, fighters from ${db_name}_ships where fleet_id = '$user_fleet[fleet_id]' order by fighters desc limit 1");
		$c_ship = dbr();
		if(!empty($c_ship))
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ship_id = '$c_ship[ship_id]' where fleet_id = '$user_fleet[fleet_id]'");
			$user_fleet['ship_id'] = $c_ship['ship_id'];
		}
	}

	if($ship_stats['ship_categ'] == 3 || $ship_stats['ship_categ'] == 4 || $ship_stats['ship_categ'] == 8 || $ship_stats['ship_categ'] == 9) {
		db(__FILE__,__LINE__,"select count(ship_id) as war_cnt from ${db_name}_ships where fleet_id = '$user_fleet[fleet_id]' && (ship_categ = 3 || ship_categ = 4 || ship_categ = 8 || ship_categ = 9)");
		$war_count = dbr();
		if($war_count['war_cnt'] >= $fleet_war_max) {
			$output = "You have the maximum permissible number of Warships in your current Fleet. You may however elect to add the ship(s) you have requested to purchase to a new Fleet. If this is agreeable, enter a name for the new Fleet. Otherwise you may return to the Star System and select an alternative fleet to command, or buy other ships for this Fleet.<br /><br />";
			$output .= Ask_CreateNewFleet();
			print_page("Create Additional Fleet",$output);
		}
	}
	db(__FILE__,__LINE__,"select count(ship_id) as total from ${db_name}_ships where fleet_id = '$user_fleet[fleet_id]'");
	$gen_count = dbr();
	if($gen_count['total'] >= $max_fleet_size) {
		$output = "You have the maximum permissible number of Ships (of any type) in your current Fleet. You may however elect to add the ship(s) you have requested to purchase to a new Fleet. If this is agreeable, enter a name for the new Fleet. Otherwise you may return to the Star System and select an alternative Fleet to command, or buy other ships for this Fleet.<br /><br />";
		$output .= Ask_CreateNewFleet();
		print_page("Create Additional Fleet",$output);
	}
}



function Check_FleetMax_FleetCommand($sid,$fid) {
	global $text,$fleet_war_max,$max_fleet_size,$user,$db_name;
	db(__FILE__,__LINE__,"select ship_categ from ${db_name}_ships where ship_id = '$sid'");
	$ship_stats = dbr();
	if($ship_stats['ship_categ'] == 3 || $ship_stats['ship_categ'] == 4 || $ship_stats['ship_categ'] == 8 || $ship_stats['ship_categ'] == 9)
	{
		db(__FILE__,__LINE__,"select count(ship_id) as war_cnt from ${db_name}_ships where fleet_id = '$fid' and (ship_categ = 3 || ship_categ = 4 || ship_categ = 8 || ship_categ = 9)");
		$war_count = dbr();
		if($war_count['war_cnt'] >= $fleet_war_max)
		{
			print_page("Fleet Limited",$text."You have the maximum permissible number of Warships in this Fleet. You should move all remaining ships to another Fleet with more capacity for this ship type.<br /><br />");
		}
	}
	db(__FILE__,__LINE__,"select count(ship_id) as total from ${db_name}_ships where fleet_id = '$fid'");
	$gen_count = dbr();
	if($gen_count['total'] >= $max_fleet_size)
	{
		print_page("Fleet Limited",$text."You have the maximum permissible number of Ships (of any type) in this Fleet. You should move all remaining ships to another Fleet with more capacity for this ship type.<br /><br />");
	}
}



function Ask_CreateNewFleet() {
	global $_GET, $_POST, $num, $s, $num_purchased, $bmrkt_id, $asyrd_id, $user, $db_name, $math, $mass, $ship_name, $ship_type, $update, $x_totalcost, $x_totaltech, $original_num, $ship, $cltarget, $categ, $array_offset, $temp798, $mass_ship, $sure, $descr, $cash_total, $mass_transfer, $t_text, $confirm_type, $transfer_list;

	// update action ref for each file this is used for!!!
	if(isset($categ))
	{
		$request_confirm = "\n\n<form name=new_fleet action=hw_used_store.php method=post onSubmit='submitonce(this);'>";
	}
	elseif(isset($cltarget))
	{
		$request_confirm = "\n\n<form name=new_fleet action=raid.php method=post onSubmit='submitonce(this);'>";
	}
	elseif(isset($mass_transfer))
	{
		$request_confirm = "\n\n<form name=new_fleet action=ship_transfer.php method=post onSubmit='submitonce(this);'>";
	}
	else
	{
		$request_confirm = "\n\n<form name=new_fleet action=ship_purchase.php method=post onSubmit='submitonce(this);'>";
	}


	// for the new fleet option locate a viable fleet number not yet used.
	$i = 1;
	while($spare_num_found <= 0) {
		db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where login_id = '$user[login_id]' AND fleet_num = '$i'");
		$fleetn = dbr();
		if(empty($fleetn)) {
			$request_confirm .= '<input type=hidden name=fleetnum value='.$i.'>';
			$spare_num_found = 999;
		}
		$i = $i + 1;
	}
	//DEV-NOTE: is it possible to cut this down a few notches - it's getting convoluted
	// This code be old - :)
	// Should convert entire function to $_GET/$_POST and simply use a foreach loop to pass hidden vars - 3 lines v 20?
	if(isset($cltarget)) { //when claiming a ship
		$request_confirm .= "\n<input type=hidden name=ship value=\"".$ship."\">";
		$request_confirm .= "\n<input type=hidden name=cltarget value=\"".$cltarget."\">";
	} elseif(isset($categ)) { //when purchasing used ship
		$request_confirm .= "\n<input type=hidden name=mass_ship value=\"".$mass_ship."\">";
		$request_confirm .= "\n<input type=hidden name=math value=\"".$math."\">";
		$request_confirm .= "\n<input type=hidden name=num_purchased value=\"".$num_purchased."\">";
		$request_confirm .= "\n<input type=hidden name=original_num value=\"".$original_num."\">";
		$request_confirm .= "\n<input type=hidden name=sure value=\"".$sure."\">";
		$request_confirm .= "\n<input type=hidden name=descr value=\"".$descr."\">";
		$request_confirm .= "\n<input type=hidden name=cash_total value=\"".$cash_total."\">";
		$request_confirm .= "\n<input type=hidden name=ship_name value=\"".$ship_name."\">";
		$temp899 = array_slice($temp798, $array_offset); //cut array at offset position to pass only remaining items
		$i=0;
		while ($var = each($temp899)) {
			$request_confirm .= "\n<input type=hidden name=mass_list[$i] value=\"".$var['value']."\">";
			$i++;
		}
		//echo("XXX->>".$array_offset." YYY->>".$i);
	} elseif(isset($mass_transfer)) { //accepting a ship transfer
		$request_confirm .= "\n<input type=hidden name=mass_transfer value=\"".$mass_transfer."\">";
		$request_confirm .= "\n<input type=hidden name=confirm_type value=\"".$confirm_type."\">";
		$request_confirm .= "\n<input type=hidden name=tt_text value=\"".urlencode($t_text)."\">";
		//cut array at offset position to pass only remaining items
		$temp899 = array_slice($transfer_list, $array_offset);
		$i=0;
		while ($var = each($temp899)) {
			$request_confirm .= "\n<input type=hidden name=transfer_list[$i] value=\"".$var['value']."\">";
			$i++;
		}
	} else { //when purchasing ships
		//DEV-NOTE - why did I type this var name over and over? Just link through concatenation!
		$request_confirm .= "\n<input type=hidden name=math value=\"".$math."\">";
		$request_confirm .= "\n<input type=hidden name=num value=\"".$num."\">";
		$request_confirm .= "\n<input type=hidden name=mass value=\"".$mass."\">";
		$request_confirm .= "\n<input type=hidden name=ship_name value=\"".$ship_name."\">";

		// Fix for Alien Shipyards purchasing when fleet needs creating - ##Hades
		// Was trying to go to non-existant BM before
		if(!empty($bmrkt_id)){
			$request_confirm .= "\n<input type=hidden name=bmrkt_id value=\"".$bmrkt_id."\">";
		} elseif (!empty($asyrd_id)){
			$request_confirm .= "\n<input type=hidden name=asyrd_id value=\"".$asyrd_id."\">";
		}

		$request_confirm .= "\n<input type=hidden name=ship_type value=\"".$ship_type."\">";
		$request_confirm .= "\n<input type=hidden name=h value=\"".$s."\">";
		$request_confirm .= "\n<input type=hidden name=num_purchased value=\"".$num_purchased."\">";
		$request_confirm .= "\n<input type=hidden name=original_num value=\"".$original_num."\">";
		$request_confirm .= "\n<input type=hidden name=x_totalcost value=\"".$x_totalcost."\">";
		$request_confirm .= "\n<input type=hidden name=x_totaltech value=\"".$x_totaltech."\">";
	}
	$request_confirm .= "<input name=fleet_name value=\"\" size=10> (10 Characters Max)<br />";
	//DEV-NOTE: remember to remove </form> tag below - else remaining form is useless
	$request_confirm .= "\n<input type=submit name=submit value=Submit>";

	// 13-4-04 - inserted section to use an existing fleet - will list all fleets at this location with spare capacity
	// for the ship category being purchased, plus all empty fleets - code will relocate empty fleets automagically.
	//$request_confirm .= "<br /><br />Or select an existing fleet from the below lists:<br />";
	//$request_confirm .= "\n<select name=\"empty_fleet_id\">";
	// add one empty dummy tab for Nil entry
	//$request_confirm .= "\n<option name=\"empty_fleet_id\" value=\"0\" selected=\"selected\">None</option>";
	//$request_confirm_append = "\n<option name=\"sameloc_fleet_id\" value=\"0\" selected=\"selected\">None</option>";
	//db3(__FILE__,__LINE__,"select fleet_id, fleet_name, fleet_num, location from ${db_name}_fleets where login_id = '$user[login_id]'");
	//while($empty_fleets = dbr3()) {
		//db2(__FILE__,__LINE__,"select count(ship_id) as numships from ${db_name}_ships where fleet_id = '$empty_fleets[fleet_id]'");
		//$ship_count = dbr2();
		//if($ship_count['numships'] == 0)
		//{
			//$request_confirm .= "\n<option name=\"empty_fleet_id\" value=\"$empty_fleets[fleet_id]\">Fleet: $empty_fleets[fleet_num] \"$empty_fleets[fleet_name]\" (<em>Empty</em>)</option>";
		//}
		//elseif($empty_fleets['location'] == $user['location'])
		//{
			//$request_confirm_append .= "\n<option name=\"sameloc_fleet_id\" value=\"$empty_fleets[fleet_id]\">Fleet: $empty_fleets[fleet_num] \"$empty_fleets[fleet_name]\" (<em>SS# $empty_fleets[location]</em>)</option>";
		//}
	//}
	//$request_confirm .= "\n</select>";
	//if(isset($request_confirm_append))
	//{
		//$request_confirm .= "<br /><br /><select name=\"sameloc_fleet_id\">";
		//$request_confirm .= $request_confirm_append;
		//$request_confirm .= "\n</select>";
	//}
	//$request_confirm .= "\n</form>\n\n";

	if(isset($update))
	{
		$request_confirm .= "<br /><br /><br /><h4>Purchased So Far:</h4>$update[text]";
	}

	// DEV-NOTE: need to tidy up this summary into a clean cut table!!!
	// delete summary if only one ship being purchased!
	if(isset($ship_name) && !isset($categ))
	{
		$num = $original_num - $num_purchased;
		$request_confirm .= "<br /><br />Original: $original_num, Purchased: $num_purchased, Remaining: $num<br />";
	}
	elseif(isset($array_offset))
	{
		$request_confirm .= "<br /><br /><b>$array_offset<b> Ships have been purchased thus far from Finbarr's<br />";
	}
	return $request_confirm;
}

// create new fleet if needed to hold additional ships being purchased that will not fit in current fleet.
function Check_ToCreateFleet($fleetnum) {
	global $db_name, $user, $fleet_name;
	db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where fleet_num = '$fleetnum' && login_id = '$user[login_id]'");
	$exist_chk = dbr();
	if(empty($exist_chk)) {
		dbn(__FILE__,__LINE__,"insert into ${db_name}_fleets (fleet_name, fleet_num, login_id, login_name, location) values ('$fleet_name','$fleetnum','$user[login_id]','$user[login_name]','$user[location]')");
		db(__FILE__,__LINE__,"select * from ${db_name}_fleets where login_id = '$user[login_id]' && fleet_num = '$fleetnum'");
		$flt = dbr();
		// switch user fleet to new fleet (changed permanently when command changed to a new ship in this fleet).
	}
	return $flt;
}

?>