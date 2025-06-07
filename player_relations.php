<?php
require("user.inc.php");
if($user[clan_id] == 0)
	{
	print_page("join clan", "you need to join a clan to have player relations...");
	}

function create_relation($clan)
	{
	GLOBAL $user, $db_name;
	db(__FILE__,__LINE__,"select v_clan_id from ${db_name}_clan_relations where clan_id = $user[clan_id] and v_clan_id = $clan");$check=dbr();
	if($check[0] != $clan)
		{
		dbn(__FILE__,__LINE__,"insert into ${db_name}_clan_relations (clan_id, v_clan_id, nap_agree) VALUES ($user[clan_id], $clan, 2)");
		}

        db(__FILE__,__LINE__,"select v_clan_id from ${db_name}_clan_relations where v_clan_id = $user[clan_id] and clan_id = $clan");$check=dbr();
        if($check[0] != $clan)
                {
                dbn(__FILE__,__LINE__,"insert into ${db_name}_clan_relations (clan_id, v_clan_id, nap_agree) VALUES ($clan, $user[clan_id],2)");
                }
	}

function delete_relation($clan)
	{
	GLOBAL $user, $db_name;
	dbn(__FILE__,__LINE__,"delete from ${db_name}_clan_relations where clan_id = $user[clan_id] and v_clan_id = $clan");
	dbn(__FILE__,__LINE__,"delete from ${db_name}_clan_relations where clan_id = $clan and v_clan_id = $user[clan_id]");
	}

function change_relation($clan, $setting)
	{
	GLOBAL $user, $db_name;
	dbn(__FILE__,__LINE__,"update ${db_name}_clan_relations set nap_agree = '$setting' where clan_id = $user[clan_id] and v_clan_id = $clan");
	}

function get_relation($clan)
	{
	GLOBAL $user, $db_name;
	db(__FILE__,__LINE__,"select nap_agree from ${db_name}_clan_relations where clan_id = $user[clan_id] and v_clan_id = $clan");
	return dbr();
	}

function get_all_relations()
	{
	GLOBAL $db_name, $user;
	db(__FILE__,__LINE__,"select * from ${db_name}_clan_relations where clan_id = '$user[clan_id]'");
	while($rel = dbr())
		{
		db2(__FILE__,__LINE__,"select clan_name from ${db_name}_clans where clan_id = $rel[v_clan_id]");
		$clan_name = dbr2();
		$returner[] = array("name" => $clan_name[0], "id" => $rel['v_clan_id'], "nap" => $rel['nap_agree']);
		}
	return $returner;
	}

if(isset($relations))
	{
	if($user[clan_id] != 0)
		{
		if(isset($deleterel))
		        {
		        delete_relation($deleterel);
		        }

		if(isset($newrel))
		        {
		        if($_POST['clan'] != "blank")
		                {
		                create_relation($_POST['clan']);
		                if(!empty($_POST['nrel']))
		                        {
		                        change_relation($_POST['clan'], $_POST['nrel']);
		                        }
		                }
		        }
		if(isset($reledit))
		        {
		        foreach($_POST['clan'] as $clanid=>$napvalue)
		                {
		                change_relation($clanid, $napvalue);
		                }
		        }

		$string = "Welcome to the clan relations page, where you can create/delete/edit your relations twords other clans.";

		$string .= make_table(array("Clan", "Neutral", "Alliance", "Enemies", "Change", "Agreement"));

		$new_clan_box = "<select type=select name=clan>";
		$new_clan_box .= "<option value=blank>---select-clan---</option>";

		db(__FILE__,__LINE__,"select * from ${db_name}_clans where clan_id != $user[clan_id]");
		while($reler = dbr())
			{
			db2(__FILE__,__LINE__,"select v_clan_id from ${db_name}_clan_relations where clan_id = $user[clan_id] and v_clan_id = $reler[clan_id]");$check=dbr2();
			if($check[0] != $reler[clan_id])
		                {
				$new_clan_box .= "<option value=$reler[clan_id]>$reler[clan_name]</option>";
				}
			}

		$new_clan_box .= "</select>";
			$string .= make_row(array("<form name=new_relation method=POST action=${page}?relations=1&clans=1&newrel=1>$new_clan_box", "<input type=radio name=nrel value=2>", "<input type=radio name=nrel value=3>", "<input type=radio name=nrel value=1>", "<input type=submit value=\"Create New Relation\"></form><form name=orels method=POST action=${page}?clans=1&relations=1&reledit=1>",""));

		$rels = get_all_relations();
		foreach($rels as $rel)
			{
			if($rel[nap] == 3)
				{
				$nap1 = "checked";
				$nap2 = NULL;
				$nap3 = NULL;
				}
			elseif($rel[nap] == 2)
				{
				$nap1 = NULL;
				$nap2 = "checked";
				$nap3 = NULL;
				}
			elseif($rel[nap] == 1)
				{
				$nap1 = NULL;
				$nap2 = NULL;
				$nap3 = "checked";
				}
			$otherclan = get_relation($rel[id]);
			db(__FILE__,__LINE__,"select nap_agree from ${db_name}_clan_relations where clan_id = $rel[id] and v_clan_id = $user[clan_id]");
			$thisclan = dbr();
//                      $rel = "<b><<font color=#FF0000>E</font>></b>";
//                      $rel = "<b><<font color=#0000FF>N</font>></b>";
//                      $rel = "<b><<font color=#FFFF00>A</font>></b>";

			if($otherclan[nap_agree] == 3 and $thisclan[nap_agree] == 3)
				{
				$agreement = "<b><<font color=#FFFF00>A</font>></b>";
				}
                        else if($otherclan[nap_agree] == 1 or $thisclan[nap_agree] == 1)
                                {
				$agreement = "<b><<font color=#FF0000>E</font>></b>";
                                }
                        else if($otherclan[nap_agree] == 3 and $thisclan[nap_agree] == 2)
                                {
                                $agreement = "<b><<font color=#0000FF>Request Pending</font>></b>";
                                }
			else if($otherclan[nap_agree] == 2 and $thisclan[nap_agree] == 3)
				{
				$agreement = "<b><<font color=#0000FF>Request Alliance</font>></b>";
				}
			else if($otherclan[nap_agree] == 2 and $thisclan[nap_agree] == 2)
				{
				$agreement = "<b><<font color=#0000FF>N</font>></b>";
				}
			else
				{
				$agreement = "None";
				}
			$string .= make_row(array($rel[name], "<input type=radio name=clan[$rel[id]] value=2 $nap2>", "<input type=radio name=clan[$rel[id]] value=3 $nap1>", "<input type=radio name=clan[$rel[id]] value=1 $nap3>", "<a href=${page}?deleterel=$rel[id]&relations=1&clans=1>Delete Relation</a>",$agreement));
			}
		$string .= "<tr class=tr_bg_alt><td colspan=6 align=right><input type=submit value=change></form></td></tr>";
		$string .= "</table>";
		$string .= "<a href=${page}?relations=1>Reload Clan Relations</a>";
		print_page("", $string);
		}
	}
?>
