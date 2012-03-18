<?php

// Database credentials
define(DB_HOST,'localhost');
define(DB_USER,'TODO: database user');
define(DB_PASS,'TODO: database password');
define(DB_NAME,'TODO: database name');

// Misc config
define(SHORT_URL_LENGTH,6);
define(LONG_URL_LENGTH,100);
define(MAX_NUM_URL_TRIES,3);
define(TITLE,'urlu.ms :: gooder links');

$CATEGORIES = array('sports','funny','puppies','spudtrooper','nsfw');
sort($CATEGORIES);

$TYPES = array('short','descriptive','long');

?>