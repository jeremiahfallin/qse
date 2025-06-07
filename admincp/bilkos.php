<?php

include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>List All Won Bilkos Items</h3>';
$ADODB_FETCH_MODE = 2;
if (isset($sort_bilkos)) {
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	$order_by = "order by '$sort_bilkos' $going";
} else {
	$order_by = "order by bidder_id asc, item_name asc, going_price asc";
}

define( 'MAX_PAGE_SIZE', 100 );
$page = isset($page) ? intval($page) : 0;
$offset = " limit " . MAX_PAGE_SIZE . " offset " . ($page * MAX_PAGE_SIZE);

db(__FILE__,__LINE__,"select item_id,bidder_id,item_name,going_price from ${db_name}_bilkos where active=0 $order_by $offset");
$bilkos = dbr();
if ($bilkos) {
	db2(__FILE__,__LINE__,"select count(*) from ${db_name}_bilkos where active=0");
	$total_rows = dbr2();
	$total_rows = array_shift( $total_rows );
	$page_link_args = isset($sort_bilkos) ? "sort_bilkos=$sort_bilkos&sorted=$_GET[sorted]&" : "";
	$page_header = build_page_list( $page, MAX_PAGE_SIZE, $total_rows, $page_link_args );
	$out .= $rs . $page_header . make_table( array(
		"<a href=\"$PHP_SELF?sort_bilkos=item_id&sorted=$sorted&page=$page\">ID</a>",
		"<a href=\"$PHP_SELF?sort_bilkos=bidder_id&sorted=$sorted&page=$page\">Winning Bidder</a>",
		"<a href=\"$PHP_SELF?sort_bilkos=item_name&sorted=$sorted&page=$page\">Item Name</a>",
		"<a href=\"$PHP_SELF?sort_bilkos=going_price&sorted=$sorted&page=$page\">Price Paid</a>" ));
	do {
		$bilkos['item_id'] = "<b class=b1>$bilkos[item_id]</b>";
		$bilkos['bidder_id'] = print_name($bilkos['bidder_id']);
		$bilkos['going_price'] = number_format($bilkos['going_price']);
		$out .= make_hash_row($bilkos);
	} while ( $bilkos = dbr(1) );
	$out .= "</table>" . $page_header;
} else {
	$out .= $page > 0 ? "No Bilkos items exist on page " . ($page + 1) . '.' : 'No one has a won Bilkos item.';
}

print_page('Bilkos List',$out);
?>
