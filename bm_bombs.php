<?php

/*
// Name: bm_bombs.php
// By: Maugrim The Reaper (bpaddy2@yahoo.com)
// Purpose: Purchase of Mines/Leech/Missiles
// Date Completed: 22/08/2002
// Version: 0.7
*/

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

$filename = "bm_bombs.php";

sudden_death_check($user);


$rs = "";
$link = "<a href='help.php?research=1#advitems' target=_blank>";
if($from_0 == 1){
 	$rs = "<p><a href=black_market.php?bmrkt_id=$bmrkt_id>Return to Blackmarket</a>";
}
$rs .= "<p><a href=location.php>Close Contact</a>";


db(__FILE__,__LINE__,"select * from ${db_name}_bmrkt where location = '$user[location]' && (bmrkt_type = 3 || bmrkt_type = 0) && bmrkt_id = '$bmrkt_id'");
$bmrkt = dbr();


if (!$bmrkt) {
	print_page("Blackmarket","You may not contact a blackmarket that is not in the same system as you are in. Stop playing with the URL's'");
} elseif($user[ship_id] ==1 && $user[login_id] !=1) {
	print_page("Error","The local Pirates who operate this service have refused you entry. How can you be a Captain with no ship!!!");
} elseif($flag_bmrkt !=1) {
	print_page("Error","Admin in his/her near infinite wisdom has disabled the Blackmarket");
} elseif($flag_mines !=1) {
	print_page("Error","Admin has deemed it necessary to disable mines.");
}

db(__FILE__,__LINE__,"select config from ${db_name}_ships where ship_id = $user[ship_id]");
$old_config = dbr();

// Tech and Credit costs of Advanced Bombs and such

// Credit Cost
if ($flag_trading == 0){
$gmine_c = 80000; //Graviton Mine
$hmine_c = 750; //Hornet Mine
$leech_c = 10000; //Leech
$missile_c = 5000; //Standard Missile
$dmissile_c = 10000; //D-M Missile
$terra_c = 450000;
} else {
$gmine_c = 800000; //Graviton Mine
$hmine_c = 7500; //Hornet Mine
$leech_c = 100000; //Leech
$missile_c = 50000; //Standard Missile
$dmissile_c = 100000; //D-M Missile
$terra_c = 4500000;
}

// Support Unit Cost

$gmine_t = 150;
$hmine_t = 5;
$leech_t = 300;
$missile_t = 150;
$dmissile_t = 200;
$terra_t = 50;

// All of the literals above need to be redone
// For now, set the TI to half the cost that it is at a home world equip shop
$terra_c = (int)(($cost_bomb * 20) / 2);
$terra_t = (int)((ceil(log($terra_c)) * 100) / 2);


// value of $buy determines upgrade to be purchased

//mass purchase of mines
	settype($num, "integer");
