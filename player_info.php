<?php
require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

/*
if(isset($transfer)) {
	if(!isset($to_user)) {
		get_var('Transfer Credits','earth.php','Who would you like to transfer credits to
	if($amount <= 0) {
		get_var('Transfer Credits','earth.php','How many credits do you want to transfer?','amount',"$user[cash]");
	} else {
		if($amount > $user[cash]) {
			$error_str .= "You do not have that many credits.<p>";
		} else {
			db(__FILE__,__LINE__,"select login_name from users where login_name = "";
			dbn(__FILE__,__LINE__,"update users set cash = cash - $amount where login_id = $user[login_id]");
			$user[cash] -= $amount;
			dbn(__FILE__,__LINE__,"update planets set cash = cash + $amount where planet_id = $planet[planet_id]");
		}
	}
}
*/
sudden_death_check($user);

if(!$target){
	$target=$user[login_id];
}
		$dev2 = $owner_id;

//Destroy ship(s)
if($self_destruct) {
	$rs .= "<br /><a href=player_info.php?target=$user[login_id]>Back to Player Info</a>";
	if(!$expl){
			print_page("Self Destruct","You must select at least one ship to destroy.");
	}
	$math = 0;
	$temp444 = $expl;
	while ($var = each($temp444)) {
		db(__FILE__,__LINE__,"select login_id from ${db_name}_ships where ship_id = '$var[value]'");
		$target_ship = dbr();
		$math++;
		if($target_ship[login_id] != $user[login_id]) {
		print_page("Self Destruct","You can only self destruct one of your own ships!");
		break;
		} elseif($user[ship_id] == $var[value]) {
		print_page("Self Destruct","You may not destroy the ship that you are commanding.");
		break;
		}
	}
	if($user[turns] < $math) {
		print_page("Self Destruct","It costs <b>1</b> turn to blow up each ship. You do not have enough turns to destroy all these ships.");
	} elseif($sure != "yes") {
	$ostr .= "Are you sure you want to destroy <b>$math</b> ship(s)?";
	$ostr .= "<form name=destroy_ships action=player_info.php method=post>";
	$temp444 = $expl;
	$i=0;
	while ($var = each($temp444)) {
		$ostr .= "<input type=hidden name=expl[$i] value='$var[value]'>";
		$i++;
	}
	$ostr .= '<input type=hidden name=self_destruct value=1>';
	$ostr .= '<input type=hidden name=sure value=yes>';
		$ostr .= '<input type=submit name=submit value=Yes> <input type="Button" width="30" value="No" onclick="javascript: history.back()"> </form>';
	print_page("Sure?",$ostr);
	} else {
	$temp444 = $expl;
	while ($var = each($temp444)) {
		//dbn(__FILE__,__LINE__,"update ${db_name}_ships set towed_by = 0 where towed_by = '$var[value]'");
		dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where ship_id = '$var[value]'");
	}
		post_news("<b class=b1>$user[login_name]</b> self destructed <b>$math</b> ship(s).");
			charge_turns($math);
	if($math > 100){
		$out = "<p>Dang, that bang almost makes a <b class=b1>SuperNova</b> Pale in comparison!<br />Here, have 1000 credits to make up for it.";
		give_cash(1000);
	} elseif($math > 60){
		$out = "<p>And I thought <b class=b1>Armageddon</b> created a big bang!!!!!<br />Here's 500 credits to help cover costs.";
		give_cash(500);
	} elseif($math > 30){
		$out = "<p>Now you're playing with BIG <b class=b1>FIREWORKS</b>!!!!!";
	} elseif($math > 10){
		$out = "<p>Thats a <b>BIG BANG</b>!!!!!";
	} else {
		$out = "<p>BOOOOOMMM!!!!!";
	}
	$text .= "<b>$math</b> Ships Blown up at a cost of <b>$math</b> turns.".$out."<br />";
	}
}

