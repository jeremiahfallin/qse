<?php
require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

//return assoc array
$ADODB_FETCH_MODE = 2;

$filename = 'player_stat.php';
$text .= "Click a players name to get their information up.<p>";
if ($table == 2) {
	$text .= "<a href=$filename>General</a> - Misc - <a href=$filename?table=3>Alive Players</a><br />";
	$text .= "<br />Select order method:";
	$text .= "<br /><a href=$filename?table=2&su=1>Signed Up</a> - <a href=$filename?table=2&lab=1>Last Attack</a> - <a href=$filename?table=2&la=1>Last Attack Date</a> - <a href=$filename?table=2&clan=1>Clan</a> - <a href=$filename?table=2>Score</a> - <a href=$filename?table=2&rac=1>Race</a><br />";
	if($su) {
		db(__FILE__,__LINE__,"select login_name,joined_game,last_attack_by,last_attack,clan_sym,score,clan_sym_color,login_id from ${db_name}_users where login_id != 1 order by joined_game desc");
		#$player = dbr();
		$text .= '<br />Ordered by time of <b>Signing Up</b> (Most Recent at the top)';
		$text .= make_table(array("Name","Signed Up","Last Attack","Last Attack Date","Clan","Score","","Race"));
	} elseif($lab) {
		db(__FILE__,__LINE__,"select login_name,joined_game,last_attack_by,last_attack,clan_sym,score,clan_sym_color,race,login_id from ${db_name}_users where login_id != 1 order by last_attack_by");
		#$player = dbr();
		$text .= '<br />Ordered by <b>Last Attack by</b>';
		$text .= make_table(array("Name","Signed Up","Last Attack","Last Attack Date","Clan","Score","","Race","Rank"));
	} elseif($la) {
		db(__FILE__,__LINE__,"select login_name,joined_game,last_attack_by,last_attack,clan_sym,score,clan_sym_color,race,login_id from ${db_name}_users where login_id != 1 && last_attack > 1 order by last_attack desc");
		#$player = dbr();
		$text .= '<br />Ordered by time of <b>Last Attack</b> (Most Recent at the top)';
		$text .= make_table(array("Name","Signed Up","Last Attack","Last Attack Date","Clan","Score","","Race","Rank"));
	} elseif($clan) {
		db(__FILE__,__LINE__,"select login_name,joined_game,last_attack_by,last_attack,clan_sym,score,clan_sym_color,race,login_id from ${db_name}_users where login_id != 1 && clan_id > 0 order by clan_id desc");
		#$player = dbr();
		$text .= '<br />Ordered by time of <b>Last Attack</b> (Most Recent at the top)';
		$text .= make_table(array("Name","Signed Up","Last Attack","Last Attack Date","Clan","Score","","Race","Rank"));
	} elseif($rac) {
		db(__FILE__,__LINE__,"select login_name,joined_game,last_attack_by,last_attack,clan_sym,score,clan_sym_color,race,login_id from ${db_name}_users where login_id != 1 order by race");
		#$player = dbr();
		$text .= '<br />Ordered by <b>Race</b>';
		$text .= make_table(array("Name","Signed Up","Last Attack","Last Attack Date","Clan","Score","","Race","Rank"));
	} else {
		db(__FILE__,__LINE__,"select login_name,joined_game,last_attack_by,last_attack,clan_sym,score,clan_sym_color,race,login_id from ${db_name}_users where login_id != 1 order by score desc");
		#$player = dbr();
		$text .= '<br />Ordered by <b>Score</b>';
		$text .= make_table(array("Name","Signed Up","Last Attack","Last Attack Date","Clan","Score","","Race","Rank"));
	}
} elseif ($table == 3) {
	$text .= "<a href=$filename>General</a> - <a href=$filename?table=2>Misc</a> - Alive Players<br />";
	db(__FILE__,__LINE__,"select cash,login_name,fighters_killed,ships_killed,ships_lost,turns_run,score,race,login_id from ${db_name}_users where ship_id != 1 && login_id != 1 order by score desc, fighters_killed desc, ships_killed desc,login_name");
	#$player = dbr();
	$text .= '<br />Top Players Ranking. Only showing Alive Players.';
	$text .= make_table(array("Rank","Name","Fighters<br />Killed","Ships <br />Killed","Ships <br />Lost","Turns <br />Run","Score","Race"));
} elseif($table == 4) {
	$text .= "<a href=$filename>General</a> - <a href=$filename?table=2>Misc</a> - <a href=$filename?table=3>Alive Players</a> - Player IP List<br />";
	db(__FILE__,__LINE__,"select cash,login_name,fighters_killed,ships_killed,ships_lost,turns_run,score,race,login_id from ${db_name}_users where ship_id != 1 && login_id != 1 order by score desc, fighters_killed desc, ships_killed desc,login_name");
	#$player = dbr();
	$text .= '<br />Top Players Ranking. Only showing Alive Players.';
	$text .= make_table(array("Rank","Name","Fighters<br />Killed","Ships <br />Killed","Ships <br />Lost","Turns <br />Run","Score","Race"));

} else {
	$text .= "General - <a href=$filename?table=2>Misc</a> - <a href=$filename?table=3>Alive Players</a><br />";
	$text .= "<br />Select order method:";
	$text .= "<br /><a href=$filename?name=1>Login Name</a> - <a href=$filename?figkills=1>Fighter Kills</a> - <a href=$filename?kills=1>Ship Kills</a> - <a href=$filename?lost=1>Ships Lost</a> - <a href=$filename?turns=1>Turns Run</a> - <a href=$filename>Score<br /></a>";
	if($name) {
		db(__FILE__,__LINE__,"select cash,login_name,fighters_killed,ships_killed,ships_lost,turns_run,score,race,login_id from ${db_name}_users where login_id != '1' order by login_name");
		#$player = dbr();
		$text .= '<br />Ordered by <b>Login Name</b>';
	$text .= make_table(array("Rank","Name","Fighters<br />Killed","Ships <br />Killed","Ships <br />Lost","Turns <br />Run","Score","Race"));
	} elseif($figkills) {
		db(__FILE__,__LINE__,"select cash,login_name,fighters_killed,ships_killed,ships_lost,turns_run,score,race,login_id from ${db_name}_users where login_id != 1 && fighters_killed > 0 order by fighters_killed desc");
		#$player = dbr();
		$text .= '<br />Ordered by <b>Fighters Killed</b>';
	$text .= make_table(array("Rank","Name","Fighters<br />Killed","Ships <br />Killed","Ships <br />Lost","Turns <br />Run","Score","Race"));
	} elseif($kills) {
		db(__FILE__,__LINE__,"select cash,login_name,fighters_killed,ships_killed,ships_lost,turns_run,score,race,login_id from ${db_name}_users where login_id != 1 && ships_killed > 0 order by ships_killed desc");
		#$player = dbr();
		$text .= '<br />Ordered by <b>Ship Kills</b>';
	$text .= make_table(array("Rank","Name","Fighters<br />Killed","Ships <br />Killed","Ships <br />Lost","Turns <br />Run","Score","Race"));
	} elseif($lost) {
		db(__FILE__,__LINE__,"select cash,login_name,fighters_killed,ships_killed,ships_lost,turns_run,score,race,login_id from ${db_name}_users where login_id != 1 && ships_lost > 0 order by ships_lost desc");
		#$player = dbr();
		$text .= '<br />Ordered by <b>Ship Lost</b>';
	$text .= make_table(array("Rank","Name","Fighters<br />Killed","Ships <br />Killed","Ships <br />Lost","Turns <br />Run","Score","Race"));
	} elseif($turns) {
		db(__FILE__,__LINE__,"select cash,login_name,fighters_killed,ships_killed,ships_lost,turns_run,score,race,login_id from ${db_name}_users where login_id != 1 && turns_run > 0 order by turns_run desc");
		#$player = dbr();
		$text .= '<br />Ordered by <b>Turns Run</b>';
	$text .= make_table(array("Rank","Name","Fighters<br />Killed","Ships <br />Killed","Ships <br />Lost","Turns <br />Run","Score","Race"));
	} else {
		db(__FILE__,__LINE__,"select cash,login_name,fighters_killed,ships_killed,ships_lost,turns_run,score,race,login_id from ${db_name}_users where login_id != 1 order by score desc");
		#$player = dbr();
		$text .= '<br />Ordered by <b>Score</b>';
	$text .= make_table(array("Rank","Name","Fighters<br />Killed","Ships<br />Killed","Ships<br />Lost","Turns<br />Run","Score","Race"));
	}
}
$text .= '<br />';
$i = 1;


