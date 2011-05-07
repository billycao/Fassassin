<?php

require_once('libraries/facebook.php');
require_once('libraries/database.php');

function userExists($fbid) {
	connect();

	$esc_fbid = mysql_real_escape_string($fbid);
	
	$checkQ = 'SELECT `id` FROM `face_users` WHERE `facebook_id` = ' . $esc_fbid;
	$checkResult = mysql_query($checkQ);
	if (!$checkResult) {
		echo $checkQ;
		die ('Query error : ' . mysql_error());
	}
	
	return mysql_num_rows($checkResult) > 0;
}

function createUser($fbid, $name) {
	connect();

	$esc_fbid = mysql_real_escape_string($fbid);
	$esc_name = mysql_real_escape_string($name);
	
	if (userExists($fbid)) {
		return FALSE;
	}
	
	$insertQ = 'INSERT INTO `face_users` (`facebook_id`, `name`) VALUES ("' . 
						$esc_fbid . '", "' . $esc_name . '")';
	$insertResult = mysql_query($insertQ);
	if (!$insertResult) {
		die ('Query error : ' . mysql_error());
	}

	return TRUE;
}

function gameExists($game_name) {
	connect();

	$esc_name = mysql_real_escape_string($game_name);
	
	$checkQ = 'SELECT `id` FROM `face_games` WHERE `name` = "' . $esc_name . '"';
	$checkResult = mysql_query($checkQ);
	if (!$checkResult) {
		die ('Query error : ' . mysql_error());
	}
	
	return mysql_num_rows($checkResult) > 0;
}

function gameExistsById($gameid) {
	connect();

	$esc_gid = mysql_real_escape_string($gameid);
	
	$checkQ = 'SELECT `id` FROM `face_games` WHERE `id` = "' . $esc_gid . '"';
	$checkResult = mysql_query($checkQ);
	if (!$checkResult) {
		die ('Query error : ' . mysql_error());
	}
	
	return mysql_num_rows($checkResult) > 0;
}

function createGame($game_name, $creator_id) {
	if (!isset($game_name) || $game_name === "") {
		return FALSE;
	}
	
	connect();
	
	if (gameExists($game_name)) {
		return FALSE;
	}
	
	$esc_gname = mysql_real_escape_string($game_name);
	$esc_creator = mysql_real_escape_string($creator_id);
	
	$insertQ = 'INSERT INTO `face_games` (`name`, `creator_id`) VALUES ("' . 
						$esc_gname . '", "' . $esc_creator . '")';
	$insertResult = mysql_query($insertQ);
	
	if (!$insertResult) {
		die ('Query error : ' . mysql_error());
	}
	
	$gameid = mysql_insert_id();
	addUserToGame($creator_id, $gameid, 1);
	
	return $gameid;
}

function userInGame($fbid, $gameid) {
	connect();

	$esc_fbid = mysql_real_escape_string($fbid);
	$esc_gid = mysql_real_escape_string($gameid);
	
	$checkQ = 'SELECT `id` FROM `face_usersgames` WHERE `user_id` = "' . $esc_fbid . '" AND `game_id` = "' . $esc_gid . '"';
	$checkResult = mysql_query($checkQ);
	if (!$checkResult) {
		die ('Query error : ' . mysql_error());
	}
	
	return mysql_num_rows($checkResult) > 0;
}

function addUserToGame($fbid, $gameid, $admin=0) {
	connect();
	
	if (!isset($fbid) || !userExists($fbid)) {
		return FALSE;
	}
	
	if (!isset($gameid) || !gameExistsById($gameid)) {
		return FALSE;
	}
	
	if (userInGame($fbid, $gameid)) {
		return FALSE;
	}
	
	$esc_fbid = mysql_real_escape_string($fbid);
	$esc_gid = mysql_real_escape_string($gameid);
	$esc_admin = mysql_real_escape_string($admin);
	
	$insertQ = 'INSERT INTO `face_usersgames` (`user_id`, `game_id`, `admin`) VALUES ("' . 
						$esc_fbid . '", "' . $esc_gid . '", ' . $admin . ')';
	$insertResult = mysql_query($insertQ);
	
	if (!$insertResult) {
		die ('Query error : ' . mysql_error());
	}
	
	return TRUE;
}

function updatePhoneNumber($fbid, $phone) {
	connect();
	
	$esc_fbid = mysql_real_escape_string($fbid);
	$esc_phone = mysql_real_escape_string($phone);
	
	$insertQ = 'UPDATE `face_users` SET `phone`="' . $phone . '" WHERE `facebook_id`=' . $fbid;
	$insertResult = mysql_query($insertQ);
	if (!$insertResult) {
		die ('Query error : ' . mysql_error());
	}
	
	return TRUE;
}

function lookupPhone($phone_num) {
	connect();
	
	$esc_phone = mysql_real_escape_string($phone_num);
	
	$lookupQ = 'SELECT * FROM `face_users` WHERE `phone`=' . $esc_phone;
	$lookupResult = mysql_query($lookupQ);
	if (!$lookupResult) {
		return FALSE;
	} else {
		return mysql_fetch_assoc($lookupResult);
	}
}

