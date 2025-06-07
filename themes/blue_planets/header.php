<?php
// header.php : file containing structure of all menu items, graphic bars and banners.


echo ""
//Insert spacing from empty table
."<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">"
."<tr><td>"
."</td></tr></table>";


///////////////////////////////////////////////
//                                           //
//          START OF HEADER OVERALL TABLE    //
//                                           //
///////////////////////////////////////////////

echo ""
."<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">"
."<tr><td valign=\"top\" width=\"175\">";

///////////////////////////////////////////////
//                                           //
//          ADD TOP LEFT SITE LOGO           //
//                                           //
///////////////////////////////////////////////

echo ""
."<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">"
."<tr><td valign=\"center\" align=\"center\">";

// Image Logo size to be added should be 173x33 Pixels

echo "<a href=\"http://www.quantum-star.com/\" target=\"_blank\"><img src=\"$CONFIG[url_prefix]/images/powered/quantumstar_se_small.png\" border=\"0\" width=\"173\" height=\"33\" alt=\"Quantum Star SE\"></a>";

echo "</td></tr></table>"
."</td>
<td width=\"20\">
</td><td valign=\"top\">";





///////////////////////////////////////////////
//                                           //
//          START OF MIDDLE MAIN MENU        //
//                                           //
///////////////////////////////////////////////

// Insert main menu table
echo "<table cellspacing=\"0\" cellpadding=\"0\" class=\"menu_border\" align=\"center\">"
."<tr>"
."<td valign=\"center\" align=\"center\" width=\"100%\" colspan=\"5\">"

// Insert Sub-Table for First Row Menu
."<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">"
."<tr>"

// Insert HOME button
."<td class=\"menu\">&nbsp;<a href=\"$CONFIG[url_prefix]/location.php\" title=\"Home\" class=\"button\">Home</a>&nbsp;</td>";

// check if fleet menu applicable/ Insert FLEETS button
if($user_options['enable_fleets'] == 1) {
	echo "<td class=\"menu\">&nbsp;<a href=\"$CONFIG[url_prefix]/fleet_command.php?summary=1\" title=\"Fleets\" class=\"button\">Fleets</a>&nbsp;</td>";
	echo "<td class=\"menu\">&nbsp;<a href=\"$CONFIG[url_prefix]/mining.php?overview=1\" title=\"Mining\" class=\"button\">Mining</a>&nbsp;</td>";
}

// Check if Admin Menu to be displayed
if($user['login_id'] == 1 || $user['login_id'] == 5) {
	$adm_menu = "<td class=\"menu\">&nbsp;<a href=\"$CONFIG[url_prefix]/admincp/index.php\" title=\"Admin Menu\" class=\"button\">AdminCP</a>&nbsp;</td>";
	$bug_cmd_name = "Bugs";
} else {
	$adm_menu = "";
	$bug_cmd_name = "Report a Bug";
}
if($user['login_id'] == 5) {
	$dev_menu = "<td class=\"menu\">&nbsp;<a href=\"$CONFIG[url_prefix]/devcp/index.php\" title=\"Dev Menu\" class=\"button\">DevCP</a>&nbsp;</td>";
} else {
	$dev_menu = "";
}

// Insert other assorted MENU buttons

echo "<td class=menu>&nbsp;<a href=\"$CONFIG[url_prefix]/clan.php\" title=\"Clans\" class=button>Clans</a>&nbsp;</td>"
."<td class=menu>&nbsp;<a href=\"$CONFIG[url_prefix]/diary.php\" title=\"Diary\" class=button>Diary</a>&nbsp;</td>"
."<td class=menu>&nbsp;<a href=\"$CONFIG[url_prefix]/news_maint.php\" title=\"Maintenance News\" class=button>Maints</a>&nbsp;</td>"
."<td class=menu>&nbsp;<a href=\"$CONFIG[url_prefix]/player_stat.php\" title=\"Rankings\" class=button>Rankings</a>&nbsp;</td>"
."<td class=menu>&nbsp;<a href=\"$CONFIG[url_prefix]/options.php\" title=\"Options\" class=button>Options</a>&nbsp;</td>"
."<td class=menu>&nbsp;<a href=\"$CONFIG[url_prefix]/help.php\" target=_new title=\"Help\" class=button>Help</a>&nbsp;</td>"
."<td class=menu>&nbsp;<a href=\"http://webgames.servegame.com/bugtracker/login.php?username=anonymous&return=set_project.php%3Fref=bug_report_page.php%26project_id=1\" target=_blank title=\"Report Bug\" class=button>$bug_cmd_name</a>&nbsp;</td>"
."<td class=menu>&nbsp;<a href=\"$CONFIG[url_prefix]/logout.php?logout_single_game=1\" title=\"Logout\" class=button>Logout</a>&nbsp;</td>"
.$adm_menu
.$dev_menu;

// Close First Row Sub-Table
echo "</tr>"
."</table></td>"
."</tr>"

//Insert Second Row Sub-Table
."<tr><td valign=\"center\" width=\"100%\" colspan=\"5\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"><tr>";


