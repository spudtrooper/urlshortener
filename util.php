<?php

define(OK,0);
define(ERR_INVALID_URL,1);
define(ERR_URL_ALREADY_USED,2);
define(ERR_TOO_MANY_TRIES,3);
define(ERR_INVALID_CATEGORY,4);
define(ERR_MALICIOUS_URL,5);

// This is set as a side effect of doing lookups on URLs
$lastFoundTitle = NULL;
$lastFoundId = NULL;

/**
 * A tagged result to use as the return value of functions.
 */
class Result {

  function Result($code,$result,$message=NULL) {
    $this->code = $code;
    $this->result = $result;
    $this->message = $message ? $message : $result;
  }

  function getCode() {
    return $this->code;
  }

  function getResult() {
    return $this->result;
  }

  function getMessage() {
    return $this->message;
  }

  function isError() {
    return $this->code != OK;
  }

  function isOK() {
    return $this->code == OK;
  }
}


/**
 * Main entry point for turning a long url into a urlumized one.
 *
 * Returns a <code>Result</code> as to whether the longUrl was added.
 * Side effect of setting $title to the last found title.
 */
function addNewUrl($longUrl,$category='',$customCategory='',$type='') {

  global $lastFoundTitle;
  global $lastFoundId;

  // Make sure thisn't a malicious URL
  if (preg_match('/^\w+\:/',$longUrl) &&
      !preg_match('/^http/',$longUrl) ) {
    return new Result(ERR_MALICIOUS_URL,'No sneaky, sneaky');
  }

  // Make sure this is a valid URL
  $longUrl = canonicalUrl($longUrl);
	
  // Check to see if this url is already in the databse
  $sql = 'SELECT * from `urls` WHERE `long_url` = ' . qw($longUrl);
  $query = sql_query($sql);
  if ($row = mysql_fetch_array($query)) {
    //
    // Try to set the last found title we had
    //
    $lastFoundTitle = $row['title'];
    $lastFoundId = $row['id'];
    return new Result(OK,$row['url'],'Already there for ' . b($longUrl));
  }

  // Custom category takes precendence
  if ($customCategory || $category == 'other') {
    $category = $customCategory;
  }

  $newUrlResult = createNewUrl($longUrl,$category,$type);
  if ($newUrlResult->isOK()) {
    //
    // Try to find the title now
    //
    if (!$lastFoundTitle) {
      $lastFoundTitle = findTitle($longUrl);
    }
    //
    // Create the new link
    //
    $sql = 'INSERT into `urls` '
      .    '(long_url,timestamp_added,clicks,category,url,title,ip,referrer,user_agent) '
      .    'VALUES (';
    $sql .= qw($longUrl)  . ',';
    $sql .= qw(timestamp())  . ',';
    $sql .= qw('0')  . ',';
    $sql .= qw($category) . ',';
    $sql .= qw($newUrlResult->getResult()) . ',';
    $sql .= qw($lastFoundTitle) . ',';
    $sql .= qw($_SERVER['REMOTE_ADDR']) . ',';
    $sql .= qw($_SERVER['HTTP_REFERER']) . ',';
    $sql .= qw($_SERVER['HTTP_USER_AGENT']);
    $sql .= ')';
    sql_query($sql);
  }

  return $newUrlResult;
}

/**
 * Returns a <code>Result</code> as to whether a short url could be
 * created.  If this result is ok, the <code>getResult()</code> value
 * of the returned result is a array of
 *  - url
 *  - title
 */
function createNewUrl($longUrl,$category,$type) {
  global $phpSelf;
  $urlStart = '';

  if ($category && $category != '--' && $category != '') {
    // 
    // Check for an invalid category
    //
    if (!isValidCategory($category)) {
      return new Result(ERR_INVALID_CATEGORY,'Invalid category \'' . $category . '\'');
    }
    $urlStart .= $category . '/';
  }
  for ($i=0;$i<MAX_NUM_URL_TRIES; $i++) {
    if (!$type || $type == 'short') {
      $newUrl = $urlStart . generateRandomString();
    } else if ($type == 'long') {
      $newUrl = $urlStart . generateLongRandomString();
    } else {
      $newUrl = $urlStart . findDescriptiveName($longUrl,$i);
    }
    //
    // Check whether we have this url is there already?
    //
    $sql = 'SELECT * from `urls` WHERE `url` = ' . qw($newUrl);
    $query = sql_query($sql);
    if (!mysql_fetch_array($query)) {
      return new Result(OK,$newUrl,'Created for ' . b($longUrl));
    }
  }
  //
  // If we've exceeded the limit of tries return an error
  //
  return new Result(ERR_TOO_MANY_TRIES,'Too many tries');
}

