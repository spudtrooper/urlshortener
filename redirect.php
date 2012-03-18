<?php

include_once 'include.php';

// We'll redirect to this url
$newUrl = mainUrl();

// We can maybe set this in the .htaccess, but otherwise
// we'll get it from the referring url
$url = request('u');
if (!$url) {
  $url = request('url');
}
if (!$url) {
  $url = $_ENV['HTTP_REFERER'];
}

// Replace spaces with +'s so that we can see if this one ends with '+'
$url = preg_replace('/ /','+',$url);

// If the url ends with '+', strip it off and redirect to clicks.php
if (preg_match('/\+$/',$url)) {
  $redirUrl = preg_replace('/\+$/','',$url);
  header('Location: ' . $mainUrl . '/clicks.php?url=' . $redirUrl);
  exit(0);
}

$sql = NULL;
if (isset($_REQUEST['id'])) {
  $id = request('id');
  $sql = 'SELECT * from `urls` WHERE `id` = ' . qw($id);
} else if ($url) {
  $sql = 'SELECT * from `urls` WHERE `url` = ' . qw($url);
}

// Record the hit in the database
if ($sql) {
  $query = sql_query($sql);
  if ($row = mysql_fetch_array($query)) {
    //
    // Add the click to the url row
    //
    $clicks = $row['clicks'];
    $id = $row['id'];
    $newUrl = $row['long_url'];
    $sql = 'UPDATE `urls` SET clicks=' . ($clicks+1) . ' WHERE id=' . qw($id);
    $query = sql_query($sql);
    //
    // Add a row in the clicks table to record this click
    //
    $sql = 'INSERT into `clicks` (url_id,timestamp,ip,referrer,user_agent) VALUES (';
    $sql .= qw($id) . ',';
    $sql .= qw(timestamp()) . ',';
    $sql .= qw($_SERVER['REMOTE_ADDR']) . ',';
    $sql .= qw($_SERVER['HTTP_REFERER']) . ',';
    $sql .= qw($_SERVER['HTTP_USER_AGENT']);
    $sql .= ')';
    $query = sql_query($sql);
  }
}

include 'done.php';
//
// Redirect to the new url
//
header('Location: ' . $newUrl);
exit(0);

?>