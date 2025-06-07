<?php
require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

sudden_death_check($user);

mt_srand((double)microtime()*1000000);
$planet_img = mt_rand(1,15);

// checks
get_star();
db(__FILE__,__LINE__,"select count(planet_id) as plnt_cnt from ${db_name}_planets where location = '$user[location]'");
$plnt = dbr();
if($plnt[plnt_cnt] >= $star[planetary_slots]) { //blocks planets above star's planet limit
	$error_str = "There are no remaining stable orbital positions that can support a planet in this star system. You will need to locate another Star System with further stable orbits.";
}elseif($user[genesis] < 1) {
	$error_str = "You don't have a genesis device.";
} elseif($user_ship[location] == 1) {
	$error_str = "Cannot build planets in Sol.";
} elseif($star[event_random] > 0 && $user[login_id] != 1) {
	$error_str = "You may not build a planet in system with a random event in.";
} elseif ($user[turns_run] < $turns_before_planet_attack && !isset($letme) && $user[login_id] != 1) {
	print_page("No landing","Cannot land, create or attack a planet within the first <b class=b1>$turns_before_planet_attack turns</b> of your account. This is to stop cheating.");
} elseif($user[turns] < 5) {
	$error_str = "You need 5 turns to create a planet.";
} elseif($planet_name == '') {
	get_var('Name your new planet','planet_build.php',
		"There are <b>" . ($star['planetary_slots']-$plnt['plnt_cnt'])
		. "</b> viable orbital positions in this star system.<br />"
		. "Please enter a name for your new planet:",
		'planet_name','');
} elseif(strlen($planet_name) < 3) {
	$rs = "<p><a href=javascript:history.back()>Try Again</a>";
	print_page("Invalid Name","That is not a valid name. Must have more than three characters.");
} else {
	$planet_name = correct_name($planet_name);
	if(!$planet_name || $planet_name == " " || $planet_name == "") {
		$rs = "<p><a href=javascript:history.back()>Try Again</a>";
		print_page("Invalid Name","That is not a valid name.");
	}
	db(__FILE__,__LINE__,"select planet_name from ${db_name}_planets");
	$name_list = dbr();
	while($name_list) {
		if($planet_name == $name_list[planet_name]) {
			$rs = "<p><a href=javascript:history.back()>Try Again</a>";
			print_page("Invalid Name","This name is already in use. Planet names must be unique.");
		}
		$name_list = dbr();
	}

	// remove gen device, but not from admin.
	if($user[login_id] > 1){
		dbn(__FILE__,__LINE__,"update ${db_name}_users set genesis = genesis - 1 where login_id = $user[login_id]");
	}
	charge_turns(5);

	if($user[clan_id]) {
		$clan_id = $user[clan_id];
	} else {
		$clan_id = -1;
	}

	#calculate the colonist population limit
	$pop_limitx = mt_rand(5,20);
	$pop_limit = $pop_limitx * 1000000;

	// build the new planet
	$q_string = "insert into ${db_name}_planets (";
	$q_string = $q_string . "planet_name,location,planet_type,owner_id,owner_name,clan_id,planet_img,pop_limit";
	$q_string = $q_string . ") values(";
	$q_string = $q_string . "'$planet_name','$user[location]','0','$user[login_id]','$user[login_name]','$clan_id','$planet_img','$pop_limit')";
	// echo $q_string;
	dbn(__FILE__,__LINE__,$q_string);

	post_news("<b class=b1>$user[login_name]</b> created the planet <b class=b1>$planet_name</b>");
	$error_str .= "Your new planet is ready for you.";
}

print_page("Planet Built",$error_str);
?>
