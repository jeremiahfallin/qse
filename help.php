<?php

require("common.inc.php");

$rs = "<br /><br /><a href=#top>Top</a><br />";

#$link = mysql_connect("$database_host","$database_user","$database_password");
#mysql_select_db(__FILE__,__LINE__,$database);
$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);


if($db_name){
	# all variables now taken directly from the DB with each request
	db2(__FILE__,__LINE__,"select name,value from ${db_name}_db_vars order by name");
	while($var_list = dbr2()) {
		$$var_list[name] = $var_list[value];
	}
}


//display advanced specials only
function list_adv_specials(){

	$error_str .= "<h3><b>Advanced Specials Index</b></h3><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","pc");
	$error_str .= quick_row("<b>Short for</b>","Plasma Cannon");
	$error_str .= quick_row("<b>Rank</b>","Attack: Level 2");
	$error_str .= quick_row("<b>Hits</b>","Fighters, Shields, Armour");
	$error_str .= quick_row("<b>Description</b>","The Plasma Cannon is a Level 2 weapons cluster, reverse engineered from an original Alien ship found derelict in Epsilon Erindi. Using waste plasma from a ship's Ion Propulsion Engine, the Cannons create a series of focused energy waves which can sweep away up to 375 fighters/shield charges per wave.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","sa");
	$error_str .= quick_row("<b>Short for</b>","Silicon Armour Module");
	$error_str .= quick_row("<b>Rank</b>","Defence: Level 2");
	$error_str .= quick_row("<b>Stops</b>","Fighters, Incoming turret fire");
	$error_str .= quick_row("<b>Description</b>","The Silicon Armour Module is a Level 2 defence system. When attacked your ship will deploy this Module immediately. The Silicon Armour Module creates a thin inpenetrable layer of Silicon Matrix Alloy, a fluid-like layer that absorbs anything up to 450 damage before failing. Regenerates almost immediately in time for other attack. How's that for ingenuity?");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","ew");
	$error_str .= quick_row("<b>Short for</b>","Electronic Warfare Pod");
	$error_str .= quick_row("<b>Rank</b>","Merged Attack/Defence: Level 1");
	$error_str .= quick_row("<b>Effects</b>","Fighters, Shields, Turret Fire");
	$error_str .= quick_row("<b>Description</b>","The Electronic Warfare Pod is a Level 1 merged attack/defence system, that uses small but deadly EMP Cannons. All opposing Fighters are left in disarray as electrical systems begin failing, and enemy shields deplete as they take a battering. It results in increased damage against an enemy, while also preventing many opposing fighters from locking their weapons on you. Estimated attack bonus of 125, and defence bonus of 225. A great all rounder!");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","gs");
	$error_str .= quick_row("<b>Short for</b>","Gravitronic Scanner");
	$error_str .= quick_row("<b>Detects</b>","Mines, Dark-Matter, Fighters");
	$error_str .= quick_row("<b>Description</b>","The Gravitronic Scanner uses gravity wave fluctuations to detect small hard to scan items such as Mines and Ship Fighters. It also has the ability to detect the presence of Dark-Matter in a star system. In conjuction with this Adv. Scanner, Cannon, Armour and Warfare systems are used more efficiently receiving an extra bonus. This item also allows the Dark-Matter Converter to mine the elusive Dark-Matter, an exotic material used in certain types of weapons and missiles. The scanner also enables equipped ships to detect mines more efficiently and allow an increased chance of dodging them completely.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","ms");
	$error_str .= quick_row("<b>Short for</b>","Missile Bay");
	$error_str .= quick_row("<b>Use</b>","Storage and Launch Bays for Missiles");
	$error_str .= quick_row("<b>Description</b>","Missile Bays act as storage and launch tubes for all Missile types. Each bay can hold 4 Missiles which may then be fired upon planets or ships. Multiple Missile Bays are allowable but the Missile Assault Frigate comes preconfigured in a PCF hull.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","mw");
	$error_str .= quick_row("<b>Short for</b>","Mine Sweeper");
	$error_str .= quick_row("<b>Use</b>","Mine sweeping and evasion");
	$error_str .= quick_row("<b>Description</b>","Mine Sweepers are able to evade Mines more efficiently while also enabled some Mines to be captured.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","ml");
	$error_str .= quick_row("<b>Short for</b>","Mine Layer");
	$error_str .= quick_row("<b>Use</b>","Mine laying and evasion");
	$error_str .= quick_row("<b>Description</b>","Mine Layers are able to evade Mines more efficiently while also being the only ship capable of laying Mines.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","rd");
	$error_str .= quick_row("<b>Short for</b>","Raider");
	$error_str .= quick_row("<b>Use</b>","Ship raiding and claiming");
	$error_str .= quick_row("<b>Description</b>","Raiders are capable of both raiding a vessel and removing all cargo, or claiming a vessel for the user's fleet.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","dy");
	$error_str .= quick_row("<b>Short for</b>","Decoy");
	$error_str .= quick_row("<b>Use</b>","Anti-Raider Ship");
	$error_str .= quick_row("<b>Description</b>","Decoy vessels self-destruct when they are being raided inflicting up to 2000 damage to the vulnerable Raider.");
	$error_str .= "</table>";

	return $error_str;
}


//display specials info.
function list_specials(){

	$error_str .= "<h3><b>Specials Index</b></h3>";
	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","br");
	$error_str .= quick_row("<b>Short for</b>","Bussard Ramjet");
	$error_str .= quick_row("<b>Description</b>","A ship with this, will collect between 1 and 3 fuel per <b class=b1>Normal</b> warp jump made. This comes from the intersteller hydrogen and other gases that are lying around in deep space.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","bs");
	$error_str .= quick_row("<b>Short for</b>","BattleShip");
	$error_str .= quick_row("<b>Description</b>","A BattleShip. This shows the ship will do increased damage when it attacks.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","fr");
	$error_str .= quick_row("<b>Short for</b>","Freighter");
	$error_str .= quick_row("<b>Description</b>","This ship is a freighter. This means that it will generally do more counter-damage when attacked, than a normal ship.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","hs");
	$error_str .= quick_row("<b>Short for</b>","High Stealth");
	$error_str .= quick_row("<b>Description</b>","Nothing about the ship can be discerned. Only that it is there, and maybe it's size. It is not possible to attack a ship with High Stealth, unless the attacking ship has a scanner. Ships with High stealth also take much less damage when attacked.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","ls");
	$error_str .= quick_row("<b>Short for</b>","Low Stealth");
	$error_str .= quick_row("<b>Description</b>","The type of ship will be shown, but other players will not be able to see the stat's for it. Everything about it can be seen if the enemy has a ship with a scanner in it. Ships with Low Stealth can do increased damage when attacking, as they have an element of surprise.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","na");
	$error_str .= quick_row("<b>Short for</b>","No Attack");
	$error_str .= quick_row("<b>Description</b>","This ship type cannot attack anything.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","oo");
	$error_str .= quick_row("<b>Short for</b>","Only One");
	$error_str .= quick_row("<b>Description</b>","Can only buy one of these ships in the game. If you loose it, it's dead, and you can't buy a replacement.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","po");
	$error_str .= quick_row("<b>Short for</b>","Planets Only");
	$error_str .= quick_row("<b>Description</b>","This ship type can only attack planets and can not attack other ships.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","sc");
	$error_str .= quick_row("<b>Short for</b>","Scanner");
	$error_str .= quick_row("<b>Description</b>","A scanner enables the ship fitted with it to see cloaked ships, and also to attack them. To use the scanner succesfully you must be commanding the ship with it in.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","sh");
	$error_str .= quick_row("<b>Short for</b>","Shield Charging Upgrade");
	$error_str .= quick_row("<b>Description</b>","A unit that can be fitted to the ship to increase shield charge rate by a further <b>25%</b>.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","sj");
	$error_str .= quick_row("<b>Short for</b>","Sub-Space Jump");
	$error_str .= quick_row("<b>Description</b>","Allows the ship to make subspace jumps. This is only available on the <b>Traverser</b> ship, and allows ten ships to be towed to the destination. The turn cost of a jump is not affected by the number of ships towed, just the distance jumped.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","so");
	$error_str .= quick_row("<b>Short for</b>","Ships Only");
	$error_str .= quick_row("<b>Description</b>","This ship type can only attack other ships, and may not attack planets.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","sv");
	$error_str .= quick_row("<b>Short for</b>","Super Weapon");
	$error_str .= quick_row("<b>Description</b>","This ship has a super weapon on it. The superweapon in question being a Quark Displacer, which can only be fired at planets, but does a serious amount of damage per shot, at a cost of a number of turns. Can attack Hostile planets as well as passive ones.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","sw");
	$error_str .= quick_row("<b>Short for</b>","Super Weapon: Mark 2");
	$error_str .= quick_row("<b>Description</b>","This ship has a super weapon on it. This Super Weapon being the Terra Maelstrom which can only be fired at planets. It can be a very major threat to a players planet provided there are enough turns to use it. Can attack Hostile planets as well as passive ones.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","tw");
	$error_str .= quick_row("<b>Short for</b>","TransWarp");
	$error_str .= quick_row("<b>Description</b>","The ship has a built in transwarp drive. This means it can jump limited distances without needing to use warp links. It can tow any number of ships, however each ship towed adds one to the turn cost of the jump.");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b>Abbreviation</b>","ws");
	$error_str .= quick_row("<b>Short for</b>","WormHole Stabiliser");
	$error_str .= quick_row("<b>Description</b>","An upgrade that is strictly for ships that have a SubSpace jump Drive. This will allow the selected ship to participate in the <b class=b1>AutoShifting</b> of colonists from Sol to a players planet. Without one on at least one transverser in the system, Autoshifting is not possible.");
	$error_str .= "</table><br /><br />";

	$error_str .= list_adv_specials();

/*	$error_str .= "<br /><b class=b1>mo</b>: <b class=b1></b><br />";
	$error_str .= "<br /><b class=b1>mf</b>: <b class=b1></b><br />";
	$error_str .= "<br /><b class=b1>sd</b>: <b class=b1></b><br />";*/

	return $error_str;
}


