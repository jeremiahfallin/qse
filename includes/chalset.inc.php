<?php

$challenge = md5(uniqid(rand(), true)); echo("1: $challenge ");
db(__FILE__,__LINE__,"insert into qbase_challenge_record (sessid, challenge) values ('' ,'$challenge')");

?>