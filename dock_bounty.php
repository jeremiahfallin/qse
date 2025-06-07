<?php
require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

sudden_death_check($user);
$rs = "<p><a href=dock_bounty.php>Return to the Bounty Store</a>";
$rs = "<br /><a href=port.php>Return to Starport</a>";


db(__FILE__,__LINE__,"select * from ${db_name}_ports where location = '$user[location]'");
$ports = dbr();
if(!$ports){
$rs = "<p><a href=dock_bounty.php>Return to the Bounty Store</a>";
$rs = "<br /><a href=port.php>Return to Starport</a>";
	print_page("Error","There is no Bounty Store at this location.");
}

$ADODB_FETCH_MODE = 2;
$commision = 7;

if($user[location] == $ports[location]) {
	if(isset($amount)) {
		if($amount < 25) {
		$initial = $amount;
		$amount = round(($amount /100) *$commision) + $amount +1;
		} else {
		$initial = $amount;
		$amount = round(($amount /100) *$commision) + $amount;
		}
		db(__FILE__,__LINE__,"select clan_id from ${db_name}_users where login_id = '$target'");
		$todo = dbr();
		settype($amount, "integer");
		if($amount > $user[cash]) {
			 print_page("Bounty","You do not have enough money to place that bounty.");
		} elseif($target == $user[login_id]) {
			print_page("Bounty","You may not place a bounty on yourself.");
		} elseif($todo[clan_id] == $user[clan_id] && $user[clan_id] > 0) {
			print_page("Bounty","You may not place a bounty on a clan-mate.");
		} elseif($target == 1) {
			print_page("Bounty","You may not place a bounty on the Admin.");
		} elseif($user[login_id] == 1) {
			print_page("Bounty","Admin's may NOT place bounties.");
		} elseif($user[turns_run] < $turns_before_attack) {
			print_page("Bounty","You may not place a bounty during the first <b>$turns_before_attack</b> turns of your accounts' excistance. This is because placing a bounty is a form of attack.");
		} elseif($amount < 0) {
			print_page("Bounty","Negative sums can not be placed for bounties.");
		} elseif(!$initial) {
			print_page("Bounty","You didn't state an amount to place on $targets head.");
		} else {
			dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = bounty + '$initial' where login_id = '$target'");
			take_cash($amount);
			db(__FILE__,__LINE__,"select bounty,login_name from ${db_name}_users where login_id = '$target'");
			$returned = dbr();
			dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'The Bounty Store','$target','$target','Someone has put <b>$initial</b> on your head, making your bounty <b>$returned[0]</b> Credits.')");
			if ($returned[0] > $amount) {
				$text .= "You have added <b>$initial</b> Credits to <b class=b1>$returned[1]'s</b> bounty, making the present bounty <b>$returned[0]</b>. You were charged <b>$amount</b> Credit(s) for the transaction.<p>";
			} else {
				$text .= "You have placed <b>$initial</b> Credits on <b class=b1>$returned[1]'s</b> head. You were charged <b>$amount</b> Credit(s) for the transaction.<p>";
			}
			if($bount_mess) {
				if($user[turns] < 1) {
					$text .= "You do not have enough turns to add the message, but rest assured, the bounty has been added non-the-less.";
				} else {
				charge_turns(1);
				dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'The Bounty Store','$target','$target','The bounty also came with a message:<p> $bount_mess')");
				$text .= "<br />You have also been charged 1 turn for the additional message.";
				}
			}
		}
	}


	//allow user to pay off bounty.
	if($payoff < 0) {
		if($user[cash] == 0) {
			print_page("Bounty","You have no money. how do you expect to pay off a bounty?");
		} else {

		db(__FILE__,__LINE__,"select login_name,login_id,bounty from ${db_name}_users where login_id = $user[login_id] || (clan_id = '$user[clan_id]' && clan_id > '0') && bounty > '0' && login_id > 5 order by login_name");
		$list_em = dbr();

		while($list_em) {
		$bount = round(($list_em[bounty] / 100) * $commision);
		$bount += $list_em[bounty];
			if($bount > 0) {
				$o_text_t .= "<option value=$list_em[login_id]>$list_em[login_name] - $bount";
			}
			$list_em = dbr();
		}

		if(empty($o_text_t)){
			$text .= "There is no-one you may pay a bounty off for.<br />It is only possible to pay off a clan mates bounty.";
		} else {
			$text .= "<input action=bounty.php>";
			$text .= "Select Player whose bounty you wish to pay off.<br />You may only pay off a fellow clan mates bounty.<br />You will pay an extra <b>$commision</b>% on top of the original bounty if you pay it off.<p>";
			$text .= "You <b class=b1>must</b> pay off the whole bounty at once.<p>";
			$text .= "<select name=payoff>";
			$text .= $o_text_t;
			$text .= "</select>";
			$text .= "<p><input type=submit value=Submit></form><p>";
		}
		print_page("Bounty Payoff",$text);
		}
	} elseif($payoff >0) {
		db(__FILE__,__LINE__,"select bounty,login_name,clan_id,outlaw from ${db_name}_users where login_id = '$payoff'");
		$topay = dbr();
		$bount = round(($topay[bounty] / 100) * $commision);
		$bount += $topay[bounty];
		if($topay[outlaw] != 0) {
			print_page("Bounty","Under Sol Authority Ruling Alpha-Z18, it is illegal to payoff an <b><font color=lime>Outlaw</font></b>'s bounty! I am also obliged to inform the Authority of this...so just get lost. Right?");
		}elseif($user[cash] < $bount) {
			print_page("Bounty","You do not have enough money. You require <b>$bount</b> to pay this bounty off.");
		}elseif($user[login_id] ==1) {// change back to 1
			print_page("Bounty","The admin may have nothing to do with bounties.");
		} elseif($sure != 'yes') {
		  get_var('Bounty Payoff','bounty.php',"Are you sure you want to spend <b>$bount</b> credits to get rid of the bounty on <b class=b1>$topay[login_name]</b>?",'sure','yes');
		}elseif($payoff == $user[login_id] || ($user[clan_id] == $topay[clan_id] && $user[clan_id] >= 1)) {
			take_cash($bount);
			dbn(__FILE__,__LINE__,"update ${db_name}_users set bounty = 0 where login_id = '$payoff'");
			$text .= "You have paid off the bounty on <b class=b1>$topay[login_name]</b>, at a cost of <b>$bount</b> Credits.<p>";
		} else {
			print_page("Bounty","You may not pay-off a bounty on anyone, other than yourself, or a clan-mate.");
		}

	}

	if(!isset($place)) {
		if(!isset($amount)) {
			if (!$text) {
				$text = "Welcome to <b>The Bounty Store</b>.";
				$text .= "<br />If you would like to add to someones 'Benevolence Fund' (<b>Bounty</b>), then please click the link below.";
				}
			}
			if ($user[login_id] != 1){// back to 1
				$text .= "<p><a href=dock_bounty.php?place=1>Place a bounty</a>";
				$text .= "<br /><a href=dock_bounty.php?payoff=-1>Payoff a bounty<p></a>";
			}
	} elseif($user[login_id] == 1) {// change back to 1
		print_page("Bounty","Admin's may NOT place bounties.");
	} elseif($user[turns_run] < $turns_before_attack) {
		print_page("Bounty","You may not place a bounty during the first <b>$turns_before_attack</b> turns of your accounts' excistance. This is because placing a bounty is a form of attack.");
		} else {
			if ($user[clan_id] > 0) {
				db(__FILE__,__LINE__,"select login_name,login_id from ${db_name}_users where ship_id != 1 && login_id != 1 && login_id != $user[login_id] && clan_id != $user[clan_id] && ((login_id != 3 && login_id != 2)|| joined_game = 1) order by login_name");
			} else {
				db(__FILE__,__LINE__,"select login_name,login_id from ${db_name}_users where ship_id != 1 && login_id != 1 && login_id != $user[login_id] && login_id > 5 order by login_name");
			}
		$list_em = dbr();
		$text .= "<input action=bounty.php method=post name=bounty_form>";
		$text .= "Select Player to put bounty on: <br />";
		$text .= "<select name=target>";

		while($list_em) {
			$text .= "<option value=$list_em[login_id]>$list_em[login_name]";
			$list_em = dbr();
		}

		$text .= "</select>";
		$text .= "<p>Enter amount of bounty to place on Player.<br /> Note: You will get charged a <b>$commision</b>% commision for the transaction :<br />";
		$text .= "<input type=text name=amount size=10>";
		$text .= "<p>If you like you can also attach a anonymous message with the bounty. This service costs just one turn.<br />";
		$text .= "<textarea name=bount_mess value='$var_bount' cols=50 rows=20 wrap=soft></textarea>";
		$text .= "<input type=hidden name=rs value=".htmlentities($rs).">";

		$text .= "<p><input type=submit value=Submit></form><p>";
	}
	db(__FILE__,__LINE__,"select login_name,fighters_killed,ships_killed,bounty,outlaw from ${db_name}_users where ship_id != 1 && login_id != 1 && bounty >= 1 order by bounty desc,login_name");

	$player = dbr();
	if($player) {
		$text .= make_table(array("<div align=\"center\">Name</div>","<div align=\"center\">Fighters<br />Killed</div>","<div align=\"center\">Kills</div>","<div align=\"center\">Bounty</div>","<div align=\"center\">Status</div>"));
		while($player) {
		  $dis_name = print_name($player);
		  $player[login_name] = $dis_name;
		  if($player[outlaw] > 0) {
		  	$player[outlaw] = "<font color=lime>Outlawed</font>";
		  }else {
		  	$player[outlaw] = "";
		  }
		  $text .= make_hash_row($player);

		  $player = dbr();
		}
		$text .= "</table><br />";
	}
	$rs = "<br /><a href=help.php?bounty=1 target=_blank>Get help on bounties</a>";
	$rs .= "<br /><a href=port.php>Return to Starport</a>";
print_page('Bounties',$text);
}
?>