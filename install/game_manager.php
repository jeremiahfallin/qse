<?php

/*
//

File:				game_manager.php
Objective:			Standalone Install Utility for adding/removing games
Version:			SR-RPG (Game Engine) 0.0.4
Author:				Maugrim The Reaper
Edited by:			Maugrim The Reaper
Date Committed:		31 August 2004
Last Date Edited:	n/a

~~~~~~~~~~~~~~~~~~~~~~~~~
Copyright (c) 2004 by:
~~~~~~~~~~~~~~~~~~~~~~~~~
Páaic Brady (Maugrim)
Shadows Rising Project
~~~~~~~~~~~~~~~~~~~~~~~~~
(All rights reserved)
~~~~~~~~~~~~~~~~~~~~~~~~~

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the XXX; either version 1 of the License, or (at your option) any later version.

Note that all changes to this file, if distributed/displayed/presented in
any way whether to associates or the general public must contain a mechanism
to download the source code containing such changes. Removal of this notice,
or any other copyright/credit notice displayed by default in the output to
this source code immediately voids your rights under the GNU General Public License.

//
*/

require_once("install.inc.php");

		if($CONFIG['game_installed'] == 1 && $server_passwd != $CONFIG['adminpass'])
		{
			$sr->assign("post_action", "game_manager.php");
			$sr->display("passwd_form.tpl.html");
			exit();
		}
