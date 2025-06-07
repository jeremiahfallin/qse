<?php
include_once("includes/nocache.inc.php");
/*
//
File:			options_passwd.php
Objective:		Code to manage all functions relating to user passwords (to be appended to options.php)
Version:		QS 2.2 Beta
Author:			Maugrim_The_Reaper (maugrimtr@hotmail.com)
Date Committed:	16 March 2004		Date Modified:	n/a

Copyright (c) 2003, 2004 by Pádraic Brady

This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
//
*/

require_once("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

// set the Return String $rs to diplay an exit link to the location page
$rs = "<p><br /><a href=\"location.php\">Return to Star System</a></p>";

// Page heading + Sub-Menu
$text = Create_PageTitleBlock("User Account Options",array("Change Password" => "options_passwd.php?changepass=1", "Game Options" => "ship_transfer.php?new=1", "Forum Options" => "ship_transfer.php?v_transfer=1"));



// Player chooses to change account password

if($_GET['changepass'] == 1)
{
	$text .= "
		<p>Enter a new password. Your password must have a minimum of 5 characters, and a maximum of 25 characters.</p>
		<div style=\"text-align: center;\">
			<form method=\"post\" action=\"options_passwd.php\" id=\"change_passwd\" name=\"change_passwd\">
			<input type=\"hidden\" name=\"changepass\" value=\"2\" />
			<table cellspacing=\"0\" cellpadding=\"0\" width=\"300\" align=\"center\">
				<tr>
					<td>Old Password:</td>
					<td><input type=\"password\" name=\"oldpass\" value=\"\" /><br /><br /></td>
				</tr>
				<tr>
					<td>New Password:</td>
					<td><input type=\"password\" name=\"newpass\" value=\"\" /><br /><br /></td>
				</tr>
				<tr>
					<td>Re-Type New Password:</td>
					<td><input type=\"password\" name=\"newpass2\" value=\"\" /><br /><br /></td>
				</tr>
				<tr>
					<td colspan=\"2\" style=\"text-align: center;\"><input type=\"submit\" value=\"Change Password\" /></td>
				</tr>
			</table>
			</form>
		</div>
	";
	print_page("Change Password",$text);
}


// new password has been submitted, so test validity

if($_POST['changepass'] == 2)
{
	if($user_perm['admin'] == 1)
	{
		db(__FILE__,__LINE__,"select admin_pw from se_games where db_name = '$db_name'");
		$a_pas_temp = dbr();
		$p_user['passwd'] = $a_pas_temp['admin_pw'];
	}
	if (isset($_POST['newpass']) && ($_POST['newpass'] == $_POST['newpass2']))
	{
		if(strlen($_POST['newpass']) < 5)
		{
		   $text = "<p>Passwords must be at least five letters.</p>";
		   $text .= "<p><a href=\"javascript:history.back()\">Back to Pass-Change Form</a></p>";
		}
		elseif($_POST['newpass'] == $_POST['oldpass'])
		{
		   $text = "<p>That's a good one, changing to your OLD password? Please try again...lol.</p>";
		   $text = "<p><a href=\"javascript:history.back()\">Back to Pass-Change Form</a></p>";
		}
		elseif($user['login_name'] == $_POST['newpass'])
		{
		   $text = "<p>What a good idea. Using your login name as the pass.<br />Use a different password.</p>";
		   $text .= "<p><a href=\"javascript:history.back()\">Back to Pass-Change Form</a></p>";
		}
		elseif (md5($_POST['oldpass']) == $p_user['passwd'])
		{
			$text = "<p>Password changed successfully</p>";
			if ($user['login_id'] == 1)
			{
				dbn(__FILE__,__LINE__,"update se_games set admin_pw='$_POST[newpass]' where db_name = '$db_name'");
				$text .= "<p>Admin password will remain after game is wiped</p>";
				$p_user['passwd'] = "$_POST[newpass]";
			}
			else
			{
				$encrypt_newpass = md5($_POST['newpass']);
				dbn(__FILE__,__LINE__,"update user_accounts set passwd='$encrypt_newpass' where login_id='$user[login_id]'");
				$p_user['passwd'] = "$_POST[newpass]";
			}
			insert_history($user['login_id'],"Password Changed");
		} else {
			$text .= "<p>The old password is not correct!</p>";
			$text .= "<p><a href=\"javascript:back()\">Go back</a></p>";
		}
	} else {
		$text .= "<p>Password mismatch!</p>";
		$text .= "<p><a href=\"javascript:back()\">Go back</a></p>";
	}
	print_page("Password Changed",$text);
}

if($_GET['lostpasswd'] == 1)
{
	$text .= "
		<p>Please enter your email address and username. The next step will ask you a short validation question to which you set the answer at sign-up. You will need to answer this correctly in order to reset your password.</p>
		<div style=\"text-align: center;\">
			<form method=\"post\" action=\"passwd_lost.php\" id=\"lost_passwd\" name=\"lost_passwd\">
			<input type=\"hidden\" name=\"lostpasswd\" value=\"2\" />
			<table cellspacing=\"0\" cellpadding=\"0\" width=\"300\" align=\"center\">
				<tr>
					<td>Username:</td>
					<td><input type=\"text\" name=\"l_name\" value=\"\" size=\"30\" /><br /><br /></td>
				</tr>
				<tr>
					<td>Email Address:</td>
					<td><input type=\"text\" name=\"l_email\" value=\"\" size=\"30\" /><br /><br /></td>
				</tr>
				<tr>
					<td colspan=\"2\" style=\"text-align: center;\"><input type=\"submit\" value=\"Submit\" /></td>
				</tr>
			</table>
			</form>
		</div>
	";
	print_page("Reset Password",$text);
}

$_POST['l_name']
$_POST['l_email']

if($_POST['lostpasswd'] == 2)
{
	db(__FILE__,__LINE__,"select hint_question from user_accounts where login_name = '$_POST[l_name]' and email = '$_POST[l_email]'");
	$val_qs = dbr();
	if(empty($val_qs))
	{
		print_page("Error",$text."<p>Either the Username you typed does not exist or has been mistyped, or the email address entered is not valid for that user account.</p>");
	}
	if(empty($val_qs[hint_question]) || $val_qs[hint_question] == "")
	{
		print_page("Error",$text."<p>Sorry, but the validation question for your account could not be found. We are unable to validate your identity.</p>");
	}
	$text .= "
		<p>Please answer the validation question. You will then be sent an email with instructions on resetting your password. Please note that if you did not give a valid email address at sign-up, this facility will not work! We cannot send email to false addresses (or the email may be sent, but to the normal user of that address). Also note that failure to answer the question correctly within 3 tries will disable this function for 12,849 hours. </p>
		<div style=\"text-align: center;\">
			<form method=\"post\" action=\"passwd_lost.php\" id=\"lost_passwd\" name=\"lost_passwd\">
			<input type=\"hidden\" name=\"lostpasswd\" value=\"3\" />
			<input type=\"hidden\" name=\"l_name\" value=\"".$_POST['l_name']."\" />
			<input type=\"hidden\" name=\"l_email\" value=\"".$_POST['l_email']."\" />
			<table cellspacing=\"0\" cellpadding=\"0\" width=\"300\" align=\"center\">
				<tr>
					<td>Validation Question:<br />
					<b>$val_qs[hint_question]</b><br /><br /></td>
				</tr>
				<tr>
					<td>Please type your answer:<br />
					<input type=\"text\" name=\"hint_ans\" value=\"\" size=\"30\" /><br /><br /></td>
				</tr>
				<tr>
					<td colspan=\"2\" style=\"text-align: center;\"><input type=\"submit\" value=\"Submit\" /></td>
				</tr>
			</table>
			</form>
		</div>
	";
	print_page("Reset Password",$text);
}


if($_POST['lostpasswd'] == 3)
{
	db(__FILE__,__LINE__,"select hint_answer from user_accounts where login_name = '$_POST[l_name]' and email = '$_POST[l_email]'");
	$val_ans = dbr();
	if(empty($val_ans) || $val_ans['hint_answer'] == "")
	{
		print_page("Error",$text."<p>Sorry, but the answer stored for your account does not appear to be in a valid form. We cannot validate your identity. Please contact the Administrator of this server and request a manual change of password. The Administrator reserves the right to refuse all such requests should they see fit.</p>");
	}
	if(empty($val_ans) || $val_ans['hint_answer'] == "")
	{
		print_page("Error",$text."<p>Sorry, but the answer stored for your account does not appear to be in a valid form. We cannot validate your identity. Please contact the Administrator of this server and request a manual change of password. The Administrator reserves the right to refuse all such requests should they see fit.</p>");
	}
	if($val_ans['hint_answer'] == $_POST['hint_ans'])
	{
		// generate auth number
		srand((double)microtime()*1000000);
		$auth = abs(rand(0,getrandmax()));
		$auth = md5($auth);
		$subject = "$server_name :: Quantum Star SE Online Space Strategy Game";
		$from = "$admin_mail";
		$to = $_POST['l_email'];
		$email = "
			This email has been sent because a user with an IP address of $REMOTE_ADDR requested a password reset for the Quantum Star SE user account associated with this email address on the \"$server_name\" server.

			If you believe this email was sent in error than please disregard, or email the Administrator of this game that this email was sent without you requesting it (it may be indicative of someone attempting to gain access to your account illegally).

			To reset your password please visit the url below and enter the provided Authorisation Code.

			Your Authorisation Code:
			$auth

			The URL to visit in order reset your account password:
			$url_prefix/options_passwd.php?lostpasswd=4

			Regards from the Quantum Star SE Administration.
		";
		mail(
			"$to",
			"the subject",
			$email,
			"From: {$from}\r\n" . "Reply-To: {$from}\r\n" . "X-Mailer: PHP/" phpversion()
		);
		print_page("Success",$text."<p>An email containing instructions on resetting your account password has been forwarded to the email address stored with your account details. Please check your email account. The email may take some time to arrive.</p>");
	}
	else
	{
		print_page("Error",$text."<p>Sorry, but the answer stored for your account does not appear to match the answer which you have provided. We are therefore unable to validate your identity.</p>");
	}
	print_page("Reset Password",$text);
}


// Catch all function for those brave souls who try their hands at Url doping.
$error_str = $text."<p>You have apparently used a false url - please use the menu links to access this function. And remember that Url editing is considered a form of cheating!</p>";
print_page("Url Error",$error_str);

?>