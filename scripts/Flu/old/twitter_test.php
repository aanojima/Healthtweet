<?php

// For Compatability, Everything With *** Changed by Defined Location
define("DISEASE", "Test");
define("LOCATION", "NYC");


// Import Database Functions and Get the Database
require_once "/home/anojima/scripts/db.function.php";
$db = getDB();
MongoCursor::$timeout = -1;
error_reporting(1024);

// *** Set Date Ranges ***


// *** Set Coordinate Boundaries ***
require_once "/home/anojima/coordinates/".LOCATION."Coordinates.php";

// Find the Tweets
$ncTweets = $db->geoTweets->find(
	
	// Basic Tweet Filtering
	array(
		
		// Time and Location
		'$and' => array(

			// Time: Start and End Date Boundaries
			array('cr' => array('$gt' => new MongoDate(strtotime("June 10, 2013 00:29:00")))),
			array('cr' => array('$lt' => new MongoDate(strtotime("June 20, 2013 00:30:00")))),     

			// Locations: Latitude and Longitude Coordinate Boundaries
			array(
				// Either Location of Tweet or Profile
				'$or'=> array(
					array(
						// TWEET LOCATION COORDINATES
						'$and'=> array(
							array('tlt' => array('$gt' => south)),
							array('tlt' => array('$lt' => north)),
							array('tln' => array('$gt' => west)),
							array('tln' => array('$lt' => east))
						)
					),
					array(
						// PROFILE LOCATION COORDINATES
						'$and'=> array(
							array('plt' => array('$gt' => south)),
							array('plt' => array('$lt' => north)),
							array('pln' => array('$gt' => west)),
							array('pln' => array('$lt' => east))
						)
					)
				)
			)
		)
	) 
);


// Collect Tweet Information
$ncTweets->immortal(true);
echo array_keys($ncTweets);

foreach ($ncTweets as $r){
	echo 'f: '.$r['f'].'; p: '.$r['p'];
	// foreach (array_keys($r) as $l){
	// 	echo $l;
	// 	echo ": ";
	// 	echo $r[$l];
	// 	echo "; ";
	// };
	echo "\n";
}


// foreach($ncTweets as $r)
// {	

// 		// Time
// 		$twDate = date("Y-m-d", $r['cr']->sec);
// 		$twTime = date("H:i:s", $r['cr']->sec);
// 		$timeSeries[$twDate]++; // Increase Daily Count
// 		$twDateTime = date("Y-m-d H:i:s", $r['cr']->sec); // DateTime-Stamp

// 		//Space
// 		$pln = $r['tln'];
// 		$plt = $r['tlt'];
// 		$long = floor($pln*10.0)/10.0;
// 		$lat = floor($plt*10.0)/10.0;
// 		$areakey = implode(",",array($lat,$long));


// 		// *** Make a Log for Progress Check ***
// 		$fl = fopen("/home/anojima/log/".LOCATION."Log.txt", "w");
// 		fwrite($fl, LOCATION."\n".$twDateTime);
// 		fclose($f1);

// 		// Create the Map Data
// 		$feature = array(
// 			'type' => 'Feature',
// 			'geometry' => array(
// 				'type' => 'Point',
// 				'coordinates' => array(
// 					$r['tln'],
// 					$r['tlt']
// 				)
// 			),
// 			'properties' => array(
// 				'dateMS' => 1000*strtotime($twDateTime),
// 				'areakey' => areakey
// 			)
// 		);
// 		// Add feature arrays to feature cllection array
// 		array_push($geojson["features"], $feature);
// }

// // *** Encode Map Data and Output to Map File ***
// $mapresult = json_encode($geojson, JSON_NUMERIC_CHECK); // JSON encoded data
// $fm = fopen("/home/anojima/raw_data/".LOCATION."MapDataRaw.js","w");
// fwrite($fm, $mapresult);
// fclose($fm);

// // Create the Time Data
// foreach(array_keys($timeSeries) as $date)
// {
// 	// Find a day and add the count
// 	$noDate = true;
// 	for($i = 0; $i < count($timejson); $i++){
// 		if($timejson[$i][0] == $date){
// 			$timejson[$i][1] += $timeSeries[$date];
// 			$noDate = false;
// 		}
// 	}
// 	//If day doesn't exist in the data, create a new one with its daily count
// 	if ($noDate){
// 		array_push($timejson, array($date, $timeSeries[$date]));
// 	}
// }

// // *** Encode Time Data and Output to Time File ***

// $timeresult = json_encode($timejson, JSON_NUMERIC_CHECK); 
// $ft = fopen("/home/anojima/raw_data/".LOCATION."TimeDataRaw.js","w");
// fwrite($ft, $timeresult);
// fclose($ft);

// // Create Readable Data for HTML-Javascript

// $readMapDataFile = fopen("/home/anojima/public_html/data/".LOCATION."MapData.js","w");
// fwrite($readMapDataFile,"var results = \n".$mapresult.";\n");
// fclose($readMapDataFile);

// $readTimeDataFile = fopen("/home/anojima/public_html/data/".LOCATION."TimeData.js","w");
// fwrite($readTimeDataFile,"var timeData = ".$timeresult.";");
// fclose($readTimeDataFile);

// ?>