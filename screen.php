<?php
include_once("qlib/nocache.inc.php");
//Page ID
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>

	<title>QS: Generations SE</title>
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
		      <td width="107" align="left"></td>
		<td width=100% align="top">
			<center>
                  <p><img src="qsgen.gif" width="565" height="86"> </p>
                  <p align="center">
                </center>
		</td>
		      <td width="107" align="right">&nbsp; </td>
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

<table cellpadding=4 cellspacing=0 style="border : thin solid #444444;" width=100%>
	<tr>
		<th BGCOLOR='#444444'>
			Screenshots
		</th>
	<tr>
		          <td> <a href="?link=1">Location Page</a><br>
                    <a href="?link=2">Planet Page</a><br>
                    <a href="?link=3">Forum Page</a><br>
                    <a href="?link=4">Homeworld Page</a></td>
	</tr>
</table>

</td>

<td width="65%" valign=top>
	<table cellpadding=4 cellspacing=0 width=100%>
		<tr>
			<th BGCOLOR='#444444'>
<?
if (!isset($link)) {
  $link=0;
}
if($link==1){
echo "<p align=center>Location Page</p>";
} elseif($link==2){
echo "<p align=center>Planet Page</p>";
} elseif($link==3){
echo "<p align=center>Forum Page</p>";
} elseif($link==4){
echo "<p align=center>Homeworld Page</p>";
}
else{
echo "<p align=center>Screenshot</p>";
}
?>			</th>
		<tr>
			      <td><p>
 <?php
if($link==1){
echo "Homeworld System: <br><a href=images/screen/loc.jpg style=\"border:hidden\"><img src=images/screen/loc.jpg width=640 height=480 border=0></a><br>
<br>Standard System: <br><a href=images/screen/loc2.jpg><img src=images/screen/loc2.jpg width=640 height=480 border=0></a>";
} elseif($link==2){
echo "Planet Page: <br><a href=images/screen/planet.jpg><img src=images/screen/planet.jpg width=640 height=480 border=0></a><br>";
} elseif($link==3){
echo "Forum Page: <br><a href=images/screen/forum.jpg><img src=images/screen/forum.jpg width=640 height=480 border=0></a><br>";
} elseif($link==4){
echo "Homeworld Page: <br><a href=images/screen/hw.jpg><img src=images/screen/hw.jpg width=640 height=480 border=0></a><br>
<br>Ship Ship: <br><a href=images/screen/hw2.jpg><img src=images/screen/hw2.jpg width=640 height=480 border=0></a>";
}
else{
echo "<p align=center>Click a screenshot to enlarge on the left hand side</p>";
}
?>
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
				  $qspage_id = 2;
				  linkpage($qspage_id);
				  ?></p>
                    </td>
	</tr>
</table>

<br />
<br />
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
</html>
