<?php
/*
//
File:			ship_transfer.php
Objective:		File to coordinate ship transfers and user management of transfer buffers
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	27 January 2004		Date Modified:	n/a

Copyright (c) 2003, 2004 by Pádraic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/


require_once("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));
require_once("includes/fleet_funcs.inc.php");

// set the Return String $rs to diplay an exit link to the location page
$rs = "<p><br /><a href=\"location.php\">Return to Star System</a></p>";

//decode the post tt_text var
if(isset($_POST['tt_text']))
{
	$t_text .= urldecode($_POST['tt_text']);
}

//Check if Sudden Death is in effect and whether player should be excluded from gameplay
db(__FILE__,__LINE__,"select count(ship_id) as shipsowned from ${db_name}_ships where login_id = '$user[login_id]'");
$numships = dbr();
if(!$numships['shipsowned'] && isset($sudden_death) && $user['login_id'] != 1 && $user['last_login'] != 0)
{
	print_page("Sudden Death","You have no ship, and this game is in <b>Sudden Death</b>. As such you are out of the game.");
}



// Page heading + Sub-Menu
$text = Create_PageTitleBlock("Sol Registrar Ship Transfer Buffer",array("Outstanding Transfers" => "ship_transfer.php?buffer=1", "Create New Transfer" => "ship_transfer.php?new=1", "View Created Transfers" => "ship_transfer.php?v_transfer=1"));




/*
Note: I'm making a *little* effort this time around to keep the interface as clean and intuitive as possible.
I have also written all code to conform to register_globals off, and in accordance to the xhtml standard.
This will continue to be extended across QS on a file by file basis over coming months.
The code is also back to a more readable format :)

**The Page Title + Sub-Menu will be introduced as standard across all pages - see print_funcs.inc.php**
These will be added to all game functions to ease navigation where multiple functions exist.
The menu-buttonstyle code for the sub-menu is CSS based - see the Blue_Tempest stylesheet
*/

//---------------------------------------------------------------------------------------------------------//
// display a users up to date transfer listing with options to immediately accept or decline the transfer. //
//---------------------------------------------------------------------------------------------------------//

if($_GET['buffer'] == 1)
{
	$text .= "<p>Welcome to your \"Transfer Buffer\" Account with the Sol Ship Registrar. This menu will in the future replace all older ship transfer code, and bring you a more streamlined logical function. In the meantime, you may view your outstanding transfers from other players and choose which transfer to accept or decline.</p>"
	."<br /><span class=\"h4\">Outstanding Ship Transfers</span><br /><br />";

	$text .= make_table(array("Time Transferred","Transferer Name","Ship Class","Current Location","<a href='javascript:TickAll(\"confirm_transfer\")'>Invert<br />Selection</a>",));

	$text .= "<form method=\"post\" action=\"ship_transfer.php\" name=\"confirm_transfer\">"
	."<input type=\"hidden\" name=\"mass_transfer\" value=\"1\" />";

	db(__FILE__,__LINE__,"select * from {$db_name}_transfer_buffer where transferto_id = '$user[login_id]' order by owner_id, timestamp desc, ship_categ asc, class_name");
	unset($buffer);
	while($buffer = dbr())
	{
		db2(__FILE__,__LINE__,"select login_id from ${db_name}_users where login_id = '$buffer[owner_id]'");
		$towner = dbr2();
		db2(__FILE__,__LINE__,"select location, login_id from ${db_name}_ships where ship_id = '$buffer[ship_id]'");
		$tship = dbr2();
		// double check that this is a valid buffer entry i.e. that ship not destroyed or sent to a different owner
		// otherwise delete the buffer entry
		if(empty($tship) || $tship['login_id'] != $towner['login_id'])
		{
			dbn(__FILE__,__LINE__,"delete from ${db_name}_transfer_buffer where ship_id = '$buffer[ship_id]'");
			continue;
		}
		$t_date = date("D M j G:i:s", $buffer['timestamp']);
		$t_name = "<div align=\"center\">".print_name($towner['login_id'])."</div>";
		$t_location = "<div align=\"center\">SS #".$tship['location']."</div>";
		$t_class = $buffer['class_name'];
		$t_option = "<div align=\"center\"><input type=\"checkbox\" name=\"transfer_list[$buffer[buffer_id]]\" value=\"$buffer[buffer_id]\" /></div>";
		$text .= make_row(array($t_date, $t_name, $t_class, $t_location, $t_option));
		$buffer_open = 1;
	}

	$text .= "</table>";

	if(!isset($buffer_open))
	{
		$text .= "<p>You have no outstanding Ship Transfers at this time.</p>";
	}
	else
	{
		$text .= "<br /><p>"
		."<select name=\"confirm_type\">"
		."<option value=\"1\" selected>Accept Transfer</option>"
		."<option value=\"2\">Decline Transfer</option>"
		."</select>"
		."&nbsp;&nbsp;&nbsp;";

		$text .= "<input type=\"submit\" value=\"Submit!\" /></form></p>";
	}

	print_page("Ships Transfer Buffer",$text);
}



