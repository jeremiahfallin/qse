<?php
if($new_page){
  require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

  print_header("Game Info");
  print_status();
} else {
	# all variables now taken directly from the DB with each request
	db2(__FILE__,__LINE__,"select name,value from ${db_name}_db_vars order by name");
	while($var_list = dbr2()) {
		$$var_list['name'] = $var_list['value'];
	}
}

function resolve_difficulty($diff){
	if($diff == 1){
		$diff_txt = "Beginner / Easy";
	} elseif($diff == 2){
		$diff_txt = "Beginner -> Intermediate";
	} elseif($diff == 3){
		$diff_txt = "Intermediate / Medium";
	} elseif($diff == 4){
		$diff_txt = "Intermediate -> Advanced";
	} elseif($diff == 5){
		$diff_txt = "Advanced / Hard";
	} elseif($diff == 6){
		$diff_txt = "All skill levels";
	}
	return $diff_txt;
}


db(__FILE__,__LINE__,"select count(login_id),sum(cash),sum(turns),sum(turns_run),sum(ships_killed), sum(fighters_lost) as lost_fighters, sum(fighters_killed) as killed_fighters from ${db_name}_users where login_id > 5");
$ct = dbr();
db(__FILE__,__LINE__,"select count(login_id) from ${db_name}_users where ship_id != 1 and login_id > 5");
$ct2 = dbr();

db(__FILE__,__LINE__,"select description, admin_name, name, paused,last_reset,difficulty from se_games where db_name = '$db_name'");
$descr = dbr();

