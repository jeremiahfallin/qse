<?php
include_once('../includes/nocache.inc.php');
require_once('admin.inc.php');
array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out = '<h3>Change days left til SD</h3>';
//	db(__FILE__,__LINE__,"select paused from se_games where db_name = '$db_name'");
//	$paused=dbr();
//if($paused['paused'] == 1){
	if($sd_day_num != 2){
		db(__FILE__,__LINE__,"select sd_day_num from se_games where db_name = '$db_name'");
		$sd_day_num = dbr();
		$out .= 'This will change when SD will come into affect';
		$out .= '<form name="get_var_form" action="'.$_SERVER['PHP_SELF'].'" method="post">';
		$out .= '<input type="hidden" name="sd_day_num" value="2" />';
		$out .= '<input type="text" name="new_sd" value="'.$sd_day_num[0].'" size="30" />';
		$out .= '<br /><input type="submit" value="Change" /></form>';
	} else {
		dbn(__FILE__,__LINE__,"update se_games set sd_day_num = '$_POST[new_sd]' where db_name = '$db_name'");
//				dbn(__FILE__,__LINE__,"update se_games set game_days_remaining = '$_POST[new_days]' where db_name = '$db_name'");
	$out .= 'Days left til SD has been changed to: <br /><b>'.$_POST['new_sd'].'</b>.';
}
//} elseif($paused['paused'] == 0){
//$out = 'Currently the game is running';
//$out = 'You cannot change the days left til reset while the game is <b>running</b>.';
//}
print_page('Days left til SD',$out);

?>