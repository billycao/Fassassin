<?php


require_once('libraries/library.php');
// Get variables:
// $gameid = ID of game you want to assign targets on
// $newgame = 'true' // If you want to reassign targets of this game
// $

if (isset($_GET['gameid']) && is_numeric($_GET['gameid'])) {
    $gameid = $_GET['gameid'];
    connect();
    $gnamesql = mysql_query("select `name` from `face_games` where `id` = ".$gameid);
    $gname = mysql_result($gnamesql, 0);
} else {
    die("gameid not specified.");
}

if ($_GET['newgame'] == 'true') {
    $newgame = true;
} else {
    $newgame = false;
}
$return = assign_targets($gameid, $newgame);
print_r($return);

require_once('libraries/googlevoice.php');

$gv = new GoogleVoice('fbhack.fassassin', 'facebookhackathon');
$players = getUsersInGame($gameid);
print_r($players);
foreach ($players as $player) {
	$msg = "Your target for the game \"".$gname."\" is " . $players[$player['target_id']]['name'] . ". Let the games begin!"; 
	$gv->sendSMS(substr($player['phone'], 2), $msg);
}
?>