<?php
// This is a simple header used to prevent the browser from caching the page
// It ensure that every page is loaded directly from the server, and hence contains the most recent game data


header("Expires: Mon, 29 Jul 1999 17:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>