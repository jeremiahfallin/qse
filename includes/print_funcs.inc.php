<?php
/*
//
File:			print_funcs.inc.php
Objective:		File of print function used throughout Quantum Star SE
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	27 January 2004		Date Modified:	n/a

Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/



// single use function for creating a standard page title and sub_menu
function Create_PageTitleBlock($title,$array,$width="553") {
	//default width of 553 matches max sub_menu width
	$text = "<hr width=\"$width\" align=\"left\" />"
	."<span class=\"pagetitle_h4\">$title</span>";
	if(!empty($array)) {
		$text .= "<br /><br />".Create_SubMenu($array);
	}
	$text .= "<hr width=\"$width\" align=\"left\" />";
	return $text;
}

// function creates a single line sub_menu for certain game function areas - $array is an array of link urls and
// respective link text - see example of the transfer sub-menu at top of file, and CSS code in the red_Tempest CSS
// file.
function Create_SubMenu($array) {
	$newline = 0;
	$ret_str="<table cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"menu_border\"><tr>";
	foreach($array as $key=>$val)
	{
		if($newline == 3)
		{
			$ret_str .= "</tr><tr>"; //adds a new line after every three sub-menu links
		}
		$ret_str .= "<td align=\"center\"><a href=\"$val\" class=\"page_menu\">$key</td>";
		$newline++;
	}
	$ret_str .= "</tr></table>";
	return $ret_str;
}


/*/ function to output the name of a fleet, and a link to its ship_listing
function Print_FleetName($id) {
	global $db_name;
	db(__FILE__,__LINE__,"select fleet_name from ${db_name}_fleets where fleet_id = '$id'");
	$f_name = dbr();
	$output = "<a href=\"javascript:popUp('fleet_listing.php?id=$id',450,600)\">$f_name[fleet_name]</a>";
	return $output;
}*/


// function to output the name of a fleet, and a link to its ship_listing
function Print_FleetName($id,$name) {
	global $db_name;
	if(!isset($name))
	{
		db(__FILE__,__LINE__,"select fleet_name from ${db_name}_fleets where fleet_id = '$id'");
		$f_name = dbr();
	} else {$f_name=array("fleet_name"=>$name);}
	$output = "<a href=\"#\" onClick=\"window.open('fleet_listing.php?id=$id', 'popup', 'height=500,width=450,screenX=150,screenY=150,top=0,left=0,toolbar=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes');\">$f_name[fleet_name]</a>";
	return $output;
}

//function to display a list of all resources at the players location
function Display_Resources($location,$show_darkmatter) {
	global $db_name;
	//get star info
	db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = '$location'");
	$star = dbr();
	//create table structure
	$text = "
		<table cellspacing=\"0\" cellpadding=\"1\" width=\"250\" class=\"all\" style=\"border-bottom: 0px;\">
			<tr>
				<th colspan=\"2\" style=\"font-size: 10pt;\">
					Available Resources in Star System #$location
				</th>
			</tr>
	";
	//list all resources
	if($show_darkmatter) {
		db(__FILE__,__LINE__,"select * from ${db_name}_resources where resource_categ = 0 order by resource_id asc");
	} else {
		db(__FILE__,__LINE__,"select * from ${db_name}_resources where resource_categ = 0 and sql_name <> 'darkmatter' order by resource_id asc");
	}
	while($r_list = dbr()) {
		$text .= "
			<tr>
				<th>
					".$r_list['name']."
				</th>
				<th>
					".number_format($star[$r_list['sql_name']])."
				</th>
			</tr>
		";
	}
	$text .= "
		</table>
	";
	return $text;
}


// prints a fleet listing to page, $page_flag is zero by default - if set to one will not include forms
function Print_FleetListing($fid,$page_flag=0) {
	global $db_name, $user;
	db(__FILE__,__LINE__,"select * from ${db_name}_fleets where fleet_id = '$fid'");
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
		if($page_flag != 1)
		{
			$text .= "<form method=\"post\" action=\"fleet_listing.php\" name=\"new_command\">"
			."<input type=\"hidden\" name=\"c_new\" value=\"1\">"
			."<input type=\"hidden\" name=\"id\" value=\"$f_fleet[fleet_id]\">";
		}
		$enable_control = TRUE;
	}
	else
	{
		$enable_control = FALSE;
	}

	$text .= "<div align=\"center\"><span class=\"h5\">" . print_name($f_fleet)
		. "<br />Fleet No. $f_fleet[fleet_num]: \"$f_fleet[fleet_name]\" in Star System #$f_fleet[location]</span></div>";

	// create a small table of totals (only if players fleet!) otherwise show only ship/fighter stats
	if($enable_control == TRUE && $page_flag != 1)
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
	elseif($page_flag == 1)
	{
		$text .= "<p align=\"center\">".make_table_width(array("Ship Name","Class Name","Fighters","Config","Select"),"400");
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
			}
			else
			{
				$f_check = "";
			}
			if($page_flag != 1)
			{
				$f_option = "<div align=\"center\"><input type=\"radio\" name=\"c_take\" value=\"$f_ship[ship_id]\"$f_check></div>";
			}
			elseif($user['ship_id'] != $f_ship['ship_id'])
			{
				$f_option = "<div align=\"center\"><input type=\"checkbox\" name=\"expl[$f_ship[ship_id]]\" value=\"$f_ship[ship_id]\" /></div>";
			}
			if($page_flag != 1)
			{
				$text .= make_row(array($f_name,$f_class,$f_fighters,$f_config,"$f_bays / $f_ship[cargo_bays]",$f_option));
			}
			else
			{
				$text .= make_row(array($f_name,$f_class,$f_fighters,$f_config,$f_option));
			}
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
	}
	return $text;
}


// display a system message (usually for a player error)
function SystemMessage($text="Error! Please try again or contact an Administrator.") {
	global $tpl;
	$tpl->assign("system_message", $text);
	$tpl->display("system_message.tpl.html");
}

?>