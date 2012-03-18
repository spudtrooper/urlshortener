<?php

include_once 'config.php';
include_once 'util.php';

$db = mysql_connect(DB_HOST,DB_USER,DB_PASS);
if (!$db) { 
	fatal('ERROR: ' . mysql_error() . '\n'); 
}

// Get a good name of this page that doesn't contain a page at the end
$phpSelf = preg_replace('/\/[^\/]+$/','/',$_SERVER['PHP_SELF']);

?>