<?php

include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>List All Transfers</h3>';
$ADODB_FETCH_MODE = 2;
if (isset($sort_xfers)) {
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	$order_by = "order by '$sort_xfers' $going";
} else {
	$order_by = "order by bfrom asc, bto asc";
}

define( 'MAX_PAGE_SIZE', 100 );
$page = isset($page) ? intval($page) : 0;
$offset = " limit " . MAX_PAGE_SIZE . " offset " . ($page * MAX_PAGE_SIZE);

db(__FILE__,__LINE__,"select b.buffer_id as bid, b.owner_id as bfrom, b.transferto_id as bto, s.location as sloc, "
	. "b.ship_id as bship, b.class_name as bclass, s.fighters as sfig, s.shields as sshld, b.timestamp as btime "
	. "from ${db_name}_transfer_buffer as b, ${db_name}_ships as s where b.ship_id = s.ship_id $order_by $offset");
$xfer = dbr();
if ($xfer) {
	db2(__FILE__,__LINE__,"select count(*) from ${db_name}_transfer_buffer");
	$total_rows = dbr2();
	$total_rows = array_shift( $total_rows );
	$page_link_args = isset($sort_xfers) ? "sort_xfers=$sort_xfers&sorted=$_GET[sorted]&" : "";
	$page_header = build_page_list( $page, MAX_PAGE_SIZE, $total_rows, $page_link_args );
	$out .= $rs . $page_header . make_table( array(
		"<a href=\"$PHP_SELF?sort_xfers=bid&sorted=$sorted&page=$page\">ID</a>",
		"<a href=\"$PHP_SELF?sort_xfers=bfrom&sorted=$sorted&page=$page\">Sender</a>",
		"<a href=\"$PHP_SELF?sort_xfers=bto&sorted=$sorted&page=$page\">Receiver</a>",
		"<a href=\"$PHP_SELF?sort_xfers=sloc&sorted=$sorted&page=$page\">Location</a>",
		"<a href=\"$PHP_SELF?sort_xfers=bship&sorted=$sorted&page=$page\">Ship ID</a>",
		"<a href=\"$PHP_SELF?sort_xfers=bclass&sorted=$sorted&page=$page\">Ship Class</a>",
		"<a href=\"$PHP_SELF?sort_xfers=sfig&sorted=$sorted&page=$page\">Fighters</a>",
		"<a href=\"$PHP_SELF?sort_xfers=sshld&sorted=$sorted&page=$page\">Shields</a>",
		"<a href=\"$PHP_SELF?sort_xfers=btime&sorted=$sorted&page=$page\">Transfer Created</a>" ));
	do {
		$xfer['bid'] = "<b class=b1>$xfer[bid]</b>";
		$xfer['bfrom'] = print_name($xfer['bfrom']);
		$xfer['bto'] = print_name($xfer['bto']);
		$xfer['btime'] = date("D M j G:i:s", $xfer['btime'] );
		$out .= make_hash_row($xfer);
	} while ( $xfer = dbr(1) );
	$out .= "</table>" . $page_header;
} else {
	$out .= $page > 0 ? "No transfers exist on page " . ($page + 1) . '.' : 'No transfers exist.';
}

print_page('Transfers List',$out);
?>
