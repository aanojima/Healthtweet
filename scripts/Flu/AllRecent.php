<?php

// Customizable
define("DISEASE", "Flu");

$now = date_create()->getTimestamp()*1000;
$interval = new DateInterval("P6D");
$weekAgo = date_create(date_create()->sub($interval)->format("Y-m-d"))->getTimestamp()*1000;

// Customizable
$locations = array(
	"BR" => "Brazil",
	"CA" => "Canada",
	"JP" => "Japan",
	"MX" => "Mexico",
	"US" => "UnitedStates"
);

$recentData = array(
	"type" => "FeatureCollection",
	"features" => array()
);

foreach($locations as $cc => $name){
	$json = file_get_contents("/home/anojima/raw_data/".DISEASE."/".$name."MapDataRaw.js");
	if (!$json){
		continue;
	}
	$data = json_decode($json, true);
	foreach($data["features"] as $id => $feature){
		$date = $feature["properties"]["date"];
		if ($date >= $weekAgo && $date <= $now){
			$recentData["features"][$id] = $feature;
			
			// Make a Log for Progress Check
			$fl = fopen("/home/anojima/log/".DISEASE."/Log.txt", "w");
			fwrite($fl, DISEASE." for ".$name."\n".$data["features"][$id]["properties"]["dateString"]["day"]);
			fclose($fl);
		}
	}
}

$recentJSON = json_encode($recentData);
$update = "var results = \n".$recentJSON;
$file = fopen("/home/anojima/public_html/healthtweet/data/".DISEASE."/AllMapData.js", "w");
fwrite($file, $update);
fclose($file);

?>