<?php
// Universe creation functions functions

//get's a variable from the database, and returns it's value
function get_db_var($name) {
	global $db_name;
	db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = '$name'");
	$result = dbr(1);
	return $result['value'];
}

//add starports to the universe.
function add_starports_se1(&$ports) {
	global $UNI, $extinfo;
	$port_id = 0;
	$max_races = get_db_var('max_races');
	for($i = 0; $i < ($UNI['num_ports'] - $max_races); $i++) {
		$new_port = array('location' => 0);

		$new_port['location'] = mt_rand(($max_races+1), $UNI['numsystems']);

		//ensure no more than 1 per system. But ONLY if there are enough systems!!!
		if($UNI['num_ports'] < $UNI['numsystems']){
			while(system_has_port($ports, $new_port)) {
				$new_port['location'] = mt_rand(($max_races+1), $UNI['numsystems']);
			}
		} else {
			$new_port['location'] = mt_rand(($max_races+1), $UNI['numsystems']);
		}
		$new_port['id'] = $port_id;
		$port_id ++;

		$ports[] = $new_port;

		if($extinfo) {
			print("<div id='addports1$new_port[location]'>-Created port #".($i + 1)." in $new_port[location]<script>document.all.addports1$new_port[location].scrollIntoView();</script></div>");
			flush();
		}
	}
}

//add BM's to the universe
function add_blackmarket_se1(&$bmarks) {
	global $UNI, $extinfo;
$max_races = get_db_var('max_races');
	$bm_names = array("Dodgy Dave", "Stinkin Sid", "Goodie-bag Central", "The Department of Corruption", "The Ultimate Goodies Store", "Stompin Jim", "The War Cabinet", "Jim  -Dead Eye- Smarms", "One Eyed Doyle", "The Ministry of Offence");
	$bm_type = 0;

	for($i = 0; $i < $UNI['num_bms']; $i++) {
		$new_bm = array('location' => 0, 'bmrkt_type' => "", 'bm_name' => "");

		$new_bm['bm_name'] = $bm_names[array_rand($bm_names)];
		#less blackmarkets than types
		if($UNI['num_bms'] < 2){
			$new_bm['bm_type'] = 0;

		} else {//increase the bm_type until we get to 2, then reset to 0.
			if($bm_type == 2){
				$bm_type = 0;
			} /*elseif($i > 0) {
				$bm_type++;*/
			//}
		}

		$new_bm['bm_type'] = $bm_type;

		$new_bm['location'] = mt_rand(($max_races+1), $UNI['numsystems']);

		//ensure no more than 1 per system. But ONLY if there are enough systems!!!
		if($UNI['num_bms'] < $UNI['numsystems']){
			while(system_has_port($bmarks, $new_bm)) {
				$new_bm['location'] = mt_rand(($max_races+1), $UNI['numsystems']);
			}
		} else {
			$new_bm['location'] = mt_rand(($max_races+1), $UNI['numsystems']);
		}

		$bmarks[] = $new_bm;

		if($extinfo) {
			print("<div id='addbms1$new_bm[location]'>-Created Blackmarket #".($i + 1)." in $new_bm[location]<script>document.all.addbms1$new_bm[location].scrollIntoView();</script></div>");
			flush();
		}
	}
}


