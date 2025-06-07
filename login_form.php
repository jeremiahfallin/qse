<?php
include_once("qlib/nocache.inc.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>

	<title>QS: Generations SE</title>
	<script language="javascript" src="md5.js"></script>
<script language="javascript">
<!--
  function doChallengeResponse() {
    str = document.login_form.l_name.value.toLowerCase() + ":" +
          MD5(document.login_form.passwd.value) + ":" +
          document.login_form.challenge.value;
    document.login_form.response.value = MD5(str);
    document.login_form.passwd.value = "";
    document.logintrue.l_name.value = document.login_form.l_name.value;
    document.logintrue.response.value = MD5(str);
    document.logintrue.submit();
    return false;
  }
// -->
</script>
</head>

<body text="#FFFFFF">
<link rel="stylesheet" href="themes/red_Tempest/style_p.css">
<div align="center">



<table border="0" cellspacing="0" style="border : thin solid #404040;" cellpadding="0">
<tr style="border-bottom-style : solid ;border-bottom-width : thin; color #404040;">
	<td  width=5></td>
	<td  align="center" valign="top">
	<center>
<table cellpadding="0" cellspacing="0" width="100%" border="0" align="center">
	<tr>
		      <td width="127" align="left"></td>
		<td width=100% align="top">
			<center>
                  <p>
				  <img src="qsgen.gif" width="666" height="87">

				   </p>
                  <p align="center">
                </center>
		</td>
		      <td width="127" align="right">&nbsp; </td>
	</tr>
</table>
</center>

	</td>
</tr>
<tr>
	<td></td>
	<td align=left>




<table width="100%" height="521" border="0" align="center">
<tr>

<td valign=top width=140 align=left>

<?php

require_once("common.inc.php");

// No longer needed as of PHP 4.2.0
// mt_srand((double)microtime()*1000000);

$p_user = 0;
$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);
if(!empty($_SESSION['login_id']))
{
	db(__FILE__,__LINE__,"select * from user_accounts where login_id = '$_SESSION[login_id]'");
	$p_user = dbr();
	$check = new Q_AuthCheck();
	$logged_in = $check->Check_Auth_2($p_user);
}
else
{
	$logged_in = 0;
}

if($p_user['login_id'] == 1 && $logged_in == 1 && isset($delete)) {
	if($sure != "yes")
	{
		GetDeleteVar('News Deletion',$_SERVER['PHP_SELF'],"Are you certain you wish to delete News Item ID (<b>$delete</b>)?",'sure','');
	}
	else
	{
		dbn(__FILE__,__LINE__,"delete from qsse_home_news where news_id = '$delete'");
	}
}


// Whether user logged in or not is decided by a check auth function, logins run from SE login functions as this page
// is aimed to be integrated into this.
$_SESSION['authenticated'] = "";
if($logged_in == 1 || $_SESSION['authenticated'] == "true")
{ //player logged in so show user functions


	echo "
<table cellpadding=4 cellspacing=0 style='border : thin solid #444444' width=100%>
	<tr>
		<th BGCOLOR=#444444>
			Quantum Star Login
		</th>
	<tr>
		<td>
			Welcome <b>$p_user[login_name]<b>.<br /><br />
			<a href='game_listing.php?t_q_p=$p_user[login_id]'>View Game Listing</a><br>
			<a href='logout.php?logout_gamelist=1'>Logout</a>
		</td>
	</tr>
</table>

<br />
<br />
	";
	$delay = time() - 300;
	db(__FILE__,__LINE__,"delete from qbase_challenge_record where sessid = '$_sid' or timestamp <= '$delay'");
}
elseif($logged_in == 0 || $_SESSION['authenticated'] != "true")
{

// generate a challenge string and delete older challenges/challanges from same session stored on database
$challenge = md5(uniqid(rand(), true));
$delay = time() - 300;
db(__FILE__,__LINE__,"delete from qbase_challenge_record where sessid = '$_sid' or timestamp <= '$delay'");
db(__FILE__,__LINE__,"insert into qbase_challenge_record (sessid, challenge, timestamp) values ('$_sid' ,'$challenge', '".time()."')");

echo "
<form action=\"login.php\" method=\"post\" name=\"login_form\" onSubmit=\"doChallengeResponse()\">
<table cellpadding=4 cellspacing=0 style=\"border : thin solid #444444;\" width=100%>
	<tr>
		<th BGCOLOR=\"#444444\">
			Quantum Star Login
		</th>
	<tr>
		<td>
			Login Name:<br />
			<input type=\"text\" name=\"l_name\" value=\"".Check_Name()."\" size=15>
		</td>
	</tr>
		<td>
			Password:
			<br /><input type=\"password\" name=\"passwd\" size=15>
		</td>
	</tr>
		<td>
			<input type=\"submit\" value=\"Login\" class=submit>
		</td>
	</tr>
</table>

<br />
<br />

<table cellpadding=4 cellspacing=0 style=\"border : thin solid #444444;\" width=100%>
	<tr>
		<th BGCOLOR=\"#444444\">
			New Player?
		</th>
	<tr>
		<td>
			QS:Gen is a free online game. To signup visit our signup form at the link below.<br /><a href=signup_form.php>Signup Here</a>
		</td>
	</tr>
</table>


<!-- Set up the form with the challenge value and an empty reply value -->
<input type=\"hidden\" name=\"challenge\" value=\"$challenge\">
<input type=\"hidden\" name=\"response\" value=\"\">
</form>

<form name=\"logintrue\" action=\"login.php\" method=post>
<input type=\"hidden\" name=\"l_name\" value=\"\">
<input type=\"hidden\" name=\"challenge\" value=\"$challenge\">
<input type=\"hidden\" name=\"response\"  value=\"\">
</form>

<br />
<br />";
}

