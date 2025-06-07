<?php

/*
//
File:			clan_funcs.inc.php
Objective:		some print functions handling display of clan details
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	18 October 2003	Date Modified:	22 October 2003

Copyright (c) 2003, 2004 by Pádraic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

function Print_ClanDetailsFull() {
	global $cd, $res3, $clan_l, $t_figs, $t_fcap, $maths3, $res1, $res2, $maths1, $maths2;

	$clan_text = "
	<div align=\"center\">
	<table cellspacing=0 cellpadding=0 class=all width=650>
		<tr>
			<th style='border-bottom: 0px'>
				Clan Details Table
			</th>
		</tr>
		<tr>
			<td>
				<table cellspacing=0 cellpadding=0 width=100% align=center>
					<tr>
						<td align=center width=25% class=color_bg>
							Clan Name
						</td>
						<td align=center width=25% class=color_bg>
							Clan Symbol
						</td>
						<td align=center width=25% class=color_bg>
							Clan Leader
						</td>
						<td align=center width=25% class=color_bg style='border-right: 0px'>
							Clan Members
						</td>
					</tr>
					<tr>
						<td align=center width=25%>
							".$cd['clan_name']."
						</td>
						<td align=center width=25%>
							<font color=#".$cd['sym_color'].">".$cd['symbol']."</font>
						</td>
						<td align=center width=25%>
							".$clan_l['login_name']."
						</td>
						<td align=center width=25%>
							".$res3['members']."
						</td>
					</tr>
				</table><br />
			</td>
		</tr>
		<tr>
			<td>
				<table cellspacing=0 cellpadding=0 width=100% align=center>
					<tr>
						<td align=center width=20% class=color_bg>
							Cash
						</td>
						<td align=center width=20% class=color_bg>
							Tech Units
						</td>
						<td align=center width=20% class=color_bg>
							Turns
						</td>
						<td align=center width=20% class=color_bg>
							Turns Run
						</td>
						<td align=center width=20% class=color_bg style='border-right: 0px'>
							Total Fighters
						</td>
					</tr>
					<tr>
						<td align=center width=20%>
							".calc_perc($res3['cash'],$maths3['cash'])."
						</td>
						<td align=center width=20%>
							".calc_perc($res3['tech'],$maths3['tech'])."
						</td>
						<td align=center width=20%>
							".calc_perc($res3['turns'],$maths3['turns'])."
						</td>
						<td align=center width=20%>
							".calc_perc($res3['trun'],$maths3['trun'])."
						</td>
						<td align=center width=20%>
							".calc_perc($t_figs,$t_fcap)."
						</td>
					</tr>
				</table><br />
			</td>
		</tr>
		<tr>
			<td>
				<table cellspacing=0 cellpadding=0 width=100% align=center>
					<tr>
						<td align=center width=20% class=color_bg>
							Ships Killed
						</td>
						<td align=center width=20% class=color_bg>
							Ships Lost
						</td>
						<td align=center width=20% class=color_bg>
							Fighters Killed
						</td>
						<td align=center width=20% class=color_bg>
							Fighters Lost
						</td>
						<td align=center width=20% class=color_bg style='border-right: 0px'>
							Total Score
						</td>
					</tr>
					<tr>
						<td align=center width=20%>
							".calc_perc($res3['skilled'],$maths3['skilled'])."
						</td>
						<td align=center width=20%>
							".calc_perc($res3['slost'],$maths3['slost'])."
						</td>
						<td align=center width=20%>
							".calc_perc($res3['fkilled'],$maths3['fkilled'])."
						</td>
						<td align=center width=20%>
							".calc_perc($res3['flost'],$maths3['flost'])."
						</td>
						<td align=center width=20%>
							".calc_perc($res3['score'],$maths3['score'])."
						</td>
					</tr>
				</table><br />
			</td>
		</tr>

		<tr>
			<td>
				<table cellspacing=0 cellpadding=0 width=100% align=center>
					<tr>
						<td align=center width=20% class=color_bg>
							Planets
						</td>
						<td align=center width=20% class=color_bg>
							Planet Fighters
						</td>
						<td align=center width=20% class=color_bg>
							Ships
						</td>
						<td align=center width=20% class=color_bg>
							Ship Fighters
						</td>
						<td align=center width=20% class=color_bg style='border-right: 0px'>
							Colonists
						</td>
					</tr>
					<tr>
						<td align=center width=20%>
							".calc_perc($res1['planets'],$maths1['planets'])."
						</td>
						<td align=center width=20%>
							".calc_perc($res1['pfigs'],$maths1['pfigs'])."
						</td>
						<td align=center width=20%>
							".calc_perc($res2['ships'],$maths2['ships'])."
						</td>
						<td align=center width=20%>
							".calc_perc($res2['sfigs'],$maths2['sfigs'])."
						</td>
						<td align=center width=20%>
							".RM_Zero($res1['colon'])."
						</td>
					</tr>
				</table><br />
			</td>
		</tr>
		<tr>
			<td>
				<table cellspacing=0 cellpadding=0 width=100% align=center>
					<tr>
						<td align=center width=25% class=color_bg>
							Launch Pads
						</td>
						<td align=center width=25% class=color_bg>
							Research Facilities
						</td>
						<td align=center width=25% class=color_bg>
							Shield Generators
						</td>
						<td align=center width=25% class=color_bg style='border-right: 0px'>
							Wormhole Generators
						</td>
					</tr>
					<tr>
						<td align=center width=25%>
							".RM_Zero($res1['lpad'])."
						</td>
						<td align=center width=25%>
							".RM_Zero($res1['rfac'])."
						</td>
						<td align=center width=25%>
							".$res1['sgens']."
						</td>
						<td align=center width=25%>
							".RM_Zero($res1['whgs'])."
						</td>
					</tr>
				</table><br />
			</td>
		</tr>
		<tr>
			<td>
				<table cellspacing=0 cellpadding=0 width=100% align=center>
					<tr>
						<td align=center width=16.66% class=color_bg>
							Genesis Devices
						</td>
						<td align=center width=16.66% class=color_bg>
							Terra Imploders
						</td>
						<td align=center width=16.66% class=color_bg>
							Alpha Bombs
						</td>
						<td align=center width=16.66% class=color_bg>
							Gamma Bombs
						</td>
						<td align=center width=16.66% class=color_bg>
							Delta Bombs
						</td>
						<td align=center width=16.66% class=color_bg style='border-right: 0px'>
							Supernova Effectors
						</td>
					</tr>
					<tr>
						<td align=center width=17%>
							".$res3['gen']."
						</td>
						<td align=center width=17%>
							".$res3['imploder']."
						</td>
						<td align=center width=16%>
							".$res3['alpha']."
						</td>
						<td align=center width=16%>
							".$res3['gamma']."
						</td>
						<td align=center width=17%>
							".$res3['delta']."
						</td>
						<td align=center width=17%>
							".$res3['alpha']."
						</td>
					</tr>
				</table><br />
			</td>
		</tr>
	</table>
	</div>
	";

	return $clan_text;

}


function RM_Zero($var) {
	if(!isset($var))
	{
		$var = 0;
	}
	return $var;
}


//update clan score, fighter kills, and member count
function update_clans() {
	global $db_name, $clan_member_limit;

	// score and kills are totalled and divided by 10 * max possible members to help the scores fit into unsigned ints
	$cml = (isset($clan_member_limit) && $clan_member_limit > 0) ? $clan_member_limit * 10 : 10;

	db(__FILE__,__LINE__,"select clan_id, sum(score) as tscore, sum(fighters_killed) as fkills, count(login_id) as member_cnt "
		. "from ${db_name}_users where clan_id > 0 group by clan_id");
	while($clans = dbr()) {
		$clan_score  = round($clans['tscore'] / $cml);
		$clan_fkills = round($clans['tkills'] / $cml);
		dbn(__FILE__,__LINE__,"update ${db_name}_clans "
			. "set clan_score = '$clan_score', members = '$clans[member_cnt]', fighter_kills = '$clan_fkills' "
			. "where clan_id = '$clans[clan_id]'");
	}
}

?>