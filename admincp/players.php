<?php

include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>List All Players</h3>';
$ADODB_FETCH_MODE = 2;
if (isset($sort_players)) {
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	$order_by = "order by '$sort_players' $going";
} else {
	$order_by = "order by login_id asc";
}

db(__FILE__,__LINE__,"select u.login_id, u.login_name, u.turns, u.cash, u.tech, "
	. "NULL as numships, NULL as sfigs, "
	. "count(p.planet_id) as numplan, sum(p.fighters) as pfigs, sum(p.colon) as pcols, u.score, u.bounty, "
//	. "u.genesis, u.terra_imploder, u.sn_effect, u.alpha, u.gamma, u.delta, u.grav_mine, u.hornet_mine, u.leech "
	. "u.genesis, u.terra_imploder, u.sn_effect, u.alpha, u.gamma, u.delta "
	. "from ${db_name}_users u "
	. "left join ${db_name}_planets p on p.owner_id = u.login_id "
	. "where u.login_id > 5 group by u.login_id $order_by");
$player = dbr();
if ($player) {
	$out .= $rs . make_table( array(
		"<a href=\"$PHP_SELF?sort_players=login_id&sorted=$sorted\">ID</a>",
		"<a href=\"$PHP_SELF?sort_players=login_name&sorted=$sorted\">Name</a>",
		"<a href=\"$PHP_SELF?sort_players=turns&sorted=$sorted\">Turns</a>",
		"<a href=\"$PHP_SELF?sort_players=cash&sorted=$sorted\">Cash</a>",
		"<a href=\"$PHP_SELF?sort_players=tech&sorted=$sorted\">Tech</a>",
		"Ships",
		"Ship Figs",
		"<a href=\"$PHP_SELF?sort_players=numplan&sorted=$sorted\">Planets</a>",
		"<a href=\"$PHP_SELF?sort_players=pfigs&sorted=$sorted\">Plan Figs</a>",
		"<a href=\"$PHP_SELF?sort_players=pcols&sorted=$sorted\">Plan Cols</a>",
		"<a href=\"$PHP_SELF?sort_players=score&sorted=$sorted\">Total Score</a>",
		"<a href=\"$PHP_SELF?sort_players=bounty&sorted=$sorted\">Bounty</a>",
		"<a href=\"$PHP_SELF?sort_players=genesis&sorted=$sorted\">GD</a>",
		"<a href=\"$PHP_SELF?sort_players=terra_imploder&sorted=$sorted\">TI</a>",
		"<a href=\"$PHP_SELF?sort_players=alpha&sorted=$sorted\">AB</a>",
		"<a href=\"$PHP_SELF?sort_players=gamma&sorted=$sorted\">GB</a>",
		"<a href=\"$PHP_SELF?sort_players=delta&sorted=$sorted\">DB</a>",
		"<a href=\"$PHP_SELF?sort_players=sn_effect&sorted=$sorted\">SN</a>" ));
//		"<a href=\"$PHP_SELF?sort_players=grav_mine&sorted=$sorted\">GM</a>",
//		"<a href=\"$PHP_SELF?sort_players=hornet_mine&sorted=$sorted\">HM</a>",
//		"<a href=\"$PHP_SELF?sort_players=leech&sorted=$sorted\">LE</a>" ));
	$totals = array();
	foreach ( $player as $k => $v ) {
		$totals[$k] = 0;
	}

	do {
		db2(__FILE__,__LINE__,"select count(ship_id) as numships, sum(fighters) as sfigs "
			. "from ${db_name}_ships where login_id = '$player[login_id]'");
		$ships = dbr2();
		if ( $ships['numships'] > 0 ) {
			$player['numships'] = $ships['numships'];
		}
		$player['sfigs'] = $ships['sfigs'];
		if ( $player['numplan'] == 0 ) {
			$player['numplan'] = NULL;
		}
		$player['login_name'] = "<a href=\"$url_prefix/player_info.php?target=$player[login_id]\">$player[login_name]</a>";
		$player['login_id'] = "<b class=b1>$player[login_id]</b>";
		$out .= make_hash_row($player);
		foreach ( $player as $k => $v ) {
			if ( is_numeric( $v ) ) {
				$totals[$k] += $v;
			}
		}
	} while ( $player = dbr(1) );

	$out .= "\n<tr class=\"tr_bg\">";
	$totals['login_id'] = "<b class=b1>Total</b>";
	$totals['login_name'] = NULL;
	foreach ( $totals as $v ) {
		$out .= "\n<td><b>$v</b></td>";
	}
	$out .= "\n</tr>";
	$out .= "</table>";
} else {
	$out .= 'No players exist';
}

print_page('Players List',$out);
?>
