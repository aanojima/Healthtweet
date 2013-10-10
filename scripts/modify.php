<?php

error_reporting(0);

// For Compatability, Everything With *** Changed by Defined Location
define("DISEASE", "Flu");
define("LOCATION", "Canada");

// *** Set Coordinate Boundaries ***
// require_once "/home/anojima/coordinates/".LOCATION."Coordinates.php";

// Load or Reload Map and Time Data as JSON

// *** Map Data ***
$dm = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."MapDataRaw.js","c");
$decodeMap = file_get_contents("/home/anojima/raw_data/".DISEASE."/".LOCATION."MapDataRaw.js");
$geojson = json_decode($decodeMap,true);
fclose($dm);

// Density Data
$dd = fopen("/home/anojima/raw_data/Density/".LOCATION."DensityDataRaw.js","c");
$decodeDensity = file_get_contents("/home/anojima/raw_data/Density/".LOCATION."DensityDataRaw.js");
$densityjson = json_decode($decodeDensity,true);
fclose($dd);

// Collect Tweet Information
$keys = array_keys($geojson["features"]);

foreach($keys as $key)
{
	
	$lt = $geojson["features"][$key]["geometry"]["coordinates"][1];
	$ln = $geojson["features"][$key]["geometry"]["coordinates"][0];

	$geojson["features"][$key]["geometry"]["coordinates"][0] = $lt;
	$geojson["features"][$key]["geometry"]["coordinates"][1] = $ln;

}

// *** Encode Map Data and Output to Map File ***
$mapresult = json_encode($geojson); // JSON encoded data
$fm = fopen("/home/anojima/raw_data/".DISEASE."/".LOCATION."MapDataRaw.js","w");
fwrite($fm, $mapresult);
fclose($fm);

// Create Readable Data for HTML-Javascript

$readMapDataFile = fopen("/home/anojima/public_html/data/".DISEASE."/".LOCATION."MapData.js","w");
fwrite($readMapDataFile,"var results = \n".$mapresult.";\n");
fclose($readMapDataFile);

?>