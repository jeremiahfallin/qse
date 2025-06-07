<?php
include_once("includes/nocache.inc.php");

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

include("themes/".$user_options['theme']."/header.php");

$filename = 'hw.php';

db2(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
$eras = dbr2();
$era = $eras['value'];
if($user['login_id'] != 1){
db4(__FILE__,__LINE__,"select * from races where race_ID = $user[race] && era = $era");
$home = dbr4();
$homeworld = $home['planet_name'];
$homeimg = $home['img'];
$descrip = $home['planet_descrip'];
} elseif($user['login_id'] == 1) {
db4(__FILE__,__LINE__,"select * from races where race_ID = $user[location] && era = $era");
$home = dbr4();
$homeworld = $home['planet_name'];
$homeimg = $home['img'];
$descrip = $home['planet_descrip'];
}
$rs = "<p><a href=location.php>Return to Star System</a>";

db2(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'Homeworld'");
$hwc = dbr2();
$hwc2 = $hwc['value'];

db2(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
$mr = dbr2();
$max_races = $mr['value'];


if($ID == $user['race']){
	print_header("Not your Homeworld");
print_status();
echo "<br><br><center>";
	if($user_options['show_pics']) {
echo "<img src='images/$homeimg.jpg'>";
}
echo "<br>$descrip<p>Welcome to $homeworld.  $homeworld, However, is not your homeworld.  I suggest you scram.  NOW</center>";
echo "<br><center><a href=location.php>Return to Star System</a></center>";
}

else{
#
#user can't access any functions as they are dead and sudden death has been set.
#


sudden_death_check($user);
settype($amount,"int");
$amount = round($amount);



#
#ensure user is in system 1 b4 continuing.
#
if (($user['location'] != $user['race'] || $user['location'] > $max_races) && $user['login_id'] != 1) {
if ($user['location'] != $user['race'] && $user['location'] <= $max_races){
db4(__FILE__,__LINE__,"select * from races where race_ID = $user[location] && era = $era");
$home = dbr4();
$homeworld2 = $home['planet_name'];
$homeimg2 = $home['img'];
$descrip2 = $home['planet_descrip'];
	if($user_options['show_pics']) {
$print_new = "<br><br><center><img src='images/era/$era/$homeimg2'><br>$descrip2<p>Welcome to $homeworld2.  $homeworld2, However, is not your homeworld.  It is suggest you leave.  NOW</center>";
} else {
$print_new = "<br><br><center><br>$descrip2<p>Welcome to $homeworld2.  $homeworld2, However, is not your homeworld.  It is suggest you leave.  NOW</center>";
}
	print_page("Homeworld",$print_new);
	}
elseif($user['location'] > $max_races){
$print_new = "This system dosent have a homeworld.  I suggest you stop toying with the URLs";
	print_page("Homeworld",$print_new);
}
}

if ($user['location'] == $user['race'] || $user['login_id'] == 1) {
	#
	#load fleet with colonists.
	#
	if($all_colon && ($user[ship_id]!=1 || $user[login_id] ==1)) {
		db(__FILE__,__LINE__,"select count(ship_id), sum(cargo_bays-metal-fuel-elect-organ-colon-darkmatter) from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && (cargo_bays-metal-fuel-elect-organ-colon-darkmatter) > 0");
		$ship_count = dbr();
		if($user[cash] < $cost_colonist*$ship_count[1]){
			$error_str .= "You do not have enough money.<p>";
		}elseif($user[turns] < $ship_count[0]) {
			$error_str .= "You do not have enough turns.<p>";
		}elseif(!$ship_count[0]) {
			$error_str .= "You do not have any ships with free cargo capacity in this system.<p>";
		}elseif($sure != 'yes') {
			get_var('Fill Fleet',$filename,"Are you sure you want to fill all <b>$ship_count[0]</b> ships with colonists?<br />Total Capacity = <b>$ship_count[1]</b>",'sure','');
		} else {
			take_cash($cost_colonist*$ship_count[1]);
			charge_turns($ship_count[0]);
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set colon = colon + (cargo_bays-metal-fuel-elect-organ-colon) where login_id = '$user[login_id]' && location = '$user[location]' && (cargo_bays-metal-fuel-elect-organ-colon) > 0");
			$user_ship[colon] += empty_bays();
			$user_ship[empty_bays] = empty_bays();
		}
	}

	#
	#load ship with coloniists
	#
	if(isset($colonist) && ($user[ship_id]!=1 || $user[login_id] ==1)) {
		$fill = empty_bays();

		if($user[cash] < ($fill*$cost_colonist)){
			$fill = floor($user[cash]/$cost_colonist);
		}
$rs = "<p><a href=hw.php>Return to $homeworld</a>";
		settype($amount, "integer");
		if($amount <= 0) {
			get_var('Take Colonists',$filename,'<a href=hw.php?all_colon=1>Fill Ship</a><p>How many colonists do you want to take?<br />They cost <b>'.$cost_colonist.'</b> credit(s) each.<p>','amount',$fill);
		} elseif($fill < 1) {
			$error_str .= "You do not have the facilities (either money OR cargo space) to buy colonists. Try a different ship.<p>";
		} elseif($user[turns] < 1) {
			$error_str .= "You don't have enough turns to load colonists onto a ship.<p>";
		} elseif($amount > empty_bays()) {
			$error_str .= "You can't carry that many colonists.<p>";
		} elseif($amount*$cost_colonist > $user[cash]) {
			$error_str .= "You can't afford that many colonists.<p>";
		} else {
		take_cash($cost_colonist*$amount);
		charge_turns(1);
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set colon = colon + $amount where ship_id = $user[ship_id]");
			$user_ship[colon] += $amount;
		$user_ship[empty_bays] -= $amount;
		}
	}

	// print page
	print_header("$homeworld");
	//print_adcode();
	print_status();
	#echo "<blockquote>";
	echo $error_str;

	if(isset($ship_shop))
	{
		// Page heading + Sub-Menu
		echo(Create_PageTitleBlock("$home[ship]"));
		echo "Welcome to <b>$home[ship]</b>";
		echo "<br />Where you'll find all the finest ships, at bargain prices.<br />";
		$rs = "<p><a href=hw.php>Return to $homeworld</a>";

		// add in ships for sale
		include_once("includes/ship_shops.inc.php");
		echo($text); //$text is from include file

		echo "<p><a href=help.php?ship_info=1&shipno=-1 target=_blank>List all information for all ships.</a>";
		$rs = "<p><a href=hw.php>Return to $homeworld</a>";
		echo $error_str;
	}
	else
	{
		echo($text = Create_PageTitleBlock("Homeworld: $homeworld - Facilities"));
		echo "<table cellpadding=2 cellspacing=0><tr>"
		."<td align=left valign=top><table cellpadding=2 cellspacing=10 class=all>"
		."<tr><td><b>$home[ship]</b></td><td><a href=hw.php?ship_shop=1>Enter</a></td></tr>";
		//."<tr><td><b>Finbarr's Used Item Store</b></td><td><a href=hw_used_store.php>Enter</a></td></tr>";

	#
	#user is only able to access bilkos, seatogus & bobs if user has no ship (admin excempt)
	#
		echo "<tr><td><b>$home[used]</b></td><td><a href=hw_used_store.php>Enter</a></td></tr>";
	if($user[ship_id]!=1 || $user[login_id] ==1){
		echo "<tr><td><b>$home[equip]</b></td><td><a href=hw_equip_shop.php>Enter</a></td></tr>";
		echo "<tr><td><b>$home[upgrade]</b></td><td><a href=upgrade.php>Enter</a></td></tr>";
	}
		echo "<tr><td><b>$home[bank]</b></td><td><a href=hw_bank.php>Enter</a></td></tr>";
		echo "<tr><td><b>$home[auth]</b></td><td><!--<a href=hw_sol_auth.php>Enter</a>-->Closed</td></tr>";
		echo "<tr><td><b>$home[auction]</b></td><td><a href=hw_bilkos.php>Enter</a></td></tr>";

	if($user[ship_id]!=1 || $user[login_id] ==1){
		echo "<tr><td><b>$home[colo]</b></td><td><a href=hw.php?colonist=1>Enter</a>&nbsp;/&nbsp;<a href=hw.php?all_colon=1>Fill Fleet</a></td></tr></table><br />";
	} else {
		echo "</td></tr></table><br />";
	}
		db3(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
		$race = dbr3();
		$era = $race['value'];
		$rs = "<a href=location.php>Takeoff</a><br /></td><td valign=top align=center>";
	if($user_options['show_pics']) {
		$rs .= "<img src='images/era/$era/$homeimg'>";
		}
		$rs .= "<br /><br /><div align=\"center\">$descrip</div></td></tr></table>";
}
} else {
	print_header("Not in Sol");
	print_status();
	$rs = "<p><a href=location.php>Return to Star System</a>";
	echo "<blockquote><p>&nbsp;<p>You're not in the Sol Star System";

}

print_footer();
}
?>
