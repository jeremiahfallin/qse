<?php
include_once("qlib/config/config.inc.php");
if($game_installed==="1")
{
header('Location:game_listing.php');
}
else
{
header('Location:install/install.php');
}
?>
<html>
<head>
<title>[ QS: Generations - Browser Based Space Strategy Game]</title>
</head>
<!--<script language="JavaScript"><!--
if (parent.frames.length > 0)
{
	parent.location.href = location.href
}
else
{
	document.writeln('<FRAMESET ROWS="*" border=0>');
	document.writeln('<FRAME NAME="location_frame" SRC="game_listing.php" SCROLLING="auto" FRAMEBORDER="no" NORESIZE>');
	document.writeln('</frameset>');
}

</script>-->

<body bg="#000000">
</body>
</html>
