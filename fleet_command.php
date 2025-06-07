<?php
include_once("includes/nocache.inc.php");
/*
//
File:				fleet_command.php
Objective:			fleet management script
Version:			QS 2.2.0
Author:				Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:		7 November 2002
Rewrite Committed:	22 April 2004
Last Modified:		25 April 2004

Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

require_once("user.inc.php");
require_once("includes/fleet_funcs.inc.php");
require_once("includes/print_funcs.inc.php");

array_push($FILE_LIST, basename(__FILE__));

// Page heading + Sub-Menu
$text = Create_PageTitleBlock("Fleet Command",array("Fleet Summary" => "fleet_command.php?summary=1",
	"Manage Fleets" => "fleet_command.php?manage=1","Create a Fleet" => "fleet_command.php?create=1"));

// default EOF links
$rs = "<br /><a href=location.php>Return to Star System</a>";

//Check if Sudden Death is in effect and whether player should be excluded from gameplay
db(__FILE__,__LINE__,"select count(ship_id) as shipsowned from ${db_name}_ships where login_id = '$user[login_id]'");
$numships = dbr();
if(!$numships['shipsowned'] && isset($sudden_death) && $user['login_id'] != 1 && $user['last_login'] != 0)
{
	print_page("Sudden Death","You have no ship, and this game is in <b>Sudden Death</b>. As such you are out of the game.");
}


// display default fleet summary
if($_GET['summary'] == 1) {
	db(__FILE__,__LINE__,"select fleet_id from ${db_name}_ships where ship_id = '$user[ship_id]'" );
	$current_ship = dbr();

	$ADODB_FETCH_MODE = 2;
	if (isset($sort_fleets)) {
		if($sorted==1){
			$going = "asc";
			$sorted=2;
		} else {
			$going = "desc";
			$sorted=1;
		}
		$order_by = "order by '$sort_fleets' $going";
	} else {
		$order_by = "order by loc asc, fnum asc";
	}

	db(__FILE__,__LINE__,"select f.fleet_id as fid, f.fleet_num as fnum, f.fleet_name as fname, f.location as loc, "
		. "count(s.ship_id) as tships, sum(s.fighters) as tfigs, sum(s.max_fighters) as tmaxfigs, "
		. "sum(s.shields) as tshlds, sum(s.max_shields) as tmaxshlds, sum(s.cargo_bays) as tbays, "
		. "sum(s.metal) as tmetal, sum(s.fuel) as tfuel, sum(s.darkmatter) as tdark, sum(s.elect) as telect, "
		. "sum(s.organ) as torg, sum(s.colon) as tcol "
		. "from ${db_name}_fleets as f "
		. "left join ${db_name}_ships as s on s.fleet_id = f.fleet_id "
		. "where f.login_id = '$user[login_id]' group by f.fleet_id $order_by");
	$fleet = dbr();
	if ($fleet) {

		$text .= "\n<br /><table cellpadding=\"1\" cellspacing=\"0\" class=\"all\"><tr><td>";
		$table_header_row = array( "<a href=\"$PHP_SELF?summary=1&sort_fleets=fnum&sorted=$sorted\">Fleet No.</a>",
			"<a href=\"$PHP_SELF?summary=1&sort_fleets=fname&sorted=$sorted\">Fleet Name</a>",
			"<a href=\"$PHP_SELF?summary=1&sort_fleets=loc&sorted=$sorted\">Location</a>",
			"<a href=\"$PHP_SELF?summary=1&sort_fleets=tships&sorted=$sorted\">Ships</a>",
			"<a href=\"$PHP_SELF?summary=1&sort_fleets=tfigs&sorted=$sorted\">Fighters</a>",
			"<a href=\"$PHP_SELF?summary=1&sort_fleets=tshlds&sorted=$sorted\">Shields</a>",
			"<a href=\"$PHP_SELF?summary=1&sort_fleets=tbays&sorted=$sorted\">Empty Bays</a>",
			"<a href=\"$PHP_SELF?summary=1&sort_fleets=tmetal&sorted=$sorted\">Metal</a>",
			"<a href=\"$PHP_SELF?summary=1&sort_fleets=tfuel&sorted=$sorted\">Fuel</a>",
			"<a href=\"$PHP_SELF?summary=1&sort_fleets=tdark&sorted=$sorted\">Darkmatter</a>",
			"<a href=\"$PHP_SELF?summary=1&sort_fleets=telect&sorted=$sorted\">Electronics</a>",
			"<a href=\"$PHP_SELF?summary=1&sort_fleets=torg&sorted=$sorted\">Organics</a>",
			"<a href=\"$PHP_SELF?summary=1&sort_fleets=tcols&sorted=$sorted\">Colonists</a>" );
		if ($flag_dmatter == 0) {
			unset( $table_header_row[9] );
		}
		$text .= make_table( $table_header_row );

		unset( $fleet_totals );
		foreach ( $fleet as $k => $v ) {
			$fleet_totals[$k] = 0;
		}

		do {
                        foreach ( $fleet as $k => $v ) {
                                if ( is_numeric( $v ) ) {
                                        $fleet_totals[$k] += $v;
                                }
                        }

			$out_arr = array();
			$out_arr[] = "<b class=b1>$fleet[fnum]</b>";
			$out_arr[] = Print_FleetName( $fleet['fid'], $fleet['fname'] );
			$out_arr[] = $fleet['loc'];
			$out_arr[] = $fleet['tships'];
			if ( $fleet['tships'] > 0 ) {
				$out_arr[] = $fleet['tfigs'] . ' / ' . $fleet['tmaxfigs'];
				$out_arr[] = $fleet['tshlds'] . ' / ' . $fleet['tmaxshlds'];
				$out_arr[] = ( $fleet['tbays'] - $fleet['tmetal'] - $fleet['tfuel'] - $fleet['tdark']
					- $fleet['telect'] - $fleet['telect'] - $fleet['torg'] - $fleet['tcol'] )
					. ' / ' . $fleet['tbays'];
			} else {
				$out_arr[] = "";
				$out_arr[] = "";
				$out_arr[] = "";
			}
			$out_arr[] = $fleet['tmetal'];
			$out_arr[] = $fleet['tfuel'];
			if ( $flag_dmatter == 1 ) {
				$out_arr[] = $fleet['tdark'];
			}
			$out_arr[] = $fleet['telect'];
			$out_arr[] = $fleet['torg'];
			$out_arr[] = $fleet['tcol'];
			if ( ! empty( $current_ship ) && ( $current_ship['fleet_id'] == $fleet['fid'] ) ) {
				$out_arr[] = "<em>Commanding</em>";
			} elseif ( $fleet['tships'] > 0 ) {
				$out_arr[] = "<a href=\"location.php?new_command=$fleet[fid]\">Command</a>";
			} else {
				$out_arr[] = "";
			}
			$text .= make_hash_row( $out_arr );
		} while ( $fleet = dbr(1) );

                $text .= "\n<tr class=\"tr_bg\">";
		unset( $fleet_totals['fid'] );
		$fleet_totals['fnum'] = "<b class=b1>Total</b>";
		$fleet_totals['fname'] = "";
		$fleet_totals['loc'] = "";
		$fleet_totals['tfigs'] .= ' / ' . $fleet_totals['tmaxfigs'];
		unset( $fleet_totals['tmaxfigs'] );
		$fleet_totals['tshlds'] .= ' / ' . $fleet_totals['tmaxshlds'];
		unset( $fleet_totals['tmaxshlds'] );
		$fleet_totals['tbays'] = ( $fleet_totals['tbays'] - $fleet_totals['tmetal'] - $fleet_totals['tfuel']
			- $fleet_totals['tdark'] - $fleet_totals['telect'] - $fleet_totals['telect'] - $fleet_totals['torg']
			- $fleet_totals['tcol'] ) . ' / ' . $fleet_totals['tbays'];
		if ( $flag_dmatter == 0 ) {
			unset( $fleet_totals['tdark'] );
		}
                foreach ( $fleet_totals as $v ) {
                        $text .= "\n<td><b>$v</b></td>";
                }
                $text .= "\n</tr>";

		$text .= "</table></td></tr></table>";
	} else {
		$text .= '<br />No fleets exist.<br />';
	}
	print_page( 'Fleet Summaries', $text );
}

// enable fleet organisation on a location by location basis

// choose location to manage
if($_GET['manage'] == 1 && empty($_GET['loc']))
{
	db3(__FILE__,__LINE__,"select distinct(location) as loc from ${db_name}_fleets where login_id = '$user[login_id]'");
	$all = dbr3();
	if ($all) {
		$text .= "\n<p>Please select a location to where the fleets you wish to manage are located:</p>\n"
			. "<form method=\"post\" action=\"fleet_command.php\" id=\"manage_fleets\" name=\"manage_fleets\">\n"
			. "<table cellpadding=\"1\" cellspacing=\"0\" class=\"all\">\n";
		do {
			$text .= "<tr><td colspan=\"5\" class=\"border_bottom\">"
				. "<span class=\"h5\" style=\"padding-left: 5px;\">Star System #$all[loc] - "
					. "<a href=\"fleet_command.php?manage=2&amp;loc=$all[loc]\">Manage</a></span>"
				. "</td></tr>\n";
			db4(__FILE__,__LINE__,"select f.fleet_name, f.fleet_num, f.fleet_id, count(s.ship_id) as numships "
				. "from ${db_name}_fleets as f "
				. "left join ${db_name}_ships as s on s.fleet_id = f.fleet_id "
				. "where f.location = '$all[loc]' and f.login_id = '$user[login_id]' "
				. "group by f.fleet_id order by f.fleet_num asc");
			while($f = dbr4()) {
				$text .= "<tr>"
					. "<td style=\"padding-left: 5px;\">Fleet No.: <b class=\"b1\">$f[fleet_num]</b></td>"
					. "<td style=\"padding-left: 2em;\">" . Print_FleetName( $f['fleet_id'], $f['fleet_name'] ) . "</td>"
					. "<td style=\"padding-left: 2em;\">" .
						( $f['numships'] > 0 ? "<b>$f[numships]</b> Ships" : "<em>Empty</em>" ) . "</td>";
				if ( $f['numships'] == 0 ) {
					$text .= "<td style=\"padding-left: 2em;\">"
							. "<a href=\"$PHP_SELF?rename=$f[fleet_id]\">Rename</a></td>"
						. "<td style=\"padding-left: 2em; padding-right: 5px;\">"
							. "<a href=\"$PHP_SELF?delete=$f[fleet_id]\">Delete</a></td>";
				} else {
					$text .= "<td colspan=\"2\" style=\"padding-left: 2em; padding-right: 5px;\">"
						. "<a href=\"$PHP_SELF?rename=$f[fleet_id]\">Rename</a></td>";
				}
				$text .= "</tr>";
			}
			$all = dbr3();
			if ($all) {
				$text .= "<tr><td colspan=\"5\" class=\"border_bottom\">&nbsp;</td></tr>";
			}
		} while ($all);
		$text .= "</table></form>";
	} else {
		$text .= "<br />No fleets to manage.<br />";
	}
	print_page("Manage Fleets",$text);
}
// show fleets for the location chosen to manage
elseif($_GET['manage'] == 2 && !empty($_GET['loc']))
{
	$text .= "<p>Welcome to Fleet Command.</p>"
		. "<p>Here you can move ships into an existing fleet or to create a new fleet. Check the "
		. "ships you wish to move and enter the fleet number where they should be placed. If the fleet "
		. "number does not already exist, a new fleet with that number will be created.</p>"
		. "<p>Please note: you may only move ships between fleets at the same location.</p>"
		. "<form method=\"post\" action=\"fleet_command.php\" name=\"fleet_mangt\">"
		. "<b>Move Checked Ships into Fleet No.</b> <input name=\"fleetnum\" value=\"\" size=\"3\" maxlength=\"3\" />"
		. "<br /><br /><input type=\"submit\" value=\"Submit\" /><br /><br />";

	// table structure
	db(__FILE__,__LINE__,"select count(fleet_id) as numflt from ${db_name}_fleets where login_id = '$user[login_id]' and location = '$_GET[loc]'");
	$flt_cnt = dbr();
	$row_c = round($flt_cnt['numflt'] * .5);
	$text .= "
		<table border=\"0\" cellspacing=\"0\" width=\"100%\">
			<tr>
				<td width=\"250\" valign=\"top\">
	";
	$ct5 = 0;
	db6(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where location = '$_GET[loc]' and login_id = '$user[login_id]'"
		. "order by fleet_num asc");
	while($fleetx = dbr6()) {
		if($ct5 == $row_c)
		{
			//add right column as necessary to split info
			$text .= "
				</td>
				<td width=\"250\" valign=\"top\">
			";
		}
		$text .= "<a name=\"t_$fleetx[fleet_id]\"></a>".Print_FleetListing($fleetx['fleet_id'],1)."<br />";
		$ct5 += 1;
	}
	//close off table
	$text .= "
				</td>
			</tr>
		</table>";
	$text .= "<br /><input type=\"submit\" value=\"Submit\" /></form>";
	print_page("Fleet Command",$text);
}
elseif($_GET['create'] == 1 && empty($_GET['loc']))
{
	$text .= "<p>You can create a fleet here.";
	$text .= "<br>Only use this when all your fleets have been blown up, but you still have ships.";
	$text .= "<br>Use <a href=\"fleet_command.php?manage=1\">Manage Fleets</a> to create new fleets if you already have them.";
	$text .= "<br><br>Are you sure you want to create a new fleet?";
	$text .= "<form name=\"form1\" method=\"post\" action=\"\">
  <p>
    <label>
    <input type=\"radio\" name=\"cfleet\" value=\"1\">
    Yes</label>
    <br />
    <label>
    <input type=\"radio\" name=\"cfleet\" value=\"0\">
    No</label>
    <br /><br />
    <input type=\"submit\" name=\"Submit\" value=\"Submit\">
</form><br />";
	if($cfleet == 1){
		db(__FILE__,__LINE__,"select max(fleet_num) as maxnum from ${db_name}_fleets where login_id = '$user[login_id]'");
		$fnum = dbr();
		$newnum = empty($fnum) || empty($fnum['maxnum']) ? 1 : $fnum['maxnum'] + 1;
		dbn(__FILE__,__LINE__,"insert into ${db_name}_fleets (fleet_name, fleet_num, login_id, login_name, location, clan_id) "
			. "values ('Alpha',$newnum,'$user[login_id]','$user[login_name]','$user[location]',$user[clan_id])");
		$text .= "<br>Fleet created.</p>";
		db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where login_id = '$user[login_id]'");
		$flt = dbr();
		$boo = $flt['fleet_id'];
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set fleet_id = '$boo' where login_id = $user[login_id] && fleet_id = '0'");
		$text .= "Ships updated.  Have a nice day";
	}
	print_page("Create Fleets",$text);
}
// show fleets for the location chosen to manage
//elseif($_GET['manage'] == 2 && !empty($_GET['loc']))


// Rename a fleet
if ( isset( $_GET['rename'] ) ) {
	$rs = "<br /><a href=fleet_command.php?manage=1>Return to Fleet Management</a><br />$rs";
	$ren_fid = $_GET['rename'];
	db( __FILE__, __LINE__, "select fleet_id, fleet_name, fleet_num, login_id from ${db_name}_fleets "
		. "where fleet_id = '$ren_fid'" );
	$fleet = dbr();
	if ( empty( $fleet ) ) {
		$text .= '<p>You cannot rename a nonexistent fleet.</p>';
	} elseif ( $fleet['login_id'] != $user['login_id'] ) {
		$text .= '<p>You cannot rename a fleet you do not own!</p>';
	} elseif ( ! isset( $_POST['fleet_name'] ) ) {
		$text .= "<p>Please enter the new callsign for Fleet No. <b class=b1>$fleet[fleet_num]</b> '"
				. Print_FleetName( $fleet['fleet_id'], $fleet['fleet_name'] ) . "':</p>"
			. "<form name=\"rename_fleet\" action=\"$PHP_SELF?rename=$ren_fid\" method=\"post\">"
			. "<input name=\"fleet_name\" value='' size=10> (10 Characters Max)<br />"
			. "<input type=\"submit\" name=\"submit\" value=\"Submit\">"
			. "</form>";
	} else {
		$newname = correct_name( $_POST['fleet_name'] );
		if ( strlen( $newname ) < 1 or strlen( $newname ) > 10 ) {
			$text .= "<p>Invalid fleet name. Fleet names must be between 1 and 10 character long.</p>"
				. "<p><a href=\"javascript:history.back()\">Try another name.</a></p>";
		} else {
			dbn( __FILE__, __LINE__, "update ${db_name}_fleets set fleet_name = '$newname' "
				. "where fleet_id = '$ren_fid'" );
			$text .= "<p>Fleet No. <b class=b1>$fleet[fleet_num]</b> is now known as '"
				. Print_FleetName( $fleet['fleet_id'], $newname ) . "'.</p>";
		}
	}
	print_page( 'Rename Fleet', $text );
}


// Delete a fleet
if ( isset( $_GET['delete'] ) ) {
	$rs = "<br /><a href=fleet_command.php?manage=1>Return to Fleet Management</a><br />$rs";
	$del_fid = $_GET['delete'];
	db( __FILE__, __LINE__, "select fleet_id, fleet_name, fleet_num, login_id from ${db_name}_fleets "
		. "where fleet_id = '$del_fid'" );
	$fleet = dbr();
	if ( empty( $fleet ) ) {
		$text .= '<p>You cannot delete a nonexistent fleet.</p>';
	} elseif ( $fleet['login_id'] != $user['login_id'] ) {
		$text .= '<p>You cannot delete a fleet you do not own!</p>';
	} else {
		db( __FILE__, __LINE__, "select count(ship_id) as numships from ${db_name}_ships "
			. "where fleet_id = '$del_fid' and login_id = '$user[login_id]'" );
		$ship_cnt = dbr();
		db( __FILE__, __LINE__, "select count(fleet_id) as numfleets from ${db_name}_fleets "
			. "where login_id = '$user[login_id]'" );
		$fleet_cnt = dbr();
		if ( $fleet_cnt['numfleets'] == 1 ) {
			$text .= "<p>Fleet No. <b class=b1>$fleet[fleet_num]</b> '"
				. Print_FleetName( $fleet['fleet_id'], $fleet['fleet_name'] )
				. "' is your last fleet which cannot be deleted.</p>";
		} elseif ( $ship_cnt['numships'] != 0 ) {
			$text .= "<p>Fleet No. <b class=b1>$fleet[fleet_num]</b> '"
				. Print_FleetName( $fleet['fleet_id'], $fleet['fleet_name'] )
				. "' has <b>$ship_cnt[numships]</b> ships in it. You can only delete empty fleets.</p>";
		} elseif ( ! isset( $_POST['sure'] ) ) {
			$rs = "<br /><br />$rs";
			$text .= "Are you sure you want to delete Fleet No. <b class=b1>$fleet[fleet_num]</b> '"
				. Print_FleetName( $fleet['fleet_id'], $fleet['fleet_name'] ) . "'?";
			get_var( 'Delete Fleet', "fleet_command.php?delete=$del_fid", $text, 'sure', 'yes' );
		} else {
			dbn( __FILE__, __LINE__, "delete from ${db_name}_fleets where fleet_id = '$del_fid'" );
			$text .= "<p>Fleet No. <b class=b1>$fleet[fleet_num]</b> '$fleet[fleet_name]' has been deleted.</p>";
		}
	}
	print_page( 'Delete Fleet', $text );
}


//the asset test of fleet management - moving ships between fleets!

if(isset($_POST['fleetnum']))
{
	if(empty($_POST['expl']))
	{
		print_page("Fleet Orders",$text."<p>You must select at least one ship to issue fleet orders to!</p>");
	}
	if($_POST['fleetnum'] >= 500)
	{
		print_page("Fleet Orders",$text."<p>Fleet numbers above 500 are prohibited since they lie outside Sol Authority registration limits! Choose a number below 500!</p>");
	}
	$temp567 = $_POST['expl']; $chkloc = 0;

	// generate a standard location (if a ship with another location pops up during processing - the process will stop)
	while ($var = each($temp567)) {
		if($chkloc == 0)
		{
			db(__FILE__,__LINE__,"select location from ${db_name}_ships where ship_id = '$var[value]'");
			$loc_set = dbr();
			$chkloc++;
		}
		else
		{
			break;
		}
	}

	db(__FILE__,__LINE__,"select count(fleet_id) as numfleets from ${db_name}_fleets where login_id = '$user[login_id]' and fleet_num = '$_POST[fleetnum]'");
	$fleet_count = dbr();
	db(__FILE__,__LINE__,"select * from ${db_name}_fleets where login_id = '$user[login_id]' and fleet_num = '$_POST[fleetnum]'");
	$fleet_info = dbr();
	db(__FILE__,__LINE__,"select count(ship_id) as num from ${db_name}_ships where fleet_id = '$fleet_info[fleet_id]' and login_id = '$user[login_id]'");
	$fleet_ship_count = dbr();

	$math = 0;
	$loc_cnt = 0;
	$temp444 = $_POST['expl'];
	while ($var = each($temp444)) {
		db(__FILE__,__LINE__,"select login_id,location,fleet_id from ${db_name}_ships where ship_id = '$var[value]'");
		$target_ship = dbr();
		$math++;
		// DEV: Are breaks not a bit pointless since print_page also calls exit() ???
		if($target_ship['location'] != $loc_set['location'])
		{
			print_page("Fleet Orders",$text."<p>You cannot add ships from differing locations to the same fleet! Choose only ships in the same location, or move them before issuing Fleet orders.</p>");
			break;
		}
		elseif($target_ship['login_id'] != $user['login_id'])
		{
			print_page("Fleet Orders",$text."<p>You can only order one of your own ships!</p>");
			break;
		}
		elseif($user['ship_id'] == $var['value'])
		{
			print_page("Fleet Orders",$text."<p>You may not order the ship that you are commanding. Your command ship determines the current Fleet In Command. Switch command to another fleet in order to change this.</p>");
			break;
		}

		//FIX: Posted by Hades 30 May 2004
		// Get command ship for current (in the loop) ships fleet
		db(__file__,__line__,"select ship_id from ${db_name}_fleets where fleet_id = $target_ship[fleet_id]");
		$f_temp = dbr();
		// If command ship for this ships fleet is being moved, select another command ship
		if($var['value'] == $f_temp['ship_id']) {
			$ship_list = implode(',',$var);
			db(__file__,__line__,"select ship_id from ${db_name}_ships where fleet_id = $target_ship[fleet_id] and ship_id not in ($ship_list)");
			if($row = dbr()) {
				dbn(__file__,__line__,"update ${db_name}_fleets set ship_id = $row[ship_id] where fleet_id = $target_ship[fleet_id]");
			}
		}
		//ENDFIX

	}
	if($fleet_count['numfleets'] == 0)
	{
		//create new fleet and assign //DEV: I'll figure out why I put this here one of these days :)
		if($_POST['fleetnum'] == 0)
		{
			print_page("Test","WRONG!!!");
		}
		if(!isset($_POST['fleet_name']))
		{
			$text .= "<p>Since the chosen Fleet No. does not exist, one will now be created (No. $_POST[fleetnum]). Please enter your new new Fleet's callsign:</p>";
			$fleet_number = $_POST['fleetnum'];
			$text .= "<form name=create_fleet action=fleet_command.php method=post>";
			$temp555 = $_POST['expl'];
			$i=0;
			while ($var = each($temp555)) {
				$text .= "<input type=hidden name=expl[$i] value='$var[value]'>";
				$i++;
			}
			$text .= '<input type=hidden name=fleetnum value='.$fleet_number.'>';
			$text .= '<input type=hidden name=math value=$math>';
			$text .= "<input name=fleet_name value='' size=10> (10 Characters Max)<br />";
			$text .= '<input type=submit name=submit value=Submit></form>';
			print_page("Create Fleet",$text);
		}
		else
		{
			dbn(__FILE__,__LINE__,"insert into ${db_name}_fleets (fleet_name, fleet_num, login_id, login_name) values ('$_POST[fleet_name]','$_POST[fleetnum]','$user[login_id]','$user[login_name]')");
			//DEV: bit of an issue - is this $login_id global? Surely it should be unset after use in authentication!!
			//supposed to be using the $user array for all this - otherwise some smartass may manage to pass login_id
			//and cause havoc.
			db(__FILE__,__LINE__,"select * from ${db_name}_fleets where fleet_name = '$_POST[fleet_name]' && login_id = '$user[login_id]' and fleet_num = '$_POST[fleetnum]'");
			$flt = dbr();
			$temp777 = $_POST['expl'];
			$find_loc = 0;
			while ($var = each($temp777)) {
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set fleet_id = '$flt[fleet_id]' where ship_id = '$var[value]' and login_id = '$user[login_id]'");
				//DEV: the find_loc switch is not reset - this query is run over and over and over...fix!
				if($find_loc == 0)
				{
					db(__FILE__,__LINE__,"select location from ${db_name}_ships where ship_id = '$var[value]'");
					$loc_flt = dbr();
				}
			}
			//now set fleet location - DEV: needed? All transfers in same location now.
			// Doesn't seem to do anything - ##Hades
			dbn(__FILE__,__LINE__,"update ${db_name}_fleets set location = '$loc_flt[location]' where fleet_id = '$flt[fleet_id]'");

			//DEV: another quick fix - delete all empty fleets
			db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where login_id = '$user[login_id]' and location = '$loc_flt[location]'");
			while($_fleet = dbr()) {
				db2(__FILE__,__LINE__,"select count(ship_id) as num from ${db_name}_ships where fleet_id = '$_fleet[fleet_id]'");
				$chk_num = dbr2();
				if(empty($chk_num['num']) || $chk_num['num'] <= 0)
				{
					dbn(__FILE__,__LINE__,"delete from ${db_name}_fleets where fleet_id = '$_fleet[fleet_id]'");
				}
			}

			print_page("Fleet Orders",$text."<p><b>$math</b> Ships have successfully been reassigned to Fleet No. <b>$flt[fleet_num]</b> (<b class=b1>$flt[fleet_name]</b>) in Star System #<b>$loc_flt[location]</b></p>");
		}
	}
	else
	{
		$temp666 = $_POST['expl'];

		if($fleet_count_ships['num'] > 0)
		{
			if($fleet_info['location'] != $loc_set['location'])
			{
				print_page("Fleet Orders",$text."<p>You cannot add ships from one location to a Fleet in a different location. Either add the ships to a Fleet at the ships' location or add them to a new Fleet or relocate the ships to the location of the Fleet you want to add them to.</p>");
			}
		}
		else
		{
			dbn(__FILE__,__LINE__,"update ${db_name}_fleets set location = '$loc_set[location]' where fleet_id = '$fleet_info[fleet_id]'");
			while ($var = each($temp666)) {
				// check the fleet limits
				Check_FleetMax_FleetCommand($var['value'], $fleet_info['fleet_id']);
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set fleet_id = '$fleet_info[fleet_id]', location = '$fleet_info[location]' where ship_id = '$var[value]' and login_id = '$user[login_id]'");
			}
		}
		print_page("Fleet Orders",$text."<b>$math</b> Ships have successfully been assigned to Fleet No. <b>$fleet_info[fleet_num]</b> (<b class=b1>$fleet_info[fleet_name]</b>)");
	}
}
?>
