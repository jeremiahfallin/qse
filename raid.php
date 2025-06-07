<?php

/*
//
File:			raid.php
Objective:		Management of ship raiding
Version:		QS 2.1.22
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	1 August 2002	Date Modified:	15 September 2003

Copyright (c) 2003, 2004 by Pádraic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.

Quantum Star SE asks that you make all changes publicly available to
help further develop and improve this game.
//
*/

require_once("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

require_once("includes/fleet_funcs.inc.php");

sudden_death_check($user);

#seed random number generator
mt_srand((double)microtime()*1000000);

if($flag_raids == 0 || $flag_research == 0) {
	print_page("Raids Disabled","Raiding is disabled in the current game! Stop playing with the url...or else...");
}


// Raiding
if(eregi("rd",$user_ship[config]) && isset($rdtarget) && $flag_raids == 1 && $flag_research == 1) {

	$final_txt = '';
	$target_config = get_config($rdtarget);
	$ship_config = get_config($user[ship_id]);
	// Get target records
	db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = '$rdtarget'");
	$target_ship = dbr();
	db(__FILE__,__LINE__,"select * from ${db_name}_users where login_id = '$target_ship[login_id]'");
	$target = dbr();

	#protect the admin
	if ($target[login_id] == 1) {
		print_page("Admin Protected","<br />You may not raid Admin ships!");
	}
	if($sure != 'yes') {
		get_var('Raid Ship','raid.php',$turns_txt.$rdtext."Are you sure you want to attempt a raid on the <b class=b1>$target_ship[ship_name]</b> and remove all its cargo?",'sure','yes','location.php');
	}
	$target_ship_bays = $target_ship[cargo_bays] - ($target_ship[metal] - $target_ship[fuel] - $target_ship[elect] - $target_ship[colon] - $target_ship[organ]- $target_ship[scrap]- $target_ship[darkmatter]);
	if($target_ship[fighters] > 10 || $target_ship[shields] > 0) {
		$error_str .= "<p>You must reduce all fighters to <b>10</b> or less and shields to <b>0</b> to enable a raid to commence.";
		print_page ("Raid Attempt",$error_str);
	} elseif ($target_ship[cargo_bays] == 0) {
		$error_str .= "<p>Your target must have cargo bays, otherwise what are you going to raid?";
		print_page ("Raid Attempt",$error_str);
	} else {
		charge_turns(1);
		if(eregi("dy",$target_ship[config])) {
			$rand101 = 1;
		} else {
			$rand101 = mt_rand(1,5);
		}
		if($rand101 > 1){

			//what to raid?
			if($target_ship[metal] > 0) {
				$final_text .= take_loot(2);
			} else {
				$final_text .= "<br /><b>0</b> Metal found on ship.";
			}
			if($target_ship[fuel] > 0) {
				$final_text .= take_loot(3);
			} else {
				$final_text .= "<br /><b>0</b> Fuel found on ship.";
			}
			if($target_ship[elect] > 0) {
				$final_text .= take_loot(4);
			} else {
				$final_text .= "<br /><b>0</b> Electronics found on ship.";
			}
			if($target_ship[organ] > 0) {
				$final_text .= take_loot(5);
			} else {
				$final_text .= "<br /><b>0</b> Organics found on ship.";
			}
			if($target_ship[darkmatter] > 0 && $flag_research == 1) {
				$final_text .= take_loot(6);
			} else {
				$final_text .= "<br /><b>0</b> Dark-Matter found on ship.";
			}

			$luck = mt_rand(1,20);
			$cash_given = round((($target_ship_bays / 5) + ($target_ship[colon] / 2)) * $luck);
			give_cash($cash_given);
			$error_str .= "<p>Your Raid was a success. You successfully looted the <b class=b1>$target_ship[ship_name]</b> belonging to <b class=b1>$target_ship[login_name]</b>. <br />Your <b class=b1>\"Plunder\"</b> report states:<br /><br />";
			$error_str .= $final_text;
			$error_str .= "<br /><br />You obtained <b>$cash_given</b> Credits from less important ship items.";
			$attack_damage = mt_rand(25,100);
			//destroy target after raid
			$dam_take = damage_ship($attack_damage,-1,0,$user,$target,$target_ship);
			if($dam_take == 1) {
				post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>s $target_ship[class_name] during a raid.");
				if($target_ship[shipclass] == 2) {
					$error_str .= "<br />And successfully blew <b class=b1>$target[login_name]</b>'s Escape Pod to smitherines.";
					if($sudden_death){
						$error_str .= "As the game is in Sudden Death(SD), <b class=b1>$target[login_name]</b> is out of the game permanently.";
						post_news("<b class=b1>$target[login_name]</b> has been completely killed in a raid, and is out of the game permanently.");
					}
					send_message($target[login_id],"Your <b class=b1>$target_ship[ship_name]</b> was destroyed by the <b class=b1>$user_ship[ship_name]</b> in a raid.");
					if($target[bounty] > 0 && $alternate_bounty_sys == 0) {
						$error_str .= "<br /><p>You have claimed the <b>$target[bounty]</b> Credit bounty that was on <b class=b1>$target[login_name]</b>s head.<br />";
						post_news("The <b>$target[bounty]</b> bounty on <b class=b1>$target[login_name]</b> has been claimed by <b class=b1>$user[login_name]</b>");
						dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + $target[bounty] where login_id = '$user[login_id]'");
						dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$target[login_id]'");
					} else {
						$error_str .= "<br /><p>You have claimed the remaining <b>$target[bounty]</b> Credit bounty that was on <b class=b1>$target[login_name]</b>s head after the destruction of their fleet.<br />";
						post_news("The <b>$target[bounty]</b> Credit bounty remaining on <b class=b1>$target[login_name]</b>s head after the destruction of their fleet has been claimed by <b class=b1>$user[login_name]</b>");
						dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + $target[bounty] where login_id = '$user[login_id]'");
						dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$target[login_id]'");
					}
				} else {
					$error_str .= "<br />And destroyed <b class=b1>$target[login_name]</b>'s $target_ship[class_name] in the raid.";
					send_message($target[login_id],"Your <b class=b1>$target_ship[ship_name]</b> was destroyed by the <b class=b1>$user_ship[ship_name]</b> during a raid.");
				}
			} elseif ($dam_take==2){
				$error_str .= "<br /><p>And destroyed <b class=b1>$target_ship[login_name]</b>'s $target_ship[class_name].";
				$error_str .= "<br /><b class=b1>$target_ship[login_name]</b> ejected in an Escape Pod.";
				if($target_ship[location] == 1) {
					post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>s $target_ship[class_name] in the <b>Sol</b> System during a raid.");
				} else {
					post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>s $target_ship[class_name] during a raid.");
				}
				send_message($target[login_id],"Your <b class=b1>$target_ship[ship_name]</b> was destroyed by the <b class=b1>$user_ship[ship_name]</b> during a raid.");
			}
			print_page ("Raid Success!",$error_str);
		} else {//raid failed
			$attack_damage = mt_rand(1000,1500);
			$dam_take = damage_ship($attack_damage,-1,0,$user,$target,$target_ship);
			if($dam_take == 1) {
				post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>s $target_ship[class_name] during a raid.");
				if($target_ship[shipclass] == 2) {
					$error_str .= "You blew <b class=b1>$target[login_name]</b>'s Escape Pod to smitherines in during the failed raid.";
					if($sudden_death){
						$error_str .= "As the game is in Sudden Death(SD), <b class=b1>$target[login_name]</b> is out of the game permanently.";
						post_news("<b class=b1>$target[login_name]</b> has been completely killed in a raid, and is out of the game permanently.");
					}
					send_message($target[login_id],"Your <b class=b1>$target_ship[ship_name]</b> was destroyed by the <b class=b1>$user_ship[ship_name]</b> in a raid.");
					if($target[bounty] > 0 && $alternate_bounty_sys == 0) {
						$error_str .= "<br /><p>You have claimed the <b>$target[bounty]</b> Credit bounty that was on <b class=b1>$target[login_name]</b>s head.<br />";
						post_news("The <b>$target[bounty]</b> bounty on <b class=b1>$target[login_name]</b> has been claimed by <b class=b1>$user[login_name]</b>");
						dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + $target[bounty] where login_id = '$user[login_id]'");
						dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$target[login_id]'");
					} elseif ($target[bounty] > 0) {
						$error_str .= "<br /><p>You have claimed the remaining <b>$target[bounty]</b> Credit bounty that was on <b class=b1>$target[login_name]</b>s head after the destruction of their fleet.<br />";
						post_news("The <b>$target[bounty]</b> Credit bounty remaining on <b class=b1>$target[login_name]</b>s head after the destruction of their fleet has been claimed by <b class=b1>$user[login_name]</b>");
						dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + $target[bounty] where login_id = '$user[login_id]'");
						dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$target[login_id]'");
					}
				} else {
					$error_str .= "You destroyed <b class=b1>$target[login_name]</b>'s $target_ship[class_name] in the failed raid.";
					send_message($target[login_id],"Your <b class=b1>$target_ship[ship_name]</b> was destroyed by the <b class=b1>$user_ship[ship_name]</b> during a raid.");
				}
			} elseif ($dam_take==2){
				$error_str .= "<br /><p>You destroyed <b class=b1>$target_ship[login_name]</b>'s $target_ship[class_name] during the failed raid.";
				$error_str .= "<br /><b class=b1>$target_ship[login_name]</b> ejected in an Escape Pod.";
				if($target_ship[location] == 1) {
					post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>s $target_ship[class_name] in the <b>Sol</b> System during a raid.");
				} else {
					post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>s $target_ship[class_name] during a raid.");
				}
				send_message($target[login_id],"Your <b class=b1>$target_ship[ship_name]</b> was destroyed by the <b class=b1>$user_ship[ship_name]</b> during a raid.");
			}
			if(eregi("dy",$target_ship[config])) {
				$boom_damage = 5000;
			} else {
				$boom_damage = mt_rand(1000,1500);
			}
			$user_dead_ship = $user_ship;
			$boom_take = damage_ship($boom_damage,0,0,$target,$user,$user_ship);
			if($boom_take == 1) {
				post_news("<b class=b1>$user[login_name]</b>s <b class=b1>$user_dead_ship[ship_name]</b> was destroyed whilst trying to raid <b class=b1>$target[login_name]</b>s <b class=b1>$target_ship[class_name]</b>");
				print_page ("Raid Failed","Your attempt to raid the ship failed. The target ship exploded before you could get to it. <br />The <b>$boom_damage</b> damage done by the explosion destroyed your <b class=b1>$user_dead_ship[ship_name]</b>. You are now in command of the <b class=b1>$user_ship[ship_name]</b>.<br /><br />$error_str");
			} elseif($boom_take == 2){
				post_news("<b class=b1>$user[login_name]</b> was reduced to an Escape Pod when <b class=b1>$target[login_name]</b>s <b class=b1>$dead_ship[class_name]</b> blew up as it was being raided, taking the attacking ship (<b class=b1>$user_dead_ship[ship_name]</b>) with it.");
				print_page ("Raid Failed","<br />Your attempt to raid the ship failed. The target ship exploded before you could get to it. <br />The <b>$boom_damage</b> damage done by the explosion destroyed your <b class=b1>$user_dead_ship[ship_name]</b>.<br /><br />You are now floating around in an Escape Pod which was jettisoned to a random system (#<b>$user[location]</b>).<br /><br />$error_str");
			} else{
				print_page ("Raid Failed","<br />Your attempt to raid the ship failed. You where fortunate not to get destroyed by the explosion of the target ship which did <b>$boom_damage</b> damage to your ship.<br /><br />$error_str");
			}
		}
	}
}

// Claiming
if(eregi("rd",$user_ship['config']) && isset($cltarget) && $flag_raids == 1 && $flag_research == 1) {
	#get information from DB about target player.
	db(__FILE__,__LINE__,"select * from ${db_name}_users where login_id = $cltarget");
	$target = dbr();

	#ensure that the user has not got too many ships
	db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '$user[login_id]'");
	$count = dbr();

	if(isset($ship)) {
		db(__FILE__,__LINE__,"select * from ${db_name}_ships where ship_id = $ship");
		$ship = dbr();
		db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '$cltarget' && (shipclass != 2 || shipclass != 1)");
		$targ_shp_cnt = dbr();
		if($sure != "yes") { #validation of request.
			get_var('Claim Ship','raid.php',"Are you sure you want to claim the <b class=b1>$ship[ship_name]</b>?",'sure','yes');
		}
		charge_turns(1);
		if(eregi("dy",$ship[config])) {
			$testcl = 1;
		} else {
			$testcl = mt_rand(1,7);
		}
		if($testcl > 2){//successful claim attempt
			if($ship[login_id] != $target[login_id]) {	#ensure the target owns the ship.
				print_page("Claim Ship","You can only claim a ship your target owns!");
			} elseif ($ship[shipclass] == 2) {
				print_page("Claim Ship","You cannot claim Escape Pods!");
			} elseif($count[0] >= $max_ships) { #ensure won't put the user over the ship limit
				print_page("Claim Ship","You have got the maximum number of ships allowed (<b>$max_ships</b>). You cannot claim extra ships.");
			}

			// add back fleet when required
			$ship_types = load_ship_types();
			$ship_stats = $ship_types[$ship['shipclass']];
			if(isset($fleetnum)) {
				$user_fleet = Check_ToCreateFleet($fleetnum);
				unset($fleetnum);
			}
			Check_FleetMax(); // check fleet max/war max are not passed
			Transfer_ClaimedShip();
			//-----------------------------

			//dbn(__FILE__,__LINE__,"update ${db_name}_ships set towed_by = '0' where towed_by = '$ship[ship_id]'");
			//if($target[ship_id] == $ship[ship_id]) {
				//dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = 1 where login_id = '$target[login_id]'");
			//}
			//if($targ_shp_cnt[0] <= 1) {


				//check if any further ships, if not generate escape pod!
				if($target['ship_id'] != $ship[ship_id]) {
						$new_ship_id = $target['ship_id'];
						if($target['login_id'] > 5) {
							db(__FILE__,__LINE__,"select location from ${db_name}_ships where ship_id = '$new_ship_id'");
							$other_ship = dbr();
						} else {
							$other_ship['location'] = 1;
						}
						dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$new_ship_id', location = '$other_ship[location]', last_attack =".time().", last_attack_by = '$user[login_name]' where login_id = '$target[login_id]'");
				} else {
					db(__FILE__,__LINE__,"select ship_id from ${db_name}_ships where login_id = '$target[login_id]' && location != 0 LIMIT 1");
					$other_ship = dbr();
					if(isset($other_ship['ship_id'])) { // jump to other ship
						$new_ship_id = $other_ship['ship_id'];
						if($target['login_id'] > 5) {
							db(__FILE__,__LINE__,"select location from ${db_name}_ships where ship_id = '$new_ship_id'");
							$other_ship = dbr();
						} else {
							$other_ship['location'] = 1;
						}
						dbn(__FILE__,__LINE__,"update ${db_name}_users set ship_id = '$new_ship_id', location = '$other_ship[location]', last_attack =".time().", last_attack_by = '$user[login_name]' where login_id = '$target[login_id]'");
					} else {	// build the escape pod
						create_escape_pod($target);
						$esc_pod_1 = "<br /><br /><b class=b1>$target[login_name]</b> was consigned to an Escape Pod as a result of losing his final free ship!";
						$esc_pod_2 = " As a result you have been forced to evacuate the area aboard you Escape Pod!";
						if($target[bounty] > 0 && $alternate_bounty_sys == 1) {
							send_message($user[login_id],"You have claimed 50% of the <b>$target[bounty]</b> Credit bounty that was on <b class=b1>$target[login_name]</b>s head for destroying his/her fleet and consigning them to an <b class=b1>Escape Pod</b>.");
							post_news("50% of the <b>$target[bounty]</b> bounty on <b class=b1>$target[login_name]</b> has been claimed by <b class=b1>$user[login_name]</b> for the destruction of their fleet. The player remains at large in an <b class=b1>Escape Pod</b> with the remaining bounty on his head.");
							$bounty_a = int($target[bounty] * .5);
							dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + '$bounty_a' where login_id = '$user[login_id]'");
							dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = bounty - '$bounty_a' where login_id = '$target[login_id]'");
						}
					}
				}
			#}
			#send the messages
			post_news("<b class=b1>$user[login_name]</b> claimed a <b>$ship[class_name]</b> from <b class=b1>$target[login_name]</b>$esc_pod_1");
			send_message($target[login_id],"The <b class=b1>$ship[ship_name]</b> has been claimed from you by <b class=b1>$user[login_name]</b> during a raid.$esc_pod_2");
			insert_history($user[login_id],"Claimed a $ship[class_name] from $target[login_name].");
			print_page("Ship Claimed","Ship Claimed Successfully. $esc_pod_1<br /><br />");
		}else{
			$attack_damage = mt_rand(1000,1500);
			$dam_take = damage_ship($attack_damage,-1,0,$user,$target,$ship);
			if($dam_take == 1) {
				post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>s $ship[class_name] while attempting to claim it.");
				if($ship[shipclass] == 2) {
					$error_str .= "You blew <b class=b1>$target[login_name]</b>'s Escape Pod to smitherines during the failed claim attempt.";
					if($sudden_death){
						$error_str .= "As the game is in Sudden Death(SD), <b class=b1>$target[login_name]</b> is out of the game permanently.";
						post_news("<b class=b1>$target[login_name]</b> has been completely killed during a claim ship attempt, and is out of the game permanently.");
					}
					send_message($target[login_id],"Your <b class=b1>$ship[ship_name]</b> was destroyed by the <b class=b1>$user_ship[ship_name]</b> in a claim attempt.");
					if($target[bounty] > 0 && $alternate_bounty_sys == 0) {
						$error_str .= "<br /><p>You have claimed the <b>$target[bounty]</b> Credit bounty that was on <b class=b1>$target[login_name]</b>s head.<br />";
						post_news("The <b>$target[bounty]</b> bounty on <b class=b1>$target[login_name]</b> has been claimed by <b class=b1>$user[login_name]</b>");
						dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + $target[bounty] where login_id = '$user[login_id]'");
						dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$target[login_id]'");
					} elseif ($target[bounty] > 0) {
						$error_str .= "<br /><p>You have claimed the remaining <b>$target[bounty]</b> Credit bounty that was on <b class=b1>$target[login_name]</b>s head after the destruction of their fleet.<br />";
						post_news("The <b>$target[bounty]</b> Credit bounty remaining on <b class=b1>$target[login_name]</b>s head after the destruction of their fleet has been claimed by <b class=b1>$user[login_name]</b>");
						dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + $target[bounty] where login_id = '$user[login_id]'");
						dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$target[login_id]'");
					}
				} else {
					$error_str .= "You destroyed <b class=b1>$target[login_name]</b>'s $ship[class_name] in the failed claim attempt.";
					send_message($target[login_id],"Your <b class=b1>$ship[ship_name]</b> was destroyed by the <b class=b1>$user_ship[ship_name]</b> during a claim attempt.");
				}
			} elseif ($dam_take==2){
				$error_str .= "<br /><p>You destroyed <b class=b1>$ship[login_name]</b>'s $ship[class_name] during the failed claim attempt.";
				$error_str .= "<br /><b class=b1>$ship[login_name]</b> ejected in an Escape Pod.";
				if($ship[location] == 1) {
					post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>s $ship[class_name] in the <b>Sol</b> System during a claim attempt.");
				} else {
					post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>s $ship[class_name] during a claim attempt.");
				}
				send_message($target[login_id],"Your <b class=b1>$ship[ship_name]</b> was destroyed by the <b class=b1>$user_ship[ship_name]</b> during a raid.");
			}
			if(eregi("dy",$ship[config])) {
				$boom_damage = 5000;
			} else {
				$boom_damage = mt_rand(1000,1500);
			}
			$user_dead_ship = $user_ship;
			$boom_take = damage_ship($boom_damage,0,0,$target,$user,$user_ship);
			if($boom_take == 1) {
				post_news("<b class=b1>$user[login_name]</b>s <b class=b1>$user_dead_ship[ship_name]</b> was destroyed whilst trying to claim <b class=b1>$target[login_name]</b>s <b class=b1>$ship[class_name]</b>");
				print_page ("Claim Ship Failed","Your attempt to claim the ship failed. The target ship exploded as you pulled alongside. <br />The <b>$boom_damage</b> damage done by the explosion destroyed your <b class=b1>$user_dead_ship[ship_name]</b>. You are now in command of the <b class=b1>$user_ship[ship_name]</b>.<br /><br />$error_str");
			} elseif($boom_take == 2){
				post_news("<b class=b1>$user[login_name]</b> was reduced to an Escape Pod when <b class=b1>$target[login_name]</b>s <b class=b1>$ship[class_name]</b> blew up as it was being claimed, taking the attacking ship (<b class=b1>$user_dead_ship[ship_name]</b>) with it.");
				print_page ("Claim Failed","<br />Your attempt to claim the ship failed. The target ship exploded as you pulled alongside. <br />The <b>$boom_damage</b> damage done by the explosion destroyed your <b class=b1>$user_dead_ship[ship_name]</b>.<br /><br />You are now floating around in an Escape Pod which was jettisoned to a random system (#<b>$user[location]</b>).<br /><br />$error_str");
			} else{
				print_page ("Claim Failed","<br />Your attempt to claim the ship failed. You where fortunate not to get destroyed by the explosion of the target ship which did <b>$boom_damage</b> damage to your ship.<br /><br />$error_str");
			}
		}
	}
}

// ----------------------------------------------------
// Function that removes cargo from ships during a raid
// ----------------------------------------------------

function take_loot($type) {
	global $target_ship,$user,$db_name,$user_ship,$sure;

	if(isset($type)){
		#first, determine what is to be dealt with.
		if($type == 2){
			$tech_mat = "metal";
			$text_mat = "Metal";
		} elseif($type == 3){
			$tech_mat = "fuel";
			$text_mat = "Fuel";
		} elseif($type == 4){
			$tech_mat = "elect";
			$text_mat = "Electronics";
		} elseif($type == 5){
			$tech_mat = "organ";
			$text_mat = "Organics";
		} elseif($type == 6){
			$tech_mat = "darkmatter";
			$text_mat = "Dark-Matter";
		}

		$taken = 0; //goods taken from ship so far.
		$ship_counter = 0;

		db2(__FILE__,__LINE__,"select ship_id, (cargo_bays - metal - fuel - elect - organ - colon - darkmatter) as free, ship_name from ${db_name}_ships where login_id = '$user[login_id]' && location = '$target_ship[location]' && (cargo_bays - metal - fuel - elect - organ - colon - darkmatter) > 0 order by free desc");
		$ships = dbr2();

		if(!isset($ships[ship_id])){ #check to see if there are any ships
			$output_str .= "<br />There are no ships available that have free cargo bays for the <b>$target_ship[$tech_mat]</b> <b class=b1>$text_mat</b> discovered.";
		} else {

			while($ships) {

				if($ships['free'] < ($target_ship[$tech_mat] - $taken)) {
					$ship_counter++;
					dbn(__FILE__,__LINE__,"update ${db_name}_ships set $tech_mat = $tech_mat + $ships[free] where ship_id = '$ships[ship_id]'");
					$out .= "<br /><b class=b1>$ships[ship_name]</b>s bays were supplemented by <b>$ships[free]</b> <b class=b1>$text_mat</b> to maximum capacity.";
					if($ships['ship_id'] == $user_ship['ship_id']){ #update user ship
						$user_ship[$tech_mat] += $ships['free'];
						$user_ship['empty_bays'] -= $ships['free'];
					}
					$taken += $ships['free'];

				} elseif($ships['free'] >= ($target_ship[$tech_mat] - $taken)) {
					$ship_counter++;
					$t868 = $target_ship[$tech_mat] - $taken;
					dbn(__FILE__,__LINE__,"update ${db_name}_ships set $tech_mat = $tech_mat + '$t868' where ship_id = '$ships[ship_id]'");
					$out .= "<br /><b class=b1>$ships[ship_name]</b>'s bays were supplemented by <b>$t868</b> <b class=b1>$text_mat</b><br />";
					if($ships['ship_id'] == $user_ship['ship_id']){ #update user ship
						$user_ship[$tech_mat] += $t868;
						empty_bays();
					}
					$taken += $t868;
					unset($ships);
					break;
				}
				$ships = dbr2();
			} //end loop of ships

			dbn(__FILE__,__LINE__,"update ${db_name}_ships set $tech_mat = $tech_mat - $taken where ship_id = '$target_ship[ship_id]'");
			$output_str .= "<br /><b>$taken</b> $text_mat found on ship:<br />".$out;
		}
	}
	return $output_str;
}//end function


?>