<?php

#function that will print the left column
function Player_Status($user) {
	global $user_ship, $count_days_left_in_game, $db_name, $max_turns, $enable_politics, $turns_safe, $flag_bmrkt, $user_options, $border_prop, $flag_research, $max_clans, $owner_id, $tpl;

	db(__FILE__,__LINE__,"select name,game_days_remaining,sd_day_num from se_games where db_name = '${db_name}'");
	$game_info = dbr();
	$tpl->assign("gamename", $game_info['name']);
	$tpl->assign("daysleft", $game_info['game_days_remaining']);
	$tpl->assign("daystosd", $game_info['sd_day_num']);

	db(__FILE__,__LINE__,"select count(login_id) as onl from ${db_name}_users where login_id > 1 and last_request > ".(time()-300));
	$lr_result = dbr();
	$tpl->assign("activeusers", $lr_result['onl']);

	db(__FILE__,__LINE__,"select count(login_id) as adm from ${db_name}_users where login_id = 1 and last_request > ".(time()-300));
	$ad_cnt = dbr();
	if($ad_cnt['adm'] != 0)
	{
		$tpl->assign("admin_online", "true");
	}


	db(__FILE__,__LINE__,"select paused from se_games where db_name = '$db_name'");
	$paused = dbr();
	if($paused['paused'] == 1)
	{
		$pause_status = "Paused"; //tpl
	}
}
	echo print_name($user,1);

	if ($flag_research != 0) {
		echo "<tr><td width=50% nowrap>T. Units:</td><td width=50% nowrap>".number_format($user['tech'])."</td></tr>";
	}

	echo "<tr><td width=50% nowrap>Turns:</td><td width=50% nowrap>$user[turns] / $max_turns</td></tr>";
	if($user['turns_run'] < $turns_safe){
		$s_turns = $turns_safe - $user['turns_run'];
		echo "<tr><td width=50% nowrap>Safe Turns:</td><td width=50% nowrap>$s_turns</td></tr>";
	} elseif($user['turns_run'] == $turns_safe && $user['login_id'] != 1 && $user['login_id'] != $owner_id) {
		echo "<tr><td colspan=2><div align=\"center\"><b><b class=b1>Now Leaving Newbie Safety</b></div></td></tr>";
		dbn(__FILE__,__LINE__,"update ${db_name}_users set turns_run = turns_run + '1' where login_id = '$user[login_id]'");
		dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'$user[login_name]','$user[login_id]','$user[login_id]','You have just left Newbie safty, which has been specified for this game by the Admin as <b>$turns_safe</b> turns.<p>This means that you are now attackable by any player who has passed the <b class=b1>turns before attack</b> period. <p>Good Luck.')");
	}