// Set default table width (equal size columns for second row)
$table_wdth = "25%";


	// A check for new Ship Transfers will be added later
	$trnsfrs = "<a href=\"$CONFIG[url_prefix]/ship_transfer.php?buffer=1\" title=\"Check your Ship Transfer Buffer\" class=\"button\">Transfers</a>";
	// Check for new Messages
	db(__FILE__,__LINE__,"select count(m.message_id) from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' && m.sender_id = u.login_id");
	$counted = dbr();

	// Check for new Messages
	db(__FILE__,__LINE__,"select count(message_id) as totcount from ${db_name}_messages where login_id = '${user['login_id']}'");
	$counted = dbr();

	if(empty($counted) || $counted['totcount'] == 0){
		$mssg = "<a href=\"${CONFIG['url_prefix']}/mpage.php\" title=\"Read Messages\" class=\"button\">No Msg(s)</a> - <a href=\"${CONFIG['url_prefix']}/message.php\" title=\"Send Message\" class=\"button\">Send</a>&nbsp;";
	}else{
	        db(__FILE__,__LINE__,"select count(message_id) as newcount from ${db_name}_messages where timestamp > ${user['last_access_inbox']} and login_id = ${user['login_id']}");
	        $newcount = dbr();
	        if(!empty($newcount) && $newcount['newcount'] > 0)
	        {
	                $new = " <font color=ff0000>New(${newcount['newcount']})</font>";
	        }

		$mssg = "<a href=\"${CONFIG['url_prefix']}/mpage.php\" title=\"Read Messages\" class=\"button\"><b>${counted['totcount']}</b> Msg(s)$new</a> - <a href=\"${CONFIG['url_prefix']}/message.php\" title=\"Send Message\" class=\"button\">Send</a>&nbsp;";
	}

	// Check for new Forum Posts
	db(__FILE__,__LINE__,"select count(message_id) as new_messages from ${db_name}_game_forum where timestamp > '$user[last_access_forum]' && login_id != '$user[login_id]'");
	$messg_count_forum = dbr();
	unset($temp_forum_text);
	if($messg_count_forum['new_messages'] > 0){
		$temp_forum_text = " <b style=\"font-size: 10px; font-weight: bold\">($messg_count_forum[new_messages]</b> <a href=\"$CONFIG[url_prefix]/forum_game.php?read_back=-1\" title=\"Read New Posts\" class=\"button\">new</a><b style=\"font-size: 10px; font-weight: bold\">)</b>";
	} else {
	 	$temp_forum_text = "";
	}
	$frmp = "<a href=\"$CONFIG[url_prefix]/forum_game.php\" title=\"Enter Forum\" class=\"button\">Forum</a> $temp_forum_text";

	// Check for new Clan Forum Posts
	if($user['clan_id'] > 0)
	{
		db(__FILE__,__LINE__,"select count(message_id) as new_messages from ${db_name}_clan_forum where timestamp > '$user[last_access_clan_forum]' && clan_id = '$user[clan_id]' && login_id != '$user[login_id]'");
		$messg_count_clan_forum = dbr();
		if($messg_count_clan_forum['new_messages'] > 0)
		{
			 $temp_clan_forum_text_var = " <b style=\"font-size: 10px; font-weight: bold\">($messg_count_clan_forum[new_messages]</b> <a href=\"$CONFIG[url_prefix]/forum_clan.php?read_back=-1\" title=\"Read New Posts\" class=\"button\">new</a><b style=\"font-size: 10px; font-weight: bold\">)</b>";
		}
		$clfrm = "<a href=\"$CONFIG[url_prefix]/forum_clan.php\" title=\"Enter $name Forum\" class=\"button\"><font color=$user[clan_sym_color]>$user[clan_sym]</font> Forum</a>".$temp_clan_forum_text_var;
		$table_wdth = "20%";
	}
	else
	{
		$table_wdth = "25%";
	}

	// Check for News and Admin Announcements
	$nwst = "";
	db(__FILE__,__LINE__,"select count(news_id) from ${db_name}_news where login_id = -1 && timestamp > '$user[last_access_news]'");
	$newss = dbr();
	$nwst = "<a href=\"$CONFIG[url_prefix]/news.php\" title=\"Enter News\" class=\"button\">News</a>";
	if($newss['0'] > 0) {
		$nwst .= " <b style=\"font-size: 10px; font-weight: bold\">($newss[0]</b> <a href=\"$CONFIG[url_prefix]/news.php?show_admin_news=1\" title=\"View Game News\" class=\"button\">Admin</a><b style=\"font-size: 10px; font-weight: bold\">)</b>";
	}
	#echo "<br /><a href=news_maint.php>Maints</a>";


// Insert all post/messg/forum Menu Links for Second Row Menu
echo "
<td class=\"menu\" width=\"$table_wdth\" nowrap>
	$trnsfrs
</td>";

echo "
<td class=\"menu\" width=\"$table_wdth\" nowrap>
	$mssg
</td>";
echo "
<td class=\"menu\" width=\"$table_wdth\" nowrap>
	$frmp
</td>";
if(isset($clfrm))
{
	echo "
	<td class=\"menu\" width=\"$table_wdth\" nowrap>
		$clfrm
	</td>";
}
echo "
<td class=\"menu\" width=\"$table_wdth\" nowrap>
	$nwst
</td>";

// Close Second Row Sub-Table
echo "</tr></table></td></tr></table>";

///////////////////////////////////////////////
//                                           //
//          END OF MIDDLE MAIN MENU          //
//                                           //
///////////////////////////////////////////////

echo "</td>
<td width=\"20\">
</td><td width=\"202\" valign=\"top\" align=\"center\">";

///////////////////////////////////////////////
//                                           //
//          ADD TOP LEFT SITE LOGO           //
//                                           //
///////////////////////////////////////////////

// no content for far right panel yet!
// Any graphic added should be 200x33 Pixels maximum
// enclose all <img> tab attributes with a double quote using \" not " (i.e. php escape char for output quotes)
echo "";


///////////////////////////////////////////////
//                                           //
//          END OF HEADER OVERALL TABLE      //
//                                           //
///////////////////////////////////////////////

echo "</td></tr></table>";


?>
