<?php
require("common.inc.php");

$size = 200;

set_time_limit(0); //removes time-limit on php execution

function star_link($im,$star,$other_star) {
  global $this_star,$offset,$white,$grey;
  imageline($im,$star[x_loc] - $this_star[x_loc] + $offset,$star[y_loc] - $this_star[y_loc] + $offset
               ,$other_star[x_loc] - $this_star[x_loc] + $offset,$other_star[y_loc] - $this_star[y_loc] + $offset
               ,$grey);
}

$db_name = $db;

$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);
db(__FILE__,__LINE__,"select * from ${db_name}_stars where star_id = $location");
$this_star = dbr();
if(isset($nether)) {
	$this_star[star_id] = $this_star[star_id] * -1;
}


#db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'uv_show_warp_numbers'");
#$uv_show_warp_numbers = dbr();

#print $database;


$im = imagecreate($size,$size);
if(!isset($nether)) {
	$full_map = imagecreatefrompng("$map_path/$db/bb_full.png");
} else {
	$full_map = imagecreatefrompng("$map_path/$db/xbb_full.png");
}

$white = ImageColorAllocate($im, 255,255,255);
$grey = ImageColorAllocate($im,80,80,80);
$black = ImageColorAllocate($im, 0,0,0);
$red = ImageColorAllocate($im, 255,0,0);
$green = ImageColorAllocate($im, 0,255,0);
$blue = ImageColorAllocate($im, 0,0,255);

ImageFill($im,1,1,$black);

imagecopy($im,$full_map,0,0,$this_star[x_loc],$this_star[y_loc],200,200);

$offset = 100;

imagestring($im,1,$offset + 3,$offset - 4,"$this_star[star_id]",$white);
imagesetpixel($im,$offset,$offset,$white);

imagecolortransparent($im,$black);

if(isset($save)){
	ImagePng($im,"$map_path/$db_name/sm".$location.".png");
	print "File $map_path/$db_name/sm".$location.".png saved";
} else {
	Header("Content-type: image/png");
	ImagePng($im);
}

ImageDestroy($im);

?>