<?php

define("DISEASE", "Flu");
define("LOCATION", "Test");

$lastUpdated = file_get_contents("/home/anojima/updated_times/".DISEASE."/".LOCATION."LastUpdated.txt");
if ($lastUpdated == null){
	$lastUpdated = "October 15, 2012 00:00:00";
	$ftime = fopen("/home/anojima/updated_times/".DISEASE."/".LOCATION."LastUpdated.txt","w");
	fwrite($ftime, $lastUpdated);
	fclose($ftime);
}
$now = date("F d, Y H:i:s");
echo $lastUpdated;

?>