//---------------------------------------------------------------------------------------------------------//
// This section takes the players decision to accept/decline a ship transfer and effects changes           //
//---------------------------------------------------------------------------------------------------------//



if(isset($_POST['mass_transfer']))
{
	//$rs .= "<p><a href=\"ship_transfer.php?buffer=1\">Return Ship Transfer Menu</a></p>";
	if(empty($_POST['transfer_list']))
	{
		$error_str = $text."<p>You do not appear to have chosen any Ship Transfers to accept or decline. You must select at least one Ship Transfer to use this function.</p><p>Please follow the return link below select a Ship Transfer to accept or decline.</p>";
		print_page("Accessing Transfer Data",$error_str);
	}
	else
	{
	db5(__FILE__,__LINE__,"select count(ship_id) as shipsowned from ${db_name}_ships where login_id = '$user[login_id]'");$numships = dbr5();
		//print_array($_POST['transfer_list']);
		$t_text .= "<p>"; //open paragraph tag
		while($var = each($_POST['transfer_list']))
		{
			db(__FILE__,__LINE__,"select * from ${db_name}_transfer_buffer where buffer_id = '$var[value]'");
			$transfer = dbr();
			if($_POST['confirm_type'] == 2) // decline the transfer
			{
				dbn(__FILE__,__LINE__,"delete from ${db_name}_transfer_buffer where buffer_id = '$transfer[buffer_id]'");
				send_message($transfer['owner_id'],"<p>Ship Transfer Buffer Alert<br />$user[login_id] has declined the transfer of a $transfer[class_name] that you initiated. The ship has therefore been removed from the Transfer Buffer and returned to your full ownership.</p>");
				$owner = array(); $owner['login_id'] = $transfer['owner_id'];
				$t_text .= "Transfer of a $transfer[class_name] from ".print_name($owner)." declined.<br />";
			}
			elseif($_POST['confirm_type'] == 1) // accept the transfer
			{
			if(($numships[shipsowned] + count($_POST['transfer_list'])) > $max_ships)
				{
				print_page("Error", "You already own <b>$numships[shipsowned]</b> ship(s). The Admin has set the maximum number of ships players may own to <b>$max_ships</b>, which would put you <b>" . (count($_POST['transfer_list']) - $max_ships) . "</b> ships, if this transfer were to be accepted.");
				}
				// No longer required if the replacement code holds up. Specific code required to prevent relocation of ships
				/*
				if(isset($_POST['fleetnum']))
				{
					$user_fleet = Check_ToCreateFleet($_POST['fleetnum']);
					unset($_POST['fleetnum']);
				}
				Check_FleetMax();

				$array_offset++; //move offset pointer forward by 1 (used to determine split off point in event a fleet must be created to hold additional ships beyond the current fleet limit.
				*/

				dbn(__FILE__,__LINE__,"delete from ${db_name}_transfer_buffer where buffer_id = '$transfer[buffer_id]'");
				send_message($transfer['owner_id'],"<p>Ship Transfer Buffer Alert<br />".print_name($user)." has accepted the transfer of a $transfer[class_name] that you initiated. The ship has therefore been fully transferred to its new owner, ".print_name($user).".</p>");
				$owner = array(); $owner['login_id'] = $transfer['owner_id'];
				$t_text .= "Transfer of a $transfer[class_name] from ".print_name($owner)." accepted.<br />";



				//start - this section should prevent relocation of transferred ships while maintaining fleet constraints. Fleets are created automatically if needed - no fallback to user naming of fleets.

				db(__FILE__,__LINE__,"select location,ship_categ from ${db_name}_ships where ship_id = '$transfer[ship_id]'");
				$transfersh = dbr();
				db(__FILE__,__LINE__,"select * from ${db_name}_fleets where login_id = '$user[login_id]' and location = '$transfersh[location]' order by fleet_num desc");
				$check_curr_fleet = dbr();
				$need_new_fleet = 0;
				if(!empty($check_curr_fleet))
				{
					if($transfersh['ship_categ'] == 3 || $transfersh['ship_categ'] == 4 || $transfersh['ship_categ'] == 8 || $transfersh['ship_categ'] == 9)
					{
						db(__FILE__,__LINE__,"select count(ship_id) as war_cnt from ${db_name}_ships where fleet_id = '$check_curr_fleet[fleet_id]' and (ship_categ = 3 || ship_categ = 4 || ship_categ = 8 || ship_categ = 9)");
						$war_count = dbr();
						if($war_count['war_cnt'] >= $fleet_war_max)
						{
							$need_new_fleet = 1;
						}
					}
					db(__FILE__,__LINE__,"select count(ship_id) as total from ${db_name}_ships where fleet_id = '$check_curr_fleet[fleet_id]'");
					$gen_count = dbr();
					if($gen_count['total'] >= $max_fleet_size)
					{
						$need_new_fleet = 1;
					}
				}
				if(empty($check_curr_fleet) || $need_new_fleet == 1)
				{
					db(__FILE__,__LINE__,"select fleet_num from ${db_name}_fleets where login_id = '$user[login_id]' order by fleet_num desc");
					$high_fn = dbr();
					$new_num = $high_fn['fleet_num'] + 1;
					$fleet_name = "Transfer ".$new_num;
					dbn(__FILE__,__LINE__,"insert into ${db_name}_fleets (fleet_name, fleet_num, login_id, login_name, location) values ('$fleet_name','$new_num','$user[login_id]','$user[login_name]','$transfersh[location]')");
					db(__FILE__,__LINE__,"select * from ${db_name}_fleets where login_id = '$user[login_id]' && fleet_num = '$new_num'");
					$check_curr_fleet = dbr();
				}

				//end




				dbn(__FILE__,__LINE__,"update ${db_name}_ships set fleet_id = '$check_curr_fleet[fleet_id]', login_id = '$user[login_id]', login_name = '$user[login_name]', clan_id = '$user[clan_id]' where ship_id = '$transfer[ship_id]'");

				// If fleet has no command ship - better rectify that - otherwise player will command empty vacuum!
				if($check_curr_fleet['ship_id'] <= 0 || !isset($check_curr_fleet['ship_id']))
				{
					dbn(__FILE__,__LINE__,"update ${db_name}_fleets set ship_id = '$transfer[ship_id]' where login_id = '$user[login_id]' and fleet_id = '$check_curr_fleet[fleet_id]' and location = '$transfersh[location]'");
				}
			}
		}
		if(!isset($t_text))
		{
			$text .= "<p>No ship transfers have been altered - please check you have correctly tried to accept or decline a ship transfer.</p>";
		}
		else
		{
			$t_text .= "</p>"; //close paragraph tag
			$text .= "<p>The results of your chosen actions are:</p>".$t_text;
		}
		print_page("Ship Transfer Action",$text);
	}
}

