<?php
include_once("includes/nocache.inc.php");
$filename = "ship_combat.php";
/*
//
File:			ship_combat.php
Objective:		file for controlling the outcome and display of fleet to fleet ship combat
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	30 December 2003	Date Modified:	26 March 2004

Copyright (c) 2003, 2004 by Pádraic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/


/*
1. check attacking force is a fleet no more than 25 ships in number
2. collect information about both fleets in an array
3. rank all ships (using array funcs?)
4. generate attack between highest ranking ships, until one is destroyed
5. re-rank ships and continue attacks
6. When one array is empty - post results and send messages - victor is last fleet standing.
*/

require_once("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

include_once("includes/combat_funcs.inc.php");

sudden_death_check($user);

//seed random number generator
mt_srand((double)microtime()*1000000);

$attack_detail_flag = 0; // decides whether detailed steps confirmed/made or full battle performed automatically

// 1 = uship wins - display results
// 2 = tship wins - ''
// 3 = no winner thus far - ''

if(!isset($ufleetid))
{
	$ufleetid = $user_fleet['fleet_id'];
}

if(!isset($target))
{
	print_page("Invalid Request - No target selected!","<p>How do you expect to attack anything without selecting a fleet to target?</p>");
}
elseif($att_sure != "yes")
{
	$start = Include_FirstConfirm($target);
	print_page("Commence Attack","<p>Do you wish to commence an attack against this fleet? You may not disengage enemy until completion of combat.<br /><br />$start</p>");
}

db(__FILE__,__LINE__,"select * from ${db_name}_fleets where fleet_id = '$target' and location = '$user_fleet[location]' and login_id != '$user[login_id]'");
$targ_fleet = dbr();
if(empty($targ_fleet))
{
	print_page("No Target!","<p>The fleet you have chosen is no longer present in this Star System. Either it has moved, or been disbanded, or maybe been destroyed by another player in the intervening period.</p>");
}

// loop through both fleets to perform combat and fetch results
while($attack_complete <= 0) {
		db(__FILE__,__LINE__,"select * from ${db_name}_ships where fleet_id = '$targ_fleet[fleet_id]' and location = '$targ_fleet[location]' and login_id != '$user[login_id]' order by fighters desc limit 1");
		$tship_array = dbr();
		db(__FILE__,__LINE__,"select * from ${db_name}_ships where fleet_id = '$user_fleet[fleet_id]' and location = '$targ_fleet[location]' and login_id = '$user[login_id]' order by fighters desc limit 1");
		$uship_array = dbr();

		// this is a check to prevent non-attack vessels resulting in a lost battle message
		//db(__FILE__,__LINE__,"select count(ship_id) as ships from ${db_name}_ships where fleet_id = '$user_fleet[fleet_id]' and location = '$targ_fleet[location]' and login_id = '$user[login_id]'");
		//$na_chk = dbr();

		//if(empty($uship_array) && $na_chk['ships'] > 0)
		//{
			//db(__FILE__,__LINE__,"select * from ${db_name}_ships where fleet_id = '$user_fleet[fleet_id]' and location = '$targ_fleet[location]' and login_id = '$user[login_id]' and config NOT REGEXP 'po' order by fighters desc limit 1");
			//$uship_array = dbr();
		//}
		//---------------------------------------------------------------------------------

		if(empty($tship_array))
		{
			$attack_complete = 1;
			// delete the defeated empty fleet
			dbn(__FILE__,__LINE__,"delete from ${db_name}_fleets where fleet_id = '$targ_fleet[fleet_id]' and fleet_name = '$targ_fleet[fleet_name]'");
			send_message($targ_fleet['login_id'],"<p>Fleet #$targ_fleet[fleet_num] <b>$targ_fleet[fleet_name]</b> has been attacked by <b class=\"b1\">$user[login_name]</b>. Your Fleet was completely destroyed. Here is a summary of the battle.</p><h4>Historical Result Listing</h4>$output");
			$rs = "<br /><a href=\"location.php\">Return to Star System</a>";
			print_page("Enemy Defeated!","You have defeated the enemy Fleet! <br />You may now return to the Star System to target another fleet, or continue your voyage elsewhere. <br />Congratulations on your victory!<br /><h4>Historical Result Listing</h4>$output");
		}
		elseif(empty($uship_array))
		{
			$attack_complete = 1;
			// delete the defeated empty fleet
			dbn(__FILE__,__LINE__,"delete from ${db_name}_fleets where fleet_id = '$user_fleet[fleet_id]' and fleet_name = '$user_fleet[fleet_name]'");
			send_message($targ_fleet['login_id'],"<p>Fleet #$targ_fleet[fleet_num] <b>$targ_fleet[fleet_name]</b> has been attacked by <b class=\"b1\">$user[login_name]</b>. Your Fleet was victorious and crushed the foolish attacker. Here is a summary of the battle.</p><h4>Historical Result Listing</h4>$output");
			$rs = "<br /><a href=\"location.php\">Return to Star System</a>";
			print_page("Enemy Victorious!","<p>The enemy Fleet has crushed your attack.<br />The few survivors from your ships have been mercilessly hunted down and annihilated.<br />Your hoped for victory is now naught but ashes...</p><h4>Historical Result Listing</h4>$output");
		}

		// check that fleet has not moved during attack
		db(__FILE__,__LINE__,"select fleet_id from ${db_name}_fleets where fleet_id = '$target' and location = '$user_fleet[location]' and login_id != '$user[login_id]'");
		$ref_fleet = dbr();
		if(empty($ref_fleet))
		{
			send_message($targ_fleet['login_id'],"<p>Fleet #$targ_fleet[fleet_num] <b>$targ_fleet[fleet_name]</b> has been attacked by <b class=\"b1\">$user[login_name]</b>. Your Fleet escaped the attack. Here is a summary of the battle.</p><h4>Historical Result Listing</h4>$output");
			print_page("No Target!","<p>The Fleet you were attacking has vanished from your sensors! It would appear that it has escaped. Here is a history of the attack before the Fleet disappeared.</p><h4>Historical Result Listing</h4>$output");
		}
		$result = Generate_Attack($uship_array, $tship_array, $target);
		$output .= $result['stats'];
}





?>