#	echo "<tr><td>Fighter Kills:</td><td><b>$user[fighters_killed]</b></td></tr>";
#	echo "<tr><td>Fighters Lost:</td><td><b>$user[fighters_lost]</b></td></tr>";
#	echo "<tr><td>Ships Killed:&nbsp;</td><td><b>$user[ships_killed]</b></td></tr>";
#	echo "<tr><td>Ships Lost:</td><td><b>$user[ships_lost]</b></td></tr>";
	echo "<tr><td width=50% nowrap>Score:</td><td width=50% nowrap>$user[score]</td></tr></table></td></tr></table>";


	echo(print_ship_info());

	echo "<br /><table cellspacing=0 cellpadding=2 class=all width=100%><tr><th>Side Menu</th></tr><tr><td><table cellspacing=0 cellpadding=0 border=0><tr><td>";

	echo "<a href=location.php>Star System</a>";

	//echo "<br /><a href=diary.php>Fleet Diary</a>";

	/*
	db(__FILE__,__LINE__,"select count(news_id) from ${db_name}_news where login_id = -1 && timestamp > '$user[last_access_news]'");
	$newss = dbr();
	echo "<a href=news.php>News</a>";
	if($newss['0'] > 0) {
		$ad_new = " ($newss[0] <a href=news.php?show_admin_news=1>Admin</a>)";
		echo " $ad_new";
	}
	echo "<br /><a href=news_maint.php>Maints</a>";


	db(__FILE__,__LINE__,"select count(m.message_id) from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' && m.sender_id = u.login_id");
	$counted = dbr();
	if($counted[0] == 0){
		echo "<br /><b class=b1>No Msgs</b> - <a href=message.php>Send</a>";
	}else{
		echo "<br /><a href=mpage.php><b>$counted[0]</b> Msg(s)</a> - <a href=message.php>Send</a>";
	}


	db(__FILE__,__LINE__,"select count(message_id) as new_messages from ${db_name}_messages where timestamp > '$user[last_access_forum]' && login_id = -1 && sender_id != '$user[login_id]'");
	$messg_count_forum = dbr();
	$temp_forum_text = "";
	if($messg_count_forum['new_messages'] > 0){
		$temp_forum_text = " ($messg_count_forum[new_messages] <a href=forum.php?last_time=$user[last_access_forum]&find_last=1>new</a>)";
	}
	echo "<br /><a href=forum.php>Forum</a> $temp_forum_text";

	if($user['login_id'] == 1) {
		echo "<br /><a href=forum.php?clan_forum=1>Clan Forums</a>";
	} elseif($user['clan_id'] > 0) {
		db(__FILE__,__LINE__,"select count(message_id) as new_messages from ${db_name}_messages where timestamp > '$user[last_access_clan_forum]' && login_id = -5 && clan_id = '$user[clan_id]' && sender_id != '$user[login_id]'");
		$messg_count_clan_forum = dbr();
		if($messg_count_clan_forum['new_messages'] > 0){
			 $temp_clan_forum_text_var = " ($messg_count_clan_forum[new_messages] new)";
		}
		echo "<br /><a href=forum.php?clan_forum=1><font color=$user[clan_sym_color]>$user[clan_sym]</font> Forum</a>$temp_clan_forum_text_var";
	}
	*/

	echo "<br /><a href=http://se.cornerjukebox.com/forum/ target=_blank>SE Global Forum</a>";
	echo "<br /><a href='http://www.quantum-star.com' target=_blank>Quantum Star SE</a>";

	//-----------------------------------
	// See how many clan mates are online
	//-----------------------------------
	db(__FILE__,__LINE__,"select count(login_id) as cl_num from ${db_name}_users where login_id > 5 && login_id != '$user[login_id]' && clan_id = '$user[clan_id]' && clan_id > 0 && last_request > ".(time()-300));
	$cl_result = dbr();
	echo "<br /><a href=clan.php>Clan Control</a>";
	if($cl_result['cl_num'] > 0 && $user['clan_id'] > 0) {
		echo " <b>($cl_result[cl_num])</b>";
	}

	//echo "<tr><td><a href=player_relations.php?relations=1>Player Relations</a>";

	//echo "<br /><a href=player_stat.php>Player Ranking</a>";
#	echo "<br /><a href=player_info.php?target=$user[login_id]>Player Info</a>";

	if($enable_politics == 1) {
		echo "<br /><a href=politics.php>Politics</a>";
	}

	#admin lower sidebar
	if($user['login_id'] == 1){
		#echo "<br /><a href=admin.php>Admin</a>";
		#echo "<br /><a href=help.php target=_blank>Help</a>";
		#echo "<br /><a href=options.php>Options</a>";
		#echo "<br /><a href=main_issuetracker.php?bug=1>Game Bugs</a>";
		#echo "<br /><a href=main_issuetracker.php?feature=1>Game Features</a>";
		#echo "<br /><a href=logout.php?logout_single_game=1 target=_top>Logout</a>";
	} else { #player lower sidebar
		#echo "<br /><a href=help.php target=_blank>Help</a>";
		#echo "<br /><a href=options.php>Options</a>";
		#echo "<br /><a href=main_issuetracker.php?bug=1>Game Bugs Control</a>";
		#echo "<br /><a href=main_issuetracker.php?feature=1>Game Features Control</a>";
		echo "<br /><a href=game_listing.php target=_top>GameList</a>";
		#echo "<br /><a href=logout.php?comp_logout=1 target=_top>Complete Logout</a>";
	}
	echo '</tr></td></table></tr></td></table>';
	echo '</td><td width=20></td><td valign=top>'; //close right-side column and open central column of main table
}





?>