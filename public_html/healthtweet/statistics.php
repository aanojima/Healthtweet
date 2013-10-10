<?php
$client = new MongoClient('mongodb://mongo-clusterAA1:27017');
$database = $client->healthtweet;
$collection = $database->classification;
$id = $_POST["id"];
$query = array("_id" => $id);
$document = $collection->findOne($query);

$pSYes = strval(round(100*$document["sYesIndex"],1));
$pSUncertain = strval(round(100*$document["sUncertainIndex"],1));
$pSNo = strval(round(100*$document["sNoIndex"],1));
$pOYes = strval(round(100*$document["oYesIndex"],1));
$pOUncertain = strval(round(100*$document["oUncertainIndex"],1));
$pONo = strval(round(100*$document["oNoIndex"],1));
$pIrrelevant = strval(round(100*$document["IrrelevantIndex"],1));



print "<b>Is this person infected or referring to someone who is infected?</b><br><i>".$pSYes."% said Yes (self).  ".$pSUncertain."% said Uncertain (self).  ".$pSNo."% said No (self).  ".$pOYes."% said Yes (other).  ".$pOUncertain."% said Uncertain (other).  ".$pONo."% said No (other).  ".$pIrrelevant."% said Irrelevant.  </i>";
?>