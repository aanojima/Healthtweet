<?php

// For Compatability, Everything With *** Changed by Defined Location and Disease
define("DISEASE", "measles");
define("LOCATION", "NYC");

// Import Database Functions and Get the Database
require_once "/home/anojima/scripts/db.function.php";
$db = getDB();
MongoCursor::$timeout = -1;
error_reporting(0);

// *** Set Date Ranges ***
$f = fopen("/home/anojima/updated_times/".DISEASE."/".LOCATION."LastUpdated.txt","r");
$lastUpdated = fgets($f);
fclose($f);
$now = date("F d, Y H:i:s");

// *** Set Coordinate Boundaries ***
require_once "/home/anojima/coordinates/".LOCATION."Coordinates.php";

// Find the Tweets
$ncTweets = $db->geoTweets->find(
	
	// Basic Tweet Filtering
	array(
		
		// Time and Location
		'$and' => array(

			// Time: Start and End Date Boundaries
			array('cr' => array('$gt' => new MongoDate(strtotime($lastUpdated)))),
			array('cr' => array('$lt' => new MongoDate(strtotime($now)))),

			// Locations: Latitude and Longitude Coordinate Boundaries
			array(
				// Either Location of Tweet or Profile
				'$or'=> array(
					array(
						// TWEET LOCATION COORDINATES
						'$and'=> array(
							array('tlt' => array('$gt' => SOUTH)),
							array('tlt' => array('$lt' => NORTH)),
							array('tln' => array('$gt' => WEST)),
							array('tln' => array('$lt' => EAST))
						)
					),
					array(
						// PROFILE LOCATION COORDINATES
						'$and'=> array(
							array('plt' => array('$gt' => SOUTH)),
							array('plt' => array('$lt' => NORTH)),
							array('pln' => array('$gt' => WEST)),
							array('pln' => array('$lt' => EAST))
						)
					)
				)
			)
		)
	) 
);

// *** Set New "Last Updated" Time ***
$lastUpdated = $now;
$ff = fopen("/home/anojima/updated_times/".DISEASE."/".LOCATION."LastUpdated.txt","w");
fwrite($ff,$lastUpdated);
fclose($ff);

// Load or Reload Map and Time Data as JSON

// *** Map Data ***

$dm = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."MapDataRaw.js","c");
$decodeMap = file_get_contents("/home/anojima/raw_data/".DISEASE."/".LOCATION."MapDataRaw.js");
if ($decodeMap == null){
	$geojson = array(
		'type' => 'FeatureCollection',
		'features' => array()
	);
}else{
	$geojson = json_decode($decodeMap,true);
}
fclose($dm);

// *** Time Data ***
$dt = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."TimeDataRaw.js","c");
$decodeTime = file_get_contents("/home/anojima/raw_data/".DISEASE."/".LOCATION."TimeDataRaw.js");
if ($decodeTime == null){
	$timejson = array(array('Date','Tweets'));
}else{	
	$timejson = json_decode($decodeTime);
}
fclose($dt);

// Collect Tweet Information
$ncTweets->immortal(true);
$step = 0.1;

foreach($ncTweets as $r)
{	
	// Exclusion Words
	if(preg_match("/stomach flu/i", $r['t']) || preg_match("/(avian|bird) flu/i", $r['t'])){
		continue;
	}

	//Flu Keywords - Filter
	elseif(
		preg_match("/\bmeasles\b/i", $r['t'])||
		preg_match("/MMR vaccine/i", $r['t']) ||
		preg_match("/measels/i", $r['t']) ||
		preg_match("/morbilli/i", $r['t']) ||
		preg_match("/rubeola/i", $r['t']) ||
		preg_match("/Koplik spots/i", $r['t'])
	)		
	
	{
		// Create Properties Variables

		// Time
		$twDate = date("Y-m-d", $r['cr']->sec);
		$twTime = date("H:i:s", $r['cr']->sec);
		$timeSeries[$twDate]++; // Increase Daily Count
		$twDateTime = date("Y-m-d H:i:s", $r['cr']->sec); // DateTime-Stamp

		//Space
		$pln = $r['pln'];
		$plt = $r['plt'];
		$long = floor($pln*10.0)/10.0;
		$lat = floor($plt*10.0)/10.0;
		$areakey = implode(",",array($lat,$long));


		// *** Make a Log for Progress Check ***
		$fl = fopen("/home/anojima/log/".DISEASE."/".LOCATION."Log.txt", "w");
		fwrite($fl, DISEASE." @ ".LOCATION."\n".$twDateTime);
		fclose($f1);

		// Create the Map Data
		$feature = array(
			'type' => 'Feature',
			'geometry' => array(
				'type' => 'Point',
				'coordinates' => array(
					$r['pln'],
					$r['plt']
				)
			),
			'properties' => array(
				'dateMS' => 1000*strtotime($twDateTime),
				'areakey' => areakey
			)
		);
		// Add feature arrays to feature cllection array
		array_push($geojson["features"], $feature);
	}
}

// *** Encode Map Data and Output to Map File ***
$mapresult = json_encode($geojson, JSON_NUMERIC_CHECK); // JSON encoded data
$fm = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."MapDataRaw.js","w");
fwrite($fm, $mapresult);
fclose($fm);

// Create the Time Data
foreach(array_keys($timeSeries) as $date)
{
	// Find a day and add the count
	$noDate = true;
	for($i = 0; $i < count($timejson); $i++){
		if($timejson[$i][0] == $date){
			$timejson[$i][1] += $timeSeries[$date];
			$noDate = false;
		}
	}
	//If day doesn't exist in the data, create a new one with its daily count
	if ($noDate){
		array_push($timejson, array($date, $timeSeries[$date]));
	}
}

// *** Encode Time Data and Output to Time File ***

$timeresult = json_encode($timejson, JSON_NUMERIC_CHECK); 
$ft = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."TimeDataRaw.js","w");
fwrite($ft, $timeresult);
fclose($ft);

// Create Readable Data for HTML-Javascript

$readMapDataFile = fopen("/home/anojima/public_html/healthtweet/data/".DISEASE."/".LOCATION."MapData.js","w");
fwrite($readMapDataFile,"var results = \n".$mapresult.";\n");
fclose($readMapDataFile);

$readTimeDataFile = fopen("/home/anojima/public_html/healthtweet/data/".DISEASE."/".LOCATION."TimeData.js","w");
fwrite($readTimeDataFile,"var timeData = \n".$timeresult.";\n");
fclose($readTimeDataFile);

?>