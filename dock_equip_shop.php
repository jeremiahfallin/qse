<?php
require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

$filename = "dock_equip_shop.php";

db(__FILE__,__LINE__,"select port_id from ${db_name}_ports where location = '$user[location]'");
$ports = dbr();
if(!$ports){
	print_page("Error","Starport Equipment Shop does not exist at this location.");
}

sudden_death_check($user);

if($alternate_play_1 == 1){
	$ship_stats_1 = load_ship_types();
	$ship_stats = $ship_stats_1[$user_ship[shipclass]];
	$mining_switch_cost = round($ship_stats[cost]/10);
}

#fighter cost is now based upon the admin var.
#$fighter_cost = 100;
$fighter_cost = $fighter_cost_earth;
if($fighter_cost <= 0){
	$fighter_cost = 1;
}
if($shield_cost <= 0){
	$shield_cost = 1;
}
//$shield_cost = 250;
$bomb_cost = $cost_bomb;

$rs = "<p><a href=port.php>Return to Starport</a>";

$amount = round($amount);

//db ("select value from ${db_name}_db_vars where name = 'flag_bomb'");
//$varsforgame = dbr();

if($switch == 1){#someone is switching mining types.
	if($alternate_play_1 != 1){#check to see if alternate style of play is in effect.
		$error_str .= "That modification is not available with this style of play.<p>";
	} elseif($user[cash] < $mining_switch_cost){
		$error_str .= "You cannot afford to switch mining types. The cost is generally 10% of the original price of the ship.<p>";
	} elseif($user_ship[mine_rate_metal] < 1 && $user_ship[mine_rate_fuel] < 1) {
		$error_str .= "This ship has no mining capability.<br />This modification simply switches the mining rates around, it does not add mining capability.";
	}elseif($sure != 'yes') {
		get_var('Switch Mining',$filename,"Are you sure you want to switch mining rates on this ship? At present this ship has a metal mining rate of: <b>$user_ship[mine_rate_metal]</b> and a fuel mining rate of: <b>$user_ship[mine_rate_fuel]</b>.<br /><br />For the cost of <b>$mining_switch_cost</b> credits, you can make the metal rate: <b>$user_ship[mine_rate_fuel]</b>, and the fuel rate: <b>$user_ship[mine_rate_metal]</b>.<br /><br />This can be reversed at any time by purchasing another mining switch.<br />This will <b class=b1>NOT</b> use an upgrade slot.",'sure','');
	} else {
		take_cash($mining_switch_cost);
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set mine_rate_fuel = $user_ship[mine_rate_metal], mine_rate_metal = $user_ship[mine_rate_fuel] where ship_id = '$user_ship[ship_id]'");
		$temp4854 = $user_ship[mine_rate_metal];
		$user_ship[mine_rate_metal] = $user_ship[mine_rate_fuel];
		$user_ship[mine_rate_fuel] = $temp4854;
		$error_str .= "Mining Rates switched for <b>$mining_switch_cost</b> Credits.<p>";
	}
} elseif($mass_switch){ #switch the fleet
	db(__FILE__,__LINE__,"select sum(s.cost / 10)as cost, count(ship_id) as num from ship_types s, ${db_name}_ships us where s.type_id = us.shipclass && location='$user[location]' && us.login_id='$user[login_id]' && ship_id != 1 && (s.mine_rate_fuel > 1 || s.mine_rate_metal > 1) group by us.login_id");
	$total_cost = dbr();
	$total_cost[cost] = round($total_cost[cost]);
	if($alternate_play_1 != 1){
		$error_str .= "That modification is not available with this style of play.<p>";
	} elseif($user[cash] < $total_cost[cost]){
		$error_str .= "You do not have enough cash to switch the <b>$total_cost[num]</b> ships that can be switched in this system. <br />The cost would be <b>$total_cost[cost]</b><p>";
	} elseif($total_cost[num] < 1){
		$error_str .= "You do not have any ships in this system that can be switched. Switching simply switches the mining rates around, but none of your ships in this system have any mining capabilities.<p>";
	}elseif($sure != 'yes') {
		get_var('Switch Mining',$filename,"Are you sure you want to switch mining rates on <b>$total_cost[num]</b> ships in this system? The price will be <b>$total_cost[cost]</b> Credits<br /><br />This can be reversed at any time by purchasing another mining switch.<br />This will <b class=b1>NOT</b> use any upgrade slots.",'sure','');
	} else {
		take_cash($total_cost[cost]);
		db(__FILE__,__LINE__,"select ship_id,mine_rate_fuel,mine_rate_metal from ${db_name}_ships where location='$user[location]' && login_id='$user[login_id]' && ship_id != 1 && (mine_rate_fuel > 1 || mine_rate_metal > 1)");
		while($results=dbr()){
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set mine_rate_fuel = '$results[mine_rate_metal]', mine_rate_metal = '$results[mine_rate_fuel]' where ship_id = $results[ship_id]");
		}

#		dbn(__FILE__,__LINE__,"update ${db_name}_ships set mine_rate_fuel = mine_rate_metal, mine_rate_metal = mine_rate_fuel where location='$user[location]' && login_id='$user[login_id]' && ship_id != 1 && (mine_rate_fuel > 1 || mine_rate_metal > 1)");

		$temp4854 = $user_ship[mine_rate_metal];
		$user_ship[mine_rate_metal] = $user_ship[mine_rate_fuel];
		$user_ship[mine_rate_fuel] = $temp4854;
		$error_str .= "Mining Rates for fleet switched, at a cost of <b>$total_cost[cost]</b> Credits. Total of <b>$total_cost[num]</b> ships affected.<p>";
	}

}

