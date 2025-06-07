<?php
require "user.inc.php";

if(!empty($_GET['id']))
{
	// Get info from database
	db(__FILE__,__LINE__,"select * from ${db_name}_powerups where id = ${_GET['id']}");
	$powerup = dbr();
	if(!empty($powerup))
	{
		if($user['location'] == $powerup['location'])
		{
			$user['owner_id'] = $user['login_id'];
			$query = "update ${db_name}_${powerup['table']} set ${powerup['type']} = ${powerup['type']} + '${powerup['amount']}' where ${powerup['user_relation']} = '${user[$powerup['user_relation']]}'";
			dbn(__FILE__,__LINE__,$query);
			dbn(__FILE__,__LINE__,"delete from ${db_name}_powerups where id = ${_GET['id']}");
			print_page("Found a item", $powerup[desc]);
		}
		else
		{
			print_page("No access", "You cannot pick up powerups that are not in the same star system as you.");
		}

	}
	else
	{
		print_page("No access", "There's no powerup here. Did someone else beat you to it?");
	}
}
else
{
	print_page("No access", "Stop trying to hack the game!");
}

print_page("No access", "Stop trying to hack the game!");
?>

