<?php
include_once("includes/nocache.inc.php");
/*
//
File:			mining.php
Objective:		Add-0n to allow greater control over who mines what on a fleet by fleet basis.
Version:		QS 2.2.0 beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	15 April 2003		Date Modified:	21 April 2004

Copyright (c) 2003, 2004 by P�draic Brady aka Maugrim The Reaper aka That Drunken Irish Developer

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

/*
dev plan as at 24 Mar 2004

-xhtml compliance (think this barely html compliant...lol) ->DONE
-get rid of non-mining vessels ->DONE
-do anything to clean up those quick-fix links ->DONE
-do a little work on using this with the new resource table per database ->DONE
-implement a preview (per hour basis) on mining result and probable value ->OUTSTANDING
-add a page heading ->DONE
-implement _get/_post ->DONE
-forget the above and just rewrite the mess??? :) ->DONE ->DONE ->DONE
-need to start using that print_fleetname func - gives link to fleet overview ->DONE
-drop the fighter info - irrelevant for mining ->DONE
-as above for config ->DONE
-'minable' - is that even spelt right?  ->???? [e before able so no double a]
-use radio options. know passing fleet details is difficult - but can pass array as part of option value??? ->DONE
-seriously, rewrite this mess...'tis a short file  ->DONE ->DONE ->DONE
*/

// start of rewritten code April 2004

require_once("user.inc.php");

array_push($FILE_LIST, basename(__FILE__)); //used for including translation files

sudden_death_check($user);

// Page heading + Sub-Menu
$text = Create_PageTitleBlock("Mining Status for Star System $user[location]",array("Mining Overview" => "mining.php?overview=1"));

// Control of whether dark matter is shown or not is currently based on if the current ship can mine it
// This should be changed to if any ship in this location can mine it. Then the darkmatter mining radio
// buttons should only show up for ships that can mine the darkmatter.
$show_darkmatter = $user_ship['mine_rate_darkmatter'] > 0 ? 1 : 0;

$text .= "<br />".Display_Resources($user['location'], $show_darkmatter);
get_star();

if($_GET['overview'] == 1)
{
	$m_count = 0;
	$text .= "<p>This is the Mining Overview page. From here you can see what your Fleets are mining, and alter their mining activities.</p>";
	$text .= "
		<form method=\"post\" action=\"mining.php\" name=\"mine_mode_form\" id=\"mine_mode_form\">
		<input type=\"hidden\" name=\"set_mining\" value=\"1\" />
		<table cellpadding=\"1\" cellspacing=\"0\" width=\"75%\" class=\"all\" style=\"border-bottom: 0px;\">
			<tr>
				<th>
					Fleet Details
				</th>
	";
	if($show_darkmatter) {
		db(__FILE__,__LINE__,"select name, sql_name from ${db_name}_resources where resource_categ = 0");
	} else {
		db(__FILE__,__LINE__,"select name, sql_name from ${db_name}_resources where resource_categ = 0 and sql_name <> 'darkmatter'");
	}
	$res_count = 0;
	while($res = dbr()) {
		if($star[$res['sql_name']] > 0)
		{
			$res_count++;
			$text .= "
				<th>
					<a href=\"javascript:CheckRadioButtons('mine_mode_form','mode[',$res_count)\">$res[name]</a>
				</th>
			";
		}
	}
	$res_count++;
	$text .= "
				<th>
					<a href=\"javascript:CheckRadioButtons('mine_mode_form','mode[',$res_count)\">No Mining</a>
				</th>
	";
	$text .= "
			</tr>
	";
	db(__FILE__,__LINE__,"select * from ${db_name}_fleets where login_id = '$user[login_id]' and location = '$user[location]' "
		. "order by fleet_name asc");
	//should be very careful not to re-use same db1/2/3/4 functions or one of the 3 interlocking while loops will terminate
	while($f_list = dbr()) {
		db2(__FILE__,__LINE__,"select count(ship_id) as scount from ${db_name}_ships where fleet_id = '$f_list[fleet_id]'");
		$sc = dbr2();
		if($sc['scount'] <= 0)
		{
			continue;
		}
		$text .= "
			<tr>
				<td class=\"border_bottom\" colspan=\"10\">
					<br />
					Fleet No. <b class=b1>$f_list[fleet_num]</b>: "
					 	. Print_FleetName($f_list['fleet_id'],$f_list['fleet_name']) . "
					<br />
				</td>
			</tr>
		";
		db2(__FILE__,__LINE__,"select distinct class_name from ${db_name}_ships where fleet_id = '$f_list[fleet_id]' and (ship_categ = 1 or ship_categ = 2 or ship_categ = 5)");
		while($class_list = dbr2()) {
			db3(__FILE__,__LINE__,"select distinct mine_mode from ${db_name}_ships where login_id = '$user[login_id]' and fleet_id = '$f_list[fleet_id]' and class_name = '$class_list[class_name]'");
			while($unique_mining = dbr3()) {
				$m_count += 1;
				$text .= Append_MineMode($show_darkmatter);
			}
		}
	}
	$text .= "
	</table>
		<br />
		<input type=\"submit\" name=\"submit\" value=\"Set Mining\" />
	</form>
	";
	print_page("Mining Overview",$text);
}