//function that will pre-generate planets.
function planet_functionality (){
	global $UNI,$db_name,$systems;
$max_races = get_db_var('max_races');
	#pre-generated planets
	dbn(__FILE__,__LINE__,"delete from ${db_name}_planets where planet_id > 2");
	print "Old planets wiped\n<br />";

	//sum total metal & fuel in the universe.
	db(__FILE__,__LINE__,"select sum(metal) as metal, sum(fuel) as fuel from ${db_name}_stars");
	$mineral_sum = dbr(1);
	$metal_sum = round($mineral_sum['metal'] / ($UNI['numsystems'] - 1));
	$fuel_sum = round($mineral_sum['fuel'] / ($UNI['numsystems'] - 1));

	for($ct = 1; $ct <= $UNI['uv_planets']; $ct++) {
		$planet_loc = mt_rand(($max_races+1), $UNI['numsystems']);

		if($systems[$planet_loc - 1]['event_random'] != 0){ //no planets in random event systems
			continue 1;
		}

		dbn(__FILE__,__LINE__,"update ${db_name}_stars set unigen = unigen + 1 where star_id = $planet_loc");

	db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $planet_loc");
	$planet_unigen = dbr(2);
	$resev = $planet_unigen['unigen'];
	$rand_name = mt_rand(1,4);
	if ($rand_name <= 3){
			if($resev <= 1) {
				$rese = " I";
			} elseif($resev <= 2) {
				$rese = " II";
			} elseif($resev <= 3) {
				$rese = " III";
			} elseif($resev <= 4) {
				$rese = " IV";
			} elseif($resev <= 5) {
				$rese = " V";
			} elseif($resev <= 6) {
				$rese = " VI";
			} elseif($resev <= 7) {
				$rese = " VII";
			} elseif($resev <= 8) {
				$rese = " VIII";
			} elseif($resev <= 9) {
				$rese = " IX";
			} elseif($resev <= 10) {
				$rese = " X";
			} elseif($resev <= 11) {
				$rese = " XI";
			} elseif($resev <= 12) {
				$rese = " XII";
			} elseif($resev <= 13) {
				$rese = " XIII";
			} elseif($resev <= 14) {
				$rese = " XIV";
			} elseif($resev <= 15) {
				$rese = " XV";
			} elseif($resev <= 16) {
				$rese = " XVI";
			} elseif($resev <= 17) {
				$rese = " XVII";
			}
		} elseif ($rand_name > 3 && $resev <= 10){
			if($resev <= 1) {
				$rese = " Major";
			} elseif($resev <= 2) {
				$rese = " Minor";
			} elseif($resev <= 3) {
				$rese = " Greater";
			} elseif($resev <= 4) {
				$rese = " Smaller";
			} elseif($resev <= 5) {
				$rese = " Prime";
			} elseif($resev <= 6) {
				$rese = " Secondary";
			} elseif($resev <= 7) {
				$rese = " Secondus";
			} elseif($resev <= 8) {
				$rese = " Primus";
			} elseif($resev <= 9) {
				$rese = " Primary";
			} elseif($resev <= 10) {
				$rese = " Binary";
			}
		}

		$p_rnd = round(mt_rand(1,10));
		if($p_rnd <= 4){
		$plnt_rnd = 1;
		$planet_name = $systems[$planet_loc -1]['name']/*." #".$ct*/;
		$colonis = 0;
		$planetary_metal = 0;
		$planetary_fuel = 0;
		$planetary_figs = 0;
		$planetary_org = 0;
		$planet_img = mt_rand(1, 7);
		} elseif ($p_rnd > 4){
		$plnt_rnd = 0;
		$planet_img = mt_rand(1, 15);
		$planet_name = $systems[$planet_loc -1]['name']/*." #".$ct*/;
		$colonis = round(mt_rand(1,($UNI['uv_planets'] * 150)));
		$planetary_metal = round((mt_rand(4, $metal_sum) / 500) /** $metal_sum*/);
		$planetary_fuel = round((mt_rand(4, $fuel_sum) / 500) /** $fuel_sum*/);
		$planetary_figs = round(($planetary_fuel + planetary_metal / 100));
		$planetary_org = round($planetary_fuel + planetary_metal);

		}


		$p_id = $ct + 2;
		dbn(__FILE__,__LINE__,"insert into ${db_name}_planets (planet_id, planet_name, location, owner_id, owner_name, fighters, colon, cash, clan_id, metal, fuel, organ, pass, planet_img, barren, unigen) values('$p_id', '$planet_name - $rese', $planet_loc, '0', 'None', '$planetary_figs', '$colonis', '1', 0, '$planetary_metal', '$planetary_fuel', '$planetary_org', '0', '$planet_img', $plnt_rnd,'1')");
		print "Planet $ct: $planet_name - $rese created at $planet_loc\n<br />";
	}
	print "Randomly Placed Planets Done.\n<br />";

#if pre-genned planets are off, then planetary slots will be implemented elsewhere.
}


