<?php

include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>List All Clans</h3>';
$ADODB_FETCH_MODE = 2;
if (isset($sort_clans)) {
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	$order_by = "order by '$sort_clans' $going";
} else {
	$order_by = "order by cid asc";
}

define( 'MAX_PAGE_SIZE', 100 );
$page = isset($page) ? intval($page) : 0;
$offset = " limit " . MAX_PAGE_SIZE . " offset " . ($page * MAX_PAGE_SIZE);

// It'd be nice to see the clan summary type info like % fighters, % cash, etc.
db(__FILE__,__LINE__,"select c.clan_id as cid, c.clan_name as cname, c.symbol as csym, u.login_name as lname, c.members as cmem, "
	. "c.sym_color as ccolor, c.clan_score as cscore, c.fighter_kills as ckills from ${db_name}_clans as c "
	. "left join ${db_name}_users as u on u.login_id = c.leader_id $order_by $offset");
$clan_info = dbr();
if ($clan_info) {
	db2(__FILE__,__LINE__,"select count(*) from ${db_name}_clans");
	$total_rows = dbr2();
	$total_rows = array_shift( $total_rows );
	$page_link_args = isset($sort_clans) ? "sort_clans=$sort_clans&sorted=$_GET[sorted]&" : "";
	$page_header = build_page_list( $page, MAX_PAGE_SIZE, $total_rows, $page_link_args );
	$out .= $rs . $page_header . make_table( array(
		"<a href=\"$PHP_SELF?sort_clans=cid&sorted=$sorted&page=$page\">Clan ID</a>",
		"<a href=\"$PHP_SELF?sort_clans=cname&sorted=$sorted&page=$page\">Clan Name</a>",
		"<a href=\"$PHP_SELF?sort_clans=csym&sorted=$sorted&page=$page\">Symbol</a>",
		"<a href=\"$PHP_SELF?sort_clans=lname&sorted=$sorted&page=$page\">Leader</a>",
		"<a href=\"$PHP_SELF?sort_clans=cmem&sorted=$sorted&page=$page\">Members</a>",
		"<a href=\"$PHP_SELF?sort_clans=cscore&sorted=$sorted&page=$page\">Score</a>",
		"<a href=\"$PHP_SELF?sort_clans=ckills&sorted=$sorted&page=$page\">Fighters Killed</a>" ));
	do {
		$clan_info['details'] = "<a href=\"$url_prefix/clan.php?clan_info=1&target=$clan_info[cid]\">Details</a>";
		// Uncomment forum link if/when admin can access clan forums this way
		//$clan_info['forum'] = "<a href=\"$url_prefix/forum_clan.php?clanid=$clan_info[cid]\">Forum</a>";
		$clan_info['cid'] = "<b class=b1>$clan_info[cid]</b>";
		$clan_info['csym'] = "<font color=\"#$clan_info[ccolor]\">$clan_info[csym]</font>";
		unset( $clan_info['ccolor'] );
		$out .= make_hash_row($clan_info);
	} while ( $clan_info = dbr(1) );
	$out .= "</table>" . $page_header;
	print_page("Clan List",$out);
} else {
	$out .= $page > 0 ? "No clans exist on page " . ($page + 1) . '.' : 'No clans exist';
}

print_page('Clan List',$out);
?>
