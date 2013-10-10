<?php
	$id = $_POST["id"];
	$relevance = $_POST["relevance"];
	if ($relevance == "relevant"){
		$type = $_POST["type"];
		$persons = $_POST["persons"];
		$confidence = $_POST["confidence"];
	}
	if ($id && $relevance){

		// Server-Side File
		$filename = "categories.js";
		$userFeedbackJSON = file_get_contents($filename);
		if ($userFeedbackJSON == null){
			$userFeedback = array();
		}else{
			$userFeedback = json_decode($userFeedbackJSON,true);
		}
		$userFeedback[$id]["replies"] += 1;
		$userFeedback[$id][$relevance] += 1;
		if ($relevance == "relevant"){
			$userFeedback[$id][$type] += 1;
			$userFeedback[$id][$persons] += 1;
			$userFeedback[$id][$confidence] += 1;
		}
		$userFeedbackJSON = json_encode($userFeedback);
		$fp = fopen($filename,"w");
		fwrite($fp, $userFeedbackJSON);
		fclose($fp);

		// Connect to Client-Database-Collection
		$client = new MongoClient('mongodb://mongo-clusterAA1:27017');
		$database = $client->healthtweet;
		$collection = $database->categories;

		// Change Count
		$query = array("_id" => $id);
		$items = array("replies" => 1, $relevance => 1);
		if ($relevance == "relevant"){
			$items[$type] = 1;
			$items[$persons] = 1;
			$items[$confidence] = 1;
		}
		$update = array('$inc' => $items);
		$options = array("upsert" => true);
		$collection->update($query, $update, $options);

	}
?>