switch($install_step)
{
	case "1":

		$configfile_writeable = (bool) is_writeable("../qlib/config/config.inc.php");
		$safemode_status = (bool) ini_get('safe_mode');
		$reglobals_status = (bool) ini_get('register_globals');
		$mapdir_status = (bool) is_writeable("../maps");

		// connect to database using configuration options set in previous steps
		$db = db_connect($CONFIG['database_host'], $CONFIG['database_user'], $CONFIG['database_password'], $CONFIG['database'], $CONFIG['database_persistent']);

		$warnings = array();
		$notices = array();

		if($safemode_status === true)
		{
			array_push($warnings, "The $app install utility has detected that your PHP configuration has the &quot;<span style=\"color: #c0c0c0;\">safe_mode</span>&quot; directive set to ON. Although this directive provides improved security, it prevents PHP applications from writing directly to the server hard-drive. Since $app generates map images, it will be necessary to either disable &quot;safe_mode&quot; or manually generate these maps offline, and upload them to the necessary directory.");
		}
		if($reglobals_status === false)
		{
			array_push($warnings, "The $app install utility has detected that your PHP configuration has the &quot;<span style=\"color: #c0c0c0;\">register_globals</span>&quot; directive set to OFF. It is intended to support this PHP directive for $app, however it is possible this may still cause problems in $app's pre-alpha versions. If you experience problems - please re-enable this directive.");
		}
		if($configfile_writeable === true)
		{
			array_push($notices, "The $app install utility has detected that the &quot;<span style=\"color: #c0c0c0;\">config.inc.php</span>&quot; configuration file is currently writeable. As noted after configuration, you should set this file's permissions to prevent unauthorised alterations or access by external users. This measure will protect the sensitive database access information, admin password, and other sensitive information from falling into the wrong hands.");
		}
		if($mapdir_status === false)
		{
			array_push($notices, "The $app installation utility has detected that the &quot;<span style=\"color: #c0c0c0;\">/core/maps</span>&quot; directory is not writeable. This directory is used to store maps generated for a game. $app will be unable to generate maps until this directory is writeable. Please make this directory writeable before generating game maps.");
		}

		if(!empty($warnings))
		{
			$warning_count = count($warnings);
			$sr->assign("warning_count", $warning_count);
			$sr->assign("warning_flag", "true");
			$sr->assign("warnings", $warnings);
		}
		else
		{
			$sr->assign("warning_flag", "false");
		}
		if(!empty($notices))
		{
			$notice_count = count($notices);
			$sr->assign("notice_count", $notice_count);
			$sr->assign("notice_flag", "true");
			$sr->assign("notices", $notices);
		}
		else
		{
			$sr->assign("notice_flag", "false");
		}

		$sr->display("game_option_form.tpl.html");
		exit();


	break;

	case "2":


		// connect to database using configuration options set in previous steps
		$db = db_connect($CONFIG['database_host'], $CONFIG['database_user'], $CONFIG['database_password'], $CONFIG['database'], $CONFIG['database_persistent']);

		// NOTE: Since we need to flush each query and it's status after each request - we will fetch (rather
		// than display) the template for the opening page segment which parses the template and stores it
		// in a variable. Then we print the variable output, execute the SQL dumpfiles (which will output a
		// tidy list of results) and finally fetch and print the closing page segment.

		$opening_template_output = $sr->fetch("game_install_top.tpl.html");
		print($opening_template_output);
		flush();
		ExecSQL("new_game.sql", "/QUANTUM/", $_POST['_dbname']);

		if($CONFIG['var_source'] == 0) //create the db_vars file if required, located in db_name maps folder
		{
			if(!is_dir("$CONFIG[map_path]/$_POST[_dbname]"))
			{
				mkdir("$CONFIG[map_path]/$_POST[_dbname]", 0777);
			}
			db2(__FILE__,__LINE__,"select name,value from ".$_POST['_dbname']."_db_vars order by name");
			$file_loc = "$CONFIG[map_path]/$_POST[_dbname]/db_vars.inc.php";
			$stream = @fopen($file_loc, "w");

			$criticals = array();
			if(empty($stream))
			{
				array_push($criticals, "The Game Manager was unable to create a Game Variable file in the &quot;<span style=\"color: #c0c0c0;\">/maps/$_POST[_dbname]</span>&quot; directory. This directory must be writeable. Please alter the permissions on this directory to either 774, or 777, whichever option enables the Game Manager write to this directory");
			}
			if (!is_writable($file_loc))
			{
				array_push($criticals, "The Game Manager has detected that the &quot;<span style=\"color: #c0c0c0;\">/maps/$_POST[_dbname]/db_vars.inc.php</span>&quot; file is not writeable. This may mean that either the &quot;<span style=\"color: #c0c0c0;\">/maps/$_POST[_dbname]</span>&quot; directory is not writeable (in which case you will have received a prior critical error regarding this) or that the file does exist but is not writeable. Please ensure the directory and files permissions are set to allow the Game Manager write to this file.");
			}
			$output_str = "<?php\n//Database: $db_name, ".date("F j, Y, g:i:s a")."\n\n";
			while($db_var = dbr2())
			{
				$output_str .= "\$$db_var[name] = '$db_var[value]';\n";
			}
			if(!fwrite($stream, $output_str."?>")) //stupid end php tag ends code colouring again...:)
			{
				echo "<h3>See the critical errors below which identify a problem with creating a new game</h3>";
			}
			else
			{
				echo "<h3>Variables successfully created for Database <b>$_dbname</b></h3>";
			}
			if(!empty($criticals))
			{
				$critical_count = count($criticals);
				$sr->assign("critical_count", $critical_count);
				$sr->assign("critical_flag", "true");
				$sr->assign("criticals", $criticals);
			}
			else
			{
				$sr->assign("critical_flag", "false");
			}
		}

		dbn(__FILE__,__LINE__,"INSERT INTO se_games (name, db_name, admin_name, admin_pw, admin_email, game_length_days, day_maint_cnt, status) VALUES ('$_POST[_name]', '$_POST[_dbname]', '$_POST[_adname]', '$_POST[_adpass]', '$_POST[_email]', '$_POST[_length]', '$_POST[_maints]', '$_POST[_status]')");

		$closing_template_output = $sr->fetch("game_install_bottom.tpl.html");
		print($closing_template_output);
		flush();
		exit();


	break;


	case "3":


		$sr->display("game_create_form.tpl.html");
		exit();


	break;


	case "4":
	echo "<form action=\"game_manager.php\" method=\"POST\"><center><table cellspacing=0 cellpadding=2 width=75%><tr><td>";
	echo "Please choose which game you wish to delete. Deleting this game will all the game tables and game data from the database. If you only wish to reset the game, you can do this from the in-game Administration menu.<br><br>";

	/*if($database_persistent == 1)
	{
		$link = mysql_pconnect($database_host, $database_user, $database_password);
	}
	else
	{
		$link = mysql_connect($database_host, $database_user, $database_password);
	}
	mysql_select_db($database);*/

	include_once("includes/db_funcs.inc.php");
	$db = db_connect($database_host, $database_user, $database_password, $database, 0);

	echo "<table cellspacing=0 cellpadding=2 style=\"border : thin solid #444444;\" width=70%>";
	echo "<tr><th valign=top colspan=5 style=\"background-color: #444444; border-bottom: thin solid #444444\">";
	echo "Game Listing</th></tr>"; //1

	db2(__FILE__,__LINE__,"SELECT game_id, name, db_name, paused, status FROM se_games");


	while ($game_stat = dbr2()){
		$p_sed = "";
		if($game_stat['paused'] == 1){
			$p_sed = "<td style=\"border-right: thin solid #444444;\">Paused</td>";
		} else {
			db(__FILE__,__LINE__,"select value from ${game_stat['db_name']}_db_vars where name = 'sudden_death'");
			$sd = dbr();

			if($sd['value'] == 1){
				$p_sed = "<td style=\"border-right: thin solid #444444;\">Sudden Death</td>";
			} else {
				$p_sed = "<td style=\"border-right: thin solid #444444;\">In Progress</td>";
			}
		}
		if($game_stat['status'] == 0) {
			$p_sed = "<td style=\"border-right: thin solid #444444\">Offline</td>";
		}
		echo "<tr><td style=\"border-right: thin solid #444444;\">$game_stat[name]</td>".$p_sed."<td style=\"border-right: thin solid #444444;\"><input type=radio name=_gamedel value=$game_stat[game_id]></td></tr>";

	}

	echo "</table><br>";

	echo "<input type=hidden name=\"install_step\" value=\"5\">";

	echo "<br><br><input type=submit name=\"submit_button\" value=\"Submit!\"></form>";

	echo "</td></tr></table></center>";

    echo "<br><br>";


break;

case "5": // delete game se_games, delete game tables


	echo "<center><table cellspacing=0 cellpadding=2 width=75%><tr><td>";
	echo "<h2>Deleting Game...</h2>";

	include_once("includes/config/config.inc.php");
	include_once("includes/db_funcs.inc.php");
	$db = db_connect($database_host, $database_user, $database_password, $database, 0);


	db(__FILE__,__LINE__,"SELECT * FROM se_games	WHERE game_id = '$_gamedel'");
	$del_dbname = dbr();

	// Important - section relies on db_name not similar to any other table names - so use a specific db_name!
	db(__FILE__,__LINE__,"show tables");
	while($table = dbr())
		{
		$lisstt[] = $table[0];
		}
	$f = 0;

	// Following improvement over the original code ruling table deletion credited
	// to Karrade (http://www.karrade.net/) - 31 Oct 2003

	foreach ($lisstt as $t){
	   if (strpos(" $t", $del_dbname['db_name']."_") == 1){
	         dbn(__FILE__,__LINE__,"DROP TABLE $t");
	         echo "$t has been dropped from the database<br>";
	   }
	}

	/*while($lisstt)
	{
		if(empty($lisstt[$f]))
		{
			break;
		}
		elseif(ereg($del_dbname['db_name'],$lisstt[$f]))
		{
			dbn(__FILE__,__LINE__,"DROP TABLE $lisstt[$f]");
			echo "$lisstt[$f] has been dropped from the database<br>";
			$f++;
		}
		else
		{
			$f++;
			next;
		}
	}*/

	dbn(__FILE__,__LINE__,"DELETE FROM se_games WHERE game_id = '$_gamedel'");

	$map_del_txt = clear_images("$map_path/$del_dbname[db_name]");
	echo $map_del_txt;

	echo "<h3>The Game has been deleted!</h3>";

	echo "The game you chose has now been deleted. You should check all maps were deleted from your filesystem correctly, also the game folder will need to be manually deleted unless you plan on using it again. You may return to the Game Management menu to delete another game or create a replacement game as if you so choose. Thank you for using the Quantum Star Game Managment Utility!";

	echo "<br><br><a href=\"game_manager.php\" target=\"_self\">Return to Game Management menu</a><br><br><a href=\"../game_listing.php\" target=\"_self\">View Game Listing</a></td></tr></table>";


break;


}


				////////////////////////////////
				//// Start of function list ////
				////////////////////////////////

