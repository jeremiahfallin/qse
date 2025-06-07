<?php





include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>Multi-Scanner</h3>';

//Moriarty's Multi Checker
$pass_check = array(); //store for all passwords
$dup_pass = array(); //Store for duplicate passwords
$ip_check = array(); //store for all passwords
$ip_pass = array(); //Store for duplicate passwords
db2(__FILE__,__LINE__,"select a.login_name,a.login_id,a.passwd,a.last_ip,a.email_address,s.login_id from user_accounts a, ${db_name}_users s where a.login_id > 5 && s.login_id = a.login_id order by last_ip");
	while($m_check = dbr2()){
		$x_pa = $pass_check;
		//This loop finds same pass's
		while ($var = each($x_pa)) {
			if ($var['value'] == $m_check['passwd']) {	//A duplicate password
				$dup_pass[$m_check['login_id']] = $m_check['passwd'];
				$dup_pass[$var[key]] = $var['value'];
			}
		}
		$pass_check[$m_check['login_id']] = $m_check['passwd'];

		$x_ip = $ip_check;
		//this loop finds same IP's
		if($m_check['last_ip']){
			while ($var = each($ip_check)) {
				if ($var['value'] == $m_check[last_ip]) {	//A duplicate IP
					$dup_ip[$m_check['login_id']] = $m_check[last_ip];
					$dup_ip[$var[key]] = $var['value'];
				}
			}
		} elseif(!$m_check['last_ip']){
			$dup_ip[$m_check['login_id']] = $m_check['last_ip'];
		}
		$ip_check[$m_check['login_id']] = $m_check['last_ip'];
	}

$out = '<p>The methods for finding <b>Multi\'s</b> cannot be disclosed, as releasing such information would nullify the advantage.<br />However rest assured, that when it says <b class=b1>Definite Multi</b> that its something like 70-90% correct.<br /><br />Note also that this program is really only basic at present, though it does do its job. More checks will be implemented in the future.</p>';
$out .= '<p>This Page does not show which Mutli\'s are related at present.</p>';

//Definate Multi check.

if($dup_pass && $dup_ip){
	$out .= '<p><b>Definite Multi\'s (<i>70-90% Certainty</i>)</b>';
	$t_pa = $dup_pass;
	while ($d_p = each($t_pa)) {
		$t_ip = $dup_ip;
		while ($d_i = each($t_ip)) {
			if($d_p[key] == $d_i[key] && $d_i['value']){
				db(__FILE__,__LINE__,"select login_name from ${db_name}_users where login_id = '$d_i[key]'");
				$ret = dbr();
				$out1 .= '<br /><a href="../player_info.php?target='.$d_i[key].'"><b class="b1">'.$ret['login_name'].'</b></a>';
			} elseif($d_p[key] == $d_i[key] && !$d_i['value']) {
				db(__FILE__,__LINE__,"select login_name from ${db_name}_users where login_id = '$d_i[key]'");
				$ret = dbr();
				db(__FILE__,__LINE__,"select login_name,login_id from user_accounts where login_id != '$d_p[key]' && passwd='$d_p[value]'");
				$sec_ret = dbr();
				$out3 .= '<br /><a href="player_info.php?target='.$d_i[key].'"><b class="b1">'.$ret['login_name'].'</b></a> (not Logged in yet).';
				$out3 .= '<br /><a href="player_info.php?target='.$sec_ret[login_id].'"><b class="b1">'.$sec_ret['login_name'].'</b></a>';
			}
		}
	}
}
if(!$out1){
	$out1 = '<br /><b class="b1"><i>No Definite Multiple Accounts</i></b>';
}
$out = $out.$out1;

$out .= '<p><b>Likely Multi\'s</b> <i>(peeps who could be a multi, however one of the two accounts hasn\'t logged in yet)</i></p>';

if(!$out3){
	$out3 = '<br /><b class=b1><i>No Likely Multiple Accounts</i></b>';
}
$out = $out.$out3;

//Same IP Check
if($dup_ip){
	$t_ip = $dup_ip;
	$out .= '<p><br /><b>Players with identical IPs</b>:';
	while ($d_i = each($t_ip)) {
		db(__FILE__,__LINE__,"select login_name from ${db_name}_users where login_id = '$d_i[key]'");
		$ret = dbr();
		$out2 .= '<br /><a href="../player_info.php?target='.$d_i[key].'"><b class="b1">'.$ret['login_name'].'</b></a>';
	}
}
if(!$out1){
	$out2 = '<br />No Peeps with same IP.';
}
$out = $out.$out2;

$rs .= '<small><p><br /><br /><div align="center">Multi-checker by Jonathan "Moriarty" 2001 <i>(Modified 2003 - Maugrim The Reaper) [Modified for new admin panel 2004 - Hades20082]</i></small></div>';
$rs .= '<small><div align="center">Disclaimer: If a player does turn out to not have been a multi, it should be noted this script only claims a 70-90% certainty of determining someone a Multi. All available evidence should be noted.</small></div>';
print_page('Multi Checking',$out);
?>