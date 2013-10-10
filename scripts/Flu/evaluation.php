<?php

define("DISEASE", "Flu");

$locations = array(
	"BR" => "Brazil",
	"CA" => "Canada",
	"JP" => "Japan",
	"MX" => "Mexico",
	"US" => "UnitedStates",
	"NYC" => "NYC"
);

foreach($locations as $cc => $country){

	$filename = "/home/anojima/raw_data/".DISEASE."/".$country."MapDataRaw.js";
	$json = file_get_contents($filename);
	$data = json_decode($json, true);

	$client = new MongoClient("mongodb://mongo-clusterAA1:27017");
	$database = $client->healthtweet;
	$collection = $database->categories;
	$cursor = $collection->find();
	foreach($cursor as $doc){
		$id = $doc["_id"];
		if (array_key_exists($id, $data["features"])){
			$data["features"][$id]["properties"]["evaluated"] = true;
		}
	}

	$json = json_encode($data);
	$file = fopen($filename,"w");
	fwrite($file,$json);
	fclose($file);

	$readMapDataFile = fopen("/home/anojima/public_html/healthtweet/data/".DISEASE."/".$country."MapData.js","w");
	fwrite($readMapDataFile,"var results = \n".$json.";\n");
	fclose($readMapDataFile);

}

?>