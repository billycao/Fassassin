<?php

include 'library.php';

$cookie = get_facebook_cookie();

if ($cookie) {
	$user = get_user($cookie);
	
	echo $cookie['access_token'];
}