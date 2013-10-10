<?php

// For Compatability, Everything With *** Changed by Defined Location
define("DISEASE", "Flu");
define("LOCATION", "World");


// Import Database Functions and Get the Database
require_once "/home/anojima/scripts/db.function.php";
$db = getDB();
MongoCursor::$timeout = -1;
error_reporting(8);

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
$timejson = array(array('Date','Tweets'));

// Density Data
$dd = fopen("/home/anojima/raw_data/Density/".LOCATION."DensityDataRaw.js","c");
$decodeDensity = file_get_contents("/home/anojima/raw_data/Density/".LOCATION."DensityDataRaw.js");
$densityjson = json_decode($decodeDensity);
fclose($dd);

// Collect Tweet Information
$ncTweets->immortal(true);

foreach($ncTweets as $r)
{	
	// Exclusion Words
	if(preg_match("/stomach flu/i", $r['t']) || preg_match("/(avian|bird) flu/i", $r['t'])){
		continue;
	}

	// Flu Keywords - Filter
	elseif(
		preg_match("/\bflu\b/i", $r['t'])||
		preg_match("/\bgripe\b/i", $r['t'])||
		preg_match ("/influenza/i", $r['t'])||
		preg_match("/high fever/i", $r['t'])
	
		// preg_match("/cough/i", $r['t'])||
		// preg_match("/(head|\b)ache\b/i", $r['t'])||
		// preg_match("/feel sick/i", $r['t'])||
		// preg_match("/feeling sick/i", $r['t'])||
		// preg_match("/see a doctor/i", $r['t'])||
		// preg_match("/sore throat/i", $r['t'])||
		// preg_match("/spread(ing) germs/i", $r['t'])
	)
	{
		// Create Properties Variables

		// Times
		$twDay = date("Y-m-d", $r['cr']->sec);
		$twTime = date("H:i:s", $r['cr']->sec);
		$twDateTime = date("Y-m-d H:i:s", $r['cr']->sec);

		// Coordinates (Prioritize Tweet Over Profile)
		$tln = $r['tln'];
		$tlt = $r['tlt'];
		$pln = $r['pln'];
		$plt = $r['plt'];
		if (($tlt == null) or ($tln == null)){
			$lt = $plt;
			$ln = $pln;
			$tp = "Profile";
		}else{
			$lt = $tlt;
			$ln = $tln;
			$tp = "Tweet";
		}

		// Grid Coordinates
		$long = floor($ln*100.0)/100.0;
		$lat = floor($lt*100.0)/100.0;
		$areakey = implode(",",array($lat,$long));

		// Weight Properties
		$boxCount = $densityjson["data"][$twDay][$areakey];
		$dailyCount = $densityjson["data"][$twDay]["daily"];
		$weight = $dailyCount/$boxCount;

		// Create the Map Data Based on Tweet
		$feature = array(
			'type' => 'Feature',
			'geometry' => array(
				'type' => 'Point',
				'coordinates' => array(
					$ln,
					$lt
				)
			),
			'properties' => array(
				'userID' => strval($r['uid']),
				'origin' => $tp,
				'areakey' => $areakey,
				'date' => 1000*strtotime($twDateTime),
				'dateString' => array("day" => $twDay, "time" => $twTime),
				'text' => $r['t'],
				'weight' => null
			)
		);

		// Add/Edit feature array to feature collection
		$id = strval($r['_id']);
		$geojson["features"][$id] = $feature;

		// Make a Log for Progress Check
		$fl = fopen("/home/anojima/log/".DISEASE."/".LOCATION."Log.txt", "w");
		fwrite($fl, DISEASE." @ ".LOCATION."\n".$twDateTime);
		fclose($fl);

	}
}

// Encode Map Data and Output to Map File
$mapresult = json_encode($geojson); // JSON encoded data
$fm = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."MapDataRaw.js","w");
fwrite($fm, $mapresult);
fclose($fm);

// Create the Time Data Based on Map Data
$timeSeries = array();
foreach(array_keys($geojson["features"]) as $key){
	$day = $geojson["features"][$key]["properties"]["dateString"]["day"];
	$timeSeries[$day] += 1;
};
foreach(array_keys($timeSeries) as $day){
	array_push($timejson, array($day, $timeSeries[$day]));
};

// Encode Time Data and Output to Time File
$timeresult = json_encode($timejson, JSON_NUMERIC_CHECK); 
$ft = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."TimeDataRaw.js","w");
fwrite($ft, $timeresult);
fclose($ft);

// Create Readable Map Data for HTML-Javascript
$readMapDataFile = fopen("/home/anojima/public_html/healthtweet/data/".DISEASE."/".LOCATION."MapData.js","w");
fwrite($readMapDataFile,"var results = \n".$mapresult.";\n");
fclose($readMapDataFile);

// Create Readable Time Data for HTML-Javascript
$readTimeDataFile = fopen("/home/anojima/public_html/healthtweet/data/".DISEASE."/".LOCATION."TimeData.js","w");
fwrite($readTimeDataFile,"var timeData = ".$timeresult.";");
fclose($readTimeDataFile);

?>