<?php
////////////////////////////////////////////////////////////////////////////////////////
//Server Info Script v1.0 by KilerCris//////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////
///Modified 3 October 2003 by Maugrim The Reaper////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////
//Server Operator: Edit these vars//////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////
$server_op = ""; //Name/Nick of server operator
$server_op_email = ""; //Email address of server operator
$server_name = ""; //Name of server
$server_version = ""; //Release version of codebase.  Suffix 'M' if code is modified
$server_configfolder = ""; //Change to the path of the folder containing configuration file
////////////////////////////////////////////////////////////////////////////////////////

//Block any request not coming from solarempire.com
//if($_SERVER['REMOTE_ADDR'] != gethostbyname("quantum-star.com"))
//{
//	header("HTTP/1.0 403 Forbidden");
//	header("Status: 403 Forbidden");
//	exit;
//}


//Create server information structure
$server_info = array();

require_once($CONFIG['gameroot']."/qlib/config/config.inc.php");

$server_info['op'] = "N/A";
$server_info['opemail'] = $CONFIG['admin_mail'];
$server_info['name'] = $CONFIG['server_name'];
$server_info['ver'] = $CONFIG['code_base'];
$server_info['games'] = array();

require_once($CONFIG['gameroot']."/qlib/db_funcs.inc.php");



//Connect to database
$db = db_connect($CONFIG['database_host'], $CONFIG['database_user'], $CONFIG['database_password'], $CONFIG['database'], $CONFIG['database_persistent']);

unset($game_count);

//Retrieve game information
db(__FILE__,__LINE__,"select * from se_games");

while($game = dbr())
{
	$game_info = array();
	$game_count += 1;

	//Basic game info
	$game_info['name'] = $game['name'];
	$game_info['admin'] = $game['admin_name'];
	$game_info['adminemail'] = $game['admin_email'];
	$game_info['desc'] = $game['description'];
	$game_info['p'] = $game['paused'];
	$game_info['diff'] = $game['difficulty'];
	$game_info['reset'] = $game['reset'];

	////Extended game info

	//Number of stars(value in se_games inaccurate)
	db2(__FILE__,__LINE__,"select count(star_id) as numstars from $game[db_name]_stars");
	$strs = dbr2();
	$game_info['numstars'] = $strs['numstars'];

	//Number of players
	db2(__FILE__,__LINE__,"select count(login_id) as numplay from $game[db_name]_users");
	$usrs = dbr2();
	$game_info['numplay'] = $usrs['numplay'];

	//Current active users count
	db2(__FILE__,__LINE__,"select count(login_id) as active_users from $game[db_name]_users where login_id > 1 and last_request > ".(time()-300));
	$actvusrs = dbr2();
	$game_info['active_u'] = $actvusrs['active_users'];

	//Get approx. time of reset from join time of first real user
	db2(__FILE__,__LINE__,"select joined_game from $game[db_name]_users where login_id != 1 order by login_id limit 1");
	$jgame = dbr2();
	$game_info['age'] = $jgame['joined_game'];

	//Get game status
	db2(__FILE__,__LINE__,"select value from $game[db_name]_db_vars where name = 'sudden_death'");
	$sd_val = dbr2();
	$game_info['sd'] = $sd_val['value'];

	db2(__FILE__,__LINE__,"select value from $game[db_name]_db_vars where name = 'new_logins'");
	$new_logs = dbr2();
	$game_info['nl'] = $new_logs['value'];

	// add this games info to the server array
	$server_info['games'][] = $game_info;
}

$server_info['numgames'] = $game_count;

//Send data
echo base64_encode(urlencode(addslashes(serialize($server_info))));
?>