<?php

define("DISEASE", "Flu");

require_once "/home/anojima/scripts/countries.php";
$countries = getCountries();

$output = array(
	"type" => "FeatureCollection",
	"features" => array()
);

foreach($countries as $cc => $country){
	if (!$country){
		continue;
	}
	$filename = "/home/anojima/raw_data/".DISEASE."/".$country."MapDataRaw.js";
	$json = file_get_contents($filename);
	if (!$json){
		continue;
	}
	$data = json_decode($json,true);
	$output["features"] = array_merge($output["features"], $data["features"]);
}

$json = json_encode($output);
echo "var AllResults = ".$json;

echo "console.log('PHP Worked!');";

?>