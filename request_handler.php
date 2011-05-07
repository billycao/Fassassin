<?php
error_reporting(00);

require_once('libraries/library.php');

$cookie = get_facebook_cookie();
$_output = array();

if ($cookie) {
	$user = get_user($cookie);
	
	$requests = explode(',', $_GET['request_ids']);
	
	foreach($requests as $request_id) {
		$url = 'https://graph.facebook.com/' . $request_id . '?access_token=' . $cookie['access_token'];
		
		try {
			$request = json_decode(file_get_contents($url));
		} catch (Exception $e) {
		
		}
		
		// Does it exist?
		if ($request) {
			
			$result = addUserToGame($request->to->id, $request->data);
			
			if($result) {
				array_push($_output, array(
					"game"	=>	$request->data,
					"uid"	=>	$request->to->id
				));
			}
	
			// create a new cURL resource
			$ch = curl_init();
	
			// set URL and other appropriate options
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_NOBODY, 1);    
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
	
			// grab URL and pass it to the browser
			curl_exec($ch);
	
			// close cURL resource, and free up system resources
			curl_close($ch); 
		}
		
	}
	
	die(json_encode($_output));
	
}

?>