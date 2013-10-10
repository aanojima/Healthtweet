<?php

// Set Disease
define("DISEASE", "TopWordsDengue");

// Set Location
define("LOCATION", "Mexico");
define("CC", "MX");


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
// $lastUpdated = "October 15, 2012 00:00:00";
// $now = "October 16, 2012 00:00:00";

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

// Load or Reload Word Data as JSON

// *** Word Data ***
$dw = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."WordDataRaw.js","c");
$decodeWord = file_get_contents("/home/anojima/raw_data/".DISEASE."/".LOCATION."/WordDataRaw.js");
if ($decodeWord == null){
	$wordjson = array();
}else{
	$wordjson = json_decode($decodeWord,true);
}
fclose($dw);

// *** Cumulative Word Data ***
$dcw = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."CumulativeWordRawData.js","c");
$decodeCumWord = file_get_contents("/home/anojima/raw_data/".DISEASE."/".LOCATION."/CumulativeWordRawData.js");
if ($decodeCumWord == null){
	$cumulativeWordJson = array();
}else{
	$cumulativeWordJson = json_decode($decodeCumWord,true);
}
fclose($dcw);

// *** History Log ***
$dwh = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."WordHistoryRawData.js","c");
$decodeWordHistory = file_get_contents("/home/anojima/raw_data/".DISEASE."/".LOCATION."/WordHistoryRawData.js");
if ($decodeWordHistory == null){
	$historyjson = array();
}else{
	$historyjson = json_decode($decodeWordHistory,false);
}
fclose($dwh);

// Collect Tweet Information
$ncTweets->immortal(true);
// // $unwantedChars = array("`","~","!","@","#","$","%","^","&","*","(",")","-","_","=","+","[","{","]","}","|",";",":","'",'"',",","<",".",">","/","?"," ",
// 	"¿","¡","♪","⛵","💕","¢","£","¤","¥","€","¦","§","¨","©","ª","«","»","¬","©","¶");
foreach($ncTweets as $r)
{	
	if (in_array(strval($r['_id']),$historyjson)){
		continue;
	}
	if (

		(($r['lang'] == 'en' || $r['lang'] == 'es') &&
		preg_match("/\bdengue\b/i", $r['t'])) ||
		
		($r['lang'] == 'ms' &&
		preg_match("/\bdenggi\b/i", $r['t'])) ||

		($r['lang'] == 'zh' &&
		preg_match("/登革热/i", $r['t'])) ||

		($r['lang'] == 'ta' &&
		preg_match("/எலும்பு கணுக்களில் நோய் உண்டு பண்ணும் காய்ச்சல்/i", $r['t']))

	)
	{
		$twDay = date("Y-m-d", $r['cr']->sec);
		$keywords = preg_split("/[\s,]+/", $r['t']);
		foreach($keywords as $word){
			$wordjson[$twDay][$word]++;
			$cumulativeWordJson[$word]++;
		}
		array_push($historyjson,strval($r['_id']));

		// Make a Log for Progress Check
		$fl = fopen("/home/anojima/log/".DISEASE."/".LOCATION."Log.txt", "w");
		$twDateTime = date("Y-m-d H:i:s", $r['cr']->sec);
		fwrite($fl, DISEASE." @ ".LOCATION."\n".$twDateTime);
		fclose($fl);
	}

}

// Encode Word Data and Output to Word File
$wordresult = json_encode($wordjson); // JSON encoded data
$fw = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."/WordDataRaw.js","w");
fwrite($fw, $wordresult);
fclose($fw);

// Encode Cumulative Word Data and Output to Cumulative Word File
$cumulativeWordResult = json_encode($cumulativeWordJson);
$fcw = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."/CumulativeWordRawData.js","w");
fwrite($fcw, $cumulativeWordResult);
fclose($fcw);

// Encode History Word Data and Output to History Word File
$historyresult = json_encode($historyjson);
$fh = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."/WordHistoryRawData.js","w");
fwrite($fh, $historyresult);
fclose($fh);

// Top 200 Words
$topwords = array("Date");
$i = 0;
arsort($cumulativeWordJson,SORT_NUMERIC);
foreach($cumulativeWordJson as $key => $value)
{
	if($i < 200){
		array_push($topwords, $key);
		$i++;
	}else{
		break;
	}
}

// CSV
$fwcsv = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."/WordData.csv","w");
fputcsv($fwcsv, $topwords);
foreach($wordjson as $day => $wordsCounts)
{
	$csv_array = array($day);
	for($i = 1; $i < count($topwords); $i++){
		$word = $topwords[$i];
		array_push($csv_array, $wordjson[$day][$word]);
	}
	fputcsv($fwcsv, $csv_array);
}

?>