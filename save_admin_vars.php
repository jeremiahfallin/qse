<?php
require("common.inc.php"); //loads functions
$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);
$fp = fopen ("$games_dir/$db_name/db_vars.inc.php", "wb");
fwrite($fp,"<?php\n\n# File generated ".gmdate("F j, Y, g:i a")."\n\n# This file holds all saved Admin Variables for a game and is written to by save_vars.php with each Admin update\n\n");
db2(__FILE__,__LINE__,"select name,value from ${db_name}_db_vars order by name");
while($var_list = dbr2()) {
	fwrite($fp,"\$$var_list[name] = '$var_list[value]';\n");
}
fwrite($fp,"\n# End of list\n?>\n");
fclose($fp);
?>