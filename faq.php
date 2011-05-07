<?php

require_once('libraries/library.php');

$cookie = get_facebook_cookie();

if ($cookie) {
	$user = get_user($cookie);
		
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$game_id = createGame($user->id, $_POST['game_name']);
	}
} else {
	header('location: ' . $_SERVER['SERVER_NAME']);
}

?>

<h2 class="blue">How this app works</h2>

<h3>Step 1: Create a Game</h3>
<p><blockquote>Hokay, the moment you click <strong>Create New Game</strong> up there at the top of the canvas, you'll be prompted for a game name. Type in something <em>unique</em> (don't worry, we'll notify you if someone's already taken that game name), and hit the Create button.</blockquote></p>
<p><blockquote>Once the game has been completed, you can select friends to join this game. Each friend will be given a notification and have a chance to respond.</blockquote></p>

<h3>Step 2: Start the Game</h3>
<p><blockquote>You can manage games you started and see games you participate in all under the <strong>Your Games</strong> page.</blockquote></p>
<ul>
	<li><strong>[Start Game]</strong>: Starts the game by randomizing target assignments to all players. Each player will receive a text to their phone telling them of their next target. Clicking on this while a game is running or after it has finished will <em>restart the game</em>. </li>
	<li><strong>[Delete]</strong>: Cancels the game and removes it from the database.</li>
	<li><strong>[Promote]</strong>: Gives a regular user administrative (Start/Delete) capabilities. Admin privileges cannot be removed at this time.</li>
</ul>
	

<h3>Step 3: Killing Someone</h3>
<p><blockquote>This is the really cool bit. To tag someone, simply get hold of their phone (we don't care if you punch them, steal it from their handbag, or blast Rebecca Black as torture until they surrender their device to you) and send a text containing the game name to <strong>(315) ASS-ASSN</strong> (that's 315-277-2776). They will receive a text back that they have been defeated, and you will receive a text on your cell containing your fresh killed victim's old target. Keep killing!</blockquote></blockquote></p>

<h3>Step 4: Moving On and Winning</h3>
<p><blockquote>A few minutes (and usually within 30 seconds) after &quot;killing&quot; your target, you will receive your new target by text to your phone. Go back to step 3, rinse, and repeat, until you win. When each competitor is eliminated, their name will be <span style="text-decoration:line-through;">crossed out</span> to indicate they are out of the game.</blockquote></p>
<p><blockquote>When all players but one have been eliminated, the winner is crowned via a mass text to all participants that the game has ended.</blockquote></p>

<h3>Just remember...</h3>
<p style="font-size:1.5em;text-align:center;"><span style="padding:7px 15px 7px 15px;background-color:#000;color:#fff;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;">Text <strong style="color:#ffee87;">Game Name</strong> to <strong style="color:#ffee87;">(315) ASS-ASSN</strong></span></p>
