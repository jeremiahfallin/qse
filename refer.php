<?php
include_once("includes/nocache.inc.php");
require("user.inc.php");
array_push($FILE_LIST, basename(__FILE__));
include("themes/".$user_options['theme']."/header.php");
print_header("Refer a friend");
print_status();

$siteaddress = $url_prefix;
$admin_email = $admin_mail;
if ($done)
{
$body = "This e-mail is from $name2 at $from about a cool website they've found. \nYou can see it at $siteaddress. \nIf this was spam, please report to the adminstators, $admin_email, containing the entire message and email addresses.\n==================\nThis is the message $name2 sent.\n $content";
mail("$to", "$subject", "$body", "FROM: $from");
die("E-Mail $subject Sent.".print_footer());
}

?>
<p></p><br>
Refer a friend to this site:
<form name="form1" method="post" action="">
<table border=1>
<tr><td>Your Name:</td>    <td><input type="text" name="name2"></td></tr>
<tr><td>Your Email Address: </td>   <td><input type="text" name="from"></td></tr>
<tr><td>Friends Email Address:</td>    <td><input type="text" name="to"></td></tr>
<tr><td>Email Subject:</td><td>	<input type="text" name="subject"></td></tr>
<tr><td>Email Content:</td>	<td><textarea name="content" cols="30" rows="15">Type content for email here</textarea></td></tr>
<tr><td>    <input type='submit' name='done' value='Send!'></td></tr>
</table>
</form>
<?php
print_footer();
?>