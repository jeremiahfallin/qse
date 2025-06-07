<?php
include_once("includes/nocache.inc.php");

#page to list the games that are available for players to join (once logged in).
#Created: 25/1/02
#By: Moriarty

ob_start(); //start buffering output

//DEV: shouldn't need this here - look into these login mechanics
//session_start();

// check for login_id cookie
// Where's login_id coming from again? Bit suspect - it's already in a cookie, so why not use that???
//if(!isset($login_id) || $login_id == 0) {
/*   echo "<script>self.location='".$url_prefix."/login_form.php';</script>";*/
//   exit;
//}

// why resetting cookies???
//SetCookie("login_id",$login_id,time()+2592000);
//SetCookie("login_name",$login_name,time()+2592000);
//SetCookie("session_id",$session_id,0);

require_once("common.inc.php");
//require_once("includes/auth.inc.php");

// check for login_id session var
if(!isset($_SESSION['login_id']) || $_SESSION['login_id'] == 0) {
   echo "<script>self.location='".$url_prefix."/login_form.php';</script>";
   exit;
}

//$link = mysql_connect("$database_host","$database_user","$database_password");
//mysql_select_db(__FILE__,__LINE__,$database);
$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);

// define all variables for concatenation
$alpha_text=""; $beta_text="";


db(__FILE__,__LINE__,"select * from user_accounts where login_id = '$login_id'");
$p_user = dbr();

$check = new Q_AuthCheck();
$check->Check_Auth($p_user);

//check_auth($p_user);

print_header("Games Listing");

