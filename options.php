<?php

include_once("includes/nocache.inc.php");

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

$filename = 'options.php';
$error_str = "";

// change player options
if(isset($player_op) && $player_op == 1){
	$error_str .= "You can ammend your present user data here.";
	$error_str .= make_table(array("",""));
	$error_str .= "<form method=post action=options.php>";
	if($user['login_id'] != 1){#admins can't alter icq etc.
		$error_str .= quick_row("<br />Newsletter Status:","1 means yes to newsletters, 0 means no.<br><input type=text name=news value=$p_user[newsletter]>");
		$error_str .= quick_row("<br />Your AIM SN:"," <input type=text name=aim value=$p_user[aim]>");
		$error_str .= quick_row("<br />Your ICQ #:"," <input type=text name=icq value=$p_user[icq]>");
		$error_str .= quick_row("<br />Your YIM SN:"," <input type=text name=yim value=$p_user[yim]>");
		$error_str .= quick_row("<br />Your MSN SN:"," <input type=text name=msn value=$p_user[msn]>");
	} else {
		$error_str .= "<br />Admins may not have an AIM, ICQ etc. Only a Signature.<br />";
	}
	$error_str .= quick_row("<br />Signature Code:"," Signature code uses the standard html code");
	$error_str .= quick_row("Your Signature (100 Char Max):","<textarea name=sig cols=25 rows=10>".stripslashes($user['sig'])."</textarea>");
	$error_str .= "</table><input type=hidden name=player_op value=2><br /><br />";
	$error_str .= "<input type=submit name=Submit></form><br /><br />";
    print_page("Change Player Information",$error_str);

} elseif(isset($player_op) && $player_op == 2){
if($news > 1 || $news < 0){
	$error_str .= "That isnt a valid number<br>";
} else {
	if($user['login_id'] != 1) { #admins can't alter icq etc
		dbn(__FILE__,__LINE__,"update user_accounts set newsletter = '$news', aim = '".addslashes($aim)."', icq = '".addslashes($icq)."', yim = '".addslashes($yim)."', msn = '".addslashes($msn)."' where login_id = '$user[login_id]'");
	}
	dbn(__FILE__,__LINE__,"update ${db_name}_users set sig = '".addslashes($sig)."' where login_id = '$user[login_id]'");
	$error_str .= "User information updated.";
}
}
#save changes to vars
if(isset($save_vars)) {
	while (list($var, $value) = each($_POST)) {
		$option_check="";
		if($var == 'save_vars') {
			continue;
		} else {
			#ensure option is in range
			db(__FILE__,__LINE__,"select option_min,option_max from option_list where option_name='$var'");
			$option_check=dbr();
			#option out of range
			if($value < $option_check['option_min'] || $value > $option_check['option_max']){
				$error_str .= "<br /><b class=b1>$var</b> out of range.";
			} else { #option in range
				dbn(__FILE__,__LINE__,"update ${db_name}_user_options set $var = '$value' where login_id = '$user[login_id]'");
				$user_options[$var] = $value;
				$error_str .= "<br /><b class=b1>$var</b> updated to <b>$value</b>";
			}
		}
	}#end while
}

