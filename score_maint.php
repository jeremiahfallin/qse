<?php
require("common.inc.php");
if($server_passwd != $adminpass || !isset($adminpass))
{
        echo("A maintenance request to hourly_maint.php made from IP ".$_SERVER['REMOTE_ADDR']." was refused as a false Server Administrator password was used
. This occurence has been reported to the Server Administrator.");
        exit();
}

$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);
set_time_limit(0);
$games = array();

db4(__FILE__,__LINE__,"select * from se_games where paused = 0 and status >= 1");
while($games = dbr4())
	{
	$db_name = $games['db_name'];
	echo '<h3>Update Scores for "'.$games[name].'"</h3>';
	score_func(0,1);
	echo "Scores successfully updated.<br /><br />";
	}

dbn(__FILE__,__LINE__,"insert into ${db_name}_news_maint (timestamp, headline, login_id) values (".time().",'Score Maintenance run for this game compleatd.','1')");
?>
