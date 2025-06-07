<?php
#page used to hold random event related functions (save on processing time in non-random event games).
//require_once("user.inc.php");


#function to check if user has just run into random event. Called from location.php (transwarp/subspace/normal moving)
function random_event_checker($star,$user,$autowarp) {
	global $db_name, $turns_safe, $rs;

	if($star[event_random] == 1) {
		if ($user[turns_run] < $turns_safe) {

$ret_str = "<br />
<table cellspacing=\"0\" cellpadding=\"2\" class=\"all\" width=\"100%\" align=\"center\">
	<tr>
		<td style=\"text-align: center;\" rowspan=\"2\">
			<img src=\"images/sys_type/blackhole.gif\" border=\"0\" width=\"65\" height=\"65\" alt=\"".$sys_type_array['name']."\" title=\"".$sys_type_array['name_v']."\">
		</td>
		<td style=\"text-align: center;\">
				<span class=\"h5\">
						Star System (#$user[location])
					<br /><b class=\"b1\">
						$star[star_name]
					</b>
				</span><br /><br />
";
			$ret_str .= "<p>Warning! Warning! <b class=b1>Black hole</b> Warning! Warning!";
			$ret_str .= "<br>Only your newbie status saved you from a grisly affair. Next time you mightn't be so lucky.";
			$ret_str .= "<div align=\"center\"><p>Warp: |";
			if($star[link_1]) {
				db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_1]");
				$new_star = dbr3();
				$link_num2 = $new_star['star_name'];
				$ret_str .= " <a href=location.php?toloc=$star[link_1]>(SS #$star[link_1]) - $link_num2</a> |";
			}
			if($star[link_2]) {
				db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_2]");
				$new_star = dbr3();
				$link_num2 = $new_star['star_name'];
				$ret_str .= " <a href=location.php?toloc=$star[link_2]>(SS #$star[link_2]) - $link_num2</a> |";			}
			if($star[link_3]) {
				db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_3]");
				$new_star = dbr3();
				$link_num2 = $new_star['star_name'];
				$ret_str .= " <a href=location.php?toloc=$star[link_3]>(SS #$star[link_3]) - $link_num2</a> |";			}
			if($star[link_4]) {
				db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_4]");
				$new_star = dbr3();
				$link_num2 = $new_star['star_name'];
				$ret_str .= "<br>| <a href=location.php?toloc=$star[link_4]>(SS #$star[link_4]) - $link_num2</a> |";			}
			if($star[link_5]) {
				db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_5]");
				$new_star = dbr3();
				$link_num2 = $new_star['star_name'];
				$ret_str .= " <a href=location.php?toloc=$star[link_5]>(SS #$star[link_5]) - $link_num2</a> |";			}
			if($star[link_6]) {
				db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_6]");
				$new_star = dbr3();
				$link_num2 = $new_star['star_name'];
				$ret_str .= " <a href=location.php?toloc=$star[link_6]>(SS #$star[link_6]) - $link_num2</a> |";			}
			if($autowarp) {
				$path_str = str_replace("+", " ", $autowarp);
				$autowarp_path = array();
				$autowarp_path = explode(" ", $path_str);
				//$ret_str .= "<br />Path_str is $path_str";
				$next_sector = array_shift($autowarp_path);
				//$ret_str .= " Next Sector is $next_sector";
				if($next_sector && ($next_sector == $star[link_1] || $next_sector == $star[link_2] || $next_sector == $star[link_3] || $next_sector == $star[link_4] || $next_sector == $star[link_5] || $next_sector == $star[link_6])) {
					$temp328 = implode($autowarp_path, "\x2B");
					if($temp328){
						$ret_str .= "<br />AutoWarp to Next System: &lt;<a href=location.php?toloc=$next_sector&autowarp=$temp328>$next_sector</a>&gt;";
					} else {
						$ret_str .= "<br />AutoWarp to Next System: &lt;<a href=location.php?toloc=$next_sector>$next_sector</a>&gt;";
					}
				}
			}
			$ret_str .= "</div>";
			$rs = '';
			print_page( "Black Hole", $ret_str );
		} else {
			black_hole($user,$star);
		}

	//player runs into nebula
	} elseif ($star[event_random] == 2) {
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = 0 where login_id = $user[login_id] && location = $user[location] && $user[login_id] != 1");
		$user_ship[shields] = 0;

	//Solar Storm
	} elseif ($star[event_random] == 12) {
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set shields = 0 where login_id = '$user[login_id]' && location = '$user[location]' && '$user[login_id]' != 1");
		$user_ship[shields] = 0;

	//end random things
	}

}

# -----------------------------------------
# Other functions


//Black Hole
// Maug - Hope you don't mind this re-write.
// Previous function didn't account for fleet locations
// Sack the space monkey
		//Hades, space monkeys are very stubborn :)
