<?php
include_once("qlib/nocache.inc.php");
require("story.inc.php");
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




<table width="800" height="521" border="0" align="center">
<tr>

<td valign=top width=140 align=left>

<table cellpadding=4 cellspacing=0 style="border : thin solid #444444;" width=100%>
	<tr>
		<th BGCOLOR='#444444'>
			Select a story
		</th>
	<tr><?php
		          echo "<td><a href=story.php?link=1>Background Story</a>";
				  echo "<br><a href=story.php?link=2>Delta Bomb</a>";
				  echo "<br><a href=story.php?link=3>Blackmarket Upgrade</a>";
				  echo "<br><a href=story.php?link=4>Seatogus Revamp</a></td>";

				  ?>
	</tr>
</table>

</td>

<td width="65%" valign=top>
	<table cellpadding=4 cellspacing=0 width=100%>
		<tr>
			<th BGCOLOR='#444444'>
				Story
			</th>
		<tr>
			      <td><p>
<?php
if (!isset($link)) {
  $link=0;
}
if($link==1){
echo $story['The_Solar_Empire_Story'];
} elseif($link==2){
echo $story['Delta_Bomb_Blueprints_Disappear'];
} elseif($link==3){
echo $story['New_Ships_Spotted_at_Blackmarkets'];
} elseif($link==4){
echo $story['Seatogus_announces_ship_improvements'];
} else {
echo "<p align=center>Select a story on the left hand side</p>";
}

?>
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
				  $qspage_id = 3;
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
