<?php
/*
//
File:			common_funcs.inc.php
Objective:		Commonly called functions exported from common.inc.php
Version:		QS 2.2 Beta
Author:			Bryan and other Solar Empire DEVs - modified by Maugrim The Reaper
Date Committed:	~1999 SE		Date Modified:	29 January 2004

Solar Empire is a Public Domain project. Modifications released under GNU License.
Modifications are Copyright (c) 2003, 2004 by P�draic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/



// puts an ad on the page
function print_adcode() { //edit for correct add server details
  $time = time();
  echo "<div align=\"center\"><a href='http://adex3.flycast.com/server/socket/127.0.0.1:2800/click/SolarEmpire/SolarEmpire/1?$time' target='_blank'>";
  echo "<img src='http://adex3.flycast.com/server/socket/127.0.0.1:2800/ad/SolarEmpire/SolarEmpire/1?";
  echo $time,"' border=1 width=468 height=60 alt='Please support our advertisers.  Upon clicking on one of these banners a new window should open up.'></a>";
  echo "\n<br /><font size=-2>When you click on this banner, you will not leave Solar Empire. &nbsp;A new window will open.</font>";
  echo "</div>\n<br />";
}


function print_footer() { //defunct, see footer.php and print_page function. Also ref: header.php
  global $rs,$start_time,$code_base,$user_options,$url_prefix;
  include_once "themes/".$user_options['theme']."/footer.php";
}


function post_news($headline) {
	global $database,$login_id,$db_name,$database_user,$database_password,$database_host,$database_persistent;
	$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);
	dbn(__FILE__,__LINE__,"insert into ${db_name}_news (timestamp, headline, login_id) values (".time().",'$headline','$login_id')");
}

function valid_input($input) {
  //allows alphanumeric and the some other characters but no spaces
  return eregi('^([a-z0-9~!@#$%&*_+-=��������׀��])+$',$input);
}


function correct_name($input) {
  //allows alphanumeric and the some other characters, as well as spaces, HTML and PHP code
	$input = htmlspecialchars($input);
	$input = strip_tags($input);
	// Browsers compress whitespace when displaying HTML, so by compressing the whitespace
	// in a player inputted name, this cannot be abused (e.g. to obscure planet names from OMs).
	$input = trim($input);
	$input = preg_replace('/\s\s+/',' ',$input);
	return eregi_replace('[^a-z0-9~!@#$%&*_+-=��������׀�� .]',"",$input);
}

function Clean_Text($input){
	$search = "*damn* *dyke *fuck* *phuck* *shit* ass asshole amcik andskota arschloch arse* atouche ayir bastard bitch* boiolas bollock* buceta butt-pirate cabron cawk cazzo chink chraa chuj cipa clit cock* cum cunt* dago daygo dego dick* dildo dike dirsa dupa dziwka ejackulate ekrem* ekto enculer faen fag* fanculo fanny fatass fcuk feces feg felcher ficken fitta fitte flikker foreskin phuck fuk* fut futkretzn fuxor gay gook guiena hor hell helvete hoer* honkey hore huevon hui injun jism jizz kanker* kawk kike klootzak knulle kraut kuk kuksuger kurac kurwa kusi* kyrp�* leitch lesbian lesbo mamhoon masturbat* merd merde mibun monkleigh mouliewop muie mulkku muschi nazis nepesaurio nigga* *nigger* nutsack orospu paska* pendejo penis perse phuck picka pierdol* pillu* pimmel pimpis piss* pizda poontsee poop porn pron preteen preud prick pula pule pusse pussy puta puto qahbeh queef* queer* qweef rautenberg schaffer scheiss* scheisse schlampe schmuck screw scrotum sharmuta sharmute shemale shipal shiz skribz skurwysyn slut smut sphencter spic spierdalaj splooge suka teets teez testicle tits titties titty twat twaty vittu votze woose wank* wetback* whoar whore wichser wop yed zabourah";
	$search = str_replace("a",'[a4@]',$search);
	$search = str_replace("l",'[l1!]',$search);
	$search = str_replace("i",'[li1!]',$search);
	$search = str_replace("e",'[e3]',$search);
	$search = str_replace("t",'[t7+]',$search);
	$search = str_replace("o",'[o0]',$search);
	$search = str_replace("s",'[sz$]',$search);
	$search = str_replace("k",'c',$search);
	$search = str_replace("c",'[c(k]',$search);
	$start = "'(\s|\A)";
	$end = "(\s|\Z)'U";
	$search = str_replace("*","([[:alnum:]]*)",$search);
	$search = str_replace(" ","$end $start", $search);
	$search = "$start".$search."$end";
	$search = split(" ",$search);
	return preg_replace($search,"\\1[color=green][bleep!] [/color]\\2",$input);
}

// will output a the beginning of a properly formatted table putting
//the values of the passed array in the first row; expects an enumerated array.
function make_table($input) {
	$ret_str="\n<table cellspacing=\"1\" cellpadding=\"2\" border=\"0\">\n<tr class=\"tr_bg\">";
	foreach($input as $value) {
		$ret_str .= "\n<td>$value\n</td>";
	}
	$ret_str .= "\n</tr>";
	return $ret_str;
}

// will output a the beginning of a properly formatted table putting
//the values of the passed array in the first row; expects an enumerated array.
//Will also set the width the table will take of the page
function make_table_width($input,$width) {
	$ret_str="\n<table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" width=\"$width\"><tr class=\"tr_bg\">";
	foreach($input as $value) {
		$ret_str .= "\n<td>$value\n</td>";
	}
	$ret_str .= "\n</tr>";
	return $ret_str;
}

//outputs a row of a table with the array values bolded in each cell; expects a four-element array.
function make_row($input) {
	$ret_str = "\n<tr class=\"tr_bg_alt\" align=\"left\">";
	foreach($input as $value) {
		if(ord($value) > 57) {
			$ret_str .= "\n<td>$value\n</td>";
		} else {
			$ret_str .= "\n<td><b>$value</b></td>";
		}
	}
	$ret_str .= "\n</tr>";
	return $ret_str;
}

//outputs a row of a table with the array values bolded in each cell; expects a four-element array.
function make_hash_row($input) {
	$ret_str = "\n<tr class=tr_bg_alt align=left>";

	foreach($input as $value){
		if((ord($value) < 48) || (ord($value) > 57)) { //bold everything except integers
			$ret_str .= "\n<td>$value</td>";
		} else {
			$ret_str .= "\n<td><b>$value</b></td>";
		}
	}
	$ret_str .= "\n</tr>";
	return $ret_str;
}

// Builds a "go to page: # # #" list of links for paged tables
function build_page_list($page, $max_size, $row_count, $link_args) {
	unset( $ret );
	if ($row_count > $max_size) {
		$ret = "<p><b>Go to Page</b>:";
		for ( $i = 1; $i <= ceil($row_count / $max_size); $i++ ) {
			$ret .= $i - 1 == $page ? " &nbsp; $i" :
				" &nbsp; <a href=\"$PHP_SELF?${link_args}page=" . ($i - 1) . "\">$i</a>";
		}
		$ret .= "</p>";
	}
	return $ret;
}

function mcit($text) {
  $text = htmlspecialchars($text);
  $text = stripslashes($text);
  //Newlines to br's
  $text = preg_replace("/\n/","<br />",$text);
  //Smilies
  $text = preg_replace("/\[smile\]/","<img src='images/smiles/smile.gif'>",$text);
    $text = preg_replace("/\[sad\]/","<img src='images/smiles/sad.gif'>",$text);
	  $text = preg_replace("/\[surp\]/","<img src='images/smiles/surp.gif'>",$text);
	    $text = preg_replace("/\[help\]/","<img src='images/smiles/help.gif'>",$text);
		  $text = preg_replace("/\[cool\]/","<img src='images/smiles/cool.gif'>",$text);
		    $text = preg_replace("/\[check\]/","<img src='images/smiles/check.gif'>",$text);
			  $text = preg_replace("/\[wink\]/","<img src='images/smiles/wink.gif'>",$text);
			    $text = preg_replace("/\[wtf\]/","<img src='images/smiles/wtf.gif'>",$text);
				  $text = preg_replace("/\[evil\]/","<img src='images/smiles/evil.gif'>",$text);
				    $text = preg_replace("/\[tongue\]/","<img src='images/smiles/tongue.gif'>",$text);
					  $text = preg_replace("/\[mad\]/","<img src='images/smiles/mad.gif'>",$text);
					    $text = preg_replace("/\[ass\]/","<img src='images/smiles/ass.gif'>",$text);
						  $text = preg_replace("/\[mad67\]/","<img src='images/smiles/mad67.gif'>",$text);
						    $text = preg_replace("/\[scream\]/","<img src='images/smiles/scream.gif'>",$text);
							  $text = preg_replace("/\[upto\]/","<img src='images/smiles/upto.gif'>",$text);
							    $text = preg_replace("/\[thumb\]/","<img src='images/smiles/thumb.gif'>",$text);
								$text = preg_replace("/\slol\s/","<img src='images/smiles/lold.gif'>",$text);
  $text = preg_replace("/\[\]\]/","]",$text);
  $text = preg_replace("/\[\[\]/","[",$text);
  //Horizontal rule
  $text = preg_replace("/\[hr=(.*?)\]/","<hr color='\\1' width='60%' align='left'>",$text);
  $text = preg_replace("/\[hr\]/","<hr width='60%' align='left'>",$text);
  //Bold and italics and underline
  $text = preg_replace("/\[b\](.*?)\[\/b\]/","<b>\\1</b>",$text);
  $text = preg_replace("/\[i\](.*?)\[\/i\]/","<i>\\1</i>",$text);
  $text = preg_replace("/\[u\](.*?)\[\/u\]/","<u>\\1</u>",$text);
  $text = preg_replace("/\[bi\](.*?)\[\/bi\]/","<b><i>\\1</i></b>",$text);
  //Links
  $text = preg_replace("/\[url\](.*?)\[\/url\]/","<a href='\\1' target='_new'>\\1</a>",$text);
  //Color
  $text = preg_replace("/\[color=(.*?)\](.*?)\[\/color\]/","<font color='\\1'>\\2</font>",$text);
  //Image
  $text = preg_replace("/\[img\](.*?)\[\/img\]/","<img src='\\1'>",$text);
  //$text = eregi_replace("\[:(.*):\]","{\\1}",$text);
  $text = addslashes($text);
  return $text;
}

//outputs a row of a table with the array values bolded in each cell; expects a four-element array.
function quick_row($name,$value) {
	$ret_str = "\n<tr align=left>";
	$ret_str .= "\n<td class=tr_bg> $name </td>";
	if((ord($value) < 48) || (ord($value) > 57)) {
		$ret_str .= "\n<td class=tr_bg_alt> $value </td>";
	} else {
		$ret_str .= "\n<td class=tr_bg_alt><b> $value </b></td>";
	}
	$ret_str .= "\n</tr>";
	return $ret_str;
}

#load ship types from database.
function load_ship_types() {
	global $db_name,$flag_trading,$fighter_cost_earth;
	$ship_types = array();
	$num_ships=0;
/*	if($flag_trading == 1 && isset($flag_trading)) {
		db(__FILE__,__LINE__,"select s.* from ${db_name}_ship_types s, ${db_name}_admin_ships a where s.type_id = a.ship_type_id && a.status=1 && s.auction != 1 order by type_id");
	} else {
		db(__FILE__,__LINE__,"select s.* from ${db_name}_ship_types s, ${db_name}_admin_ships a where s.type_id = a.ship_type_id && a.status=1 && s.auction != 1 order by type_id");
	}*/
