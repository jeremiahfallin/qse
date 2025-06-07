<?php
require("user.inc.php");

array_push($FILE_LIST, basename(__FILE__));

$filename = 'diary.php';

// max number of entries in diary.
if($user[login_id] != 1){
	$max = 50;
} else {
	$max = 200;
}

db(__FILE__,__LINE__,"select count(entry_id) from ${db_name}_diary where login_id = $user[login_id]");
$num_ent = dbr();

$rs = "<p><a href=$filename>Back to Diary</a><br />";

//Add message to diary.
if($log_ent){
	if($num_ent[0] > $max) {
		print_page("Error","<p>Your Diary is full");
	}
    db(__FILE__,__LINE__,"select text,sender_name from ${db_name}_messages where message_id = '$log_ent' && (login_id = '$user[login_id]' || login_id < 0)");
	$message_text = dbr();
	$message_text[sender_name] = stripslashes($message_text[sender_name]);
	$message_text[sender_name] = addslashes($message_text[sender_name]);
	$message_text[text] = stripslashes($message_text[text]);
	$message_text[text] = addslashes($message_text[text]);
	dbn(__FILE__,__LINE__,"insert into ${db_name}_diary (timestamp,login_id, entry, topic) values(".time().",'$user[login_id]','<b class=b1>$message_text[sender_name]</b> - $message_text[text]','messages')");
	$error_str .= "Message Successfully Copied to Diary.<p>";
}

if($term) {


// news search
if($term) {

	$text .= search_the_db(__FILE__,__LINE__,$term,$can_do_stop,"diary",$filename,"entry");
	print_page("Diary Search",$text);
}

	if(empty($ret_text)){ // no results found
		$text .= "<br />There are no entries of <b class=b1>$term</b> in your diary. Please broaden your search.<br /><br />";
	} else {
		print_page("Diary Search",$ret_text);
	}
}

// Adds
if($add==1) {
	if($num_ent[0] > $max) {
		print_page("Error","<p>Your diary is full");
	}
	$text .= "<form method=post action=diary.php>";
	$text .= "Select a topic for it to go under:<br />";
	$text .= "<select name=topic>";
	$text .= "<option value=player>Players";
	$text .= "<option value=clan>Clan";
	$text .= "<option value=universe>Universe";
	$text .= "<option value=ship>Ships";
	$text .= "<option value=messages>Messages";
	$text .= "<option value=misc>Misc";
	$text .= "<option value=other>Other";
	$text .= "</select>";
	$text .= "<p>Please enter your diary entry:<br />";
	$text .= "<textarea name=add_ent value='$var_bount' cols=50 rows=20 wrap=soft></textarea>";
	$text .= "<p><input type=submit value=Submit></form><p>";
	print_page("Add Diary Entry",$text);
}


if($add_ent){
	if($num_ent[0] > $max) {
		print_page("Error","<p>Your diary is full");
	}
    dbn(__FILE__,__LINE__,"insert into ${db_name}_diary (timestamp,login_id, entry, topic) values(".time().",'$user[login_id]','$add_ent','$topic')");
	$text .= "Entry Successfully Made.<p>";
}


//Deletes
if($delete) {
	db(__FILE__,__LINE__,"select login_id from ${db_name}_diary where entry_id = '$delete'");
	$entry = dbr();
	if($entry[0] != $user[login_id]){
		$text = "You may not delete other peoples entries.";
	} else {
		dbn(__FILE__,__LINE__,"delete from ${db_name}_diary where entry_id='$delete'");
		$text .= "Entry Successfully Deleted.<p>";
	}
}
if($delete_all){
	if($sure != 'yes') {
		get_var('Delete all','diary.php',"Are you sure you want to delete all entries in your diary?",'sure','yes');
		$rs = "<a href=$filename>Back to Diary</a>";
	} else{
		dbn(__FILE__,__LINE__,"delete from ${db_name}_diary where login_id='$user[login_id]'");
		$text .= "Diary Successfully Cleared.<p>";
		$rs = "<a href=$filename>Back to Diary</a>";
	}
}

if($del_select){
	if(!$del_ent){
	    $text .= "No Entries selected to be deleted.<p>";
	} else {
		$q_m = 0;
		$temp734 = $del_ent;
		while ($var = each($temp734)) {
		  dbn(__FILE__,__LINE__,"delete from ${db_name}_diary where entry_id = '$var[value]' && login_id = '$user[login_id]'");
		  $q_m++;
		}
		$text .= "<b>$q_m</b> Entries(s) Deleted.<p>";
	}
}