function black_hole($user,$star, $multi = 0) {
global $db_name,$user_ship;

$bh_text .= "<br />
<table cellspacing=\"0\" cellpadding=\"2\" class=\"all\" width=\"55%\" align=\"center\">
	<tr>
		<td style=\"text-align: center;\" rowspan=\"2\">
			<img src=\"images/sys_type/blackhole.gif\" border=\"0\" width=\"65\" height=\"65\" alt=\"".$sys_type_array['name']."\" title=\"".$sys_type_array['name_v']."\">
		</td>
		<td style=\"text-align: center;\">
				<span class=\"h5\">
						Star System (#$user[location])
					<br /><b class=\"b1\">
						$star[star_name]
					</b>
				</span><br /><br />
";


// print the out the warp links
$bh_text .= "Warp: ";
$bh_text .= " Star Chart Failure";

if(!isset($autowarp))
{
	$bh_text .= "<br />AutoWarp Failure";
}
else
{
	$bh_text .= "<br />Autowarp Failure";
}


if($star['wormhole'])
{
	$bh_text .= "<br />Wormhole to : Star Chart Failure
		</td>
	</tr>
</table>";
}
else
{
	$bh_text .= "
		</td>
	</tr>
</table>";
}

	$bh_text .= "<p>Warning! Warning! System <b>$star[star_id]</b> had a Black Hole in it. <br />You were nearly pulled in, but you managed to escape in the nick of time.<br />However, whilst escaping you were flung to another star system, and took varying degrees of damage to all ships you were towing.</p>";

	// Get fleets in system owned by player
	db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where location = $user[location] AND login_id = $user[login_id]");

	while($fleet = dbr()) {
		// Set fleet location
		$rand_star = safe_rand_star($user_ship['location']);
		dbn(__FILE__,__LINE__,"update ${db_name}_fleets set location = $rand_star where fleet_id = $fleet[fleet_id]");

		// Get ships in current fleet
		$ADODB_FETCH_MODE = 2;
		db2(__FILE__,__LINE__,"select ship_id,shields,fighters,ship_name from ${db_name}_ships where fleet_id = $fleet[fleet_id]");

		while($f_ship = dbr2()) {
			// Set ship location same as fleet
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set location = $rand_star where ship_id = $f_ship[ship_id]");
			if($f_ship['ship_id'] == $user['ship_id']) {
				dbn(__FILE__,__LINE__,"update ${db_name}_users set location = $rand_star where login_id = $user[login_id]");
			}

			// Calculate and apply damage
			$totaldefs = $f_ship[shields] + $f_ship[fighters];

			if($totaldefs > 9) {
				$damtodo = round((($totaldefs /100) * 5) * $multi);
				$damtodo2 = $damtodo;
				$shield_damage = $damtodo;
				if($shield_damage > $f_ship['shields']) {
					$shield_damage = $f_ship['shields'];
				}
				$damtodo -= $shield_damage;

				dbn(__FILE__,__LINE__,"update ${db_name}_ships set fighters = fighters - $damtodo, shields = shields - $shield_damage where ship_id = '$f_ship[ship_id]'");
				$bh_text .= "<br />The <b class=b1>$f_ship[ship_name]</b> took <b>$damtodo2</b> damage and was thrown to system #<b>$rand_star</b>.";
			}
		}
	}

	post_news("Mayday, Mayday. Am <b class=b1>$user[login_name]</b>. Have found a black hole in syste ...... *crackle* ..... Need help.... *static*");

	print_page("Location",$bh_text);
}

// function to generate a random star system which not the current location nor another random event affected system
// credit: Hades
function safe_rand_star($current=0) {
	global $db_name;

	// Get safe systems from database
	db3(__FILE__,__LINE__,"select star_id from ${db_name}_stars where event_random <> 1 AND star_id <> $current");

	$ar_star = array();
	while($total = dbr3()) {
		$ar_star[] = $total['star_id'];
	}
	$rand_star = mt_rand(2,count($ar_star));
	return $ar_star[$rand_star];
}


// This is the minimum amount of fuel a Nebula is allowed to have
function nebula_min_fuel() {
	global $rr_fuel_chance_max, $uv_fuel_max, $day_maint_count;

	// Max possible from universe gen plus 1 week's max regen
	$min = $uv_fuel_max + ($rr_fuel_chance_max * $day_maint_count * 7);
	if ($min < 50000) {
		$min = 50000;   // Must be at least 50k
	} elseif ($min > 500000) {
		$min = 500000;  // But not over 500k
	}
	return $min;
}

// This is the minimum amount of metal a Mining Rush is allowed to have
function miningrush_min_metal() {
	global $rr_metal_chance_max, $uv_metal_max, $day_maint_count;

	// Max possible from universe gen plus 1 week's max regen
	$min = $uv_metal_max + ($rr_metal_chance_max * $day_maint_count * 7);
	if ($min < 50000) {
		$min = 50000;   // Must be at least 50k
	} elseif ($min > 500000) {
		$min = 500000;  // But not over 500k
	}
	return $min;
}

?>