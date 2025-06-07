<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Send site to friend</title>
</head>

<body>
<h2 align="left">Send site to friend</h2>
<!-- Put headers above here -->
<?php
/*
--------------------------------------------------------------
|MD Send to Friend 1.2                                       |
|(c)Matthew Dingley 2002                                     |
|For more scripts or assistance go to Cube Web at:           |
|http://members.lycos.co.uk/matthewdingley/                  |
|or view the readme                                          |
|You may use this program only if the copyright remains      |
|intact. If it is not, it is a breach of your use of this    |
|program.                                                    |
|You are free to use this program on a non commercial site.  |
|To use this program on a commercial site, you need a licence|
|Go to my website for more details                           |
--------------------------------------------------------------
*/
$embordercolor = "#ff0000"; //The color of the highlight around forgotten fields
$siteaddress = "www.yahoo.com"; //The address in the email sent
if($email&&$name&&$sendname&&$sendemail) {
//You can change the next variable if you know what you're doing.
$body = "This e-mail is from $name at $email about a cool website they've found. You can see it at $siteaddress. This is the message $name sent. $message";
$thesubject = "A cool website from $name";
$headers  .= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
$headers .= "From: " . trim($name);
if(mail($sendemail, $thesubject, $body, $headers)) {
echo "<BR><BR>Your e-mail was sent successfully.";
}
else {
echo "Sorry, $name, your e-mail was not sent successfully please try again<br><br>";
echo "<form name=\"theform\" method=\"get\" action=\"friend.php\">";
echo "Your name:<br><input name=\"name\" type=\"text\" value=\"$name\"><br>";
echo "Your e-mail:<br><input name=\"email\" type=\"text\" value=\"$email\"><br>";
echo "Your friend's name:<br><input name=\"sendname\" type=\"text\" value=\"$sendname\"><br>";
echo "Your friend's e-mail:<br><input name=\"sendemail\" type=\"text\" value=\"$sendemail\"><br>";
echo "Your message:<br><textarea name=\"message\" rows=4 cols=50>$message</textarea>";
echo "<br><input type=\"submit\" value=\"Send\" class=\"submit\"></form>";
echo "<br><font size=\"2\" color=\"#666666\">Contact form &copy; <a href=\"http://members.lycos.co.uk/matthewdingley/\">Matthew Dingley</a> 2002</font>";
}
}
if ((!$email||!$name||!$sendname||!$sendemail)&&!(!$email&&!$subject&&!$name&&!$sendname&&!$sendemail)) {
echo "You have forgotten to fill in a detail.<br>";
echo "Please correct the error(s) and resubmit the form";

echo "<style>";
if (!$email) {
echo ".email {border-color:" . $embordercolor . ";}";
}
if (!$name) {
echo ".name {border-color:" . $embordercolor . ";}";
}
if (!$sendemail) {
echo ".sendemail {border-color:" . $embordercolor . ";}";
}
if (!$sendname) {
echo ".sendname {border-color:" . $embordercolor . ";}";
}
echo "</style>";
echo "<form name=\"theform\" method=\"get\" action=\"friend.php\">";
echo "Your name:<br><input name=\"name\" type=\"text\" value=\"$name\" class=\"name\"><br>";
echo "Your e-mail:<br><input name=\"email\" type=\"text\" value=\"$email\" class=\"email\"><br>";
echo "Your friend's name:<br><input name=\"sendname\" type=\"text\" value=\"$sendname\" class=\"sendname\"><br>";
echo "Your friend's e-mail:<br><input name=\"sendemail\" type=\"text\" value=\"$sendemail\" class=\"sendemail\"><br>";
echo "Your message:<br><textarea name=\"message\" rows=4 cols=50 class=\"message\">$message</textarea>";
echo "<br><input type=\"submit\" value=\"Send\" class=\"submit\"></form>";
echo "<br><font size=\"2\" color=\"#666666\">Contact form &copy; <a href=\"http://members.lycos.co.uk/matthewdingley/\">Matthew Dingley</a> 2002</font>";
}
if(!$email&&!$name&&!$sendname&&!$sendemail) {
echo "Just fill in your details and your friend's details and an e-mail will be sent to them, telling them about this site.<BR>";
echo "<form name=\"theform\" method=\"get\" action=\"friend.php\">";
echo "Your name:</b><br><input name=\"name\" type=\"text\" value=\"$name\"><br>";
echo "Your e-mail:</b><br><input name=\"email\" type=\"text\" value=\"$email\"><br>";
echo "Your friend's name:</b><br><input name=\"sendname\" type=\"text\" value=\"$sendname\"><br>";
echo "Your friend's e-mail:</b><br><input name=\"sendemail\" type=\"text\" value=\"$sendemail\"><br>";
echo "Your message:</b><br><textarea name=\"message\" rows=4 cols=50>$message</textarea>";
echo "<br><input type=\"submit\" value=\"Send\" class=\"submit\"></form>";
echo "<br><font size=\"2\" color=\"#666666\">Contact form &copy; <a href=\"http://members.lycos.co.uk/matthewdingley/\">Matthew Dingley</a> 2002</font>";
}
?>

</body>
</html>
