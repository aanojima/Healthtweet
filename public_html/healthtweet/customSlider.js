var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sept", "Oct", "Nov", "Dec"];
var date = new Date();
var tomorrow = new Date(date.getTime() + 86400000);
var oneweekago = new Date(date.getTime() - 604800000);
var dateMin;
var dateMax;
function dataRange(){
	dateMin = $("#slider").dateRangeSlider("min");
	dateMax = $("#slider").dateRangeSlider("max");
}

$(document).ready(function(){
	$("#slider").dateRangeSlider({
		defaultValues:{
			min: oneweekago,
			max: tomorrow
		},
		arrows: true,
		bounds:{
			min: new Date(2012, 09, 01),	
			max: tomorrow
		},
		valueLabels: "change",
		range: isVectormap ? {
			min: {days : 7},
			max: {days : 7}
		} : {
			min: {days : 1},
			max: {days : 7}
		},
		step: {days: isVectormap ? 7 : 1},
		wheelMode: null,
		formatter:function(val){
			var days = val.getDate(),
				month = val.getMonth() + 1,
				year = val.getFullYear();
			return month + "/" + days + "/" + year;
		},
		scales: [{
			first: function(value){ return value; },
			end: function(value) {return value; },
			next: function(value){
				var next = new Date(value);
				return new Date(next.setMonth(value.getMonth() + 1));
			},
			label: function(value){
				return months[value.getMonth()];
			},
			format: function(tickContainer, tickStart, tickEnd){
				tickContainer.addClass("myCustomClass");
			}
		}]
	});

	$("#slider").on("valuesChanged", dataRange); 
	$("#slider").on("valuesChanged", updateMap);
	$("#slider").on("valuesChanged", updateChart);

});

// IS Maptype