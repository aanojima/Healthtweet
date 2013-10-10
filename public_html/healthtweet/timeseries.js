google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(updateChart);
var timeseries = {};
var downloadData;
var chartoptions = {
	title: name+ ' Flu Season 2012-13',
	width: 410,
	height: 270,
	chartArea:{
		left: 50,
		top: 25,
		width: 350,
		height: 200	
	},
	// curveType: "function",
};

Date.prototype.addDays = function(days) {
	var dat = new Date(this.valueOf())
	dat.setDate(dat.getDate() + days);
	return dat;
}

Date.prototype.getDateString = function() {
	var year = String(this.getFullYear());
	var month = String(this.getMonth()+1);
	if (month.length < 2){
		month = String(0) + month;
	}
	var day = String(this.getDate());
	if (day.length < 2){
		day = String(0) + day;
	}
	var string = year + "-" + month + "-" + day;
	return string;
}

function getDates(startDate, stopDate) {
	var dateArray = new Array();
	var currentDate = startDate;
	while (currentDate <= stopDate) {
		dateArray.push(currentDate)
		currentDate = currentDate.addDays(1);
	}
	return dateArray;
}

function drawChart() {
	var data = google.visualization.arrayToDataTable(window.timeData);
	chart = new google.visualization.LineChart(document.getElementById('chart_div'));
	chart.draw(data, chartoptions);
}

function timeseriesToChart(timeseries){
	var time = [["Date", "Tweets"]];
	var days = Object.keys(timeseries);
	for (var i = 0; i < days.length; i++){
		time.push([days[i], timeseries[days[i]]]);
	}
	if (time.length < 2){
		time.push(["Out of Range",0,0]);
	}else{
		for (var i = 0; i < days.length; i++){
			var total = 0;
			var step = 0;
			for (var j = i-3; j < i+4; j++){
				var count = timeseries[days[j]];
				if (count === undefined){
					continue;
				}else{
					total += count;
					step += 1;
				}
			}
			var average = total / step;
			time[i+1].push(average);
		}	
	}
	downloadData = time;
	time[0].push("Average");
	

	var data = google.visualization.arrayToDataTable(time);
	chart = new google.visualization.LineChart(document.getElementById('chart_div'));
	chart.draw(data, chartoptions);
	
}

function updateChart(){
	if (map !== undefined){
		var bounds = map.getBounds();
		if (bounds === undefined){
			return null;
		}
		var n = bounds.getNorthEast().lat();
		var e = bounds.getNorthEast().lng();
		var s = bounds.getSouthWest().lat();
		var w = bounds.getSouthWest().lng();
		if (n==s && e==w){
			return null;
		}
		var start = $("#slider").dateRangeSlider("option", "bounds").min;
		var end = new Date();
		var timePoints = createMapData(start, end);
		timeseries = {};
		var datesFull = getDates(start,end);
		for (var i = 0; i < datesFull.length; i++) {
			var dateString = datesFull[i].getDateString();
			timeseries[dateString] = 0;
		}
		for (var i = 0; i < timePoints.length; i++){
			var tp = timePoints[i];
			if (tp.location.lat() >= s &&
				tp.location.lat() <= n &&
				tp.location.lng() >= w &&
				tp.location.lng() <= e){
				timeseries[tp.day] = timeseries[tp.day] == null ? 1 : timeseries[tp.day] + 1;
			}
		}
		timeseriesToChart(timeseries);
	}
}

function updateRegionsChart(){
	if (map !== undefined){
		if (regions.length < 1){
			updateChart();
			return null;
		}
		var start = $("#slider").dateRangeSlider("option", "bounds").min;
		var end = new Date();
		var timePoints = createMapData(start, end);
		timeseries = {};
		var datesFull = getDates(start,end);
		for (var i = 0; i < datesFull.length; i++) {
			var dateString = datesFull[i].getDateString();
			timeseries[dateString] = 0;
		}
		for (var i = 0; i < regions.length; i++){
			if (!regions[i].selected){
				continue;
			}
			for (var j = 0; j < timePoints.length; j++){
				if (regions[i].containsLatLng(timePoints[j].location)){
					timeseries[timePoints[j].day] = timeseries[timePoints[j].day] == null ? 1 : timeseries[timePoints[j].day] + 1;
				}
			}	
		}
		if (Object.keys(timeseries).length < 1){
			updateChart();
		}else{
			timeseriesToChart(timeseries);
		}
	}
}