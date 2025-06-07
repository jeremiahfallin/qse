<?php
#
#
# file to be run manually to replenish material contents without editing DB, just change the vars and away...
#
#
require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

if($user[login_id] != 1) {
	print_page("Admin","Admin access only.");
	exit();
}

mt_srand((double)microtime()*1000000);

//manually edit following 5 variables
$number = 50; //set to whatever number of stars you feel like changing, chooses each randomly, may overlap.
$top = 123456; //set as max amount of material per system
$bottom = 1234; //set as min amount of material per system
$material = "fuel"; //change to either fuel, metal, darkmatter as appropriate

for($i=0; $i<$number; $i++) {
	$star = random_system_num();
	$test1 = mt_rand($bottom, $top);
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set ".$material." = ".$test1." where star_id = ".$star);
	$text .= "$test1 of $material added to Star System $star.<br />";
}

print_page("Star Systems Altered",$text."<br /><b>As per the manual editing of manual_star.php, the information calculated above has been entered into the database.</b>");

?>