//function that places random events around the universe.
function random_event_placer(){
	global $UNI, $systems, $db_name;
$max_races = get_db_var('max_races');
	//high level random events
	if($UNI['random_events'] == 3){
		//black holes
		$to_do = ceil($UNI['numsystems'] / 110);
		for($i=1; $i <= $to_do; $i++){
			$place = mt_rand(($max_races+1), $UNI['numsystems']);
			dbn(__FILE__,__LINE__,"update ${db_name}_stars set event_random = 1, planetary_slots = 0, metal = 0, fuel = 0 where star_id = '$place'");
			$systems[$place - 1]['event_random'] = 1;
			$systems[$place - 1]['planetary_slots'] = 0;//no planets in BH systems.
			dbn(__FILE__,__LINE__,"delete from ${db_name}_ports where location = '$place'");
			dbn(__FILE__,__LINE__,"delete from ${db_name}_planets where location = '$place'");
		}
	}
}

//check to see if a system already has a port
function system_has_port(&$ports, $s_port) {
	foreach($ports as $port) {
		if($port['location'] == $s_port['location']) {
			return true;
		}
	}
	return false;
}

//save the universe
function save_universe_se1(&$systems, &$ports, &$bmarks, $table_stars, $table_ports, $table_bms, $delete=true) {
	global $UNI,$db_name;

	if($delete) {
		if(!empty($table_stars)) {
			dbn(__FILE__,__LINE__,"delete from $table_stars");
		}
		if(!empty($table_ports)) {
			dbn(__FILE__,__LINE__,"delete from $table_ports");
			dbn(__FILE__,__LINE__,"delete from ${db_name}_indices");
		}
		if(!empty($table_bms)) {
			dbn(__FILE__,__LINE__,"delete from $table_bms");
		}
	}

	if(!empty($table_stars)) {
		foreach($systems as $system) {
			$link_arr = array();
			if($system['links'] != ""){//don't link all systems to 1 automatically.
				$link_arr = array_map("plus_one", explode(',', $system['links']));
			}
			$link_arr = array_pad($link_arr, $UNI['maxlinks'], 0);
			dbn(__FILE__,__LINE__,"insert into $table_stars set star_id = ".($system['num'] + 1).", star_name = \"".addslashes($system['name'])."\", sys_type = '$system[sys_type]', x_loc = $system[x_loc], y_loc = $system[y_loc], link_1 = '$link_arr[0]', link_2 = '$link_arr[1]', link_3 = '$link_arr[2]', link_4 = '$link_arr[3]', link_5 = '$link_arr[4]', link_6 = '$link_arr[5]', metal = '$system[metal]', fuel = '$system[fuel]', darkmatter = '$system[darkmatter]', planetary_slots = '$system[planetary_slots]', wormhole = '$system[wormhole]'");

		}
		dbn(__FILE__,__LINE__,"update se_games set num_stars = '$UNI[numsystems]' where db_name = '$db_name'");
	}

	if(!empty($table_ports)) {
		foreach($ports as $port) {
			dbn(__FILE__,__LINE__,"insert into $table_ports set port_id = $port[port_id], location = ".($port['location'] + 1));
		}
	}

	if(!empty($table_bms)) {
		foreach($bmarks as $bmark) {
			dbn(__FILE__,__LINE__,"insert into $table_bms set location = '$bmark[location]', bmrkt_type = '$bmark[bm_type]', bm_name = '$bmark[bm_name]'");
		}
	}
}

