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
        <button onclick="reloadFunction();">Refresh Map</button>
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
    
    L.circle([19.857971, 75.334360], 10, {
		color: 'green',
		fillColor: '#f03',
		fillOpacity: 0.5
	}).addTo(mymap).bindPopup("<b>Dargah Chowk</b>");
   
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
        L.circle([parseFloat(ele[0]),parseFloat(ele[1])], 10, {
		color: 'red',
		fillColor: '#f03',
		fillOpacity: 0.5
	}).addTo(mymap).bindPopup("<b>"+ele[1]+"</b>");
        
    }
</script>
    
    </html>