<?php

require_once('libraries/library.php');

if (!isset($_GET['userid']) || !isset($_GET['gameid'])) {
    die('Invalid userid or gameid.');
}
connect();
$userid = mysql_real_escape_string($_GET['userid']);
$gameid = mysql_real_escape_string($_GET['gameid']);

$query = 'UPDATE `face_usersgames` SET `admin`=1 WHERE `user_id`='.$userid.' AND `game_id`='.$gameid;
$result = mysql_query($query);
?>
