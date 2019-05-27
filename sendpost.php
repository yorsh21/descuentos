<!DOCTYPE HTML>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	<h1>hola mundo</h1>

	<?php

	$url = 'http://server.com/path';
	$data = array('key1' => 'value1', 'key2' => 'value2');

	// use key 'http' even if you send the request to https://...
	$options = array(
	    'http' => array(
	        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
	        'method'  => 'POST',
	        'content' => http_build_query($data)
	    )
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if ($result === FALSE) { /* Handle error */ }

	var_dump($result);

	?>

	<script>
		const Http = new XMLHttpRequest();
		const url='http://localhost/descuentos/api';
		Http.open("POST", url);
		Http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		Http.send("fname=Henry&lname=Ford");
		Http.onreadystatechange=(e) => {
			console.log(Http.responseText)
		}
	</script>
</body>
</html>