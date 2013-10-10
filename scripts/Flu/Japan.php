<?php

// Set Disease
define("DISEASE", "Flu");

// Set Location
define("LOCATION", "Japan");
define("CC", "JP");

// Import Database Functions and Get the Database
require_once "/home/anojima/scripts/db.function.php";
$db = getDB();
MongoCursor::$timeout = -1;
error_reporting(0);

// *** Set Date Ranges ***
$lastUpdated = file_get_contents("/home/anojima/updated_times/".DISEASE."/".LOCATION."LastUpdated.txt");
if ($lastUpdated == null){
	$lastUpdated = "October 15, 2012 00:00:00";
	$ftime = fopen("/home/anojima/updated_times/".DISEASE."/".LOCATION."LastUpdated.txt","w");
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

			// Locations: Country Code
			array('cc' => CC)
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

// Collect Tweet Information
$ncTweets->immortal(true);
$unwantedChars = array("`","~","!","@","#","$","%","^","&","*","(",")","-","_","=","+","[","{","]","}","|",";",":","'",'"',",","<",".",">","/","?"," ",
	"¿","¡","♪","⛵","💕","¢","£","¤","¥","€","¦","§","¨","©","ª","«","»","¬","©","¶");
foreach($ncTweets as $r)
{	
	
	// Setup Search Text
	$text = $r['t'];
	$search = str_replace($unwantedChars, "-", $text);

	// Exclusion Words
	if(preg_match("/stomach flu/i", $search) || preg_match("/(avian|bird) flu/i", $search)){
		continue;
	}

	// Flu Keywords - Filter
	elseif(
		
		// English
		($r['lang'] == "en" &&
		preg_match("/high fever/i", $search)) ||

		// English-Malay
		(($r['lang'] == "en" || $r['lang'] == "ms") &&
		preg_match("/\bflu\b/i", $search)) ||
		
		// English-Malay-Italian-Danish
		(($r['lang'] == "en" || $r['lang'] == "ms" || $r['lang'] == "it" || $r['lang'] == "da") &&
		preg_match ("/influenza/i", $search)) ||

		// Spanish-Portuguese
		(($r['lang'] == "es" || $r['lang'] == "pt") &&
		preg_match("/\bgripe\b/i", $search)) ||

		// French
		($r['lang'] == "fr" &&
		preg_match("/\bgrippe\b/i", $search)) ||

		// Japanese
		($r['lang'] == "ja" &&
		preg_match("/インフルエンザ/", $search)) ||

		// Chinese
		(($r['lang'] == "zh" || $r['lang'] == "ja") &&
		(preg_match("/流感/", $search) ||
		preg_match("/流行性感冒/", $search))) ||

		// Indonesian
		($r['lang'] == "id" &&
		preg_match("/\binfluensa\b/i", $search)) ||

		// Hindi
		($r['lang'] == "hi" &&
		preg_match("/फ़्लू/i", $search)) ||

		// Filippino
		preg_match("/\btrangkaso\b/i", $search) ||

		// Turkish
		($r['lang'] == "tr" &&
		preg_match("/\bgrip\b/i", $search)) ||

		// Russian
		($r['lang'] == "ru" &&
		(preg_match("/\bгрипп\b/i", $search) || preg_match("/^грипп/i", $search) || preg_match("/грипп$/i", $search) ||
		preg_match("/\bгриппом\b/i", $search) || preg_match("/^гриппом/i", $search) || preg_match("/гриппом$/i", $search))) ||
		
		// Korean NOT TESTED
		($r['lang'] == "ko" &&
		(preg_match("/독감/", $search) ||
		preg_match("/유행성 감기/", $search) ||
		preg_match("/인플루엔자/", $search))) ||

		// Dutch
		($r['lang'] == "nl" &&
		preg_match("/\bgriep\b/", $search)) ||

		// Arabic ??? NOT TESTED ***
		($r['lang'] == "ar" &&
		preg_match("/أنفلونزا/i", $search)) ||

		// Tamil ??? NOT TESTED ***
		($r['lang'] == "ta" &&
		preg_match("/காய்ச்சல்/i", $search)) ||

		// Thai NOT TESTED
		($r['lang'] == "th" &&
		(preg_match("/ไข้หวัดใหญ่/", $search) ||
		preg_match("/โรคไข้หวัดใหญ่/", $search))) ||

		// Swedish NOT TESTED
		($r['lang'] == "sv" &&
		preg_match("/\bflunsa\b/i", $search)) ||

		// Polish NOT TESTED
		($r['lang'] == "pl" &&
		(preg_match("/\bgrypa\b/i", $search) ||
		preg_match("/\bgrypą\b/i", $search))) ||

		// Finnish
		($r['lang'] == "fi" &&
		(preg_match("/\bflunssa\b/i", $search) ||
		preg_match("/\binfluenssa\b/i", $search))) ||

		// Czech ???
		($r['lang'] == "cs" &&
		(preg_match("/\bchřípka\b/i", $search) || 
		preg_match("/\bchripka\b/i", $search) || 
		preg_match("/\bchřipka\b/i", $search) || 
		preg_match("/\bchrípka\b/i", $search))) ||

		// Greek ???
		($r['lang'] == "el" &&
		(preg_match("/\bγρίπη\b/i", $search) || preg_match("/^γρίπη/i", $search) || preg_match("/γρίπη$/i", $search))) ||

		// Ukrainian ??? NOT TESTED
		($r['lang'] == "uk" &&
		(preg_match("/\bгрип\b/i", $search) || preg_match("/^грип/i", $search) || preg_match("/грип$/i", $search))) ||

		// Vietnamese ??? NOT TESTED ***
		($r['lang'] == "vi" &&
		preg_match("/\bcúm\b/i", $search)) ||

		// Persian ???
		($r['lang'] == "fa" &&
		(preg_match("/آنفولانزا/i", $search) ||
		preg_match("/انفلوانزا/i", $search)))

		// preg_match("/cough/i", $search)||
		// preg_match("/(head|\b)ache\b/i", $search)||
		// preg_match("/feel sick/i", $search)||
		// preg_match("/feeling sick/i", $search)||
		// preg_match("/see a doctor/i", $search)||
		// preg_match("/sore throat/i", $search)||
		// preg_match("/spread(ing) germs/i", $search)
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

		// Grid Coordinates
		$long = floor($ln*100.0)/100.0;
		$lat = floor($lt*100.0)/100.0;
		$areakey = implode(",",array($lat,$long));

		// Create the Map Data Based on Tweet
		$feature = array(
			'type' => 'Feature',
			'geometry' => array(
				'type' => 'Point',
				'coordinates' => array(
					$lt,
					$ln
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

// Apply Weight to Map Data
$mapjson = file_get_contents("/home/anojima/raw_data/".DISEASE."/".LOCATION."MapDataRaw.js");
$densityjson = file_get_contents("/home/anojima/raw_data/Density/".LOCATION."DensityDataRaw.js");
$densitydata = json_decode($densityjson,true);
if ($densitydata != null){
	$mapdata = json_decode($mapjson,true);
	foreach($mapdata["features"] as $id => $object){
		$twDay = $mapdata["features"][$id]["properties"]["dateString"]["day"];
		$areakey = $mapdata["features"][$id]["properties"]["areakey"];
		$boxCount = $densitydata["data"][$twDay][$areakey];
		$dailyCount = $densitydata["data"][$twDay]["daily"];
		$weight = $dailyCount/$boxCount;
		$mapdata["features"][$id]["properties"]["weight"] = $weight;
	}
	$mapjson = json_encode($mapdata);
	$fm = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."MapDataRaw.js", "w");
	fwrite($fm,$mapjson);
	fclose($fm);
}

?>