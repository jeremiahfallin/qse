<?php
header("Pragma: no-cache");
header("Expires: 0");
ob_end_flush();

require("common.inc.php");

print_header("Save maps");

print "<a href='location.php'>Back to the Star System</a><br />";
print "Please wait for page to load COMPLETLY.<br /><br />";
flush();

if(!isset($db)) {
	print "db required";
	print "</body></html>";
	exit;
} else {
	$db_name = $db;
	mkdir("$map_path/$db_name","0777");
}

$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);

db(__FILE__,__LINE__,"select admin_pw from se_games where db_name = '$db_name'");
$results = dbr();
$admin_pass = $results[0];

if(!isset($pass) || ($pass != $admin_pass)) {
	print "Admin pass incorrect";
	print "</body></html>";
	exit;
}

set_time_limit(0);

if(!isset($nether)) {//###
	readfile("$url_prefix/main_map.php?db=$db_name&save=1");
	flush();
	readfile("$url_prefix/main_map.php?db=$db_name&print=1&save=1");
	flush();
	readfile("$url_prefix/main_map.php?db=$db_name&big_buffer=1&save=1");
	flush();
} else {//###
	readfile("$url_prefix/main_map.php?db=$db_name&save=1&nether=1");//###
	flush();//###
	readfile("$url_prefix/main_map.php?db=$db_name&print=1&save=1&nether=1");//###
	flush();//###
	readfile("$url_prefix/main_map.php?db=$db_name&big_buffer=1&save=1&nether=1");//###
	flush();//###
}//###

if(!isset($nether)) {//###
	db(__FILE__,__LINE__,"select count(*) from ${db_name}_stars where star_id > 0");
	$numstars = dbr();//###
} else {//###
	db(__FILE__,__LINE__,"select count(*) from ${db_name}_stars where star_id < 0");
	$numstars = dbr();//###
}//###

$numstars = $numstars[0];

$sysno = 1;

while($sysno <= $numstars) {
  if(!isset($nether)) {//###
  	readfile("$url_prefix/star_map.php?location=$sysno&db=$db_name&save=1");
  } else {//###
	$locale = 0 - $sysno;//###
  	readfile("$url_prefix/star_map.php?location=$locale&db=$db_name&save=1&nether=1");//###
  }//###
  print " - ".($numstars-$sysno)." remaining<br /><div id='div$sysno'><script>if(document.all)document.all.div$sysno.scrollIntoView();else document.layers['div$sysno'].scrollIntoView();</script></div>\n";
  flush();
  $sysno++;
}
?>

Finished

<br /><br /><a href='location.php'>Back to the Star System</a><br />
Automatic redirection in 20 seconds.<script>setTimeout("self.location='location.php';",20000);</script><br /><br /><br /><br /><br />
<div id='bottom'><script>if(document.all)document.all.bottom.scrollIntoView();else document.layers['bottom'].scrollIntoView();</script></div>
</body></html>