// set the new mining states
if($_POST['set_mining'] == 1)
{
	foreach($_POST['mode'] as $key=>$val) {
		$o_array = unserialize(urldecode($val));
		if(is_array($o_array))
		{
			//print_array($o_array);
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set mine_mode = '$o_array[resource_id]' "
				. "where login_id = '$user[login_id]' and fleet_id = '$o_array[fleet_id]' and "
					. "class_name = '$o_array[class_name]' and mine_mode = '$o_array[mine_mode]'");
		}
	}
	print_page("Mining Updates",$text."<br />The status of all mining vessels in your Fleet in Star System #$user[location] have been updated to reflect your changes.");
}


// remove repetitive db calls!

// to support check-boxes for such specific group - possibly best method is pass each array after being serialised.
function Append_MineMode($show_darkmatter) {
	global $f_list, $class_list, $unique_mining, $user, $db_name, $m_count;
	db4(__FILE__,__LINE__,"select count(ship_id) as numships from ${db_name}_ships where fleet_id = '$f_list[fleet_id]' and login_id = '$user[login_id]' and class_name = '$class_list[class_name]' and mine_mode = '$unique_mining[mine_mode]'");
	$cnt = dbr4();
	$text .= "
	<tr>
		<td class=\"border_bottom\">
			$cnt[numships] $class_list[class_name] Ships
		</td>
	";
	db4(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = '$user[location]'");
	$star = dbr4();
	if($show_darkmatter) {
		db4(__FILE__,__LINE__,"select resource_id, sql_name from ${db_name}_resources where resource_categ = 0 order by resource_id asc");
	} else {
		db4(__FILE__,__LINE__,"select resource_id, sql_name from ${db_name}_resources where resource_categ = 0 and sql_name <> 'darkmatter' order by resource_id asc");
	}
	while ($res = dbr4()) {
		if($star[$res['sql_name']] > 0)
		{
			// create array to be serialised and entered as input name value
			$form_name = NULL;
			$mode_name = NULL;
			$form_name = array();
			$form_name['fleet_id'] = $f_list['fleet_id'];
			$form_name['class_name'] = $class_list['class_name'];
			$form_name['mine_mode'] = $unique_mining['mine_mode'];
			$form_name['resource_id'] = $res['resource_id'];

			//DEV: painful stuff passing arrays :) - I'll pass them as a urlencoded serialized string
			//on ACTION side will unserialize the urldecoded string into the original array
			//Each option is stored within a holding $_POST['mode'] array, with numbered keys
			$mode_name = urlencode(serialize($form_name));

			//will be unserialized after submission
			$checked = $unique_mining['mine_mode'] == $res['resource_id'] ? 'checked' : '';
			$text .= "
				<td class=\"border_bottom\" style=\"text-align: center;\">
					<input type=\"radio\" name=\"mode[$m_count]\" value=\"".$mode_name."\" $checked />
				</td>
				";
		}
	}
	// create array to be serialised and entered as input name value
			$form_name = NULL;
			$mode_name = NULL;
			$form_name = array();
			$form_name['fleet_id'] = $f_list['fleet_id'];
			$form_name['class_name'] = $class_list['class_name'];
			$form_name['mine_mode'] = $unique_mining['mine_mode'];
			$form_name['resource_id'] = 0; // disables mining
			$mode_name = urlencode(serialize($form_name));
	// As suggested by Hades, throw in the option to disable mining for insane individuals...:)
	$checked = $unique_mining['mine_mode'] == 0 ? 'checked' : '';
	$text .= "
		<td class=\"border_bottom\" style=\"text-align: center;\">
			<input type=\"radio\" name=\"mode[$m_count]\" value=\"".$mode_name."\" $checked />
		</td>
	</tr>
	";
	return $text;
}


print_page("Mining Menu",$text."No variables specified - please return to game pages and select a valid link.");


?>