<?php

require_once("admin.inc.php");

$perf =& NewPerfMonitor($db);



$menu = "
	<div style=\"text-align: center;\"><h2>Quantum Star SE (AdoDB) Performance Monitoring of Database Activity</h2></div>
	<p>The Adodb Performance Monitor provides statistics based on logged SQL queries if enabled in configuration to identify suspiciously high average execution times, expensive average execution times, invalid queries and a general database healthcheck. The top 25 of each category is displayed based on execution time multiplied by number of instances of execution.</p>
	<p>This data may be used to identify SQL queries whose optimisation will gain the most benefit and reduce overall server load and processing time. This Monitor is not required to host Quantum Star SE, and is provided mainly as a tool for tracking SQL execution times as an aid in identifying bottlenecks and routes for optimisation. Please note that the monitor logs all SQL queries, so running the monitor queries will take time to search these records and assemble results.</p>
	<div>- <a href=\"$url_prefix/admin/adodb_perfmon.php?op=health\">HealthCheck</a> - <a href=\"$url_prefix/admin/adodb_perfmon.php?op=suspicious\">Suspicious SQL</a> - <a href=\"$url_prefix/admin/adodb_perfmon.php?op=expensive\">Expensive SQL</a> - <a href=\"$url_prefix/admin/adodb_perfmon.php?op=invalid\">Invalid SQL</a> -</div>
";

//$report2 = $perf->SuspiciousSQL(10);
$report3 = $perf->ExpensiveSQL(10);
//$report4 = $perf->InvalidSQL(10);

if($_GET['op'] == "health")
{
	$report1 = $perf->HealthCheck();
	$report = "<p><h3>Health Checks</h3></p>";
	$report .= $report1;
	echo($menu.$report);
}
elseif($_GET['op'] == "suspicious")
{
	$report = $report2;
	echo($menu.$report);
}
elseif($_GET['op'] == "expensive")
{

	$report = $report3;
	echo($menu.$report);
}
elseif($_GET['op'] == "invalid")
{

	$report = $report4;
	echo($menu.$report);
}
elseif($_GET['op'] == "menu")
{
	echo($menu);
}
else
{
	echo("<br /><br /><a href=\"javascript:history.back()\">Back</a>");
}





?>