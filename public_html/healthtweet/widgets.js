$(function(){$("#tabs").tabs();});
$(function(){$("#search").button({icons: {primary: "ui-icon-search"}, label: "Change Location/Disease", text:false})});
$(function(){$("#map_tools").button({icons: {primary: "ui-icon-gear"}, label: "Map Tools", text: false})});
$(function(){$("#timeseries").button({icons: {primary: "ui-icon-image"}, label: "Timeseries", text: false})});
$(function(){$("#twitter").button({icons: {primary: "ui-icon-note"}, label: "Twitter Feed", text: false})});
$(function(){$("#drawing").button({icons: {primary: "ui-icon-pencil"}, label: "Drawing Tools", text: false})});

$(function() { $( "#radius_slider" ).slider({animate: "fast", max: 100, min: 0, step: 0.1, value: 50, 
	"slide": function(event, ui){changeRadius();},
	"start": function(event, ui){changeRadius();},
	"stop" : function(event, ui){changeRadius();}
})});
$(function() { $( "#opacity_slider" ).slider({animate: "fast", max: 1.0, min: 0, step: 0.01, value: 0.5, 
	"slide": function(event, ui){changeOpacity();},
	"start": function(event, ui){changeOpacity();},
	"stop" : function(event, ui){changeOpacity();}
})});

$(document).ready(function(){
	$("#search").click(function(){
		if($("#search_menu").is(":visible")){$(".menu_popup").hide()}
		else{$(".menu_popup").hide();$("#search_menu").show()};
	});
	$("#timeseries").click(function(){
		if($("#timeseries_div").is(":visible")){$(".menu_popup").hide()}
		else{$(".menu_popup").hide();$("#timeseries_div").show()};
	});
	$("#map_tools").click(function(){
		if($("#map_tools_menu").is(":visible")){$(".menu_popup").hide()}
		else{$(".menu_popup").hide();$("#map_tools_menu").toggle()};
	});
	$("#drawing").click(function(){
		if($("#drawing_menu").is(":visible")){$(".menu_popup").hide()}
		else{$(".menu_popup").hide();$("#drawing_menu").toggle()};
	});
	$("#twitter").click(function(){
		if($("#twitter_feed").is(":visible")){$(".menu_popup").hide()}
		else{$(".menu_popup").hide();$("#twitter_feed").toggle()};
	});
});