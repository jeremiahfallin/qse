<?php
require("user.inc.php");
array_push($FILE_LIST, basename(__FILE__));
$filename = 'shipyard.php';
sudden_death_check($user);

if(is_int($_POST['asyrd_id'])) {
	$asyrd_id = $_POST['asyrd_id'];
}

db(__FILE__,__LINE__,"select * from ${db_name}_shipyards where location = '${user['location']}' && asyrd_id = '$asyrd_id'");
$asyrd = dbr();

if (!$asyrd) {
        print_page("Alien Shipyards","You may not contact an Alien Shipyard that is not in the same system as you are in. Stop playing with the URL's'");
} elseif($user['ship_id'] ==1 && $user['login_id'] !=1) {
        print_page("Error","The local Shipyard operater has refused you entry. How can you be a Captain with no ship!!!");
}

$text .= "<p>Welcome to the <b>Alien Shipyards</b>, one of sevral converted alien shipyards found throught the universe.";
$text .= "<br /><p>If it's cutting edge alien ships you want, then this is the right place for you.<br />";
db(__FILE__,__LINE__,"select count(ship_id) from ${db_name}_ships where login_id = '${user['login_id']}'");
$numships = dbr();
$text .= "<p>Your Fleet currently consists of <b>${numships[0]}</b> Ships.";

$text .= "<p>Available Alien/Other Ships:";

include_once("includes/ship_shops.inc.php");

print_page("Alien Ships",$text);
?>

