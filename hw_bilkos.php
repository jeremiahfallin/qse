<?php
require("user.inc.php");


sudden_death_check($user);

db(__FILE__,__LINE__,"select port_id from ${db_name}_ports where location = '${user['location']}'");
$ports = dbr();
if(($user['location'] != $user['race']) && ($user['login_id'] != 1)){
	print_page("Error","Bilkos Auction House does not exist at this location.");
}

if($user['location'] == $user['race']){
	$rs = "<p><a href=hw.php>Return to Homeworld</a>";
}
// percentage new bids must increase over old ones.
$rate = 5;

// work out the number of seconds an item is to remain in bilkos for.
$bilkos_seconds = $bilkos_time * 3600;

$text .= "<br>Select a catogory to see lots presently available. New lots arrive hourly.<br>";
db(__FILE__,__LINE__,"select count(item_id),item_type from ${db_name}_bilkos where active = 1 && timestamp + $bilkos_seconds > ".time()." group by item_type");
for($i=1;$i<=5;$i++){
	$count=dbr();
	if($count[item_type]){
		$out[$count[item_type]] = $count[0];
	}
}
for($i=1;$i<=5;$i++){
	if(!$out[$i]){
		$out[$i] = 0;
	}
}
$text .= "<br><a href=hw_bilkos.php?view=2>Equipment</a> - (<b>$out[2]</b>)";
$text .= "<br><a href=hw_bilkos.php?view=3>Upgrades</a> - (<b>$out[3]</b>)";
$text .= "<br><a href=hw_bilkos.php?view=4>Misc</a> - (<b>$out[4]</b>)";
$text .= "<br><a href=hw_bilkos.php?view=5>Planetary</a> - (<b>$out[5]</b>)";


db(__FILE__,__LINE__,"select count(item_id) from ${db_name}_bilkos where active=0 && bidder_id='$user[login_id]'");
$do_now=dbr();
if($do_now[0]){
	$text .= "<p><a href=hw_bilkos.php?show_won=1>Collect Item(s)</a> - (<b>$do_now[0]</b>)";
}

$text .= "<p>";

