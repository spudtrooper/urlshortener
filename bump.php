<?php
include 'include.php';

textHeader();

$lat         = param('lat');
$lng         = param('lng');
$deviceId    = param('id');
$email       = param('email');

$deviceId    = translateDeviceId($deviceId,$email,$lat,$lng);

// Send this to connect with first available
$firstAvailable = isset($_REQUEST['_fa']);
note('First available: ' . boolString($firstAvailable));

define('SLEEP_TIME',1);
define('NUM_TIMES_TO_WAIT_FOR_THE_BALL',7);
$numTimesToWaitForTheBallToBePickedUp = NUM_TIMES_TO_WAIT_FOR_THE_BALL + 2;

$now = timestamp();

insertBump($deviceId,$lat,$lng,$now,'bumps');
insertBump($deviceId,$lat,$lng,$now,'pending_bumps');
$lookingForBall = FALSE;
$foundDeviceId = NULL;
$done = FALSE;
for ($i=0; $i<$numTimesToWaitForTheBallToBePickedUp && !$done; $i++) {
  note('Looking for a pick up #' . $i);
  $sql = 'SELECT * from `pending_bumps` WHERE `date` >= ' 
    .    qw($now-$numTimesToWaitForTheBallToBePickedUp)  
    .    ' AND `device_id` != ' . qw($deviceId);
  $query = sql_query($sql);
  while ($row = mysql_fetch_array($query)) {
    $thatDeviceId = $row['device_id'];
    $thatLat = $row['lat'];
    $thatLng = $row['lng'];
    $diff = distanceEquiv($lat,$lng,$thatLat,$thatLng);
    note(' - diff=' . $diff . ' @ ' . $thatDeviceId);
    if ($firstAvailable || $diff <= MAX_DIFF) {
      $foundDeviceId = $thatDeviceId;
      $done = TRUE;
      break;
    }
  }
  sleep(SLEEP_TIME);
}
note('Found user = ' . boolString($foundDeviceId));
if ($foundDeviceId) {
  $foundEmail = getEmailFromDevice($foundDeviceId);
  echo $foundDeviceId;
  echo "\t";
  echo $foundEmail;
} else {
  echo -1;
}

include 'done.php';
?>