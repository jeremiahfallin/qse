<?php
// This is an example config file - please edit the variables for your own server and copy
// the file to /includes/config/ folder. Available for those users who are having difficulties
// in getting the configuration script install.php to save this file.


// Automatically created configuration file. Do not change!

/*********************************************************
*        !!! Quantum Star SE Configuration File !!!
*
*   Please consider each option carefully. 80% of all
*   start up problems for QS are caused by wrongly set
*   configuration variables.
*
*   Fill out all fields !!!
*
**********************************************************/

$var_source = "0"; // change to 1 if your host has PHP safe_mode enabled.
$enable_gzip = "0"; // enable HTML Compression? May allow faster browsing by players in game.
$database_persistent = "1"; // use persistent connections to database? see PHP manual for more info
$db_type = "mysql"; // type of database system used
$database = "testqsse"; // name of the database you created for the game
$database_user = "XXXX";
$database_password = "XXXX";
$database_host = "localhost"; //default - normally requires no change
$database_port = ""; // if blank, default database port is used
$gameroot = "D:/Server/htdocs/testqsse"; //- DO NOT INCLUDE A TRAILING FORWARD SLASH!
$map_path = "D:/Server/htdocs/testqsse/maps"; //- DO NOT INCLUDE A TRAILING FORWARD SLASH!
$sql_path = "D:/Server/htdocs/testqsse/sql"; //- DO NOT INCLUDE A TRAILING FORWARD SLASH!
$server_name = "My Server"; // Title of your server
$url_prefix = "http://localhost/testqsse"; //url to game - DO NOT INCLUDE A TRAILING FORWARD SLASH!
$code_base = "Quantum Star SE 2.1.23"; //change to current version number
$link_forums = "http://se.cornerjukebox.com/forum/"; //enter address of your forum
$admin_mail = "maugrimtr@hotmail.com"; //enter your email addy
$adminpass = "demon"; //password used for crontab to access maints, and you to access create_server/create_game files
$gzip_level = ""; // 0-9 level of compression used
$sendmail = "0"; // whether to require email accounts to be validated
$game_installed = "1"; //please leave as 1 - ensures password is enforced
error_reporting  (E_ERROR | E_PARSE);
?>