if(isset($join_game) && $join_game == 1 && isset($game_db)) { #join a game
	$db_name = $game_db;

		//include rejoin-delay check. Time entered into db_name field of user account when retiring.
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'rejoin_delay'");
		$state = dbr();
		if($state[value] == 1) {
			#db(__FILE__,__LINE__,"select '$db_name' from user_accounts where login_id = '$login_id'");
			#$check1 = dbr();
			if($p_user[$db_name] != 0) {
				$timing = $p_user[$db_name] + 86400; //86400 = 24hrs (no. of secs)
				if(time() < $timing) {
					print_header("Rejoin Delay Activated");
					echo "<b>Rejoin Delay Activated</b><br /><br />In accordance with the current game settings, players who retire from a game are banned from rejoining for 24 hrs. This aims to prevent recent usage of players using a cycle of joining and retiring to explore the universe among other things. This is a form of cheating, one which appears to be growing. To this aim, since you have retired from the game, you must wait 24 hrs after the time you retired before rejoining. We apologise for any inconveniance this may cause to legitimate non-cheating fair-minded players.<br /><br />Accordingly you may rejoin this game on <b>".date( "D jS M - H:i",($timing + 60))."</b>.<br /><p><a href=javascript:history.back()>Back to Gamelist</a>";
					print_footer();
					exit;
				} else {
					dbn(__FILE__,__LINE__,"update user_accounts set ".$db_name." = 0 where login_id = ".$p_user['login_id']);
				}
			}
		}
		// end of rejoin-delay check

	db(__FILE__,__LINE__,"select count(login_id) from ${db_name}_users where login_id = '$login_id'");
	$check_count = dbr();
	db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_players'");
	$max_players = dbr();
	db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'new_logins'");
	$new_logins = dbr();
	db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'sudden_death'");
	$sudden_death = dbr();
	db(__FILE__,__LINE__,"select name,intro_message from se_games where db_name = '$db_name'");
	$game_name = dbr();

	$rs = "<br /><br /><a href=\"javascript:history.back()\">Back to Game Listing</a>";

	if($check_count[0] >= $max_players[0]){
		print_header("Game Full");
		echo "<b class=b1>$game_name[0]</b> is Full. Try a Different Game.";
		print_footer();
		exit;
	} elseif($new_logins[0] == 0 || $sudden_death[0] == 1){
		print_header("Game Full");
		echo "Admin has Disabled Logins for this game (<b class=b1>$game_name[0]</b>). Try a Different Game";
		print_footer();
		exit;
	} elseif(!$in_game_name){ #ask user what they want their in-game name to be.
		print_header("Choose Username");
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'Homeworld'");
		$hw = dbr();
		$homeworld = $hw['value'];

		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
		$eras = dbr();
		$era = $eras['value'];

		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
		$mr = dbr();
		$max_races = $mr['value'];
//if ($homeworld == 1){
		echo "<br />Please enter the Username you would like to appear as in this game (<b class=b1>$game_name[0]</b>).<br /><br />Or you can simply use your Login Name.";
		echo "<br /><br /><form name=form_user_name action=game_listing.php method=post>";
		echo "Game Name: <input name=in_game_name value='$p_user[login_name]' size=10>";
		echo "<input type=hidden name=join_game value=1>";
		echo "<input type=hidden name=game_db value=$game_db>";
		echo "<br><br>Which race would you like to play as?<br><br>";
		if($homeworld == 0){
			db(__FILE__,__LINE__,"select * from races where era = $era && race_ID = 1");
			$race1 = dbr();
			echo "<table width=60% border=0 class=\"menu_border\" cellpadding=1>
  <tr>
    <td width=5% class=gamelist><input type=radio name=race value=$race1[race_ID] checked></td>
    <td width=25% class=gamelist>$race1[race_name]</td>
    <td width=40% class=gamelist>Homeworld: $race1[planet_name]</td>
    <td rowspan=2 class=menu><img src=images/era/$era/$race1[img] width=100 height=100></td>
  </tr>
  <tr>
    <td colspan=3 class=gamelist>$race1[race_descrip]</td>
  </tr>
</table>";
		} else {
			for($i = 1; $i <= $max_races; $i++)
			{
			db(__FILE__,__LINE__,"select * from races where race_ID = $i && era = $era");
			$race2 = dbr();
			if ($i == 1){
			echo "<table width=60% border=0 class=\"menu_border\" cellpadding=1>
  <tr>
    <td width=5% class=gamelist><input type=radio name=race value=$race2[race_ID] checked></td>
    <td width=25% class=gamelist>Race: $race2[race_name]</td>
    <td width=40% class=gamelist>Homeworld: $race2[planet_name]</td>
    <td rowspan=2 class=menu><img src=images/era/$era/$race2[img] width=100 height=100></td>
  </tr>
  <tr>
    <td colspan=3 class=gamelist>$race2[race_descrip]</td>
  </tr>
</table>";
			} else {
			echo "<table width=60% border=0 class=\"menu_border\" cellpadding=1>
  <tr>
    <td width=5% class=gamelist><input type=radio name=race value=$race2[race_ID]></td>
    <td width=25% class=gamelist>Race: $race2[race_name]</td>
    <td width=40% class=gamelist>Homeworld: $race2[planet_name]</td>
    <td rowspan=2 class=menu><img src=images/era/$era/$race2[img] width=100 height=100></td>
 </tr>
  <tr>
    <td colspan=3 class=gamelist>$race2[race_descrip]</td>
  </tr>
</table>";			}
			}
		}
		echo "<br><br><input type=submit value=Submit></form>";
		echo "<br /><br />Note: This name may <b>NOT</b> be changed once in the game.";
		echo "<script> document.form_user_name.in_game_name.focus(); </script>";
/*} elseif ($homeworld == 0){
		echo "<br />Please enter the Username you would like to appear as in this game (<b class=b1>$game_name[0]</b>).<br /><br />Or you can simply use your Login Name.";
		echo "<br /><br /><form name=form_user_name action=game_listing.php method=post>";
		echo "Game Name: <input name=in_game_name value='$p_user[login_name]' size=10>";
		echo "<input type=hidden name=join_game value=1>";
		echo "<input type=hidden name=game_db value=$game_db>";
		echo " - <input type=submit value=Submit></form>";
		echo "<br /><br />Note: This name may <b>NOT</b> be changed once in the game.";
		echo "<script> document.form_user_name.in_game_name.focus(); </script>";
}*/

		print_footer();
		exit;
	} else{ //fine to join
		$in_game_name = trim($in_game_name);
		if((strcmp($in_game_name,htmlspecialchars($in_game_name))) || (strlen($in_game_name) < 3) || (eregi("[^a-z0-9~!@#$%&*_+-=��������׀�� ]",$in_game_name))) {
			print_header("New Account - $game");
			echo "Invalid login name. No slashes, no spaces and minimum of three characters.";
			echo "<p><a href=javascript:history.back()>Back to Sign-up Form</a>";
			print_footer();
			exit();
		}
		#determine if that username is already in user by another player in the game, or another player as a server name.
		db(__FILE__,__LINE__,"select pu.login_name, u.login_name as alternate_name from ${db_name}_users u, user_accounts pu where u.login_id != '$p_user[login_id]' && pu.login_id != '$p_user[login_id]' && (u.login_name = '$in_game_name' || pu.login_name = '$in_game_name')");
		$test_name = dbr();
		if($test_name['login_name'] || $test_name['alternate_name']){
			print_header("Choose Username");
			echo "There is already a user in this game, or on the server, with that username.";
			$rs = "<br /><br /><a href=\"javascript:history.back()\">Try a different one.</a>";
			print_footer();
			exit;
		}

		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'start_turns'");
		$start_turns = dbr();
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'cash_scaling'");
		$cash_scaling = dbr();
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'start_cash'");
		$start_cash = dbr();
		db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'start_tech'");
		$start_tech = dbr();


		#determine what the user entered as their connection speed, and set the default user variables to reflect this.
		#slow
		if($p_user['con_speed'] == 1){
			$news_back = 50;
			$forum_back = 30;
			$show_pics = 0;
			$show_minimap = 0;
			$allow_popups = 1;
			$show_sigs = 0;
			$show_config = 0;
			$show_clan_ships = 0;

		#average
		} elseif($p_user['con_speed'] == 2){
			$news_back = 100;
			$forum_back = 36;
			$show_pics = 1;
			$show_minimap = 1;
			$allow_popups = 1;
			$show_sigs = 1;
			$show_config = 1;
			$show_clan_ships = 0;

		#fast
		} else{
			$news_back = 200;
			$forum_back = 48;
			$show_pics = 1;
			$show_minimap = 1;
			$allow_popups = 1;
			$show_sigs = 1;
			$show_config = 1;
			$show_clan_ships = 1;
		}

//		$vclan = $p_user['login_id'] + 100000; #CREATE V_CLAN_ID USED WHEN NOT IN A CLAN
db(__FILE__,__LINE__,"select * from user_accounts where login_id = $p_user[login_id]");
$emailaddy = dbr();
		//apply sliding scale to cash of applicable
		// sliding scale calculates the game's average net worth in cash and adds to new players as their starting
		// cash. The averaging is very ad-hoc and limits the number of players used as the underlying averager. This
		// will be unbalancing but is being replaced with an averager based on active players logging in at least
		// once over prior two days. If game is paused, or starting cash average falls below the preset start cash of
		// the admin, the sliding average value is ignored. 26-Mar-03, adjusted to 60% of the true average.

		if($cash_scaling['0'] == 1) {
			$start_credits = SlidingScale(0);
		} else {
			$start_credits = $start_cash['0'];
		}

/*		if($race == 1){
			$start_loc == 1;
			$race3 == 1;
		} elseif($race == 2){
			$start_loc == 2;
			$race3 == 2;
		}*/

		#insert user
		dbn(__FILE__,__LINE__,"insert into ${db_name}_users (login_id,login_name,race,joined_game,turns,cash,tech,clan_id,clan_sym,clan_sym_color,email_address, location) VALUES ('$p_user[login_id]','$in_game_name','$race','".time()."','$start_turns[0]','$start_credits','$start_tech[0]','$clan_id','$clan_sym','$clan_sym_color','$emailaddy[email_address]', '$race')");
		//Insert bank account
		dbn(__FILE__,__LINE__,"insert into ${db_name}_bank_account (login_id,loan,deposit) VALUES ('$p_user[login_id]',0,0)");
		#insert clan relations
//		dbn(__FILE__,__LINE__,"insert into ${db_name}_clan_relations (v_clan_id) values ('$vclan')");
//		dbn(__FILE__,__LINE__,"alter table ${db_name}_clan_relations add x_".$vclan." tinyint(4) default '0' not null");

                #insert user options
                $q_s = "insert into ${db_name}_user_options (login_id,color_scheme,news_back,forum_back,show_pics,show_minimap,allow_popups,show_sigs,show_config,show_aim,show_icq,show_clan_ships) VALUES('$p_user[login_id]','$p_user[default_color_scheme]','$news_back','$forum_back','$show_pics','$show_minimap','$allow_popups','$show_sigs','$show_config','$aim_show','$icq_show','$show_clan_ships')";
                dbn(__FILE__,__LINE__,$q_s);


		#send the intro message (if there is one to send).
		if($game_name['intro_message'] != ""){

		$game_name[intro_message] = Clean_Text($game_name[intro_message]);
		$game_name[intro_message] = addslashes($game_name[intro_message]);

			dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (sender_id,sender_name,text,login_id,timestamp) values ('1','Admin','$game_name[intro_message]','$p_user[login_id]','".time()."')");
		}

		insert_history($login_id,"Joined Game");

		#update game counter.
		dbn(__FILE__,__LINE__,"update user_accounts set num_games_joined = num_games_joined + 1 where login_id = '$p_user[login_id]'");

		post_news("<b class=b1>$in_game_name</b> joined the game.");

		echo "<script>self.location='login.php?game_db=$db_name';</script>";
		exit;
	}

} elseif(isset($d_name)){ #list specific game details
	$db_name = $d_name;
	require("game_info.php");


} else { #list games
	echo "<link rel=\"stylesheet\" href=\"themes/red_Tempest/style_p.css\">";
	echo "<div align=\"center\">
<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" border=\"0\" align=\"center\">
	<tr>

		<td width=100% align=\"top\">
			<div align=\"center\">
			<img src=\"qsgen.gif\" alt=\"Welcome to QS: Generations\" title=\"Welcome to QS Generations\" border=\"0\" align=\"top\">
			</div>
		</td>

	</tr>
</table>
</div>
	<table border=0 width=100% cellspacing=0 cellpadding=0><tr width=100%><td width=450 valign=top><br /><br /><br />";

	echo "<div align=\"center\">";
	echo "<b>Game Listings</b> for <b class=b1>$p_user[login_name]</b><br /><br />";
	echo "<table border=1 cellspacing=0 cellpadding=20>";
	echo "To Login, Join, or See Information about a game, click its name below.<br /><br />";
	echo "<tr><td valign=top width=300 bgcolor=#555555 align=center>";
	echo "List of games presently running on this server:<br /><br />";

	//cycle through the games that are running.
	if($p_user['login_id'] == 1) { //let Admins view unavailable games
		db2(__FILE__,__LINE__,"select name, db_name, paused, status from se_games order by game_id");
	}
	else
	{
		db2(__FILE__,__LINE__,"select name, db_name, paused from se_games where status = '1' order by game_id");
	}
	while ($game_stat = dbr2()){
		$p_sed = "";
		if($game_stat['paused'] == 1){
			$p_sed = " (Paused)";
		} else {
			db(__FILE__,__LINE__,"select value from ${game_stat['db_name']}_db_vars where name = 'sudden_death'");
			$sd = dbr();
			if($sd['value'] == 1){
				$p_sed = " (Sudden Death)";
			}
		}
		if($p_user['login_id'] == 1 && $game_stat['status'] == 0) {
			$p_sed .= " - (Offline)";
		}

		db(__FILE__,__LINE__,"select login_id from ${game_stat['db_name']}_users where login_id = '$p_user[login_id]'");


		if($in_game = dbr()) {
			//player in that game (points to location.php).
			$alpha_text .= "<a href=login.php?game_db=" .$game_stat['db_name'] . "&amp;l_in=" .$p_user['login_id']. ">$game_stat[name]</a>$p_sed<br />";
		}
		else
		{
			//player not in that game (points to game_listing.php, requiring game_info.php).
			$beta_text .= "<a href=game_listing.php?d_name=" . $game_stat['db_name'] . ">$game_stat[name]</a>$p_sed<br />";
		}


	}
	echo "Games you are presently in:<br />";
	if(!empty($alpha_text)){
		echo $alpha_text;
	} else {
		echo "<b>None</b><br />";
	}

	echo "<br />Other Games:<br />";
	if(!empty($beta_text)){
		echo $beta_text;
	} else {
		echo "<b>None</b><br />";
	}
	echo "</tr></td></table>";

	echo "<br /> - <a href=http://www.quantum-star.com target=_blank>QS: Generations Forums</a><br />";

	if($p_user['login_id'] != 1){
		echo "<br /> - <a href=logout.php?logout_gamelist=1>Logout Completely</a>";
	}



	echo "</tr></table><br /><br /><br /><br /><br /><div align=\"center\"><a href=docs/copyright.html target=_blank>Copyright Notices</a></div><br /><div align=\"center\"><a href=credits.php target=_blank>Credits</a></div>";

}

require_once("themes/red_Tempest/footer.php");

ob_end_flush(); //flush all output to the browser
exit;

?>
