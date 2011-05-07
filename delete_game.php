<?php

if (!isset($_GET['gameid'])) {
    die('Invalid gameid.');
}

require_once('libraries/library.php');
connect();

$gameid = mysql_real_escape_string($_GET['gameid']);

$query = 'DELETE FROM `face_usersgames` WHERE `game_id`='.$gameid;
$result = mysql_query($query);

$query = 'DELETE FROM `face_games` WHERE `id`='.$gameid;
$result = mysql_query($query);
?>