function isValidCategory($category) {
  $res = preg_match('/^[\+_a-zA-Z0-9@]+$/',$category);
  return $res;
}

/**
 * Side effect of setting lastFoundTitle to the last title that was
 * found in this page.
 */
function findTitle($url,$id=NULL) {
  global $lastFoundTitle;
  $html = @file_get_contents(canonicalUrl($url));
  if (preg_match('/<title>([^<]+)<\/title>/mi',$html,$out)) {
    $lastFoundTitle = $out[1];
  } else if (preg_match('/<h1>([^<]+)<\/h1>/mi',$html,$out)) {
    $lastFoundTitle = $out[1];
  }
  if ($lastFoundTitle) {
    $lastFoundTitle = trimWhiteSpace($lastFoundTitle);
  }
  //
  // If we've passed in an id we want to add this title to the databse
  //
  if ($id && $lastFoundTitle) {
    $sql = 'UPDATE `urls` SET `title` = ' . qw($lastFoundTitle) . ' WHERE `id` = ' . qw($id);
    sql_query($sql);
  }
  return $lastFoundTitle;
}

function canonicalUrl($url) {
  if (!preg_match('/^https?:\/\//',$url)) {
    $url = 'http://' . $url;
  }
  return $url;
}

/**
 * Returns an array of
 * - url
 * - title
 */
function findDescriptiveName($url,$iteration) {
  $result = NULL;
  $title = findTitle($url);
  if ($title) {
    $result = transformDescriptiveName($title);
    if ($iteration > 0) {
      $result .= '-' . $iteration;
    }
  }
  return $result;
}

function transformDescriptiveName($s) {
  $s = preg_replace('/\s+/','-',$s);
  $s = preg_replace('/[^a-zA-Z0\-]/','',$s);
  $s = preg_replace('/\-+/','-',$s);
  $s = preg_replace('/\-$/','',$s);
  $s = preg_replace('/^\-/','',$s);
  return $s;
}

function generateRandomString() {
  return randomString(SHORT_URL_LENGTH);
}

function generateLongRandomString() {
  return randomString(rand(LONG_URL_LENGTH,LONG_URL_LENGTH+100));
}

function randomString($length) {
  $RANDOM_CHARS = array('0','1','2','3','4','5','6','7','8','9',
			'q','w','e','r','t','y','u','i','o','p','a','s','d',
			'f','g','h','j','k','l','z','x','c','v','b','n','m',
			'Q','W','E','R','T','Y','U','I','O','P','A','S','D',
			'F','G','H','J','K','L','Z','X','C','V','B','N','M');
  $s = '';
  for ($i=0; $i<$length; $i++) {
    $s .= $RANDOM_CHARS[rand(0,count($RANDOM_CHARS))];
  }
  return $s;
}

function qw($s) {
  global $db;
  //$db->quote($s);
  $s = preg_replace("/'/","\\'",$s);
  $s = preg_replace("/\"/","\\\"",$s);
  return "'" . $s . "'";
}

function sql_query($sql) {
  global $db;
  $result = mysql_db_query(DB_NAME,$sql,$db);
  if (!$result) {
    echo("ERROR: " . mysql_error() . "\n$sql\n");
  }
  return $result;
}

function sql_row($sql) {
  $query = sql_query($sql);
  $row = mysql_fetch_array($query);		
  return $row;
}

function timestamp() {
  list($usec, $sec) = explode(" ", microtime());
  return $sec;
}

$DATE_FORMAT = "Y-m-d H:i:s";
function datetime() {
  global $DATE_FORMAT;
  return date($DATE_FORMAT);
}

$DATE_FORMAT_DATE = "Y-m-d";
function justdate() {
  global $DATE_FORMAT_DATE;
  return date($DATE_FORMAT_DATE);
}

$DATE_FORMAT_TIME = "H:i:s";
function justtime() {
  global $DATE_FORMAT_TIME;
  return date($DATE_FORMAT_TIME);
}

function mainUrl() {
  global $phpSelf;
  return 'http://' . host() . $phpSelf;
}

function fullUrl($urlStart='') {
  if (preg_match("/http\:\/\//",$urlStart)) {
    return $urlStart;
  }
  return 'http://' . host() . '/' . $urlStart;
}

function host() {
  return $_SERVER['HTTP_HOST'];
}

function n($s) {
  echo $s;
  echo "\n";
}

function b($s) {
  return '<b>' . $s . '</b>';
}

function urlLink($url,$text=0) {
  if (!$text) $text = $url;
  $s  = '<a href="' . $url . '" class="url">';
  $s .= $text;
  $s .= '</a>';
  return $s;
}

