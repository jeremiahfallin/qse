<?php
require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

if($print) {
	$map_url = "maps/$db_name/psm_full.png";
} elseif($find) {
	db(__FILE__,__LINE__,"select count(star_id) from ${db_name}_stars");
	$max_sect = dbr();
	if($find < 1 || $find > $max_sect[0] || !$find) {
		$rs = "<p><a href=\"javascript: history.back()\">Back</a>";
		print_header("Error");
		echo "That system does not exist. Try searching for systems between <b>1</b> and <b>$max_sect[0]</b>.";
		print_footer();
		exit;
	} elseif($allow_search_map == 0) {
		$rs = "<p><a href=\"javascript: history.back()\">Back</a>";
		print_header("Error");
		echo "Admin has turned off search facility.";
		print_footer();
		exit;
	} else {
		$map_url = "star_find.php?sys1=$user[location]&sys2=$find";
	}
} else {
	$map_url = "maps/$db_name/ref_full.png";
}

if($print) {
	$link_url = "<a href='map.php'>Normal Map</a> - ";
} else {
	$link_url = "<a href='map.php?print=1'>Printable Map</a> - ";
}
?>
<html>
<head>
<title>Universal Starmap</title>
<link rel="stylesheet" href="themes/red_Tempest/style_p.css">
</head>
<body></body>

<div align="center">
<?php
if($allow_search_map == 1){
	echo "<form action='map.php' method=post><b>Find system: </b><input type='text' size=4 name='find'> <input type='submit' value='Search'></form><br />";
}
?>
<img src="<?php echo $map_url; ?>" border=0>
<br />
<?php if($wormholes == 2){echo"Key:<br /><font color=#FFFF44>Yellow Lines</font> represent One-Way Wormholes.<br /><font color=#00FF00>Green Lines</font> Show Two Way Wormholes.<p>";} ?>
<?php echo $link_url; ?>
<a href="javascript:self.close();">Close Map</a>
</div>
</body>