db2(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
$races2 = dbr2();
$era = $races2['value'];
	db(__FILE__,__LINE__,"select * from ship_types where era = $era && type_id > 1 && auction != 1 order by type_id ");
	while($this_type = dbr()) {
		// postgres fix
		foreach($this_type as $key=>$val) {
			if(empty($val) || $val = "")
			{
				$this_type[$key] = 0;
			}
		}
		$ship_types[$this_type['type_id']] = $this_type;
	}
	return $ship_types;
}


#function to insert stuff into the user history in a common format.
function insert_history($l_id=0,$i_text,$this){
	global $REMOTE_ADDR, $HTTP_USER_AGENT,$db_name;
	echo($this);
	if(!isset($db_name))
	{
		$dbname = "None";
	}
	else
	{
		$dbname = $db_name;
	}
	dbn(__FILE__,__LINE__,"insert into user_history VALUES ('$l_id','".time()."','$dbname','$i_text','$REMOTE_ADDR','$HTTP_USER_AGENT')");
}

#function to figure out the size of a ship in textual terms
function discern_size ($size){

	if($size == 1){
		return "Tiny";
	} elseif($size == 2){
		return "Very Small";
	} elseif($size == 3){
		return "Small";
	} elseif($size == 4){
		return "Medium";
	} elseif($size == 5){
		return "Large";
	} elseif($size == 6){
		return "Very Large";
	} elseif($size == 7){
		return "Huge";
	} elseif($size == 8){
		return "Gigantic";
	}
}