#History of players actions.
if($history){
	$action_show = 200;
	if($user[login_id] == 1 || $user[login_id] == $history || $user[login_id] == $dev2){
		$rs="<a href=player_info.php?target=$history>Back to Player Info</a><br /><br />";

		if($sort_history){
			if($sorted_history==1){
				$going = "asc";
				$sorted_history=2;
			} else {
				$going = "desc";
				$sorted_history=1;
			}
			if($sort_history != "timestamp") {
				$sec_sort = ", 'timestamp' desc";
			}
			db(__FILE__,__LINE__,"select timestamp,game_db,action,user_IP,other_info from user_history where login_id = '$history' order by '$sort_history' ".$going.$sec_sort." LIMIT $action_show");
		} else {
			db(__FILE__,__LINE__,"select timestamp,game_db,action,user_IP,other_info from user_history where login_id = '$history' order by timestamp desc LIMIT $action_show");
		}


		$text = $rs;
		$text .= "List of last <b>$action_show</b> actions:";
		$text .= make_table(array("<a href=player_info.php?history=$history&sort_history=timestamp&sorted_history=$sorted_history>Time/Date</a>","<a href=player_info.php?history=$history&sort_history=game_db&sorted_history=$sorted_history>Game</a>","<a href=player_info.php?history=$history&sort_history=action&sorted_history=$sorted_history>Entry</a>","<a href=player_info.php?history=$history&sort_history=user_IP&sorted_history=$sorted_history>IP</a>","<a href=player_info.php?history=$history&sort_history=other_info&sorted_history=$sorted_history>Other</a>"));
		while($hist = dbr()){
			$text .= make_row(array("<b>".date("M d - H:i",$hist[timestamp]),$hist[game_db],$hist[action],$hist[user_IP],$hist[other_info]));
		}
		$text .= "</table><br />";
		print_page("Account History",$text);
	} else{
		print_page("Account History","You may not view another players account history.");
	}
}

// transfer cash
if($transfer) {
#db ("select value from ${db_name}_db_vars where name = 'min_before_transfer'");
#$min_b4_trans1 = dbr();
#$min_b4_trans = $min_b4_trans1[0];
	if($sure != 'yes') {
		get_var('Transfer cash','player_info.php',"Are you sure you want to transfer $trans_amount cash to $trans_target?",'sure','yes');
	} else {
		settype($trans_amount, "integer");
		if ($trans_amount<=0) {
			print_page("Transfer Error","You can't transfer negative or 0 cash<br /><a href=javascript:back()>Go back</a><br />");
		} elseif ($user[cash] < $trans_amount) {
			print_page("Transfer Error","You don't have that much cash<br /><a href=javascript:back()>Go back</a><br />");
		} elseif ($user[joined_game] > (time() - ($min_before_transfer * 86400)) && $user[login_id] > 1) {
			print_page("Transfer Error","The admin has restricted access to Credit transfer. You may only transfer Credits once you have been in the game <b>$min_before_transfer</b> days or more. <br /><a href=javascript:back()>Go back</a><br />");
		} else {
			take_cash($trans_amount);
			dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + $trans_amount where login_id = '".$trans_target_id."'");
			send_message($trans_target_id,"<b class=b1>$user[login_name]</b> has sent you <b>$trans_amount</b> Credits.");
			insert_history($user[login_id],"Transfered $trans_amount to $trans_target");
			print_page("Transfer Complete","You sent <b>$trans_amount</b> Credits to <b class=b1>$trans_target</b>.");
		}
	}
}

