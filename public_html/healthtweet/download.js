function csvDownload(){
	var data = window.downloadData;
	var csvContent = "data:text/csv;charset=utf-8,";
	for (var i = 0; i < data.length; i++){
		dataString = data[i].join(",");
		csvContent += i < data.length - 1 ? dataString + "\n" : dataString;
	}
	var encodedUri = encodeURI(csvContent);
	var link = document.createElement("a");
	var downloadName = "HealthTweet_" + disease + "_" + name + "_Data.csv"
	link.setAttribute("href", encodedUri);
	link.setAttribute("download", downloadName);
	link.click();
}