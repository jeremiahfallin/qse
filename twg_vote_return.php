<?php
/**
 * Top Web Games Reward Script for QSGEN
 */

ini_set('display_errors','on');
require_once("qlib/config/config.inc.php");
require_once("qlib/db_funcs.inc.php");

$db = db_connect("$database_host","$database_user","$database_password","$database","$database_persistent");

function voteAllowed($uid){
	// This should check to see if this user has voted since 12:00 last monday
	// TWG ticks over every Monday at midnight on their server which is Tuesday 4am in GMT
	$last_mon = round(strtotime('last monday 4am GMT'));

	// Get last voted time from database
	db(__FILE__,__LINE__,"select last_voted from user_accounts where login_id = '$uid'");
	$row = dbr();

	if($row){
		$last_voted = $row['last_voted'];
		if($last_voted <= $last_mon){
			return true;
		} else {
			return false;
		}
	}
}

// Get variables returned by voting script
$voted_login_id = $_POST['uid'];
$vote_counted = $_POST['votecounted'];

$vote_allowed = voteAllowed($voted_login_id);

$now = round(strtotime('now'));

if($vote_allowed){
	// Check if the vote was counted or not and adjust reward amounts as needed.
	if($vote_counted == 1){
		$reward = 200;
	} else {
		$reward = 100;
	}

	// loop though games and apply reward to this user for each game they are in
	db(__FILE__,__LINE__,"select db_name from se_games");

	while($row = dbr()){
		$db_name = $row['db_name'];
		// Give reward
		dbn(__FILE__,__LINE__,"update ${db_name}_users set turns = turns + $reward where login_id = '$voted_login_id'");

		// Send user a message in game to tell them about their reward
		if($vote_counted == 1){
			$text = 'Thankyou for voting.<br><br>Your vote was counted by Top Web Games and you have been rewarded with <b>' . $reward . ' turns</b>.
			<br><br>You are next eligable to vote on ' . date('r',strtotime('next monday 4am GMT'));
			dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text, cat) values(".time().",'Voting System','1','$voted_login_id','$text','3')");
		} else {
			$text = 'Thankyou for voting.<br><br>Your vote was <b>not</b> counted by Top Web Games.<br><br>
			This is probably because someone has already voted this week from your IP address.
			This is very likely if you use a shared connection to the internet and other people on that connection
			also play this game.<br><br> Since you have not tried to vote previously in the last 7 days you have been awarded ' . $reward . ' turns as a "thankyou for trying".
			<br><br>You are next eligable to vote on ' . date('r',strtotime('next monday 4am GMT'));
			dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text, cat) values(".time().",'Voting System','1','$voted_login_id','$text','3')");
		}
	}

	// Set last_voted for this user
	dbn(__FILE__,__LINE__,"update user_accounts set last_voted = $now where login_id = '$voted_login_id'");
} else {
	db(__FILE__,__LINE__,"select last_voted from user_accounts where login_id = '$uid'");
	$row = dbr();

	if($row){
		$last_voted = $row['last_voted'];
	}
	// loop though games and apply message to this user for each game they are in
	db(__FILE__,__LINE__,"select db_name from se_games");

	while($row = dbr()){
		$db_name = $row['db_name'];

		$text = 'Thankyou for trying to vote.<br><br>Your vote has <b>not</b> been counted because you have already voted in the
		last 7 days.  As such you have not received any reward on this occasion.<br><br>You are next eligable to vote on ' . date('r',strtotime('next monday 4am GMT',$last_voted));
		dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text, cat) values(".time().",'Voting System','1','$voted_login_id','$text','3')");
	}
}
?>