<?php
function linkpage($page_id)
{
	$out = "";
	if($page_id != 1)/*login_form page*/
	{
		echo "<a href=login_form.php>Login Form</a><br>";
	}
	if($page_id != 2)/*screenshot page*/
	{
		echo "<a href=screen.php>Screenshots</a><br>";
	}
	if($page_id != 3)/*story page*/
	{
		echo "<a href=story.php>Background Story</a> <br>";
	}
	if($page_id != 4)/*story page*/
	{
		echo "<a href=signup_form.php>Signup Form</a> <br>";
	}
	echo "<br><a href=http://webgames.servegame.com>Return to Webgames Main Site</a><br>";
	echo "<br><a href=http://www.quantum-star.com target=_blank>Quantum Star Homepage</a>";
}
?>