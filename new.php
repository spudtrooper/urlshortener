<?php

include_once 'include.php';

$longUrl = requestUrl();
$format = $_REQUEST['format'];
if (!$format) {
  $format = 'xml';
}

apiHeader($format);

$newUrlResult = addNewUrl($longUrl);
if ($newUrlResult->isOK()) {
  $result   = fullUrl($newUrlResult->getResult());
  $msg      = $newUrlResult->getMessage();
  $title = findLastTitle($longUrl);
  if ($format == 'xml') {
    echo xml('result',
             array(xml('url',$result),
                   xml('title',$title),
                   xml('message',removeTags($msg))));
  } else if ($format == 'text') {
    echo $result;
    echo "\t";
    echo $title;
  }
} else {
  $msg = $newUrlResult->getResult();
  if ($format == 'xml') {
    echo xml('error',$msg);
  } else if ($format == 'text') {
    echo $msg;
  } 
}

apiFooter($format);

include 'done.php';

?>