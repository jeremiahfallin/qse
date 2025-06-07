<?php
/*
A script for creating the db_vars_inc file.
This script can be used stand-alone (password in password.php), or by way of direct calling.
Created:
By: Jonathan "Moriarty"
On: 14/4/03
Edited by: Lee 'Hades' Conlin
On: 03/04/04
*/

$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);

db(__FILE__,__LINE__,"select * from ${db_name}_users where login_id = '${_SESSION['login_id']}'");
$user = dbr();

//initial error checking, and security measures
if($user['login_id'] != 1 || $var_source != 0) {
	echo 'Access Denied to user on IP '.$_SERVER['REMOTE_ADDR'];
	exit;
}

//ensure a DB has been selected
if(!isset($db_name)){
	echo 'No database was selected.<form action="'.$_SERVER['PHP_SELF'].'" method="post"><input type="hidden" name="pass" value="'.$pass.'" /><br /> Database: <input type="text" name="db_name" /><p><input type="submit" value="Submit" /></form>';
	exit;
}

//location of the file in relation to the games directory.
$file_loc = "$map_path/$db_name/db_vars.inc.php";

//open a stream
$stream = @fopen($file_loc, 'w');

//ensure a stream could be created
if(empty($stream))
{
	echo 'Unable to create db_vars.inc.php in the specified location.<p>Ensure you have the necassary permissions, and that the sub-directory '.$db_name.' does exist in the Maps directory.</p>';
	exit;
}
elseif (!is_writable($file_loc))
{
	echo 'Unable to write to the specified file for some reason. Ensure permissions are valid.';
	exit;
}

//start the output string
$output_str = "<?php\n//Database: $db_name, ".date('F j, Y, g:i:s a')."\n\n";

//do the DB stuff
$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);
db(__FILE__,__LINE__,"select name, value from ${db_name}_db_vars order by name");
while($db_var = dbr()){
	$output_str .= "\$$db_var[name] = '$db_var[value]';\n";
}

// Add the new format $GAMEVAR array - will work alongside currently used vars until complete changover achieved
$output_str .= "\n\n\n// New Format $GAMEVAR array for all Admin Variables\n\n";
$output_str .= "\$GAMEVAR = array(\n";
db(__FILE__,__LINE__,"select count(name) as num from ${db_name}_db_vars");
$var_count = dbr();
$i = 0;
db(__FILE__,__LINE__,"select name, value from ${db_name}_db_vars order by name");
while($db_var2 = dbr())
{
	$i ++; // so we don't add a comma to the last array element and end up with a syntax error on loading the file!
	if($i == $var_count['num'])
	{
		$output_str .= "\t\"$db_var2[name]\"=>\"$db_var2[value]\"\n";
	}
	else
	{
		$output_str .= "\t\"$db_var2[name]\"=>\"$db_var2[value]\",\n";
	}
}
$output_str .= ");\n\n";


//output the final file
if(!fwrite($stream, $output_str.'?>')){
	echo 'For some reason the file could not be written to. Ensure permissions are valid.';
} else {
	echo 'Variables successfully created for Database <b>'.$db_name.'</b>';
}
?>