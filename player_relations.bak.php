<?php

/*
// Name: player_relations.php
// By: Maugrim The Reaper (bpaddy2@yahoo.com)
// Purpose: Management of in-game player relations
// Date Completed: 16/08/2002
// Version: 0.5
// Prior: 0.3
*/

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

$filename = 'player_relations.php';

$rel_link = "<p><a href=player_relations.php?relations=1>Back to Player Relations</a>";

// saves all changes to database from all in-file forms
if($save_vars) {
	if($user[clan_id] != 0){
		db(__FILE__,__LINE__,"select * from ${db_name}_clan_relations where clan_id = '$user[clan_id]'");
		$all_rel = dbr(); #select all clan designations
		db(__FILE__,__LINE__,"select leader_id from ${db_name}_clans where clan_id ='$user[clan_id]'");
		$clan_lead = dbr();
	}else{
		db(__FILE__,__LINE__,"select * from ${db_name}_clan_relations where v_clan_id = '$user[v_clan_id]'");
		$all_rel = dbr(); #select all indep designations
	}

	if(($all_rel[clan_permission] != 1) && ($clan_lead[leader_id] != $user[login_id])){
		$out .= "Your Clan Leader has made the decision not to allow any Clan Member to alter the Player Relation Settings. If you wish to have a setting to any relation changed, you must petition your Clan Leader with the request.";
		print_page("Permission Denied!",$out);
	}else{

		while (list($var, $value) = each($_POST)) {
			if($var == 'save_vars') {
				continue;
			}

			if($user[clan_id] != 0){
				dbn(__FILE__,__LINE__,"update ${db_name}_clan_relations set ${var} = '$value' where clan_id = '$user[clan_id]'");
			}else{
				dbn(__FILE__,__LINE__,"update ${db_name}_clan_relations set ${var} = '$value' where v_clan_id = '$user[v_clan_id]'");
			}
		}
	}

if($save_vars == 1){
	$new_link = "<br /><p><a href=player_relations.php?relations=1&designate=1>View New Settings</a>";
}elseif($save_vars == 2){
	$new_link = "<br /><p><a href=player_relations.php?relations=1&designate=2>View New Settings</a>";
}else{
	$new_link = "<br /><p><a href=player_relations.php?change_perm=1>View New Permission Setting</a>";
}

$string .= "Your settings have been successfully changed.";
$string .= $new_link;
$string .= $rel_link;
print_page("Designations Changed",$string);
}

if(isset($activate_nap)){
	dbn(__FILE__,__LINE__,"update ${db_name}_naps set activated = 1 where nap_id = '$nap_id'");
	print_page("Activated NAP","You have accepted the proposed NAP which will take effect from 12:00am Midnight of the proposed start date. If the start date has already passed the NAP will take effect as from 12:00am tonight.");
}


if(isset($refuse_nap)){
	db(__FILE__,__LINE__,"select propose_id from ${db_name}_naps where nap_id = '$nap_id'");
	$proposer = dbr();
	if($proposer[propose_id] < 100000){
		db(__FILE__,__LINE__,"select leader_id from ${db_name}_clans where clan_id = '$proposer[propose_id]'");
		$leader_id = dbr();
		$targ_id = $leader_id[leader_id];
	}else{
		db(__FILE__,__LINE__,"select login_id from ${db_name}_users where v_clan_id = '$proposer[propose_id]'");
		$indep_id = dbr();
		$targ_id = $indep_id[login_id];
	}
	if($user[clan_id] != 0){
		db2(__FILE__,__LINE__,"select clan_name from ${db_name}_clans where leader_id = '$user[login_id]'");
		$clan_inf = dbr2();
		$from_name = $clan_inf[clan_name];
	}else{
		$from_name = $user[login_name];
	}
	send_message($targ_id, "<b class=b1>$from_name</b> has refused your offer of a <b>Non-Aggression Pact</b>. You may try again at a later date if you wish");
	dbn(__FILE__,__LINE__,"delete from ${db_name}_naps where nap_id = '$nap_id'");
}

