<?php
	require "yt/Translator.php";
	require "yt/Translation.php";
	require "yt/TException.php";
	
	$translator = new Translator('trnsl.1.1.20180708T070340Z.a42043fd6fffcf8f.455f9977fdc7eaa1244ae0432cbc22becbcd606b');
	
	//Search query by GET method as srch
	$name = strtolower($_GET["name"]);

	$ch = curl_init();

	// Set query data here with the URL
	curl_setopt($ch, CURLOPT_URL, 'https://api.genderize.io/?name=' . $name); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3);
	$content = trim(curl_exec($ch));
	curl_close($ch);
	$json = json_decode($content, true);
	$gender = $json['gender'];
	if($gender == NULL){
		$gender = 'male';
	}
	$genderIndo = $translator->translate($gender, 'en-id');
	$a = (string)$genderIndo;
	header('Content-Type: application/json');
	$hasil = array('gender' => $a);
	echo json_encode(array('data' => $hasil));
?>