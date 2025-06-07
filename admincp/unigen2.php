<?php
include_once('../includes/nocache.inc.php');

require_once('admin.inc.php');

array_push($FILE_LIST, basename(__FILE__));

if($user['login_id'] != 1) {
	print_page('Admin','<div align="center">Admin access only.</div>');
	exit();
}

if(( !isset($_POST['sure']) ) || ( $_POST['sure'] != 'yes' )){
	$out .= 'Are you sure you want to generate a new universe? Any existing data for this game will be lost';
	get_var('Create Universe',$_SERVER['PHP_SELF'],$out,'sure','yes','index.php');
} elseif(( isset($_POST['sure']) ) && ( $_POST['sure'] == 'yes' )) {
	require_once('includes/unigen_funcs2.inc.php');

	if(!isset($preview)) {
		print_header("Build Universe","../");
		if(!isset($nodirmake)){
			mkdir("$map_path/$db_name");
		}
	}

	mt_srand((float)microtime()*1000000);
	$UNI = array();
	/*************************************************/
	/********************VARIABLES********************/
	$extinfo = 1;
	/*************************************************/
	$UNI['size'] = get_db_var('uv_universe_size');
	$UNI['numsystems'] = get_db_var('uv_num_stars');
	$UNI['mindist'] = get_db_var('uv_min_star_dist');
	$UNI['minfuel'] = get_db_var('uv_fuel_min');
	$UNI['maxfuel'] = get_db_var('uv_fuel_max');
	$UNI['fuelpercent'] = get_db_var('uv_fuel_percent');
	$UNI['minmetal'] = get_db_var('uv_metal_min');
	$UNI['maxmetal'] = get_db_var('uv_metal_max');
	$UNI['metalpercent'] = get_db_var('uv_metal_percent');
	//darkmatter
	$UNI['mindark'] = get_db_var('uv_dark_min');
	$UNI['maxdark'] = get_db_var('uv_dark_max');
	$UNI['darkpercent'] = get_db_var('uv_dark_percent');
	$UNI['map_layout'] = get_db_var('uv_map_layout');
	$UNI['uv_planets'] = get_db_var('uv_planets');
//	$UNI['uv_pla_figs'] = get_db_var('uv_planet_fighters');
//	$UNI['uv_pla_col'] = get_db_var('uv_planet_colos');
//	$UNI['uv_pla_fuel'] = get_db_var('uv_planet_fuel');
//	$UNI['uv_pla_metal'] = get_db_var('uv_planet_metal');
//	$UNI['uv_pla_org'] = get_db_var('uv_planet_org');
	$UNI['uv_planet_slots'] = get_db_var('uv_planet_slots');
	//pl slota
	$UNI['wormholes'] = get_db_var('wormholes');
	$UNI['num_ports'] = get_db_var('uv_num_ports');
	$UNI['num_bms'] = get_db_var('uv_num_bmrkt');
	$UNI['flag_research'] = get_db_var('flag_research');
	#$UNI['year_act'] = get_db_var('alternate_play_2');
	$UNI['random_events'] = get_db_var('random_events');
	$UNI['link_dist'] = get_db_var('uv_max_link_dist'); //maximum distance between linked star systems (pixels).
	$UNI['map_border'] = 25; //border on all sides around the image (stops numbers going off the edge) (pixels).
	$UNI['minlinks'] = 2; //miniumum number of links a system may have.
	$UNI['maxlinks'] = 6; //maximum number of links a system may have.
	$UNI['num_size'] = 1; //font size for system numbers (on map).
	$UNI['bg_color'] = array(0,0,0); //background colour of map
	$UNI['link_color'] = array(90,90,90); //colour of links between systems
	$UNI['num_color'] = array(0,255,255);//Most system numbers
	$UNI['num_color2'] = array(255,255,255);//Current system number
	$UNI['num_color3'] = array(255,0,0);//Sol color
	$UNI['star_color'] = array(255,255,255);
	$UNI['worm_one_way_color'] = array(230,230,64); //yellow
	$UNI['worm_two_way_color'] = array(0,230,0); //green
	$UNI['label_color'] = array(0, 255, 0);
	$UNI['print_bg_color'] = array(255,255,255); //background colour of printable map.
	$UNI['print_link_color'] = array(200,200,200); //link colour for printable map
	$UNI['print_num_color'] = array(0,0,0);//Most system numbers for printably map
	$UNI['print_star_color'] = array(0,0,0); //star colour for printable map
	$UNI['print_label_color'] = array(0, 0, 0);
	$UNI['localmapwidth'] = 196; //width of 'local area' map.
	$UNI['localmapheight'] = 196; //height of 'local area' map.

$max_races = get_db_var('max_races');
$hwc2 = get_db_var('Homeworld');
$era = get_db_var('era');

	for ($i = 0; $i < $max_races; ++$i)
	{
		$xloc = mt_rand(1, $UNI['size']);
		$yloc = mt_rand(1, $UNI['size']);
		$f = $i + 1;
		//Get the race system name
		db(__FILE__,__LINE__,"select * from races where race_ID = $f && era = $era");
		$homeworld = dbr();
		$name_star = $homeworld['hw_s_name'];

		$systems[] = array('num' => $i, 'x_loc' => $xloc, 'y_loc' => $yloc, 'links' => '', 'name' => $name_star, 'fuel' => 0, 'metal' => 0, 'darkmatter' => 0, 'wormhole' => 0,'planetary_slots' => 0);
		$ports[] = array('location' => $i);
	}
	$bmarks = array();
//	$systems = array( array('num' => 0, 'x_loc' => $UNI['size']/2, 'y_loc' => $UNI['size']/2, 'links' => '', 'name' => 'Sol', 'fuel' => 0, 'metal' => 0, 'darkmatter' => 0, 'wormhole' => 0,'planetary_slots' => 0) );
//	$ports = array( array('location' => 0) );
//	$bmarks = array();


	if(isset($extinfo)) { //print detailed information about the generation of the universe.
		$extinfo = true;
	} else {
		$extinfo = false;
	}

	if(isset($build_universe) || isset($preview) or true) {
		isset($build_universe) ? print("Generating Systems...<br />"):1;flush();
		make_systems_1($systems);
		isset($build_universe) ? print("Linking Systems...<br />"):1;flush();
			link_systems_2($systems);

		if(isset($build_universe) or true){//generating a new universe
			print("Adding Minerals...<br />");flush();
			add_minerals($systems);
			print("Adding Starports...<br />");flush();
			add_starports_se1($ports);
			if($UNI['flag_research'] == 1){
				print("Adding Blackmarkets...<br />");flush();
				add_blackmarket_se1($bmarks);
				$bm_allowed = "${db_name}_bmrkt";
			} else {
				$bm_allowed = "";
			}
			print("Saving Universe...<br />");flush();
			save_universe_se1($systems, $ports, $bmarks, "${db_name}_stars", "${db_name}_ports", $bm_allowed);
			if($UNI['uv_planets'] >= 1){
				print("Creating pre-genned planets...<br />");flush();
				planet_functionality();
			}
			random_event_placer();
			#year based system implementation.
			if($UNI['year_act'] > 0){
				dbn(__FILE__,__LINE__,"update development_time set {$db_name}_available = 0 where year_set_{$UNI['year_act']} > 0");
				dbn(__FILE__,__LINE__,"update development_time set {$db_name}_available = 1 where year_set_{$UNI['year_act']} = 0");
				print "Year reset to 0...<br />";
			}

			print("Finished Creating the universe.");
			print("<p>Universe generation complete.</p><p>You should now click the link below to continue to map generation.</p>");
print("<div id='done'><p><a href=admin_regen_maps.php>Continue onto Map Generation</a></p><script>document.all.done.scrollIntoView();</script></div>");
			print_footer();
		}
	}
} else {
	print_page('Wierd', 'Wierd access.... try again');
}
?>
