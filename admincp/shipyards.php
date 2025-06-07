<?php

include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$user_ship_config = get_config($user['ship_id']);

$out .= '<h3>List All Alien Shipyards</h3>';
$ADODB_FETCH_MODE = 2;
if (isset($sort_yards)) {
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	$order_by = "order by '$sort_yards' $going";
} else {
	$order_by = "order by location asc, asyrd_id asc";
}

define( 'MAX_PAGE_SIZE', 100 );
$page = isset($page) ? intval($page) : 0;
$offset = " limit " . MAX_PAGE_SIZE . " offset " . ($page * MAX_PAGE_SIZE);

db(__FILE__,__LINE__,"select asyrd_id,location,move from ${db_name}_shipyards $order_by $offset");
$yard = dbr();
if ($yard) {
	db2(__FILE__,__LINE__,"select count(*) from ${db_name}_shipyards");
	$total_rows = dbr2();
	$total_rows = array_shift( $total_rows );
	$page_link_args = isset($sort_bmrkts) ? "sort_bmrkts=$sort_bmrkts&sorted=$_GET[sorted]&" : "";
	$page_header = build_page_list( $page, MAX_PAGE_SIZE, $total_rows, $page_link_args );
	$out .= $rs . $page_header . make_table( array(
		"<a href=\"$PHP_SELF?sort_yards=asyrd_id&sorted=$sorted&page=$page\">Shipyard ID</a>",
		"<a href=\"$PHP_SELF?sort_yards=location&sorted=$sorted&page=$page\">Location</a>",
		"<a href=\"$PHP_SELF?sort_yards=move&sorted=$sorted&page=$page\">DMs Until Move</a>" ));
	do {
		$yard['jump'] = "<a href=\"$url_prefix/location.php?subspace=$yard[location]\">Jump</a>";
		$yard['asyrd_id'] = "<b class=b1>$yard[asyrd_id]</b>";
		$out .= make_hash_row($yard);
	} while ( $yard = dbr(1) );
	$out .= "</table>" . $page_header;
} else {
	$out .= $page > 0 ? "No alien shipyards exist on page " . ($page + 1) . '.' : 'No alien shipyards exist.';
}

print_page('Alien Shipyard List',$out);
?>
