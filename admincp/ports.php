<?php
include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>List All Ports</h3>';
$ADODB_FETCH_MODE = 2;
if(isset($sort_ports)){
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	$order_by = "order by '$sort_ports' $going";
} else {
	$order_by = "order by location asc, port_id asc";
}

define( 'MAX_PAGE_SIZE', 100 );
$page = isset($page) ? intval($page) : 0;
$offset = " limit " . MAX_PAGE_SIZE . " offset " . ($page * MAX_PAGE_SIZE);

db(__FILE__,__LINE__,"select port_id,location from ${db_name}_ports $order_by $offset");
$clan_port = dbr(1);
if($clan_port) {
	db2(__FILE__,__LINE__,"select count(*) from ${db_name}_ports");
	$total_rows = dbr2();
	$total_rows = array_shift( $total_rows );
	$page_link_args = isset($sort_ports) ? "sort_ports=$sort_ports&sorted=$_GET[sorted]&" : "";
	$page_header = build_page_list( $page, MAX_PAGE_SIZE, $total_rows, $page_link_args );
	$out .= $rs . $page_header . make_table( array(
		"<a href=$PHP_SELF?sort_ports=port_id&sorted=$sorted&page=$page>Port ID</a>",
		"<a href=$PHP_SELF?sort_ports=location&sorted=$sorted&page=$page>Location</a>"));
	while($clan_port) {
		$clan_port['jump'] = "<a href=\"$url_prefix/location.php?subspace=$clan_port[location]\">Jump</a>";
		$clan_port['port_id'] = "<b class=b1>$clan_port[port_id]</b>";
		$out .= make_hash_row($clan_port);
		$clan_port = dbr(1);
	}
	$out .= "</table>" . $page_header;
} else {
	$out .= $page > 0 ? "No ports exist on page " . ($page + 1) . '.' :
	 	'No ports exist.  I suggest you run universe generation.';
}


print_page("Ports",$out);

?>
