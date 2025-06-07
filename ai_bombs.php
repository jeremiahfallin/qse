<?php

/*
// Name: ai_bombs.php
// By: Maugrim The Reaper (bpaddy2@yahoo.com)
// Purpose: Management of Mine/Leech AI
// Date Completed: 19/09/2002
// Version: 0.9
*/

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

$order = 0;

if(!isset($flag_mines) || !isset($flag_bmrkt)) {
	print_page("AI Bombs","Admin has disabled all Mines and Leeches.");
}

if($user['turns_run'] < $turns_before_attack && $user['login_id'] != 1) {
  print_page("AI Bombs","You can't attack during the first <b>$turns_before_attack</b> turns of having your account.");
}

if($user['ship_id'] == 1 && $user['login_id'] != 1) {
  print_page("AI Bombs","You may not use a Bomb when you are not commanding a ship. Try buying a ship then set up a Bomb");
}

sudden_death_check($user);
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
		$mr = dbr();
		$max_races = $mr['value'];
//Grav/Hornet/Leech Minetypes
// hornets & graviton mines
if(isset($m_type) && ($m_type == 1 || $m_type == 2)) {

	if($m_type == 1){
		$m_name = "Graviton Mines";
		$m_tech = "grav_mine";
	} else {
		$m_name = "Hornet Mines";
		$m_tech = "hornet_mine";
	}

	settype($m_num, "integer");

	db(__FILE__,__LINE__,attack_planet_check($db_name,$user));
	$planets = dbr();

	for($i=0;$i<$max_races;$i++) {
	$home_num = $i+1;
	if($user[location] == $home_num and $user[login_id] != 1)
		{
                        $error_str = "You cannot lay Mines within <b>$banned_mine_dist</b> warp jumps of a Homeworld System! You are currently IN the Homeworld System #$home_num.";
                        print_page("$m_name",$error_str);
		}
	$sol_dist = 0;
	$sol_dist = get_warp_dist($user['location'],$home_num);
	if( $sol_dist <= $banned_mine_dist and $user[login_id] != 1) {
        	        $error_str = "You cannot lay Mines within <b>$banned_mine_dist</b> warp jumps of a Homeworld System! You are currently <b>$sol_dist</b> warp jumps from the Homeworld System #$home_num.";
			print_page("$m_name",$error_str);
			}
		}

	if($user[$m_tech] < 1) {
		$error_str .= "You don't have any $m_name!";
	} elseif(!empty($planets) && $user['login_id'] != 1) {
		$error_str .= "You may not lay mines in a system with a hostile planet.";
	} elseif($user['location'] == 1 && $user['login_id'] != 1) {
		$error_str = "You may not lay mines in the Sol System.";
	} elseif(!eregi("ml",$user_ship['config']) and $user[login_id] != 1) {
		$error_str = "You cannot lay Mine Clusters without a Mine-Layer!";
	} elseif($m_num <= 0) {//should = 0 to operate
		$error_str .= "Enter Number of <b class=b1>$m_name</b> you wish to include in this mine cluster:";
		$error_str .= "<form name=m_num_form action=ai_bombs.php method=post>";
		$error_str .= "<br /><p>Enter the type of <b class=b1>Clan</b> or <b class=b1>Independent</b> Ships you wish to programme the <b class=b1>$m_name</b> Cluster AI to target.<br /><p>";
		$error_str .= "<input type=radio name=m_target value=0> Neutral";
		$error_str .= "<br /><input type=radio name=m_target value=1> Enemy";
		$error_str .= "<br /><input type=radio name=m_target value=2 checked> Both";
		if($m_type == 2) {
			$error_str .= "<br /><p><input type=radio name=sequence value=1 checked> Strongest Ship";
			$error_str .= "<br /><input type=radio name=sequence value=2> Weakest Ship";
		}
		$error_str .= "<br /><p><input type=submit value=Submit></form>";
	} elseif($m_num > $user[$m_tech]){
		$error_str .= "You do not have that many <b class=b1>$m_name</b> to use.";
	} else {
		if($m_type == 1) {
			dbn(__FILE__,__LINE__,"insert into ${db_name}_mines (login_id, login_name, mine_type, clan_id, v_clan_id, cluster, rel_target, location) values ('$user[login_id]', '$user[login_name]', '$m_type', '$user[clan_id]', '$user[v_clan_id]', '$m_num', '$m_target', '$user[location]')");
		} else {
			dbn(__FILE__,__LINE__,"insert into ${db_name}_mines (login_id, login_name, mine_type, clan_id, v_clan_id, cluster, rel_target, location, sequence) values ('$user[login_id]', '$user[login_name]', '$m_type', '$user[clan_id]', '$user[v_clan_id]', '$m_num', '$m_target', '$user[location]', '$sequence')");
		}
		if($user['login_id'] != 1){
			dbn(__FILE__,__LINE__,"update ${db_name}_users set ${m_tech} = ${m_tech} - '$m_num' where login_id = '$user[login_id]'");
			$user[$m_tech] -= $m_num;
		}

		$error_str .= "Your cluster of <b>$m_num</b> <b class=b1>$m_name</b> have been programmed and are now laid in Star System <b>$user[location]</b>.";
	}
	print_page("$m_name",$error_str);

// leeches
} elseif($m_type == 3){

	db(__FILE__,__LINE__,attack_planet_check($db_name,$user));
	$planets = dbr();

	if($user['location'] == 1 && $user['login_id'] != 1){
		$error_str .= "Ships harboured within the Sol system are safe from Leechs!";
	} elseif($user['leech'] < 1) {
			$error_str = "You don't have any Leeches!";
	} elseif($flag_sol_attack == 0 && $user['location'] <= $max_races && $user['login_id'] != 1) {
		$error_str = "The Admin has disabled all forms of attack in the Homeworld Systems.";
	} elseif(!empty($planets) && $user['login_id'] != 1) {
		$error_str .= "You may not attach leechs to ships that are cowering behind a hostile planet.";
	} elseif(!$ship_targ) {
		db(__FILE__,__LINE__,"select s.ship_name,s.class_name,s.login_name,s.fighters,s.ship_id from ${db_name}_ships s, ${db_name}_users u where s.location = '$user[location]' && s.login_id != '$user[login_id]' && s.disp_rank = 0 && u.login_id = s.login_id && u.turns_run > '$turns_safe' && s.login_id != 1 order by s.login_name");
		$all_shp = dbr();
		if($all_shp){
			$error_str .= "<form name=leech_ai action=ai_bombs.php method=post>";
			$error_str .= "<input type=hidden name=m_type value=3>";
			$error_str .= "Enter the task you wish to programme the <b class=b1>Leech</b> AI to perform.<br /><p>";
			$error_str .= "<input type=radio name=leech_task value=0 checked> Damage Ship";
			$error_str .= "<br /><input type=radio name=leech_task value=1> Drain Shields";
			if($user['tachyon_comm'] == 0){
				$error_str .= "<br /><br />Surveillance - a <b class=b1>Tachyon Comm. Array</b> is required.<br />";
				$error_str .= "<br /><p>You have <b>$user[tachyon_comm]</b> <b class=b1>Tachyon Comm. Arrays</b> available for surveillance purposes.<br />";
			}else{
				$error_str .= "<br /><input type=radio name=leech_task value=2> Surveillance";
				$error_str .= "<br /><p>You have <b>$user[tachyon_comm]</b> <b class=b1>Tachyon Comm. Arrays</b> available for surveillance purposes.<br />";
			}
			$error_str .= "<br /><br /><input type=submit value=Deploy-Leech></form>";
			$error_str .= "Select which ship the <b class=b1>Leech</b> should target. You cannot target stealthed ships.<br /><table>";
			if($user['clan_id'] > 0){
				db2(__FILE__,__LINE__,"select symbol from ${db_name}_clans where clan_id = '$all_shp[clan_id]'");
				$clan = dbr2();
				$clan_sig = $clan['symbol'];
			}else {
				$clan_sig = "";
			}
			while($all_shp){
				$shp .= make_row(array("$all_shp[ship_name]", "$all_shp[class_name]", "$all_shp[login_name]", "$all_shp[fighters]", "<div align=\"center\">$clan_sig</div>", "<input type= radio name=ship_targ value=$all_shp[ship_id]>"));
				$all_shp = dbr();
			}

			$error_str .= make_table(array("Ship Name","Class","Owner","Fighters","Clan Symbol","Target"));
			$error_str .= stripslashes($shp);
			$error_str .= "</table><br /><p>";
			$error_str .= "<input type=submit value=Deploy-Leech></form>";
		} else {
			$error_str .= "There are no other ships in this system that you can attach a leech to.";
		}

	}else {
		dbn(__FILE__,__LINE__,"insert into ${db_name}_leeches (login_id,ship_id,task) values ('$user[login_id]','$ship_targ','$leech_task')");
		if($user['login_id'] != 1){
			dbn(__FILE__,__LINE__,"update ${db_name}_users set leech = leech - 1 where login_id = '$user[login_id]'");
			$user['leech'] --;
			if($leech_task == 2){
				dbn(__FILE__,__LINE__,"update ${db_name}_users set tachyon_comm = tachyon_comm - 1 where login_id = '$user[login_id]'");
				$user['tachyon_comm'] --;
			}
		}
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set leech = leech + 1 where ship_id = '$ship_targ'");

		$error_str .= "Your <b>Leech</b> has been programmed to carry out its task on a ship in Star System <b>$user[location]</b>.";

	}
	print_page("Leech",$error_str);
}

?>
