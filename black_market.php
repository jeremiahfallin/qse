<?php

/*
// Name: black_market.php
// By: Maugrim The Reaper (bpaddy2@yahoo.com)/ Moriarty
// Purpose: Control of available BMs
// Date Completed: 16/08/2002
// Version: 0.7
*/

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

sudden_death_check($user);



db(__FILE__,__LINE__,"select * from ${db_name}_bmrkt where location = '$user[location]' && bmrkt_id = '$bmrkt_id' order by bmrkt_type asc");
$bmrkt = dbr();

if (!$bmrkt) {
	print_page("Port","You may not contact a blackmarket that is not in the same system as you are in. Stop playing with the URL's'");
} elseif($user[ship_id] ==1 && $user[login_id] !=1) {
  	print_page("Error","You are unable to contact the blackmarket here, as you are not a real Starship Captain. You have no ship!");
} elseif($flag_bmrkt == 0 && $user[login_id] !=1) {
  	print_page("Error","Admin has disabled the blackmarkets for the duration.");
}


$error_str .= "You've reached <b class=b1>$bmrkt[bm_name]'s</b> Blackmarket.";
$error_str .= "<p>Welcome to the Blackmarket!<br />Ahhhh! I see you are a fellow starship Captain. Excellent! No doubt you have heard of the many exotic wonders I offer? Good. Do you wish to purchase Upgrades or Bombs? All strictly illegal, of course.<br />";

$error_str .= "<br /><a href=bm_ships.php?bmrkt_id=$bmrkt_id&from_0=1>Stolen Ships and Advanced Starships</a>";
$error_str .= "<br /><a href=bm_upgrades.php?bmrkt_id=$bmrkt_id&from_0=1>Blackmarket Upgrades and Alien Devices</a>";
if($flag_mines == 1 || $user[login_id] == 1) {
	$error_str .= "<br /><a href=bm_bombs.php?bmrkt_id=$bmrkt_id&from_0=1>Prototype AI controlled Bombs and Mines</a>";
}
$error_str .= "<p>Please note captain that these items will require a number of <b>Technological Support Units</b> before you can purchase them.";




$rs = "<p><a href=location.php>Close Contact</a><br />";

print_page("Blackmarket",$error_str);


?>