function Check_Name() {
	if(isset($_COOKIE['login_name']))
	{
		return $_COOKIE['login_name'];
	}
	else
	{
		return "";
	}
}

function GetDeleteVar($title,$page_name,$text,$var_name,$var_default,$back_page) {
	global $_GET,$rs,$_POST,$border_prop,$url_prefix;
	$ostr = "<form name=get_var_form action=$page_name method=POST onSubmit='submitonce(this);'>";
	$ostr .= "$text<p>";
	while (list($var, $value) = each($_GET)) {
		$ostr .= "<input type=hidden name=$var value='$value'>";
	}
	while (list($var, $value) = each($_POST)) {
		$ostr .= "<input type=hidden name=$var value='$value'>";
	}
	if($var_name == 'sure') {
		$ostr .= '<input type=hidden name=sure value=yes>';
		if(isset($back_page)) {
			$ostr .= '<input type=submit name=submit value=Yes> <input type="Button" width="30" value="No" onclick="javascript: self.location=\''.$url_prefix.'/'.$back_page.'\'"> </form>';
		} else {
			$ostr .= '<input type=submit name=submit value=Yes> <input type="Button" width="30" value="No" onclick="javascript: history.back()"> </form>';
		}
	}
	print_header($title);
	echo $ostr;
	require_once("themes/red_Tempest/footer.php");
	exit();
}


?>

<table cellpadding=4 cellspacing=0 style="border : thin solid #444444;" width=100%>
	<tr>
		<th BGCOLOR='#444444'>
			Description
		</th>
	<tr>
		          <td> <b>QS:Gen</b> is a competitive browser-based space strategy
                    and combat style game. No downloads are required to play.
                    Players aim to upgrade their fleets of mining and war vessels,
                    support their planets, and produce fighters all in order to
                    defeat enemy clans, and fractions, of other players.</td>
	</tr>
</table>

</td>

<td width="65%" valign=top>
	<table cellpadding=4 cellspacing=0 width=100%>
		<tr>
			<th BGCOLOR='#444444'>
				Latest News
			</th>
		<tr>
			      <td><p>
                      <?php require_once("print_qs_news.php"); ?>
                    </p>
                    </td>
		</tr>
	</table>
</td>



<td valign=top align=left width=20%>


<table cellpadding=4 cellspacing=0 style="border : thin solid #444444;" width=100%>
	<tr>
		          <th BGCOLOR='#444444'> Links</th>
	<tr>
		          <td><p><?php
				  include("links.inc.php");
				  $qspage_id = 1;
				  linkpage($qspage_id);
				  ?></p>
                    </td>
	</tr>
</table>

<br />
<br />

<table cellpadding=4 cellspacing=0 style="border : thin solid #444444;" width=100%>
	<tr>
		          <th BGCOLOR='#444444'>Voting Stats</th>
	<tr>
		          <td>
		          	<b>Votes This Week:</b> <SCRIPT language="Javascript" src="http://www.topwebgames.com/games/votes.js?id=4621"></SCRIPT><br />
		          	<br /><b>Current Placement:</b> <SCRIPT language="Javascript" src="http://www.topwebgames.com/games/placement.js?id=4621"></SCRIPT><br />
		          	<br /><SCRIPT language="Javascript" src="http://www.topwebgames.com/games/countdown.js"></SCRIPT>
		          </td>
	</tr>
</table>

              <br />
<br />


<br />
<br />

</td>
</tr>
</table>
	</td>
	<td></td>
</tr>
<tr>
	<td width="5"></td>
	<td ></td>

</tr>
</table><br /></form>
</div>

<?php
include_once("themes/red_Tempest/footer.php");
?>

</body>
<script language="JavaScript">
<!--
  // Activate the appropriate input form field.
  if (document.login_form.l_name.value == '') {
    document.login_form.l_name.focus();
  } else {
    document.login_form.passwd.focus();
  }
// -->
</script>
</html>
