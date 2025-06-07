<?php
/*
File:				hw_bank.php
Objective:			Script for using a bank like system. Loans will be added at a later date
Author:				Ikoda
Date Committed:		15 Dec 2005
*/
require_once("user.inc.php");

db2(__FILE__,__LINE__,"select * from ${db_name}_db_vars where name = 'bank'");
$bank = dbr2();
db3(__FILE__,__LINE__,"select * from ${db_name}_db_vars where name = 'intrest'");
$intrest = dbr3();
db2(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
$eras = dbr2();
$era = $eras['value'];
if($user['login_id'] != 1){
db4(__FILE__,__LINE__,"select * from races where race_ID = $user[race] and era = $era");
$home = dbr4();
$homeworld = $home['planet_name'];
} elseif($user['login_id'] == 1){
db4(__FILE__,__LINE__,"select * from races where race_ID = $user[location] and era = $era");
$home = dbr4();
$homeworld = $home['planet_name'];
}

if($user['location'] != $user['race'] && $user['login_id'] != 1){
	$return = "You appear rather lost.  Your not in your homeworld system.  <br>Try coming back when you are
	<br>Just to remind you, its SS #$user[race]";
} else {
	if($bank['value'] == 1){
		$return = "<b>Welcome to $home[bank]</b>";
		if($service == 1){
			db(__FILE__,__LINE__,"select * from ${db_name}_bank_account where login_id = $user[login_id]");
			$cash = dbr();
			$deposit = $cash['deposit'];
			$new_intrest = ($intrest['value'] / 100);
if($deposit >= 10000000){
			$dm_deposit = "There will be no further intrest because there is over 10000000 in your account";
} else {
			$dm_deposit = "At the next daily, it will change to ".(int)($deposit * (1.0 + $new_intrest))."";
}
			if($dep_ser == 1){
				if($deposit >= 5000000){
					$return = "<br><br>We apologize, but the account is at full amount allowed";
				} else {
				if($dep_user != 2){
					if($user['cash'] >= 5000000){
						$user_deposit = 5000000 - $deposit;
					} elseif($user['cash'] < $deposit){
						$user_deposit = $user['cash'];
					} elseif($user['cash'] >= $deposit && $user['cash'] < 5000000){
						$user_deposit = $user['cash'];
					} elseif($user['cash'] >= $deposit && $user['cash'] < 5000000){
						$user_deposit = 5000000 - $user['cash'];
					}
					$return .= "<br><br>How many credits would you like to deposit?";
					$return .= '<br><form name="hw_bank_deposit" action="'.$_SERVER['PHP_SELF'].'?service=1&dep_ser=1" method="post">';
					$return .= '<input type="hidden" name="dep_user" value="2" />';
					$return .= '<input type="text" name="new_deposit" value="'.$user_deposit.'" size="30" />';
					$return .= '<br /><input type="submit" value="Update Account" /></form>';
				} else {
					if($_POST['new_deposit'] < 0){
						$return .= "<br>Well, i dont know how you intend to give minus cash.<br>Scram, you yob.";
					}elseif($_POST['new_deposit'] > (5000000 - $deposit)){
						$return .= "<br>How do you intend to deposit that much? You can only add a maximum of";
						$return .= " ".(5000000 - $deposit)."";
						$return .= "<br>You'd go above the maximum amount if you credited your account with ".$_POST['new_deposit']."";
					} elseif($_POST['new_deposit'] > $user['cash']){
						$return .= "<br>How do you expect to deposite more credits than you actually have?  Stop trying to cheat :oP";
					} else {
						$new_deposit2 = (int)$_POST[new_deposit];
						dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash - '$new_deposit2' where login_id = '$user[login_id]'");
						dbn(__FILE__,__LINE__,"update ${db_name}_bank_account set deposit = deposit + '$new_deposit2' where login_id = '$user[login_id]'");
						$return .= "<br>Your ISA Account is now calculated at ";
						$return .= " ".$deposit + $new_deposit2."";
					}
					}
				}
					$rs = "<p><a href=hw_bank.php?service=1>Return to Deposits";
			} elseif($dep_ser == 2){
				if($wit_user != 2){
					$return .= "<br><br>How many credits would you like to withdraw?";
					$return .= '<br><form name="hw_bank_withdraw" action="'.$_SERVER['PHP_SELF'].'?service=1&dep_ser=2" method="post">';
					$return .= '<input type="hidden" name="wit_user" value="2" />';
					$return .= '<input type="text" name="new_withdraw" value="'.$deposit.'" size="30" />';
					$return .= '<br /><input type="submit" value="Update Account" /></form>';
				} else {
					if($_POST['new_withdraw'] < 0){
						$return .= "<br>Well, i dont know how you intend to withdraw minus cash.";
					}elseif($_POST['new_withdraw'] > ($deposit)){
						$return .= "<br>How do you intend to withdraw that much? You can only withdraw a maximum of";
						$return .= " ".($deposit)."";
						$return .= "<br>You'd go below the minimum amount if you debited your account of ".$_POST['new_withdraw']."";
					} else {
						$new_withdraw2 = (int)$_POST['new_withdraw'];
						dbn(__FILE__,__LINE__,"update ${db_name}_users set cash = cash + '$new_withdraw2' where login_id = '$user[login_id]'");
						dbn(__FILE__,__LINE__,"update ${db_name}_bank_account set deposit = deposit - '$new_withdraw2' where login_id = '$user[login_id]'");
						$return .= "<br>Your ISA Account is now calculated at ";
						$return .= " ".$deposit - $new_withdraw2."";
					}
					}
				$rs = "<p><a href=hw_bank.php?service=1>Return to Deposits";
			} else {
				$return .= "<br><br>Welcome to the ISA section of $home[bank]";
				$return .= "<br>Currently, the intrest rate is $intrest[value]% per day";
				$return .= "<br>A reminder that accounts DO have a limit of no more than 5 million";
				$return .= "<br>At the moment, you have <b>$deposit</b> in your ISA. ".$dm_deposit."";
				$return .= "<br><br>Please select what you would like to do:<br><a href=?service=1&dep_ser=1>Deposit Cash</a><br><a href=?service=1&dep_ser=2>Withdraw Cash</a>";
				$rs = "<p><a href=hw_bank.php>Return to $home[bank]";
			}

		} elseif($service == 2){
			$return .= "<br><br>We aplogize for the inconvenice, but currently loans are not avilable";
			$rs = "<p><a href=hw_bank.php>Return to $home[bank]";
		} else {
			$return .= "<br><br>We, here at $home[bank], pride ourselves at looking after your money, and offer you a great intrest rate";
			$return .= "<br>Unfortantly, due to technical issues, you currently cannot loan money off us.";
			$return .= "<br>However, please feel free to deposit your money with us in a Saving account";
			$return .= "<br><br>Please choose a service:<br><a href=?service=1>Savings Account</a><br>Take out a loan";
			$rs = "<p><a href=hw.php>Return to $homeworld</a>";

		}

	}
	elseif($bank['value'] == 0)
	{
		$return = "<b>Welcome to $home[bank]</b>";
		$return .= "<br><br>We apologize for the Inconvenience, but we are unfortantly closed for business";
		$return .= "<br>Please forward all complaints to the Admin.";
		$rs = "<p><a href=hw.php>Return to $homeworld</a>";
	}

}

print_page($home['bank'],$return);

?>