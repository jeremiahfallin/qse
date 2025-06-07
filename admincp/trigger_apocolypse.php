<?php
include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

if(( !isset($_POST['sure']) ) || ( $_POST['sure'] != 'yes' )){
	$out .= 'Are you sure you want to destroy this universe?<br>Once started, this process cant be stopped';
	get_var('Trigger Apocolypse',$_SERVER['PHP_SELF'],$out,'sure','yes','index.php');
} elseif(( isset($_POST['sure']) ) && ( $_POST['sure'] == 'yes' )) {
	db(__FILE__,__LINE__,"select count(star_id) as star_count from ${db_name}_stars");
	$stars = dbr();
	for($i = 1; $i <= $stars['star_count']; $i++){
	db2(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $i");
	$exploding = dbr2();
	$new_event = mt_rand(1,4);
	if($new_event <= 3){
	$random_e = " has exploded into a SuperNova";
	$random_n = 6;
	}elseif($new_event == 4){
	$random_e = " has collapsed into a Blackhole";
	$random_n = 1;
	}
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = $random_n where star_id = $i");
	if($new_event == 4){
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set metal = 0, fuel = 0, planetary_slots = 0, darkmatter = 0 where star_id = $i");
	dbn(__FILE__,__LINE__,"delete from ${db_name}_ports where location = '$i'");
	dbn(__FILE__,__LINE__,"delete from ${db_name}_planets where location = '$i'");
	}
	if($new_event < 4){
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set t_a = 1 where star_id = $i");
	}
	$out .= "SS#$i - $exploding[star_name]"."$random_e<br>";
	}
			post_news("Observatory of Sol Special Announcement:<br>Run for your lives!  An unexplained scientific annomaly has caused every sun in every system to collapse or explode!");
}


print_page('Trigger Apocolypse',$out);
?>