if(isset($relations)) {

	//Clan Designations
	if($designate == 1){
		db2(__FILE__,__LINE__,"select * from ${db_name}_clans order by clan_name");
		$clan = dbr2(); #array of clan player info

		if(!$clan){
			$out .= "There are no other clans".$rel_link;
		} else {

			$out .= "You have reached the <b>Clan Relations</b> page.";
			$out .= "<br /><p>From here you may manually alter your stance against individual clans.";
			#$out .= "<br /><p>Remember that 0=Neutral, 1=Enemy, 2=NAPed";
			$out .= $rel_link;


			if($user[clan_id] != 0){
				db(__FILE__,__LINE__,"select * from ${db_name}_clan_relations where clan_id = '$user[clan_id]'");
				$all_rel = dbr(); #select all clan designations
			}else{
				db(__FILE__,__LINE__,"select * from ${db_name}_clan_relations where v_clan_id = '$user[v_clan_id]' && clan_id = 0");
				$all_rel = dbr(); #select all indep designations
			}

			#start form
			$out .= "<form action='player_relations.php' name=get_rel_form method=post>";
			$out .= "<input type='hidden' name='save_vars' value='1'>";
			$out .= "<input type='submit' value='Submit Vars'>";

			while ($clan){
				if ($clan[clan_id] == $user[clan_id]){
					next;
				}else{

					$clan_id = $clan[clan_id];
					$designation = find_designation($clan_id);
					$fname = "x_".$clan_id;
					if($all_rel[$fname] == 0){$chn = " checked";
					}elseif($all_rel[$fname] == 1){$che = " checked";
					}else{$chnap = " checked";}

					#part of array row including form action for var
					$inf .= make_row(array("<a href=clan.php?clan_info=1&target=$clan[clan_id]><b class=b1>$clan[clan_name]</b></a>","<div align=\"center\"><b><font color=$clan[sym_color]>$clan[symbol]</font></b></div>","<div align=\"center\">$clan[members]</div>","$clan[clan_score]","<div align=\"center\">$designation</div>","<input type='radio' name='$fname' value=0$chn>","<input type='radio' name='$fname' value=1$che>","<input type='radio' name='$fname' value=2$chnap>"));

					unset($chn,$che,$chnap);
				}
			$clan = dbr2();
			}#end while loop

			$out .= "<p><b>Current Clan Relations:</b><br /><p>";
			$out .= make_table(array("Clan Name","Symbol","Members","Clan Score","Current Relation","Neutral","Enemy","NAPed"));
			$out .= stripslashes($inf);
			$out .= "</table>";

			$out .= "<p><input type='submit' value='Submit Vars'>";
			$out .= "<br /></form>";
			#end form

		}

		print_page("Clan & Player Relation Designations",$out);

	}#end $designate=1 if stat

	//Indeps Designations
	if($designate == 2){

		db2(__FILE__,__LINE__,"select login_id, v_clan_id, login_name, score from ${db_name}_users where clan_id = 0 && login_id != 1 order by login_name");
		$indep = dbr2(); #array of non-clan player info

		if(!$indep){
			$out = "There are no independent players who you may form a relationship with.".$rel_link;
		} else {

			$out .= "You have reached the <b>Independent Player Relations</b> page.";
			$out .= "<br /><p>From here you may manually alter your stance against individual players.";
			#$out .= "<br /><p>Remember that 0=Neutral, 1=Enemy, 2=NAPed";
			$out .= $rel_link;

			if($user[clan_id] != 0){
				db(__FILE__,__LINE__,"select * from ${db_name}_clan_relations where clan_id = '$user[clan_id]'");
				$all_rel = dbr(); #select all clan designations
			}else{
				db(__FILE__,__LINE__,"select * from ${db_name}_clan_relations where v_clan_id = '$user[v_clan_id]'");
				$all_rel = dbr(); #select all indep designations
			}

			#start form
			$out .= "<form action='player_relations.php' name=get_rel_form method=post>";
			$out .= "<input type='hidden' name='save_vars' value='2'>";
			$out .= "<input type='submit' value='Submit Vars'>";

			while ($indep){
				if ($indep[v_clan_id] == $user[v_clan_id]){
					next;
				}else{
					$v_clan_id = $indep[v_clan_id];
					$designation = find_designation($v_clan_id);
					$fname = "x_".$v_clan_id;
					if($all_rel[$fname] == 0){$chn = " checked";
					}elseif($all_rel[$fname] == 1){$che = " checked";
					}else{$chnap = " checked";}

					#part of array row including form action for var
					$ind .= make_row(array("<a href=player_info.php?target=$indep[login_id]><b class=b1>$indep[login_name]</b></a>","$indep[score]","<div align=\"center\">$designation</div>","<input type='radio' name='$fname' value=0$chn>","<input type='radio' name='$fname' value=1$che>","<input type='radio' name='$fname' value=2$chnap>"));

					unset($chn,$che,$chnap);
				}
			$indep = dbr2();
			}#end while loop

			$out .= "<p><b>Current Independent Player Relations:</b><br /><p>";
			$out .= make_table(array("Player Name","Player Score","Current Relation","Neutral","Enemy","NAPed"));
			$out .= stripslashes($ind);
			$out .= "</table>";

			$out .= "<p><input type='submit' value='Submit Vars'>";
			$out .= "<br /></form>";
			#end form
		}

		print_page("Independent Player Relations",$out);

	}#end $designate=2 if stat

if($user[clan_id] != 0){
	db(__FILE__,__LINE__,"select * from ${db_name}_clan_relations where clan_id = '$user[clan_id]'");
	$all_rel = dbr(); #select all clan designations
}else{
	db(__FILE__,__LINE__,"select * from ${db_name}_clan_relations where v_clan_id = '$user[v_clan_id]'");
	$all_rel = dbr(); #select all indep designations
}

if($all_rel[clan_permission] != 1){
	$perm_status = "<font color=FF0000>Permission Denied</font>";
}else{
	$perm_status = "<font color=00FF00>Permission Granted</font>";
}

$string .= "Welcome to the <b>Player Relations</b> menu!<br /><br />From here you can manage your relationship with all clans and independent players.";
$string .= "<br /><p>Please choose whether you would like to use independent, or clan relations:.";
if($user[clan_id] != 0){
	$string .= "<br /><p>Please note that now you have joined a clan, the Clan Leader may choose not to allow members to alter these settings. For those now members of a Clan, the current status is set as: $perm_status. Either way you may view the current settings by choosing one of the links below.";
}
$string .= "<br /><p><a href=player_relations.php?relations=1&designate=1>Clan Relations</a>";
$string .= "<br /><a href=player_relations.php?relations=1&designate=2>Independent Player Relations</a>";

if($user[clan_id] != 0){
	db(__FILE__,__LINE__,"select leader_id from ${db_name}_clans where clan_id = '$user[clan_id]'");
	$leader_check = dbr();
	if($leader_check[leader_id] == $user[login_id]){
		$string .= "<br /><a href=player_relations.php?change_perm=1>Change Clan Permission to Alter Settings</a>";
	}
}

print_page("Player Relations Relations",$string);

}#end isset $relations if stat