// change password
if(isset($changepass)) {
  $rs = "<br /><a href=options.php>Back To Options</a>";
  if($changepass == 'change') {
	$temp_str = "Enter a new password. Min 5 characters, Max 25.";
	$temp_str .= "<table><form action=options.php method=post><input type=hidden name=changepass value=changed>";
	$temp_str .= "<tr><td align=right>Old Password:</td><td><input type=password name=oldpass></td></tr>";
	$temp_str .= "<tr><td align=right>New Password:</td><td><input type=password name=newpass></td></tr>";
    $temp_str .= "<tr><td align=right>Re-type New Password:</td><td><input type=password name=newpass2></td></tr>";
	$temp_str .= "<tr><td></td><td><input type=submit value='Change Password'></td></tr></form></table><br />";
	print_page("Change Password",$temp_str);
  } elseif ($changepass == 'changed') {
		if($user['login_id'] == 1){
			db(__FILE__,__LINE__,"select admin_pw from se_games where db_name = '$db_name'");
			$a_pas_temp = dbr();
			$p_user['passwd'] = $a_pas_temp['admin_pw'];
		}
		if (isset($newpass) && ($newpass == $newpass2)) {
			if(strlen($newpass) < 5) {
			   $temp_str = "Passwords must be at least five letters.<br />";
			   $temp_str .= "<p><a href=javascript:history.back()>Back to Pass-Change Form</a>";
			} elseif($newpass == $oldpass) {
			   $temp_str = "That's a good one, changing to your OLD password? Please try again...lol.";
			   $temp_str = "<p><a href=javascript:history.back()>Back to Pass-Change Form</a>";
			} elseif($user['login_name'] == $newpass) {
			   $temp_str = "What a good idea. Using your login name as the pass.<br />Use a different password.";
			   $temp_str .= "<p><a href=javascript:history.back()>Back to Pass-Change Form</a>";
			} elseif (md5($oldpass) == $p_user['passwd']) {
				$temp_str = "Password changed successfully<br />";
				if ($user['login_id'] == 1) {
					dbn(__FILE__,__LINE__,"update se_games set admin_pw='$newpass' where db_name = '$db_name'");
					$temp_str .= "Admin password will remain after game is wiped<br />";
					$p_user['passwd']='$newpass';
				} else {
					$encrypt_newpass = md5($newpass);
					dbn(__FILE__,__LINE__,"update user_accounts set passwd='$encrypt_newpass' where login_id='$user[login_id]'");
					$p_user['passwd']='$newpass';
				}
				insert_history($user['login_id'],"Password Changed");
			} else {
				$temp_str = "The old password is not correct!<br /><br />";
				$temp_str .= "<a href='javascript:back()'>Go back</a><br />";
			}
		} else {
			$temp_str = "Password mismatch!<br />";
			$temp_str .= "<a href='javascript:back()'>Go back</a><br />";
		}
		print_page("Change Password",$temp_str);
  }
}

$red_Tempest = 1;
$Redlight_District = 2;
$Emerald_Isles = 3;
$Old_red_Tempest = 4;

// change colour scheme
if(isset($scheme)) {

$checked[$user_options['color_scheme']] = " checked";

$error_str .= "Select a colour scheme you like the sound of:";
$error_str .= "<form method=post action=options.php><br />";

	$error_str .= "<select name='style'>";
	$handle=opendir('themes');
    while ($file = readdir($handle)) {
	if ( (!ereg("[.]",$file)) ) {
		$themelist .= "$file ";
	}
    }
    closedir($handle);
    $themelist = explode(" ", $themelist);
    sort($themelist);
    for ($i=0; $i < sizeof($themelist); $i++) {
	if($themelist[$i]!="") {
	    $error_str .= "<option name='style' value='$themelist[$i]' ";
		if($themelist[$i]==$user_options['theme']) $error_str .= "selected";
		$error_str .= ">$themelist[$i]\n";
	}
    }
    $error_str .= "</select>";

$error_str .= "<p><input type=submit value=Submit>";
$error_str .= "</form>";
print_page("Select Scheme",$error_str);


#demo of style sheet
} elseif (isset($style)) {

   $temp = "themes/".$style."/style.css";

   $error_str .= "\n\n<link rel=stylesheet href=$temp>\n";

   $error_str .="Here is an example of what things may look like.";
   $error_str .= "<p>Normal text.\n";
   $error_str .= "<br /><b>Bold text.</b>\n";
   $error_str .= "<br /><b class=b1>Bold class 1</b>\n";
   $error_str .= "<br /><b class=b2>Bold class 2</b>\n";
   $error_str .= "<br /><b class=b3>Bold class 3</b>\n";
   $error_str .= "<br /><a href=>Normal Link</a>\n";
   $error_str .= "<br /><a href=location.php>Visited Links</a>\n";
   $error_str .= "<br /><b class=cloak>Cloaked vessels</b>\n";
   $error_str .= "<p>Do you want to:\n";

   $error_str .= "<br /><a href=options.php?keep=$style>Keep it.</a>\n";

   $error_str .= "<br /><a href=options.php?scheme=1>Select a different Scheme.</a>\n";

   print_page("Test Scheme ", $error_str);

#keep new style sheet.
} elseif ($keep) {
	$user_options['color_scheme'] = 1;
	$color_nw = $$keep;
	$user_options['theme'] = $keep;
	dbn(__FILE__,__LINE__,"update ${db_name}_user_options set color_scheme = '$color_nw' where login_id = '$user[login_id]'");
	dbn(__FILE__,__LINE__,"update ${db_name}_user_options set theme = '$keep' where login_id = '$user[login_id]'");
	$error_str .="Theme changed to: <b>$keep</b>.";
	$error_str .="<br />You can change it again at any time using the same procedure.";
	$rs .= "<br /><a href=options.php>Back To Options</a>";
	print_page("Theme Changed",$error_str);
}

