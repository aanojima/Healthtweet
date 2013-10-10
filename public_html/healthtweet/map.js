google.maps.visualRefresh = true;
var normalize = false;
var isHeatmap = false;
var isMarkermap = true;
var isRegionmap = false;
var isVectormap = false;
var markers = [];
var mc;
var vectors = [];
var heatmap;
var map;
var region;
var regions = [];
var regionpath = [];
var markerpath = [];
var regionstart;
var regionend;
var regionClicked;
var div;
var viewWidth;
var lineSymbol = {
	path : google.maps.SymbolPath.FORWARD_CLOSED_ARROW
};
var dotSymbol = {
	path : google.maps.SymbolPath.CIRCLE
};

Array.prototype.remove = function(from, to) {
	var rest = this.slice((to || from) + 1 || this.length);
	this.length = from < 0 ? this.length + from : from;
	return this.push.apply(this, rest);
};

function createMapData(start,end){
	var mapData = [];
	for (key in results.features) {
		var date = results.features[key].properties.date;
		if (date >= start && date <= end){
			var coords = results.features[key].geometry.coordinates;
			var latLng = new google.maps.LatLng(coords[0],coords[1]);
			var Loc = {
				id: key,
				location: latLng,
				weight: normalize ? results.features[key].properties.weight : null,
				text: results.features[key].properties.text,
				day: results.features[key].properties.dateString.day,
				time: results.features[key].properties.dateString.time,
				eval: results.features[key].properties.evaluated
			};
			mapData.push(Loc);
		};
	};
	return mapData;
};

function submit(){
	if ($("#classify-table input:checked").length == 0	||
		(document.getElementById("relevant").checked && $("#classify-table input:checked").length < 4)){
		return;
	}
	$.post("process.php",$(".classify").serialize());
	for (var i = 0; i < markers.length; i++){
		markers[i].infowindow.close()
	}
}

function closeAllIW(){
	for (var j = 0; j < markers.length; j++){
		markers[j].infowindow.close();
		markers[j].isInfoWindowOpen = false;
	}
}

function updateMarkerData(){
	mc.clearMarkers();
	clearMarkers();
	var markerData = createMapData($("#slider").dateRangeSlider("min"),$("#slider").dateRangeSlider("max"));
	for (var i = 0; i < markerData.length; i++){
		var date = new Date(markerData[i].day + " " + markerData[i].time);
		var marker = new google.maps.Marker({
			position: markerData[i].location,
			map: isMarkermap ? map : null,
			optimized: false,
			icon: markerData[i].eval ? null : "http://maps.google.com/mapfiles/ms/icons/blue-dot.png",
			infowindow: new google.maps.InfoWindow({
				content: 
					'<b>'+markerData[i].text+'</b><br>'+
					date+
					'<hr>'+
					'<form id="id" class="classify" method="POST">'+
						'<input type="hidden" name="id" value="'+markerData[i].id+'"/>'+
					'</form>'+
					'<form id="reply" class="classify" method="POST">'+
						'<table id="classify-table">'+
							'<th><b><u>Help Us Categorize This Tweet</u></b></th>'+
							'<tr>'+
								'<td><i>Relevance: </i></td>'+
								'<td><input type="radio" name="relevance" class="relevance" value="relevant" id="relevant">Relevant </input></td>'+
								'<td><input type="radio" name="relevance" class="relevance" value="irrelevant" id="irrelevant">Irrelevant</input></td>'+
							'</tr>'+
							'<tr>'+
								'<td><i>Type: </i></td>'+
								'<td><input type="radio" class="post-relevant" name="type" value="infection" id="infection" disabled>Infection</input></td>'+
								'<td><input type="radio" class="post-relevant" name="type" value="awareness" id="awareness" disabled>Awareness</input></td>'+
							'</tr>'+
							'<tr>'+
								'<td><i>Person(s): </i></td>'+
								'<td><input type="radio" class="post-relevant" name="persons" value="self" id="self" disabled>Self</input></td>'+
								'<td><input type="radio" class="post-relevant" name="persons" value="others" id="others" disabled>Others</input></td>'+
							'</tr>'+
							'<tr>'+
								'<td><i>Certainty of Being Sick: </i></td>'+
								'<td><input type="radio" class="post-relevant" name="confidence" value="high" id="high" disabled>High</input></td>'+
								'<td><input type="radio" class="post-relevant" name="confidence" value="medium" id="medium" disabled>Medium</input></td>'+
								'<td><input type="radio" class="post-relevant" name="confidence" value="low" id="low" disabled>Low</input></td>'+
							'</tr>'+
						'</table>'+
					'</form>'+
					'<button onclick="submit()">Submit</button>'
			}),
			isInfoWindowOpen: false
		});
		google.maps.event.addListener(marker, "click", function(){
			closeAllIW();
			this.infowindow.open(map,this);
			$(".relevance").click(function(){
				var disable = document.getElementById("relevant").checked ? false : true;
				var elements = document.getElementsByClassName("post-relevant");
				for (var i in elements){
					elements[i].disabled = disable;
				};
			});
			this.setAnimation(google.maps.Animation.BOUNCE);
			this.setAnimation(null);
		});
		google.maps.event.addListener(map, 'click', function(){
			closeAllIW();
		});
		markers.push(marker);

	};
	isMarkermap ? mc.addMarkers(markers) : mc.clearMarkers();
};

