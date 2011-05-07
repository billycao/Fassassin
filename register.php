<?php

function registerUser($fbid, $name) {
	$link = mysql_connect('localhost', 'hackathon', 'deneve');
	if (!$link) {
		die('Could not connect: ' . mysql_error());
	}

	$db_selected = mysql_select_db('hackathon', $link);
	if (!$db_selected) {
		die ('Can\'t use foo : ' . mysql_error());
	}

	$esc_fbid = mysql_real_escape_string($fbid);
	$esc_name = mysql_real_escape_string($name);
	
	$checkQ = 'SELECT `id` FROM `face_users` WHERE `facebook_id` = ' . $esc_fbid;
	$checkResult = mysql_query($checkQ);
	if (!$checkResult) {
		die ('Query error : ' . mysql_error());
	}
	if (mysql_num_rows($checkResult) > 0) {
		return FALSE;
	}
	
	$insertQ = 'INSERT INTO `face_users` (`facebook_id`, `name`) VALUES ("' . 
						$esc_fbid . '", "' . $esc_name . '")';
	$insertResult = mysql_query($insertQ);
	
	if (!$insertResult) {
		die ('Query error : ' . mysql_error());
	}

	mysql_close($link);
	
	return TRUE;
}

