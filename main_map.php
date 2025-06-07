<?php
set_time_limit(0);

require("common.inc.php");

$query = "";

$numsize = 1;

if(isset($db)){
  $db_name = $db;
}

function star_link($im,$star,$other_star) {
  global $this_star,$offset,$white,$grey,$lgrey,$print;
  if($print) {
    imageline($im,$star[x_loc] - $this_star[x_loc] + $offset,$star[y_loc] - $this_star[y_loc] + $offset
               ,$other_star[x_loc] - $this_star[x_loc] + $offset,$other_star[y_loc] - $this_star[y_loc] + $offset
               ,$lgrey);
  } else {
    imageline($im,$star[x_loc] - $this_star[x_loc] + $offset,$star[y_loc] - $this_star[y_loc] + $offset
               ,$other_star[x_loc] - $this_star[x_loc] + $offset,$other_star[y_loc] - $this_star[y_loc] + $offset
               ,$grey);
  }

//  imagesetpixel($im,$other_star[x_loc] - $this_star[x_loc] + $offset,$other_star[y_loc] - $this_star[y_loc] + $offset,$white);
//  imagestring($im,1,$star[x_loc] - $this_star[x_loc] + $offset + 3,$star[y_loc] - $this_star[y_loc] + $offset - 4,"$star[star_id]",$white);
//  imagestring($im,1,$other_star[x_loc] - $this_star[x_loc] + $offset + 3,$other_star[y_loc] - $this_star[y_loc] + $offset - 4,"$other_star[star_id]",$white);
}

function worm_link($im,$star,$other_star,$w) {
  global $this_star,$offset,$white,$yellow,$green,$lgrey,$print;
	  if($w==1){
		imageline($im,$star[x_loc] - $this_star[x_loc] + $offset,$star[y_loc] - $this_star[y_loc] + $offset
				   ,$other_star[x_loc] - $this_star[x_loc] + $offset,$other_star[y_loc] - $this_star[y_loc] + $offset,$yellow);
	  } else {
		imageline($im,$star[x_loc] - $this_star[x_loc] + $offset,$star[y_loc] - $this_star[y_loc] + $offset
				   ,$other_star[x_loc] - $this_star[x_loc] + $offset,$other_star[y_loc] - $this_star[y_loc] + $offset,$green);
	  }
 }


$link = mysql_connect("$database_host","$database_user","$database_password");
mysql_select_db(__FILE__,__LINE__,$database);

if(isset($nether)) {//###
	$location = -1;//###
} else {//###
	$location = 1;//###
}//###
db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $location");
$this_star = dbr();

if(isset($nether)) {//###
	$this_star[star_id] = $this_star[star_id] * -1;
}


# read uv size from db

db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'uv_universe_size '");
$size_record = dbr();
if($big_buffer) {
   $size = $size_record[value] + 200;
} else {
   $size = $size_record[value] + 50;
}

db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'uv_show_warp_numbers'");
$uv_show_warp_numbers = dbr();

$im = ImageCreate($size,$size);

$white = ImageColorAllocate($im, 255,255,255);
$grey = ImageColorAllocate($im,90,90,90);
$lgrey = ImageColorAllocate($im,150,150,150);
$black = ImageColorAllocate($im, 0,0,0);
$red = ImageColorAllocate($im, 255,0,0);
$green = ImageColorAllocate($im, 0,255,0);
$blue = ImageColorAllocate($im, 100,200,200);
$yellow = ImageColorAllocate($im, 255,255,64);
$aliengreen = ImageColorAllocate($im, 0,181,0);

if($print) {
  ImageFill($im,1,1,$white);
} else {
  ImageFill($im,1,1,$black);
}


$offset = $size / 2;
if(!isset($nether)) {//###
	db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id > 0");
} elseif(isset($nether)) {//###
	db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id < 0");
}//###


