<?php




require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user[login_id] != 1) {
	print_page('Admin','Admin access only.');
	exit();
}

if(isset($_POST['cut'])) {
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_1 = 0 where star_id = ${_POST['s1']} && link_1 = ${_POST['s2']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_2 = 0 where star_id = ${_POST['s1']} && link_2 = ${_POST['s2']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_3 = 0 where star_id = ${_POST['s1']} && link_3 = ${_POST['s2']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_4 = 0 where star_id = ${_POST['s1']} && link_4 = ${_POST['s2']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_5 = 0 where star_id = ${_POST['s1']} && link_5 = ${_POST['s2']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_6 = 0 where star_id = ${_POST['s1']} && link_6 = ${_POST['s2']}");

	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_1 = 0 where star_id = ${_POST['s2']} && link_1 = ${_POST['s1']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_2 = 0 where star_id = ${_POST['s2']} && link_2 = ${_POST['s1']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_3 = 0 where star_id = ${_POST['s2']} && link_3 = ${_POST['s1']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_4 = 0 where star_id = ${_POST['s2']} && link_4 = ${_POST['s1']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_5 = 0 where star_id = ${_POST['s2']} && link_5 = ${_POST['s1']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_6 = 0 where star_id = ${_POST['s2']} && link_6 = ${_POST['s1']}");
}

if(isset($_POST['add'])) {
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_1 = ${_POST['s2']} where star_id = ${_POST['s1']} && link_1 = 0");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_2 = ${_POST['s2']} where star_id = ${_POST['s1']} && link_2 = 0 && link_1 != ${_POST['s2']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_3 = ${_POST['s2']} where star_id = ${_POST['s1']} && link_3 = 0 && link_1 != ${_POST['s2']} && link_2 != ${_POST['s2']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_4 = ${_POST['s2']} where star_id = ${_POST['s1']} && link_4 = 0 && link_1 != ${_POST['s2']} && link_2 != ${_POST['s2']} && link_3 != ${_POST['s2']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_5 = ${_POST['s2']} where star_id = ${_POST['s1']} && link_5 = 0 && link_1 != ${_POST['s2']} && link_2 != ${_POST['s2']} && link_3 != ${_POST['s2']} && link_4 != ${_POST['s2']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_6 = ${_POST['s2']} where star_id = ${_POST['s1']} && link_6 = 0 && link_1 != ${_POST['s2']} && link_2 != ${_POST['s2']} && link_3 != ${_POST['s2']} && link_4 != ${_POST['s2']} && link_5 != ${_POST['s2']}");

	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_1 = ${_POST['s1']} where star_id = ${_POST['s2']} && link_1 = 0");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_2 = ${_POST['s1']} where star_id = ${_POST['s2']} && link_2 = 0 && link_1 != ${_POST['s1']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_3 = ${_POST['s1']} where star_id = ${_POST['s2']} && link_3 = 0 && link_1 != ${_POST['s1']} && link_2 != ${_POST['s1']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_4 = ${_POST['s1']} where star_id = ${_POST['s2']} && link_4 = 0 && link_1 != ${_POST['s1']} && link_2 != ${_POST['s1']} && link_3 != ${_POST['s1']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_5 = ${_POST['s1']} where star_id = ${_POST['s2']} && link_5 = 0 && link_1 != ${_POST['s1']} && link_2 != ${_POST['s1']} && link_3 != ${_POST['s1']} && link_4 != ${_POST['s1']}");
	dbn(__FILE__,__LINE__,"update ${db_name}_stars set link_6 = ${_POST['s1']} where star_id = ${_POST['s2']} && link_6 = 0 && link_1 != ${_POST['s1']} && link_2 != ${_POST['s1']} && link_3 != ${_POST['s1']} && link_4 != ${_POST['s1']} && link_5 != ${_POST['s1']}");
}


$error_str .= '<h3>Edit Warp Links</h3><p>Remember to regenerate your maps after editing to ensure accurate maps.</p>';
$error_str .= make_table(array('&nbsp;','Cut Star Link'));
$error_str .= '<form action="cut_link.php" method="post">';
$error_str .= '<input type="hidden" name="cut" value="1" />';
$error_str .= quick_row('Star 1:','<input name="s1" value="'.$_POST['s1'].'" />');
$error_str .= quick_row('Star 2:','<input name="s2" value="'.$_POST['s2'].'" />');
$error_str .= '<tr><td colspan="2"><input type="submit" value="Cut Link" /></form></table>';

$error_str .= '<br /><br /><br />';
$error_str .= make_table(array('&nbsp;','Add Star Link'));
$error_str .= '<form action="cut_link.php" method="post">';
$error_str .= '<input type="hidden" name="add" value="1" />';
$error_str .= quick_row('Star 1:','<input name="s1" value="'.$_POST['s1'].'" />');
$error_str .= quick_row('Star 2:','<input name="s2" value="'.$_POST['s2'].'" />');
$error_str .= '<tr><td colspan="2"><input type="submit" value="Create Link" /></form></table>';

print_page('Alter Warp Links',$error_str);
?>