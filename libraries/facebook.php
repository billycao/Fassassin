<?php

define('YOUR_APP_ID', '208549769177114');
define('YOUR_APP_SECRET', 'a55d5b276869f4f2f625fa8edc7b63a1');

function get_facebook_cookie($app_id=YOUR_APP_ID, $app_secret=YOUR_APP_SECRET) {
  $args = array();
  parse_str(trim($_COOKIE['fbs_' . $app_id], '\\"'), $args);
  ksort($args);
  $payload = '';
  foreach ($args as $key => $value) {
    if ($key != 'sig') {
      $payload .= $key . '=' . $value;
    }
  }
  if (md5($payload . $app_secret) != $args['sig']) {
    return null;
  }
  return $args;
}

function get_user($cookie) {
	$user = json_decode(file_get_contents(
		'https://graph.facebook.com/me?access_token=' .
		$cookie['access_token']));
	return $user;
}

?>