//Edits
if($edit){
	db(__FILE__,__LINE__,"select * from ${db_name}_diary where entry_id = '$edit'");
	$entry = dbr();
	if($entry[login_id] != $user[login_id]){
		$text = "You may not edit other peoples entries.";
	} else {
		$selected[$entry[topic]] = " selected";
		echo $selected[clan];
		$text .= "<form method=post action=diary.php>";
	    $text .= "<input type=hidden name=edit2 value='$entry[entry_id]'>";
		$text .= "Select Topic here:<br />";
		$text .= "<select name=edit_topic>";
		$text .= "<option value=player".$selected[player].">Players";
		$text .= "<option value=clan".$selected[clan].">Clan";
		$text .= "<option value=universe".$selected[universe].">Universe";
		$text .= "<option value=ship".$selected[ship].">Ships";
		$text .= "<option value=ship".$selected[messages].">Messages";
		$text .= "<option value=misc".$selected[misc].">Misc";
		$text .= "<option value=other".$selected[other].">Other";
		$text .= "</select>";
		$text .= "<p>Change Text here:<br />";
		$text .= "<textarea name=edit_ent cols=50 rows=20 wrap=soft>$entry[entry]</textarea>";
		$text .= "<p><input type=submit value=Submit></form><p>";
		print_page("Add Diary Entry",$text);
	}
}
if($edit2){
	db(__FILE__,__LINE__,"select login_id from ${db_name}_diary where entry_id = '$edit2'");
	$entry = dbr();
	if($entry[login_id] != $user[login_id]){
		$text = "You may not edit other peoples entries.";
	} else {
    dbn(__FILE__,__LINE__,"delete from ${db_name}_diary where entry_id='$edit2'");
	dbn(__FILE__,__LINE__,"insert into ${db_name}_diary (timestamp,login_id, entry, topic) values(".time().",'$user[login_id]','$edit_ent','$edit_topic')");
	$text .= "Entry Successfully Changed.<p>";
	}
}



// Lists
if($list){
	if($list="player"){
	} elseif($list="clan"){
	} elseif($list="universe"){
	} elseif($list="ship"){
	} elseif($list="misx"){
	} elseif($list="other"){
	} else {
	db2(__FILE__,__LINE__,"select * from ${db_name}_diary where login_id = '$user[login_id]' order by timestamp desc");
	$entry = dbr2();
	}
	$text .= make_table(array("Time","Topic","Entry"));

	while($entry) {
	 $entry[entry_id] = "- <a href=$filename?edit=$entry[entry_id]>Edit</a> - <a href=$filename?delete=$entry[entry_id]>Delete</a>";
	  $text .= make_row(array("<b>".date("M d - H:i",$entry[timestamp])."</b>","<b class=b1>".$entry[topic]."</b>",$entry[entry],$entry[entry_id]));
		$entry = dbr2();
	}
	$text .= "</table><br />";
}

// Top of front diary page
db(__FILE__,__LINE__,"select count(entry_id) from ${db_name}_diary where login_id = $user[login_id]");
$num_ent = dbr();
$text .= "Using this <b class=b1>Diary</b>, you may store up to <b>$max</b> pieces of information. <br />There are presently <b>$num_ent[0]</b> entries in your diary.";
if($user[login_id] == 1){
	$text .= "<p>The Admin Diary does <b class=b1><b>NOT</b> get wiped</b> when the game resets.";
}

if(!$num_ent[0]){
	if($num_ent[0] < $max) {
		$text .= "<p><a href=$filename?add=1>Add entry</a>";
	} else {
		$text .= "<br />Your diary is full";
	}
	$text .= "<p>There are no entries in your diary.";
} else {
	if(!$show){
		$text .= "<p><a href=$filename?show=topic>Order by topic</a>";
		db2(__FILE__,__LINE__,"select * from ${db_name}_diary where login_id = '$user[login_id]' order by timestamp desc");
	} else {
		$text .= "<p><a href=$filename>Order by date</a>";
		db2(__FILE__,__LINE__,"select * from ${db_name}_diary where login_id = '$user[login_id]' order by topic");
	}
	if($num_ent[0] < $max) {
		$text .= "<br /><a href=$filename?add=1>Add entry</a>";
	} else {
		$text .= "<br />Your diary is full";
	}
	$text .= "<br /><br />Search for a term in the Diary:";
	$text .= "<form method=post action=$filename>";
	$text .= "<input type=text name=term size=10>";
	$text .= " - <input type=submit value=Search></form><p>";
	$text .= "<p>Contents of the diary:";
	$text .= make_table(array("Time","Topic","Entry"));
	$entry = dbr2();
	if($num_ent[0]){
		$text .= "<form method=post action=diary.php name=quick_del><input type=hidden name=del_select value=1>";
	}
	while($entry) {
		$entry[entry_id] = "- <a href=$filename?edit=$entry[entry_id]>Edit</a> - <a href=$filename?delete=$entry[entry_id]>Delete</a> - <input type=checkbox name=del_ent[$entry[entry_id]] value=$entry[entry_id]>";
		$text .= make_row(array("<b>".date("M d - H:i",$entry[timestamp])."</b>","<b class=b1>".$entry[topic]."</b>",$entry[entry],$entry[entry_id]));
		$entry = dbr2();
	}
	$text .= "</table><br />";
}

if ($num_ent[0] > 1){
		$text .= "<br /><input type=submit value=\"Delete Selected\"></form>";

	$text .= "<br /><a href=$filename?delete_all=1>Clear Diary</a> of all entries.";
}
$rs = "<p><a href=location.php>Back to Star System</a>";
// print page
print_page("Diary of the Fleet",$text);
?>