if($transfer_t) {
#db ("select value from ${db_name}_db_vars where name = 'min_before_transfer'");
#$min_b4_trans1 = dbr();
#$min_b4_trans = $min_b4_trans1[0];
	if($sure != 'yes') {
		get_var('Transfer Tech. Units','player_info.php',"Are you sure you want to transfer $trans_amount Tech. Units to $trans_target?",'sure','yes');
	} else {
		settype($trans_amount, "integer");
		if ($trans_amount<=0) {
			print_page("Transfer Error","You can't transfer negative or 0 Tech. Units<br /><a href=javascript:back()>Go back</a><br />");
		} elseif ($user[tech] < $trans_amount) {
			print_page("Transfer Error","You don't have that much Tech. Units<br /><a href=javascript:back()>Go back</a><br />");
		} elseif ($user[joined_game] > (time() - ($min_before_transfer * 86400)) && $user[login_id] > 1) {
			print_page("Transfer Error","The admin has restricted access to Tech transfer. You may only transfer Tech. Units once you have been in the game <b>$min_before_transfer</b> days or more. <br /><a href=javascript:back()>Go back</a><br />");
		} else {
			take_tech($trans_amount);
			dbn(__FILE__,__LINE__,"update ${db_name}_users set tech = tech + $trans_amount where login_id = '".$trans_target_id."'");
			send_message($trans_target_id,"<b class=b1>$user[login_name]</b> has sent you <b>$trans_amount</b> Tech. Units.");
			insert_history($user[login_id],"Transfered $trans_amount to $trans_target");
			print_page("Transfer Complete","You sent <b>$trans_amount</b> Tech. Units to <b class=b1>$trans_target</b>.");
		}
	}
}

db(__FILE__,__LINE__,"select u.*,pu.login_name as generic_l_name, pu.first_name,pu.last_name,pu.email_address,pu.icq,pu.aim,pu.msn,pu.yim,pu.last_ip,u.game_login_count,u.last_attack,u.last_attack_by,pu.num_games_joined,u.joined_game,u.last_request from ${db_name}_users u, user_accounts pu where u.login_id = $target && pu.login_id = u.login_id");
$player = dbr();

# Won't display alien or pirate information, or the other two reserved accounts.
# they have login_id 2 or 3, or 4,5 for the reserved ones.
# Minimum login id is 0 (or 1, but then as admin gets 1, it must be 2 for players cos its auto increment).
if($target < 5){
	$special_show=1;
	if($user[login_id] == 1 && $target == 1){
		$full = 1;
	} elseif($target == 3 and $user[login_id] == 1){
		$full = 3;
	} else {
		$full = 0;
	}
} elseif($target == $user[login_id] || $target == 1 || ($user[clan_id] == $player[clan_id] && $user[clan_id] > 0) || $user[login_id] == 1 || $user[login_id] == 5) { #admin can see all, but not aliens/pirates
	$full = 1;
#} elseif($target == $user[login_id] || $target == 1) {	#allows players to see own info, and admins info.
#	$full = 1;
#} elseif($user[clan_id] == $player[clan_id] && $user[clan_id] > 0) { #can see clan mates
#	$full = 1;
} else { #if none of the above are true, then a more limited view is given.
	$full = 0;
}

if($enable_politics && $full) {
	if($player[politics] == 0 && $player[login_id] > 1){
		$player[politics] = "Civilian";
	} elseif($player[login_id] < 2){
		$player[politics] = "Supreme Being";
	} elseif($player[politics] == 1){
		$player[politics] = "Monarch";
	} elseif($player[politics] == 2){
		$player[politics] = "Industry Senator";
	} elseif($player[politics] == 3){
		$player[politics] = "Military Senator";
	} elseif($player[politics] == 4){
		$player[politics] = "Defense Senator";
	} elseif($player[politics] == 5){
		$player[politics] = "Trade Senator";
	} elseif($player[politics] == 6){
		$player[politics] = "Militia Senator";
	}
}

