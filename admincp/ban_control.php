<?php




include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

//ban player from game.
if($ban == 2){ //show ban a player page.
	$max_time = 168;
	if(!isset($ban_target) || $ban_target < 1 || !$ban_time){
		db(__FILE__,__LINE__,"select login_name,login_id from ${db_name}_users where banned_time <= ".time()." && banned_time != -1 && login_id > 5 order by login_name");
		$out .= '<h3>Ban Control</h3>';
		$out .= '<p>Notes:<br />Number of hours you may ban a player for is limited to '.$max_time.', which is 1 week (7 days).<br />Setting a ban-time of -1 means the ban time will last until the game is reset.<br />You may reset a ban period at any time from this page.</p><form action="'.$_SERVER['PHP_SELF'].'" method="post" name="ban_form">';
		$out .= '<p>Select Player to Ban: <br /><br />';
		$out .= '<select name="ban_target">';
		$out .= '<option value="0">Select player... </option>';
		while($list_em = dbr()) {
			$out .= '<option value="'.$list_em['login_id'].'">'.$list_em['login_name'].'</option>';
		}
		$out .= '</select></p>';
		$out .= '<p>Enter the number of hours you would like the player to be banned for:</p><input type="text" name="ban_time" size="3" /> hours';
		$out .= '<input type="hidden" name="ban" value="2" /><p>Please give the reason you are banning this player (do not use apostrophes or quotation marks).</p><textarea name="ban_reason" cols="50" rows="5" wrap="soft"></textarea><br /><br /><input type="submit" value="Ban" /></form>';
	} elseif ($ban_target > 0){
		db(__FILE__,__LINE__,"select login_name from ${db_name}_users where login_id = '$ban_target'");
		$ban_info = dbr();
		if($ban_time > $max_time || $ban_time < -1){
			$out .= '<h3>Ban Control</h3>';
			$out .= 'Maximum period of time a player may be banned for is <b>'.$max_time.'</b> hours.<br />Or set to -1 to ban for the rest of the game.';
		} elseif(!$sure){
			$rs='';
			get_var('Ban Control',$_SERVER['PHP_SELF'],'Are you sure you want to ban <b class=b1>'.$ban_info['login_name'].'</b> for <b>'.$ban_time.'</b> hours?','sure','yes');
		} else {
			$out .= '<h3>Ban Control</h3>';
			insert_history($ban_target,'Was Banned from the game for '.$ban_time.' hours');
			if(empty($ban_reason)){
				$ban_reason = 'No Reason.';
			}

			if($ban_time > 0){
				$ban_time = time() + round($ban_time * 3600);
			}

			dbn(__FILE__,__LINE__,"update ${db_name}_users set banned_time = '$ban_time', banned_reason = '$ban_reason' where login_id = '$ban_target'");
			if($ban_time > 0){
				$time_text = date( 'D jS M - H:i',$ban_time);
			} else {
				$time_text = 'it resets';
			}
			post_news('<b class=b1>'.$ban_info['login_name'].'</b> has been banned from the game until '.$time_text.' by the Admin. <br />The reason being:<br />'.$ban_reason);
			$out = '<b class=b1>'.$ban_info['login_name'].'</b> has been banned from the game until '.$time_text.'.<br /><br />';
		}
	}
} elseif($unban){
	db(__FILE__,__LINE__,"select login_name from ${db_name}_users where login_id = $unban");
	$ban_info = dbr();
	$out .= '<h3>Ban Control</h3>';
	insert_history($unban,'Was Un-Banned from the game');
	dbn(__FILE__,__LINE__,"update ${db_name}_users set banned_time = '0', banned_reason = '' where login_id = '$unban'");
	$out .= '<b class=b1>'.$ban_info['login_name'].'</b> was un-banned.<br /><br />';
	post_news('<b class=b1>'.$ban_info['login_name'].'</b> was un-banned by the Admin');
}

//list players who are presently banned
db(__FILE__,__LINE__,"select login_name, login_id, banned_time, banned_reason from ${db_name}_users where banned_time = -1 || banned_time > ".time()." order by banned_time desc");
$b_t1_out .= 'Listing Banned Players:';
$b_t1_out .= make_table(array('Login Name','Banned until','Reason',''));
while($list_banned = dbr()){
	if($list_banned['banned_time'] != -1){
		$temp_343 = date( 'D jS M - H:i',$list_banned['banned_time']);
	} else {
		$temp_343 = 'End of Game';
	}
	$b_t_out .= make_row(array(print_name($list_banned),$temp_343,$list_banned['banned_reason'],'<a href='.$_SERVER['PHP_SELF'].'?ban=1&unban='.$list_banned['login_id'].'>Un-Ban</a>'));
}

$out .= '<h3>Ban Control</h3>';
$out .= '<a href='.$_SERVER['PHP_SELF'].'?ban=2>Ban a player</a><br /><br />';
if(empty($b_t_out)){
	$out .= '<br /><br />No players presently banned.<br />';
} else {
	$out .= $b_t1_out.$b_t_out.'</table>';
}
print_page('Ban Control',$out);
?>