// SplitSqlFile: A modified version of PhpMyAdmins PMA_splitSqlFile() function. Who better to turn to for handling
// SQL files?? Released under the GNU GENERAL PUBLIC LICENSE.


    function SplitSqlFile(&$ret, $sql, $release)
    {
        $sql          = trim($sql);
        $sql_len      = strlen($sql);
        $char         = '';
        $string_start = '';
        $in_string    = FALSE;
        $time0        = time();
        for ($i = 0; $i < $sql_len; ++$i) {
            $char = $sql[$i];
            if ($in_string) {
                for (;;) {
                    $i         = strpos($sql, $string_start, $i);
                    if (!$i) {
                        $ret[] = $sql;
                        return TRUE;
                    }
                    else if ($string_start == '`' || $sql[$i-1] != '\\') {
                        $string_start      = '';
                        $in_string         = FALSE;
                        break;
                    }
                    else {
                        $j                     = 2;
                        $escaped_backslash     = FALSE;
                        while ($i-$j > 0 && $sql[$i-$j] == '\\') {
                            $escaped_backslash = !$escaped_backslash;
                            $j++;
                        }
                        if ($escaped_backslash) {
                            $string_start  = '';
                            $in_string     = FALSE;
                            break;
                        }
                        else {
                            $i++;
                        }
                    }
                }
            }
            else if ($char == ';') {
                $ret[]      = substr($sql, 0, $i);
                $sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
                $sql_len    = strlen($sql);
                if ($sql_len) {
                    $i      = -1;
                } else {
                    return TRUE;
                }
            }
            else if (($char == '"') || ($char == '\'') || ($char == '`')) {
                $in_string    = TRUE;
                $string_start = $char;
            }
            else if ($char == '#'
                     || ($char == ' ' && $i > 1 && $sql[$i-2] . $sql[$i-1] == '--')) {
                $start_of_comment = (($sql[$i] == '#') ? $i : $i-2);
                $end_of_comment   = (strpos(' ' . $sql, "\012", $i+2))
                                  ? strpos(' ' . $sql, "\012", $i+2)
                                  : strpos(' ' . $sql, "\015", $i+2);
                if (!$end_of_comment) {
                    if ($start_of_comment > 0) {
                        $ret[]    = trim(substr($sql, 0, $start_of_comment));
                    }
                    return TRUE;
                } else {
                    $sql          = substr($sql, 0, $start_of_comment)
                                  . ltrim(substr($sql, $end_of_comment));
                    $sql_len      = strlen($sql);
                    $i--;
                }
            }
            else if ($release < 32270
                     && ($char == '!' && $i > 1  && $sql[$i-2] . $sql[$i-1] == '/*')) {
                $sql[$i] = ' ';
            }
            $time1     = time();
            if ($time1 >= $time0 + 30) {
                $time0 = $time1;
                header('X-pmaPing: Pong');
            }
        }
        if (!empty($sql) && ereg('[^[:space:]]+', $sql)) {
            $ret[] = $sql;
        }

        return TRUE;
    }



