<?php




include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

// If variables have already been submitted save them, else display selected variables.
if(isset($_POST['save_vars'])) {
	unset($out);
	foreach($_POST as $var => $value){
		if($var == 'save_vars') {
			continue;
		}
		#update the var, and be sure to only update it if the new range is in value. Otherwise leave it alone.
		dbn(__FILE__,__LINE__,"update ${db_name}_db_vars set value = '$value' where name = '$var' && '$value' >= min && '$value' <= max");
	}
	if(($var_source == 0 ) && ( isset($var_source) )) {
		//save the changed variables to files.
		require_once('includes/build_vars.php');
	}
	insert_history($user['login_id'],'Updated Game Vars');
	$out .= '<p>Admin variables update successfully</p>';
	$out .= '<p><a href="index.php">Back to Admin Index</a></p>';
	$rs = '<p><a href="location.php">Back to Star System</a></p>';
} else {
	unset($out);
	$out .= '<form action="'.$_SERVER['PHP_SELF'].'" name="get_var_form" method="post">';
	$out .= '<input type="hidden" name="save_vars" value="1" />';
	$out .= '<input type="submit" value="Submit Vars" />';
	$out .= '<p>Note: Only variables that are within range will be saved.</p>';
	if(isset($type_var)) {
		db(__FILE__,__LINE__,"select * from ${db_name}_db_vars where name != 'rejoin_delay' && type = '${_GET['type_var']}' order by name");
		$db_var = dbr();
		db2(__FILE__,__LINE__,"select count(value) from ${db_name}_db_vars where name != 'rejoin_delay' && type = '${_GET['type_var']}' order by name");
		$db_var_c = dbr2();
	} else {
		db(__FILE__,__LINE__,"select * from ${db_name}_db_vars where name != 'rejoin_delay' order by name");
		$db_var = dbr();
		db2(__FILE__,__LINE__,"select count(value) from ${db_name}_db_vars where name != 'rejoin_delay' order by name");
		$db_var_c = dbr2();
	}
	$row_c = round($db_var_c[0] * .5);
	$out .= '<table border="0" cellspacing="10" width="100%"><tr><td width="50%" valign="top">';
	$ct5 = 0;
	while($db_var) {
		if($ct5 == $row_c) {
			$out .= '</td><td width="50%" valign="top">';
		}
		$out .= '<table border="2" cellspacing="1" width="100%"><tr bgcolor="#333333"><td width="250"><b><font color="#AAAAEE">'.$db_var['name'].'</font></b> ( '.$db_var['min'].' .. '.$db_var['max'].' )</td><td align="right"><input type="text" name="'.$db_var['name'].'" value="'.$db_var['value'].'" size="8" /></td></tr><tr bgcolor="#555555"><td colspan="2"><blockquote>'.$db_var['descript'].'</blockquote></td></tr></table><br />';
		$ct5 += 1;
		$db_var = dbr();
	}
	$out .= '</td></tr></table>';
	$out .= '<p><input type="submit" value="Submit Vars" />';
	$out .= '<br /></form>';
}
print_page('Admin :: Game Variables',$out);
?>