//---------------------------------------------------------------------------------------------------------//
// This section allows a player to create a new Ship Transfer and add it to the buffer                     //
//---------------------------------------------------------------------------------------------------------//

if(isset($_GET['new']))
{
	$text .= "<p>From the menu below please choose the user you wish to transfer ships to, and any ships you wish to add to that user's Transfer Buffer:</p>"
	."<form method=\"post\" action=\"ship_transfer.php\" name=\"new_transfer\">"
	."<input type=\"hidden\" name=\"t_new\" value=\"1\" />"
	."<span class=\"h4\">Step 1: Select another player to transfer ships to.</span>";
	db(__FILE__,__LINE__,"select login_name, login_id from ${db_name}_users where login_id != '$user[login_id]' order by login_name asc");
	$text .= "<p>"
	."<select name=\"transfer_to\">";
	while($players = dbr()) {
		$text .= "<option value=\"$players[login_id]\">$players[login_name]</option>";
	}
	$text .= "</select></p>";
	$text .= "<br /><span class=\"h4\">Step 2: Select the ships you wish to transfer.</span>"
	."<p>Note that this list omits any ships that are already present on a transfer buffer for a player.</p>";
	db(__FILE__,__LINE__,"select fleet_id, fleet_name from ${db_name}_fleets where login_id = '$user[login_id]' order by fleet_num asc");

	// create a quick nav menu to all fleets :-)
	$text .= "<p>Your Fleets ( :: ";
	while($q_fleet = dbr()) {
		$text .= "<a href=\"#fleet".$q_fleet['fleet_id']."\">$q_fleet[fleet_name]</a> :: ";
	}
	$text .= ")</p>";

	// enable column switch marker for overall columned table
	db(__FILE__,__LINE__,"select count(fleet_id) as flcnt from ${db_name}_fleets where login_id = '$user[login_id]'");
	$flt_cnt = dbr();
	$row_c = round($flt_cnt['flcnt'] * .5);
	// create the columned table for each fleet fleet table
	$text .= "<p><table border=\"0\" cellspacing=\"0\" width=\"100%\"><tr><td width=\"50%\" valign=\"top\">";
	$ct5 = 0;

	db(__FILE__,__LINE__,"select * from ${db_name}_fleets where login_id = '$user[login_id]' order by fleet_num asc");
	while($t_fleet = dbr())
	{
		// switch to new column after every fleet in columned table
		if($ct5 == $row_c) {
			$text .= "</td><td width=50% valign=top>";//add right column as necessary to split info
		}

		//add a quick link
		$text .= "<a name=\"fleet".$t_fleet['fleet_id']."\" id=\"fleet".$t_fleet['fleet_id']."\"></a>";
		$text .= "<span class=\"h5\">Fleet No. $t_fleet[fleet_num]: \"$t_fleet[fleet_name]\" in Star System #$t_fleet[location]</span>";
		$text .= "<p>".make_table_width(array("Ship Name","Class Name","Location","Fighters","<a href='javascript:TickAll(\"new_transfer\")'>Invert<br />Selection</a>"),"350");

		db2(__FILE__,__LINE__,"select ship_id, ship_name, fighters, location, class_name from ${db_name}_ships where fleet_id = '$t_fleet[fleet_id]' && login_id = '$user[login_id]' && ship_id != '$user[ship_id]' order by ship_id asc, ship_categ asc, ship_name asc");
		$c_ship = 0;
		while($t_ship = dbr2())
		{
			db3(__FILE__,__LINE__,"select buffer_id from ${db_name}_transfer_buffer where ship_id = '$t_ship[ship_id]'");
			$t_buffer = dbr3();
			if(!empty($t_buffer))
			{
				continue;
			}
			$t_name = $t_ship['ship_name'];
			$t_class = $t_ship['class_name'];
			$t_location = "SS #".$t_ship['location'];
			$t_fighters = $t_ship['fighters'];
			$t_option = "<div align=\"center\"><input type=\"checkbox\" name=\"t_newlist[$t_ship[ship_id]]\" value=\"$t_ship[ship_id]\" /></div>";
			$text .= make_row(array($t_name,$t_class,$t_location,$t_fighters,$t_option));
			$c_ship++;
		}
		$text .= "</table></p>";

		//advance column counter
		$ct5 += 1;
	}

	// close off the columned table
	$text .= "</td></tr></table></p>";

	$text .= "<br /><span class=\"h4\">Step 3: Submit your Transfer Orders!</span><p><input type=\"submit\" value=\"Create!\" /></p></form>";

	print_page("New Ship Transfer",$text);
}