function sanitize($input) {
  $input = preg_replace('/\'/','',$input);
  $input = preg_replace('/\"/','',$input);
  $input = preg_replace('/`/','',$input);
  return $input;
}

function post($key) {
  return sanitize($_POST[$key]);
}

function get($key) {
  return sanitize($_GET[$key]);
}

function request($key) {
  return sanitize($_REQUEST[$key]);
}

function requestUrl() {
  $url = $_REQUEST['url'];
  if (!$url) {
    $url = $_REQUEST['u'];
  }
  return $url;
}

function apiHeader($format) {
  if ($format == 'xml') {
    $contentType = 'text/xml';
  } else if ($format == 'text') {
    $contentType = 'text/plain';
  }
  header('Content-type: ' . $contentType);
  if ($format == 'xml') {
    echo '<?xml version="1.0"?>';
  } else if ($format == 'text') {
  }
}

function apiFooter($format) {
  if ($format == 'xml') {

  } else if ($format == 'text') {
    
  } 
}

function xml($tag,$arr=NULL) {
  $s = '<' . $tag . '>';
  if ($arr) {
    if (is_array($arr)) {
      foreach ($arr as $a) $s .= $a;
    } else {
      $s .= $arr;
    }
  }
  $s .= '</' . $tag . '>';
  return $s;
}

function removeTags($s) {
  return preg_replace('/<[^>]+>/','',$s);
}

function findClicksAfter($urlId,$time) {
  $sqlStart = 'SELECT COUNT(*) from `clicks` WHERE `url_id` = ' . qw($urlId);
  $sql = $sqlStart . ' AND `timestamp` > ' . $time;
  $row = sql_row($sql);
  $clicks = $row ? $row['COUNT(*)'] : '0';
  return $clicks;
}

function findLastClick($urlId) {
  $sql  = 'SELECT * from `clicks` WHERE `url_id` = ' . qw($urlId);
  $sql .= ' ORDER BY `timestamp` DESC LIMIT 1';
  $row = sql_row($sql);
  return $row ? (timestamp()-$row['timestamp']) : 0;
}

function findClickStats($urlId) {
  $now = timestamp();
  $lastClick = findLastClick($urlId);
  $clicksThisMinute = findClicksAfter($urlId,$now-60);
  $clicksThis10Minutes = findClicksAfter($urlId,$now-(10*60));
  $clicksThisHour = findClicksAfter($urlId,$now-(60*60));
  $clicksThisDay = findClicksAfter($urlId,$now-(60*60*24));
  $clicksThisWeek = findClicksAfter($urlId,$now-(60*60*24*7));
  $clicksThisMonth = findClicksAfter($urlId,$now-(60*60*24*7*30));
  $clicksThisYear = findClicksAfter($urlId,$now-(60*60*24*7*30*365));
  return array($lastClick,
               $clicksThisMinute,
               $clicksThis10Minutes,
               $clicksThisHour,
               $clicksThisDay,
               $clicksThisWeek,
               $clicksThisMonth,
               $clicksThisYear);
}

function pluralize($num,$name) {
  $s  = $num . ' ' . $name;
  if ($num != 1) $s .= 's';
  return $s;
}

function formatSeconds($secs) {
  if ($secs < 60) {
    return pluralize($secs,'second');
  }
  $minutes = $secs/60;
  $secsRem = $secs%60;
  if ($minutes < 60) {
    return 'about ' . pluralize(floor($minutes),'minute');
  }
  $hours = $minutes/60;
  $minsRem = $minutes%60;
  if ($hours < 24) {
    return 'about ' . pluralize(floor($hours),'hour');
  }
  $days = $hours/24;
  return 'about ' . pluralize(floor($days),'day');
}

function ahref($link,$name=NULL) {
  if (!$name) $name = $link;
  $s = '<a href="' . $link . '">' . $name . '</a>';
  return $s;
}

function statsLink($link) {
  $newLink = $link;
  if (!preg_match('/\+$/',$newLink)) {
    $newLink .= '+';
  }
  return $newLink;
}

function trimWhiteSpace($s,$newlines=TRUE) {
  if ($s) {
    if ($newlines) {
      $s = preg_replace('/\n/','',$s);
      $s = preg_replace('/\r/','',$s);
    }
    $s = preg_replace('/^\s+/','',$s);
    $s = preg_replace('/\s+$/','',$s);
  }
  return $s;
}

function findLastTitle($longUrl) {
  global $lastFoundTitle;
  global $lastFoundId;
  $title = $lastFoundTitle;
  $title = trimWhiteSpace($title);
  if (!$title) {
    $title = findTitle($longUrl,$lastFoundId);
  }
  return $title;
}

?>