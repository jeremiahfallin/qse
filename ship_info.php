<?php
require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

db(__FILE__,__LINE__,"select * from ship_types where type_id = '$type'");
$info = dbr();

#determine if the user wants to see ship pictures.
if($user_options[show_pics] == 1){
	$img_txt = "<a href=./images/ships/ship_${info[type_id]}.jpg target=_blank><img border=0 height=120 width=160 src='./images/ships/ship_${info[type_id]}_tn.jpg'></a>";
} elseif($user_options[show_pics] == 0) {
	$img_txt = "<a href=./images/ships/ship_${info[type_id]}.jpg target=_blank>Click for Ship Picture</a>";
}

$error_str .= "<div align=\"center\"><table width=250 height=250 cellspacing=0 cellpadding=3 border=1><tr><td colspan=2 align='center' bgcolor='#555555'><b>$info[name]</b></td></tr><tr><td colspan=2><div align=\"center\">$img_txt</div></td></tr>";
$error_str .= "<tr><td><b>Size</b></td><td>".discern_size($info[size])."</td></tr>";
$error_str .= "<tr><td><b>Type</b></td><td>$info[type]</td></tr>";
$error_str .= "<tr><td><b>Fighters</b></td><td>$info[fighters]/$info[max_fighters]</td></tr>";
$error_str .= "<tr><td><b>Max Shields</b></td><td>$info[max_shields]</td></tr>";
$error_str .= "<tr><td><b>Cargo Bays</b></td><td>$info[cargo_bays]</td></tr>";

if($alternate_play_1 == 1){
	$error_str .= "<tr><td><b>Mining Rate: Metal</b></td><td>$info[mine_rate_metal]</td></tr>";
	$error_str .= "<tr><td><b>Mining Rate: Fuel</b></td><td>$info[mine_rate_fuel]</td></tr>";
} else {
	$quick_maths = $info[mine_rate_metal] + $info[mine_rate_fuel];
	$error_str .= "<tr><td><b>Mining Rate</b></td><td>$quick_maths</td></tr>";
}

if($ship_warp_cost < 0){
	$error_str .= "<tr><td><b>Move Cost (</b>turns<b>)</b></td><td>$info[move_turn_cost]</td></tr>";
}

if (!$info[config]) {
	$error_str .= "<tr><td><b>Specials</b></td><td>None</td></tr>";
} else {
	$error_str .= "<tr><td><b>Specials</b></td><td>$info[config]</td></tr>";
}
$error_str .= "<tr><td><b>Upgrade Pods</b></td><td>$info[upgrades]</td></tr>";
$error_str .= "<tr><td><b>Description</b></td><td>$info[descr]</td></tr>";
$error_str .= "<tr><td><b>Cost</b></td><td>".number_format($info[cost])."</td></tr>";
if($info[type_id] >= 300){
	$error_str .= "<tr><td><b>Tech. Cost</b></td><td>".number_format($info[tcost])."</td></tr>";
}

print_header("Ship Info");
echo $error_str;
$rs ="";
print_footer();
?>