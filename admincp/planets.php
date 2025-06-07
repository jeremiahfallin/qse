<?php

include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>List All Planets</h3>';
$ADODB_FETCH_MODE = 2;
if(isset($sort_planets)){
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	$order_by = "order by '$sort_planets' $going";
} else {
	$order_by = "order by owner_name asc, fighters desc, planet_name asc";
}

define( 'MAX_PAGE_SIZE', 100 );
$page = isset($page) ? intval($page) : 0;
$offset = " limit " . MAX_PAGE_SIZE . " offset " . ($page * MAX_PAGE_SIZE);

db(__FILE__,__LINE__,"select owner_name,planet_name,location,fighters,colon,cash,tech,metal,fuel,elect,organ,darkmatter "
	. "from ${db_name}_planets where location != 1 && planet_type >= 0 && unigen = 0 $order_by $offset");
$clan_planet = dbr();
if($clan_planet) {
	db2(__FILE__,__LINE__,"select count(*) from ${db_name}_planets where location != 1 && planet_type >= 0 && unigen = 0");
	$total_rows = dbr2();
	$total_rows = array_shift( $total_rows );
	$page_link_args = isset($sort_planets) ? "sort_planets=$sort_planets&sorted=$_GET[sorted]&" : "";
	$page_header = build_page_list( $page, MAX_PAGE_SIZE, $total_rows, $page_link_args );
	$out .= $rs . $page_header . make_table( array(
		"<a href=$PHP_SELF?sort_planets=owner_name&sorted=$sorted&page=$page>Planet Owner</a>",
		"<a href=$PHP_SELF?sort_planets=planet_name&sorted=$sorted&page=$page>Planet Name</a>",
		"<a href=$PHP_SELF?sort_planets=location&sorted=$sorted&page=$page>Location</a>",
		"<a href=$PHP_SELF?sort_planets=fighters&sorted=$sorted&page=$page>Fighters</a>",
		"<a href=$PHP_SELF?sort_planets=colon&sorted=$sorted&page=$page>Colonists</a>",
		"<a href=$PHP_SELF?sort_planets=cash&sorted=$sorted&page=$page>Cash</a>",
		"<a href=$PHP_SELF?sort_planets=tech&sorted=$sorted&page=$page>Tech</a>",
		"<a href=$PHP_SELF?sort_planets=metal&sorted=$sorted&page=$page>Metal</a>",
		"<a href=$PHP_SELF?sort_planets=fuel&sorted=$sorted&page=$page>Fuel</a>",
		"<a href=$PHP_SELF?sort_planets=elect&sorted=$sorted&page=$page>Electronics</a>",
		"<a href=$PHP_SELF?sort_planets=organ&sorted=$sorted&page=$page>Organics</a>",
		"<a href=$PHP_SELF?sort_planets=darkmatter&sorted=$sorted&page=$page>Darkmatter</a>"));
	while($clan_planet) {
		$clan_planet['jump'] = "<a href=\"$url_prefix/location.php?subspace=$clan_planet[location]\">Jump</a>";
		$clan_planet['owner_name'] = "<b class=b1>$clan_planet[owner_name]</b>";
		$out .= make_hash_row($clan_planet);
		$clan_planet = dbr(1); //remove 1?
	}
	$out .= "</table>" . $page_header;
} else {
	$out .= $page > 0 ? "No planets exist on page " . ($page + 1) . '.' : 'No planets exist.';
}

print_page('Planet List',$out);
?>