//function used to work out players scores based on what they have, and which scoring method is being used.
function score_func($login_id, $full, $db_prefix){
	global $score_method;

	//required workaround for an issue with globals in include files.
	if(!empty($db_prefix))
	{
		$db_name = $db_prefix;
	}
	else
	{
		global $db_name;
	}

		/*
		Listed below are all of the score methods, and some info on them.
		0 = Scores are off.
		1 = (fighter kills + (ship kills * 10)) - (fighter kills * 0.75 + (ship kills *5))
		2 = ship points killed - (ship points lost * 0.5)
		3 = total value (ship/planet fighters plus ship point value)
		4 = ultimate score. takes everything into account.
		*/

	//determines if admin is updateing all scores, or an individual players score is being updated.
	if($full != 1) {
		$extra_text = "login_id = '$login_id'";
		$plan_text = "owner_id = '$login_id'";
	} else {
		$and_text = " and ";
		$extra_text = "login_id > 5";
		$plan_text = "owner_id > 5";
	}
	db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'score_method'");
	$alpha_var = dbr();
	$score_method = $alpha_var['value'];
	if($score_method==1){ #scoring method, whereby only kills,are taken into account.
		dbn(__FILE__,__LINE__,"update ${db_name}_users set score = (fighters_killed + (ships_killed * 10)) - (fighters_lost * 0.3333 + (ships_lost * 3.3333)) where ".$extra_text);
	} elseif($score_method == 2){ #takes into account ship points. //very ad-hoc
		dbn(__FILE__,__LINE__,"update ${db_name}_users set score = ships_killed_points - (ships_lost_points * 0.3333) where ".$extra_text);
	} elseif($score_method == 3){ #total net worth used as score - MTR March2003.
		if($full == 1){
			db(__FILE__,__LINE__,"select count(login_id) as markh from ${db_name}_users where login_id > 5");
			$countc = dbr();
			db(__FILE__,__LINE__,"select * from ${db_name}_users where login_id > 5 order by login_id limit $countc[markh]");
			$score_line = dbr();
			while($score_line) {
				$check_id = $score_line['login_id'];
				$networth = Total_Net_Worth($check_id);
				$networth = round($networth * .65);
				dbn(__FILE__,__LINE__,"update ${db_name}_users set score = ".$networth." where login_id = '$check_id'");
				unset($networth);
				echo("Scores Updated. - $check_id<br />");
				$score_line = dbr();
			}
			dbn(__FILE__,__LINE__,"update ${db_name}_users set score = 0 where score < 0");
		} else {
			$networth = (Total_Net_Worth($login_id)) * .65;
			dbn(__FILE__,__LINE__,"update ${db_name}_users set score = '$networth' where login_id = '$login_id'");
		}
	}
	echo("Scores Updated.<br />");
}

