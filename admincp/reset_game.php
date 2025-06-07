<?php






include_once('../includes/nocache.inc.php');
require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

if(!isset($_POST['sure'])){
	$out .= 'Are you sure you want to reset the game?';
	get_var('Reset Game',$_SERVER['PHP_SELF'],$out,'sure','yes','index.php');
} elseif(( isset($_POST['sure']) ) && ( $_POST['sure'] == 'yes' )) {
	$out .= '<p>Game reset started.</p>';

/*	dbn(__FILE__,__LINE__,"update se_games set paused = 1 where db_name = '$db_name'");
	out .= 'Game Paused<br />';*/

	dbn(__FILE__,__LINE__,"delete from ${db_name}_users where login_id > 5");
	dbn(__FILE__,__LINE__,"delete from ${db_name}_user_options where login_id > 5");
	$out .= 'Users & their options deleted.<br />';

	dbn(__FILE__,__LINE__,"update ${db_name}_users set turns='1000', turns_run='500', location='1', ship_id='1501', cash='100000000000000', tech='10000000000000', on_planet='0', last_attack='0', alien='0', pirate='0', last_attack_by='', ships_killed='0', ships_lost=0, ships_lost_points=0,ships_killed_points=0, genesis='1', gamma='1', clan_sym='', clan_sym_color='', clan_id=0, clan_leader=0,fighters_killed='0', one_brob='0', alpha='1', sn_effect='1', last_request='0', score='0' where login_id=1");
	$out .= 'Admin account refurbished.<br />';


//	dbn(__FILE__,__LINE__,"DROP TABLE IF EXISTS ${db_name}_clan_relations");
//	dbn(__FILE__,__LINE__,"CREATE TABLE ${db_name}_clan_relations (relation_id int(11) NOT NULL auto_increment, clan_id int(11) NOT NULL default '0', v_clan_id int(11) NOT NULL default '0', clan_permission tinyint(4) NOT NULL default '1', nap_agree tinyint(4) NOT NULL default '0', x_100001 tinyint(4) NOT NULL default '0', PRIMARY KEY  (relation_id), UNIQUE KEY relation_id (relation_id))");

	dbn(__FILE__,__LINE__,"delete from ${db_name}_clan_relations");

	db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'rejoin_delay'");
	$rejoin = dbr();
	if($rejoin['value'] == 1) {
		dbn(__FILE__,__LINE__,"ALTER TABLE `user_accounts` DROP `$db_name`");
		dbn(__FILE__,__LINE__,"update {$db_name}_db_vars set value = 0 where name = 'rejoin_delay'");
		$out .= 'Rejoin Delay set to disabled.';
	}


	dbn(__FILE__,__LINE__,"delete from ${db_name}_news");
	$out .= 'News erased.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_bank_account");
	$out .= 'Bank Accounts Purged.<br />';


	dbn(__FILE__,__LINE__,"delete from ${db_name}_news_maint");
	$out .= 'Maintenance News erased.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_planets");
	$out .= 'Planets erased.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_messages where login_id != 1");
	$out .= 'Messages deleted.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_politics");
	dbn(__FILE__,__LINE__,"INSERT INTO ${db_name}_politics VALUES ( '1', 'Monarch', '0', '', '0')");
	dbn(__FILE__,__LINE__,"INSERT INTO ${db_name}_politics VALUES ( '2', 'Industry Senator', '0', '', '0')");
	dbn(__FILE__,__LINE__,"INSERT INTO ${db_name}_politics VALUES ( '3', 'Military Senator', '0', '', '0')");
	dbn(__FILE__,__LINE__,"INSERT INTO ${db_name}_politics VALUES ( '4', 'Defense Senator', '0', '', '0')");
	dbn(__FILE__,__LINE__,"INSERT INTO ${db_name}_politics VALUES ( '5', 'Trade Senator', '0', '', '0')");
	dbn(__FILE__,__LINE__,"INSERT INTO ${db_name}_politics VALUES ( '6', 'War Senator', '0', '', '0')");
	dbn(__FILE__,__LINE__,"INSERT INTO ${db_name}_politics VALUES ( '7', 'Espionage Senator', '0', '', '0')");
	dbn(__FILE__,__LINE__,"INSERT INTO ${db_name}_politics VALUES ( '8', 'Research Senator', '0', '', '0')");
	$out .= 'Politics refurbished.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_mines");
	$out .= 'Mines Obliterated.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_leeches");
	$out .= 'Cleansed of Leeches.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_naps");
	$out .= 'NAPS Eliminated.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_diary where login_id > 1");
	$out .= 'Diaries erased.<br />';
	dbn(__FILE__,__LINE__,"delete from ${db_name}_clan_forum");
	$out .= 'Clan Forums erased.<br />';
	dbn(__FILE__,__LINE__,"delete from ${db_name}_game_forum");
	$out .= 'Forums erased.<br />';
	dbn(__FILE__,__LINE__,"delete from ${db_name}_ships where ship_id > 1");
	$out .= 'Ships deleted.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_clans");
	$out .= 'Clans deleted.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_bilkos");
	$out .= 'Bilkos Auction House Emptied.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_used");
	$out .= 'Finbarrs Used Item Store Emptied.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_upgrade_units");
	$out .= 'Upgrade list emptied.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_fleets where fleet_id != 1");
	$out .= 'Ship Fleets deleted.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_powerups");

	$out .= 'Powerups deleted.<br />';

	dbn(__FILE__,__LINE__,"delete from ${db_name}_shipyards");

	$out .= "Alien Shipyards deleted.<br />";

	dbn(__FILE__,__LINE__,"delete from ${db_name}_transfer_buffer");
	$out .= 'Ship Transfer Buffer erased.<br />';
	dbn(__FILE__,__LINE__,"INSERT INTO ${db_name}_ships VALUES (1501, 'Broby', 1, 'Admin', 0, 1, 10, 'Flagship', 12, 'Brobdingnagian', 'Brob', 1000, 1000, 32000, 32000, 3000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 7, 0x6f6f3a73763a74773a6f743a6474, 8, 0, 2, 150, 5, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 16000, 4, 2, 1113848058)");
	$out .= 'Admin\'s Ship Inserted. <br />';
	dbn(__FILE__,__LINE__,"INSERT INTO ${db_name}_fleets VALUES (2, 'Admin', 1, 'Admin', 1, 0, 0, 1, 1501, 0, 0)");
	$out .= 'Admin\'s Fleet Inserted<br />';

	dbn(__FILE__,__LINE__,"update se_games set last_reset = ".time().", game_days_remaining = game_length_days, day_maint_cnt = 1, sd_day_num = game_length_days - 10 where db_name = '$db_name'");
	$out .= 'Last reset date updated to now.<br />';
	$out .= 'SD set to 10 days prior to game end.<br />';
	$out .= 'GAME RESET COMPLETED SUCCESSFULLY';

	post_news('Game Reset.');
	insert_history($user['login_id'],'Reset Game');
}
print_page('Reset Game',$out);
?>