/*
upgrades
up2 = terra maelstrom
all below require 1 free upgrade slot:
upbs = battleship
upfig500 = increase fighter cap by 500
upfig1500 = increase fighter cap by 1500
upattack = +200 shield cap. +400 fighter cap.

equip
warpack = 2alpha + 4 gammas
deltabomb = 1 Delta Bomb

planetary
4-9 = shield capacity =X000, charge rate = *X
MLPad = Missile Launch Pad.

misc
10 - 80 = X turns.
*/
if($collect){
	db(__FILE__,__LINE__,"select item_type,bidder_id,item_code,active from ${db_name}_bilkos where item_id = $collect");
	$item=dbr();
	$all_done = 0;
	if($user[login_id] != $item[bidder_id]){
		$text .= "You did not win this item.";
	} elseif($item[active] == 1) {
		$text .= "You have not won this item yet";
	} elseif($user[location] != $user[race]) {
		$text .= "You may only collect this item from the Bilkos Store on Earth, as that is where the central repository of purchased goods is stored.";
	} else {
		if($item['item_type'] == 3){ #upgrades
			if($item['item_code'] == "up2"){ #terra maelstrom
				if(eregi("sw",$user_ship[config])) {
					$text .= "This ship has already had its Super Weapon upgraded.";
				} elseif(eregi("sv",$user_ship[config])) {
					$text .= "Your Quark Disrupter has now been upgraded to a Terra Maelstrom... Have Fun!";
					$user_ship[config] = $user_ship[config].":sw";
					dbn(__FILE__,__LINE__,"update ${db_name}_ships set config = '$user_ship[config]' where ship_id = '$user_ship[ship_id]'");
					$all_done = 1;
				} else {
					$text .= "You must be commanding a ship with a Quark Disrupter on it to be able to collect this item.";
				}
			} elseif(($user_ship[upgrades] < 1) && ($item[item_code] != "upgrade")) { #Ensure enough free slots.
				$text .= "You must be commanding a ship with at least one upgrade pod free to collect this upgrade.";
			} elseif($item[item_code] == "upbs"){
				if(eregi("bs",$user_ship[config])){
					$text .= "This ship already has the battleship upgrade. Please command a different ship and try again.";
				} else {
					$text .= "This ship has now been upgraded to <b class=b1>Battleship Status</b> which means more damage done when attacking, and more shields generated per hour.";
					$user_ship[config] = $user_ship[config].":bs";
					dbn(__FILE__,__LINE__,"update ${db_name}_ships set config = '$user_ship[config]', upgrades = upgrades -1 where ship_id = '$user_ship[ship_id]'");
					$all_done = 1;
				}
			} elseif($item[item_code] == "fig500"){
				$text .= "Here's 500 more Fighter Capacity.";
				$user_ship[max_fighters] = $user_ship[max_fighters] + 500;
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set max_fighters = '$user_ship[max_fighters]', upgrades = upgrades -1 where ship_id = '$user_ship[ship_id]'");
				$all_done = 1;
			} elseif($item[item_code] == "fig1500"){
				$text .= "Here's 1500 more Fighter Capacity.";
				$user_ship[max_fighters] = $user_ship[max_fighters] + 1500;
				dbn(__FILE__,__LINE__,"update ${db_name}_ships set max_fighters = '$user_ship[max_fighters]', upgrades = upgrades -1 where ship_id = '$user_ship[ship_id]'");
				$all_done = 1;
			}elseif($item[item_code] == "car1500"){
                                $text .= "Here's 1500 more Cargo Capacity.";
                                $user_ship[cargo_bays] = $user_ship[cargo_bays] + 1500;
                                $user_ship[empty_bays] = empty_bays();
                                dbn(__FILE__,__LINE__,"update ${db_name}_ships set cargo_bays = '$user_ship[cargo_bays]', upgrades = upgrades -1 where ship_id = '$user_ship[ship_id]'");
                                $all_done = 1;
                        }elseif($item[item_code] == "car500"){
                                $text .= "Here's 500 more Cargo Capacity.";
                                $user_ship[cargo_bays] = $user_ship[cargo_bays] + 500;
                                $user_ship[empty_bays] = empty_bays();
                                dbn(__FILE__,__LINE__,"update ${db_name}_ships set cargo_bays = '$user_ship[cargo_bays]', upgrades = upgrades -1 where ship_id = '$user_ship[ship_id]'");
                                $all_done = 1;
                        }elseif($item[item_code] == "she1500"){
                                $text .= "Here's 1500 more Shield Capacity.";
                                $user_ship[max_shields] = $user_ship[max_shields] + 1500;
                                dbn(__FILE__,__LINE__,"update ${db_name}_ships set max_shields = '$user_ship[max_shields]', upgrades = upgrades -1 where ship_id = '$user_ship[ship_id]'");
                                $all_done = 1;
                        }elseif($item[item_code] == "she500"){
                                $text .= "Here's 500 more Shield Capacity.";
                                $user_ship[max_shields] = $user_ship[max_shields] + 500;
                                dbn(__FILE__,__LINE__,"update ${db_name}_ships set max_shields = '$user_ship[max_shields]', upgrades = upgrades -1 where ship_id = '$user_ship[ship_id]'");
                                $all_done = 1;
                        }elseif($item[item_code] == "upgrade"){
                                $text .= "Here's 5 Upgrade Pods.";
                                $user_ship[upgrades] = $user_ship[upgrades] + 5;
                                dbn(__FILE__,__LINE__,"update ${db_name}_ships set upgrades = $user_ship[upgrades] where ship_id = '$user_ship[ship_id]'");
                                $all_done = 1;
                       }elseif($item[item_code] == "Orbit"){
                                if(eregi("ob",$user_ship[config]))
                                        {
                                        $text .= "This ship already has the upgrade!";
                                        }
                                elseif($user_ship[max_fighters] <= 0){
                                        $text .= "Your ship needs fighters to upgrade";
                                        }
                                else
                                        {
                                        $text .= "We've installed your new Targeting System.";
                                        $user_ship[config] .= ":ob";
                                        dbn(__FILE__,__LINE__,"update ${db_name}_ships set config = '$user_ship[config]' where ship_id = '$user_ship[ship_id]'");
                                        $all_done = 1;
                                        }
                        }elseif($item[item_code] == "attack_pack"){
				if(eregi("sj",$user_ship[config])){
					$text .= "Ships with SubSpace Jump Drives cannot have shields on them.";
				} else {
					$text .= "Your ship has been upgraded with a further 200 shield cap, and 700 fighter cap.";
					$user_ship[max_fighters] = $user_ship[max_fighters] + 700;
					$user_ship[max_shields] = $user_ship[max_shields] + 200;
					dbn(__FILE__,__LINE__,"update ${db_name}_ships set max_fighters = '$user_ship[max_fighters]',max_shields = '$user_ship[max_shields]', upgrades = upgrades -1 where ship_id = '$user_ship[ship_id]'");
					$all_done = 1;
				}
			}

		} elseif($item[item_type] == 2){ // equipment
			if($item[item_code] == "warpack"){
				$text .= "Here's your Warpack. Enjoy.";
				dbn(__FILE__,__LINE__,"update ${db_name}_users set gamma = gamma+'4', alpha=alpha+2 where login_id = '$item[bidder_id]'");
				$all_done = 1;
			}elseif($item[item_code] == "deltabomb"){
				if($user[delta] == 1){
					$text .= "Sorry. You may only have one Delta bomb at a time. These things <b>are</b> Contraband you know!";
				} else {
					$text .= "Here's your Delta Bomb. Enjoy.";
					dbn(__FILE__,__LINE__,"update ${db_name}_users set delta = 1 where login_id = '$item[bidder_id]'");
					$all_done = 1;
				}
			}
		} elseif($item[item_type] == 4){ // misc
			$text .= "Here are your <b>$item[item_code]</b> turns.";
			$user[turns] += $item[item_code];
			dbn(__FILE__,__LINE__,"update ${db_name}_users set turns = turns+'$item[item_code]' where login_id = '$item[bidder_id]'");
			$all_done = 1;
		} elseif($item[item_type] == 5){ // Planetary
			if($destination){
				db(__FILE__,__LINE__,"select owner_id,shield_gen,planet_name,planet_id,launch_pad from ${db_name}_planets where planet_id != 1 && planet_id = '$destination'");
				$planets=dbr();
				if($planets[owner_id] != $user[login_id]){
					$text .= "That Planet does not belong to you.";
				} elseif($item[item_code] >3 && $item[item_code] <10) {
					if($planets[shield_gen] > 3) {
						$text .= "This planet has already had its shield generater upgraded.";
					}
					$charge_cap = $item[item_code] * 1000;
					$text .= "Shield Generater on <b class=b1>$planets[planet_name]</b> is now lvl<b>$item[item_code]</b>.<br>This means Shield Capacity of <b>$charge_cap</b> and <b>$item[item_code]</b>* Shield Generation Rate.";
					dbn(__FILE__,__LINE__,"update ${db_name}_planets set shield_gen = '$item[item_code]' where planet_id = '$planets[planet_id]'");
					$all_done = 1;
				} elseif($item[item_code] == "MLPad") {
					if($planets[launch_pad] != 0) {
						$text .= "This planet already has a <b class=b1>Missile Launch Pad</b>";
					}
					$text .= "<b class=b1>Missile Launch Pad</b> fitted on <b class=b1>$planets[planet_name]</b>.";
					dbn(__FILE__,__LINE__,"update ${db_name}_planets set launch_pad = '1' where planet_id = '$planets[planet_id]'");
					$all_done = 1;
				}
			} else {
				db(__FILE__,__LINE__,"select planet_name,planet_id from ${db_name}_planets where planet_id != 1 && owner_id = '$user[login_id]'");
				$planets=dbr();
				if(!$planets){
					$text .= "You have no planets and so cannot collect this lot.";
				} else {
					$text .= "Select Planet to install this Lot on.";
					$text .= "<form method=post action=hw_bilkos.php name=despatch_form>";
					$text .= "<input type=hidden name=collect value=$collect>";
					$text .= "<select name=destination>";
					while($planets){
						$text .= "<option value=$planets[planet_id]> $planets[planet_name] ";
						$planets=dbr();
					}
					$text .= "</select>";
					$text .= "<p><INPUT type=submit value=Install></form><p>";
				}
			}
		}

		if($all_done==1){ // remove lot from auction
			dbn(__FILE__,__LINE__,"delete from ${db_name}_bilkos where item_id = $collect");
		}
	}

} elseif($bid){
	db(__FILE__,__LINE__,"select * from ${db_name}_bilkos where item_id = $bid");
	$item=dbr();
	if($item[active] = 0){
		$text .= "This item is not for sale.";
	} elseif($item[timestamp] + $bilkos_seconds < time()){
		$text .= "This item has been removed from sale. Hard luck.";
	} elseif($new_bid){
		settype($new_bid, "integer");
		$new_price = round(($item[going_price] /100) * $rate) + $item[going_price];
		if($new_bid > $user[cash]){
			$text .= "Sorry, you do not have enough money";
		} elseif($new_price > $new_bid) {
			$text .= "That bid is not large enough. Minimum bid increases of <b>$rate%</b> are accepted.<br>Minimum bid on this item is <b>$new_price</b>";
		} elseif($new_bid < 1) {
			$text .= "Thats a ridiculous bid.";
//		} elseif($numships[0] > $max_ships && $item[item_type] == 1) {
//			$text .= "You may not bid for a ship as you are already at the Ship Limit.";
		} else {
			if($item[bidder_id] > 0){
				dbn(__FILE__,__LINE__,"update ${db_name}_users set cash=cash+'$item[going_price]' where login_id='$item[bidder_id]'");
				dbn(__FILE__,__LINE__,"insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'Bilkos','$user[login_id]','$item[bidder_id]','Your bid on the <b class=b1>$item[item_name]</b> has been beaten by <b class=b1>$user[login_name]</b> who has put a new bid of <b>$new_bid</b> Credits on the item.<p>The lot will remain open for a further <b>24hrs</b>. If there are no new bidders, then <b class=b1>$user[login_name]</b> will take the lot.<p>You have been refunded the money you deposited on the lot.')");
			}
			dbn(__FILE__,__LINE__,"update ${db_name}_bilkos set timestamp=".time().", bidder_id=$user[login_id],going_price=$new_bid where item_id = $bid;");
			take_cash($new_bid);
			$text .= "<br>Bid successful. <br>Provided no-one out bids you within the next <b>24hrs</b>, you will soon be the proud new owner of a <b class=b1>$item[item_name]</b>.";
		}
	} else {
		$new_price = round(($item[going_price] /100) * $rate) + $item[going_price];
		$text .= "Please place a bid for this <b class=b1>$item[item_name]</b>.<br>Present bid stands at: <b>"
			. number_format($item[going_price]) . "</b><br>New bid must be at least: <b>"
			. number_format($new_price) . "</b>";
		$text .= "<form method=post action=hw_bilkos.php name=bid_form>";
		$text .= "<input type=hidden name=bid value=$bid>";
		$text .= "<input type=text name=new_bid value=\"$new_price\" size=10>";
		$text .= " - <INPUT type=submit value=Bid></form><p>";
		$rs = "<a href=hw_bilkos.php>Back to Bilkos</a>";
		print_page("Make a Bid",$text);
	}
} elseif($view){ // Show all items in a particular catagory.
	$ADODB_FETCH_MODE = 2;
	$text .= "Current stock:<p>";
	db2(__FILE__,__LINE__,"select item_name,descr,timestamp,going_price,bidder_id,item_id from ${db_name}_bilkos where item_type = $view && active=1 && timestamp + '$bilkos_seconds' > ".time()." order by timestamp asc, item_name asc");
	$items=dbr2();

	if(!$items){
		$text .= "None - Sorry.";
	} else {
		$text .= make_table(array("Item Name","Description","Open Till","Present Price","Present Bidder"));
		while($items) {
			$items[going_price] = number_format($items[going_price]);
			if($items[bidder_id] > 0){
				db(__FILE__,__LINE__,"select login_name,login_id,clan_sym,clan_sym_color from ${db_name}_users where login_id = $items[bidder_id]");
				$bidder=dbr(1);
				$items[bidder_id] = print_name($bidder);
				$items[timestamp] = date( "M d - H:i",$items[timestamp]+$bilkos_seconds);
			} else {
				$items[bidder_id] = "None Yet";
				$items[timestamp] = date( "M d - H:i",$items[timestamp] + ($bilkos_seconds * 2) );
			}
			$items[item_id] = " - <a href=hw_bilkos.php?bid=$items[item_id]>Bid</a>";
			$text .= make_hash_row($items);
			$items = dbr2();
		}
		$text .= "</table>";
	}

} elseif($show_won) { // Show items user has won.
	db2(__FILE__,__LINE__,"select item_name,item_type,going_price,item_id from ${db_name}_bilkos where active=0 && bidder_id='$user[login_id]'");
	$collect=dbr2(1);
	if($collect){
		$text .= "You have at least one item to collect, which you have already paid for. Click the link next to the item to collect it.<br><br>";

		$text .= make_table(array("Item Name","Item Type","Price Paid"));
		while($collect) {
			if($collect[item_type] == 2){
				$collect[item_type] = "Equipment";
			} elseif($collect[item_type] == 3){
				$collect[item_type] = "Upgrade";
			} elseif($collect[item_type] == 4){
				$collect[item_type] = "Misc";
			} elseif($collect[item_type] == 5){
				$collect[item_type] = "Planetary";
			}
			$text .= make_hash_row(array($collect[item_name],$collect[item_type],number_format($collect[going_price]),
				" - <a href=hw_bilkos.php?collect=$collect[item_id]>Collect</a>"));
			$collect = dbr2(1);
		}
		$text .= "</table>";
	} else {
		$text .= "<br>You have no items to collect.";
	}

}

print_page("Bilkos Auction House",$text);

?>
