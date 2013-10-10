<?php

$now = date("F d, Y H:i:s");
// $now = "October 16, 2012 00:00:00";
$lastUpdated = file_get_contents("/home/anojima/updated_times/Density/LastUpdated.txt");
if ($lastUpdated == null){
	$lastUpdated = "October 15, 2012 00:00:00";
}

$client = new MongoClient('mongodb://mongo-clusterAA1:27017');
$db = $client->twitter;
$db->setSlaveOkay(true);
MongoCursor::$timeout = -1;
$tweets = $db->geoTweets->find(
	array(
		'$and' => array(
			array('cr' => array('$gt' => new MongoDate(strtotime($lastUpdated)))),
			array('cr' => array('$lt' => new MongoDate(strtotime($now))))
		)
	)
);
$tweets->immortal(true);

echo "got here fine\n";

$collection = $client->healthtweet->density;
$options = array("upsert" => true);
foreach($tweets as $r){

	// Day
	$day = date("Y-m-d", $r['cr']->sec);
	$time = date("H:i:s", $r['cr']->sec);

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
		}
	}else{
		$lt = $tlt;
		$ln = $tln;
	}

	// Areakey Coordinates
	$long = floor($ln*100.0)/100.0;
	$lat = floor($lt*100.0)/100.0;
	$areakey = implode(",",array($lat,$long));
	$areakey = str_replace(".", "d", $areakey);

	// // Update Areakey & Daily (and ID if necessary) $td[$day] is a document of interest
	$query = array("_id" => $day);
	$update = array('$inc' => array("daily" => 1, $areakey => 1));
	$collection->update($query, $update, $options);

	// // Make a Log for Progress Check
	$fl = fopen("/home/anojima/log/Density/Log.txt", "w");
	fwrite($fl, "Updating Density on ".$day." at ".$time);
	fclose($fl);
}

$lastUpdated = $now;
$ff = fopen("/home/anojima/updated_times/Density/LastUpdated.txt","w");
fwrite($ff,$lastUpdated);
fclose($ff);

?>