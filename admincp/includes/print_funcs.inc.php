<?php
/*
//
File:			print_funcs.inc.php
Objective:		File of print function used throughout Quantum Star SE
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	27 January 2004		Date Modified:	n/a

Copyright (c) 2003, 2004 by Pádraic Brady

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
function Print_FleetName($id) {
	global $db_name;
	db(__FILE__,__LINE__,"select fleet_name from ${db_name}_fleets where fleet_id = '$id'");
	$f_name = dbr();
	$output = "<a href=\"#\" onClick=\"window.open('fleet_listing.php?id=$id', 'popup', 'height=500,width=450,screenX=150,screenY=150,top=0,left=0,toolbar=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes');\">$f_name[fleet_name]</a>";
	return $output;
}


?>