if($mass == 1){
	if ($num < 1) {	// num check for purchase
		$temp_c_num = floor($user[cash] / $gmine_c);
		$temp_t_num = floor($user[tech] / $gmine_t);
		if($temp_c_num < $temp_t_num){
			$final_num = $temp_c_num;
		}else{
			$final_num = $temp_t_num;
		}

		$error_str .= "Enter Number of <b class=b1>Graviton Mines</b> to purchase:";
		$error_str .= "<form name=mass_buy action=bm_bombs.php method=post>";
		$error_str .= "<input type=hidden name=mass value='$mass'>";
		$error_str .= "<input type=hidden name=bmrkt_id value='$bmrkt_id'>";
		$error_str .= "<input name=num value='$final_num' size=3>";
		$error_str .= ' <input type=submit value=Submit></form>';
	}elseif($user[cash] < $gmine_c*$num) { #check to see if the user can afford them
		$error_str = "You can't afford <b>$num</b> Graviton Mines!";
	}elseif($user[tech] < $gmine_t*$num) {
		$error_str = "You do not have enough Support units for <b>$num</b> Graviton Mines!";
	}else{
		dbn(__FILE__,__LINE__,"update ${db_name}_users set grav_mine = grav_mine + '$num' where login_id = '$user[login_id]'");
		take_cash($gmine_c*$num);
		take_tech($gmine_t*$num);
		bm_visit($num);
		$error_str .= "You have successfully purchased <b>$num</b> <b class=b1>Graviton Mines</b>. The Mines are now ready and waiting.<br />";
	}
	$rs = "<p><a href=bm_bombs.php?bmrkt_id=$bmrkt_id>Return to AI Bomb and Mine Store</a>";
	$rs .= "<p><a href=location.php>Back to Star System</a>";
	print_page("Mass Purchase of Graviton Mines",$error_str);

} elseif($mass == 2){
	if ($num < 1) {	// num check for purchase
		$temp_c_num = floor($user[cash] / $hmine_c);
		$temp_t_num = floor($user[tech] / $hmine_t);
		if($temp_c_num < $temp_t_num){
			$final_num = $temp_c_num;
		}else{
			$final_num = $temp_t_num;
		}


		$error_str .= "Enter Number of <b class=b1>Hornet Mines</b> to purchase:";
		$error_str .= "<form name=mass_buy action=bm_bombs.php method=post>";
		$error_str .= "<input type=hidden name=bmrkt_id value='$bmrkt_id'>";
		$error_str .= "<input type=hidden name=mass value='$mass'>";
		$error_str .= "<input name=num value='$final_num' size=3>";
		$error_str .= ' <input type=submit value=Submit></form>';
	}elseif($user[cash] < $hmine_c*$num) { // check to see if the user can afford them
		$error_str = "You can't afford <b>$num</b> Hornet Mines!";
	}elseif($user[tech] < $hmine_t*$num) {
		$error_str = "You do not have enough Support units for <b>$num</b> Hornet Mines!";
	}else{
		dbn(__FILE__,__LINE__,"update ${db_name}_users set hornet_mine = hornet_mine + '$num' where login_id = '$user[login_id]'");
		take_cash($hmine_c*$num);
		take_tech($hmine_t*$num);
		bm_visit($num);
		$error_str .= "You have successfully purchased <b>$num</b> <b class=b1>Hornet Mines</b>. The Mines are now ready and waiting.<br />";
	}
	$rs = "<p><a href=bm_bombs.php?bmrkt_id=$bmrkt_id>Return to AI Bomb and Mine Store</a>";
	$rs .= "<p><a href=location.php>Back to Star System</a>";
	print_page("Mass Purchase of Hornet Mines",$error_str);

}elseif($mass == 3){
	if ($num < 1) {	// num check for purchase
		$temp_c_num = floor($user[cash] / $leech_c);
		$temp_t_num = floor($user[tech] / $leech_t);
		if($temp_c_num < $temp_t_num){
			$final_num = $temp_c_num;
		}else{
			$final_num = $temp_t_num;
		}


		$error_str .= "Enter Number of <b class=b1>Leeches</b> to purchase:";
		$error_str .= "<form name=mass_buy action=bm_bombs.php method=post>";
		$error_str .= "<input type=hidden name=bmrkt_id value='$bmrkt_id'>";
		$error_str .= "<input type=hidden name=mass value='$mass'>";
		$error_str .= "<input name=num value='$final_num' size=3>";
		$error_str .= ' <input type=submit value=Submit></form>';
	}elseif($user[cash] < $leech_c*$num) { // check to see if the user can afford them
		$error_str = "You can't afford <b>$num</b> Leeches!";
	}elseif($user[tech] < $leech_t*$num) {
		$error_str = "You do not have enough Support units for <b>$num</b> Leeches!";
	}else{
		dbn(__FILE__,__LINE__,"update ${db_name}_users set leech = leech + '$num' where login_id = '$user[login_id]'");
		take_cash($leech_c*$num);
		take_tech($leech_t*$num);
		bm_visit($num);
		$error_str .= "You have successfully purchased <b>$num</b> <b class=b1>Leeches</b>. The Leeches are now ready and waiting.<br />";
	}
	$rs = "<p><a href=bm_bombs.php?bmrkt_id=$bmrkt_id>Return to AI Bomb and Mine Store</a>";
	$rs .= "<p><a href=location.php>Back to Star System</a>";
	print_page("Mass Purchase of Leeches",$error_str);

} elseif ($mass == 4){
	if ($num < 1) {	// num check for purchase
		$temp_c_num = floor($user[cash] / $terra_c);
		$temp_t_num = floor($user[tech] / $terra_t);
		if($temp_c_num < $temp_t_num){
			$final_num = $temp_c_num;
		}else{
			$final_num = $temp_t_num;
		}


		$error_str .= "Enter Number of <b class=b1>Terra Imploders</b> to purchase:";
		$error_str .= "<form name=mass_buy action=bm_bombs.php method=post>";
		$error_str .= "<input type=hidden name=mass value='$mass'>";
		$error_str .= "<input type=hidden name=bmrkt_id value='$bmrkt_id'>";
		$error_str .= "<input name=num value='$final_num' size=3>";
		$error_str .= ' <input type=submit value=Submit></form>';
	}elseif($user[cash] < $terra_c*$num) { // check to see if the user can afford them
		$error_str = "You can't afford <b>$num</b> Terra Imploders!";
	}elseif($user[tech] < $terra_t*$num) {
		$error_str = "You do not have enough Support units for <b>$num</b> Terra Imploders!";
	}else{
		dbn(__FILE__,__LINE__,"update ${db_name}_users set terra_imploder = terra_imploder + '$num' where login_id = '$user[login_id]'");
		take_cash($terra_c*$num);
		take_tech($terra_t*$num);
		bm_visit($num);
		$error_str .= "You have successfully purchased <b>$num</b> <b class=b1>Terra Imploders</b>. The Imploders are now ready and waiting. Each is capable of reducing any undefended planet to a neat pile of radioactive ash and a sprinkling of vaporised colonists.<br />";
	}
	$rs = "<p><a href=bm_bombs.php?bmrkt_id=$bmrkt_id>Return to AI Bomb and Mine Store</a>";
	$rs .= "<p><a href=location.php>Back to Star System</a>";
	print_page("Mass Purchase of Terra Imploders",$error_str);

} elseif ($mass == 5){
	$total_slots = $user_ship['num_mi'] * 4;
	$empty_slts = $total_slots - $user_ship['missiles'] - $user_ship['dmissiles'];
	if ($num < 1) {	// num check for purchase
		$temp_c_num = floor($user[cash] / $missile_c);
		$temp_t_num = floor($user[tech] / $missile_t);
		if($temp_c_num < $temp_t_num){
			$final_num = $temp_c_num;
		}else{
			$final_num = $temp_t_num;
		}


		$error_str .= "Enter Number of <b class=b1>Standard Missile</b> to purchase:";
		$error_str .= "<form name=mass_buy action=bm_bombs.php method=post>";
		$error_str .= "<input type=hidden name=mass value='$mass'>";
		$error_str .= "<input type=hidden name=bmrkt_id value='$bmrkt_id'>";
		$error_str .= "<input name=num value='$final_num' size=3>";
		$error_str .= ' <input type=submit value=Submit></form>';
	}elseif($empty_slts <= 0) {
		$error_str .= "Stupid Landlover! You have no enough Missile Bays!<p>";
	}elseif($user_ship[num_mi] <= 0) {
		$error_str .= "Stupid Landlover! You need a Missile Bay to launch Missiles!<p>";
	}elseif($user[cash] < $missile_c*$num) { // check to see if the user can afford them
		$error_str = "You can't afford <b>$num</b> Standard Missiles!";
	}elseif($user[tech] < $missile_t*$num) {
		$error_str = "You do not have enough Support units for <b>$num</b> Standard Missiles!";
	}else{
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set missiles = missiles + '$num' where ship_id = '$user[ship_id]'");
		take_cash($missile_c*$num);
		take_tech($missile_t*$num);
		bm_visit($num);
		$error_str .= "You have successfully purchased <b>$num</b> <b class=b1>Standard Missiles</b>. The Missiles are now ready and waiting.<br />";
	}
	$rs = "<p><a href=bm_bombs.php?bmrkt_id=$bmrkt_id>Return to AI Bomb and Mine Store</a>";
	$rs .= "<p><a href=location.php>Back to Star System</a>";
	print_page("Mass Purchase of Standard Missiles",$error_str);

} elseif ($mass == 6){
	$total_slots = $user_ship['num_mi'] * 4;
	$empty_slts = $total_slots - $user_ship['missiles'] - $user_ship['dmissiles'];
	if ($num < 1) {	// num check for purchase
		$temp_c_num = floor($user[cash] / $dmissile_c);
		$temp_t_num = floor($user[tech] / $dmissile_t);
		if($temp_c_num < $temp_t_num){
			$final_num = $temp_c_num;
		}else{
			$final_num = $temp_t_num;
		}


		$error_str .= "Enter Number of <b class=b1>Dark-Matter Missile</b> to purchase:";
		$error_str .= "<form name=mass_buy action=bm_bombs.php method=post>";
		$error_str .= "<input type=hidden name=mass value='$mass'>";
		$error_str .= "<input type=hidden name=bmrkt_id value='$bmrkt_id'>";
		$error_str .= "<input name=num value='$final_num' size=3>";
		$error_str .= ' <input type=submit value=Submit></form>';
	}elseif($empty_slts <= 0) {
		$error_str .= "Stupid Landlover! You have no enough Missile Bays!<p>";
	}elseif($user_ship[num_mi] <= 0) {
		$error_str .= "Stupid Landlover! You need a Missile Bay to launch Missiles!<p>";
	}elseif($user[cash] < $dmissile_c*$num) { // check to see if the user can afford them
		$error_str = "You can't afford <b>$num</b> Dark-Matter Missiles!";
	}elseif($user[tech] < $dmissile_t*$num) {
		$error_str = "You do not have enough Support units for <b>$num</b> Dark-Matter Missiles!";
	}else{
		dbn(__FILE__,__LINE__,"update ${db_name}_ships set dmissiles = dmissiles + '$num' where ship_id = '$user[ship_id]'");
		take_cash($dmissile_c*$num);
		take_tech($dmissile_t*$num);
		bm_visit($num);
		$error_str .= "You have successfully purchased <b>$num</b> <b class=b1>Dark-Matter Missiles</b>. The Missiles are now ready and waiting.<br />";
	}
	$rs = "<p><a href=bm_bombs.php?bmrkt_id=$bmrkt_id>Return to AI Bomb and Mine Store</a>";
	$rs .= "<p><a href=location.php>Back to Star System</a>";
	print_page("Mass Purchase of Dark-Matter Missiles",$error_str);
} elseif($mass == 7){
        if ($num < 1) { // num check for purchase
                $temp_c_num = floor($user[cash] / ($leech_c / 2));
                $temp_t_num = floor($user[tech] / $leech_t);
                if($temp_c_num < $temp_t_num){
                        $final_num = $temp_c_num;
                }else{
                        $final_num = $temp_t_num;
                }


                $error_str .= "Enter Number of <b class=b1>Tachyon Comm. Array</b> to purchase:";
                $error_str .= "<form name=mass_buy action=bm_bombs.php method=post>";
                $error_str .= "<input type=hidden name=bmrkt_id value='$bmrkt_id'>";
                $error_str .= "<input type=hidden name=mass value='$mass'>";
                $error_str .= "<input name=num value='$final_num' size=3>";
                $error_str .= ' <input type=submit value=Submit></form>';
        }elseif($user[cash] < ($leech_c/2)*$num) { // check to see if the user can afford them
                $error_str = "You can't afford <b>$num</b> Tachyon Comm. Array!";
        }elseif($user[tech] < $leech_t*$num) {
                $error_str = "You do not have enough Support units for <b>$num</b> Leeches!";
        }else{
                dbn(__FILE__,__LINE__,"update ${db_name}_users set tachyon_comm = tachyon_comm + '$num' where login_id = '$user[login_id]'");
                take_cash(($leech_c/2)*$num);
                take_tech($leech_t*$num);
                bm_visit($num);
                $error_str .= "You have successfully purchased <b>$num</b> <b class=b1>Tachyon Comm. Arrays</b>. The Tachyon Comm. Arrays are now ready and waiting.<br />";
        }
        $rs = "<p><a href=bm_bombs.php?bmrkt_id=$bmrkt_id>Return to AI Bomb and Mine Store</a>";
        $rs .= "<p><a href=location.php>Back to Star System</a>";
        print_page("Mass Purchase of Tachyon Comm. Arrays",$error_str);

}



