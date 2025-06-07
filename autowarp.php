<?php
include_once("includes/nocache.inc.php");

require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

sudden_death_check($user);
set_time_limit(0);

db(__FILE__,__LINE__,"select count(star_id) from ${db_name}_stars");
$max_sect = dbr();
if(!isset($dest_sector)) {
  get_var("AutoWarp", "autowarp.php", "Note: Autowarp will not necassarily find the shortest route, but normally it does.<p>Find route to system:", "dest_sector","");
} elseif($dest_sector < 1 || $dest_sector > $max_sect[0] || $user[location] == $dest_sector){
	print_page("AutoWarp","That is an invalid destination.");
}

$start_sector = $user[location];
$ouptut_str = "";

for($i=1; $i <= 150; $i++) {
  $visit[$i] = 0;
  $pred[$i] = 0;
  $dist[$i] = 150;
}
$dist[$dest_sector] = 0;

$search_queue = array();
array_unshift($search_queue, $dest_sector);

while($search_sector = array_pop($search_queue)) {
  db(__FILE__,__LINE__,"SELECT link_1, link_2, link_3, link_4, link_5, link_6 FROM ${db_name}_stars WHERE star_id = '$search_sector'");
  $adj_sectors = dbr();

  foreach($adj_sectors as $vertex) {
    if($vertex == $start_sector) {
      $j = $dist[$search_sector] + 1;
      $output_str .= "Distance is <b>$j warps</b><br />Path is <b>$vertex - ";
      $path = "";
      for($linkback = $search_sector; $linkback != $dest_sector && $j; $j--) {
        $output_str .= "$linkback - ";
        $path .= "$linkback+";
        $linkback = $pred[$linkback];
      }
      $output_str .= "$linkback</b><br /><br />";
      $path .= "$linkback";
      $output_str .= "<a href=location.php?autowarp=$path>Set Course For System $dest_sector</a>";
      print_page("AutoWarp", $output_str);
    }
    if($vertex > 0 && $visit[$vertex] == 0) {
      $visit[$vertex] = 1;
      $dist[$vertex] = $dist[$search_sector] + 1;
      $pred[$vertex] = $search_sector;
      array_unshift($search_queue, $vertex);
    }
  }
}
print_page("AutoWarp","No path Found");
?>