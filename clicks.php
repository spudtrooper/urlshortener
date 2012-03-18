<?php include 'head.php'; ?>

<div id="long_url_label">Enter your link here for link stats (hint: <em>you can add a + to the end of a link, too</em>)</div>
   <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" >
   <input type="text" name="url" id="url" class="main_input round" size="57" />	
   <div id="inputs">
   <div id="choices">
   <input type="submit" name="getlink" id="getlink" value="Get stats" class="round" />
   </div>
   </div>
   </form>

   <?php

   $msgClass = '';
$msg = '';
$result   = '';
$success = FALSE;
if (isset($_REQUEST['url'])) {
  $realUrl = request('url');
  //
  // Remove the possible protocol from the url
  //
  $url = preg_replace('/http:\/\/[^\/]+\//','',$realUrl);
  $sql = 'SELECT * from `urls` WHERE `url` = ' . qw($url);
  $row = sql_row($sql);
  if (!$row) {
    //
    // Treat this as a long url
    //
    $sql = 'SELECT * from `urls` WHERE `long_url` = ' . qw(canonicalUrl($realUrl));
    $row = sql_row($sql);
    if ($row) {
      $urlum = fullUrl($row['url']);
    }
  }
  if ($row) {
    $numClicks = $row['clicks'];
    $msg = '<b>' . $numClicks . '</b> click';
    if ($numClicks != 1) $msg .= 's';
    $msg .= ' found for <a href="' . fullUrl($realUrl) . '" class="url">' . fullUrl($realUrl) .'</a>';
    $msgClass = 'success';
    //
    // Now find the stats
    //
    list($lastClick,
         $clicksThisMinute,
         $clicksThis10Minutes,
         $clicksThisHour,
         $clicksThisDay,
         $clicksThisWeek,
         $clicksThisMonth,
         $clicksThisYear) = findClickStats($row['id']);
    function row($one,$two) {
      $s = '';
      $s .= '<tr>';
      $s .= '<td>';
      $s .= $one;
      $s .= '</td>';
      $s .= '<td>';
      $s .= '<b>' . $two . '</b>';
      $s .= '</td>';
      $s .= '</tr>';
      return $s;
    }
    function statsRow($period,$value) {
      $str .= 'Clicks this past ';
      return row($str . $period,$value);
    }
    $msgStats  = '<table>';
    $msgStats .= row('Last click', formatSeconds($lastClick) . ' ago');
    if ($clicksThisMinute     != 0) {
      $msgStats .= statsRow('minute',$clicksThisMinute);
    }                     
    if ($clicksThis10Minutes != 0 && $clicksThis10Minutes != $clicksThisMinute) {
      $msgStats .= statsRow('10 minutes',$clicksThis10Minutes);
    }     
    if ($clicksThisHour != 0 && $clicksThisHour != $clicksThis10Minutes) {
      $msgStats .= statsRow('hour',$clicksThisHour);
    }  
    if ($clicksThisDay != 0 && $clicksThisDay != $clicksThisHour ) {
      $msgStats .= statsRow('day',$clicksThisDay);
    }      
    if ($clicksThisWeek != 0 && $clicksThisWeek != $clicksThisDay  ) {
      $msgStats .= statsRow('week',$clicksThisWeek);
    }      
    if ($clicksThisMonth != 0 && $clicksThisMonth != $clicksThisWeek ) {
      $msgStats .= statsRow('month',$clicksThisMonth);
    }      
    if ($clicksThisYear != 0 && $clicksThisYear != $clicksThisMonth) {
      $msgStats .= statsRow('year',$clicksThisYear);
    }      
    $msgStats .= '</table>';
    if ($urlum) {
      $msg2 = 'This link has been urlumized: ' . urlLink($urlum);
    }
  } else {
    $msg = 'No urlumized link found for <a href="' . $realUrl . '" class="url">' . $realUrl . '</a>';
    $msgClass = 'error';
    $msg2 = 'Get one <a href="/?u=' . $realUrl . '">here</a>';
  }
}
?>

<div class="url-title"><?php echo $title; ?></div>
<div id="message" class="<?php echo $msgClass; ?>"><?php echo $msg; ?></div>
<div ><?php echo $msg2; ?></div>
<div id="results" class="url">
  <a target="_" href="<?php echo $result; ?>"><?php echo $result; ?></a>
  </div>
  <div ><?php echo $msgStats; ?></div>

<?php include 'foot.php'; ?>
