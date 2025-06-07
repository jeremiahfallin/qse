<?php

include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>List All Powerups</h3>';
$ADODB_FETCH_MODE = 2;
if (isset($sort_pups)) {
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	$order_by = "order by '$sort_pups' $going";
} else {
	$order_by = "order by location asc, id asc";
}

define( 'MAX_PAGE_SIZE', 100 );
$page = isset($page) ? intval($page) : 0;
$offset = " limit " . MAX_PAGE_SIZE . " offset " . ($page * MAX_PAGE_SIZE);

db(__FILE__,__LINE__,"select id,location,name,amount,type,p.desc as descrip from ${db_name}_powerups as p $order_by $offset");
$pup = dbr();
if ($pup) {
	db2(__FILE__,__LINE__,"select count(*) from ${db_name}_powerups");
	$total_rows = dbr2();
	$total_rows = array_shift( $total_rows );
	$page_link_args = isset($sort_pups) ? "sort_pups=$sort_pups&sorted=$_GET[sorted]&" : "";
	$page_header = build_page_list( $page, MAX_PAGE_SIZE, $total_rows, $page_link_args );
	$out .= $rs . $page_header . make_table( array(
		"<a href=\"$PHP_SELF?sort_pups=id&sorted=$sorted&page=$page\">ID</a>",
		"<a href=\"$PHP_SELF?sort_pups=location&sorted=$sorted&page=$page\">Location</a>",
		"<a href=\"$PHP_SELF?sort_pups=name&sorted=$sorted&page=$page\">Name</a>",
		"<a href=\"$PHP_SELF?sort_pups=amount&sorted=$sorted&page=$page\">Amount</a>",
		"<a href=\"$PHP_SELF?sort_pups=type&sorted=$sorted&page=$page\">Type</a>",
		"<a href=\"$PHP_SELF?sort_pups=descrip&sorted=$sorted&page=$page\">Description</a>" ));
	do {
		$pup['jump'] = "<a href=\"$url_prefix/location.php?subspace=$pup[location]\">Jump</a>";
		$pup['id'] = "<b class=b1>$pup[id]</b>";
		$out .= make_hash_row($pup);
	} while ( $pup = dbr(1) );
	$out .= "</table>" . $page_header;
} else {
	$out .= $page > 0 ? "No powerups exist on page " . ($page + 1) . '.' : 'No powerups exist.';
}

print_page('Powerups List',$out);
?>
