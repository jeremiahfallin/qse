<?php





include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}
db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'era'");
$races2 = dbr();
$era = $races2['value'];
#change ships available to players.
	if($submit_ships){
		$out .= "Selected ships have now been made available to the players. The rest of the ships are un-available.";
		db2(__FILE__,__LINE__,"select count(type_id) from ship_types where type_id > 2 && auction=0");
		$ships=dbr2();
		dbn(__FILE__,__LINE__,"delete from ${db_name}_admin_ships where ship_type_id > 2");
		$counter = $ships[0] + 250;
		db(__FILE__,__LINE__,"select type_id from ship_types where type_id > 2 && auction = 0 && era = $era");
		while($list_ships = dbr()){

			if($add_ship[$list_ships[type_id]] == 0){
				dbn(__FILE__,__LINE__,"insert into ${db_name}_admin_ships values ('$list_ships[type_id]',0)");
			} else {
				dbn(__FILE__,__LINE__,"insert into ${db_name}_admin_ships values ('$list_ships[type_id]',1)");
			}
		}
	} else {
		$out .= "Note: Ships will still turn up in Sol, but will not be purchasable.";
		$out .= "<br>Select the ships that you would like users to be able to use within the game:";
		$out .= "\n<form name=select_ships action=$PHP_SELF method=POST>";
		$out .= "\n<input type=hidden name=admin_choose value='1'>";
		$out .= "\n<input type=hidden name=submit_ships value='1'>";
		$out .= make_table(array("Avail","<b class=b1>Ship Name</b>","Fighters","Shields","Bays",
			"Mining","Move","Config","Upgs","Race","Seller","Cost","Tech","Points"));
		db(__FILE__,__LINE__,"select * from ship_types s, ${db_name}_admin_ships a "
			. "where s.type_id > 2 && a.ship_type_id = s.type_id && s.auction = 0 && s.era = $era "
			. "order by s.type_id");
		while($list_ships = dbr()){
			$out_arr = array();
			if($list_ships['status'] == 1){
				$out_arr[] = "<input type=checkbox name=add_ship[$list_ships[type_id]] value=$list_ships[type_id] CHECKED>";
			} else {
				$out_arr[] = "<input type=checkbox name=add_ship[$list_ships[type_id]] value=$list_ships[type_id]>";
			}
			$out_arr[] = $list_ships['name'];
			$out_arr[] = $list_ships['fighters'] . ' / ' . $list_ships['max_fighters'];
			$out_arr[] = $list_ships['max_shields'];
			$out_arr[] = $list_ships['cargo_bays'];
			$out_arr[] = $list_ships['mine_rate_metal'] . ' / ' . $list_ships['mine_rate_fuel']
				. ' / ' . $list_ships['mine_rate_darkmatter'];
			$out_arr[] = $list_ships['move_turn_cost'];
			$out_arr[] = $list_ships['config'];
			$out_arr[] = $list_ships['upgrades'];
			$out_arr[] = $list_ships['race'] == 1 ? 'H' : ( $list_ships['race'] == 2 ? 'BS' : 'All' );
			$out_arr[] = $list_ships['purchase_loc'] == 2 ? 'BM' :
				( $list_ships['purchase_loc'] == 3 ? 'Alien' : 'Home' );
			$out_arr[] = $list_ships['cost'];
			$out_arr[] = $list_ships['tcost'];
			$out_arr[] = $list_ships['point_value'];
			$out .= make_hash_row( $out_arr );
		}
		$out .= "\n</table>";
		$out .= "\n<br><a href=javascript:TickAll(\"select_ships\")>Invert Ship Selection</a>";
		$out .= "\n<p><INPUT type=submit value='Submit'></form>";
	}
print_page('Edit Ship Types',$out);
?>