//where x_loc between ".$star[x_loc] - $offset." and ".$star[x_loc] + $offset." && y_loc between".$star[y_loc] - $offset." and ".$star[y_loc] + $offset);
$star = dbr();
$star_query = $query;
while($star) {
  if($star[link_1]) {
    db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_1]");
    $other_star = dbr();

	if(isset($nether)) {//###
		$other_star[star_id] = $other_star[star_id] * -1;
	}

    star_link($im,$star,$other_star);
/*    imageline($im,$star[x_loc] - $this_star[x_loc] + $offset,$star[y_loc] - $this_star[y_loc] + $offset
                 ,$other_star[x_loc] - $this_star[x_loc] + $offset,$other_star[y_loc] - $this_star[y_loc] + $offset
		 ,$grey);
    imagesetpixel($im,$other_star[x_loc] - $this_star[x_loc] + $offset,$other_star[y_loc] - $this_star[y_loc] + $offset,$white);
*/
  }
  if($star[link_2]) {
    db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_2]");
    $other_star = dbr();
	if(isset($nether)) {//###
		$other_star[star_id] = $other_star[star_id] * -1;
	}
    star_link($im,$star,$other_star);
  }
  if($star[link_3]) {
    db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_3]");
    $other_star = dbr();
	if(isset($nether)) {//###
		$other_star[star_id] = $other_star[star_id] * -1;
	}
    star_link($im,$star,$other_star);
  }
  if($star[link_4]) {
    db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_4]");
    $other_star = dbr();
	if(isset($nether)) {//###
		$other_star[star_id] = $other_star[star_id] * -1;
	}
    star_link($im,$star,$other_star);
  }
  if($star[link_5]) {
    db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_5]");
    $other_star = dbr();
	if(isset($nether)) {//###
		$other_star[star_id] = $other_star[star_id] * -1;
	}
    star_link($im,$star,$other_star);
  }
  if($star[link_6]) {
    db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $star[link_6]");
    $other_star = dbr();
	if(isset($nether)) {//###
		$other_star[star_id] = $other_star[star_id] * -1;
	}
    star_link($im,$star,$other_star);
  }
  $query = $star_query;
  $star = dbr();
}

db(__FILE__,__LINE__,"select * from ${db_name}_db_vars where name = 'wormholes'");
$worm_val = dbr();
if($worm_val[value] == 2 && !isset($nether)) {//###
	db2(__FILE__,__LINE__,"select * from ${db_name}_stars where wormhole != 0");
	$i=0;
	$stuff[0] = 0;
	while ($worms = dbr2()) {
		if(in_array("$worms[wormhole]", $stuff)){
			$send=0;
			db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $worms[wormhole]");
			$other_star = dbr();
			worm_link($im,$worms,$other_star,$send);
		} else {
			$send=1;
			$stuff[$i] = $worms[star_id];
			$i++;
			db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $worms[wormhole]");
			$other_star = dbr();
			worm_link($im,$worms,$other_star,$send);
		}
	}
}

if(!isset($nether)) {//###
	db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id > 0");
} else {//###
	db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id < 0");
}//###
$star = dbr();
while($star) {
	if(isset($nether)) {
		$star[star_id] = $star[star_id] * -1;
	}

  imagesetpixel($im,$star[x_loc] - $this_star[x_loc] + $offset,$star[y_loc] - $this_star[y_loc] + $offset,$white); //sets star point colour and position
  if($uv_show_warp_numbers[value]) {
    if($print) {
	  imagestring($im,$numsize,$star[x_loc] - $this_star[x_loc] + $offset + 3,$star[y_loc] - $this_star[y_loc] + $offset - 4,"$star[star_id]",$black);
    } else {
      imagestring($im,$numsize,$star[x_loc] - $this_star[x_loc] + $offset + 3,$star[y_loc] - $this_star[y_loc] + $offset - 4,"$star[star_id]",$blue);
    }
  }
  $star = dbr();
}

if(!$big_buffer && !$print) {
  imagestring($im,5,(($size/2)-80),5,"Star Map",$green);
}

if($print) {
  imagestring($im,5,(($size/2)-80),5,"Printable Star Map",$black);
}

imagestring($im,$numsize,$offset + 3,$offset - 4,"$this_star[star_id]",$red);
imagesetpixel($im,$offset,$offset,$red);

if(!$print){
  imagecolortransparent($im,$black);
}

if(isset($save)){
	if(isset($big_buffer) && !isset($nether)) {
		ImagePng($im,"$map_path/$db_name/bb_full.png");
		print "File $map_path/$db_name/bb_full.png saved<br />\n";
	} elseif($print && !isset($nether)) {
		ImagePng($im,"$map_path/$db_name/psm_full.png");
		print "File $map_path/$db_name/psm_full.png saved<br />\n";
	} elseif (!isset($nether)) {
		ImagePng($im,"$map_path/$db_name/sm_full.png");
		print "File $map_path/$db_name/sm_full.png saved<br />\n";
    } elseif(isset($big_buffer) && isset($nether)) {
    	ImagePng($im,"$map_path/$db_name/xbb_full.png");
		print "File $map_path/$db_name/xbb_full.png saved<br />\n";
    } #elseif($print && isset($nether)) {
    	#ImagePng($im,"$map_path/$db_name/xpsm_full.png");
		#print "File $map_path/$db_name/xpsm_full.png saved<br />\n";
    #} else {
    	#ImagePng($im,"$map_path/$db_name/xsm_full.png");
		#print "File $map_path/$db_name/xsm_full.png saved<br />\n";
    #}


} else {
	Header("Content-type: image/png");
	ImagePng($im);
}

ImageDestroy($im);

?>