//---------------------------------------------------------------------------------------------------------//
// This section generate the new Ship Transfer entry and inform the intended player of their good fortune  //
//---------------------------------------------------------------------------------------------------------//


if(isset($_POST['t_new']))
{
	if(empty($_POST['t_newlist']))
	{
		$error_str = $text."<p>You do not appear to have selected any ships to be transferred to another player.<br />Please return to the Create New Transfer menu if you wish to transfer ships to another player.</p>";
		print_page("Accessing Transfer Data",$error_str);
	}
	elseif(!isset($_POST['transfer_to']))
	{
		$error_str = $text."<p>You do not appear to have selected a player to transfer ships to.<br />Please return to the Create New Transfer menu if you wish to transfer ships to another player.</p>";
		print_page("Accessing Transfer Data",$error_str);
	}
	else
	{
		$text .= "<p>Please note that Ship Transfers are not completed until the chosen player accepts them. Also Ship Transfers must be completed within 48hrs or they will be cancelled and deleted from the Ship Transfer Buffer<br /><br />The results of your Transfer Request are as follows:<br /><br />";
		while($var = each($_POST['t_newlist'])) {
			db(__FILE__,__LINE__,"select ship_id, class_name, ship_categ from ${db_name}_ships where ship_id = '$var[value]'");
			$new_t_ship = dbr();
			if(empty($new_t_ship))
			{
				continue;
			}
			else
			{
				$transferee = array("login_id" => $_POST['transfer_to']);
				dbn(__FILE__,__LINE__,"insert into ${db_name}_transfer_buffer (owner_id, ship_id, class_name, ship_categ, transferto_id, timestamp) values ('$user[login_id]','$new_t_ship[ship_id]','$new_t_ship[class_name]','$new_t_ship[ship_categ]','$transfer_to',".time().")");
				$text .= "$new_t_ship[class_name] transferred to ".print_name($transferee)." <br />";
			}
		}
		$text .= "</p>";
		print_page("New Ship Transfer",$text);
	}
}

