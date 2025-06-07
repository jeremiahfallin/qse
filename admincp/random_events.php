<?php

include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>List All Random Events</h3>';
$ADODB_FETCH_MODE = 2;
if (isset($sort_events)) {
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	$order_by = "order by '$sort_events' $going";
} else {
	$order_by = "order by event_random asc, star_id asc";
}

define( 'MAX_PAGE_SIZE', 100 );
$page = isset($page) ? intval($page) : 0;
$offset = " limit " . MAX_PAGE_SIZE . " offset " . ($page * MAX_PAGE_SIZE);

define( 'RANDOM_EVENT_NONE', 0 );
define( 'RANDOM_EVENT_BLACK_HOLE', 1 );
define( 'RANDOM_EVENT_NEBULA', 2 );
define( 'RANDOM_EVENT_METAL_RUSH', 4 );
define( 'RANDOM_EVENT_SN_POSSIBLE', 5 );
define( 'RANDOM_EVENT_UNSTABLE_SN_REMNANT', 6 );
define( 'RANDOM_EVENT_ALIEN_HOME', 7 );
define( 'RANDOM_EVENT_ALIEN_COLONY', 8 );
define( 'RANDOM_EVENT_ALIEN_FRONTIER', 9 );
define( 'RANDOM_EVENT_SN_INITIATED', 10 );
define( 'RANDOM_EVENT_SN_PENDING', 11 );
define( 'RANDOM_EVENT_SOLAR_STORM', 12 );
define( 'RANDOM_EVENT_STABLE_SN_REMNANT', 14 );
$RANDOM_EVENT_NAMES = array(
	RANDOM_EVENT_NONE		 => "No random event",
	RANDOM_EVENT_BLACK_HOLE		 => "Black Hole",
	RANDOM_EVENT_NEBULA		 => "Nebula",
	RANDOM_EVENT_METAL_RUSH		 => "Metal Mining Rush",
	RANDOM_EVENT_SN_POSSIBLE	 => "Chance of a SuperNova",
	RANDOM_EVENT_UNSTABLE_SN_REMNANT => "Unstable SuperNova Remnant",
	RANDOM_EVENT_ALIEN_HOME		 => "Alien Controlled Home System",
	RANDOM_EVENT_ALIEN_COLONY	 => "Alien Controlled Colony System",
	RANDOM_EVENT_ALIEN_FRONTIER	 => "Alien Controlled Frontier System",
	RANDOM_EVENT_SN_INITIATED	 => "SuperNova initiated by device",
	RANDOM_EVENT_SN_PENDING		 => "SuperNova will occur soon",
	RANDOM_EVENT_SOLAR_STORM	 => "Solar Storm",
	RANDOM_EVENT_STABLE_SN_REMNANT	 => "Stable SuperNova Remnant" );

db(__FILE__,__LINE__,"select star_id,star_name,event_random from ${db_name}_stars where event_random != 0 $order_by $offset");
$star = dbr();
if ($star) {
	db2(__FILE__,__LINE__,"select count(*) from ${db_name}_stars where event_random != 0");
	$total_rows = dbr2();
	$total_rows = array_shift( $total_rows );
	$page_link_args = isset($sort_events) ? "sort_events=$sort_events&sorted=$_GET[sorted]&" : "";
	$page_header = build_page_list( $page, MAX_PAGE_SIZE, $total_rows, $page_link_args );
	$out .= $rs . $page_header . make_table( array(
		"<a href=\"$PHP_SELF?sort_events=star_id&sorted=$sorted&page=$page\">Star ID</a>",
		"<a href=\"$PHP_SELF?sort_events=star_name&sorted=$sorted&page=$page\">Star Name</a>",
		"<a href=\"$PHP_SELF?sort_events=event_random&sorted=$sorted&page=$page\">Random Event</a>" ));
	do {
		$star['jump'] = "<a href=\"$url_prefix/location.php?subspace=$star[star_id]\">Jump</a>";
		$star['star_id'] = "<b class=b1>$star[star_id]</b>";
		$event_name = "Unrecognized event #$star[event_random]";
		foreach ($RANDOM_EVENT_NAMES as $num => $name ) {
			if ( $star['event_random'] == $num ) {
				$event_name = $name;
				break;
			}
		}
		$star['event_random'] = $event_name;
		$out .= make_hash_row($star);
	} while ( $star = dbr(1) );
	$out .= "</table>" . $page_header;
} else {
	$out .= $page > 0 ? "No random events exist on page " . ($page + 1) . '.' : 'No random events exist.';
}

print_page('Random Event List',$out);
?>