function updateVectorData(){
	clearVectors();
	var viewWidth = map.getBounds() ? map.getBounds().toSpan().lng() : 360;
	div = Math.floor(90/viewWidth);
	div = 5;

	// Current
	var currentMin = $("#slider").dateRangeSlider("min");
	var currentMax = $("#slider").dateRangeSlider("max");
	var vectorDataCurrent = createMapData(currentMin,currentMax);

	// 2D Current Array
	var current = [];
	for (var i = 0; i < vectorDataCurrent.length; i++){
		var x = vectorDataCurrent[i].location.lng();
		var y = vectorDataCurrent[i].location.lat();
		var xkey = Math.floor(x*div) + 180*div;
		var ykey = Math.floor(y*div) + 90*div;
		current[ykey] ? null : current[ykey] = [];
		current[ykey][xkey] ? current[ykey][xkey] += 1 : current[ykey][xkey] = 1;
	}
	
	// Past
	var pastMin = currentMin - 604800000;
	var pastMax = currentMax - 604800000;
	var vectorDataPast = createMapData(pastMin,pastMax);

	// 2D Past Array
	var past = [];
	for (var i = 0; i < vectorDataPast.length; i++){
		// var areakey = vectorDataPast[i].areakey;
		var x = vectorDataPast[i].location.lng();
		var y = vectorDataPast[i].location.lat();
		var xkey = Math.floor(x*div) + 180*div;
		var ykey = Math.floor(y*div) + 90*div;
		past[ykey] ? null : past[ykey] = [];
		past[ykey][xkey] ? past[ykey][xkey] += 1 : past[ykey][xkey] = 1;
	}

	// 2D Delta Array
	var delta = [];
	for (var r = 0; r <= 180*div; r++){
		if (!(current[r] || past[r])){continue};
		current[r] ? null : current[r] = [];
		past[r] ? null : past[r] = [];
		for (var c = 0; c <= 360*div; c++){
			if (!(current[r][c] || past[r][c])){continue};
			var before = past[r][c] ? past[r][c] : 0;
			var after = current[r][c] ? current[r][c] : 0;
			var change = before == 0 ? after : (after - before)/(before);
			delta[r] ? null : delta[r] = [];
			delta[r][c] = change;
		}
	}

	// Vector Arrows
	var multiplier = 1;
	var dimension = 5;
	for (var r = 0; r <= 180*div; r++){
		for (var c = 0; c <= 360*div; c++){
			var center = [((r/div)-90)+(1/(div*2)), ((c/div)-180)+(1/(div*2))];
			var startPoint = new google.maps.LatLng(center[0],center[1]);
			var surround = 0;
			var horz = 0;
			var vert = 0;
			var step = (dimension - 1)/2;
			var localMax = true;
			var localMin = true;
			for (var dy = -step; dy <= step; dy++){
				for (var dx = -step; dx <= step; dx++){
					if (dx == 0 && dy == 0){continue};
					if (!delta[r+dy]){continue};
					if (!delta[r+dy][c+dx]){continue};
					if (delta[r]){if (delta[r][c]){
						localMax = localMax ? delta[r][c] >= delta[r+dy][c+dx] : false;
						localMin = localMin ? delta[r][c] <= delta[r+dy][c+dx] : false;
					}};
					var dist = Math.sqrt(Math.pow(dx,2) + Math.pow(dy,2));
					var val = multiplier * delta[r+dy][c+dx] / dist;
					horz += val*(dx/dist);
					vert += val*(dy/dist);
					surround += 1;
				}
			}
			if ((localMax || localMin) && surround == dimension*dimension - 1){continue};
			if ((horz == 0 && vert == 0) || surround <= 1){continue};			
			var mag = Math.sqrt(Math.pow(horz,2) + Math.pow(vert,2));
			var end = [center[0] + vert/(2*div*mag), center[1] + horz/(2*div*mag)];
			var endPoint = new google.maps.LatLng(end[0],end[1]);

			//CREATE VECTOR!!!
			var vector = new google.maps.Polyline({
				path: [startPoint,endPoint],
				icons: [{
					icon: lineSymbol,
					offset: '100%'
				}],
				strokeColor: "rgb(" + String(Math.floor(mag*32)) + ", 0, 0)",
				map: isVectormap ? map : null
			});
			vectors.push(vector);
		}
	}
}

