<?php

include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$user_ship_config = get_config($user['ship_id']);

$out .= '<h3>List All Ships</h3>';
$ADODB_FETCH_MODE = 2;
if (isset($sort_ships)) {
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	$order_by = "order by '$sort_ships' $going";
} else {
	$order_by = "order by login_name asc, class_name asc, location asc, sid asc";
}

define( 'MAX_PAGE_SIZE', 100 );
$page = isset($page) ? intval($page) : 0;
$offset = " limit " . MAX_PAGE_SIZE . " offset " . ($page * MAX_PAGE_SIZE);

// Build SQL select queries for attack/defense bonus fields
unset( $bonus );
define( 'ATTACK_BONUS_TYPE', 6 );
define( 'DEFENSE_BONUS_TYPE', 7 );
foreach ( array( ATTACK_BONUS_TYPE => "abonus", DEFENSE_BONUS_TYPE => "dbonus" ) as $type => $field ) {
	db(__FILE__,__LINE__,"select sql_name, damage from upgrade_list where type = $type");
	while($uplist = dbr()) {
		if (isset($bonus[$type])) {
			$bonus[$type] .= " + ";
		}
		$bonus[$type] .= "(up.$uplist[sql_name] * $uplist[damage])";
	}
	if (isset($bonus[$type])) {
		$bonus[$type] = ", ($bonus[$type]) as $field";
	}
}

db(__FILE__,__LINE__,"select s.ship_id as sid, login_name, ship_name, class_name, location, fighters, shields, config "
	. "${bonus[ATTACK_BONUS_TYPE]} ${bonus[DEFENSE_BONUS_TYPE]} from ${db_name}_ships as s "
	. "left join ${db_name}_upgrade_units as up on up.ship_id = s.ship_id where s.ship_id != 1 group by s.ship_id "
	. "$order_by $offset");
$ship = dbr();
if ($ship) {
	db2(__FILE__,__LINE__,"select count(*) from ${db_name}_ships where ship_id != 1");
	$total_rows = dbr2();
	$total_rows = array_shift( $total_rows );
	$page_link_args = isset($sort_ships) ? "sort_ships=$sort_ships&sorted=$_GET[sorted]&" : "";
	$page_header = build_page_list( $page, MAX_PAGE_SIZE, $total_rows, $page_link_args );
	$out .= $rs . $page_header . make_table( array(
		"<a href=\"$PHP_SELF?sort_ships=sid&sorted=$sorted&page=$page\">Ship ID</a>",
		"<a href=\"$PHP_SELF?sort_ships=login_name&sorted=$sorted&page=$page\">Owner Name</a>",
		"<a href=\"$PHP_SELF?sort_ships=ship_name&sorted=$sorted&page=$page\">Ship Name</a>",
		"<a href=\"$PHP_SELF?sort_ships=class_name&sorted=$sorted&page=$page\">Ship Class</a>",
		"<a href=\"$PHP_SELF?sort_ships=location&sorted=$sorted&page=$page\">Location</a>",
		"<a href=\"$PHP_SELF?sort_ships=fighters&sorted=$sorted&page=$page\">Fighters</a>",
		"<a href=\"$PHP_SELF?sort_ships=shields&sorted=$sorted&page=$page\">Shields</a>",
		"<a href=\"$PHP_SELF?sort_ships=config&sorted=$sorted&page=$page\">Configuration</a>",
		"<a href=\"$PHP_SELF?sort_ships=abonus&sorted=$sorted&page=$page\">A Bonus</a>",
		"<a href=\"$PHP_SELF?sort_ships=dbonus&sorted=$sorted&page=$page\">D Bonus</a>" ));
	do {
		$ship['jump'] = "<a href=\"$url_prefix/location.php?subspace=$ship[location]\">Jump</a>";
		$ship['sid'] = "<b class=b1>$ship[sid]</b>";
		$out .= make_hash_row($ship);
	} while ( $ship = dbr(1) );
	$out .= "</table>" . $page_header;
} else {
	$out .= $page > 0 ? "No ships exist on page " . ($page + 1) . '.' : 'No ships exist.';
}

print_page('Ship List',$out);
?>
