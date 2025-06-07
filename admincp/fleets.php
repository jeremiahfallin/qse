<?php

include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>List All Fleets</h3>';
$ADODB_FETCH_MODE = 2;
if (isset($sort_fleets)) {
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	$order_by = "order by '$sort_fleets' $going";
} else {
	$order_by = "order by lname asc, loc asc, fid asc";
}

define( 'MAX_PAGE_SIZE', 100 );
$page = isset($page) ? intval($page) : 0;
$offset = " limit " . MAX_PAGE_SIZE . " offset " . ($page * MAX_PAGE_SIZE);

db(__FILE__,__LINE__,"select f.fleet_id as fid, f.fleet_name as fname, f.login_name as lname, f.location as loc, "
	. "count(s.ship_id) as tships, sum(s.fighters) as tfighters, sum(s.cargo_bays) as tbays from ${db_name}_fleets as f "
	. "left join ${db_name}_ships as s on s.fleet_id = f.fleet_id group by f.fleet_id $order_by $offset");
$fleet = dbr();
if ($fleet) {
	db2(__FILE__,__LINE__,"select count(*) from ${db_name}_fleets");
	$total_rows = dbr2();
	$total_rows = array_shift( $total_rows );
	$page_link_args = isset($sort_fleets) ? "sort_fleets=$sort_fleets&sorted=$_GET[sorted]&" : "";
	$page_header = build_page_list( $page, MAX_PAGE_SIZE, $total_rows, $page_link_args );
	$out .= $rs . $page_header . make_table( array(
		"<a href=\"$PHP_SELF?sort_fleets=fid&sorted=$sorted&page=$page\">Fleet ID</a>",
		"<a href=\"$PHP_SELF?sort_fleets=fname&sorted=$sorted&page=$page\">Fleet Name</a>",
		"<a href=\"$PHP_SELF?sort_fleets=lname&sorted=$sorted&page=$page\">Owner Name</a>",
		"<a href=\"$PHP_SELF?sort_fleets=loc&sorted=$sorted&page=$page\">Location</a>",
		"<a href=\"$PHP_SELF?sort_fleets=tships&sorted=$sorted&page=$page\">Ships</a>",
		"<a href=\"$PHP_SELF?sort_fleets=tfighters&sorted=$sorted&page=$page\">Fighters</a>",
		"<a href=\"$PHP_SELF?sort_fleets=tbays&sorted=$sorted&page=$page\">Cargo Bays</a>" ));
	do {
		$fleet['jump'] = "<a href=\"$url_prefix/location.php?subspace=$fleet[loc]\">Jump</a>";
		$fleet['fid'] = "<b class=b1>$fleet[fid]</b>";
		$out .= make_hash_row($fleet);
	} while ( $fleet = dbr(1) );
	$out .= "</table>" . $page_header;
} else {
	$out .= $page > 0 ? "No fleets exist on page " . ($page + 1) . '.' : 'No fleets exist.';
}

print_page('Fleet List',$out);
?>