//function to calculate starting cash by reference to current player reduced averages on a sliding scale basis.
function SlidingScale($text) {
	global $db_name,$fighter_cost_earth,$buy_fuel,$buy_organ,$buy_metal,$buy_elect;

	$u_cash = 0;
	$u_tech = 0;
	db(__FILE__,__LINE__,"select cash,tech from ${db_name}_users where login_id > 4");
	while($u_info = dbr()) {
		$u_cash += $u_info['cash'];
		$u_tech += $u_info['tech'];
	}

	$p_cash = 0;
	$p_fighters = 0;
	$p_fuel = 0;
	$p_fuel = 0;
	$p_metal = 0;
	$p_metal = 0;
	db(__FILE__,__LINE__,"select cash,fighters,fuel,metal,organ,elect from ${db_name}_planets where owner_id > 4 group by owner_id");
	while($p_info = dbr()) {
		$p_cash += $p_info['cash'];
		$p_fighters += $p_info['fighters'];
		$p_fuel += $p_info['fuel'];
		$p_organ += $p_info['organ'];
		$p_metal += $p_info['metal'];
		$p_elect += $p_info['elect'];
	}

	$u_fighters = 0;
	$u_fuel = 0;
	$u_fuel = 0;
	$u_metal = 0;
	$u_elect = 0;
	$s_total = 0;
	db2(__FILE__,__LINE__,"select fighters,fuel,metal,organ,elect,ship_id,shipclass from ${db_name}_ships where login_id > 4 group by ship_id");
	while($s_info = dbr2()) {
		$u_fighters += $s_info['fighters'];
		$u_fuel += $s_info['fuel'];
		$u_organ += $s_info['organ'];
		$u_metal += $s_info['metal'];
		$u_elect += $s_info['elect'];

		db(__FILE__,__LINE__,"select cost,fighters from ship_types where type_id = '${s_info['shipclass']}'");

		$s_cost = dbr();
		$s_total += $s_cost['cost'] - ($s_cost['fighters'] * ($fighter_cost_earth * .5));
	}

	#start adding all cash equivalents to a total amount
	$f_total = ($p_fighters + $u_fighters) * $fighter_cost_earth;
	$c_total = $u_cash + $p_cash;
	$fl_total = ($p_fuel + $u_fuel) * $buy_fuel;
	$o_total = ($p_organ + $u_organ) * $buy_organ;
	$m_total = ($p_metal + $u_metal) * $buy_metal;
	$e_total = ($p_elect + $u_elect) * $buy_elect;
	$total_scale = $f_total + $c_total + $fl_total + $o_total + $m_total + $e_total + $s_total;

	dbr(__FILE__,__LINE__,"select count(login_id) from ${db_name}_users where login_id > 4");
	$u_count = dbr();
	if($u_count[0] > 1) {
		$denominator = $u_count[0] * (6/4); //reduces amt by 0.333 to reduce overstated averages
	} else {
		$denominator = 1; //avoid division by zero
	}
	$current_index = round($total_scale / ($denominator * 1000));//remove very odd numbers, move to nearest 1000
	$current_scale = $current_index * 1000;

	#check if scale is too low or game is paused
	db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'start_cash'");
	$start_cash = dbr();
	db(__FILE__,__LINE__,"select paused from se_games where db_name = '$db_name'");
	$paused = dbr();
	if($paused['paused'] == 1) {
		$current_scale = $start_cash[0];
		$out2 = '<br /><br />Cash adjusted back to Starting Cash figure as Game is still paused!';
	} elseif ($start_cash[0] > $current_scale) {
		$current_scale = $start_cash[0];
		$out2 = '<br /><br />Cash adjusted back to Starting Cash figure as Current Starting Scale is too low!';
	}

	$out = '<b>Cash Equivalents of Game Assets</b><br /><br />Fighters <b>'.$f_total.'</b><br />Ships <b>'.$s_total.'</b><br />Cash <b>'.$c_total.'</b><br />Fuel <b>'.$fl_total.'</b><br />Metal <b>'.$m_total.'</b><br />Organics <b>'.$o_total.'</b><br />Electronics <b>'.$e_total.'</b><br /><br />Total Scale <b>'.$total_scale.'</b><br />Scale Index <b>'.$current_index.'</b><br /><br /><b>Current Starting Scale '.$current_scale.' Credits</b> '.$out2;
	if($text == 1) {
		return ($out);
	} else {
		return ($current_scale);
	}
}