// resets clan permission from leader to change any relations settings
if(isset($change_perm)){
	$out .= "This section is where you can change the variable allowing your Clan's members the ability to change any clan relation settings. The default is to allow this since in many cases the Clan Leader may not be online when an event that justifies changing your Clan's relation to another Clan or Independent Player occurs and allowing a member to change the settings may prove helpful to all members. However it is your perogitive to disable this as Clan Leader and retain full authority over all relation changes. Below is the current setting. The default is 1, i.e. Members may change relation settings. Changing this to 0 will disable members' access.";
	$out .= "<form action='player_relations.php' name=get_perm_form method=post>";
	$out .= "<input type='hidden' name='save_vars' value='3'>";

	db(__FILE__,__LINE__,"select * from ${db_name}_clan_relations where clan_id = '$user[clan_id]'");
	$all_rel = dbr(); #select all clan designations
	$fname = "clan_permission";
	$fvalue = $all_rel[clan_permission];

	if($fvalue == 1){
		$current = "<font color=00FF00>Permission Granted</font>";
	}else{
		$current = "<font color=FF0000>Permission Denied</font>";
	}

	$inc .= make_row(array("$current","<input type='text' name='$fname' value='$fvalue' size='8'>"));

	$out .= make_table(array("Current Setting","Change"));
	$out .= stripslashes($inc);
	$out .= "</table>";
	$out .= "<p><input type='submit' value='Submit Vars'>";
	$out .= "<br /></form>";
	#end form

	print_page("Permission Setting",$out);
} #end isset change_perm

$out .= "You are missing several variables required to enter player relations, please return to the Star System and choose a valid option.";
print_page("Player Relations Error",$out);

?>