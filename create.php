<?php

require_once('libraries/library.php');

$cookie = get_facebook_cookie();

if ($cookie) {
	$user = get_user($cookie);
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$game_id = createGame($user->id, $_POST['game_name']);
	}
} 

?>
<script type="text/javascript">
$('#create_form').ready(function() {
	$('#game_name').focus();
});
</script>

<form id="create_form" action="create_game.php" onsubmit="return false;">
<h2 class="blue">Create New Game</h2>
<p><strong>Yes!</strong> You are awesome for making a new game. It's really simple: type in a name for your game. We'll update you on whether the game name has already been taken. Game names must be unique so you can text in and we can properly understand who to kill. Then, select friends you'd like to play with. They will all get invites to the game via Facebook notifications. Easy, no?</p>
<p><strong>The maximum game name length is 20 characters</strong>. You can use spaces, letters and numbers. There's no cap on users, so invite all your friends!</p>
	<label for="game_name" style="font-weight:bold;">Game Name:</label>
	<input type="text" id="game_name" name="game_name" onkeyup="checkGameName();" maxlength="20" />
	<input name="submit" type="button" onclick="submitNewGame();" value="Create Game" />
	<p>		<span id="game_exists" style="font-weight:bold;"></span></p>
</form>
