<?php

// results page size
define('DEFAULT_QUERY_LIMIT', 20);
define('MAX_POST_DISPLAY_LENGTH', 200);
// mongo. db name (twitter) is hardcoded in db.function.php for now
//define('DB_CONNECT_STRING', 'mongodb://mongo-clusterA1:27020');
//define('DB_CONNECT_STRING', 'mongodb://mongo-clusterA1:27017');
//define('DB_CONNECT_STRING', 'mongodb://mongo-clusterAA1:27017,mongo-clusterAA2:27017');
define('DB_CONNECT_STRING', 'mongodb://mongo-clusterAA1:27017');

// lookuptree
define('ID', ';;');
define('NUM_TABLES', 1);
define('DEFAULT_SUDDEN_DEATH_WINDOW', 20);
//define('REGISTERED_TM_CHAR', 174);
//define('TM_CHAR', 0x2122);
//define('STAR_CHAR', 9733);

// notes: 194 is just Â
$SPECIAL_CHARS = array_flip(array(174, 0x2122, 9733, 8226, 9830, 8594, 8221, 8220, 8212, 8226, 10004, 9786, 58371, 12304, 12305, 9671, 36, 128526, 128076, 128138, 8230, 194, 12289, 57607, 57607, 128557, 128552, 128516, 128081, 8211, 128566, 128530, 128553, 9654, 9660, 9654, 9650, 128534, 128591, 128111, 128548, 128564, 128532, 128567, 127815, 128581, 128299, 8217, 128527));

$TAGS = array(0 => array('icon' => 'img/nt.png', 'title' => 'No tag', 'checked_by_default' => false),
              1 => array('icon' => 'img/jk.png', 'title' => 'Junk/NA', 'checked_by_default' => false),
              2 => array('icon' => 'img/ae.png', 'title' => 'Adverse Event', 'checked_by_default' => true),
              3 => array('icon' => 'img/ns.png', 'title' => 'Possible AE', 'checked_by_default' => true),
              4 => array('icon' => 'img/cf.png', 'title' => 'User Confused', 'visible' => false));

$PRODUCT_TYPES = array('drug', 'device', 'vaccine'); // coming soon: biologic (non-vaccine)

if(isset($_SESSION['collection'])) {
    $COLLECTION = $_SESSION['collection'];
} else {
    $COLLECTION = 'fdaTweets';
}

//$GOOGLE_NEWS_BASE_URL = "http://news.google.com/news?hl=en&ned=us&ie=UTF-8&scoring=n&output=rss&q=";
$GOOGLE_NEWS_BASE_URL = "http://news.google.com/news?hl=en&ned=us&ie=UTF-8&output=rss&q=";
//$GOOGLE_NEWS_BASE_URL = "https://medwatcher.org/clark/svc/passthru.php?url=" . urlencode("http://news.google.com/news?hl=en&ned=us&ie=UTF-8&output=rss&q=");

// Bayes params
define('STRENGTH', 0.9);
define('ASSUMED_PROB', 0.2); // start w .5 but prob need to go lower
define('INCL_RAD', 0.06);       // include only tokens whose scores are outside this radius (wrt to CUTOFF)

define('KYOTO_TYCOON_HOST', '10.245.30.230');
define('KYOTO_TYCOON_PORT', 1978);

?>