// checks
if(isset($buy)) {
	if($buy == 1) {
		if($amount < 1) {
			$def = floor($user[cash] / $fighter_cost);
			if($def > $user_ship[max_fighters] - $user_ship[fighters]) {
				$def = $user_ship[max_fighters] - $user_ship[fighters];
			}
			get_var('Buy Fighters',$filename,'How many fighters do you want to buy?','amount',$def);
		} else {
			if($user[cash] < $amount * $fighter_cost) {
				$error_str .= "You can not afford that many fighters.<p>";
			} elseif($user_ship[fighters] + $amount > $user_ship[max_fighters]) {
				$error_str .= "Your ship can not hold that many more fighters.<p>";
			} else {
				$error_str .= "<b>$amount</b> fighters purchased for <b>". $amount * $fighter_cost ."</b> Credits. <p>";
	take_cash($amount * $fighter_cost);
	dbn(__FILE__,__LINE__,"update ${db_name}_ships set fighters = fighters + '$amount' where ship_id = $user_ship[ship_id]");
				$user_ship[fighters] += $amount;
			}
		}
	} elseif($buy == 2) { // shields
		if($amount < 1) {
			$def = floor($user[cash] / $shield_cost);
			if($def > $user_ship[max_shields] - $user_ship[shields]) {
				$def = $user_ship[max_shields] - $user_ship[shields];
			}
			get_var('Buy Shields',$filename,'How many shields do you want to buy?','amount',$def);
		} else {
			if($user[cash] < $amount * $shield_cost) {
				$error_str .= "You can not afford that many shields.<p>";
			} elseif($user_ship[shields] + $amount > $user_ship[max_shields]) {
				$error_str .= "Your ship can not hold that many more shields.<p>";
			} else {
				$error_str .= "<b>$amount</b> shields purchased for <b>". $amount * $shield_cost ."</b> Credits.<p>";
	take_cash($amount * $shield_cost);
	dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = shields + '$amount' where ship_id = $user_ship[ship_id]");
				$user_ship[shields] += $amount;
			}
		}

	} elseif($buy == 6) { // gamma bomb
		if($sure != 'yes') {
			get_var('Buy Gamma Bomb',$filename,'Are you sure you want to buy a gamma bomb?','sure','');
		} else {
			if($user[cash] < $bomb_cost) {
				$error_str .= "You can not afford a gamma bomb.<p>";
			} elseif (($flag_bomb) || ($user[login_id] ==1)) {
				$error_str .= "Gamma bomb purchased for <b>$bomb_cost</b> Credits.<p>";
		take_cash($bomb_cost);
	dbn(__FILE__,__LINE__,"update ${db_name}_users set gamma = gamma + 1 where login_id = $user[login_id]");
		} else {
			$error_str .= "Admin has disabled the purchasing of Bombs. However if you have one you can still use it.<p>";
			}
		}
	} elseif($buy == 7) { // alpha bomb
		if($sure != 'yes') {
			get_var('Buy Alpha Bomb',$filename,'Are you sure you want to buy a Alpha bomb?','sure','');
		} else {
			if($user[cash] < $bomb_cost) {
				$error_str .= "You can not afford a Alpha bomb.<p>";
			} elseif (($flag_bomb) || ($user[login_id] ==1)) {
				$error_str .= "Alpha bomb purchased for <b>$bomb_cost</b> Credits.<p>";
		take_cash($bomb_cost);
	dbn(__FILE__,__LINE__,"update ${db_name}_users set alpha = alpha + 1 where login_id = $user[login_id]");
		} else {
			$error_str .= "Admin has disabled the purchasing of Bombs. However if you have one you can still use it.<p>";
			}
		}
	} elseif($buy == 10){
	$taken = 0; //Fighters taken from planet so far.
	$ship_counter = 0;
	db(__FILE__,__LINE__,"select sum(max_fighters-fighters), count(ship_id) from ${db_name}_ships where location=$user[location] && login_id='$user[login_id]' && max_fighters > 0 && fighters < max_fighters");
	$maths=dbr();
		if($user[cash] < $fighter_cost){
		print_page("Failed","You don't have enough money for one fighter, let alone a fleet of them.<br />Come back when you can afford it");
		} elseif(!$maths[0]) {
		print_page("Failed","This operation failed as there are no ships that have fighter bays empty in this system that belong to you.");
		} elseif($sure != "yes") {
		get_var('Load all ships','dock_equip_shop.php',"There are <b>$maths[0]</b> empty fighter bays in <b>$maths[1]</b> ships in this system. <br />Do you want to fill as many as you can afford to fill?",'sure','yes');
		} else {
			db2(__FILE__,__LINE__,"select ship_id,fighters,max_fighters,ship_name from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && max_fighters > 0 && fighters < max_fighters order by max_fighters desc");
			while($ships = dbr2()) {
				//player can load ship.
				$free = $ships[max_fighters] - $ships[fighters];
				if($user[cash] >= ($free * $fighter_cost)) {
					$ship_counter++;
					dbn(__FILE__,__LINE__,"update ${db_name}_ships set fighters = max_fighters where ship_id = '$ships[ship_id]'");
					$out .= "<br /><b class=b1>$ships[ship_name]</b> had its fighter cargo increased by <b>$free</b> to maximum capacity.";
					if($ships[ship_id] == $user_ship[ship_id]){
						$user_ship[fighters] = $user_ship[max_fighters];
					}
					$taken += $free;
					take_cash($free*$fighter_cost);
				//player will run out of cash.
				} else {
					$ship_counter++;
					$t868 = $ships[fighters] + floor($user[cash]/$fighter_cost);
					dbn(__FILE__,__LINE__,"update ${db_name}_ships set fighters = '$t868' where ship_id = '$ships[ship_id]'");
					if($ships[ship_id] == $user_ship[ship_id]){
						$user_ship[fighters] = $t868;
					}
					$taken += $t868 - $ships[fighters];
					$q_m = ($t868 - $ships[fighters]) *$fighter_cost;
					$out .= "<br /><b class=b1>$ships[ship_name]</b>s fighter count was increased to <b>$t868</b>.";
					take_cash($q_m);
					break;
				}
			}
		if($ship_counter > 0){
			$cost=$taken*$fighter_cost;
			print_page("Fighters Loaded","<b>$ship_counter</b> ships had their fighters augmented by new fighters from Sol.<br />Total New Fighters = <b>$taken</b>; Cost = <b>$cost</b><p>More Detailed Statistics :".$out);
		} else {
			print_page("No Ships","No ships where loaded as all ships in this system are already full of fighters.");
		}
		}
	}elseif($buy == 11){
	$taken = 0; //Shields taken from planet so far.
	$ship_counter = 0;
	db(__FILE__,__LINE__,"select sum(max_shields-shields), count(ship_id) from ${db_name}_ships where location=$user[location] && login_id='$user[login_id]' && max_shields > 0 && shields < max_shields");
	$maths2=dbr();
		if($user[cash] < $shield_cost){
		print_page("Failed","You don't have enough money for a shield charge, let alone a fleet of them.<br />Come back when you can afford it");
		} elseif(!$maths2[0]) {
			print_page("Failed","This operation failed as there are no ships that have shield generators uncharged in this system that belong to you.");
		} elseif($sure != "yes") {
		get_var('Load all ships','dock_equip_shop.php',"There are <b>$maths2[0]</b> empty shield charges in <b>$maths2[1]</b> ships in this system. <br />Do you want to fill as many as you can afford to fill?",'sure','yes');
		} else {
			db2(__FILE__,__LINE__,"select ship_id,shields,max_shields,ship_name from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && max_shields > 0 && shields < max_shields order by max_shields desc");
			while($ships = dbr2()) {
				//player can load ship.
				$free2 = $ships[max_shields] - $ships[shields];
				if($user[cash] >= ($free2 * $shield_cost)) {
					$ship_counter++;
					dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = max_shields where ship_id = '$ships[ship_id]'");
					$out .= "<br /><b class=b1>$ships[ship_name]</b> had its shields chrage increased by <b>$free2</b> to maximum capacity.";
					if($ships[ship_id] == $user_ship[ship_id]){
						$user_ship[shields] = $user_ship[max_shields];
					}
					$taken += $free2;
					take_cash($free2*$shield_cost);
				//player will run out of cash.
				} else {
					$ship_counter++;
					$t868 = $ships[shields] + floor($user[cash]/$shield_cost);
					dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = '$t868' where ship_id = '$ships[ship_id]'");
					if($ships[ship_id] == $user_ship[ship_id]){
						$user_ship[shields] = $t868;
					}
					$taken += $t868 - $ships[shields];
					$q_m = ($t868 - $ships[shields]) *$shield_cost;
					$out .= "<br /><b class=b1>$ships[ship_name]</b>s shield charge increased to <b>$t868</b>.";
					take_cash($q_m);
					break;
				}
			}
		if($ship_counter > 0){
			$cost=$taken*$shield_cost;
			print_page("Shields Charged","<b>$ship_counter</b> ships had their shields charged by shield charges from Sol.<br />Total New shields = <b>$taken</b>; Cost = <b>$cost</b><p>More Detailed Statistics :".$out);
		} else {
			print_page("No Ships","No ships where loaded as all ships in this system are already full of shields.");
		}
		}
	}
}

