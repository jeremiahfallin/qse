<?php
/*
//
File:			fleet_listing.php
Objective:		Enables the generation of a pop up list of member ships and attached functions
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	1 February 2004		Date Modified:	n/a

Copyright (c) 2003, 2004 by Pádraic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

include_once("includes/nocache.inc.php");

require_once("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

//Check if Sudden Death is in effect and whether player should be excluded from gameplay
db(__FILE__,__LINE__,"select count(ship_id) as shipsowned from ${db_name}_ships where login_id = '$user[login_id]'");
$numships = dbr();
if(!$numships['shipsowned'] && isset($sudden_death) && $user['login_id'] != 1 && $user['last_login'] != 0)
{
	print_page("Sudden Death","You have no ship, and this game is in <b>Sudden Death</b>. As such you are out of the game.");
}

// unset end-of-page Return-String link until suitable close_window function added
unset($rs);

// check presence of $id var
if(isset($_POST['id']))
{
	$fleet_id = $_POST['id'];
}
elseif(isset($_GET['id']))
{
	$fleet_id = $_GET['id'];
}
else
{
	unset($fleet_id);
}


// insert page header and sub_menu
$text = Create_PageTitleBlock("Fleet Listing","",430);

if(isset($_POST['c_new']) && isset($fleet_id))
{
	db(__FILE__,__LINE__,"select location from ${db_name}_fleets where fleet_id = '$fleet_id'");
	$f1_fleet = dbr();
	db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where ship_id = '$_POST[c_take]' and login_id = '$user[login_id]' and location = '$f1_fleet[location]'");
	$new_comm = dbr();
	if(!empty($new_comm))
	{
		dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ship_id = '$new_comm[ship_id]' where fleet_id = '$fleet_id'");
		if($user_fleet['fleet_id'] == $fleet_id)
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$new_comm[ship_id]' where login_id = '$user[login_id]'");
		}
		print("<em>Command Ship Updated</em>");
	}
	else
	{
		print("<em>Error: Ship Not Present in SS #$user[location]</em>");
	}
}


if(isset($fleet_id))
{
	/*db(__FILE__,__LINE__,"select * from ${db_name}_fleets where fleet_id = '$fleet_id'");
	$f_fleet = dbr();

	// check for fleet command ship - if none, make a ship the fleet command vessel
	if($f_fleet['ship_id'] == 0 || !isset($f_fleet['ship_id']))
	{
		db(__FILE__,__LINE__,"select ship_id, fighters from ${db_name}_ships where fleet_id = '$f_fleet[fleet_id]' order by fighters desc limit 1");
		$c_ship = dbr();
		if(!empty($c_ship))
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ship_id = '$c_ship[ship_id]' where fleet_id = '$f_fleet[fleet_id]'");
			$f_fleet['ship_id'] = $c_ship['ship_id'];
		}
	}

	// check request so only the owner of a fleet can access the listing with all totals and control options
	// non-owners will only see the basic ship listing without any control options depending on $enable_control var
	if($f_fleet['login_id'] == $user['login_id'])
	{
		$text .= "<form method=\"post\" action=\"fleet_listing.php\" name=\"new_command\">"
		."<input type=\"hidden\" name=\"c_new\" value=\"1\">"
		."<input type=\"hidden\" name=\"id\" value=\"$f_fleet[fleet_id]\">";
		$enable_control = TRUE;
	}
	else
	{
		$enable_control = FALSE;
	}

	$text .= "<div align=\"center\"><span class=\"h5\"> ".print_name($f_fleet)." - Fleet No. $f_fleet[fleet_num]: \"$f_fleet[fleet_name]\" in Star System #$f_fleet[location]</span></div>";

	// create a small table of totals (only if players fleet!) otherwise show only ship/fighter stats
	if($enable_control == TRUE || $page_flag == 1)
	{
		$text .= "<p align=\"center\">".make_table_width(array("Ships","Fighters","Empty Bays","Fuel","Metal","Colonists"),"400");
		db(__FILE__,__LINE__,"select count(ship_id) as tships, sum(fighters) as tfighters, sum(cargo_bays) as tbays, sum(fuel) as tfuel, sum(metal) as tmetal, sum(colon) as tcolon, sum(darkmatter) as tdmatter from ${db_name}_ships where fleet_id = '$f_fleet[fleet_id]'");
		$f_total = dbr();
		$t_bays = $f_total['tbays'] - $f_total['tfuel'] - $f_total['tmetal'] - $f_total['tdmatter'] - $f_total['tcolon'];
		$text .= make_row(array($f_total['tships'], $f_total['tfighters'], $t_bays, $f_total['tfuel'], $f_total['tmetal'], $f_total['tcolon']));
		$text .= "</table></p>";
		// create main ship listing in fleet + command option
		$text .= "<p align=\"center\">".make_table_width(array("Ship Name","Class Name","Fighters","Config","Empty Bays","Command"),"400");
	}
	elseif($enable_control == FALSE)
	{
		$text .= "<p align=\"center\">".make_table_width(array("Ships","Fighters"),"400");
		db(__FILE__,__LINE__,"select count(ship_id) as tships, sum(fighters) as tfighters from ${db_name}_ships where fleet_id = '$f_fleet[fleet_id]'");
		$f_total = dbr();
		$text .= make_row(array($f_total['tships'], $f_total['tfighters']));
		$text .= "</table></p>";
		// create main ship listing in fleet + command option
		$text .= "<p align=\"center\">".make_table_width(array("Ship Name","Class Name","Fighters","Config"),"400");
	}

	db(__FILE__,__LINE__,"select * from ${db_name}_ships where fleet_id = '$f_fleet[fleet_id]' and login_id = '$f_fleet[login_id]' order by ship_categ desc, class_name, ship_name asc, fighters asc");
	while($f_ship = dbr()) {
		$f_name = $f_ship['ship_name'];
		$f_class = $f_ship['class_name'];
		$f_location = "SS #".$f_ship['location'];
		$f_fighters = $f_ship['fighters'];
		$f_config = $f_ship['config'];
		if($enable_control == TRUE || $page_flag == 1)
		{
			$f_bays = $f_ship['cargo_bays'] - $f_ship['fuel'] - $f_ship['metal'] - $f_ship['darkmatter'] - $f_ship['colon'] - $f_ship['organ'] - $f_ship['elect'];
			if($f_ship['ship_id'] == $f_fleet['ship_id'])
			{
				$f_check = " checked";
			} else
			{
				$f_check = "";
			}
			if($page_flag != 1)
			{
				$f_option = "<div align=\"center\"><input type=\"radio\" name=\"c_take\" value=\"$f_ship[ship_id]\"$f_check></div>";
			}
			else
			{
				$f_option = NULL;
			}
			$text .= make_row(array($f_name,$f_class,$f_fighters,$f_config,"$f_bays / $f_ship[cargo_bays]",$f_option));
		}
		elseif($enable_control == FALSE)
		{
			$text .= make_row(array($f_name,$f_class,$f_fighters,$f_config));
		}
	}
	$text .= "</table></p>";
	if($enable_control == TRUE && $page_flag != 1)
	{
		$text .= "<br /><p align=\"center\"><input type=\"submit\" value=\"Submit!\"></p></form>";
	}*/

	$text .= Print_FleetListing($fleet_id);

	print_page_popup("Choose Fleet Command Ship",$text);
}












$error_str = $text."<p>You have apparently used a false url. Please remember that Url editing is considered a form of cheating!</p>";
print_page_popup("Url Error",$error_str);


?>