$text .= make_table(array("",""));
$text .= quick_row("Game Name",print_name($player));
if($full || $special_show) {
	$text .= quick_row("Login name",$player[generic_l_name]);
	if($special_show){
		$text .= quick_row("Misc Info #1",$player[first_name]);
		$text .= quick_row("Misc Info #2",$player[last_name]);
		$text .= quick_row("Purpose","$player[email_address]");
	} else {
		$text .= quick_row("First Name",$player[first_name]);
		$text .= quick_row("Last Name",$player[last_name]);
		$text .= quick_row("Email Address","<a href=mailto:$player[email_address]>$player[email_address]</a>");
	}
	if($player[aim] != ''){
		$text .= quick_row("AIM SN","<a href=\"aim:goim?screenname=$player[aim]&message=Hi+$player[aim].+Are+you+there?\">$player[aim]</a>");
	}
	if(!$player[icq] == '0'){
		$text .= quick_row("ICQ #","<a href=\"http://wwp.mirabilis.com/$player[icq]\" TARGET=\"_blank\">$player[icq]</a>");
	}
	if($player[msn] != ''){
		$text .= quick_row("MSN SN","$player[msn]");
	}
	if($player[yim] != ''){
		$text .= quick_row("YIM SN","$player[yim]");
	}
	$text .= quick_row("&nbsp;","");
	db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
	$eras = dbr();
	$era = $eras['value'];
	db3(__FILE__,__LINE__,"select * from races where race_id = {$player['race']} && era = $era");
	$user_race = dbr3();
	$user_race_name = $user_race['race_name'];
	$text .= quick_row("Race:","$user_race_name");
	$text .= quick_row("Joined Game",date( "M d - H:i",$player[joined_game]));
	$text .= quick_row("Last Page Request",date( "M d - H:i:s",$player[last_request]));
	$text .= quick_row("Login Count",$player[game_login_count]);
	$text .= quick_row("Last IP Address",$player[last_ip]);
	if($full == 1){
		$text .= quick_row("Num. Games Joined",$player[num_games_joined]);
	}
/*	if($user[login_id] == 1 && $target != 1){
		$text .= quick_row("Authorisation Code",$player[auth]);
	}*/
#	$text .= quick_row("Current Location",$player[location]);
	$text .= quick_row("&nbsp;","");
	db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '$target'");
	$ship_count = dbr();
	$text .= quick_row("Ship Count",$ship_count[0]);
	$text .= quick_row("Cash",$player[cash]);
	$text .= quick_row("Turns",$player[turns]);
}
$text .= quick_row("Turns Run",$player[turns_run]);
$text .= quick_row("&nbsp;","");

$text .= quick_row("Ships Killed",$player[ships_killed]);
$text .= quick_row("Ships Lost",$player[ships_lost]);
$text .= quick_row("Ships Points Killed",$player[ships_killed_points]);
$text .= quick_row("Ships Points Lost",$player[ships_lost_points]);
$text .= quick_row("Fighters Killed",$player[fighters_killed]);
$text .= quick_row("Fighters Lost",$player[fighters_lost]);
$text .= quick_row("Score",$player[score]);

if($player[last_attack] <= 1){
	$text .= quick_row("Last Attack (Time)","Never");
	$player[last_attack_by] = "No-one";
} else {
	$text .= quick_row("Last Attack (Time)",date( "M d - H:i",$player[last_attack]));
}
$text .= quick_row("Last Attack (Against)","<b class=b1>$player[last_attack_by]</b>");
$text .= quick_row("&nbsp;","");
$text .= quick_row("Bounty",$player[bounty]);

if($enable_politics && $full) {
	$text .= quick_row("Political Rank",$player[politics]);
}

