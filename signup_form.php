<?php
require("common.inc.php");

print_header("New Account");
?>
<table width=100%><tr><td>
<table width="900" align="center" style="border : thin solid #444444;">
	<tr>
		<td colspan=3 valign="top" align="center">
			<img src="qsgen.gif" alt="Welcome to Quantum Star SE" title="Welcome to Quantum Star SE" border="0" align="top">
		</td>
	</tr>
<tr><td></td><td valign="top" align="center">

<table align="center"><tr align="center"><td>
	<table cellpadding=4 cellspacing=0>
		<tr><br /><br />
			<th BGCOLOR='#444444' width=750>
				Signup Form
			</th>
		<tr>
	</table>
<br><br><b>Server Rules</b>
</tr></td><tr><td>
<hr width="15%">
<ul>
<li>One account per user (no "multies").
<li>NO Bug exploitation. If you find a bug, report it (and I'm sure you will find a few).
<li>Follow specific game rules.
</ul></tr></td>

</table>
<br>
<form method="post" action="signup.php" name="s_form" id="s_form">
<blockquote>
<table cellspacing="1" cellpadding="2" border=0 align="center"><tr bgcolor=#555555>
<tr>
<td></td><td bgcolor=#333333 align=center><B>Login Name:</B></td>
<td bgcolor=#555555 ><input type="text" name='l_name' value='' size="20">
</td>
</tr>
<tr></tr>
<tr><td bgcolor=#555555 align=center><B>Password:</B></td>
<td bgcolor=#333333><input type="password" name='passwd' value='' size="20"></td>
<!--</tr>
<tr>-->
<td bgcolor=#555555 align=center><B>Password Again:</B></td>
<td bgcolor=#333333><input type="password" name='passwd_verify' value='' size=20></td>
</tr>
<tr></tr>
<tr>
<td bgcolor=#555555 align=center><B>First Name:</B></td>
<td bgcolor=#333333><input name='first_name' value='' size="20"></td>
<!--</tr>
<tr>-->
<td bgcolor=#555555 align=center><B>Last Name:</B></td>
<td bgcolor=#333333><input name='last_name' value='' size="20"></td></tr>
<tr></tr>
<tr><td bgcolor=#555555 align=center><B>Email Address:</B><br>(Must be valid for authorisation)<br>(It will remain private)</td>
<td bgcolor=#333333><input name='email_address' value='' size="20"></td>
<!--</tr>
<tr>-->
<td  bgcolor=#555555 align=center><B>Email Address Again:</B><br>(Please retype the above address)</td>
<td bgcolor=#333333><input name='email_address_verify' value='' size="20"></td>
</tr>
<tr></tr>
<tr>
<td bgcolor=#555555 align=center><B>Select the your <br>Internet Connection Speed:</B><br>(Used to determine user options.)</td>
<td bgcolor=#333333><input type="radio" name='con_speed' value=1>Slow
<br><input type="radio" name='con_speed' value=2 checked>Average
<br><input type="radio" name='con_speed' value=3>Fast</td>
<!--</tr>
<tr>-->
<td bgcolor=#555555 align=center><B>Would you like a<br>newsletter/game updates?</B><br></td>
<td bgcolor=#333333><input type="radio" name='news' value=0>No
<br><input type="radio" name='news' value=1 checked>Yes</td></tr>
<tr><td><br><br></td></tr>
<tr align=center><td bgcolor=#555555 colspan=4>The below listed are optional:</tr></td>
<tr><td bgcolor=#555555 align=right><a href="http://www.aol.com" target=_blank><B><font color="red">*</font></B>&nbsp<img src=images/aim.gif border=0></a> AIM<B>:</B></td>
<td bgcolor=#333333><input name='aim' value='' size="20"></td>
<td bgcolor=#555555 align=right><a href="http://www.icq.com" target=_blank><B><font color="red">*</font></B>&nbsp<img src=images/icq_on.gif border=0></a> ICQ<B>:</B></td>
<td bgcolor=#333333><input name='icq' value='0' size="20"></td></tr>
<tr><td bgcolor=#555555 align=right><a href="http://www.msn.com" target=_blank><B><font color="red">*</font></B>&nbsp<img src=images/msnm.gif border=0></a> MSNM<B>:</B></td>
<td bgcolor=#333333><input name='msn' value='' size="20"></td>
<td bgcolor=#555555 align=right><a href="http://www.yahoo.com" target=_blank><B><font color="red">*</font></B>&nbsp<img src=images/yim.gif border=0></a> YIM<B>:</B></td>
<td bgcolor=#333333><input name='yim' value='' size="20"></td></tr>
<input type=hidden name=db value=<?php echo $db; ?>
</td></tr>

<tr>
	<td>
	I have read and accepted the <a class='delta_link' href="disclaimer.html" target="_new">rules</a> and <a class='delta_link' href="disclaimer.html" target="_new">disclaimer</a>.</td>
	<td>
	<br /><input type="checkbox" name="disc" value="1">
    </td>
</tr>

</table>

</blockquote>
<br><center><input type="submit" value="Submit" name="submission"></center>
<center><a href="login_form.php">Back to the Login Form</a></center>
</form>
</td>
<td valign="top" width=150><br /><br>
<table cellpadding=4 cellspacing=0 style="border : thin solid #444444;" width=100%>
	<tr>
		          <th BGCOLOR='#333333'>Links</th>
	<tr>
		          <td><?php
				  include("links.inc.php");
				  $qspage_id = 4;
				  linkpage($qspage_id);
				  ?>
                    </td>
	</tr>
</table>


</td>




</tr></table>
</table>
</table>
<?php
print_footer();
?>
