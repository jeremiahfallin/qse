<?php
include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>List All Systems</h3>';
$ADODB_FETCH_MODE = 2;
if(isset($sort_stars)){
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	$order_by = "order by '$sort_stars' $going";
} else {
	$order_by = "order by star_id asc";
}

define( 'MAX_PAGE_SIZE', 100 );
$page = isset($page) ? intval($page) : 0;
$offset = " limit " . MAX_PAGE_SIZE . " offset " . ($page * MAX_PAGE_SIZE);

db(__FILE__,__LINE__,"select star_id,star_name,link_1,link_2,link_3,link_4,link_5,link_6,metal,fuel,darkmatter, "
	. "wormhole,planetary_slots,unigen from ${db_name}_stars $order_by $offset");
$clan_star = dbr(1);
if($clan_star) {
	db2(__FILE__,__LINE__,"select count(*) from ${db_name}_stars");
	$total_rows = dbr2();
	$total_rows = array_shift( $total_rows );
	$page_link_args = isset($sort_stars) ? "sort_stars=$sort_stars&sorted=$_GET[sorted]&" : "";
	$page_header = build_page_list( $page, MAX_PAGE_SIZE, $total_rows, $page_link_args );
	$out .= $rs . $page_header . make_table( array(
		"<a href=$PHP_SELF?sort_stars=star_id&sorted=$sorted&page=$page>Star ID</a>",
		"<a href=$PHP_SELF?sort_stars=star_name&sorted=$sorted&page=$page>Star Name</a>",
		"<a href=$PHP_SELF?sort_stars=link_1&sorted=$sorted&page=$page>Link 1</a>",
		"<a href=$PHP_SELF?sort_stars=link_2&sorted=$sorted&page=$page>Link 2</a>",
		"<a href=$PHP_SELF?sort_stars=link_3&sorted=$sorted&page=$page>Link 3</a>",
		"<a href=$PHP_SELF?sort_stars=link_4&sorted=$sorted&page=$page>Link 4</a>",
		"<a href=$PHP_SELF?sort_stars=link_5&sorted=$sorted&page=$page>Link 5</a>",
		"<a href=$PHP_SELF?sort_stars=link_6&sorted=$sorted&page=$page>Link 6</a>",
		"<a href=$PHP_SELF?sort_stars=metal&sorted=$sorted&page=$page>Metal</a>",
		"<a href=$PHP_SELF?sort_stars=fuel&sorted=$sorted&page=$page>Fuel</a>",
		"<a href=$PHP_SELF?sort_stars=darkmatter&sorted=$sorted&page=$page>Darkmatter</a>",
		"<a href=$PHP_SELF?sort_stars=wormhole&sorted=$sorted&page=$page>WormHole</a>",
		"<a href=$PHP_SELF?sort_stars=planetary_slots&sorted=$sorted&page=$page>Planetary<br />Slots</a>",
		"<a href=$PHP_SELF?sort_stars=unigen&sorted=$sorted&page=$page>Unigen<br />Planets</a>"));
	while($clan_star) {
		$clan_star['jump'] = "<a href=\"$url_prefix/location.php?subspace=$clan_star[star_id]\">Jump</a>";
		$clan_star['star_id'] = "<b class=b1>$clan_star[star_id]</b>";
		$out .= make_hash_row($clan_star);
		$clan_star = dbr(1);
	}
	$out .= "</table>" . $page_header;
} else {
	$out .= $page > 0 ? "No star systems exist on page " . ($page + 1) . '.' :
		'No star systems exist.  I suggest you run universe generation.';
}


print_page("Systems",$out);

?>