if($full) {
$text .= quick_row("&nbsp;","");
	$text .= quick_row("Genesis Devices",$player[genesis]);
	if($flag_bomb){
		$text .= quick_row("Alpha Bombs",$player[alpha]);
		$text .= quick_row("Gamma Bombs",$player[gamma]);
		$text .= quick_row("Delta Bombs",$player[delta]);
		if($player[sn_effect] == 1){
			$player[sn_effect] = "Yes";
		} else {
			$player[sn_effect] = "No";
		}
		$text .= quick_row("SuperNova Effector?",$player[sn_effect]);
	}
}
$text .= "</table><br />";
if($full) {

	//return assoc array
	$ADODB_FETCH_MODE = 2;

	if($sort_planets){
		if($sorted_planets==1){
			$going = "asc";
			$sorted_planets=2;
		} else {
			$going = "desc";
			$sorted_planets=1;
		}
		db(__FILE__,__LINE__,"select planet_name,location,fighters,colon,cash,tech,metal,fuel,elect,organ,darkmatter,tax_rate,alloc_fight,alloc_elect,alloc_organ "
			. "from ${db_name}_planets where owner_id = '$target' order by '$sort_planets' $going");
	} else {
		db(__FILE__,__LINE__,"select planet_name,location,fighters,colon,cash,tech,metal,fuel,elect,organ,darkmatter,tax_rate,alloc_fight,alloc_elect,alloc_organ "
			. "from ${db_name}_planets where owner_id = '$target' order by fighters desc, planet_name asc, location desc");
	}
	$clan_planet = dbr(1);
	if($clan_planet) {
		if ($flag_dmatter) {
			$text .= make_table(array("<a href=player_info.php?target=$target&sort_planets=planet_name&sorted_planets=$sorted_planets>Planet Name</a>",
				"<a href=player_info.php?target=$target&sort_planets=location&sorted_planets=$sorted_planets>Location</a>",
				"<a href=player_info.php?target=$target&sort_planets=fighters&sorted_planets=$sorted_planets>Fighters</a>",
				"<a href=player_info.php?target=$target&sort_planets=colon&sorted_planets=$sorted_planets>Colonists</a>",
				"<a href=player_info.php?target=$target&sort_planets=cash&sorted_planets=$sorted_planets>Cash</a>",
				"<a href=player_info.php?target=$target&sort_planets=tech&sorted_planets=$sorted_planets>Tech</a>",
				"<a href=player_info.php?target=$target&sort_planets=metal&sorted_planets=$sorted_planets>Metal</a>",
				"<a href=player_info.php?target=$target&sort_planets=fuel&sorted_planets=$sorted_planets>Fuel</a>",
				"<a href=player_info.php?target=$target&sort_planets=elect&sorted_planets=$sorted_planets>Electronics</a>",
				"<a href=player_info.php?target=$target&sort_planets=organ&sorted_planets=$sorted_planets>Organics</a>",
				"<a href=player_info.php?target=$target&sort_planets=darkmatter&sorted_planets=$sorted_planets>Darkmatter</a>",
				"<a href=player_info.php?target=$target&sort_planets=tax_rate&sorted_planets=$sorted_planets>Tax</a>",
				"<a href=player_info.php?target=$target&sort_planets=alloc_fight&sorted_planets=$sorted_planets>Alloc Fight</a>",
				"<a href=player_info.php?target=$target&sort_planets=alloc_elect&sorted_planets=$sorted_planets>Alloc Elec</a>",
				"<a href=player_info.php?target=$target&sort_planets=alloc_organ&sorted_planets=$sorted_planets>Alloc Org</a>"));
		} else {
			unset( $clan_planet['darkmatter'] );
			$text .= make_table(array("<a href=player_info.php?target=$target&sort_planets=planet_name&sorted_planets=$sorted_planets>Planet Name</a>",
				"<a href=player_info.php?target=$target&sort_planets=location&sorted_planets=$sorted_planets>Location</a>",
				"<a href=player_info.php?target=$target&sort_planets=fighters&sorted_planets=$sorted_planets>Fighters</a>",
				"<a href=player_info.php?target=$target&sort_planets=colon&sorted_planets=$sorted_planets>Colonists</a>",
				"<a href=player_info.php?target=$target&sort_planets=cash&sorted_planets=$sorted_planets>Cash</a>",
				"<a href=player_info.php?target=$target&sort_planets=tech&sorted_planets=$sorted_planets>Tech</a>",
				"<a href=player_info.php?target=$target&sort_planets=metal&sorted_planets=$sorted_planets>Metal</a>",
				"<a href=player_info.php?target=$target&sort_planets=fuel&sorted_planets=$sorted_planets>Fuel</a>",
				"<a href=player_info.php?target=$target&sort_planets=elect&sorted_planets=$sorted_planets>Electronics</a>",
				"<a href=player_info.php?target=$target&sort_planets=organ&sorted_planets=$sorted_planets>Organics</a>",
				"<a href=player_info.php?target=$target&sort_planets=tax_rate&sorted_planets=$sorted_planets>Tax</a>",
				"<a href=player_info.php?target=$target&sort_planets=alloc_fight&sorted_planets=$sorted_planets>Alloc Fight</a>",
				"<a href=player_info.php?target=$target&sort_planets=alloc_elect&sorted_planets=$sorted_planets>Alloc Elec</a>",
				"<a href=player_info.php?target=$target&sort_planets=alloc_organ&sorted_planets=$sorted_planets>Alloc Org</a>"));
		}
		unset( $planet_totals, $k, $v );
		foreach ( $clan_planet as $k => $v ) {
			$planet_totals[$k] = 0;
		}
		while($clan_planet) {
			if (!$flag_dmatter) {
				unset( $clan_planet['darkmatter'] );
			}
			$clan_planet['planet_name'] = "<b class=b1>$clan_planet[planet_name]</b>";
			$clan_planet['tax_rate'] = "$clan_planet[tax_rate]%</b>";
			$text .= make_hash_row($clan_planet);
			foreach ( $clan_planet as $k => $v ) {
				if ( is_numeric( $v ) ) {
					$planet_totals[$k] += $v;
				}
			}
			$clan_planet = dbr(1);
		}
		$planet_totals['planet_name'] = "<b class=b1>Total</b>";
		$planet_totals['location'] = NULL;
		$planet_totals['tax_rate'] = NULL;
		$text .= "\n<tr class=\"tr_bg\">";
		foreach ( $planet_totals as $v ) {
			$text .= "\n<td><b>$v</b></td>";
		}
		$text .= "\n</tr>";
		$text .= "</table><br />";
	}

	 	#determine if users want to see the abbreviation or not of ship types..
	if($user_options[show_abbr_ship_class] == 1){ #abbriviate class names
		$class_temp_var = "class_name_abbr";
	} else {
		$class_temp_var = "class_name";
	}

	if($sort_ships){
		if($sorted_ships==1){
			$going = "asc";
			$sorted_ships=2;
		} else {
			$going = "desc";
			$sorted_ships=1;
		}

		db(__FILE__,__LINE__,"select ship_name,$class_temp_var,location,fighters,shields,config,ship_id,login_id from ${db_name}_ships where login_id = '$target' order by '$sort_ships' $going");
	} else {
		db(__FILE__,__LINE__,"select ship_name,$class_temp_var,location,fighters,shields,config,ship_id,login_id from ${db_name}_ships where login_id = '$target' order by fighters desc,ship_name asc");
	}

	$clan_ship = dbr(1);
	if($clan_ship) {
		if($clan_ship[login_id] == $user[login_id] && $ship_count[0] > 1) {
			$text .= "<form method=post action=player_info.php name=blow_em_up><input type=hidden name=self_destruct value=1>";
			$text .= make_table(array("<a href=player_info.php?target=$target&sort_ships=ship_name&sorted_ships=$sorted_ships>Ship Name","<a href=player_info.php?target=$target&sort_ships=class_name_abbr&sorted_ships=$sorted_ships>Ship Class</a>","<a href=player_info.php?target=$target&sort_ships=location&sorted_ships=$sorted_ships>Location</a>","<a href=player_info.php?target=$target&sort_ships=fighters&sorted_ships=$sorted_ships>Fighters</a>","<a href=player_info.php?target=$target&sort_ships=shields&sorted_ships=$sorted_ships>Shields</a>","<a href=player_info.php?target=$target&sort_ships=config&sorted_ships=$sorted_ships>Specials</a>","Destroy - Command"));
		} else {
			$text .= make_table(array("<a href=player_info.php?target=$target&sort_ships=ship_name&sorted_ships=$sorted_ships>Ship Name","<a href=player_info.php?target=$target&sort_ships=class_name_abbr&sorted_ships=$sorted_ships>Ship Class</a>","<a href=player_info.php?target=$target&sort_ships=location&sorted_ships=$sorted_ships>Location</a>","<a href=player_info.php?target=$target&sort_ships=fighters&sorted_ships=$sorted_ships>Fighters</a>","<a href=player_info.php?target=$target&sort_ships=shields&sorted_ships=$sorted_ships>Shields</a>","<a href=player_info.php?target=$target&sort_ships=config&sorted_ships=$sorted_ships>Specials</a>"));
		}

		#loop through ships
		while($clan_ship) {
			if(!$clan_ship[config]){
				$clan_ship[config] = "None";
			}
			if($clan_ship[ship_id] == $user[ship_id]){
				$clan_ship[ship_id] = "";
			} elseif($clan_ship[login_id] == $user[login_id] && $ship_count[0] > 1) {
				$clan_ship[ship_id] = "<input type=checkbox name=expl[$clan_ship[ship_id]] value=$clan_ship[ship_id]> - <a href=location.php?command=$clan_ship[ship_id]>Command</a>";
			} else {
				$clan_ship[ship_id] = "";
			}

			$clan_ship[login_id] = "";
			$clan_ship[login_name]="<b class=b1>$clan_ship[login_name]</b>";
			$text .= make_hash_row($clan_ship);
			//"- <a href=location.php?command=$ship_id>Command</a>"
			$clan_ship = dbr(1);
		}
		$text .= "</table><br />";
		if($ship_count[0] > 1 && $target == $user[login_id]) {
			$text .= "<a href=javascript:TickAll(\"blow_em_up\")>Invert Ship Selection</a> - ";
			$text .= "<input type=submit value='Blow Up'></form><p>";
		}


	}
}

