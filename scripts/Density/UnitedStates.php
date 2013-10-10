<?php

// Define Location
define("LOCATION", "UnitedStates");
define("CC", "US");

// Import Database Functions and Get the Database
require_once '/home/anojima/scripts/db.function.php';
$db = getDB();
MongoCursor::$timeout = -1;
error_reporting(0);

// *** Set Date Ranges ***
$lastUpdated = file_get_contents("/home/anojima/updated_times/Density/".LOCATION."LastUpdated.txt");
if ($lastUpdated == null){
	$lastUpdated = "October 15, 2012 00:00:00";
	$ftime = fopen("/home/anojima/updated_times/Density/".LOCATION."LastUpdated.txt","w");
	fwrite($ftime, $lastUpdated);
	fclose($ftime);
}
$now = date("F d, Y H:i:s");

// Find the Tweets
$ncTweets = $db->geoTweets->find(
	
	// Basic Tweet Filtering
	array(
		
		// Time and Location
		'$and' => array(

			// Time: Start and End Date Boundaries
			array('cr' => array('$gt' => new MongoDate(strtotime($lastUpdated)))),
			array('cr' => array('$lt' => new MongoDate(strtotime($now)))),     

			// Location
			array('cc' => CC)
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
	$densityjson = array("type" => "DensityCollection", "step" => 0.01, "total" => 0, "data" => array());		
}else{
	$densityjson = json_decode($decodeDensity,true);
}
fclose($dd);

// Collect Tweet Information
$ncTweets->immortal(true);
foreach($ncTweets as $r)
{
	
	// Create Time Variables
	$twDateTime = date("Y-m-d H:i:s", $r['cr']->sec); // DateTime-Stamp
	$twDay = date("Y-m-d", $r['cr']->sec); // Day

	// *** Make a Log for Progress Check ***
	$fl = fopen("/home/anojima/log/Density/".LOCATION."Log.txt", "w");
	fwrite($fl, LOCATION."\n".$twDateTime);
	fclose($fl);

	// Coordinates (Prioritize Tweet Over Profile)
	$tln = $r['tln'];
	$tlt = $r['tlt'];
	$pln = $r['pln'];
	$plt = $r['plt'];
	if (($tlt == null) or ($tln == null)){
		if (($plt == null) or ($pln == null)){
			continue;
		}else{
			$lt = $plt;
			$ln = $pln;
			$tp = "Profile";
		}
	}else{
		$lt = $tlt;
		$ln = $tln;
		$tp = "Tweet";
	}

	$densityjson["total"] += 1;
	$densityjson["data"][$twDay]["daily"] += 1;
	$long = floor($ln*100.0)/100.0;
	$lat = floor($lt*100.0)/100.0;
	$areakey = implode(",",array($lat,$long));
	$densityjson["data"][$twDay][$areakey] += 1;
	
}

// *** Encode Density Data and Output to Density File ***
$densityresult = json_encode($densityjson, JSON_NUMERIC_CHECK); // JSON encoded data
$fd = fopen("/home/anojima/raw_data/Density/".LOCATION."DensityDataRaw.js","w");
fwrite($fd, $densityresult);
fclose($fd);

?>