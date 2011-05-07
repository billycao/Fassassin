<?php

require_once('libraries/library.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$cookie = get_facebook_cookie();

	if ($cookie) {
		$user = get_user($cookie);
		

		$game_id = createGame($_POST['game_name'], $user->id);
		if ($game_id) {
			echo '{"game_id": ' . $game_id . ', "game_name": "' . strip_tags($_POST['game_name']) . '"}';
		} else {
			echo '{"game_id": false}';
		}
	} else {
		header('location: ' . $_SERVER['SERVER_NAME']);
	}
} else {
	$exists = gameExists($_GET['game_name']);
	if ($exists) {
		echo '{"exists": true}';
	} else {
		echo '{"exists": false}';
	}
}

?>