//function to calculate starting cash by reference to current player reduced averages on a sliding scale basis.
function Total_Net_Worth($login_id) {
	global $db_name,$fighter_cost_earth,$buy_fuel,$buy_organ,$buy_metal,$buy_elect;
	db2(__FILE__,__LINE__,"select sum(cash),sum(tech) from " . $db_name . "_users where login_id ='$login_id'");
	$u_info = dbr2();
	db2(__FILE__,__LINE__,"select sum(cash),sum(fighters),sum(fuel),sum(metal),sum(organ),sum(elect),sum(colon) from ${db_name}_planets where owner_id ='$login_id'");
	$total_fighters_planet = 0;
	$total_fuel_planet = 0;
	$total_metal_planet = 0;
	$total_organ_planet = 0;
	$total_elect_planet = 0;
	$total_colon_planet = 0;
	$total_cash_planet = 0;
	while($p_info = dbr2()) {
		$total_fighters_planet += $p_info[1];
		$total_fuel_planet += $p_info[2];
		$total_metal_planet += $p_info[3];
		$total_organ_planet += $p_info[4];
		$total_elect_planet += $p_info[5];
		$total_colon_planet += $p_info[6];
		$total_cash_planet += $p_info[0];
	}
	db3(__FILE__,__LINE__,"select sum(fighters),sum(fuel),sum(metal),sum(organ),sum(elect),sum(colon),ship_id,shipclass from ${db_name}_ships where login_id = '$login_id' group by ship_id");
	$s_total = 0;
	$total_fighters_ship = 0;
	$total_fuel_ship = 0;
	$total_metal_ship = 0;
	$total_organ_ship = 0;
	$total_elect_ship = 0;
	$total_colon_ship = 0;
	while($s_info = dbr3()) {
		db2(__FILE__,__LINE__,"select cost,fighters from ship_types where type_id = '${s_info['shipclass']}'");
		$s_cost = dbr2();
		$s_total += $s_cost['cost'] - ($s_cost['fighters'] * ($fighter_cost_earth * .5));
		$total_fighters_ship += $s_info[0];
		$total_fuel_ship += $s_info[1];
		$total_metal_ship += $s_info[2];
		$total_organ_ship += $s_info[3];
		$total_elect_ship += $s_info[4];
		$total_colon_ship += $s_info[5];
	}

	#start adding all cash equivalents to a total amount
	$f_total = ($total_fighters_planet + $total_fighters_ship) * $fighter_cost_earth;
	$c_total = $u_info[0] + $total_cash_planet;
	$fl_total = ($total_fuel_planet + $total_fuel_ship) * $buy_fuel;
	$o_total = ($total_organ_planet + $total_organ_ship) * $buy_organ;
	$m_total = ($total_metal_planet + $total_metal_ship) * $buy_metal;
	$e_total = ($total_elect_planet + $total_elect_ship) * $buy_elect;
	$col_total = ($total_colon_planet + $total_colon_ship) * $cost_colonist;
	$total_scale = $f_total + $c_total + $fl_total + $o_total + $m_total + $e_total + $s_total + $col_total;
	Return $total_scale;
}


?>