$error_str .= Create_PageTitleBlock("Starport Equipment Store");

$error_str .= "<p>Equipment Shop:";
$error_str .= "<p>Ship Stuff:";
$error_str .= "<br /><a href=$filename?buy=1>Fighters</a>: $fighter_cost each - <a href=$filename?buy=10>Fill Fleet</a>";
$error_str .= "<br /><a href=$filename?buy=2>Shields</a>: $shield_cost each - <a href=$filename?buy=11>Fill Fleet</a>";
if($alternate_play_1 == 1){
	$error_str .= "<br /><a href=$filename?switch=1>Mining Switcher</a> $mining_switch_cost - <a href=$filename?mass_switch=1>Switch Fleet</a>";
}

if (($flag_bomb) || ($user[login_id] ==1)) {
	$error_str .= "<p>Explosive Stuff:";
	$error_str .= "<br /><a href=$filename?buy=7>Alpha Bomb</a>: " . number_format($bomb_cost);
	$error_str .= "<br /><a href=$filename?buy=6>Gamma Bomb</a>: " . number_format($bomb_cost);
}

$error_str .= "<p><a href=help.php?equip=1 target=_blank>Information about equipment</a>";
$rs = "<p><a href=port.php>Return to Starport</a>";

print_page("Equipment Shop",$error_str);
?>