<?php
// Map generation functions

//deletes all image files.
function clear_images($path) {
	$dir = opendir($path);
	while($filename = readdir($dir)) {
		if(eregi('\\.png$', $filename)) {
			unlink("$path/$filename");
		}
	}
	closedir($dir);
}

//generate the three global maps.
function render_global_se1($db_name) {
	global $UNI,$extinfo,$games_dir, $systems,$preview, $map_path, $url_prefix;


	$uv_show_warp_numbers = get_db_var('uv_show_warp_numbers');
	$transparency = 1;
	$size = $UNI['size'] + ($UNI['map_border'] * 2);
	$numsize = $UNI['num_size'];

	$offset_x = $UNI['map_border'];
	$offset_y = $UNI['map_border'];

	$im = ImageCreate($size,$size);

	$color_bg = ImageColorAllocate($im, $UNI['bg_color'][0], $UNI['bg_color'][1], $UNI['bg_color'][2]);
	$color_st = ImageColorAllocate($im, $UNI['num_color'][0], $UNI['num_color'][1], $UNI['num_color'][2]);
	$color_sd = ImageColorAllocate($im, $UNI['star_color'][0], $UNI['star_color'][1], $UNI['star_color'][2] );
	$color_sl = ImageColorAllocate($im, $UNI['link_color'][0], $UNI['link_color'][1], $UNI['link_color'][2] );
	$color_sh = ImageColorAllocate($im, $UNI['num_color3'][0], $UNI['num_color3'][1], $UNI['num_color3'][2] );
	$color_l = ImageColorAllocate($im, $UNI['label_color'][0], $UNI['label_color'][1], $UNI['label_color'][2] );

	$worm_1way_color = ImageColorAllocate($im,$UNI['worm_one_way_color'][0], $UNI['worm_one_way_color'][1], $UNI['worm_one_way_color'][2] );
	$worm_2way_color = ImageColorAllocate($im,$UNI['worm_two_way_color'][0], $UNI['worm_two_way_color'][1], $UNI['worm_two_way_color'][2] );

	//process stars
	foreach($systems as $star){
		if($star['links'] != ""){//don't link all systems to 1 automatically.
			$star_links = explode(',', $star['links']);
			$star_id = $star['num'];

			foreach($star_links as $link){ //make star links
				if($link < 1){
					continue 1;
				}
				$other_star = $systems[$link-1];
				imageline($im, ($star['x_loc'] + $offset_x), ($star['y_loc'] + $offset_y), ($other_star['x_loc'] + $offset_x), ($other_star['y_loc'] + $offset_y), $color_sl);
			}
		}
		if($UNI['wormholes'] == 2) {
			if(!empty($star['wormhole'])) {//Wormhole Manipulation
				$other_star = $systems[$star['wormhole']-1];
				if($other_star['wormhole'] == $star_id){ //two way
					imageline($im, ($star['x_loc'] + $offset_x), ($star['y_loc'] + $offset_y), ($other_star['x_loc'] + $offset_x), ($other_star['y_loc'] + $offset_y), $worm_2way_color);
				} else { //one way
					imageline($im, ($star['x_loc'] + $offset_x), ($star['y_loc'] + $offset_y), ($other_star['x_loc'] + $offset_x), ($other_star['y_loc'] + $offset_y), $worm_1way_color);
				}
			}
		}
	}
db5(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'Homeworld'");
$hwc = dbr5();
$hwc2 = $hwc['value'];
db4(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'max_races'");
$teams = dbr4();
$max_races = $teams['value'];

	foreach($systems as $star){
		$star_id = $star['num'];
		if($star_id <= $max_races) {//Place and Highlight sol
			if($uv_show_warp_numbers == 1) {
				imagestring($im, $numsize, ($star['x_loc'] + $offset_x + 3), ($star['y_loc'] + $offset_y - 4), $star_id, $color_sh);
			}
			imagesetpixel($im, ($star['x_loc'] + $offset_x), ($star['y_loc'] + $offset_y), $color_sh);
		} else { //placec normal Star
			if($uv_show_warp_numbers == 1) {
				imagestring($im, $numsize, ($star['x_loc'] + $offset_x + 3), ($star['y_loc'] + $offset_y - 4), $star_id, $color_st);
			}
			imagesetpixel($im, ($star['x_loc'] + $offset_x), ($star['y_loc'] + $offset_y), $color_sd);
		}
	}

	if(isset($transparency)) //##//##//
	{
		$ref_im = ImageCreate($size,$size);
		$color_ty = ImageColorAllocate($ref_im, $UNI['bg_color'][0], $UNI['bg_color'][1], $UNI['bg_color'][2]);
		//$aliengreen = ImageColorAllocate($im, 0,181,0);
		ImageColorAllocate($im, $UNI['bg_color'][0], $UNI['bg_color'][1], $UNI['bg_color'][2]);
		ImageCopy($ref_im, $im, 0, 0, 0, 0, $size, $size);
		//$color_ty = ImageColorAllocate($ref_im, $UNI['bg_color'][0], $UNI['bg_color'][1], $UNI['bg_color'][2]);
		$aliengreen = ImageColorAllocate($ref_im, 0,181,0);
		imagecolortransparent($ref_im, $color_ty);
		imagestring($ref_im, 5, (($size/2)-80), 5, "Reference World Map - For Player Use", $aliengreen);
	}

	//for just a preview we can quite while we're ahead.
	if(isset($preview)){
		Header("Content-type: image/png");
		ImagePng($im);
		ImageDestroy($im);
		exit;
	}

	//Draw title
	imagestring($im, 5, (($size/2)-80), 5, "Universal Star Map", $color_l);

	//Create buffer image
	$bb_im = ImageCreate(($UNI['size'] + $UNI['localmapwidth']), ($UNI['size'] + $UNI['localmapheight']));

	ImageColorAllocate($im, $UNI['bg_color'][0], $UNI['bg_color'][1], $UNI['bg_color'][2]);
	ImageCopy($bb_im, $im, ($UNI['localmapwidth'] / 2), ($UNI['localmapheight'] / 2), $offset_x, $offset_y, $UNI['size'], $UNI['size']);

	//Create printable map
	$p_im = ImageCreate($size,$size);
	ImageColorAllocate($p_im, $UNI['print_bg_color'][0], $UNI['print_bg_color'][1], $UNI['print_bg_color'][2]);
	ImageCopy($p_im, $im, 0, 0, 0, 0, $size, $size);

	//Replace colors
	$index = ImageColorExact($p_im, $UNI['bg_color'][0], $UNI['bg_color'][1], $UNI['bg_color'][2]);
	ImageColorSet($p_im, $index, $UNI['print_bg_color'][0], $UNI['print_bg_color'][1], $UNI['print_bg_color'][2]);
	$index = ImageColorExact($p_im, $UNI['link_color'][0], $UNI['link_color'][1], $UNI['link_color'][2]);
	ImageColorSet($p_im, $index, $UNI['print_link_color'][0], $UNI['print_link_color'][1], $UNI['print_link_color'][2]);
	$index = ImageColorExact($p_im, $UNI['num_color'][0], $UNI['num_color'][1], $UNI['num_color'][2]);
	ImageColorSet($p_im, $index, $UNI['print_num_color'][0], $UNI['print_num_color'][1], $UNI['print_num_color'][2]);
	$index = ImageColorExact($p_im, $UNI['num_color3'][0], $UNI['num_color3'][1], $UNI['num_color3'][2]);
	ImageColorSet($p_im, $index, $UNI['print_num_color'][0], $UNI['print_num_color'][1], $UNI['print_num_color'][2]);
	$index = ImageColorExact($p_im, $UNI['star_color'][0], $UNI['star_color'][1], $UNI['star_color'][2]);
	ImageColorSet($p_im, $index, $UNI['print_star_color'][0], $UNI['print_star_color'][1], $UNI['print_star_color'][2]);

	//Draw new label
	ImageFilledRectangle($p_im, 0, 0, $size, $UNI['map_border'], ImageColorExact($p_im, $UNI['print_bg_color'][0], $UNI['print_bg_color'][1], $UNI['print_bg_color'][2]));
	imagestring($p_im, 5, (($size/2)-80), 5, "Printable Star Map", ImageColorExact($p_im, $UNI['print_label_color'][0], $UNI['print_label_color'][1], $UNI['print_label_color'][2]));

	#if($UNI['transparent'] == 0) {
	#	imagecolortransparent($im,$UNI['bg_color']);
	#	imagecolortransparent($bb_im,$UNI['bg_color']);
	#}

	//Save map and finish
	ImagePng($im, "$map_path/$db_name/sm_full.png");
	ImagePng($bb_im, "$map_path/$db_name/bb_full.png");
	ImagePng($p_im, "$map_path/$db_name/psm_full.png");
	if(isset($transparency)) //##//##//
	{
		ImagePng($ref_im, "$map_path/$db_name/ref_full.png");
	}

	if($extinfo) {
		print("<br /><br /><br /><hr><img src='$url_prefix/maps/$db_name/sm_full.png' onLoad='this.scrollIntoView();'>");
		flush();
		print("<br /><br /><br /><hr><img src='$url_prefix/maps/$db_name/ref_full.png' onLoad='this.scrollIntoView();'>"); //##//##//
		flush();
		print("<br>");
	}
	ImageDestroy($im);
	ImageDestroy($bb_im);
	ImageDestroy($p_im);
}



//draw the local maps.
function render_local_se1($db_name) {
	global $UNI, $extinfo,$games_dir, $map_path, $url_prefix;
	$full_map = imagecreatefrompng("$map_path/$db_name/bb_full.png");
	db(__FILE__,__LINE__,"select * from ${db_name}_stars");
	while($star = dbr()) {
		$im = imagecreate($UNI['localmapwidth'], $UNI['localmapheight']);
		$color_bg = $color_bg = ImageColorAllocate($im, $UNI['bg_color'][0], $UNI['bg_color'][1], $UNI['bg_color'][2]);
		$color_ht = ImageColorAllocate($im, $UNI['num_color2'][0], $UNI['num_color2'][1], $UNI['num_color2'][2]);
		$color_hd = ImageColorAllocate($im, $UNI['num_color2'][0], $UNI['num_color2'][1], $UNI['num_color2'][2]);
		$black = ImageColorAllocate($im,0,0,0);
		imagecopy($im, $full_map, 0, 0, $star['x_loc'], $star['y_loc'], $UNI['localmapwidth'], $UNI['localmapheight']);
		imagestring($im, $UNI['num_size'], ($UNI['localmapwidth'] / 2) + 3, ($UNI['localmapheight'] / 2) - 4, "$star[star_id]", $color_ht);
		imagesetpixel($im, ($UNI['localmapwidth'] / 2), ($UNI['localmapheight'] / 2), $color_hd);
		imagecolortransparent($im,$color_bg);
		ImagePng($im, "$map_path/$db_name/sm$star[star_id].png");
		if($extinfo) {
//			print("<br /><img src='$url_prefix/maps/$db_name/sm$star[star_id].png' onLoad='this.scrollIntoView();'>");
			flush();
		}
		ImageDestroy($im);
	}
	ImageDestroy($full_map);
}
?>