<?php

// For Compatability, Everything With *** Changed by Defined Location
define("LOCATION", "NYC");

// Import Database Functions and Get the Database
require_once '/home/anojima/scripts/db.function.php';
$db = getDB();
MongoCursor::$timeout = -1;
error_reporting(1);

// *** Set Date Ranges ***
$f = fopen("/home/anojima/updated_times/Density/".LOCATION."LastUpdated.txt","r");
$lastUpdated = fgets($f);
fclose($f);
$now = date("F d, Y H:i:s");

// *** Set Coordinate Boundaries ***
require_once 'coordinates/'.LOCATION.'Coordinates.php';

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
$ff = fopen("/home/anojima/updated_times/Density/".LOCATION."LastUpdated.txt","w");
fwrite($ff,$lastUpdated);
fclose($ff);

// Load or Reload Map and Time Data as JSON

// *** Density Data ***
$dd = fopen("/home/anojima/raw_data/Density/".LOCATION."DensityDataRaw.js","c");
$decodeDensity = file_get_contents("/home/anojima/raw_data/Density/".LOCATION."DensityDataRaw.js");
if ($decodeDensity == null){
	$densityjson = array("type" => "coordinates: count", "step" => 0.1, "total" => 0, "data" => array());
	for($j = floor(SOUTH*10.0)/10.0; $j <= NORTH; $j += 0.1){
		for($i = floor(WEST*10.0)/10.0; $i <= EAST; $i += 0.1){
			$key = implode(",",array($i,$j));
			$densityjson["data"][$key] = 0;
		}
	}		
}else{
	$densityjson = json_decode($decodeDensity,true);
}
fclose($dd);

// Collect Tweet Information
$ncTweets->immortal(true);
$step = 0.1;

foreach($ncTweets as $r)
{
	
	// Create Time Variables
	$twDateTime = date("Y-m-d H:i:s", $r['cr']->sec); // DateTime-Stamp

	// *** Make a Log for Progress Check ***
	$fl = fopen("/home/anojima/log/Density/".LOCATION."Log.txt", "w");
	fwrite($fl, LOCATION."\n".$twDateTime);
	fclose($fl);

	// Update the Density Data
	$pln = $r['pln'];
	$plt = $r['plt'];
	$densityjson["total"] += 1;
	// echo $twDateTime.": ".(string)$densityjson["total"]."\n";
	$long = floor($pln*10.0)/10.0;
	$lat = floor($plt*10.0)/10.0;
	$areakey = implode(",",array($lat,$long));
	$densityjson["data"][$areakey] += 1;
	
}

	

// *** Encode Density Data and Output to Density File ***
$densityresult = json_encode($densityjson, JSON_NUMERIC_CHECK); // JSON encoded data
$fd = fopen("/home/anojima/raw_data/Density/".LOCATION."DensityDataRaw.js","w");
fwrite($fd, $densityresult);
fclose($fd);

// Create Readable Data for HTML-Javascript

$readDensityDataFile = fopen("/home/anojima/public_html/data/Density/".LOCATION."DensityData.js","w");
fwrite($readDensityDataFile,"var density = \n".$densityresult.";");
fclose($readDensityDataFile);

?>