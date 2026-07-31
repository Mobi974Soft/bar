<?php



?>

<html>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<head>
<!-- <script type="text/javascript" src="../../ZebraWebDriverJS/BrowserPrint.js"></script>-->
<script data-require="jquery@*" data-semver="3.2.1" src="https://cdn.jsdelivr.net/npm/jquery@3.2.1/dist/jquery.min.js"></script>
<script src="../Print/BrowserPrint-3.0.216.min.js"></script>
<script type="text/javascript">
var selected_device;
function setup()
{
	BrowserPrint.getDefaultDevice("printer", function(device)
			{
				selected_device = device;
				var ele = document.getElementById("selected_device");
				ele.innerHTML = device.name;
			}, function(error){
				alert(error);
			});

}
function writeToSelectedPrinter(dataToWrite)
{
	selected_device.send(dataToWrite, undefined, errorCallback);
}
var readCallback = function(readData) {
	if(readData === undefined || readData === null || readData === "")
	{
		alert("No Response from Device");
	}
	else
	{
		alert(readData);
	}
	
}
var errorCallback = function(errorMessage){
	alert("Error: " + errorMessage);	
}
function readFromSelectedPrinter()
{

	selected_device.read(readCallback, errorCallback);
	
}
function getDeviceCallback(deviceList)
{
	alert("Devices: \n" + JSON.stringify(deviceList, null, 4))
}
function selectDevice()
{
		
}
function sendImage(imageUrl)
{
	url = window.location.href.substring(0, window.location.href.lastIndexOf("/"));
	url = url + "/" + imageUrl;
	selected_device.sendUrl(url, undefined, errorCallback)
}
window.onload = setup;
</script>
</head>
<body>

<span style="padding-right:50px; font-size:200%">Zebra Browser Print Image Demo</span><br/>
Selected Device: <span id="selected_device"></span> <!--  <input type="button" value="Change" onclick="changeDevice();">--> <br/><br/> 

<input type="button" value="Send BMP" onclick="sendImage('Zebra_logobox.bmp');"><br/><br/>
<input type="button" value="Send JPG" onclick="writeToSelectedPrinter('^XA^PQ2^FO230,12^A0,40^FDBERKEYDZDZDZDZDZDZ^FS^FO240,105^BY2^BE,50,Y,N^FD4714622045252^FS^FO435,85^A0,125,65^^FH_^FD125.^FS^FO545,130^^A0,60^^FH_^FD00^FS^FO525,85^^A0,50^^FH_^FD _15^FS^XZ');"><br/><br/>


</body>
</html>