function createPolygon(isNew){
	
	// Exit if Not 3 Vertices
	if (regionpath.length < 3){
		region.setMap(null);
		region = null;
		regionstart.setMap(null);
		regionstart = null;
		regionpath = [];
		return null;
	}

	// Setup the Polygon
	var randomColor = '#'+Math.floor(Math.random()*16777215).toString(16);
	var polygon = new google.maps.Polygon({
		paths: regionpath,
		strokeColor: randomColor,
		strokeOpacity: 0.8,
		strokeWeight: 2,
		fillColor: randomColor,
		map: map,
		fillOpacity: 0.4,
		editable: true,
		draggable: true,
		ID: region.ID,
		selected: false
	});

	// Clear Polyline Region
	region.setMap(null);
	region = null;

	// Add Listener to Clear Polygon
	google.maps.event.addListener(polygon,"rightclick",function(){
		polygon.selected = false;
		clearPolygons(polygon);
		updateRegionsChart();
	});

	google.maps.event.addListener(polygon,"click",function(){
		polygon.selected = !polygon.selected;
		polygon.setOptions({fillOpacity: polygon.selected ? 0.8 : 0.4});
		polygon.setOptions(polygon.selected ? {editable: false, draggable: true} : {editable : true, draggable : true});
		updateRegionsChart();
		
	});

	google.maps.event.addListener(polygon, 'dragend', function(){
		updateRegionsChart();
	});

	// Check if Old or New and Add or Edit to Regions Array
	if (isNew){
		regions.push(polygon);	
	}else{
		regions[polygon.ID] = polygon;
	}

	// Make Regions Clickable
	for (var i = 0; i < regions.length; i++){
		regions[i].setOptions({clickable:true});
	}

	// Reset Start Marker and Current Path Status
	regionstart.setMap(null);
	regionstart = null;
	regionpath = [];
}

function drawRegion(location) {
	for(var i = 0; i < regions.length; i++){
		regions[i].setOptions({clickable:false});
	}
	regionpath.push(location);
	if (regionpath.length == 1){
		region = new google.maps.Polyline({
			map: map,
			ID: regions.length
		});
		regionstart = new google.maps.Marker({
			position: regionpath[0],
			map: map,
		});
		google.maps.event.addListener(region,"rightclick",function(){
			clearRegionstart();
			clearPolyline(this);
		});
		google.maps.event.addListener(regionstart,"rightclick",function(){
			clearRegionstart();
			clearPolyline(region);
		});
		google.maps.event.addListener(regionstart,"click",function(){
			createPolygon(true);
		});
	}
	region.setPath(regionpath);
}
 