//add minerals to the systems
function add_minerals(&$systems) {
	global $UNI,$extinfo;

	foreach($systems as $system) {
		if($system['num'] == 0 || $system['num'] == 1) {
			continue;
		}
		if(mt_rand(0,100) < $UNI['fuelpercent']) {
			$systems[$system['num']]['fuel'] = mt_rand($UNI['minfuel'],$UNI['maxfuel']);
		}
		if(mt_rand(0,100) < $UNI['metalpercent']) {
			$d3 = $systems[$system['num']]['metal'] = mt_rand($UNI['minmetal'],$UNI['maxmetal']);
		}
		if(mt_rand(0,100) < $UNI['darkpercent']) {
			$d3 = $systems[$system['num']]['darkmatter'] = mt_rand($UNI['mindark'],$UNI['maxdark']);
		}
		if($extinfo) {
			print("<div id='addmin1$system[num]'>-Adding minerals to system #".($system['num']+1)."<script>document.all.addmin1$system[num].scrollIntoView();</script></div>");
			flush();
		}
	}
}

//create the star systems
function make_systems_1(&$systems) {
	global $UNI,$extinfo,$tables;
	db(__FILE__,__LINE__,"select * from se_svr_star_names order by rand()");

	set_time_limit(0);
	$centre = round($UNI['size'] / 2); //centre of map
	$do_this = 0;

	if($UNI['map_layout'] == 1) { //grid layout
		$rows = round(sqrt($UNI['numsystems']));
		$row_dist = round($UNI['size'] / $rows);
		$per_col = round($UNI['numsystems'] / $rows);
		$col_dist = round($UNI['size'] / $per_col);
		$row_count = 0;
		$col_count = 0;
	} elseif($UNI['map_layout'] == 2){ //galactic core
		$one_quart = round($centre / 4);
	} elseif($UNI['map_layout'] == 3){ //clusters
		$num_clus = round(sqrt($UNI['numsystems'])) - 1; //number of clusters
		$stars_per_cluster = round($UNI['numsystems'] / $num_clus); //stars per cluster
		$cluster_size = round(($UNI['size'] / ($num_clus * 0.55)) / 2); //size of cluster in pixels
		$offset_cluster = $UNI['size'] - $cluster_size;
		$sec_count = 0;
		$basis_x = $centre;
		$basis_y = $centre;
	}

	while(($count = count($systems)) < $UNI['numsystems']) {
		$result = dbr();
		$newname = $result['name'];

		set_time_limit(0);

		//planetary slot counter
		$planet_slots = mt_rand(0,$UNI['uv_planet_slots']);

		//Add system type - For visibility - more later
		$system_check = mt_rand(0, 100);
		if($system_check <= 60)
		{
			$system_type = 0; //Normal Space - 100% Vis
		}
		elseif($system_check <= 75)
		{
			$system_type = 1; //Gas Nebula - 95% Vis
		}
		elseif($system_check <= 85)
		{
			$system_type = 2; //Dust Cloud - 90% Vis
		}
		elseif($system_check <= 95)
		{
			$system_type = 3; //Radiation Field - 85% Vis
		}
		else
		{
			$system_type = 4; //Stellar Debris - 80% Vis
		}

		$newsystem = array('num' => $count, 'x_loc' => mt_rand(0,$UNI['size']), 'y_loc' => mt_rand(0,$UNI['size']), 'links' => '', 'name' => $newname, 'fuel' => 0, 'metal' => 0, 'darkmatter' => 0, 'wormhole' => 0, 'planetary_slots' => $planet_slots, 'sys_type' => $system_type);

		if($UNI['map_layout'] == 1) { //grid layout
			if($row_count > $rows){//create a new column
				$row_count = 0;
				$col_count++;
			}
			$newsystem['x_loc'] = $col_dist * $col_count;
			$newsystem['y_loc'] = $row_dist * $row_count;
			$row_count++;
			while(system_too_close($newsystem,$systems,$UNI['mindist'])) {
				$newsystem['x_loc'] = mt_rand(0,$UNI['size']);
				$newsystem['y_loc'] = mt_rand(0,$UNI['size']);
			}

		} elseif($UNI['map_layout'] == 2) { //galactic core
			$basis = mt_rand(0,100);

			if($basis > 75){ //within centre quarter
				$div_by = 4;
			} elseif($basis > 50){ //within centre half
				$div_by = 3;
			} elseif($basis > 25){ //within half
				$div_by = 2;
			} else { //anywhere
				$div_by = 1;
			}
			while((get_sys_dist($systems[0],$newsystem) > $UNI['size']/$div_by) || system_too_close($newsystem,$systems,$UNI['mindist'])) {
				$newsystem['x_loc'] = mt_rand(0,$UNI['size']);
				$newsystem['y_loc'] = mt_rand(0,$UNI['size']);
			}

		} elseif($UNI['map_layout'] == 3) { //clusters
			if($sec_count > $stars_per_cluster){ //create new cluster
				$basis_x = mt_rand($cluster_size, $offset_cluster);
				$basis_y = mt_rand($cluster_size, $offset_cluster);
				$sec_count = 0;
			}
			$newsystem['x_loc'] = mt_rand(0, $cluster_size); //x_loc - within cluster
			if(mt_rand(0,100) > 50) { //decide offset from center of cluster.
				$newsystem['x_loc'] += $basis_x;
			} else {
				$newsystem['x_loc'] = $basis_x - $newsystem['x_loc'];
			}
			$newsystem['y_loc'] = mt_rand(0, $cluster_size); //y_loc - within cluster
			if(mt_rand(0,100) > 50) { //decide offset from center of cluster.
				$newsystem['y_loc'] += $basis_y;
			} else {
				$newsystem['y_loc'] = $basis_y - $newsystem['y_loc'];
			}
			while(system_too_close($newsystem,$systems,$UNI['mindist'])) {
				$newsystem['x_loc'] = mt_rand(0,$UNI['size']);
				$newsystem['y_loc'] = mt_rand(0,$UNI['size']);
			}
			$sec_count++;

		} elseif($UNI['map_layout'] == 4) { //circle layout
			while((get_sys_dist($systems[0],$newsystem) > $UNI['size']/2) || system_too_close($newsystem,$systems,$UNI['mindist'])) {
				$newsystem['x_loc'] = mt_rand(0,$UNI['size']);
				$newsystem['y_loc'] = mt_rand(0,$UNI['size']);
			}
		} else { #random layout
			while(system_too_close($newsystem,$systems,$UNI['mindist'])) {
				$newsystem['x_loc'] = mt_rand(0,$UNI['size']);
				$newsystem['y_loc'] = mt_rand(0,$UNI['size']);
			}
		}
		if($extinfo) {
			print("<div id='makesys1$count'>-System #".($count+1)." created.<script>document.all.makesys1$count.scrollIntoView();</script></div>");
			flush();
		}
		$systems[] = $newsystem;
	}
	//exit;
}