//list game vars
if($game_vars) {
	if(!$admin_var_show) {
	 $error_str .= "The Admin of this game, doesn't want the game variables displayed.";
	} else {
		db(__FILE__,__LINE__,"select * from ${db_name}_db_vars order by name");
		$error_str .= "<h3><b>Game Variables</b></h3>Shown below are all the variables for the game, as set by the Admin.";
		$error_str .= "<table border=0 cellspacing=1 width=350>";
		while($var = dbr()) {
			$error_str .= "<tr bgcolor=#333333><td width=220>$var[name] = ${var[value]}</td>";
			$error_str .= "<tr bgcolor=#555555><td><blockquote>${var[descript]}<br /></td>";
			$error_str .= "<tr bgcolor=#000000><td colspan=2>&nbsp;</td></tr>";
		}
		$error_str .= "</table>";
	}

	$help_type = "Game Variables";


#the stories
} elseif($story==1) {
	$stories = include("story.inc.php");
#	$header_str = "<a href=#story>Main Story</a><br />";
#	$story_str = "<a name=story> </a>$stories[main].$rs<br /><br />";
	$counter = 0;
	while(list($s_title, $s_content) = each($stories)){
		$s_title = eregi_replace("_"," ",$s_title);
		$header_str .= "<a href=#$counter>$s_title</a><br />";
		$story_str .= "<a name=$counter><div align=\"center\"><b>$s_title</b></div></a><br />$s_content.$rs<br /><br />";
		$counter++;
	}

	$error_str .= "<h3><b>Solar Empire Stories</b></h3>List of Stories:<br /><br />".$header_str."<br /><br />".$story_str;
	$help_type = "Game Stories";
	$rs = "";


#ship listing
} elseif($ship_info) {
/*	$ship_types = load_ship_types();
	if($shipno < 0) {//list stats of all ships:
	if($shipno == -1){
		$error_str .= "<h3><b>Ship Listing</b></h3>Listed below is information for all ships that can be brought from the Shipyards at Earth.<p>";
	} elseif($shipno==-2){
		$error_str .= "<h3><b>Ship Listing</b></h3>Listed below is information for all ships that can be brought from the blackmarkets.<p>";
	}
		$ship_types_2 = $ship_types;
		while($ship_array_1 = each($ship_types_2)) {
			$ship_stats_1 = $ship_array_1[0];
			$ship_stats = $ship_types[$ship_stats_1];
//			if(($ship_stats[type_id] >= 300 && $shipno==-2) || ($ship_stats[type_id] < 300 && $shipno==-1 && $ship_stats[type_id] != 1 || ($ship_stats[race] == 1 && $ship_stats[purchase_loc] == 1))){
			if(($ship_stats[race] == 1 || $ship_stats[race] == 0) && $ship_stats[purchase_loc] == 1){

				$ship_stats[cost] = number_format($ship_stats[cost]);
				$ship_stats[tcost] = number_format($ship_stats[tcost]);
				$error_str .= make_table_width(array("",""),"75%");
				$img_txt = "<tr><td colspan=2><div align=\"center\"><img border=0 src='./images/ships/ship_${ship_stats[type_id]}_tn.jpg'></div></td></tr>";
				$error_str .= "<tr><td colspan=2 align='center' bgcolor='#555555'><b>$ship_stats[name]</b> ($ship_stats[class_abbr]) </td></tr>$img_txt";
				$error_str .= quick_row("<b>Size</b>",discern_size($ship_stats[size]));
				$error_str .= quick_row("<b>Type</b>","$ship_stats[type]");
				$error_str .= quick_row("<b>Race</b>","$ship_stats[race]");
				$error_str .= quick_row("<b>Fighters</b>","$ship_stats[fighters]/$ship_stats[max_fighters]");
				$error_str .= quick_row("<b>Shields</b>","$ship_stats[max_shields]");
				$error_str .= quick_row("<b>Cargo Bays</b>","$ship_stats[cargo_bays]");
					$error_str .= quick_row("<b>Mining Rate: Metal</b>","$ship_stats[mine_rate_metal]");
					$error_str .= quick_row("<b>Mining Rate: Fuel</b>","$ship_stats[mine_rate_fuel]");
					$error_str .= quick_row("<b>Move Cost (</b>turns<b>)</b>","$ship_stats[move_turn_cost]");
				if(($ship_stats[type_id] >= 300) && ($shipno==-2)){
					$error_str .= quick_row("<b>Tech Bonus</b>","$ship_stats[tech_bonus]");
				}
				if (!$ship_stats[config]) {
					$error_str .= quick_row("<b>Specials</b>","None");
				} else {
					$error_str .= quick_row("<b>Specials</b>","$ship_stats[config]");
				}
				if(($ship_stats[type_id] >=300) && ($shipno == -2)) {
					$error_str .= quick_row("<b>Bonus Index</b>","<b class=b1>$ship_stats[upg_index]</b>");
				}
				$error_str .= quick_row("<b>Upgrade Pods</b>","$ship_stats[upgrades]");
				$error_str .= quick_row("<b>Description</b>","$ship_stats[descr]");
				$error_str .= quick_row("<b>Cost</b>","$ship_stats[cost]");
				if($ship_stats[type_id] >= 300){
					$error_str .= quick_row("<b>Tech. Support Cost</b>","$ship_stats[tcost]");
				}
				$error_str .= "</table><br />";
			}
			$ship_counter++;
		}
		$help_type = "Complete Ship Listing";

		//list stats for specific ship.
	} else {
			$ship_counter=$shipno;
			$ship_stats = $ship_types[$ship_counter];
			$ship_stats[cost] = number_format($ship_stats[cost]);
			$ship_stats[tcost] = number_format($ship_stats[tcost]);
			$error_str .= make_table_width(array("",""),"75%");
			//$error_str .= quick_row("<b>Name</b>","<b>$ship_stats[name]</b>");
			$error_str .= "<tr><td colspan=2 align='center' bgcolor='#555555'><b>$ship_stats[name]</b></td></tr>";
			$error_str .= quick_row("<b>Size</b>",discern_size($ship_stats[size]));
			$error_str .= quick_row("<b>Type</b>","$ship_stats[type]");
			$error_str .= quick_row("<b>Fighters</b>","$ship_stats[fighters]/$ship_stats[max_fighters]");
			$error_str .= quick_row("<b>Shields</b>","$ship_stats[max_shields]");
			$error_str .= quick_row("<b>Cargo Bays</b>","$ship_stats[cargo_bays]");
			if($alternate_play_1 == 1){
				$error_str .= quick_row("<b>Mining Rate: Metal</b>","$ship_stats[mine_rate_metal]");
				$error_str .= quick_row("<b>Mining Rate: Fuel</b>","$ship_stats[mine_rate_fuel]");
			} else {
				$quick_maths = $ship_stats[mine_rate_metal] + $ship_stats[mine_rate_fuel];
				$error_str .= quick_row("<b>Mining Rate</b>","$quick_maths");
			}

			if (!$ship_stats[config]) {
				$error_str .= quick_row("<b>Specials</b>","None");
			} else {
				$error_str .= quick_row("<b>Specials</b>","$ship_stats[config]");
			}
			$error_str .= quick_row("<b>Upgrade Pods</b>","$ship_stats[upgrades]");
			$error_str .= quick_row("<b>Description</b>","$ship_stats[descr]");
			$error_str .= quick_row("<b>Cost</b>","$ship_stats[cost]");
			if($ship_stats[type_id] >= 300){
				$error_str .= quick_row("<b>Tech. Support Cost</b>","$ship_stats[tcost]");
			}
			$error_str .= "</table>";
			$help_type = "$ship_stats[name] Ship Info";
	}

if(!$specials) {
	$error_str .= "<p><a href=help.php?ship_info=1&shipno=$shipno&specials=1>Add information about Specials.</a>";
} else {
	$error_str .= list_specials();
}*/
		$error_str .= "<h3><b>Ship Listing</b></h3>Listed below is information for all ships that can be brought in the game.<p>Coming Soon";
		$help_type = "Complete Ship Listing";

//list random events
} elseif(isset($random)) {

	$error_str .= "<h3><b>Random Events</b></h3>";
	$error_str .= "Shown below are all the random events that can occur in Solar Empire.";
	$error_str .= "The first table shows a general key to what the information means.";
	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("<b class=b1>Name</b>","This is the name the Event comes under.");
	$error_str .= quick_row("<b class=b1>Type</b>","Whether the event activly does things, or is stationary etc.");
	$error_str .= quick_row("<b class=b1>When</b>","When does it occur? Hourly, Daily, Initiated (when a player starts it). A <b>?</b> (Question mark) shows that it may be random.");
	$error_str .= quick_row("<b class=b1>Level</b>","At what level does it occur. From 1 to 3. The higher the level the more potent it will most likely be.");
	$error_str .= quick_row("<b class=b1>Information</b>","Helpful information about the event. Usually what it does.");
	$error_str .= quick_row("<b class=b1>Description</b>","A general, and completly useless description.");
	$error_str .= quick_row("<b class=b1>Notes</b>","Any Notes. Generally a brief list of what the event does.");
#	$error_str .= "<tr bgcolor=#000000><td colspan=2>&nbsp;</td></tr>";
	$error_str .= "</table><br />";

	//Nebula
	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("Name","<b class=b1>Nebula</b>");
	$error_str .= quick_row("Type","Stationary/Active");
	$error_str .= quick_row("When","Hourly");
	$error_str .= quick_row("Level","2+");
	$error_str .= quick_row("Information","<b class=b1>Nebula </b>offer a get rich quick system that can also decimate a fleet. Mining of Fuel in a <b class=b1>Nebula </b>is tripled due to their gaseous nature. And due to the size of the <b class=b1>Nebula</b>, the fuel is infinite. <br />However the density of material in <b class=b1>Nebula </b>means that there can be no shields on any ship that is in one, even if the ship is just passing through. A ship may take up to 2 damage per maint it is left in a <b class=b1>Nebula</b>, also due to the density.<br />Due to the quantity Hydrogen in a <b class=b1>Nebula</b>, a bomb let loose in one will do <b>triple</b> damage!!!");
	$error_str .= quick_row("Description","A Huge cloud of gases that are responsible for star creation.");
	$error_str .= quick_row("Notes","One <b class=b1>Nebula</b> will cover many systems.<br />Mining of Fuel in a <b class=b1>Nebula</b> is tripled.<br />Fuel is infinite in a <b class=b1>Nebula</b>.<br />Gamma bombs do triple damage in a <b class=b1>Nebula</b>.<br />There are <b>No</b> shields on any ships in an <b class=b1>Nebula</b>.<br />Ships in a <b class=b1>Nebula</b> may take up to 2 damage per maint.");
	$error_str .= "</table><br />";
	//Mining rush
	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("Name","<b class=b1>Mining Rush</b>");
	$error_str .= quick_row("Type","Stationary");
	$error_str .= quick_row("When","Daily - Randomised");
	$error_str .= quick_row("Level","2+");
	$error_str .= quick_row("Information","Prospectors have found a very large ammount of metal in a system. This metal can be mined at a rate of 4 times normal (quadrupled). The metal is also infinite until the rush ends, which could be any hour. <br />There are <b>no</b> bad effects.");
	$error_str .= quick_row("Description","'Gold rush o' 49' was nothing compared to this. A very large quantity of metal has been found in a system and lots of people are going to get there fast.");
	$error_str .= quick_row("Notes","Regularity of a <b class=b1>rush </b>based on the number of stars.<br />Mining rate of any ship in the system is quadrupled.<br />Metal is infinite for the duration of the <b class=b1>rush</b>.<br />The rush could end at any time, and the length of a <b class=b1>rush </b>is based on the random_event admin var, as well as the number of stars.");
	$error_str .= "</table><br />";
	//Solar Storm
	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("Name","<b class=b1>Solar Storm</b>");
	$error_str .= quick_row("Type","Active");
	$error_str .= quick_row("When","Hourly");
	$error_str .= quick_row("Level","2+");
	$error_str .= quick_row("Information","Increased activity within a Star results in a <b class=b1>Solar Storm</b>. This means huge amounts of charged particles are thrown all around the system which in turn interfere with the shields, thus reducing them to zero.");
	$error_str .= quick_row("Description","Activity within a Star varys depending on where it is in its <b class=b1>Solar Cycle</b>. When a Star's cycle reaches its peak, it can easily cause major disruption to many things in the system, shields being one of them. <b class=b1>Storms</b> don't generally last long though.");
	$error_str .= quick_row("Notes","Short lived <b class=b1>Storms</b>, that are common.<br />Will cause a ships shields to go to, and stay at zero, for the duration of the <b class=b1>Storm</b>.<br />Can <b>even</b> effect the <b class=b1>Sol</b> system");
	$error_str .= "</table><br />";
	//SuperNova
	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("Name","<b class=b1>SuperNova</b>");
	$error_str .= quick_row("Type","Active");
	$error_str .= quick_row("When","Daily");
	$error_str .= quick_row("Level","3");
	$error_str .= quick_row("Information","When the Science Institute of Sol find out a star is near death, they report it to the news. As of that time, it could go <b class=b1>SuperNova</b> in <b>24-72</b> hours. Maybe longer on rare occasions, but <b>24</b> hours is the absolute mininmum.<br />When the star does go <b class=b1>SuperNova</b> it will destroy absolutly everything in the system.<br />The star will then become a <b class=b1>SuperNova Remnant</b>.");
	$error_str .= quick_row("Description","A star that has gone bang in a very big way. This is a rare event.");
	$error_str .= quick_row("Notes","Happens rarely. Based on size of universe.<br />Once reported to the news, players have a minimum of <b>24 </b>hrs to get clear of the system, though it could take longer to go. But <b>24-72</b> is the norm.<br />When the star does blow, everything in the system will be destroyed. Planets and all.<br />After it has blown, the star will turn into a <b class=b1>SuperNova Remnant</b>.");
	$error_str .= "</table><br />";
	//SuperNova Remnant
	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("Name","<b class=b1>SuperNova Remnant</b>");
	$error_str .= quick_row("Type","Active");
	$error_str .= quick_row("When","Daily<br />Created by a <b class=b1>SuperNova</b>.");
	$error_str .= quick_row("Level","3");
	$error_str .= quick_row("Information","Once a system has gone <b class=b1>SuperNova</b>, a huge shockwave sends out large quantities of minerals, which then end up in the adjoining systems. Also due to the shear size of a star, when it has gone <b class=b1>SuperNova</b> theres lots of minerals left in the system too. This can be mined at higher rates than normal because there is just so much.");
	$error_str .= quick_row("Description","When a star goes <b class=b1>SuperNova</b> some of what it was made of gets thrown outward. This is a <b class=b1>SuperNova Remnant</b>. It takes a while for the scientist to work out if the <b class=b1>Remnant</b> will remain <b class=b1>Safe</b>, or turn into a <b class=b1>BlackHole</b>");
	$error_str .= quick_row("Notes","When a star has gone <b class=b1>SuperNova</b> it will throw lots of materials to the adjoining systems.<br />There will be huge quantities of materials within the system that has just had the star explode in it. Though not infinite, it can be mined quicker than normal.<br />Mining of Fuel in a <b class=b1>Remnant</b>system is tripled.<br />Mining of Metal is quintupled.<br />Could turn into <b>either</b> a <b class=b1>BlackHole</b> or (more likely) a <b class=b1>Safe SuperNova Remnant</b>.");
	$error_str .= "</table><br />";
	#Safe SuperNova Remnant
	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("Name","<b class=b1>Safe SuperNova Remnant</b>");
	$error_str .= quick_row("Type","Inactive");
	$error_str .= quick_row("When","~");
	$error_str .= quick_row("Level","3");
	$error_str .= quick_row("Information","Once a system has gone <b class=b1>SuperNova</b>, it could well form a Blackhole, or the former star could have been too small, meaning it will remain as a <b class=b1>Safe SuperNova Remnant.</b> If it stays as a <b class=b1>Remnant</b>, then it will be the same as any 'normal' system, but with lots more materials (which are <b class=b1>NOT</b> mined faster in this type of <b class=b1>Remnant</b>).");
	$error_str .= quick_row("Description","Once a Star has gone <b class=b1>SuperNova</b>, it has to be determined by the Boffs at the Observatory if the star will turn into a BlackHole, or if it will just stay as a Remnant.");
	$error_str .= quick_row("Notes","Is same as normal system.<br />However it will most probably have more minerals in.");
	$error_str .= "</table><br />";
	//Black Hole
	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("Name","<b class=b1>Black Hole</b>");
	$error_str .= quick_row("Type","Stationary");
	$error_str .= quick_row("When","Intiated (Player runs into it)<br />Created with game/or SuperNova");
	$error_str .= quick_row("Level","3");
	$error_str .= quick_row("Information","These are created with either the universe, or a Super-Nova of a massive star. They don't go away.<br />Due to the wonders of Gravity, anything that goes into a system with one of these in will have a tough time escaping, but escape they will, though taking <b>5</b>% damage per ship in the process.<br />Whilst escaping, your whole fleet will be thrown around, with each ship most likely ending up in a different system, so be sure to read the messages you get from it.");
	$error_str .= quick_row("Description","These are massive stars that have exploded(SuperNova), and formed into a <b class=b1>Black Hole</b> which is now causing havoc in its neighbourhood.");
	$error_str .= quick_row("Notes","Created with universe, or by a SuperNova.<br />Will damage each ship you are towing (as well as the one you are commanding) by <b>5%</b>.<br />Will scatter all ships in your convoy all over the galaxy. Even onto star Islands.");
	$error_str .= "</table><br />";
	$help_type = "Random Event Info";


//equipment information
} elseif(isset($equip)) {

		$error_str .= "<h3><b>Equipment</b></h3>Equipment is the general stuff you use to do things, and that doesn't fall into some other category.<br />";
		$error_str .= "<br /><a href=#fighters>Fighters & Shields</a>";
		$error_str .= "<br /><a href=#genesis>Genesis Device</a>";
		$error_str .= "<br /><a href=#bombs>Bombs</a>";

		$error_str .= "<br /><br /><br /><a name=fighters></a><b>Fighters & Shields</b><br /><br />All ships have a specified maximum number of fighters that they can be equipped with. <br />Fighters are the units that do all of the work in a battle situation. They deal damage to enemy ships/fighters and when attacked, they deal counter damage to the attacker. <br /><br />A ship that has only shields will only be able to take damage. Shields absorb the first impacts of enemy fighters on your ship. Once they have run-out the fighters get to work in defending you ship. Shields automatically replenish over each hour until they reach the maximum a ship can hold. Fighters cannot and do not.<br /><br />Fighters can be switched from ship to planet, as can shields (provided the target planet has a shield generator). $rs";
		$error_str .= "<br /><br /><br /><a name=genesis></a><b>The Genesis Device</b><br /><br />Genesis Devices are used to create a planet. Simply buy a genesis device, go to a system other than system #1, and click the <b class=b1>Use Genesis Device</b> link in the right column of the star system.<br /><br />This will create a thriving planet, which will have 1,000 colonists on it, which you can then practice your despotic ways upon.";
		$error_str .= "<br /><br /><br /><a name=bombs></a><b>Bombs</b><br /><br />'Big things that go Bang' : Definition of <b class=b1>Bombs</b> from the 'Idiots guide to destroying the universe'. <br /><br />An <b class=b1>Alpha bomb</b> will Eliminate ALL shields from ALL ships in a system (yours included), no matter how many shields the ships may have. It will not actually do any damage to the fighters, or the ships.<br /><br />A <b class=b1>Gamma bomb</b> can be used to do 200 damage to each ship in a star system (yours included). It will take this damage from shields first, and when all shields are gone, then from the fighter count. If there are no fighters, then it will simply destroy the ship. Best used in conjuction with an Alpha Bomb.<br /><br />A <b class=b1>SuperNova Effector</b> can responsible for destroying an entire star system. It will get the Sun to go 'bang in rather a big way' (Idiots guide again), in the process eliminating everything that happens to be in the system when it goes bang. Planets and all. It takes between 24 and 48 hours for a sun to go bang after one of these things has been released.";


	$help_type = "Equipment";

//Upgrade & Accessories information
} elseif(isset($upgrades)) {
	$help_type = "Upgrades & Accessories";
	if($specials == 1){
		$error_str .= "<a href=\"javascript:history.back()\">Back to Upgrades & Accessories</a>";
		$error_str .= list_specials();
		$error_str .= "<br /><a href=\"javascript:history.back()\">Back to Upgrades & Accessories</a>";
	} else {

		$error_str .= "<h3><b>Upgrades & Accessories</b></h3>There are numerous other upgrades not mentioned here, however where they do appear they in the game they come with a suitable explanation.<br />Upgrades can be purchased from either:<b class=b1>Vladimirs Accessories & Upgrades Store</b> or <b class=b1>Bilkos Auction House</b>.<br /><br />";
		$error_str .= "<br /><a href=#about>About Upgrades</a>";
		if($flag_bmrkt == 1){
			$error_str .= "<br /><a href=#aboutadv>About Advanced Upgrades</a>";
		}
		$error_str .= "<br /><a href=#basic>3 Basic Upgrades</a>";
		$error_str .= "<br /><a href=#ot>Offensive Turret</a>";
		$error_str .= "<br /><a href=#dt>Defensive Turret</a>";
		$error_str .= "<br /><a href=#transwarp>Transwarp Drive</a>";
		$error_str .= "<br /><a href=#shroud>Shrouding Unit</a>";
		$error_str .= "<br /><a href=#scanner>Scanner</a>";
		$error_str .= "<br /><a href=#shield_charge>Shield Charging Unit</a>";
		$error_str .= "<br /><a href=#wormhole>Wormhole Stabiliser</a>";
		$error_str .= "<br /><a href=#terra>Terra Maelstrom</a>";
		$error_str .= "<br /><a href=help.php?upgrades=1&specials=1>List of config meanings</a>";

		$error_str .= "<br /><br /><br /><a name=about></a><b>About Upgrades</b><br /><br />Upgrades are used to improve your current ship in one way or another. You may simply want to put some more shield capacity onto it, or you may want to give it a new Super Weapon, the point remains that you are aiming to improve it.<br /><br />Each upgrade requires one upgrade pod (unless otherwise stated), and will allow your ship to do something it couldn't do before. However once a upgrade pod has been used, it cannot be reclaimed.<br />Use upgrade pods wisely on ships that you intend to keep in your service for a long time. $rs";

		if($flag_bmrkt == 1){
			$error_str .= "<br /><br /><br /><a name=aboutadv></a><b>About Advanced Upgrades</b><br /><br />Advanced Upgrades are similar to normal Upgrades on most respects. The main difference is that they are based on Alien Technologies and can only be purchased from Blackmarkets. Normally these Upgrades are reverse engineered from originals discovered on Alien Derelict Ships found after battles or lost in Star Systems.<br /><br />To install Advanced Upgrades requires that you research what are called Tech. Support Units. These are required to use most Alien Technologies. You generate Support Units by building Research Facilities on your planets among other methods.<br /><br />Note that these Advanced Upgrades rather than increasing fighters/shields etc. actually convey certain Attack/Defence Bonuses which are used during battle. See the <a href=help.php?research=1>Research & Blackmarkets</a> section of this file for more information.";

			$error_str .= "$rs";
		}

		$error_str .= "<br /><br /><br /><a name=basic></a><b>3 Basic Upgrades</b><br /><br />The 3 basic upgrades will each upgrade certain aspects of the ship you are commanding for a small price. $rs";

		$error_str .= "<br /><br /><a name=ot></a><b>Offensive Turret</b><br /><br />The Offensive Turret is an AI controlled Level 1 weapons array dedicated to clearing local space of enemy fighters. The AI ensures the turrets hit each and every time to the tune of 225 damage points.<br />No battleship should be without it! $rs";

		$error_str .= "<br /><br /><a name=dt></a><b>Defensive Turret</b><br /><br />The Defensive Turret is a Level 1 defence system controlled by an AI. Its special design enables it to intercept and destroy incoming munitions. <br />Excellent combat defence system that will block up to 275 attack points before allowing a ship to take damage. The first defence system of its kind! $rs";

		$error_str .= "<br /><br /><a name=transwarp></a><b>Transwarp Drive</b><br /><br />This upgrade will allow a ship to jump between systems whilst skipping the systems in-between. It does cost a couple more turns, however its invaluable for getting to star islands, and can tow an unlimited number of ships. It may not be installed onto ships that have the sub-space jump capability. $rs";

		$error_str .= "<br /><br /><a name=shroud></a><b>Shrouding Unit</b><br /><br />This upgrade gives a ship the <b class=b1>ls</b> configeration. This means that enemy players will not be able to determine any information about the ship, unless they have a ship with a scanner on it. $rs";

		$error_str .= "<br /><br /><a name=scanner></a><b>Scanner</b><br /><br />Allows a user to see all information about a ship that would otherwise be an 'unknown', if the ship is lightly stealthed.<br />However, if the ship is Highly Stealthed, then it will give limited information about a ships fighter count, and such like, however it will not be able to determine the ships owner. $rs";

		$error_str .= "<br /><br /><a name=shield_charge></a><b>Shield Charging Unit</b><br /><br />This upgrade will allow a ship's shields to regenerate 25% faster. $rs";

		$error_str .= "<br /><br /><a name=wormhole></a><b>Wormhole Stabiliser</b><br /><br />An absolutely essential upgrade if you have a Transverser, or plan on building a planet, or both.<br />This upgrade allows for <b class=b1>Autoshifting</b> to take place, whereby a fleet of ships will shift from your planet, to Sol, nab as many colonists as it can carry and you can afford, and then come back and dump them onto the planet. Saves vast amounts of time, and generally costs the same number of turns as doing it manually.<br />This upgrade also allows for an infinite number of ships to be towed with it when it makes a jump (from 10 ships max if there is no stabiliser installed).<br />May only be used on ships with a sub-space jump drive. $rs";

		$error_str .= "<br /><br /><a name=terra></a><b>Terra Maelstrom</b><br /><br />This upgrade is here because of its complexity. It does not require an upgrade pod, instead the ship must already have the Quark Disrupter built in.<br /><br />It can only be fired at a planet, and uses either a lot of turns, or ALL of a players turns, depending on the size of the planet, and the turn count of the user. In general, it is best used against big planets when the player has a very high turn count.<br />It also charges the ships shields at 3 times the normal rate. $rs<br />";
		$rs = "";
	}


//research information
} elseif(isset($research)) {
	$help_type = "Research & Blackmarkets";

		$error_str .= "<a name=top></a><br /><h3><b>Research and Tech Support Units</b></h3>Research is the term used to describe the generation of Tech. Support Units from a planets Research Facility. It is these Tech. Support Units that will enable you to purchase all the advanced Alien items at your nearest Blackmarket. Research and Blackmarkets is an area of Solar Empire that may be added or removed by the games Admin. The basic system is described in the following sections below.<br /><br />";
		$error_str .= "<br /><a href=#blackmarket>Illegal Blackmarkets</a>";
		$error_str .= "<br /><a href=#research>Research, Facilities & Support Units</a>";
		$error_str .= "<br /><a href=#advitems>About Advanced and Alien Items</a>";
		$error_str .= "<br /><a href=#ship_spec>Ship Specific Upgrades</a>";
		$error_str .= "<br /><a href=#bonus_inx>Attack/Defense Bonuses</a>";
		$error_str .= "<br /><a href=#mines>Info on Mines</a>";
		$error_str .= "<br /><a href=#fines>Blackmarket Fines</a>";
		$error_str .= "<br /><a href=#dark>Dark-Matter Mining</a>";
		$error_str .= "<br /><a href=#raiding>Raiding vs Claiming</a>";
		$error_str .= "<br /><a href=#worm>Generating Wormholes</a>";
		$error_str .= "<br /><br /><a href=help.php?ship_info=1&shipno=-2>Blackmarket Ship Listings</a>";
		$error_str .= "<br /><a href=help.php?story=1#4>Blackmarket Newslines</a>";

		$error_str .= "<br /><br /><br /><a name=blackmarket></a><h3><b>The Illegal Blackmarket</b></h3><br /><br />In Solar Empire certain groups of ex-pirates have set up an extensive blackmarket network. In-game any number of blackmarkets can exist, each normally located in a random Star System. Due to their illegal nature, modern blackmarkets take the form of immense Trader or Cargo Ships which move at random between Star Systems, always one step ahead of the Sol Authorities. Once you actually find a Blackmarket and contact it you will find that up to three areas exist within it. The first area deals with all manner of Advanced Ships and/or Alien Spacecraft. The second area you will see handles the fitting of equipment and upgrades to ships. The third is a more unusual realm since here you will find many bombs, mines, or missiles which often have extremely odd purposes. Most of these have been reverse engineered from Alien originals, even though few could even guess at just how they work. It should be noted that Blackmarkets change location each day. It is also planned for the future that prices at Blackmarkets will vary from place to place, so exercise your consumer right to shop around!. $rs";

		$error_str .= "<br /><br /><br /><a name=research><h3><b>Research Facilities and Tech. Support Units</b></h3></a><br /><br />Tech Support Units are those things which enable you to make use of Blackmarket goods. They are basically a measure of the level of Knowledge, Support Staff, Power Sources and Computer Skills you can bring to bear when using non-standard items. These units are used up as you purchase more Blackmarket items.<br /><br />So how do you get hold of all these Blackmarket items? To purchase these items from any Blackmarket you will require two things, cash and a source of Tech. Support Units. Research begins very simply by building a Research Facility on your planet. Each player is limited to two such Facilities, one to a planet. (<b>Important Note</b>: Claiming a planet that has a research facility on it will result in the facility being destroyed. No matter who presently owns the planet).<br /><br />These Research Facilities generate Tech. Support Units each hour. The number generated depends primarily on the population of the planet in question. Typically your planet's maximum research output is 5 times the basic hourly rate. The maximum rate is reached by attaining successive research rate increases, each of which requires approximately 1.7 times the number of colonists as the previous one. Thus each new level is just that much more difficult to reach. Certain Blackmarket Ships also allow you to gain Tech Units as your crews gain valuable experience in handling their vessels. This is added automatically to your Tech pool every hour. You may also purchase Tech Units at some cost in any Blackmarket Upgrade & Equipment Store. $rs";

		$error_str .= "<br /><br /><br /><a name=advitems><h3><b>Advanced and Alien Items</b></h3></a><br /><br />So what are Advanced and Alien Items? To put it simply, they are the collection of Ships, Upgrades and Bombs that are based either on reverse-engineered Alien artifacts or on advanced human designs considered illegal and dangerous by the Sol Authorities. They include all manner of exotic ships, equipment, upgrades and a range of experimental bombs, mines and missiles. For a full list of ship special items consult the relevant area of this help file. Certain non-ship specific items are listed below:";

		$error_str .= "<br /><br /><b><i>Tachyon Communication Array</i></b><br /><br />The <b class=b1>Tachyon Communication Array</b> was developed over two years ago in secret by the Sol Authorities. Its purpose was to allow supra-light communications through the use of a stealthed Tachyon emission. Used almost exclusively by the much feared Stealth Probes, Pirates stumbled across the design after salvaging a malfuntioning unit. Some slight redesigning has resulted in a much smaller efficient unit capable of being installed on Leeches and other forms of Stealthed ship to enable undetectable transmissions.";

		$error_str .= "<br /><br /><b><i>Graviton Mine</i></b><br /><br />The <b class=b1>Graviton Mine</b> is a new mine concept. When planted near a Star System's emergence point, the onboard AI awaits for the next group of ships to arrive or attempt leaving. Once activated its passive Graviton Drive draws it closer to its targets without betraying its existence and detonates. Damage is limited to but these Mines can be laid in clusters for multiple strikes, each of which creates enough spacetime distortions to put any hull under serious stress causing 75 damage to all ships targeted. Clusters of more than 10 mines will drain a ships shields before delivering damage. The AI system can even be programmed to target certain ships on the basis of Clan relations!<br /><p>Note: All ships have a calculated chance of being targeted by Mines. See the <a href=#mines>Info on Mines</a> section for more information.";

		$error_str .= "<br /><br /><b><i>Hornet Mine</i></b><br /><br />The <b class=b1>Hornet Mine</b> was first developed by the Sol Authorities as a means of discouraging ships from entering the so-called Nether Regions, areas of space where spacetime seems prone to some unusual wormhole activity. Their design was stolen by the Pirate Blackmarketeers and improved upon by adding the same AI feature inherent in Graviton Mines. The result were the Hornet Mines, each capable of causing 15 damage to any ship. The danger they pose was improved by AI programs which enable clusters of Hornets to target one specific ship (either the strongest or weakest) and damage it until it is either destroyed or the all the mines have detonated.<br /><p>Note: All ships have a calculated chance of being targeted by Mines. See the <a href=#mines>Info on Mines</a> section for more information.";

		$error_str .= "<br /><br /><b><i>Leech</i></b><br /><br />The <b class=b1>Leech</b> is one of a new line of covert devices that has been designed to latch onto a ships hull undetected and carry out its programmed task. Holographic technology makes this hitchhiker invisible to conventional sensors. The onboard AI may be programmed for one of three duties. The first is to cause 50 damage to a ship on an hourly basis. The second is to drain shields by a maximum of 50 per hour and transfer these shields to its own shield unit. The third and most interesting use requires an extra component from the Blackmarket, a <b class=b1>Tachyon Comm. Unit </b>. Once the unit has been fitted the <b class=b1>Leech</b> may send an hourly message to a player and/or clan which contains information on the victim ship such as location, number of fighters on board, and the number of accompanying ships in the same fleet. Although the Leech is almost undetectable its presence can be deduced from the effects it has on its host ship. Certain blackmarkets also have specialised equipment to detect and remove these parasitic devices.";

		$error_str .= "<br /><br /><b><i>Standard Missile</i></b><br /><br />The <b class=b1>Standard Missile</b> is a rather common missile type that was mass produced by most of the Corporations before being banned by the Sol Authority in an effort to remove them as a threat. Each comprises a nuclear fission warhead loaded with an exotic radioactive material which increases their damage potential by a factor of 10 compared to more conventional terrestrial missiles. In space its main purpose is long range attack against heavily defended forces. Damage ranges from 400-500 depending on a number of factors including ship-type and defensive anti-missile measures.<br /><br />Unavailable at present.";

		$error_str .= "<br /><br /><b><i>Dark-Matter Missile</i></b><br /><br />The <b class=b1>Dark-Matter Missile</b> is a revolutionary new product from the infamous Blackmarket Labs. Using exotic Dark-Matter usually mined using a Dark-Matter Converter, each Missile is loaded with a carefully prepared mix of Dark-Matter and Fuel chambered within a portable conversion device. On impact this Missile will create a sphere of Anti-Matter which on contact with a ship's hull will create a detonation of such magnitude that damage caused can reach anything up to 1000, with surrounding ships receiving a small amount of damage as a side-effect. The only proven defense has been found to be Silicon Matrix Armour, although this only limits the damage taken.<br /><br />Unavailable at present. $rs";

		$error_str .= "<br /><br /><br /><a name=ship_spec></a>";
		$error_str .= list_adv_specials();
		$error_str .= "$rs";

		$error_str .= "<br /><br /><br /><a name=bonus_inx><h3><b>Attack/Defense Bonuses</b></h3></a><br /><br />Attack and defense bonuses arise from five available upgrade items: Defensive Turret Arrays, Pea Shooter Clusters, Plasma Cannon, Silicon Armour Matrices and Electronic Warfare Pods. Each conveys to your ship a base bonus amount for either Attacking or Defending. These bonuses are calculated randomly during battle. The Attack Bonus is added to your damage, while the Defense Bonus is deducted from your victims counter-attack damage. The same is calculated for your victim. Because these are recalculated each and every time you attack other ships, a Defense Bonus of about 500 makes your ship impervious to all ships who do not have enough fighters and/or Attack items to overcome this bonus. Thus certain well equipped ships can be deemed to be invulnerable when faced with only light to non-armed ships. The base bonuses (before random additions/subtractions) can be viewed below the Specials description for your ship. It looks like this: <b class=b1>Index: 860-300</b>.<br /><p> The far left amount is your base Attack Bonus, the second figure on the right is your base Defense Bonus. The base or starting Bonuses for any Blackmarket ship are detailed in the Blackmarket Ship Listing. There is also a limit of the number of such upgrades you can place on any one ship, these may not apply to certain preconfigured Blackmarket vessels. $rs";

		$error_str .= "<br /><br /><br /><a name=mines><h3><b>Info on using Mines</b></h3></a><br /><br /> Functions have been modified so that ships are damaged immediately for each cluster found (starting with first set). This allows News, Scores and Messages to be accurately updated with the Attacker's name and other info. The oldest clusters are used up first since they will be the first to attack. I'm reverting to my first attempt for this and leaving out past modifications at present (they were bugged!). The damage function has been modified from that included previously. Damage will now only occur when ENTERING or LEAVING the system. Ships already in the system are deemed safe from Mines. Players already in-system may therefore use the Forum, and other menus and return to the Star System without triggering mines. Since damage is calculated per cluster, if all ships targeted are destroyed remaining clusters are NOT deleted but REMAIN in place for another target. The Mine system now uses three standard functions to operate... The new mine system results in Graviton Mines dealing damage first, with Hornet Mines dealing their damage after. Graviton Mines take precedence... Also, all players now have a 64% chance of being targeted by valid mines entering a system or 70% when leaving with a reduction of both to 50% if the Command Ship (i.e. the ship you currently control) is fitted with a Gravitronic Scanner. A later addition will make these chances increase the more clusters are present in the Star System. <br /><br />Note: Laying Mines within 5 \"Warps\" of Sol is deemed illegal and is not allowed.<br />Only the Mine-Layer ship is capable of laying Mines and must be purchased from a Blackmarket. $rs";

		$error_str .= "<br /><br /><br /><a name=fines><h3><b>Blackmarket Fines</b></h3></a><br /><br />Earth, the Sol System and indeed all of the known galaxy is ruled by the iron fist of the \"Sol Authority\". The Sol Authority, a dictatorial government supported by the Corporations and their constituent Clans, enforces all laws in Solar Empire (You may refer to your Admin as the dictator in charge). One of these laws is a general ban in dealing with Blackmarkets. Each gameday there is a small, remote but real chance that you will be found guilty of dealing with a Blackmarket (if you have not used one you cannot be caught!). This may result in either a fine measuring several thousand credits or a more substantial amount as decided by the range and number of transactions you have had. Such is the price of dealing with criminal elements in the galaxy! Although the chances of being caught are quite small you should keep this in mind. Don't say you weren't warned! Nevertheless you may decide that the risk is worthwhile... <br /><br />Any fines deemed payable should be paid into the Sol Authority Fine Office on Earth. Failure to pay will result in you being classed as an \"Outlaw\" with an amount equal to twice your fine being added as a bounty on your head. Dictators have so little imagination... $rs";

		$error_str .= "<br /><br /><br /><a name=dark><h3><b>Dark-Matter Mining</b></h3></a><br /><br />Dark-Matter is an exotic material which physicists have spent decades in debate over. Of course Aliens had long ago learned the truth and proceeded to make use of their knowledge in many ways. Unusual or not, the Blackmarkets are willing to pay large amounts for this elusive material and have begun selling a cut-priced version or their most controversial ship, the Dark-Matter Converter, to mine it. Controversial because no-one outside of a long disappeared Alien race have even the faintest clue how such craft operate! Exact copies of each other, the Converters are the only ships capable of Dark-Matter \"Conversion\" or mining. Dark-Matter itself is a rare material and there is not much of it in the Universe. Only ships carrying a Gravitronic Scanner have the ability to detect its presence. $rs";

		$error_str .= "<br /><br /><br /><a name=raiding><h3><b>Raiding vs Claiming</b></h3></a><br /><br />Raiding ships is an option available only with a raiding enabled vessel. To begin raiding your raider must first reduce all shields to zero, fighters to 10 or less, and remove all defensive/offensive upgrades. This is done automatically when your raider vessel is successful in any attack that would normally destroy your target. Unlike other ships which would destroy your chosen victim, the raider will instead disable its defenses and permit a raid. So use it when you don't want a vessel to be completely destroyed; it's the only ship with such a highly valued ability.<br /><br />When your attack has been successful a \"raid\" link will appear, though the ship itself may be now located at the bottom of the ships listing due to the decimation of its fighters. Clicking on this link will begin a raid. Choose Yes when asked, if sure, and commence. Your raiding vessel, if successful, will transfer all metal, fuel, electronics, organics, and darkmatter discovered to its own cargo bays, or the cargo bays of other fleet ships if present. It will also give you an amount of cash from smaller and less valuable items found aboard. Once your raid is completed, the raided vessel will be destroyed. The looted material may then be sold or transported to your planets for processing.<br /><br />Not all raids will be a success. Sometimes, the targeted vessel will self-destruct, causing an amount of damage to your raider. Keep this in mind if you fall low in fighters or shields. It is also important to note that any defensive upgrades are disabled during a raid attempt!<br /><br />An alternative to raiding (which destroys the vessel) is claiming it. Any vessel opened to raiding can be claimed by the appropriate raiding vessel if your fleet contains spare ship slots. Claiming is riskier than simple raiding but in the case of expensive ships can be sometimes worth the risk. A claimed vessel is yours to command. You will also be in possession of any fuel etc. aboard. However remember that as a result of being weakened and being made vulnerable to raiding, the claimed vessel will be without any defensive or offensive turrets, cannon or armour. You will have to purchase further upgrades if you feel them necessary. Nevertheless it should be noted that this is the only method by which you can own more than one Flagship, i.e. claiming an enemy's!<br /><br />Note: As an unfortunate by-product of the specialised hard-wired AI systems, a raider class vessel is actually incapable of fully destroying a ship. If you wish to destroy a ship, switch to another battle enabled ship. Also, each raid/claim attempt costs 1 turn and all materials found are loaded on ships with the current highest free cargo bays. $rs";

		$error_str .= "<br /><br /><br /><a name=worm><h3><b>Generating Wormholes</b></h3></a><br /><br />Wormholes connect far distant star systems with each other allowing a fleet to travel between each instantaneously using only 1 turn and skipping all star systems in between. The <b class=b1>Wormhole Generator</b> allows the owner of the planet it is built on to generate a two way wormhole between their home star system and another of their choice. The wormhole created, like all wormholes, is closed to enemy ships but allows a player, their clan, and NAP partners to travel easily through the Universe as well as providing an additional escape route relatively free of Mines since the ultimate exit point remains invisible to enemy and neutral players, thus making it an extremely useful defensive and even offensive aid. The resulting wormhole is however extremely unstable and requires a planetary supply of Dark-Matter to remain functional. If starved of Dark-Matter the Generator may lose control of the generated wormhole, allowing a rogue wormhole to form capable of causing one of three effects. The least spectacular effect is the shift from a two to one way wormhole. The second effect is to cause the system's star to go supernova within 48 hrs. The third effect is that the wormhole may restablise in an inverted form, opening a gateway to an alternate universe. The alternate universe can be either good or bad. Good, if empty, since it's unlikely a lot of people would have access to it, bad because there may be a few immensely hostile Aliens who you just disturbed...never a good thing! To be safe you can always collapse the wormhole manually from your generator controls.";


//planet information
} elseif(isset($planet)) {
		$error_str .= "<h3><b>Planets</b></h3>Planets are created by using a <a href=help.php?equip=1#genesis>Genesis</a> device, which you must have purchased at Earths Equipment shop. Once you have a Genesis device, simply goto a system where you want to build a planet (you may not build a planet in system #1), and click the <b class=b1>Use Genesis Device</b> link which appears in the right column of the star system view.<br /><br />";
		$error_str .= "<br /><a href=#basics>Planet Basics</a>";
		$error_str .= "<br /><a href=#colonists>Colonists</a>";
		$error_str .= "<br /><a href=#missiles>Missiles</a>";
		$error_str .= "<br /><a href=#shields>Shield Generators</a>";

		$error_str .= "<br /><br /><br /><a name=basics></a><b>Planet Basics</b><br /><br />The basic functions of a planet are to produce fighters, defend a fleet, and/or make money. Any player may make a planet assuming (s)he is out of safe turns, and can afford a Genesis Device from the Equipment shop at Earth. <br />Planets may be renamed at any time, and a password may be set on a planet to keep clan members from taking your money/minerals/fighters/colonists. Passwords are usually not recommended unless you are uneasy about another clan member, because they tend to restrict your clan members from free movement of those commodities.<br />Once in the planet view, you have the ability to “claim” a planet.<br /><br />Each planet has the ability to house virtually limitless numbers of fighters. Fighters are the actual units that defend your planet/fleet from oncoming attack by enemy forces. <br />Fighters are set at either one of two modes, Passive and Hostile.<br /><b>Hostile</b> fighters will defend your system from any ship not belonging to you, or your clan (should you be in one), while <b>Passive</b> fighters will only defend the planet itself from direct attack. Setting fighters to Hostile also defends every ship within the system. $rs";

		$error_str .= "<br /><br /><br /><a name=colonists></a><b>Colonists & Taxing & Production</b><br /><br />Colonists are the workforce of the planets. <br />500 <b class=b1>assinged</b> Colonists can take 10 units of fuel and 10 units of metal and produce an admin specified amount of electronics.<br />100 <b class=b1>assinged</b> Colonists can use one unit of the following: Fuel, Metal, Electronics, and produce an admin specified number of fighters which will be used to defend the planet with until you find other things for them to do.<br />An admin specified number of colonists can be used to create one organic unit if you assign them to that task.<br />See the <a href=help.php?game_vars=1>Game Variables</a> section of this help for what the vars are for this game)<br /><br />Colonists that are not doing anything - Idle Colonists - will pay taxes and reproduce. Taxes set at a level higher than 11% will cause negative reproduction (death). Taxes set at a lower level will cause higher reproduction. At a zero percent tax rate, idle colonists will reproduce at a rate of 30% per night. <br />Colonists can also be <b class=b1>assigned</b> to farm organic material that may be sold at ports. $rs";

		$error_str .= "<br /><br /><br /><a name=missiles></a><b>Missiles</b><br /><br />Missile Launch Pads can be constructed on any planet. The cost is given to you on the planet menu screen. These usually take a considerable amount of time, money and materials to build, and they give you the ability to construct <b class=b1>Omega Missiles</b>.<br />Omega Missiles also require a large amount of materials to construct. However, once constructed you may launch the Omega Missile at the planet of your choice.<br />When launching, you must also have fuel and turns available, from which the missile generates the energy necassary to make the long distance trek from one solar system to another. <br /><br />Missile Launch Pads may also be purchased through auction at <b class=b1>Bilko’s Auction House</b>. These may run for as little as 100,000 credits with no cost in minerals. $rs";

		$error_str .= "<br /><br /><br /><a name=shields></a><b>Shield Generators</b><br /><br />A shield generator may also be built on any planet. A typical shield generator can generate and store up to 3000 units of shields at a time, which may then be used to augment your fleets shields. Shield generators in <b>NO</b> way increase the defensive capabilities of a planet itself.<br /><br />Larger generators can be purchased at a <b class=b1>Bilko’s Auction House</b>. These store significantly more shields, and can also regenerate much faster.";

	$help_type = "Planets";

//clans information
} elseif(isset($clans)) {
	$error_str .= "This Page contains basic information on clans. How to join, what they're good for, and that sort of thing.";

	$error_str .= "<br /><br /><a href=#basic_clan>Basic Clan Information</a>";
	$error_str .= "<br /><br /><a href=#loyal_clan>Loyalty in a Clan</a>";

	$error_str .= "<br /><br /><br /><b><a name=basic_clan>Basic Clan Information</a></b><br />";
	$error_str .= "It is not required to join a clan. Some players play solo with great success (sometimes), however if you are new at this game then it should be a high priority to find a clan. You may search through different organised clans by selecting the Clan Control option. You may also post onto the Forum. <br />If you are in a newbie game, just post that you are a newbie and are looking for some aid in the game. You may even find someone advertising a clan for newbie assistance. These are usually experienced members who are willing to give a helping hand to anyone new to the game.<br />After you post a message to the forum, check back often to see if someone else has invited you. You will also want to check your Messages to see if anyone has sent you a personal reply. To go through the process of joining a clan, click the Clan Control link again. Now find the name of the clan you have been recruited to and select the Join link. Then enter the password of the clan you are entering, usually given to you by the clan leader. $rs";

	$error_str .= "<br /><br /><br /><b><a name=loyal_clan>Loyalty in a Clan</a></b><br />";
	$error_str .= "Clan loyalty is one of the more important aspects of the game; if you show strong loyalty to help your clan without many complaints to the clan leader then others may ask you to be part of their clan at a later date. If you are unloyal to your clan, you will be most likely be kicked out of the clan, and then probably destroyed by that clan's members to boot. After that offense, information about you will be posted on the forum and you will have a very hard time finding clans to join in the future.";

	$help_type = "Clans";



//Misc information
} elseif(isset($misc)) {
	$error_str .= "<h3><b>Miscellaneous Information</b></h3>This page contains much Misc Info that is really quite important, and is a must read for any newbie to the game.";

	$error_str .= "<br /><a href=#misc_turns>Turns</a>";
	$error_str .= "<br /><a href=#misc_attack>Attacking</a>";
	$error_str .= "<br /><a href=#misc_mining>Mining</a>";
	$error_str .= "<br /><a href=#misc_moving>Moving & Autowarp</a>";



	$error_str .= "<br /><br /><br /><b><a name=misc_turns>Turns</a></b><br />";
	$error_str .= "Turns are used for pretty much anything. Given below is a small table which lists some of the things that use turns and their costs. A players turns are augmented each hour by some more turns. These turns are given out by the game, and vary from game to game. For this game they are at <b>$hourly_turns</b> turns/hour.<br /><br />";
	$error_str .= make_table(array("Action","Turn Cost"));
	if($ship_warp_cost < 0){
		$error_str .= quick_row("Moving","Depends on the Ship Size");
	} else {
		$error_str .= quick_row("Moving","$ship_warp_cost");
	}
	$error_str .= quick_row("Attack Ship","$space_attack_turn_cost");
	$error_str .= quick_row("Attack Planet","$planet_attack_turn_cost");
	$error_str .= quick_row("Self Destruct a Ship","1");
	$error_str .= quick_row("Buy Colonists","1 Turn/Ship");
	$error_str .= quick_row("Selling At Ports","0,1,5 (Depends on Method Used)");
	$error_str .= "</table><br />Note About Table: Many of the variables listed in the table above may change from game to game. The variables shown are correct for this game. Always check the <a href=help.php?game_vars=1>Game Variables</a> section for a complete and up-to-date variable list for the game. $rs";


	$error_str .= "<br /><br /><br /><b><a name=misc_attack>Attacking</a></b><br />";
	$error_str .= "To be able to attack a ship requires that some conditions are met.<br /><br />You must be out of the <b class=b1>Turns Safe</b> Period.<br />The Enemy must also be out of the <b class=b1>Turns Safe</b> Period.<br />Admin must have attacking enabled.<br />You need to have enough turns to be able to attack.<br /><br />Those are the common reasons players are un-able to attack each other. When you are able to attack a player a link will appear next each of their ships saying <b class=b1>attack</b>. $rs";


	$error_str .= "<br /><br /><br /><b><a name=misc_mining>Mining</a></b><br />Different ships mine at different rates, and this game be seen in the Ship listing section of the help.<br /><br />Material comes into a ships cargo bays from mining every hour on the hour.<br />There are no conditions attached, anyone with a mining capable ship, or fleet of them, can mine. Random events can effect mining rates, but more about those is discussed elsewhere in the help. $rs";

	$error_str .= "<br /><br /><br /><b><a name=misc_moving>Moving & Autowarp</a></b><br />There are several different types of ways to move between systems. <br />They are all listed here:<br /><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("Name","Warp-Links");
	$error_str .= quick_row("Special Equipment <br />Required","None");
	$error_str .= quick_row("Description","The normal way to get around. All stars are connected to other stars via these links. It is possible to get so called <b class=b1>star islands</b> which is where a cluster of islands is segregated from the main group.");
	$error_str .= quick_row("Turn Cost","Low; Avg: 1");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("Name","Wormholes");
	$error_str .= quick_row("Special Equipment <br />Required","None");
	$error_str .= quick_row("Description","These are links between two random systems. They can go either one-way, or two-way. Using one is just like using a normal warp-link, however they are random in their locations.");
	$error_str .= quick_row("Turn Cost","Low; Avg: 1");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("Name","Transwarp");
	$error_str .= quick_row("Special Equipment <br />Required","<a href=help.php?upgrades=1#transwarp>Transwarp Drive</a>");
	$error_str .= quick_row("Description","This is best used for getting to star islands, and skipping around peninsulas. Otherwise its turn cost gets expensive in comparison to normal travel. Has a limited range of about 15 Light-years (2-3 systems), and can have any number of ships in tow (however, each extra ship in tow costs 1 turn more to move).");
	$error_str .= quick_row("Turn Cost","Medium-Low; Depends on distance Jumped & Num ships in tow..");
	$error_str .= "</table><br />";

	$error_str .= make_table_width(array("",""),"75%");
	$error_str .= quick_row("Name","Sub-Space-Jump");
	$error_str .= quick_row("Special Equipment <br />Required","(Adv.) Transverser");
	$error_str .= quick_row("Description","Can jump from any one location in the game to another, skipping everything in-between. It may only tow 10 ships at a time, though, unless the wormhole stabiliser upgrade is installed. Best for <b class=b1>Autoshifting colonists</b> using the <a href=help.php?upgrades=1#wormhole>Wormhole Stabiliser</a> upgrade.");
	$error_str .= quick_row("Turn Cost","Medium-High; Depends on distance Jumped.");

	$error_str .= "</table><br />The cost for moving between systems can vary due the admin being able to control it.<br /><br />There is a function called <b class=b1>Autowarp</b> which can be used to plot, and follow a course to a different system from the system you are in. This will not always find the shortest route to your destination, it will not use wormholes, and it will cost one turn to implement. However it will also save you considerable time in getting to your destination.<br />The link for <b class=b1>Autowarp</b> can be seen in the right column in the star system view.";





	$help_type = "Misc Info";


//external links
} elseif(isset($ext)) {
	$error_str .= "Listed Below are links to resources directly related to Solar Empire. Links will open in a new browser window.";

	$error_str .= "<br /><br />Help and Strategy Guides:";
	$error_str .= "<br /><a href=http://www.quantum-star.com/ target=_blank>Official QSSE Website</a>";
	$error_str .= "<br /><a href=http://www.solarempire.com/ target=_blank>Official Solar Empire Website</a>";

	$error_str .= "<br /><br />Open Source Projects:";
	$error_str .= "<br /><a href=http://sourceforge.net/projects/quantumstar/ target=_blank>Quantum Star - SourceForge</a>";
	$error_str .= "<br /><a href=http://sourceforge.net/projects/solar-empire/ target=_blank>Solar Empire - SourceForge</a>";

	$error_str .= "<br /><br /><br /><small>Disclaimer: The Solar Empire Project cannot be held responsible for the results of visiting any of the links shown above.</small>";

	$help_type = "External Links";
$rs = "";

//getting started information
} elseif(isset($started)) {
	$error_str .= "<h3><b>Getting Started</b></h3>This guide should be open in a different window from the one in which you are playing the game in. The idea being that as you read the instructions in this window, you can then switch over to the game and see just what we're talking about.<br />So without any further ado, lets get started:<br /><br />";

	$error_str .= "<b>First</b> - Check and see how many credits you are given at signup. (This is visible in the column on the left, directly below your <b class=b1>user name</b>). <br />Why? Because you need to buy more ships. Moneymaking is exponential in this game. That means the more ships you have, the faster you can make money, and the faster you can buy more ships.";

	$error_str .= "<br /><br /><b>Buying your first ship(s)</b> - You'll be in a Star System(SS) called <b class=b1>Sol</b>. This system is number <b>1</b>. This information is in the top centre of your screen.<br /> Land on the planet (<b class=1>Earth</b>) and take a visit to <b class=b1>Seatogu's Spacecraft Emporium</b>. Here you will find numerous different kinds of ships, with the ones that you want to look at at this stage in the game being the <b class=b1>Freighters</b> at the top of the page.<br />The <b class=b1>Adalade Class Freighter</b>(ACLF) is the least costly mining ship. Its mining rate is about 5 units per hour for at a cost of <b>7,500</b> Credits (To see information about a specific ship, click the 'info' link next to it.<br />The <b class=b1>London Class</b>(LCMM) may look very tempting. For 62,500 grand you can get a ship that mines about 14 units/hour.<br /><br />Now go ahead and buy yourself some ACLF's. If you have lots of money, you can click the <b class=b1>Mass Purchase</b> link which will allow you to buy many Freighters at once. But if you only want to buy one ship, just click the purchase button.";

	$error_str .= "<br /><br /><b>Once the ship has been brought and named</b> - You will want to collate your ships into a fleet, whereby one ship will tow the others. You can command different ships by clicking the <b class=b1>command</b> link that is next to them, or you can tow them by selecting that instead. When a ship is being towed the tow link will change to <b class=b1>Stop Towing</b>. Also the <b class=b1>Tow All</b> link will save you time and effort by towing all ships in the fleet behind the ship you are currently commanding.";

	$error_str .= "<br /><br /><b>When you have all your ships in tow</b> - Leave the Sol system and go to an adjacent system. To do this simply click one of the numbers at the top of the screen which look like so: <b class=b1>Warp:</b> <X>, <X>,<X>. Each number is a different system. The starmap on the right of your screen helps you navigate.";

	$error_str .= "<br /><br /><b>In the New system</b> - You will quite likely find either some Fuel, or Metal, or usually both (it will be shown in the top centre of the screen, below the warp links). If there is none, simply warp around until you find some. but don't go too far because you only have a limited number of turns (shown in the left column below your cash). You are better off finding a system adjacent to Sol that has Fuel in it. Once in a system with Fuel (fuel generally gets more money than Metal) click the <b class>Mine All</b> button next to the fuel and all of your mining ships in the system will now start mining fuel. Do this right now.";

	$error_str .= "<br /><br /><b>Congratulations</b> - You have just got started. There is much more information in these help pages, but thats all you need to get started.<br />You will get more turns per hour that passed. In this game your turns will increase by <b>$hourly_turns</b>/hour.<br /> Your ships will mine the fuel/metal when the game gets to the next hour. You may then sell the fuel/metal at a port and go back to Earth (System #<b>1</b>) to buy more ships.";

	$error_str .= "<br /><br /><b>Other help sections</b> - Recommended for immediate reading for a Newbie:<br /> - Clans<br /> - -Misc Info.<br /><br />The other areas of this help are concerned with aspects of the game you won't be needing to get to know for a short while yet. But you may start reading them whenever you like. Feel free to click links randomly. You won't be able to do anything you're not allowed to do, and the best way to learn is by exploring.<br /><br />So off you go to start having fun, and we hope to see you perusing these help files later in the game.";

	$help_type = "Getting Started";

#politics help
} elseif($politics){
	$help_type = "Politics";
	$error_str .= "<h3><b>Politics</b></h3>At present politics does nothing other than appoint Senators (people who have the most of something).<br />Being a senator doesn't bestow any priviledges at present, apart from a nice title next to that players name.<br /><br />When politics are completed, so will this help information be.";

#technical information about the game.
} elseif($tech_info){
	$help_type = "Technical Information";
	$error_str .= "<h3><b>Technical Information</b></h3>This page contains technical information about the game, and as such can get pretty technical.<br />The idea behind the page is to give details about how certain aspects of the game work, so as to allow players to better exploit those areas.<p>The present collection of information is listed below:";
	$error_str .= "<p><a href=#tech_attack_sys>The Attack System</a>";
	$error_str .= "<br /><a href=#tech_terras>Terra Maelstroms</a>";
	$error_str .= "<br /><a href=#tech_omega>Omega Missiles</a>";
	#New (as of June 02) attacking system
	$error_str .= "<br /><br /><br /><a name=tech_attack_sys></a><b>The Attack System</b> - Moriarty (11/June/02)<br /><br /><b class=b1>Important Note: The Attack System described below is operational only on the Purely Server, the non-Purely system used on other servers employs a simplified and intuitive adding/subtraction system using calculated Attack and Defense Bonuses - Please see the Research and Blackmarket section of this help file for a full description of these Bonuses.</b><br /><br />The ship to ship as well as ship to planet attacking system can get quite complicated and so it was deemed worthy of a mention.<br />First up, a couple of things should be clarified: The attacker is called the <b>user</b> and the person being attacked is called the <b>target</b>. Ships are the same but with the word <b>ship</b> on the end funnily enough. ;-)";
	$error_str .= "<p>Electronic Warfare Pods (EWP's)<br />The first thing that is done is the number of EWPs are counted. If both sides have the same number, then they cancel each other out. If eithe side has more, then the other side's are nullified, and the winning ship gets to use its EWP's to do some damage.<p>Noting that EWP's have attack, and defense values, these are calculated seperatly. The system then uses the defensive amount of the EWP to try and stop the other ships defensive turrets. Whichever does the more damage wins, and whatever damage is done is taken from the winner, whilst the loosing upgrade doesn't take any more part within the battle as it's been nullified.<br />If there is any defensive EWP charge left, it is then directed at the opposing ships offensive turrets. The same happens here as with the defensive turrets.<br />The remaining defensive EWP charge (if any) is then merged with the offensive EWP charge. This charge is then used to destroy enemy fighters.<p>Defensive Turrets<br />Provided that the defensive turrets on the ship, and they survived the EWP onslought (if there was one), they will now come into play.<br />What these do is destroy enemy fighters. However they destroy enemy fighters <b class=b1>BEFORE</b> they can do damage to your ship. All other components destroy fighters after they have hit your ship.";
	$error_str .= "<P>Offensive Turrets<br />It is now time for the offensive turrets to come into play. These are both the Pea Shooters, and the plasma cannons, and their damage is total is added together to give the offensive turret damage capacity.<br />This damage is then flung at the enemy Silicon armour if there is any. If it is stopped, then the Offensive turrets are out for the round, and the silicon armour takes that much damage.<br />Otherwise they kill the silicon armour and work their way towards the shields, which they proceed to try and destroy.<br />If any shots get through the shields too, they then set about destroying fighters.<br />Should all the fighters be destroyed, then they will destroy the ship.<p>Fighter damage<br />Assuming the ship wasn't destroyed and has some fighters on it, then its time to figure out how much damage they should do. Below is a complete listing of how the fighters damage is calculated.<p>Whatever fighters survived the defensive turrets are used.<br />The damage done is 65% of the fighters on the attacking ship, and 85% on the defending ship.<br />This number then gets randomised by up to 6% plus or minus.<p>The resulting attack/counter attack number is then modified in the following ways once the total percentage modifiers have been worked out:";
	$error_str .= "<p>The damage done by the opponenet goes up in % by whatever your ships move_turn_cost (ship speed. Bigger is slower, and thus worse) + ship size (bigger is worse) total to. So a ship with a speed of 7 (slowest) and size of 8 (biggest) would take an extra 15% damage, whilst a small ship (2) that is quick (2) would only take 4% extra damage.<p>The ships speed is then also converted direcretly into a %, which will be removed from the damage your ship takes.<br />The amount of damage each ship does then goes up by a percentage value based upon the number and size of previous ships killed by that ship. This can be up to 20%! (but that would require killing about 130 HM's with that ship).<p>It is then time to take in the configuration of the ship:<br />battleship = +10% dam done<br />hs = +3.5% dam done<br />ls = +1% dam done<br />sc = +5% dam done<br />fr = +4% dam done<br />po = +25% dam taken!<br />fr = -7.5% dam taken<br />hs = -4.5% dam taken<br />ls = -3% dam taken<p>At this point the damage done and damage taken modifying percentages are then used on the total damages to figure out the final numbers.<br />This damage is then thrown at the silicon armour. If it gets through that it takes on the shields, and then the fighters.<br /><br />This system is also used for ship to planet assualts with a few minor differences.<br />EWP's server no purpose against planets, as they just don't work.<br />The defending planet does not do like a defending ship and have only 85% of its fighters do damage. It does 100% damage!.<br />Scanners and freighter configs also serve no purpose against planets.<br />And of course planets don't have armour or turrets to defend themselves these days.<P>And that is the attacking system. I hope I made it all clear. :-)";
	#terra maelstrom := How it works.
	$error_str .= "<br /><br /><br /><a name=tech_terras></a><b>Terra Maelstroms</b> - Moriarty (11/June/02)<br /><br />These are vicious little anti-planet weapons that are an upgrade for the quark disrupter.<br />At present, the quark disrupter does 600 to 1400 damage per shot (randomised) for 30 turns.<p>The Terra takes 50 turns as an absolute minimum to fire. What happens is this:<br />Those 50 turns can generate between 4000 and 6000 damage (randomised). <br />Now, if the planet has more than that number of fighters, and the user has more than 50 turns, the game will first find out how many turns is the maximum a player can have. If the player has the maximum number of turns, they will kill between 65% and 75% of the fighters on the planet (randomised). If they don't have that many turns, the gun will use all of the turns a player has and work out what amount up to that 65-75% the user is capable of doing.<br.Whatever number is a result of working out the damage done is then randomised by 5%.<p>Once that is all done, the game will see if the damage done by using all of a players turns is actually greater than the fixed damage done for 50 turns. Whichever does the most damage wins, and is used.";

	#omega missile information
	$error_str .= "<br /><br /><br /><a name=tech_omega></a><b>Omega Missiles</b> - Moriarty (11/June/02)<br /><br />Like the Terra's, these things do damage based on percentages.<br />The missile takes at least 5 turns to launch, but this number can go up, the further away the target planet is. The amount of fuel required is 20 times greater than the turns required.<br />If there are less than 100 fighters on the planet, the missile will destroy the planet.<br />If there are more than 100, but less than 1000, the missile will destroy all fighters.<br />If the fighter count is greater than 1000, the missile will kill 4% of the fighters on the planet (the 4% is randomised by 15%).<p>Once the fighters have been figured, its the turn of the colonists to perish (provided there are some). Any less than 3000, and they are all killed off instantly. More than 3000, and the missile will kill 4% of them (the 4% is also randomised by 15%).";

//not looking for specific info, give them the getting started stuff.
} else {
	$help_type = "Index";
	$error_str .= "<h3><b>Welcome to the QS: Generations: QS Help Pages</b></h3><br />The aim of these pages is to provide you with all the information you'll need to become a successfull QS player. As you may have noted, this help page comes up in its own window, so you can flick between it and the game to see whats being talked about. <br /><br />If you're new to QS, then lets <a href=help.php?started=1>get you started</a>.<br /><br />If you're more of a Vet, then you may want to peruse the other sections of the site to try and find out more information to try and improve your play.<br />";
}


