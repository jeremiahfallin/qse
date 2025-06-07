<?php
require("common.inc.php");
$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);
#$link = mysql_connect("$database_host","$database_user","$database_password");
#mysql_select_db(__FILE__,__LINE__,$database);
$db_name = $_GET['db_name'];
	echo "<link rel=\"stylesheet\" href=\"themes/red_Tempest/style_p.css\">";
print_header("Game Variables");
$rs = "<p><a href=javascript:history.back()>Back</a></b></blockquote>";
echo $rs."<BR><BR>";


if($db_name){
	# all variables now taken directly from the DB with each request
	db2(__FILE__,__LINE__,"select name,value from ${db_name}_db_vars order by name");
	while($var_list = dbr2()) {
		$$var_list[name] = $var_list[value];
	}
}

//list game vars
	if(!$admin_var_show) {
	 $error_str .= "The Admin of this game, doesn't want the game variables displayed.";
	} else {
		db(__FILE__,__LINE__,"select * from ${db_name}_db_vars order by name");
		$error_str .= "<h3><b>Game Variables</b></h3>Shown below are all the variables for the game, as set by the Admin.";
		$error_str .= "<table border=0 cellspacing=1 width=350>";
		while($var = dbr()) {
			$error_str .= "<tr bgcolor=#333333><td width=220>$var[name] = ${var[value]}</td>";
			$error_str .= "<tr bgcolor=#555555><td><blockquote>${var[descript]}<br /></td>";
			$error_str .= "<tr bgcolor=#000000><td colspan=2>&nbsp;</td></tr>";
		}
		$error_str .= "</table>";
		}
echo $error_str;
echo "<br><br>".$rs;

print_footer();
?>