//link the systems
function link_systems_1(&$systems) {
	global $UNI,$extinfo;

	foreach($systems as $system) {
		set_time_limit(0);
		$numlinks = mt_rand($UNI['minlinks'],$UNI['maxlinks']);
		foreach(get_closest_systems($system,$systems,$numlinks) as $linksys) {
			make_link($systems[$system['num']],$systems[$linksys['num']]);
		}
		if($extinfo) {
			print("<div id='linksys1$system[num]'>-Created $numlinks links in system #".($system['num']+1)."<script>document.all.linksys1$system[num].scrollIntoView();</script></div>");
			flush();
		}
	}

	//add wormholes if appropriate
	if($UNI['wormholes'] > 0 && $UNI['numsystems'] > 15){
		$num_worms = ceil($UNI['numsystems'] / 35);//num wormholes to make

		$worms_placed = array();

		for($a=1; $a <= $num_worms; $a++){//loop through
			set_time_limit(0);
			$start_loc = mt_rand(2,$UNI['numsystems']);
			while(system_has_wormhole($worms_placed, $start_loc)) {
				$start_loc = mt_rand(2,$UNI['numsystems']);
			}
			$worms_placed[] = $start_loc;//push into wormhole checking array.


			$end_loc = mt_rand(1,$UNI['numsystems']);
			while(system_has_wormhole($worms_placed, $end_loc)) {
				$end_loc = mt_rand(1,$UNI['numsystems']);
			}
			$worms_placed[] = $end_loc;//push into wormhole checking array.

			//make them permanent
			$systems[$start_loc -1]['wormhole'] = $end_loc;
			if (mt_rand(0,10) > 5) {//two way wormhole
				$systems[$end_loc -1]['wormhole'] = $start_loc;
			}
		}
	}
}

