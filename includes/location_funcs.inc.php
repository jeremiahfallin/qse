<?php
/*
//
File:			location_funcs.inc.php
Objective:		functions used in location.php
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	15 October 2003		Date Modified:	26 March 2004

Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

/*
//
These functions are mainly those parts of the location page, handling standard output html.
Segregation here decreases the overall location.php size in terms of code to read, and enables
greater focus on the actual page structure. Also includes code used in certain WHILE loops for
the ship listings.
//
*/

function print_link($link_num) {
	global $error_str,$db_name;
	if($link_num)
	{
		db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $link_num");
		$new_star = dbr3();
		$link_num2 = $new_star['star_name'];

		$error_str .= "&nbsp;&nbsp;<a href=\"location.php?toloc=$link_num\">(SS #$link_num) - $link_num2</a>&nbsp;&nbsp;|";
	}
}
function print_link2($link_num) {
	global $error_str,$db_name;
	if($link_num)
	{
		db3(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $link_num");
		$new_star = dbr3();
		$link_num2 = $new_star['star_name'];

		$error_str .= "&nbsp;&nbsp;<br>|&nbsp;&nbsp;<a href=\"location.php?toloc=$link_num\">(SS #$link_num) - $link_num2</a>&nbsp;&nbsp;|";
	}
}


// function to add sys type image alt/title text
function GrabSystemType($systype)
{
	if($systype == 0)
	{
		$name = "Normal Space"; $name_v = "Normal Space: Visibility 100%";
	}
	elseif($systype == 1)
	{
		$name = "Gas Nebula"; $name_v = "Gas Nebula: Visibility 95%";
	}
	elseif($systype == 2)
	{
		$name= "Dust Cloud"; $name_v = "Dust Cloud: Visibility 90%";
	}
	elseif($systype == 3)
	{
		$name = "Radiation Belt"; $name_v = "Radiation Belt: Visibility 85%";
	}
	else
	{
		$name = "Stellar Debris"; $name_v = "Stellar Debris: Visibility 80%";
	}
	$systype_array = array();
	$systype_array['name'] = $name;
	$systype_array['name_v'] = $name_v;
	return $systype_array;
}


function search_links($star,$link_num) {
	if($star['link_1'] == $link_num) {
		return 0;
	} elseif($star['link_2'] == $link_num) {
		return 0;
	} elseif($star['link_3'] == $link_num) {
		return 0;
	} elseif($star['link_4'] == $link_num) {
		return 0;
	} elseif($star['link_5'] == $link_num) {
		return 0;
	} elseif($star['link_6'] == $link_num) {
		return 0;
	} elseif($star['wormhole'] == $link_num) {
		return 0;
	}
	return 1;
}


function SystemInfo() {
	global $user_ship, $user, $alternate_play, $star, $flag_research, $flag_bmrkt, $random_events, $db_name,$user_options;
	$system_text = array();
	$error_str = "";
	$error_str .= "
	<table cellspacing=\"0\" cellpadding=\"2\" class=\"all\" width=\"100%\" align=\"center\" style=\"border-top: 0px;\">
		<tr>
			<td>
				<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">
					<tr>
						<td>";

		$mine_str .= "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">";
		$mine_str .= "<tr>";
	if($star['metal']) {
	$mine_str .= "<td align=center>";
if($user_options['show_pics']){
		$mine_str .= "<a href=\"mining.php?overview=1\">"
			. "<img src='images/lrg_met.gif' width=50 height=50 border=0 alt=\"Metal\"></img></a><br>";
}
		$mine_str .= "Metals: <b>$star[metal]</b>";
		if($user_ship['mine_rate'] > 0 && $alternate_play == 0){
			if($user_ship['mine_mode'] == 1) {
				$mine_str .= " - (Current Ship Mining) - <a href=\"mining.php?overview=1\">Mining Menu</a><br />";
			} else {
				$mine_str .= " - <a href=\"mining.php?overview=1\">Mining Menu</a><br />";
			}
		} else {
			if($user_ship['mine_mode'] == 1) {
				$mine_str .= " - (Current Ship Mining) - <a href=\"mining.php?overview=1\">Mining Menu</a><br />";
			} else {
				$mine_str .= " - <a href=\"mining.php?overview=1\">Mining Menu</a><br /></td>";
			}
		}
	}
	if($star['fuel']) {
		$mine_str .= "<td align=center>";
if($user_options['show_pics']){
		$mine_str .= "<a href=\"mining.php?overview=1\">"
			. "<img src='images/lrg_fuel.jpg' width=50 height=50 border=0 alt=\"Fuel\"></img></a><br>";
}
		$mine_str .= "Fuel: <b>$star[fuel]</b>";
		if($user_ship['mine_rate'] > 0 && $alternate_play == 0){
			if($user_ship['mine_mode'] == 2) {
				$mine_str .= " - (Current Ship Mining) - <a href=\"mining.php?overview=1\">Mining Menu</a><br />";
			} else {
				$mine_str .= " - <a href=\"mining.php?overview=1\">Mining Menu</a><br />";
			}
		} else {
			if($user_ship['mine_mode'] == 2) {
				$mine_str .= " - (Current Ship Mining) - <a href=\"mining.php?overview=1\">Mining Menu</a><br />";
			} else {
				$mine_str .= " - <a href=\"mining.php?overview=1\">Mining Menu</a><br /></td>";
			}
		}
	}

	// Control of whether dark matter is shown or not is currently based on if the current ship can mine it
	// This should be changed to if any ship in this location can mine it. Then mining overview should be
	// changed so the darkmatter mining radio buttons only show up for ships that can mine the darkmatter.
	$show_darkmatter = $user_ship['mine_rate_darkmatter'] > 0 ? 1 : 0;

    if($star['darkmatter'] && $show_darkmatter) {
            $mine_str .= "<td align=center>";
if($user_options['show_pics']){
		$mine_str .= "<a href=\"mining.php?overview=1\">"
			. "<img src='images/lrg_dm.jpg' width=50 height=50 border=0 alt=\"Darkmatter\"></img></a><br>";
}
        $mine_str .= "
            Dark-Matter: $star[darkmatter]";
            if($user_ship['mine_rate_darkmatter'] > 0 || (($user_ship['mine_rate_metal'] > 0 || $user_ship['mine_rate_fuel'] > 0) && $alternate_play == 0)){
                if($user_ship['mine_mode'] == 5) {
                    $mine_str .= " - (Currently Mining) - <a href=\"mining.php?overview=1\">Mining Menu</a>
";
                } else {
                    $mine_str .= " - <a href=\"mining.php?overview=1\">Mining Menu</a>
";
                }
            } else {
                $mine_str .= " - <a href=\"mining.php?overview=1\">Mining Menu</a>
\n\n</td>";
            }
    }


	//Darkmatter addition

	if((!$star['darkmatter']) && (!$star['fuel']) && (!$star['metal'])){
	$mine_str .= "<td align=center><br><em>No mining resources detected.</em><br><br></td>";
	}
	$mine_str .= "</tr></table>";
	if(isset($mine_str)) {
		$error_str .= $mine_str;
	} else {
//		$error_str .= "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=center><tr><td><em>No mining resources detected.</em></tr></td></table>";
	}

	$error_str .= "
						</td>
					</tr>
				</table>
			</td>";
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
		$mr = dbr();
		$max_races = $mr['value'];
	// ports
	db(__FILE__,__LINE__,"select port_id from ${db_name}_ports where location = '$user[location]'");
	while($port = dbr()) {
		if($port['port_id'] == 1) {$fd = " width=33%";}
		$temp2_str = "";
if($user_options['show_pics']){
		$temp2_str .= "
			<td align=\"center\" valign=top width=33%>
				<a href=\"port.php?port_id=$port[port_id]\">
					<img src=\"images/starport.jpg\" border=\"0\" height=\"51\" alt=\"Starport $port[port_id]\" title=\"Starport $port[port_id]\" />
				</a><br><b class=\"red_txt\">Starport $port[port_id]</b>
			</td>";
} else {
		$temp2_str .= "
			<td align=\"center\" valign=top width=33%>
				<a href=\"port.php?port_id=$port[port_id]\">
					<br><b class=\"red_txt\">Starport $port[port_id]</b></a>
			</td>";
}


	}



	// blackmarkets
	if($flag_bmrkt){
		$bm_t['0'] = "black_market.php";
		$bm_t['1'] = "bm_ships.php";
		$bm_t['2'] = "bm_upgrades.php";
		$bm_t['3'] = "bm_bombs.php";

		db(__FILE__,__LINE__,"select bmrkt_id,bm_name,bmrkt_type from ${db_name}_bmrkt where location = '$user[location]'");
		$bmrkt = dbr();
		if($bmrkt){
			while($bmrkt) {

	if($user_options['show_pics']) {
				$temp2_str .= "
			<td align=\"center\" valign=\"top\">
				<a href=\"".$bm_t[$bmrkt['bmrkt_type']]."?bmrkt_id=$bmrkt[bmrkt_id]\"><img src=\"images/blackmarket.jpg\" border=\"0\" height=\"51\" alt=\"Blackmarket $bmrkt[bmrkt_id]\" title=\"Blackmarket $bmrkt[bmrkt_id]\" /></a><br /><b class=\"red_txt\">$bmrkt[bm_name]<b>
			</td>";
			} else {
				$temp2_str .= "
			<td align=\"center\" valign=\"top\">
				<a href=\"".$bm_t[$bmrkt['bmrkt_type']]."?bmrkt_id=$bmrkt[bmrkt_id]\"><br /><b class=\"red_txt\">$bmrkt[bm_name]<b></a>
			</td>";
			}
				$bmrkt = dbr();
			}
		}
	}
        if($flag_bmrkt){
                db(__FILE__,__LINE__,"select * from ${db_name}_shipyards where location = '$user[location]'");
                $asyrd = dbr();
                if($asyrd){
                        while($asyrd) {

        if($user_options['show_pics']) {
                                $temp2_str .= "
                        <td align=\"center\" valign=\"top\">
                                <a href=\"shipyard.php?asyrd_id=$asyrd[asyrd_id]\"><img src=\"images/station.gif\" border=\"0\" height=\"51\" alt=\"Shipyards $asyrd[asyrd_id]\" title=\"Shipyards $asyrd[asyrd_id]\" /></a><br /><b class=\"red_txt\">Alien Shipyards #$asyrd[asyrd_id]<b>
                        </td>";
                        } else {
                                $temp2_str .= "
                        <td align=\"center\" valign=\"top\">
                                <a href=\"shipyard.php?asyrd_id=$asyrd[asyrd_id]\"><br /><b class=\"red_txt\">Alien Shipyards #$asyrd[asyrd_id]<b></a>
                        </td>";
                        }
                                $asyrd = dbr();
                        }
                }
        }
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
		$teams = dbr();
		$era = $teams['value'];
		db2(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'Homeworld'");
		$hwc = dbr2();
		$hwc2 = $hwc['value'];


	if ($user['location'] <= $max_races && $random_events != 0) {
	if($user_options['show_pics']) {
		$temp2_str .= "
			<td align=\"center\" valign=\"top\" width=\"33%\">
				<a href=\"science.php\"><img src=\"images/sport.jpg\" border=\"0\" height=\"50\" alt=\"Sol Observatory\" title=\"Sol Observatory\" /></a><br /><b class=\"red_txt\">Observatory</b>
			</td>";
	} else {
		$temp2_str .= "
			<td align=\"center\" valign=\"top\" width=\"33%\">
				<a href=\"science.php\"><br /><b class=\"red_txt\">Observatory</b></a>
			</td>";	}
	}

	//To create teh homeworlds
	db(__FILE__,__LINE__,"select * from races where era = $era && race_ID = '$user[location]'");
	$races1 = dbr();
		db3(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
		$race = dbr3();
		$era = $race['value'];
		if ($user['location'] <= $max_races && $hwc2 == 1) {
	if($user_options['show_pics']) {
		$temp2_str .= "
			<td align=\"center\" valign=\"top\" width=\"33%\">
				<a href=\"hw.php\"><img src=\"images/era/$era/planet_s".$user['location'].".gif\" border=0 height=50 alt=\"Homeworld: $races1[planet_name] \" title=\"Homeworld: $races1[planet_name]\" /></a><br /><b class=\"red_txt\"><font color=#FF0000>Homeworld</font><br><a href=hw.php> $races1[planet_name]</a></b>
			</td>";
	} else {
		$temp2_str .= "
			<td align=\"center\" valign=\"top\" width=\"33%\">
				<br /><b class=\"red_txt\"><font color=#FF0000>Homeworld</font><br><a href=hw.php> $races1[planet_name]</a></b>
			</td>";	}
	}

	if(isset($fac_str)) {
		$error_str .= $fac_str;
	}

	$error_str .= "
		</tr>
	</table>";

	$system_text['str'] = $error_str;
	$system_text['fac'] = $temp2_str;
	return $system_text;
}




// Function to create the column structure for planets

function PlanetInfo($planets) {
	global $hostile_planets, $user, $flag_planet_attack, $user_ship_config, $enable_superweapons, $login_id, $db_name, $user_options;

	$planet_info = array();
		db4(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'Homeworld'");
		$hwc = dbr4();
		$hwc2 = $hwc['value'];
		db2(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
		$mr = dbr2();	// first off we prepare all user planets
		$max_races = $mr['value'];


		if($planets['barren'] == 1){
		$planet_type_img = "sb";
		} else {
		$planet_type_img = "sp";
		}

	if($planets['owner_id'] == $user['login_id']){
		if($planets['fighter_set'] == 1 && $hostile_planets != 0)
		{
			$danger = " hostile ";
		}
		elseif($planets['fighter_set'] == 2 && $hostile_planets != 0)
		{
			$danger = " super hostile ";
		}
		elseif($hostile_planets != 0)
		{
			$danger = " passive ";
		}
		else
		{
			$danger = " ";
		}
				$planet_info['temp2'] .= "
			<td align=\"center\" valign=\"top\">
				<a href=\"planet.php?planet_id=$planets[planet_id]\">";
	if($user_options['show_pics']) {
$planet_info['temp2'] .= "<img src=\"images/planets/$planet_type_img".$planets['planet_img'].".gif\" border=\"0\" width=\"51\" height=\"51\" alt=\"Planet $planets[planet_name]\" title=\"Planet $planets[planet_name]\" />
				</a>";
				}
$planet_info['temp2'] .= "<br /><font color=009900>Planet:</font> <b class=\"b1\">$planets[planet_name]</b>
				<br />(w/ <b>$planets[fighters]</b> $danger fighters)
				<br />Owned By: <font color=#00ff00><strong>You</strong></font>

			</td>";
	}
	else // next come the non-user planets
	{

		// check if planet is BARREN
		if($planets['barren'] != 1)
		{
				$link1 = "<a href=\"planet.php?planet_id=$planets[planet_id]\">"; ///note///
				$link2 = "</a>";
			$planet_info['temp'] .= "
			<td align=\"center\" valign=\"top\">
				";
	if($user_options['show_pics']) {
$planet_info['temp'] .= $link1."<img src=\"images/planets/$planet_type_img".$planets['planet_img'].".gif\" border=\"0\" width=\"51\" height=\"51\" alt=\"Planet $planets[planet_name]\" title=\"Planet $planets[planet_name]\" />".$link2."<br /><font color=\"009900\">Planet:</font> <b class=\"b1\" style=\"font-weight: bold\">$planets[planet_name]</b>";
} else {
$planet_info['temp'] .= $link1."<br /><font color=\"009900\">Planet:</font> <b class=\"b\" style=\"font-weight: bold\">$planets[planet_name]</b>";
}

		} else{
	if($user_options['show_pics']) {
			$planet_info['temp'] .= "
			<td align=\"center\" valign=\"top\">
				<img src=\"images/planets/$planet_type_img".$planets['planet_img'].".gif\" border=\"0\" width=\"51\" height=\"51\" alt=\"Planet $planets[planet_name]\" title=\"Planet $planets[planet_name]\" />
				<br /><font color=\"FF6600\">Barren World:</font> <b class=\"b1\">$planets[planet_name]</b>";
		} else {
			$planet_info['temp'] .= "
			<td align=\"center\" valign=\"top\">
				<br /><font color=\"FF6600\">Barren World:</font> <b class=\"b1\">$planets[planet_name]</b>";
		}

	}

		if($planets['barren'] != 1) {
			$planet_info['temp'] .= "
				<br />(w/ <b>$planets[fighters]</b>";

			if($planets['fighter_set'] == 1 && $hostile_planets != 0){
				$planet_info['temp'] .= " hostile ";
			}elseif($planets['fighter_set'] == 2 && $hostile_planets != 0){
				$planet_info['temp'] .= " super hostile ";
			} elseif($hostile_planets != 0) {
				$planet_info['temp'] .= " passive ";
			} else {
				$planet_info['temp'] .= " ";
			}
			$planet_info['temp'] .= "fighters) ";
if($planets['clan_id'] == $user['clan_id'] && $planets['clan_id'] != 0 && $planets['unigen'] == 0){
			$planet_info['temp'] .= "<br><b>Clan Planet</b>: Owned By: <br><font color=red><strong>$planets[owner_name]</strong></font>";
		}
if($planets['clan_id'] != $user['clan_id'] && $planets['unigen'] == 0){
			$planet_info['temp'] .= "<br>Owned By: <br> <font color=yellow><strong>$planets[owner_name]</strong></font>";
}

			if(($planets['owner_id'] == $user['login_id']) || ($planets['clan_id'] == $user['clan_id'] && $planets['clan_id'] != 0) || ($user['login_id'] == 1) || ($planets['fighters'] == 0 && $planets['barren'] != 1))
			{
//				$planet_info['temp'] .= "";
				//<br /><a href=planet.php?planet_id=$planets[planet_id]>Land</a><br />"; ///note///
			}
			else
			{
				if($flag_planet_attack){
					$planet_info['temp'] .= "<br /><a href=\"planet.php?planet_id=$planets[planet_id]\">Attack</a>";
					if($user_ship_config['sv']) {
						$planet_info['temp'] .= "<br /><a href=\"attack.php?quark=1&amp;planet_num=$planets[planet_id]\">Fire Quark Displacer</a>";
					}
					if($user_ship_config['ob']) {
						$planet_info['temp'] .= "<br /><a href=\"attack.php?orbit=1&amp;planet_num=$planets[planet_id]\">Orbital Bombardment</a>";
					}
					if($user_ship_config['md']) {
						$planet_info['temp'] .= "<br /><a href=\"attack.php?driver=1&amp;planet_num=$planets[planet_id]\">Launch Mass Drivers</a>";
					}
					if(isset($user_ship_config['sw']) && $enable_superweapons == 1) {
						$planet_info['temp'] .= "<br /><a href=\"attack.php?terra=1&amp;planet_num=$planets[planet_id]\">Fire Terra Maelstrom</a>";
					}
					if($planets['pass'] != '0') { //watch password disable resets to Zero, not blank text!
						$planet_info['temp'] .= "<br /><a href=\"planet.php?planet_id=$planets[planet_id]&amp;want_access=1\">Have Password</a>";
					}
				}
			}

		} elseif ($planets['barren'] == 1) {
			if($flag_planet_attack && $planets['fighters'] > 0){
			}
			}
		}
		$planet_info['temp'] .= "</td>"; //the closing column tag

	return $planet_info;
}







function UserShip_FullList($ships) {
	global $user, $user_options, $user_ship, $count, $db_name, $fleet_name_array;

			$ships['ship_name'] = stripslashes($ships['ship_name']);
			$error_str .= "
							<tr>
			";
			// IF SHOW ABBRIVIATED SHIP CLASSES IS SET, THEN...
			if($user_options['show_abbr_ship_class'] == 1)
			{
				//// we have cellpadding - drop the <td width=2> column
				$error_str .= "
								<td width=\"1%\" nowrap=\"nowrap\">
									".$ships['class_name_abbr']."
								</td>
								<td width=\"1%\" nowrap=\"nowrap\">
									".$fleet_name_array[$ships['fleet_id']]."
								</td>
								<td width=\"1%\" nowrap=\"nowrap\">
									".$ships['ship_name'] ."
								</td>
								<td width=\"2\">
								</td>
								<td width=\"1%\" nowrap=\"nowrap\" align=\"right\">
									<b>".$ships['fighters']."</b>
								</td>
								<td width=\"1%\" nowrap=\"nowrap\">
									fighters
								</td>
				";

			// IF SHOW ABBRIVIATED SHIP CLASSES IS NOT SET, THEN...
			}
			else
			{
				$error_str .= "
								<td width=\"1%\" nowrap=\"nowrap\">
									".$ships['class_name']."
								</td>
								<td width=\"1%\" nowrap=\"nowrap\">
									".$fleet_name_array[$ships['fleet_id']]."
								</td>
								<td width=\"1%\" nowrap=\"nowrap\">
									".$ships['ship_name'] ."
								</td>
								<td width=\"2\">
								</td>
								<td width=\"1%\" nowrap=\"nowrap\" align=\"right\">
									<b>".$ships['fighters']."</b>
								</td>
								<td width=\"1%\" nowrap=\"nowrap\">
									fighters
								</td>
				";
			}

			if($ships['config'] && $user_options['show_config']==1)
			{
				$error_str .= "
								<td width=\"2\">
								</td>
								<td width=\"1%\" nowrap=\"nowrap\">
									$ships[config]
								</td>
				";
			}
				$error_str .= "
								<td width=\"2\">
								</td>
								<td width=\"1%\" nowrap=\"nowrap\">
									<a href=\"location.php?command=$ships[ship_id]\">Command</a>
								</td>
				";

					// this error string is a remnant from the defend ship option and may not be required
					$error_str .= "
								<td>
								</td>
					";
					//===================================================================================

		$error_str .= "
								<td>
								</td>
							</tr>
		";
	return $error_str;
}






function UserShip_SummaryList($fleet) {
	global $user, $db_name, $user_fleet;
	//print_array($fleet);

	// check for ramscoops
	db3(__FILE__,__LINE__,"select a.ship_id, b.ramscoop from ${db_name}_ships a, ${db_name}_upgrade_units b where a.fleet_id = '$fleet[fleet_id]' and a.ship_id = b.ship_id and b.ramscoop > 0");
	$ramscoop = dbr3();
	if(!empty($ramscoop))
	{
		if($fleet['ramscoop'] == 0)
		{
			$ram_text = "
								<td width=\"1%\" align=\"right\" nowrap=\"nowrap\">
									&nbsp;&nbsp;<a href=\"location.php?ramfleet=$fleet[fleet_id]\">Ramscoops (off)</a>
								</td>
			";
		}
		else
		{
			$ram_text = "
								<td width=\"1%\" align=\"right\" nowrap=\"nowrap\">
									&nbsp;&nbsp;<a href=\"location.php?ramfleet=$fleet[fleet_id]\">Ramscoops (on)</a>
								</td>
			";
		}
	}


	db(__FILE__,__LINE__,"select count(ship_id) as t_ships, sum(fighters) as t_fighters, count(distinct class_name) as t_classes from ${db_name}_ships where login_id = '$user[login_id]' and fleet_id = '$fleet[fleet_id]' and location = '$user[location]'");
	$t_fleet = dbr();
	//print_array($t_fleet);
		$error_str = "";
	if($t_fleet['t_ships'] > 0)
	{
		if($user_fleet['fleet_id'] == $fleet['fleet_id'])
		{
			$command_url = "<em>Commanding</em>";
		}
		else
		{
			$command_url = "<a href=\"location.php?new_command=$fleet[fleet_id]\">Command</a>";
		}
		$error_str .= "
							<tr>
								<td width=\"1%\" align=\"left\" nowrap=\"nowrap\">
									<b>Fleet $fleet[fleet_num]&nbsp;&nbsp;</b>
								</td>
								<td width=\"1%\" align=\"left\" nowrap=\"nowrap\">
									<b>".Print_FleetName($fleet[fleet_id])."</b>
								</td>
								<td width=\"1%\" align=\"right\" nowrap=\"nowrap\">
									Ships:
								</td>
								<td width=\"1%\" align=\"left\" nowrap=\"nowrap\">
									<b> $t_fleet[t_ships]&nbsp;&nbsp;<b>
								</td>
								<td width=\"1%\" align=\"right\" nowrap=\"nowrap\">
									Fighters:
								</td>
								<td width=\"1%\" align=\"left\" nowrap=\"nowrap\">
									<b> $t_fleet[t_fighters]&nbsp;&nbsp;</b>
								</td>
								<td width=\"1%\" align=\"right\" nowrap=\"nowrap\">
									Ship Classes:
								</td>
								<td width=\"1%\" align=\"left\" nowrap=\"nowrap\">
									<b> $t_fleet[t_classes]&nbsp;&nbsp;</b>
								</td>
								<td width=\"1%\" align=\"left\" nowrap=\"nowrap\">
									$command_url
								</td>
								$ram_text
								<td>
								</td>
							</tr>
		";
	}
	return $error_str;
}



// function to display a full list of all detected enemy ships (cloaking/scanners test which ships detected

function EnemyShip_FullList($ships) {
	global $ships, $user_ship_config, $user, $user_options, $flag_space_attack, $cannot_attack, $turns_safe, $db_name, $user_ship;
			$error_str .= "
				<tr>
			";
			$error_str .= "
					<td width=\"1%\" nowrap=\"nowrap\">
						".print_name($ships)."
					</td>
					<td width=\"2\">
					</td>
			";

			//non-abbreviated ship class.
			if($user_options['show_abbr_ship_class'] == 1){
				$error_str .= "
					<td width=\"1%\" nowrap=\"nowrap\">
						".$cloak_str_start.$ships['ship_name'].$cloak_str_end."
					</td>
					<td width=\"2\">
					</td>
					<td width=\"1%\" nowrap=\"nowrap\">
						$ships[class_name_abbr]
					</td>
					<td width=\"2\">
					</td>
					<td align=\"right\" width=\"1%\" nowrap=\"nowrap\">
						<b>$ships[fighters]</b>
					</td>
					<td width=\"1%\" nowrap=\"nowrap\">
						fighters
					</td>
					<td width=\"2\">
					</td>
				";
			//abbriviated ship class.
			} else {
				$error_str .= "
					<td width=\"1%\" nowrap=\"nowrap\">
						".$cloak_str_start.$ships['ship_name'].$cloak_str_end."
					</td>
					<td width=\"2\">
					</td>
					<td width=\"1%\" nowrap=\"nowrap\">
						$ships[class_name]
					</td>
					<td width=\"2\">
					</td>
					<td align=\"right\" width=\"1%\" nowrap=\"nowrap\">
						<b>$ships[fighters]</b>
					</td>
					<td width=\"1%\" nowrap=\"nowrap\">
						fighters
					</td>
					<td width=\"2\">
					</td>
				";
			}

		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
		$mr = dbr();
		$max_races = $mr['value'];
		//determine if attack string is shown or not.
		if(($user['clan_id'] == $ships['clan_id'] && $user['clan_id'] > 0) || (!$flag_space_attack || !$cannot_attack || $ships['turns_run'] < $turns_safe) && ($user['location'] <= $max_races && !$flag_sol_attack))
		{
			$error_str .= "";
		}
		elseif ($user_ship_config['mi']) //enable missile attack (!testing!)
		{
			$error_str .= "
					<td>
						<a href=\"ship_combat.php?target=$ships[fleet_id]\">Attack</a>
					</td>
					<td width=\"2\">
					</td>
					<td>
						<a href=\"attack.php?mtarget=$ships[ship_id]\">Launch</a>
					</td>";
		}
		elseif(eregi("rd",$user_ship['config']) && $ships['fighters'] < 10 && $ships['shields'] == 0)
		{
			$error_str .= "
					<td width=\"1%\" nowrap=\"nowrap\">
						<a href=\"ship_combat.php?target=$ships[fleet_id]\">Attack</a>
					</td>
					<td width=2>
					</td>
					<td width=\"1%\" nowrap=\"nowrap\">
						<a href=\"raid.php?rdtarget=$ships[ship_id]\">Raid</a>
					</td>
					<td width=\"2\">
					</td>
					<td>
						<a href=\"raid.php?cltarget=$ships[login_id]&amp;ship=$ships[ship_id]\">Claim</a>
					</td>";
		}
		elseif(eregi("rd",$user_ship['config']))
		{
			$error_str .= "
					<td width=\"1%\" nowrap=\"nowrap\">
						<a href=\"ship_combat.php?target=$ships[fleet_id]\">Attack</a>
					</td>
					<td width=\"2\">
					</td>
					<td width=\"1%\" nowrap=\"nowrap\">
						<a href=\"attack.php?target=$ships[ship_id]&amp;preptarget=1\">Attack &amp; Raid</a>
					</td>
					<td width=\"2\">
					</td>
					<td>
						<a href=\"javascript:alert('The Attack &amp; Raid option allows players to attack and raid vessels in a single step where necessary.\\n\\nHowever the normal attack option must be used if you wish to claim the vessel!\\n\\n\\t\\t\\tUse this option carefully!')\">?</a>
					</td>";
		}
		else
		{
			$error_str .= "
					<td>
						<a href=\"ship_combat.php?target=$ships[fleet_id]\">Attack</a>
					</td>";
		}
		$error_str .= "
					<td>
					</td>
				</tr>
		";
	return $error_str;
}

// function to create the row structure for each enemy fleet - basically a variant of the UserShip_SummaryList() function - these two will be merged to reduce use of that CTRL-F function I know you're fond of :)

function EnemyShip_SummaryList($fleet, $enemy) {
	global $user, $db_name, $turns_safe, $flag_space_attack, $turns_before_attack,$flag_sol_attack;
	db(__FILE__,__LINE__,"select count(ship_id) as t_ships, sum(fighters) as t_fighters, count(distinct class_name) as t_classes from ${db_name}_ships where login_id = '$enemy[login_id]' and fleet_id = '$fleet[fleet_id]' and location = '$fleet[location]'");
	$t_fleet = dbr();
	if($t_fleet['t_ships'] > 0)
	{
			$error_str .= "
							<tr>
								<td width=\"1%\" align=\"left\" nowrap=\"nowrap\">
									".print_name($enemy)."&nbsp;&nbsp;
								</td>
								<td width=\"1%\" align=\"right\" nowrap=\"nowrap\">
									Fleet:
								</td>
								<td width=\"1%\" align=\"left\" nowrap=\"nowrap\">
									<b>".Print_FleetName($fleet[fleet_id])."</b>
								</td>
								<td width=\"1%\" align=\"right\" nowrap=\"nowrap\">
									Ships:
								</td>
								<td width=\"1%\" align=\"left\" nowrap=\"nowrap\">
									<b> $t_fleet[t_ships]&nbsp;&nbsp;<b>
								</td>
								<td width=\"1%\" align=\"right\" nowrap=\"nowrap\">
									Fighters:
								</td>
								<td width=\"1%\" align=\"left\" nowrap=\"nowrap\">
									<b> $t_fleet[t_fighters]&nbsp;&nbsp;</b>
								</td>
								<td width=\"1%\" align=\"right\" nowrap=\"nowrap\">
									Ship Classes:
								</td>
								<td width=\"1%\" align=\"left\" nowrap=\"nowrap\">
									<b> $t_fleet[t_classes]&nbsp;&nbsp;</b>
								</td>
			";
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
		$mr = dbr();
		$max_races = $mr['value'];
			if(($user['clan_id'] == $enemy['clan_id'] && $user['clan_id'] > 0) || !$flag_space_attack || ($enemy['turns_run'] < $turns_safe) || ($user['turns_run'] < $turns_before_attack))
			{
				$error_str .= "
								<td>
								</td>
							</tr>
				";
			}
			elseif($user['location'] <= $max_races && !$flag_sol_attack){
				$error_str .= "
								<td>
								</td>
							</tr>
				";
			} else {
				$error_str .= "
								<td width=\"1%\" align=\"left\" nowrap=\"nowrap\">
									<a href=\"ship_combat.php?target=$fleet[fleet_id]\">Attack</a>
								</td>
								<td>
								</td>
							</tr>
				";
			}
		return $error_str;
	}
}



// function to display news posts on the front page (an optional admin variable)
function Display_News($show_news) {
	global $db_name;
$error_str = "";
	if($show_news == 1)
	{
		$error_str .= "
	<br />
	<table cellspacing=\"0\" cellpadding=\"2\" class=\"no_bottom\" width=\"100%\">
		<tr>
			<th colspan=\"2\">
				Last 5 News Headlines
			</th>
		</tr>
	";
		db(__FILE__,__LINE__,"select * from ${db_name}_news where login_id != -1 order by timestamp desc LIMIT 5");
		$news = dbr();
		while($news)
		{
			$error_str .= "
		<tr>
			<td width=\"75\" class=\"notopleft\">
				<b>".date("M d - H:i",$news['timestamp'])."</b>
			</td>
			<td class=\"emot_td\">
				&nbsp;".stripslashes($news['headline'])."
			</td>
		</tr>
			";
			$news = dbr();
		}
		$error_str .= "
	</table>
		";
	}
	return $error_str;
}





// function to determine which scanner ship is using

function Scan_Type($upg_info)
{
	if($upg_info['stand_scanner'] >= 1) {
		return 1;
	} elseif($upg_info['adv_scanner'] >= 1) {
		return 2;
	} elseif($upg_info['grav_scanner'] >= 1) {
		return 3;
	} elseif($upg_info['subs_array'] >= 1) {
		return 4;
	} elseif($upg_info['alien_array'] >= 1) {
		return 5;
	} else {
		return 0;
	}
}

// function to determine which cloaking device ship is using

function Cloak_Type($upg_info)
{
	if($upg_info['stand_cloak'] >= 1) {
		return 1;
	} elseif($upg_info['adv_cloak'] >= 1) {
		return 2;
	} elseif($upg_info['pir_deflect'] >= 1) {
		return 3;
	} elseif($upg_info['spat_unit'] >= 1) {
		return 4;
	} elseif($upg_info['dim_flux'] >= 1) {
		return 5;
	} else {
		return 0;
	}
}




// function to enable ramscooping of resources while in normal space transit - only call if player moving between star systems in normal space (i.e. not using subspace warping, etc.)
function Ramscoop() {
	global $user, $user_fleet, $db_name;
	mt_srand((double)microtime()*1000000);
	db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where (fleet_id = '$user_fleet[fleet_id]' or fleet_link = '$user_fleet[fleet_id]') and ramscoop = 1 and login_id = '$user[login_id]'");
	while($flt = dbr()) {
		if(empty($flt))
		{
			break;
		}
		//DEV: Have set a limit of 5 free cargo bays to prevent over loading with scooped resources
		// Desperately need to divorce resources from the ships table!! Impossible to automagically calculate free bays without specifying all resources in each DB query. Perhaps a quick function for the calc???
		db3(__FILE__,__LINE__,"select a.ship_id, a.cargo_bays, a.fuel, a.metal, a.colon, a.elect, a.organ, a.darkmatter, b.ramscoop from ${db_name}_ships a, ${db_name}_upgrade_units b where a.ship_id = b.ship_id and b.ramscoop = 1 and a.fleet_id = '$flt[fleet_id]' and a.login_id = '$user[login_id]'");
		while($counter = dbr3()) {
			//print_array($counter);
			$free = $counter['cargo_bays']-$counter['fuel']-$counter['metal']-$counter['colon']-$counter['elect']-$counter['organ']-$counter['darkmatter'];
			if($free < 5)
			{
				continue;
			}
			else
			{
				$temp056 = round(mt_rand(1,3)); //re-examine, need max 1-2 resource pickup with 33% chance get nothing
				// only scoops fuel for the moment - a *bit* out of sync with description :)
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set fuel = fuel + '$temp056' where ship_id = '$counter[ship_id]'");
				$result_chk = 1;
			}
		}
	}
	if(isset($result_chk) && $result_chk == 1)
	{
		return 1;
	}
	else
	{
		return 0;
	}
}


// function to print out a list of fleet links with a form to alter links
function Print_FleetLinks() {
	global $user, $lnk_confirm, $user_fleet, $db_name;
	// print the mini fleet links box
	db(__FILE__,__LINE__,"select a.fleet_id, a.fleet_num, a.fleet_name, a.fleet_link, count(b.ship_id) as ships from ${db_name}_fleets a, ${db_name}_ships b where a.fleet_id = b.fleet_id and a.location = '$user[location]' and a.location = b.location and a.login_id = '$user[login_id]' and a.login_id = b.login_id and a.fleet_id != '$user_fleet[fleet_id]' group by b.fleet_id, a.fleet_id, a.fleet_num, a.fleet_name, a.fleet_link order by a.fleet_num");
	$check_fleets = dbr();
	$text = "";
	if(!empty($check_fleets))
	{
		$text .= "<br /><form method=\"post\" action=\"location.php\" name=\"fleet_mangt\">"
		."<table cellspacing=\"0\" cellpadding=\"2\" class=\"all\" width=\"100%\">"
		."<tr><th>Fleet Links</th></tr>"
		.$lnk_confirm;
		// loop through all fleets present which have ships
		while($check_fleets) {
			if($check_fleets['ships'] > 0)
			{
				// check all fleets linked to current command fleet
				if($check_fleets['fleet_link'] == $user_fleet['fleet_id'])
				{
					$checked = " checked";
				}
				$text .= "<tr><td><input type=\"checkbox\" name=\"lnks[$check_fleets[fleet_id]]\" value=\"$check_fleets[fleet_id]\"".$checked." /> Fleet No. $check_fleets[fleet_num] \"$check_fleets[fleet_name]\"</td></tr>";
				unset($checked);
			}
			$check_fleets = dbr();
		}
		$text .= "<tr><td><input type=\"hidden\" name=\"chng_lnks\" value=\"1\" /><div align=\"center\"><input type=\"submit\" value=\"Confirm\" /></div></td></tr></form></table>";
	}
	return $text;
}

?>