function updateMap() {
	if (map !== undefined){
		heatmap.setData(createMapData($("#slider").dateRangeSlider("min"),$("#slider").dateRangeSlider("max")));
		updateMarkerData();
		updateVectorData();
	}
}

function setMarkersMap(mapset){
	for (var i = 0; i < markers.length; i++){
		markers[i].setMap(mapset);
	}
}

function setVectorsMap(mapset){
	for (var i = 0; i < vectors.length; i++){
		vectors[i].setMap(mapset);
	}
}

function setRegionsMap(mapset){
	for (var i = 0; i < regions.length; i++){
		regions[i].setMap(mapset);
	} 
}

function showMarkers(){
	setMarkersMap(map);
	mc.addMarkers(markers);
}

function showVectors(){
	setVectorsMap(map);
}

function showRegions(){
	setRegionsMap(map);
	updateRegionsChart();
}

function hideMarkers(){
	setMarkersMap(null);
	mc.clearMarkers();
}

function hideVectors(){
	setVectorsMap(null);
}

function hideRegions(){
	setRegionsMap(null);
	regionstart ? clearRegionstart() : null;
	region ? clearPolyline(region) : null;
	updateChart();
}

function clearMarkers(){
	hideMarkers();
	markers = [];
}

function clearVectors(){
	hideVectors();
	vectors = [];
}

function clearPolygons(selection) {
	selection.setMap(null);
	selection.setPaths([]);
	var index = regions.indexOf(selection);
	regions.remove(index);
}

function clearPolyline(selection) {
	regionpath = [];
	selection.setMap(null);
	selection.setPath([]);
	selection = null;
}

function clearRegionstart() {
	regionstart.setMap(null);
	regionstart = null;
}

function toggleMarkermap() {
	isMarkermap ? hideMarkers() : showMarkers();
	isMarkermap = !isMarkermap;
}

function toggleHeatmap() {
	heatmap.setMap(heatmap.getMap() ? null : map);
	isHeatmap = isHeatmap ? false : true;
}

function toggleNormalize() {
	normalize = !normalize;
	updateMap();
}

function toggleVectormap() {
	// $("#slider").dateRangeSlider("option", "step", {days: isVectormap ? 1 : 7});
	$("#slider").dateRangeSlider("option", "range", isVectormap ? 
		{min: {months : 0}, max: {months : 1}} : 
		{min : {days : 7}, max : {days : 7}});
	isVectormap ? hideVectors() : showVectors();
	isVectormap = !isVectormap;
}

function toggleRegionmap() {
	isRegionmap ? hideRegions() : showRegions();
	isRegionmap = !isRegionmap;
}

function changeGradient() {
	var gradient = [
		'rgba(0, 255, 255, 0)',
		'rgba(0, 255, 255, 1)',
		'rgba(0, 191, 255, 1)',
		'rgba(0, 127, 255, 1)',
		'rgba(0, 63, 255, 1)',
		'rgba(0, 0, 255, 1)',
		'rgba(0, 0, 223, 1)',
		'rgba(0, 0, 191, 1)',
		'rgba(0, 0, 159, 1)',
		'rgba(0, 0, 127, 1)',
		'rgba(63, 0, 91, 1)',
		'rgba(127, 0, 63, 1)',
		'rgba(191, 0, 31, 1)',
		'rgba(255, 0, 0, 1)'
	]
	heatmap.setOptions({
		gradient: heatmap.get('gradient') ? null : gradient
	});
}

function changeRadius() {
	var newRadius = $("#radius_slider").slider("value");
	heatmap.setOptions({radius: newRadius});
}

function changeOpacity() {
	newOpacity = $("#opacity_slider").slider("value");
	heatmap.setOptions({opacity: newOpacity});
}