//single buy of one mine
if(isset($buy)){

	 if($buy ==1) { //Graviton Mine, can be mass purchased
		if($user[cash] < $gmine_c) {
			$error_str .= "Shiver me hull plates! You don't have enough Credits.<p>";
		} elseif($user[tech] < $gmine_t) {
			$error_str .= "Ignorant planet-dweller! You don't have enough Tech. Support Units.";
		} elseif($sure != 'yes') {
			get_var('Purchase Graviton Mine',$filename,"Are you sure you want to buy a <b class=b1>Graviton Mine</b>?",'sure','');
		} elseif (($gmine_c) || ($gmine_t) || ($user[login_id] ==1)) {
			$error_str .= "<b class=b1>Graviton Mine</b>, purchased for <b>$gmine_c</b> Credits and <b>$gmine_t</b> Tech. Support Units.<p>";
			take_cash($gmine_c);
			take_tech($gmine_t);
			bm_visit(1);
			dbn(__FILE__,__LINE__,"update ${db_name}_users set grav_mine = grav_mine + 1 where login_id = '$user[login_id]'");
		}
	 } elseif($buy ==2) { //Hornet Mine, can be mass purchased

		if($user[cash] < $hmine_c) {
			$error_str .= "Shiver me hull plates! You don't have enough Credits.<p>";
		} elseif($user[tech] < $hmine_t) {
			$error_str .= "Ignorant planet-dweller! You don't have enough Tech. Support Units.";
		} elseif($sure != 'yes') {
			get_var('Purchase Hornet Mine',$filename,"Are you sure you want to buy a <b class=b1>Hornet Mine</b>?",'sure','');
		} elseif (($hmine_c) || ($hmine_t) || ($user[login_id] ==1)) {
			$error_str .= "<b class=b1>Hornet Mine</b>, purchased for <b>$hmine_c</b> Credits and <b>$hmine_t</b> Tech. Support Units.<p>";
			take_cash($hmine_c);
			take_tech($hmine_t);
			bm_visit(1);
			dbn(__FILE__,__LINE__,"update ${db_name}_users set hornet_mine = hornet_mine + 1 where login_id = '$user[login_id]'");
		}
	} elseif($buy ==3) { //Leeches, can be mass purchased
		if($user[cash] < $leech_c) {
			$error_str .= "Shiver me hull plates! You don't have enough Credits.<p>";
		} elseif($user[tech] < $leech_t) {
			$error_str .= "Ignorant planet-dweller! You don't have enough Tech. Support Units.";
		} elseif($sure != 'yes') {
			get_var('Purchase Leech',$filename,"Are you sure you want to buy a <b class=b1>Leech</b>?",'sure','');
		} elseif (($leech_c) || ($leech_t) || ($user[login_id] ==1)) {
			$error_str .= "<b class=b1>Leech</b>, purchased for <b>$leech_c</b> Credits and <b>$leech_t</b> Tech. Support Units.<p>";
			take_cash($leech_c);
			take_tech($leech_t);
			bm_visit(1);
			dbn(__FILE__,__LINE__,"update ${db_name}_users set leech = leech + 1 where login_id = '$user[login_id]'");

		}
	} elseif($buy ==4) { //Terra Imploder, can be mass purchased
		if($user[cash] < $terra_c) {
			$error_str .= "Shiver me hull plates! You don't have enough Credits.<p>";
		} elseif($user[tech] < $terra_t) {
			$error_str .= "Ignorant planet-dweller! You don't have enough Tech. Support Units.<p>";
		} elseif($sure != 'yes') {
			get_var('Purchase Terra Imploder',$filename,"Are you sure you want to buy a <b class=b1>Terra Imploder</b>? Terra Imploders are capable of reducing any undefended planet to a neat pile of radioactive ash.",'sure','');
		} elseif (($terra_c) || ($terra_t) || ($user[login_id] ==1)) {
			$error_str .= "<b class=b1>Terra Imploder</b>, purchased for <b>$terra_c</b> Credits and <b>$terra_t</b> Tech. Support Units.<p>";
			take_cash($terra_c);
			take_tech($terra_t);
			bm_visit(1);
			dbn(__FILE__,__LINE__,"update ${db_name}_users set terra_imploder = terra_imploder + 1 where login_id = '$user[login_id]'");

		}
	} elseif($buy ==5) { //Std Missiles, can be mass purchased
		$total_slots = $user_ship['num_mi'] * 4;
		$empty_slts = $total_slots - $user_ship['missiles'] - $user_ship['dmissiles'];
		if($user_ship[num_mi] <= 0) {
			$error_str .= "Stupid Landlover! You need a Missile Bay to launch Missiles!<p>";
		} elseif($empty_slts <= 0) {
			$error_str .= "Stupid Landlover! You have no enough Missile Bays!<p>";
		} elseif($user[cash] < $missile_c) {
			$error_str .= "Shiver me hull plates! You don't have enough Credits.<p>";
		} elseif($user[tech] < $missile_t) {
			$error_str .= "Ignorant planet-dweller! You don't have enough Tech. Support Units.<p>";
		} elseif($sure != 'yes') {
			get_var('Purchase Standard Missile',$filename,"Are you sure you want to buy a <b class=b1>Standard Missile</b>?",'sure','');
		} elseif (($missile_c) || ($missile_t) || ($user[login_id] ==1)) {
			$error_str .= "<b class=b1>Standard Missile</b>, purchased for <b>$missile_c</b> Credits and <b>$missile_t</b> Tech. Support Units.<p>";
			take_cash($missile_c);
			take_tech($missile_t);
			bm_visit(1);
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set missiles = missiles + 1 where ship_id = '$user[ship_id]'");

		}
	} elseif($buy ==6) { //D-M Missiles, can be mass purchased
		if($user_ship[num_mi] <= 0) {
		$total_slots = $user_ship['num_mi'] * 4;
		$empty_slts = $total_slots - $user_ship['missiles'] - $user_ship['dmissiles'];
			$error_str .= "Stupid Landlover! You need a Missile Bay to launch Missiles!<p>";
		} elseif($empty_slts <= 0) {
			$error_str .= "Stupid Landlover! You do not have enough Missile Bays!<p>";
		} elseif($user[cash] < $dmissile_c) {
			$error_str .= "Shiver me hull plates! You don't have enough Credits.<p>";
		} elseif($user[tech] < $dmissile_t) {
			$error_str .= "Ignorant planet-dweller! You don't have enough Tech. Support Units.";
		} elseif($sure != 'yes') {
			get_var('Purchase Dark-Matter Missile',$filename,"Are you sure you want to buy a <b class=b1>Dark-Matter Missile</b>?",'sure','');
		} elseif (($dmissile_c) || ($dmissile_t) || ($user[login_id] ==1)) {
			$error_str .= "<b class=b1>Dark-Matter Missile</b>, purchased for <b>$dmissile_c</b> Credits and <b>$dmissile_t</b> Tech. Support Units.<p>";
			take_cash($dmissile_c);
			take_tech($dmissile_t);
			bm_visit(1);
			dbn(__FILE__,__LINE__,"update ${db_name}_ships set dmissiles = dmissiles + 1 where ship_id = '$user[ship_id]'");

		}
	} elseif($buy == 7) {
                if($user[cash] < ($leech_c / 2)) {
                        $error_str .= "Shiver me hull plates! You don't have enough Credits.<p>";
                } elseif($user[tech] < $leech_t) {
                        $error_str .= "Ignorant planet-dweller! You don't have enough Tech. Support Units.";
                } elseif($sure != 'yes') {
                        get_var('Purchase Tachyon Comm. Array',$filename,"Are you sure you want to buy a <b class=b1>Tachyon Comm. Array</b>?",'sure','');
                } elseif (($user[login_id] ==1)) {
                        $error_str .= "<b class=b1>Tachyon Comm. Array</b>, purchased for <b>".($leech_c / 2)."</b> Credits and <b>$leech_t</b> Tech. Support Units.<p>";
                        take_cash($leech_c / 2);
                        take_tech($leech_t);
                        bm_visit(1);
                        dbn(__FILE__,__LINE__,"update ${db_name}_users set `tachyon_comm` = '".($user[tachyon_comm] + 1)."' where login_id = '$user[login_id]'");

                }
        }
}


