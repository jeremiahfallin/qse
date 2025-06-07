<?php
include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>List All Blackmarkets</h3>';
$ADODB_FETCH_MODE = 2;
if(isset($sort_bmrkts)){
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	$order_by = "order by '$sort_bmrkts' $going";
} else {
	$order_by = "order by location asc, bmrkt_id asc";
}

define( 'MAX_PAGE_SIZE', 100 );
$page = isset($page) ? intval($page) : 0;
$offset = " limit " . MAX_PAGE_SIZE . " offset " . ($page * MAX_PAGE_SIZE);

db(__FILE__,__LINE__,"select bmrkt_id,location,bm_move,bm_name from ${db_name}_bmrkt $order_by $offset");
$bmrkt = dbr(1);
if($bmrkt) {
	db2(__FILE__,__LINE__,"select count(*) from ${db_name}_bmrkt");
	$total_rows = dbr2();
	$total_rows = array_shift( $total_rows );
	$page_link_args = isset($sort_bmrkts) ? "sort_bmrkts=$sort_bmrkts&sorted=$_GET[sorted]&" : "";
	$page_header = build_page_list( $page, MAX_PAGE_SIZE, $total_rows, $page_link_args );
	$out .= $rs . $page_header . make_table( array(
		"<a href=$PHP_SELF?sort_bmrkts=bmrkt_id&sorted=$sorted&page=$page>BM ID</a>",
		"<a href=$PHP_SELF?sort_bmrkts=location&sorted=$sorted&page=$page>Location</a>",
		"<a href=$PHP_SELF?sort_bmrkts=bmrkt_move&sorted=$sorted&page=$page>DMs Until Move</a>",
		"<a href=$PHP_SELF?sort_bmrkts=bm_name&sorted=$sorted&page=$page>Name</a>"));
	while($bmrkt) {
		$bmrkt['jump'] = "<a href=\"$url_prefix/location.php?subspace=$bmrkt[location]\">Jump</a>";
		$bmrkt['bmrkt_id'] = "<b class=b1>$bmrkt[bmrkt_id]</b>";
		$out .= make_hash_row($bmrkt);
		$bmrkt = dbr(1);
	}
	$out .= "</table>" . $page_header;
} else {
	$out .= $page > 0 ? "No black markets exist on page " . ($page + 1) . '.' : 'No black markets exist.';
}


print_page("Blackmarkets",$out);

?>
