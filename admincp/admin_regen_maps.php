<?php




include_once('../includes/nocache.inc.php');
require_once('admin.inc.php');
$enable_gzip = "0";
$CONFIG['enable_gzip'] = 0;

array_push($FILE_LIST, basename(__FILE__));

set_time_limit(0); //a full minute to run it.
print(str_repeat(' ',300)."\n");flush(); //Make sure IE's 300 byte buffer is filled

if($user['login_id'] != 1) { //(server) admin only.
	print_page('Error','Admin only');
} elseif(empty($_POST['sure'])) {
	$sure_str = '<p>Are you sure you want to regenerate maps for the current universe?</p><p>This may take some time and all current maps will be replaced.</p>';
	get_var('Regenerate Maps','admin_regen_maps.php',$sure_str,'sure','yes');
} else {
	require_once('includes/unigen_funcs.inc.php');
	require_once('includes/mapgen_funcs.inc.php');
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

	db(__FILE__,__LINE__,"select * from ${db_name}_stars");

	$systems = array();
	while($stars = dbr()) {
		$links = implode(',', array($stars['link_1'],$stars['link_2'],$stars['link_3'],$stars['link_4'],$stars['link_5'],$stars['link_6']));
		$systems[] = array('num' => $stars['star_id'], 'x_loc' => $stars['x_loc'], 'y_loc' => $stars['y_loc'], 'links' => $links, 'name' => $stars['star_name'], 'fuel' => $stars['fuel'], 'metal' => $stars['metal'], 'darkmatter' => $stars['darkmatter'], 'wormhole' => $stars['wormhole'],'planetary_slots' => $stars['planetary_slots']);
	}

	print_header("Regenerate Maps","../");
	print("<a href=index.php>Go to AdminCP.</a>");
	print("<br />Deleting old images...<br />");
	flush();
	clear_images("$map_path/$db_name");
	print("Rendering global map...<br />");
	flush();
	render_global_se1($db_name);
	print("Rendering local maps...<br />");
	flush();
	render_local_se1($db_name);
	print("Finished");
	print_footer();
	print("<div id='done'><a href=index.php>Go to AdminCP.</a><script>document.all.done.scrollIntoView();</script></div>");

}
?>