#print main page
$error_str .= "<p>On this page you will find a range of options that will enable you to customise Solar Empire.";
$error_str .= "<p><a href=options.php?changepass=change>Change your Password</a>";
$error_str .= "<br /><a href=options.php?scheme=1>Change your Colour Scheme</a>";
$error_str .= "<br /><a href=options.php?player_op=1>Change your player information</a> (newsletter, aim, icq, msn, yim, signature)";
if($user['login_id'] != 1){
	$error_str .= "<p><a href=location.php?retire=1>Retire from Game</a>";
}

#list other options
$error_str .= "<p>Here are some other Options that you may set.<form method=post name=get_var_form action=options.php>";

$error_str .= "<br /><input type=submit value=\"Submit Vars\">";

// select and output all the user options
db(__FILE__,__LINE__,"select * from option_list where option_name != 'enable_fleets' order by option_name asc");
while($gen_options=dbr()){

	// radio boxes.
	if($gen_options['option_type'] == 1){
		$ct = 0;
		$desc_vars = preg_split("/ &&& /", $gen_options['option_desc']);
		$error_str.= "<p><table border=2 cellspacing=1 width=350><tr bgcolor='#333333'><td><b><font color='#AAAAEE'>$gen_options[option_name]</font></b></td></tr><tr bgcolor='#555555'><td>$desc_vars[0]<br />";
		$checked = array();
		$checked = array_pad($checked,5,"");
		$checked[$user_options[$gen_options['option_name']]] = " checked";

		$sec_count = 1; // used to extract definitions for array (arrays start at 0).

		// loop through the possible selections for each option
		for($ct=$gen_options['option_min'];$ct <= $gen_options['option_max'];$ct++){
			$error_str .= "<br /><input type=radio name='".$gen_options['option_name']."' value='$ct'".$checked[$ct].">".$desc_vars[$sec_count];
			$sec_count ++;
		}

		$error_str .= "</td></tr></table>\n";

	// numerical interface
	} elseif($gen_options['option_type'] == 2){
		$error_str .= "<p><table border=2 cellspacing=1 width=350><tr bgcolor='#333333'><td width='250'><b><font color='#AAAAEE'>$gen_options[option_name]</font></b></td><td align='right'><input type='text' name='$gen_options[option_name]' size='4' value='".$user_options[$gen_options['option_name']]."'> </td></tr><tr bgcolor='#555555'><td colspan='2'><blockquote>$gen_options[option_desc]<br />Min: <b>$gen_options[option_min]</b>, Max: <b>$gen_options[option_max]</b></blockquote></td></tr></table>\n";
	}
}

$error_str .= "<input type='hidden' name='save_vars' value='1'><input type=submit value=\"Submit Vars\"></form>";


print_page("Account Options", $error_str);

?>