//check to see if a star system has a wormhole in it already.
function system_has_wormhole($worms_placed, $this_worm) {
	foreach($worms_placed as $worm) {
		if($worm == $this_worm) {
			return true;
		}
	}
	return false;
}

//function that determines if it's ok to link to a system
function ok_to_link($sys1, $sys2) {
	global $UNI;

	//linking to itself.
	if($sys1['num'] == $sys2['num']) {
		return false;
	}

	$sys2_links = explode(',', $sys2['links']);

	//return o.k. if target still has empty links || already linked.
	if((count($sys2_links) < $UNI['maxlinks']) || in_array($sys1['num'], $sys2_links)) {
		return true;
	} else {
		return false;
	}
}

//find the closest systems to link to.
function get_closest_systems($sys,$systems,$howmany) {
	global $UNI;
	$dists = array();
	foreach($systems as $system) {
		if(ok_to_link($sys, $system)) {
			$dists[$system['num']] = get_sys_dist($sys,$system);
		}
	}
	reset($dists);
	asort($dists,SORT_NUMERIC);

	$systems_to_link = array();
	while(count($systems_to_link) < $howmany) {//find as many systems as allowed.
		if(!$present_sys = each($dists)) {//get a system out of the dist array. Return results if none.
			return $systems_to_link;
		}

		//too far away to be linked to (Sol System excepted).
		if($present_sys['value'] > $UNI['link_dist'] && $UNI['link_dist'] > 0 && $sys['num'] != 0){
			return $systems_to_link;
		}

		$systems_to_link[] = $systems[$present_sys['key']];
	}
	return $systems_to_link;
}

//work out if a system is too close to another system
function system_too_close($sys,&$systems,$within) {
	foreach($systems as $system) {
		if($system['num'] == $sys['num']) {//same system
			continue;
		}
		if($dist = get_sys_dist($sys,$system) < $within) {//too close
			return true;
		}
	}
	return false;
}

//make a single link between two systems.
function make_link(&$sys1,&$sys2) {
	if((string)$sys1['links'] != "") {
		$sys1warps = explode(',',$sys1['links']);
		if(!in_array($sys2['num'],$sys1warps)) {
			$sys1warps[] = $sys2['num'];
			$sys1['links'] = implode(',',$sys1warps);
		}
	} else {
		$sys1['links'] = $sys2['num'];
	}
	if((string)$sys2['links'] != "") {
		$sys2warps = explode(',',$sys2['links']);
		if(!in_array($sys1['num'],$sys2warps)) {
			$sys2warps[] = $sys1['num'];
			$sys2['links'] = implode(',',$sys2warps);
		}
	} else {
		$sys2['links'] = $sys1['num'];
	}
}

//work out the distance (in pixels) between two star systems.
function get_sys_dist(&$sys1,&$sys2) {
	return (int)round(sqrt(pow($sys1['x_loc']-$sys2['x_loc'],2) + pow($sys1['y_loc']-$sys2['y_loc'],2)));
}

function plus_one($a) {
	return $a + 1;
}
?>