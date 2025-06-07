<?php
/*
//
File:			bm_ships.php
Objective:		To display purchase information for Blackmarket ships
Version:		0.71 (QS 2.0.8b)
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com) - [modified by: Moriarty]
Date Committed:	16 May 2002	Date Modified:	30 December 2003

Copyright (c) 2002 - 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

$filename = 'bm_ships.php';

sudden_death_check($user);


$rs = "";
if($from_0 == 1){
 	$rs = "<p><a href=black_market.php?bmrkt_id=$bmrkt_id>Return to Blackmarket</a>";
}
$rs .= "<p><a href=location.php>Close Contact</a>";

$return_lnk = "<a href=bm_ships.php?bmrkt_id=$bmrkt_id>Return to Blackmarket</a>";

// Blackmarket Controls

db(__FILE__,__LINE__,"select * from ${db_name}_bmrkt where location = '$user[location]' && (bmrkt_type = 1 || bmrkt_type = 0) && bmrkt_id = '$bmrkt_id'");
$bmrkt = dbr();

if (!$bmrkt) {
	print_page("Blackmarket","You may not contact a blackmarket that is not in the same system as you are in. Stop playing with the URL's'");
} elseif($user[ship_id] ==1 && $user[login_id] !=1) {
	print_page("Error","The local Pirates who operate this service have refused you entry. How can you be a Captain with no ship!!!");
} elseif($flag_research !=1) {
	print_page("Error","Admin in his/her near infinite wisdom has disabled the Blackmarkets");
}

$text .= "<p>Welcome to <b>O'Reilly's Shipbuilders</b>, a division of <b class=b1>$bmrkt[bm_name]'s</b> Blackmarket.";
$text .= "<br /><p>If it's cutting edge ships you want, you've found the right place.<br />";
db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '$user[login_id]'");
$numships = dbr();
$text .= "<p>Your Fleet currently consists of <b>$numships[0]</b> Ships.";

// list all available ships.
$text .= "<p>Available Blackmarket Ships:";

include_once("includes/ship_shops.inc.php");

// Blackmarket Ships

$text .= "<p><a href=help.php?ship_info=-1&shipno=-2 target=_blank>List all information for all Blackmarket ships.</a>";

print_page("Blackmarket Ships",$text);

?>
