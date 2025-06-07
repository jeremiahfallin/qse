<?php

include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

$out .= '<h3>List All Relations</h3>';

db(__FILE__,__LINE__,"select * from ${db_name}_clan_relations order by clan_id asc, v_clan_id asc");
$rel = dbr();
if ($rel) {
	unset($relations);
	do {
		$relations[$rel['clan_id']][$rel['v_clan_id']] = $rel['nap_agree'];
	} while ($rel = dbr(1));

	unset($clans);
	db(__FILE__,__LINE__,"select clan_id, clan_name, symbol, sym_color from ${db_name}_clans order by clan_id asc");
	while ($clan=dbr()) {
		$clans[$clan['clan_id']] = "$clan[clan_name] (<font color=$clan[sym_color]>$clan[symbol]</font>)";
	}

	define( "RELATION_NONE",    0 );
	define( "RELATION_ENEMY",   1 );
	define( "RELATION_NEUTRAL", 2 );
	define( "RELATION_ALLY",    3 );
	$REL_STR[RELATION_NONE]    = "";
	$REL_STR[RELATION_ENEMY]   = "<b>&lt;<font color=#FF0000>E</font>&gt;</b>";
	$REL_STR[RELATION_NEUTRAL] = "<b>&lt;<font color=#0000FF>N</font>&gt;</b>";
	$REL_STR[RELATION_ALLY]    = "<b>&lt;<font color=#FFFF00>A</font>&gt;</b>";
	$out .= $rs.make_table( array( "Clan", "With ==&gt;", "With &lt;==", "Clan" ) );
	foreach ( $relations as $cid => $data ) {
		foreach ( $data as $with => $level ) {
			if ( ($cid > $with) && (isset($relations[$with][$cid]))) {
				// Since we ordered by clan_id, skip the reverse relationships we already printed
			} else {
				$withlevel = isset( $relations[$with][$cid] ) ? $relations[$with][$cid] : RELATION_NONE;
				$out .= make_hash_row( array( "$clans[$cid]", "$REL_STR[$level]",
					"$REL_STR[$withlevel]",	"$clans[$with]" ) );
			}
		}
	}
	$out .= '</table>';
} else {
	$out .= 'No relations exist';
}

print_page('Relations List',$out);
?>
