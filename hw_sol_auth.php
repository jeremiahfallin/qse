<?php

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

sudden_death_check($user);
db4(__FILE__,__LINE__,"select * from ${db_name}_planets where planet_id = 1");
$home = dbr4();
$homeworld = $home['planet_name'];

db4(__FILE__,__LINE__,"select teams,max_teams from se_games where db_name = '${db_name}'");
$tm = dbr4();
db5(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'Homeworld'");
$hwc = dbr5();

	if($user[location] != 1){
		print_page("Error","A Sol Authority HQ does not exist at this location.");
	}
if($user[location] == 1){
	$rs = "<p><a href=hw.php>Return to $homeworld</a>";
} else {
	$rs = "<p><a href=port.php>Back to the Port</a>";
}


if(isset($fines)) {
if($user[location] == 1){
	$tex = "<a href=hw_sol_auth.php>Return to Sol Authority HQ</a><br />";
}
	$tex .= "<br /><b>You have entered the <font color=red>Fines and Tax Office</font> of the Sol Authority, Citizen.</b><br /><p>";

	db(__FILE__,__LINE__,"select * from ${db_name}_fines where login_id = '$user[login_id]'");
	$my_fine = dbr();
	if($user[login_id] == 1) {
		$tex .= "Welcome to the Tax and Fines Office, Your Excellency! Here is our list of outstanding fines for your perusal. - (Dev Note: To be implemented with remaining SA Additions - 11/11/02)";
	} elseif($my_fine) {
		$tex .= "It would appear that you owe us a fine of <b>$my_fine[amount]. - <a href=sol_auth.php?pay_fine=1>Pay Fine</a>";
	} elseif ($user[outlaw] != 0) {
		$tex .= "Strange your face seems familiar. Perhaps I fined you once before... Unfortunately you appear to have no fines outstanding. Do you mind leaving now?";
	} else {
		$tex .= "It appears you have no outstanding fines or taxes. Do you wish to be fined? Then stop wasting my time!";
	}

	print_page("Fines and Tax Office",$tex);
}

if(isset($pay_fine)) {
if($user[location] == 1){
	$tex = "<a href=hw_sol_auth.php>Return to Sol Authority HQ</a><br />";
}
	$tex = "<a href=hw_sol_auth.php>Return to Sol Authority HQ</a><br />";
	db(__FILE__,__LINE__,"select * from ${db_name}_fines where login_id = '$user[login_id]'");
	$the_fine = dbr();
	if($user[cash] < $the_fine[amount]) {
		print_page("Fines and Tax Office","You do not have enough credits to pay this fine!");
	} else {
		take_cash($the_fine[amount]);
		dbn(__FILE__,__LINE__,"update ${db_name}_fines set paid = 1 where login_id = '$user[login_id]'");
	}
	$tex .= "Your fine has been paid. Do not re-offend Citizen!";
	print_page("Fines and Tax Office",$tex);
}

if($user[location] == 1){
$tex = "<b>Welcome Citizen to the <font color=red>Sol Authority Headquarters</font> of $homeworld.</b><br /><br />";
}
$tex .= make_table(array("Services Available"));
$tex .= make_row(array("<a href=hw_sol_auth.php?fines=1>Fines and Tax Office</a>"));
$tex .= "</table>";
$tex .= "<br />We must apologise for the lack of services. We are undergoing some renovations...";






print_page("Sol Authority Compound",$tex);


?>