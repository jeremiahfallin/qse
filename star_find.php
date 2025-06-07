<?php
require("common.inc.php");
if(!isset($sys1) || !isset($sys2)) {
  echo "Required parem's missing!";
  exit;
}
$db = db_connect($database_host, $database_user, $database_password, $database, $database_persistent);
db(__FILE__,__LINE__,"select x_loc,y_loc from ${db_name}_stars where star_id = '$sys1'");
$star_one = dbr();

db(__FILE__,__LINE__,"select x_loc,y_loc from ${db_name}_stars where star_id = '$sys2'");
$star_two = dbr();

db(__FILE__,__LINE__,"select value from ${db_name}_db_vars where name = 'uv_universe_size '");
$size_record = dbr();
$size = $size_record[value] + 50;

$im = imagecreatefrompng("maps/$db_name/ref_full.png");

$red = ImageColorAllocate($im, 255,50,50);
$red2 = ImageColorAllocate($im, 255,150,150);
$green = ImageColorAllocate($im, 50,255,50);
$green2 = ImageColorAllocate($im, 50,255,150);

imagestring($im,3,($star_one[x_loc]-10),$star_one[y_loc]-5,"You are here",$red2);

imagearc($im, ($star_one[x_loc]+30), ($star_one[y_loc]+25), 30, 30, 0, 360, $red);

if($sys1 != $sys2){
	#imagestring($im,3,($star_two[x_loc]-15),$star_two[y_loc]+40,"System $sys2 here",$green2);
	imagearc($im, ($star_two[x_loc]+29), ($star_two[y_loc]+25), 35, 35, 0, 360, $green);
}
Header("Content-type: image/png");
ImagePng($im);

ImageDestroy($im);
?>