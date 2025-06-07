<?php
include_once("includes/nocache.inc.php");

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

$filename = 'science.php';

$rs = "<p><a href=science.php>Return to Observatory</a>";

if (($user['location'] != $user['race']) && ($user['login_id'] != 1)) {
	$rs = "<p><a href=location.php>Back to the Star System</a>";
	print_page("Observatory","This <b class=b1>Observatory </b>is not accessable to you. <br>For your Observotory, please go to system <b>$user[race]</b>.");
}

/*if(isset($library)) {
	  $error_str = "As you stroll into the official repository for all information in the universe, you stop to take a look at a huge plaque of the known star systems and their warp links.<p>";
	  $error_str .= "<img src=$games_dir/$db_name/sm_full.gif border=1>";
	  //star_map.php?location=1&size=500 border=1><br />";
	print_page("Observatory",$error_str);
}*/

print_header("$homeworld");
	include_once("themes/".$user_options['theme']."/header.php");
print_status();
echo "<blockquote>";

if ($news) {

	echo make_table(array("",""));
	echo "Last <b>$user_options[news_back]</b> news posts relating to random events:<br />";

	db(__FILE__,__LINE__,"select headline,timestamp from ${db_name}_news where login_id = -10 || login_id = -11 || login_id = 2 || login_id = 3 order by timestamp desc LIMIT $user_options[news_back]");
	while($news = dbr()) {
		echo quick_row("<b>".date("M d - H:i",$news[timestamp]),$news[headline]);
	}
	echo "</table><br />";
	print_footer();
	exit();
}

if($list){
	db(__FILE__,__LINE__,"select count(star_id) from ${db_name}_stars where event_random != '0'");
	$events = dbr();
 	if($events[0]){
		echo "<p>There are a total of <b>$events[0]</b> systems with random events in them.";
		echo "<br />These include:<br />";
		db(__FILE__,__LINE__,"select count(event_random),event_random from ${db_name}_stars where event_random > 0 GROUP by event_random");
		while($ran_ev = dbr()){
			if($ran_ev[event_random] == 4){
				#Mining Rushes
				$t_s .= "<br /><b>$ran_ev[0]</b> <b class=b1>Mining Rush(es)</b>.";
			} elseif($ran_ev[event_random] == 10){
				#Artifical SuperNova
				$t_s .= "<br /><b>$ran_ev[0]</b> <b class=b1>Artificial SuperNova(s)</b> set to explode.";
			} elseif($ran_ev[event_random] == 5){
				#SuperNova
				$t_s .= "<br /><b>$ran_ev[0]</b> <b class=b1>Star(s) Set to go SuperNova</b>(Explode).";
			} elseif($ran_ev[event_random] == 6){
				#SuperNova Remnant - Unknown
				$t_s .= "<br /><b>$ran_ev[0]</b> Unknown <b class=b1>SuperNova Remnant(s)</b>.";
			} elseif($ran_ev[event_random] == 14){
				#SuperNova Remnant - Safe
				$t_s .= "<br /><b>$ran_ev[0]</b> Safe<b class=b1> SuperNova Remnant(s)</b>.";
			} elseif($ran_ev[event_random] == 1){
				#Black holes
				$t_s .= "<br /><b>$ran_ev[0]</b> <b class=b1>Black Hole(s)</b>.";
			} elseif($ran_ev[event_random] == 12){
				#Solar Storm
				$t_s .= "<br /><b>$ran_ev[0]</b> <b class=b1>Solar Storm(s)</b> in Progress.";
			} elseif($ran_ev[event_random] == 2){
				#Nebula
				$t_s .= "<br /><b>$ran_ev[0]</b> systems engulfed by a <b class=b1>Nebula</b>.";
			}
		}
	} else {
		echo "<p>There are no random events in this game.";
	}

	echo $t_s;

	#WormHoles
	$owayworm = 0;
	$twayworm = 0;
	db(__FILE__,__LINE__,"select star_id,wormhole from ${db_name}_stars where wormhole > '0'");
	while($events = dbr()) {
		db2(__FILE__,__LINE__,"select star_id,wormhole from ${db_name}_stars where star_id = '$events[wormhole]' && wormhole = $events[star_id]");
		$tway = dbr2();
		if ($tway[wormhole]) {
				$twayworm++;
		} else {
			$owayworm++;
		}
	}
		echo "<p>WormHole Count:";
	if($owayworm){
		echo "<br /><b>$owayworm</b> <b class=b1>One-Way</b> wormhole(s).";
		if($twayworm){
			$twayworm = $twayworm /2;
			echo "<br /><b>$twayworm</b> <b class=b1>Two-Way</b> wormhole(s).";
		} else {
			echo "<br />There are no <b class=b1>Two-Way</b> wormholes.";
		}
	} elseif($twayworm){
			$twayworm = $twayworm /2;
		echo "<br />There are no <b class=b1>One-Way</b> wormholes.";
		echo "<br /><b>$twayworm</b> <b class=b1>Two-Way</b> wormhole(s).";
	} else{
		echo "<br />There are no wormholes of any type.";
	}

	db(__FILE__,__LINE__,"select COUNT(*) from ${db_name}_powerups");
	$count = dbr();
	echo "<br><br />There are $count[0] powerups in the universe.";

	print_footer();
	exit();
}


echo "The <b class=b1>Observatory of $homeworld</b> is home of the <b class=b1>Science Institute of $homeworld</b>.";
echo "<br />The random event level for this game is <b>$random_events</b> (max is <b>3</b>).";

echo "<p><a href=$filename?list=1>Listing of Random Events/WormHoles</a>";
echo "<br /><a href=$filename?news=1>News of Random Events</a>";
//echo "<p><a href=science.php?library=1>Galactic Library</a><br />";

$rs = "<p><a href=location.php>Back to the Star System</a>";
print_footer();
?>
