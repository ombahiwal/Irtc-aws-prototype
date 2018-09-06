 <?php
//API URL
$url = 'https://0hlla8ccid.execute-api.us-east-2.amazonaws.com/irtc_get_method/irtc-resource';
$ch = curl_init($url);
$data = json_decode(file_get_contents($url), true);
$arr = (array) json_decode($data["body"]);

//echo var_dump($data["body"]);
//$try =explode("location", $data["body"]);
//print_r($try);

curl_close($ch);
?>
<html>
    <meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="shortcut icon" type="image/x-icon" href="docs/images/favicon.ico" />
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js" integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA==" crossorigin=""></script>
    <style>
        body{font-family: helvetica;}
    </style>
    <body> <center>
    <div id="demo"></div>
        <h2>Driving Behaviour</h2>
        <div id="mapid" style="width: 75vw; height: 75vh;"></div>
        <br>
        <button onclick="reloadFunction();">Refresh Map</button><br><br><br>
        <div id="chartContainer" style="height: 300px; width: 75vw;"></div>
        <br>
        <div id="chartContainer2" style="height: 300px; width: 75vw;"></div>
        
        <div id="mapid" style="width: 75vw; height: 75vh;"></div>
        
        </center>
    </body>
      
<script>
    function goBack() {
    window.history.back();
}
    
    function reloadFunction(){
        location.reload();
    }
    
    
    // GET DATA FROM DynamoDB
var data = <?php echo $data["body"];?>;
//var obj = JSON.parse(str);
    
     
var mymap = L.map('mapid').setView([19.86515531,75.34508235], 15);
	L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1Ijoib21iYWhpd2FsIiwiYSI6ImNqbGc5c2t6ZjE1ODYza251amRibjBsY3kifQ.bIh0iKBf8Dhy6icjTen17A', {
		maxZoom: 18,
		attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
			'<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
			'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
		id: 'mapbox.streets'
	}).addTo(mymap);
    
    
    
    var my_lat = 19.857971;
    var my_long = 75.334360;
       var lat_long = [[],[], [], [], []];
    //var data = [];
    var meters = 10;
    var coef = 0.000008983 * meters;
    var new_lat = my_lat + coef;
    var new_long = my_long + coef / Math.cos(my_lat * 0.018);
    console.log(new_lat+", "+new_long);
    
    L.circle([new_lat, new_long], 10, {
		color: 'green',
		fillColor: '#f03',
		fillOpacity: 0.5
	}).addTo(mymap).bindPopup("<b>Dargah Chowk</b>");
    
    // Dargah Chowk 19.857971, 75.334360
    var ele = [];

    for(var i = 0; i < data.Count ; i++){
        // Add location Points on the map    
       // console.log(data["Items"][i]["location"]["S"]);
            ele = data["Items"][i]["location"]["S"].split(',');
           lat_long[0].push([parseFloat(ele[0]),parseFloat(ele[1]),new Date(data["Items"][i]["timestamp"]["S"]).getTime(), i ]);
// Time array
     //   lat_long[1].push(new Date(data["Items"][i]["timestamp"]["S"]).getTime());

        L.circle([parseFloat(ele[0]),parseFloat(ele[1])], 10, {
		color: 'red',
		fillColor: '#f03',
		fillOpacity: 0.5
	}).addTo(mymap).bindPopup("<b>"+ele[1]+"</b>");    
    }
    
    
     function Comparator(a, b) {
   if (a[2] < b[2]) return -1;
   if (a[2] > b[2]) return 1;
   return 0;
 }
    
    // Sorted Array of locations
 lat_long[0] = lat_long[0].sort(Comparator);
 console.log(lat_long[0][1]);
    

    
    
    // Function for calucating distance between two points
    
     function deg2rad(deg){
         return deg * (Math.PI/180)
     }

     function getDistanceFromLatLonInKm(lat1,lon1,lat2,lon2) {
  var R = 6371; // Radius of the earth in km
  var dLat = deg2rad(lat2-lat1);  // deg2rad below
  var dLon = deg2rad(lon2-lon1); 
  var a = 
    Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
    Math.sin(dLon/2) * Math.sin(dLon/2)
    ; 
  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
  var d = R * c; // Distance in km
  return d*1000; // Distance in M
}
    
    
    
//    calculate velocities in lat_long[1]
//     Initial velocity of 0 
    lat_long[1].push(0);
    
    var velocity = 0;
    var velocity_prev = 0;
    var acceleration = 0; 
    var distance = 0;
    var time_diff = 0;
    var convert_factor_m_km = 18/5;
    var graph_points = {};
 for(var i = 1; i< lat_long[0].length - 2; i++){
       time_diff = (lat_long[0][i+1][2] - lat_long[0][i][2])/1000;
     distance = getDistanceFromLatLonInKm(lat_long[0][i][0], lat_long[0][i][1], lat_long[0][i+1][0], lat_long[0][i+1][1]);
     //console.log(time_diff);
        
     //velocity in km
     velocity = convert_factor_m_km * (distance / time_diff);
     if(velocity < 50 && velocity != 0){
     lat_long[1].push(velocity);
         acceleration = (velocity - velocity_prev) / time_diff;
        lat_long[2].push(acceleration);
     }
     velocity_prev = velocity;
 }
    console.log(lat_long[1]);
    //console.log(getDistanceFromLatLonInKm(lat_long[0][2][0], lat_long[0][2][1], lat_long[0][3][0], lat_long[0][3][1]));
    
    var dataPoints = [];
    
    for(var i =0; i<lat_long[1].length-2; i++){
        dataPoints.push({x: new Date(lat_long[0][i][2]), y: lat_long[1][i]});
    }
    var average = lat_long[1].reduce((a, b) => a + b, 0)/ lat_long[1].length;
  
    window.onload = function () {

        var chart_object = {
	animationEnabled: true, 
    animationDuration: 5000,
	title:{
		text: "Velocity / Time"
	},
	axisY: {
		title: "Velocity",
		valueFormatString: "",
		suffix: "km/s",
        interval: 5,
        stripLines: [{
			value: average,
			label: "Average"
		}],
        minimum: 0,
        maximum: 60,
        interval: 10
	},
	data: [{
		yValueFormatString: "",
		xValueFormatString: "",
		type: "spline",
		dataPoints: dataPoints
	}]
};
        
        
var chart = new CanvasJS.Chart("chartContainer", chart_object);
chart.render();

        dataPoints = [];
for(var i =0; i<lat_long[1].length; i++){
        dataPoints.push({x: new Date(lat_long[0][i][2]), y: lat_long[2][i]});
    } 
    
     var chart_object = {
	animationEnabled: true, 
    animationDuration: 5000,
	title:{
		text: "Instantaneous Acceleration"
	},
	axisY: {
		title: "Accleration",
		valueFormatString: "",
		suffix: "km/s/s",
	},
	data: [{
		yValueFormatString: "",
		xValueFormatString: "",
		type: "spline",
		dataPoints: dataPoints
	}]
     };
    var chart = new CanvasJS.Chart("chartContainer2", chart_object);
chart.render();

    }
</script>
    
    </html>