if(!$new_page) {
	echo make_table(array("",""));
	echo quick_row("Game Name:","<b>$descr[name]</b>");
	echo quick_row("Admin Name:","<b class=b1>$descr[admin_name]</b>");
	if($descr[paused] == 1){
		$g_status = "Paused";
	} else {
		$g_status = "Running";
	}
	echo quick_row("Game Status:","$g_status");
	echo quick_row("Players/Max Players:","$ct[0] / $max_players");
db2(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
$eras = dbr2();
$era = $eras['value'];
	echo quick_row("Era:",$era);
	echo quick_row("Difficulty:",resolve_difficulty($descr[difficulty]));
	echo quick_row("Last reset:",date("M d - H:i",$descr['last_reset']));
	echo "</table><br /><br />";
  if($ct[0] >= $max_players) {
    echo "Player Limit for game reached. No New players allowed to join game.<br />";
  } elseif($new_logins == 0  || $sudden_death[0] == 1){
    echo "Signups are disabled.<br />";
  } else {
	echo "<a href=game_listing.php?join_game=1&game_db=$db_name>Join Game</a>";
  }
  echo "<br /><br />Back to <a href=game_listing.php>Game List</a><br /><br />";

	if($admin_var_show[0] == 1){
		if(!$new_page){
			echo "<a href=game_vars.php?db_name=$db_name>Game Variables</a><br /><br />";
		} else {
			echo "<br /><a href=help.php?game_vars=1>Game Vars</a><br /><br />";
		}
	}

}

if(isset($new_page)){
	echo "<table cellspacing=1 cellpadding=2 border=0>
			<tr>
				<td bgcolor=#555555 nowrap>Admin Name:</td><td bgcolor=#333333><b class=b1>$descr[admin_name]</b></td>
			</tr>
			<tr>
				<td bgcolor=#555555>Difficulty:</td><td bgcolor=#333333>".resolve_difficulty($descr['difficulty'])."</td>
			</tr>
			<tr>
				<td bgcolor=#555555>Last Reset:</td><td bgcolor=#333333>".date("M d - H:i",$descr['last_reset'])."</td>
			</tr>
			<tr>
				<td bgcolor=#555555>Era:</td><td bgcolor=#333333>$era</td>
			</tr>
	</table>";
}
	db(__FILE__,__LINE__,"select * from era where era_id = $era");
	$new_era = dbr();

	echo "<br /><table cellspacing=1 cellpadding=2 border=0><tr bgcolor=#555555>";
	echo "<tr bgcolor=#555555 align=left><td>Year: </td><td bgcolor=#333333>$new_era[year]</td></tr>";
	echo "<tr bgcolor=#555555 align=left><td>Number of Races: </td><td bgcolor=#333333>$new_era[no_races]</td></tr>";
	echo "<td bgcolor=#555555>Era description:</td>";
	echo "<td bgcolor=#333333>$new_era[descrip]</td></tr>";
	echo "</table>";
if($descr['description']){
	echo "<br /><table cellspacing=1 cellpadding=2 border=0><tr bgcolor=#555555><td>Admin description of the game:</td></tr>";
	echo "<tr bgcolor=#333333 align=left><td>$descr[description]</td></tr>";
	echo "</table>";
}

echo "<br /><br />";

//Admin board
//admin news start
//echo "<td valign=top>";
#	echo make_table(array("",""));
echo "<table border=0 cellpadding=5><tr valign=top><td colspan=3>";
db(__FILE__,__LINE__,"select headline,timestamp from ${db_name}_news where login_id = -1 or login_id = -11 order by timestamp desc LIMIT 5");
$news = dbr();
if($news){
	echo "Last 5 news headlines from Admin:<br />";
	echo "<table cellspacing=1 cellpadding=2 border=0 width=525>";
	while($news) {
		echo quick_row("<b>".date("M d - H:i",$news['timestamp']),stripslashes($news['headline']));
		$news = dbr();
	}
	echo "</table><br />";
}
//admin news end


    //echo "</td>";
//Start of the Viewable Information.
echo "</td></tr>";

if($ct2[0]) {
  echo "<tr valign=top><td>";
  echo make_table(array("Players","<b>".($ct[0])."</b>"));
  if($ct[0] > 0) {
    echo quick_row("Players Alive",($ct2['0'])." (".round(($ct2['0']) * 100/ ($ct['0']))."%)");
    echo quick_row("Cash",number_format($ct[1]));
    echo quick_row("Cash Average",number_format(round(($ct['1'] * 100/$ct['0']) / 100)));
    echo quick_row("Turns",$ct['2']);
    echo quick_row("Turns Average",round(($ct['2'] * 100/$ct['0']) / 100));
    echo quick_row("Turns Run",$ct['3']);
    echo quick_row("Turns Run Average",round(($ct['3'] * 100/$ct['0']) / 100));
    echo quick_row("Ship Kills",$ct['4']);
    echo quick_row("Ship Kills Average",round(($ct['4'] * 100/$ct['0']) / 100));
    echo quick_row("Fighters Killed",$ct['killed_fighters']);
    echo quick_row("Avg. Fighters Killed",round(($ct['killed_fighters'] * 100/$ct['0']) / 100));
  }

  echo "</table><br />";
  echo "</tr><td>";

  db(__FILE__,__LINE__,"select count(login_id),sum(fighters) from ${db_name}_ships where login_id != 1 and login_id !=0 && shipclass < 100");
  $ct3 = dbr();
  if($ct3['0'] > 0) {
    echo make_table(array("Ships","<b>$ct3[0]</b>"));
    echo quick_row("Ships Average",round($ct3['0']/$ct['0']));
	echo quick_row("Ship Fighters",$ct3[1]);
    echo quick_row("Avg. Fighters/Ship",round(($ct3['1'] * 100/$ct3['0']) / 100));
    echo "</table><br />";
  }

  db(__FILE__,__LINE__,"select count(planet_id),sum(fighters) from ${db_name}_planets where owner_id != 1 && planet_type >=0");
  $ct4 = dbr();
  if($ct4[0]) {
    echo make_table(array("Planets","<b>$ct4[0]</b>"));
    echo quick_row("Planets Average",number_format($ct4['0']/$ct['0'],3));
    echo quick_row("Planet Fighters",$ct4[1]);
    echo quick_row("Avg. Fighters/Planet",round(($ct4['1'] * 100/$ct4['0']) / 100));
    echo "</table><br />";
  }

  db(__FILE__,__LINE__,"select count(clan_id),sum(members) from ${db_name}_clans where leader_id !=1");
  $ct5 = dbr();
  if($ct5[0]) {
    echo make_table(array("Clans","<b>$ct5[0]</b>"));
    echo quick_row("Membership",$ct5['1']);
    echo "</table><br />";
  }

  echo "</td><td>";

	#echo "Top 10 Players<br />";
  echo make_table(array("Score","Login Name"));
  db(__FILE__,__LINE__,"select login_name,clan_id,clan_sym,clan_sym_color,score from ${db_name}_users where ship_id != 1 and login_id > 5 order by score desc, login_name limit 10");
  $players = dbr();

  while(($players)) {
    if ($players['clan_id'] == 0 || $players[clan_sym] == "") {
      $players['login_name'] = "<b class=b1>$players[login_name]</b>";
    } else {
      $players['login_name'] = "<b class=b1>$players[login_name]</b>(<font color=$players[clan_sym_color]>$players[clan_sym]</font>)";
    }

    echo quick_row("<b>$players[score]</b>",$players['login_name']);
  	$players = dbr();
  }
  echo "</table><br />";

  echo "</td></tr><tr><td colspan=3>";

//Game news

/*	if(!$new_page){
		  echo "Last 25 News Headlines:<br />";
		  echo "<table cellspacing=1 cellpadding=2 border=0 width=525>";
		#	echo make_table(array("",""));

		  db(__FILE__,__LINE__,"select timestamp,headline from ${db_name}_news where login_id != '-1' order by timestamp desc limit 25");
		  while($news = dbr()) {
			  echo quick_row("<b>".date("M d - H:i",$news[timestamp]),stripslashes($news[headline]));
		  }
		  echo "</table>";
	}*/
}
  echo "</table>";
if($new_page){
  require_once("footer.php");
}
?>