function initialize() {
	var styles = [
  
  {
    "featureType": "landscape",
    "stylers": [
      { "visibility": "off" }
    ]
  },{
    "featureType": "poi",
    "stylers": [
      { "visibility": "off" }
    ]
  },{
    "featureType": "road",
    "stylers": [
      { "visibility": "off" }
    ]
  },{
    "featureType": "transit",
    "stylers": [
      { "visibility": "off" }
    ]
  },{
    "featureType": "administrative.country",
    "elementType": "labels",
    "stylers": [
      { "visibility": "simplified" }
    ]
  },{
    "featureType": "administrative.neighborhood",
    "elementType": "labels",
    "stylers": [
      { "visibility": "off" }
    ]
  },{
    "featureType": "administrative.locality",
    "elementType": "labels",
    "stylers": [
      { "visibility": "off" }
    ]
  },{
    "featureType": "administrative.province",
    "elementType": "labels",
    "stylers": [
      { "visibility": "simplified" }
    ]
  },{
    "featureType": "water",
    "elementType": "labels",
    "stylers": [
      { "visibility": "off" }
    ]
  }

	];
	var styledMap = new google.maps.StyledMapType(styles,{name: "Styled Map"});

	map_canvas = document.getElementById('map_canvas');
	mapOptions = {
		zoom: 2,
		// center: new google.maps.LatLng(coordinates[0],coordinates[1]),
		mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
	};
	map = new google.maps.Map(map_canvas, mapOptions);
	
	if(navigator.geolocation) {
		browserSupportFlag = true;
		navigator.geolocation.getCurrentPosition(function(position) {
			initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
			map.setCenter(initialLocation);
		}, function() {
			handleNoGeolocation(browserSupportFlag);
		});
	}
	// Browser doesn't support Geolocation
	else {
		browserSupportFlag = false;
		handleNoGeolocation(browserSupportFlag);
	}
	function handleNoGeolocation(errorFlag) {
		if (errorFlag == true) {
			alert("Geolocation service failed.");
			initialLocation = newyork;
		} else {
			alert("Your browser doesn't support geolocation. We've placed you in Siberia.");
			initialLocation = siberia;
		}
		map.setCenter(initialLocation);
	}
	zoomWas = map.getZoom();
	map.mapTypes.set('map_style', styledMap);
	map.setMapTypeId('map_style');
	
	google.maps.event.addListener(map, 'idle', function(){
		isRegionmap ? updateRegionsChart() : updateChart();
	});
	google.maps.event.addListener(map, "click", function(event){
		isRegionmap ? drawRegion(event.latLng) : null;
	});
	google.maps.event.addListener(map, "mousemove", function(event){
		var report = "Cursor Location: " + String(event.latLng.lat()) + ", " + String(event.latLng.lng());
		var newDiv = document.getElementById("cursorLog");
		if (!newDiv){
			var newDiv = document.createElement("div");
			newDiv.id = "cursorLog";
			newDiv.style.position = "absolute";
			newDiv.style.left = "100px";
			newDiv.style.top = "10px";
			newDiv.style.zIndex = "1";
			var newInput = document.createElement("input");
			newInput.size = "55";
			newInput.disabled = true;
			newDiv.appendChild(newInput);
			var mapContainer = document.getElementById("map_container");
			mapContainer.insertBefore(newDiv,mapContainer.childNodes[0]);
		}
		newDiv.childNodes[0].value = report;
	});
	mc = new MarkerClusterer(map, markers, {
		maxZoom: 15,
		gridSize: 40
	});
	heatmap = new google.maps.visualization.HeatmapLayer({
		data: createMapData($("#slider").dateRangeSlider("min"),$("#slider").dateRangeSlider("max")),
		radius: 50,
		map: isHeatmap ? map : null,
		maxIntensity: 0.3,
		opacity: 0.5
	});
	updateMarkerData();
	updateVectorData();
	
};

google.maps.event.addDomListener(window, 'load', initialize);