while($player = dbr())
{
	if (!isset($player['last_attack_by']))
	{
		$player['last_attack_by']="~";
	}

	if(isset($player['race'])){
	db3(__FILE__,__LINE__,"select * from races where race_id = {$player['race']} && era = $era");
	$user_race = dbr3();
	$user_race_name = $user_race['race_name'];
	$player['race'] = "$user_race_name";
	}

	//##//
	if (isset($player['clan_sym']))
	{
		$player['clan_sym'] = "<font color=#$player[clan_sym_color]>$player[clan_sym]</font>";
		$player['clan_sym_color'] = "";
	}
	elseif($table == 2)
	{
		$player['clan_sym'] = "~";
	}
	//##//
	if ($player['last_attack'] == 1)
	{
		$player['last_attack']="~";
	}
	elseif (isset($player['last_attack']))
	{
		$player['last_attack'] = date("M d - H:i",$player['last_attack']);
	}
	//##//
	if (isset($player['joined_game']))
	{
		$player['joined_game'] = date("M d - H:i",$player['joined_game']);
	}
	$dis_name = print_name($player);
	unset($player['login_id']);
	$player['cash'] = $i;
	$player['login_name'] = $dis_name;

	$text .= make_hash_row($player);
	if(isset($player)) {
		next;
	}
	if($player['score'] != $last['score'])
	{
		$last = $player;
		$i++;
	}
	}

$text .= "</table><br />";
print_page('Player Ranking',$text);
?>