print_header("Help - $help_type");
#prints help left column
echo '<table border=0 cellspacing=0 cellpadding=0>';
echo '<tr><td valign=top width=150>';
echo "QS:Gen Help<br /><br />";

echo "Present Help Topic:<br /><b class=b1>$help_type</b>";

echo "\n<br /><br /><br />Other Help Topics:<ul>";
echo "\n<li><a href=help.php?started=1>Getting Started</a>";
echo "\n<li><a href=help.php?misc=1>Misc Info</a>";

if($clan_member_limit > 0 && $max_clans > 0){
	echo "\n<li><a href=help.php?clans=1>Clans/Factions</a>";
}

if($enable_politics == 1){
	echo "\n<li><a href=help.php?politics=1>Politics</a>";
}

echo "\n<li><a href=help.php?equip=1>Equipment</a>";
echo "\n<li><a href=help.php?upgrades=1>Upgrades & Accessories</a>";
echo "\n<li><a href=help.php?planet=1>Planets</a>";
echo "\n<li><a href=help.php?story=1>The Stories</a>";

if($random_events > 0){
	echo "\n<li><a href=help.php?random=1>Random Events</a>";
}

if($flag_bmrkt == 1){
	echo "\n<li><a href=help.php?research=1>Research & Blackmarkets</a>";
}

echo "\n<li><a href=help.php?ship_info=1&shipno=-1>Ship Listings</a>";
echo "\n<li><a href=help.php?tech_info=1>Technical Information</a>";
echo "\n<li><a href=help.php?game_vars=1>Game Variables</a>";
echo "\n<li><a href=help.php?ext=1>External Links</a>";

echo '</ul></td><td valign=top><br />';

echo $error_str;

print_footer();
?>