// Overall exec function for SQL files - Maugrim The Reaper 21 Aug 2003
// If set, will perform a preg_replace to replace $replace with $with (for new_game.sql). This will update the loaded
// SQL file for the relevant db_name provided.

function ExecSQL ($filename, $replace, $with) {
	global $db , $CONFIG, $sr;
	$file = fopen('sql/'.$CONFIG['db_type'].'/'.$filename, 'rb');
    $sql_query = fread($file, filesize('sql/'.$CONFIG['db_type'].'/'.$filename));
    fclose($file);
	$pieces       = array();
	SplitSqlFile($pieces, $sql_query, '32300');
	$pieces_count = count($pieces);

	print("
		<table class=\"invisible\" style=\"text-align: left;\">
			<tr>
				<td>
					<p class=\"h3\">Running $CONFIG[db_type] -> $filename</p>
				</td>
			</tr>
	");
	flush();

	for ($i = 0; $i < $pieces_count; $i++)
	{
		$a_sql_query = $pieces[$i];
		if(isset($replace) && isset($with) && $replace != "" && $with != "") {
			$a_sql_query = preg_replace($replace, $with, $a_sql_query);
		}
		$result = $db->Execute($a_sql_query);
		if ($result == FALSE)
		{
			print("
				<tr>
					<td style=\"width: 70%;\">
						" . strip_tags($a_sql_query) . ";
					</td>
					<td style=\"width: 30%;\">
						<div style=\"color: red;\">
						Query Error in Execution!<br />
						<span style=\"color: yellow;\">
						Execution Error:<br />".$db->ErrorMsg()."
						</span></div>
					</td>
				</tr>
			</table>
			");
			flush();
			exit();
		}
		else
		{
			print("
				<tr>
					<td width=\"70%\">
						" . strip_tags($a_sql_query) . ";
					</td>
					<td style=\"width: 30%;\" nowrap=\"nowrap\">
						<div style=\"color: lightgreen;\">
						Successfully Executed!
						</div>
					</td>
				</tr>
			");
			flush();
		}
	}
	unset($pieces);
	if($pieces_count == 0)
	{
		print("
			</table>
			<p class=\"h3\">
			<span style=\"color: red;\">Warning!</span> The Install Utility was unable to execute any queries from &quot;$CONFIG[db_type] -> $filename&quot;. Please double check that the configuration file contains valid database details for username, password and database. Please note that the database must be created BEFORE beginning the installation of database tables.
			</p>
		");
		flush();
	}
	else
	{
		print("
			</table>
			<p class=\"h3\">
			$pieces_count Queries successfully completed!
			</p>
		");
		flush();
	}
}


// function to delete images from the maps folder for game being deleted
function clear_images($path)
{
	$dir = opendir($path);
	$message .= "<p>";
	while($filename = readdir($dir))
	{
		if(eregi("\\.png$", $filename))
		{
			unlink("$path/$filename");
			$message .= "<br>$path/$filename - deleted";
		}
	}
	$message .= "</p>";
	closedir($dir);
	$message .= "<br /> Attempting to delete the game map folder <br />";
	rmdir($path);
	$message .= "The main Map flder for the game was deleted.";
	Return $message;
}
?>
