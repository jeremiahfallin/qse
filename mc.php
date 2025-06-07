<?php
require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

print_header("Message Code");
print_status();

if(!isset($try)) {
?>

Listing of code tags to be used in forum. To test these <a href='mc.php?try=1'>Click here to practice</a>
<br />
[url]http://www.solarempire.com[/url] = <a href='http://www.solarempire.com' target='_blank'>http://www.solarempire.com</a>
<br />
<br />
[color=lime]COLOR TEXT[/color] = <font color='lime'>COLOR TEXT</font>
<br />
<br />
[hr] = <hr>
<br />
<br />
[hr=lime] = <hr color='lime'>
<br />
<br />
[happy] = <img src='images/happy.gif' width=15 height=15>
<br />
<br />
[mad] = <img src='images/mad.gif' width=15 height=15>
<br />
<br />
[sad] = <img src='images/sad.gif' width=15 height=15>
<br />
<br />
[surprised] or [surp] = <img src='images/surp.gif' width=15 height=15>
<br />
<br />
[tongue] = <img src='images/tongue.gif' width=15 height=15>
<br />
<br />
[b]BOLD TEXT[/b] = <b>BOLD TEXT</b>
<br />
[i]ITALIC TEXT[/i] = <i>ITALIC TEXT</i>
<br />
[bi]BOLD ITALIC TEXT[/bi] = <b><i>BOLD ITALIC TEXT</i></b>
<br />
<br />And if you want to put a mc wihtout it being interpretted put [[] and []] for [ and ]
<br />
<br />
Also all newlines are converted to br..soo the returns you do while typing your message will show on the forum
<br /><br />
<?php
} else {
?>

<form name=get_var_form action='mc.php' method=post>
<input type='hidden' name='try' value='1'>
<textarea name=text value='' cols=30 rows=10 wrap=soft>
<?php echo stripslashes($text);?>
</textarea>
<input type=hidden name=rs value='&lt;p&gt;&lt;a href=location.php&gt;Back to the Star System&lt;/a&gt;&lt;br&gt;'>
<p><input type=submit value=submit>
</form>
<br />
<br />
  <?php
}

if(isset($text)) {
  $text = mcit($text);
  //$text = preg_replace("/\[:(.*?):\]/","{\\1}",$text);
  $text = stripslashes($text);
  echo $text;
}
?>
</table></body></html>