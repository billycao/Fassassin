<?php

error_reporting(00);

require_once('libraries/library.php');

$cookie = get_facebook_cookie();

if ($cookie) {
	$user = get_user($cookie);
	
	createUser($user->id, $user->name);
}


$_data = new mysqli('localhost', 'hackathon', 'deneve', 'hackathon');
$people = $_data->query("select facebook_id, name from face_users limit 0,50");
$moisql = mysql_query("select * from face_users where facebook_id = '".$user->id."'");
$moi = mysql_fetch_assoc($moisql);

// Get next target
$targetsql = $_data->query("select face_usersgames.*, face_games.name as gamename from face_usersgames, face_games where user_id = '".$user->id."' and dead = 0 and target_id > 0 and face_games.id = face_usersgames.game_id limit 0,1");
$target = $targetsql->fetch_assoc();

// Number of active games
$activesql = $_data->query("select user_id from face_usersgames where user_id = '".$user->id."' and target_id > 0");
$numactive = $activesql->num_rows;

$sleepsql = $_data->query("select user_id from face_usersgames where user_id = '".$user->id."' and target_id = 0");
$numsleep = $sleepsql->num_rows;

?>
	<script type="text/javascript">
		$(function() {
			FB.init({ 
				appId:'208549769177114', cookie:true, 
				status:true, xfbml:true 
			 });
			FB.Event.subscribe('auth.login', function(response) {
				window.location.reload();
			});
			
			<?php if ($_GET['request_ids']) { ?>
			$('#boom').show();
			$.getJSON('./request_handler.php', 'request_ids=<?=$_GET['request_ids']; ?>', function(ret) {
				var numadded = 0;
				$.each(ret, function(index, value) {
					numadded++;
				});
				if (numadded == 1) {
					$('#boom').html('<p>You have been added to a game.<br />Click this box to get to your games list.</p>').show();
				} else if (numadded > 1) {
					$('#boom').html('<p>You have been added to <strong>'+numadded+'</strong> games.<br />Click this box to get to your games list.</p>').show();
				} else {
					$('#boom').hide();
				}
			});
			<?php } ?>
		});
	</script>
	  <div id="fb-root"></div>
	  <div id="boom" style="display:none;margin-left:20px;margin-top:-20px;z-index:99;position:absolute;background-color:#ffdadc;color:#f00;border:1px solid #f00;-moz-border-radius:6px;-webkit-border-radius:6px;border-radius:6px;padding:3px 10px 3px 10px;width:300px;-moz-box-shadow: 0px 0px 5px #888;-webkit-box-shadow: 0px 0px 5px #888;box-shadow: 0px 0px 5px #888;" class="ml" onclick="navNick('listgames.php');">Wait...checking whether to add you to a game...</div>
	  <?php if ($cookie) { ?>
	  
	  <?php

		if ($targetsql->num_rows == "1") { 
		
		$targetnamesql = $_data->query("select name from face_users where facebook_id = '".$target["target_id"]."'");
		$tname = $targetnamesql->fetch_assoc();
		$targetname = $tname["name"];
		
		?><p style="text-align:center;"><span style="-moz-border-radius:6px;-webkit-borer-radius:6px;border-radius:6px;margin-left:auto;margin-right:auto;font-size:1.8em;color:#f00;background-color:#ffaaab;display:inline;padding:10px;height:50px;line-height:50px;">
	  Your mission: KILL <a href="http://www.facebook.com/profile.php?id=<?=$target["target_id"]; ?>" target="_blank" style="font-weight:bold;" class="red"><?=strtoupper($targetname); ?></a> <a href="http://www.facebook.com/profile.php?id=<?=$target["target_id"]; ?>" target="_blank" style="font-weight:bold;"><img src="https://graph.facebook.com/<?=$target["target_id"]; ?>/picture?access_token=<?=$cookie["access_token"]; ?>&type=square" style="vertical-align:middle;height:25px;width:25px;" /></a></span><br /><span style="font-size:1.1em;color:#f00;">by texting <strong><?=$target["gamename"]; ?></strong> from their phone to <strong>(315) ASS-ASSN</strong>.</span> <a href="javascript:;" onclick="navNick('faq.php');">Help me!</a></p><h2 class="blue" style="line-height:18px;float:left;display:inline;">You currently have <strong><?=$numactive; ?></strong> game(s) running and <strong><?=$numsleep; ?></strong> preparing to start.</h2>
	  <p style="height:1px;clear:both;">&nbsp;</p>
	  
	  <?php } else { ?>
	  <p style="text-align:center;"><span style="line-height:1.5em;font-size:1.5em;padding:7px 15px 7px 15px;background-color:#000;color:#fff;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;">Oh no, you have no active games! <a href="javascript:;" onclick="navNick('create.php');" class="dark" style="font-weight:bold;">Start a game!</a></span></p><p style="text-align:center;">(Or, ask an admin to begin a game)</p>
	  
	  <?php } ?>
	  <p style="clear:both;height:1px;">&nbsp;</p>
      <p style="width:45%;float:left;display:inline-block;"><strong>Welcome, <?= $user->first_name ?></strong>, to Fassassin! Laugh all you want at our...interesting...app name, but we're here to give you a whole new way to play Assassins.</p>
      <p style="width:45%;margin-left:10px;float:left;display:inline-block;">First, check if your friends have already <a href="http://www.facebook.com/?sk=apps&ap=1" target="_parent">invited you to a game</a> on the Facebook requests page. If not, click <a href="javascript:;" onclick="navNick('create.php');">Create New Game</a> to get started and invite YOUR friends to play.</p>
      <p style="clear:both;margin-bottom:15px;">&nbsp;</p>
	  <h3>Feed</h3>
	  <?php $msgs = readLogByUser($user->id); ?>
	  <ul>
	  <?php foreach ($msgs as $msg) { ?>
	    <li><?php echo $msg; ?></li>
	  <?php } ?>
	  </ul>
	  <div style="display:inline-block;width:350px;float:left;vertical-align:top;">
      <h2 class="blue">People &hearts; us!</h2>
      <p><?php
      while ($facepile = $people->fetch_assoc()) {
      printf('<img src="https://graph.facebook.com/%s/picture" alt="%s" title="%s"/>', $facepile["facebook_id"], $facepile["name"], $facepile["name"]); } ?> </p>
      </div>
      <div style="display:inline-block;width:350px;float:right;vertical-align:top;">
      <h2 class="blue">New phone number?</h2>
      <p>Don't worry, we got you covered. Using your <strong>new phone</strong>...</p>
      <p style="font-size:1.2em;text-align:center;"><span style="padding:5px 11px 5px 11px;background-color:#000;color:#fff;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;">Text <strong style="color:#ffee87;"><?php echo base_convert($user->id,10,36); ?></strong> to <strong style="color:#ffee87;">(315) ASS-ASSN</strong></span></p>
      <p>PS: We currently have your phone number as <strong><?=$moi["phone"]; ?></strong></p>
      </div>
    <?php } else { ?>
      <fb:login-button></fb:login-button>
    <?php } ?>
	</body>
 </html>