$error_str .= "<br />Welcome to the <b class=b1>Bomb and Mine Store</b> of your friendly local Blackmarket! Perhaps I could remind you, our most valued customer, that failure to possess the correct amount of credits or support units may result in possible injury, mutilation, or even death by the most painful means we can find. I do hope you enjoy your visit. Happy shopping!<br />";
$error_str .= "<br />Please examine our wares as laid out below.<br /><br />";

$upg_text = '';
if ($flag_mines) {
	//commented out until code complete
	// $upg_text .= make_row(array("<a href=$filename?buy=1&bmrkt_id=$bmrkt_id>Graviton Mine</a>","$gmine_c","$gmine_t","$link<b>Info</b></a> - <a href=$filename?mass=1&bmrkt_id=$bmrkt_id>Mass Purchase</a>"));
	// $upg_text .= make_row(array("<a href=$filename?buy=2&bmrkt_id=$bmrkt_id>Hornet Mine</a>","$hmine_c","$hmine_t","$link<b>Info</b></a> - <a href=$filename?mass=2&bmrkt_id=$bmrkt_id>Mass Purchase</a>"));
}
//commented out until code complete
// $upg_text .= make_row(array("<a href=$filename?buy=3&bmrkt_id=$bmrkt_id>Leech</a>","$leech_c","$leech_t","$link<b>Info</b></a> - <a href=$filename?mass=3&bmrkt_id=$bmrkt_id>Mass Purchase</a>"));
// $upg_text .= make_row(array("<a href=$filename?buy=7&bmrkt_id=$bmrkt_id>Tachyon Comm. Array</a>",$leech_c / 2, $leech_t,"$link<b>Info</b></a> - <a href=$filename?mass=7&bmrkt_id=$bmrkt_id>Mass Purchase</a>"));

$upg_text .= make_row(array("<a href=$filename?buy=4&bmrkt_id=$bmrkt_id>Terra Imploder</a>",number_format($terra_c),"$terra_t","$link<b>Info</b></a> - <a href=$filename?mass=4&bmrkt_id=$bmrkt_id>Mass Purchase</a>"));

//commented out until code complete
// $upg_text .= make_row(array("<a href=$filename?buy=5&bmrkt_id=$bmrkt_id>Standard Missile</a>","$missile_c","$missile_t","$link<b>Info</b></a> - <a href=$filename?mass=5&bmrkt_id=$bmrkt_id>Mass Purchase</a>"));
// $upg_text .= make_row(array("<a href=$filename?buy=6&bmrkt_id=$bmrkt_id>Dark-Matter Missile</a>","$dmissile_c","$dmissile_t","$link<b>Info</b></a> - <a href=$filename?mass=6&bmrkt_id=$bmrkt_id>Mass Purchase</a>"));

$error_str .= make_table(array("Item Name","Cost","Tech Cost"));
$error_str .= stripslashes($upg_text);
$error_str .= "</table>";

$error_str .= "<br /><p>All items are non-refundable!";

print_page("Blackmarket Bomb and Mine Store",$error_str);
?>