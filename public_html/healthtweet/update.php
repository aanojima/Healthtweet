<?php

$north = floatval($_POST['n']);
$south = floatval($_POST['s']);
$east = floatval($_POST['e']);
$west = floatval($_POST['w']);
$start = floatval($_POST['start']);
$end = floatval($_POST['end']);
$loc = $_POST['loc'];
$disease = $_POST['disease'];

$filename = "/home/anojima/raw_data/".$disease."/".$loc."MapDataRaw.js";
$json = file_get_contents($filename);
$data = json_decode($json, true);

$newdata = array(
	"type" => "FeatureCollection",
	"features" => array()
);

foreach($data["features"] as $key => $feature){
	$coordinates = $feature["geometry"]["coordinates"];
	$lat = $coordinates[0];
	$long = $coordinates[1];
	$date = $feature["properties"]["date"];
	if ($date >= $start && $date <= $end){
		if ($lat >= $south && $lat <= $north && $long >= $west && $long <= $east){
			$newdata["features"][$key] = $feature;
		}
	}
}

$results = json_encode($newdata);
echo $results;

?>