function getUsersInGame($gameid) {
	connect();
	
	$esc_gid = mysql_real_escape_string($gameid);
	
	$selectQ = "SELECT * FROM `face_usersgames` INNER JOIN `face_users` ON `user_id`=`facebook_id` WHERE `game_id`=" . $esc_gid;
	$selectResult = mysql_query($selectQ);

	$players = array();
	while ($player = mysql_fetch_assoc($selectResult)) {
		$players[$player['facebook_id']] = $player;
	}
	
	return $players;
}

function assign_targets($gameid, $newgame_get) {
	$victory = false;

	// Connect to mysql
	connect();

	$query = "SELECT * FROM `face_usersgames` WHERE `game_id` = ".$gameid;
	$result = mysql_query($query);
	
	$newgame = true;
	$players = array();
	while ($player = mysql_fetch_assoc($result)) {
		$players[] = $player['user_id'];
		if ($player['target_id'] != 0) {
			$newgame = false;
		}
	}

	$count = 0;
	if ($newgame || $newgame_get == 'true') {
		if (sizeof($players) < 2) {
			die("You must have at least two players to play.");
		}
		
		// New game, randomly assign targets
		shuffle($players); reset($players);
		$targets = $players;
		foreach ($targets as $player_id) {
			$next = next($players);
			if ($next === false) {
				$next = reset($players);
			}
			$query = "UPDATE `face_usersgames` SET `target_id`=".$next." WHERE `user_id`=".$player_id." AND `game_id`=".$gameid;
			$result = mysql_query($query);
		}
		// Make everyone not dead
		$query = "UPDATE `face_usersgames` SET `dead`=0 WHERE `game_id`=".$gameid;
		$result = mysql_query($query);
	} else {
		// Handle when people die
		$query = "SELECT * FROM `face_usersgames` WHERE `game_id` = ".$gameid;
		$result = mysql_query($query);
		while ($player = mysql_fetch_assoc($result)) {
			if ($player['dead'] == 1) {
					// Make dead assassin's target the dead person's target
					$query = "UPDATE `face_usersgames` SET `target_id`=".$player['target_id']." WHERE `game_id`=".$gameid." AND `target_id`=".$player['user_id'];
					mysql_query($query);
					// Make dead person's target -1
					$query = "UPDATE `face_usersgames` SET `target_id`=-1,`dead`=2 WHERE `game_id`=".$gameid." AND `user_id`=".$player['user_id'];
					mysql_query($query);		
			}
		}
		// If there is only one person alive, declare him the winner
		$query = "SELECT * FROM `face_usersgames` WHERE `game_id`=".$gameid." AND `dead`=0";
		$result = mysql_query($query);
		if (mysql_num_rows($result) < 2) {
			// Set dead = 3 to declare him the winner
			$query = "UPDATE `face_usersgames` SET target_id=-2 WHERE `game_id`=".$gameid." AND `dead`=0";
			mysql_query($query);
			$victory = true;
		}
	}
	return $victory;
}

function killUser($phone_num, $game_name) {
	$return = array(
		'killer'=>'', 
		'newtarget'=>'',
		'victory'=>false,
	);
	connect();
	
	// Get gameid of game with $game_name
	$query = "SELECT * FROM `face_games` WHERE `name`='".mysql_real_escape_string(trim($game_name))."'";
	$result = mysql_query($query);
	$game = mysql_fetch_assoc($result);
	$gameid = $game['id'];
	if (!$gameid) {
		return false;
	}
	
	// Get the victim
	$victim = lookupPhone($phone_num);
	$victimid = $victim['facebook_id'];
	
	// Kill the player of the gameid
	$query = "UPDATE `face_usersgames` SET dead=1 WHERE `user_id`=".$victimid." AND `game_id`=".$gameid;
	$result = mysql_query($query);
	
	// Get the new target (dead guy's old target)
	$query = "SELECT * FROM `face_usersgames` INNER JOIN `face_users` ON `facebook_id`=`target_id` " . 
			 "WHERE `user_id`=".$victimid." AND `game_id`=".$gameid;
	$result = mysql_query($query);
	if ($result) {
		$return['newtarget'] = mysql_fetch_assoc($result);
	} else {
		echo 'MySQL Error: '.mysql_error();
		return false;
	}
	
	// Get the killer
	$query = "SELECT * FROM `face_usersgames` INNER JOIN `face_users` ON `facebook_id`=`user_id` " .
			 "WHERE `target_id`=".$victimid." AND `game_id`=".$gameid;
	$result = mysql_query($query);
	if ($result) {
		$return['killer'] = mysql_fetch_assoc($result);
	} else {
		echo 'MySQL Error: '.mysql_error();
		return false;
	}
	
	$verbs = array('ninja\'d', 'sliced', 'annihilated', 'destroyed', 'humiliated', 'falcon punch\'d', 
		'pummeled', 'shuriken\'d', 'nunchuck\'d', 'assassinated', 'struck');
	shuffle($verbs);
	$msg = $return['killer']['name'] . ' ' . array_pop($verbs) . ' ' . $victim['name'];
	addLog($gameid, $msg);
	
	// Process dead guy
	$return['victory'] = assign_targets($gameid, 'false');
	return $return;
}

?>