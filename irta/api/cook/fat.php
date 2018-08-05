<?php
	require "yt/Translator.php";
	require "yt/Translation.php";
	require "yt/TException.php";
	
	$translator = new Translator('trnsl.1.1.20180708T070340Z.a42043fd6fffcf8f.455f9977fdc7eaa1244ae0432cbc22becbcd606b');
	
	//Search query by GET method as srch
	$consumer_key = "85fc0ed320624b88a7ed26353e0bd3aa";
	$secret_key = "8b1e3604d22a4a34ac6cb49f2feefa1d";
	$bahanid = strtolower($_GET["srch"]);
	$bahantrans = $translator->translate($bahanid, 'id-en');
	$bahantrans = str_replace(" ", "%20", $bahantrans);

	$base = rawurlencode("GET")."&";
	$base .= "http%3A%2F%2Fplatform.fatsecret.com%2Frest%2Fserver.api&";
	
	//sort params by abc....necessary to build a correct unique signature
	$params = "format=json&";
	$params .= "max_results=1&";
	$params .= "method=foods.search&";
	$params .= "oauth_consumer_key=$consumer_key&"; // ur consumer key
	$params .= "oauth_nonce=".rand()."&";
	$params .= "oauth_signature_method=HMAC-SHA1&";
	$params .= "oauth_timestamp=".time()."&";
	$params .= "oauth_version=1.0&";
	$params .= "search_expression=".$bahantrans;

	$params2 = rawurlencode($params);
	$base .= $params2;

	$sig= base64_encode(hash_hmac('sha1', $base, "$secret_key&",
		true)); // replace xxx with Consumer Secret

	$url = "http://platform.fatsecret.com/rest/server.api?".
		$params."&oauth_signature=".rawurlencode($sig);

	//echo $url;
	list($output,$error,$info) = loadFoods($url);
	if($error == 0){
		if($info['http_code'] == '200'){
			$json = json_decode($output, true);
			$gizi = $json['foods']['food']['food_description'];
			$bahan = $json['foods']['food']['food_name'];
			$gizi = str_replace(" |", ",", $gizi);
			$giziIndo = $translator->translate($gizi, 'en-id');
			$bahanIndo = $translator->translate($bahan, 'en-id');
			$a = (string)$bahanIndo;
			$b = (string)$giziIndo;
			header('Content-Type: application/json');
			$hasil = array('nama' => $a, 'gizi' => $b);
			echo json_encode(array('data' => $hasil));
		}
		else
			die('Status INFO : '.$info['http_code']);
	}

	else
		die('Status ERROR : '.$error);
	
	function loadFoods($url)
	{
		// create curl resource
		$ch = curl_init();
		// set url
		curl_setopt($ch, CURLOPT_URL, $url);
		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// $output contains the output string
		$output = curl_exec($ch);
		$error = curl_error($ch);
		$info = curl_getinfo($ch);
		// close curl resource to free up system resources
		curl_close($ch);
		return array($output,$error,$info);
	}

?>