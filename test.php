<?php

require_once('libraries/library.php');

$cookie = get_facebook_cookie();

if ($cookie) {
    $user = get_user($cookie);
} else {
    // User is not logged in
    die('You are not logged in.');
}

connect();

$return = killUser('+16266232953','asdfasdf');
print_r($return);
?>
