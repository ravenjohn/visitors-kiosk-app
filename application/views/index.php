<!DOCTYPE html>
<html>
	<head>
		<title>Kiosk Login</title>
		<style>
			html, body{
				width: 100%;
				height: 100%;
				margin: 0;
				padding: 0;
			}
			*{
				overflow: hidden;
			}
			#overlay{
				width: 150px;
				height: 100px;
				position: absolute;
				right: 50px;
				bottom: 0;
			}
		</style>
	</head>
	<body>
		<object width="100%" height="100%"
		classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000">
			<param name="SRC" value="/flash/Visitors_Kiosk.swf" />
			<embed src="/flash/Visitors_Kiosk.swf" width="100%" height="100%"></embed>
		</object>
		<div id="overlay" onclick="document.location='/admin'"></div>
	</body>
</html>