<?php

$db_connection = FALSE;

function connect() {
	if ($db_connection) {
		return;
	}
	
	$db_connection = mysql_connect('localhost', 'hackathon', 'deneve');
	if (!$db_connection) {
		die('Could not connect: ' . mysql_error());
	}

	$db_selected = mysql_select_db('hackathon', $db_connection);
	if (!$db_selected) {
		die ('Can\'t use foo : ' . mysql_error());
	}
}

function addLog($game_id, $message) {
	connect();
	
	$esc_gid = mysql_real_escape_string($game_id);
	$esc_msg = strip_tags(mysql_real_escape_string($message));
	
	$insertQ = "INSERT INTO `face_events` (`game_id`, `message`, `timestamp`) VALUES ('" . $esc_gid . "', '" . $esc_msg . "', NOW())";
	$insertResult = mysql_query($insertQ);
	if (!$insertResult) {
		die ('Query error : ' . mysql_error());
	}
}

function readLogByGame($game_id) {
	connect();
	
	$esc_gid = mysql_real_escape_string($game_id);
	$selectQ = "SELECT * FROM `face_events` WHERE `game_id`=" . $esc_gid . ' AND `timestamp`> NOW( )-3600*24*7 ORDER BY `timestamp` DESC LIMIT 10';
	$selectResult = mysql_query($insertQ);
	if (!$selectResult) {
		die ('Query error : ' . mysql_error());
	}
}

function readLogByUser($fbid) {
	connect();

	$esc_fbid = mysql_real_escape_string($fbid);
	$gamesQ = mysql_query('SELECT `game_id` FROM `face_usersgames` WHERE `user_id`=' . $esc_fbid);
	$ids = array();
	while ($row = mysql_fetch_row($gamesQ)) {
		$ids[] = $row[0];
	}

	$msgs = array();
	$eventsQ = mysql_query('SELECT * FROM `face_events` WHERE `game_id` IN (' . implode($ids, ',') . ') AND `timestamp`> NOW( )-3600*24*7 ORDER BY `timestamp` DESC LIMIT 10');
	while ($row = mysql_fetch_assoc($eventsQ)) {
		$msgs[] = $row['message'];
	}
	
	return $msgs;
}

?>