//---------------------------------------------------------------------------------------------------------//
// Allow a player to view any transfers they created and cancel them if they wish                          //
//---------------------------------------------------------------------------------------------------------//

if(isset($_GET['v_transfer']))
{
	$text .= "<p>From this menu you can view ship transfers you made to other players, which are still outstanding, and cancel them if you wish. You cannot of course cancel completed transfers!</p>"
	."<br /><span class=\"h4\">Your Ship Transfers</span><br /><br />";

	$text .= make_table(array("Time Transferred","Transfered To","Ship Class","Current Location","<a href='javascript:TickAll(\"delete_transfer\")'>Invert<br />Selection</a>",));

	$text .= "<form method=\"post\" action=\"ship_transfer.php\" name=\"delete_transfer\">"
	."<input type=\"hidden\" name=\"mass_delete\" value=\"1\" />";

	db(__FILE__,__LINE__,"select * from ${db_name}_transfer_buffer where owner_id = '$user[login_id]' order by timestamp desc, transferto_id, ship_categ asc, class_name");
	unset($t_record);
	while($t_record = dbr()) {
		db2(__FILE__,__LINE__,"select login_id from ${db_name}_users where login_id = '$t_record[transferto_id]'");
		$t_transferee = dbr2();
		db2(__FILE__,__LINE__,"select location, login_id from ${db_name}_ships where ship_id = '$t_record[ship_id]'");
		$t_ship = dbr2();
		if(empty($t_ship) || $t_ship['login_id'] != $user['login_id'])
		{
			dbn(__FILE__,__LINE__,"delete from ${db_name}_transfer_buffer where ship_id = '$t_record[ship_id]'");
			continue;
		}
		$t_date = date("D M j G:i:s", $t_record['timestamp']);
		$t_name = "<div align=\"center\">".print_name($t_transferee)."</div>";
		$t_location = "<div align=\"center\">SS #".$t_ship['location']."</div>";
		$t_class = $t_record['class_name'];
		$t_option = "<div align=\"center\"><input type=\"checkbox\" name=\"delete_list[$t_record[buffer_id]]\" value=\"$t_record[buffer_id]\" /></div>";
		$text .= make_row(array($t_date, $t_name, $t_class, $t_location, $t_option));
		$buffer_open = 1;
	}
	$text .= "</table>";
	if(!isset($buffer_open))
	{
		$text .= "<p>You have no created Ship Transfers at this time.</p>";
	}
	else
	{
		$text .= "<p><input type=\"submit\" value=\"Cancel!\" /></form></p>";
	}
	print_page("Your Initiated Transfers",$text);
}

//---------------------------------------------------------------------------------------------------------//
// This section executes a players decision to delete a transfer they created at an earlier date           //
//---------------------------------------------------------------------------------------------------------//

if(isset($_POST['mass_delete']))
{
	if(empty($_POST['delete_list']))
	{
		$error_str = $text."<p>You do not appear to have chosen any Ship Transfers to delete.<br />You must select at least one Ship Transfer to use this function.</p>";
		print_page("Accessing Transfer Data",$error_str);
	}
	else
	{
		$t_text .= "<p>"; //open paragraph tag
		while($var = each($_POST['delete_list'])) {
			db(__FILE__,__LINE__,"select class_name, transferto_id from ${db_name}_transfer_buffer where buffer_id = '$var[value]'");
			$t_entry = dbr();
			$t_to = array("login_id" => $t_entry['transferto_id']);
			db(__FILE__,__LINE__,"delete from ${db_name}_transfer_buffer where buffer_id = '$var[value]'");
			$t_text .= "Transfer of a $t_entry[class_name] to ".print_name($t_to)." cancelled.<br />";
		}
		if(!isset($t_text))
		{
			$text .= "<p>No ship transfers have been deleted - please check you have correctly tried to delete a ship transfer.</p>";
		}
		else
		{
			$t_text .= "</p>"; //close paragraph
			$text .= "<p>The results of your chosen actions are:</p>".$t_text;
		}
		print_page("Ship Transfer Deletion",$text);
	}
}




// Catch all function for those brave souls who try their hands at Url doping.
$error_str = $text."<p>You have apparently used a false url - please use the menu links to access this function. And remember that Url editing is considered a form of cheating!</p>";
print_page("Url Error",$error_str);

?>
