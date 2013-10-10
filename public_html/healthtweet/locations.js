$(document).ready(function(){

var select = document.getElementById("select");

var option = document.createElement("option");
option.value = "";
option.innerHTML = "Select a location";
select.appendChild(option);

var option = document.createElement("option");
option.value = "br";
option.innerHTML = "Brazil";
select.appendChild(option);

var option = document.createElement("option");
option.value = "ca";
option.innerHTML = "Canada";
select.appendChild(option);

var option = document.createElement("option");
option.value = "jp";
option.innerHTML = "Japan";
select.appendChild(option);

var option = document.createElement("option");
option.value = "mx";
option.innerHTML = "Mexico";
select.appendChild(option);

var option = document.createElement("option");
option.value = "us";
option.innerHTML = "United States";
select.appendChild(option);

// var option = document.createElement("option");
// option.value = "boston-heatmap.html";
// option.innerHTML = "Boston";
// select.appendChild(option);

// var option = document.createElement("option");
// option.value = "chicago-heatmap.html";
// option.innerHTML = "Chicago";
// select.appendChild(option);

// var option = document.createElement("option");
// option.value = "hawaii-heatmap.html";
// option.innerHTML = "Hawaii";
// select.appendChild(option);

// var option = document.createElement("option");
// option.value = "houston-heatmap.html";
// option.innerHTML = "Houston";
// select.appendChild(option);

// var option = document.createElement("option");
// option.value = "losangeles-heatmap.html";
// option.innerHTML = "Los Angeles";
// select.appendChild(option);

var option = document.createElement("option");
option.value = "nyc";
option.innerHTML = "NYC";
select.appendChild(option);

// var option = document.createElement("option");
// option.value = "saopaulo-heatmap.html";
// option.innerHTML = "Sao Paulo";
// select.appendChild(option);

// var option = document.createElement("option");
// option.value = "sydney-heatmap.html";
// option.innerHTML = "Sydney";
// select.appendChild(option);

});