#links at bottom of page to transfer stuff and message player.
if($player[login_id] != $user[login_id] && $player[auth] >= 0) {
	$text .= "<a href=message.php?target=$target>Message $player[login_name]</a><br /><br />";
	if($user[joined_game] < (time() - ($min_before_transfer * 86400)) || $user[login_id] < 2){

		$text .= "<form action=player_info.php><input type=hidden name=transfer value=yes>";
		$text .= "Transfer Credits to <b class=b1>$player[login_name]</b>:<br />";
		$text .= "<input type=text name=trans_amount size=6>";
		$text .= "<input type=hidden name=trans_target_id value=$player[login_id]>";
		$text .= "<input type=hidden name=trans_target value=$player[login_name]>";
		$text .= "<input type=submit value='Transfer'></form>";

		$text .= "<form action=player_info.php><input type=hidden name=transfer_t value=yes>";
		$text .= "Transfer Tech. Units to <b class=b1>$player[login_name]</b>:<br />";
		$text .= "<input type=text name=trans_amount size=6>";
		$text .= "<input type=hidden name=trans_target_id value=$player[login_id]>";
		$text .= "<input type=hidden name=trans_target value=$player[login_name]>";
		$text .= "<input type=submit value='Transfer'></form>";
	} else {
		$text .= "<p>You may not Transfer Things yet. <br />Your account must be <b>$min_before_transfer</b> or more days old.<p>";
	}
}

#retire player
if(($user[login_id] == 1 && ($target != 1 || $target != $dev2) && $player[auth] != -1) || ($user[login_id] == $dev2 && $target != 1)) {
	$text .= "<a href=retire.php?target=$target>Retire $player[login_name]</a><br />";
}

#show account history
if(($target > 1 && $full) || $user[login_id] == 1 || $user[login_id] == $dev2){
	$text .= "<a href=player_info.php?history=$target>Show Account History</a><br />";
}

print_page('Player Info',$text);

?>
