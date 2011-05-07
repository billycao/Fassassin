<?php

// This is the index page. Are they actually logged into Facebook?
require_once("../app/fbsdk/secret.php");
require_once("../app/fbsdk/facebook.php"); 

$facebook = new Facebook(array(
  'appId'  => FB_APPID,
  'secret' => FB_SECRET,
  'cookie' => true,
));
$session = $facebook->getSession();
$me = null;
// Session based API call.
if ($session) {
  try {
    $uid = $facebook->getUser();
    $me = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
  }
}

if (!$me) {
    include_once("../app/static/notenoughperms.html");exit();
}

require_once('libraries/library.php');

if (!userExists($me['id'])) {
	createUser($me['id'], $me['name']);
}


if ($_GET['request_ids']) {
	$handler = "landing.php";
	$data = $_SERVER['QUERY_STRING'];
} else {
	$handler = "landing.php";
	$data = '';
}

// Do we still need their phone number?
$_data = new mysqli('localhost', 'hackathon', 'deneve', 'hackathon');
$checksql = $_data->query("select phone from face_users where facebook_id = '".$uid."' limit 0,1");

if ($checksql->num_rows !== 1) die("ERROR: User not in database");

$checkresult = $checksql->fetch_assoc();

if ($checkresult["phone"] == "") {
	$_errorpage = "no_phone_number.php";
	$data = '';
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
<link href='http://fonts.googleapis.com/css?family=PT+Sans:regular,italic,bold,bolditalic' rel='stylesheet' type='text/css' />
<link href='../app/static/styles.css' rel='stylesheet' type='text/css' />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js"></script>
<script type="text/javascript" src="http://konami-js.googlecode.com/svn/trunk/konami.js"></script>
<script type="text/javascript" src="../app/static/soundmanager.js"></script>
<script type="text/javascript" src="../app/static/scripts.js"></script>
<script src="https://connect.facebook.net/en_US/all.js"></script>
<script type="text/javascript">
	konami = new Konami()
	konami.code = function() {
		soundManager.play('loop');
	};
	konami.load();
	
var merry = setTimeout('', 2000);

$(function() {
	FB.init({ 
		appId:'208549769177114', cookie:true, 
		status:true, xfbml:true 
	 });
	FB.Event.subscribe('auth.login', function(response) {
		window.location.reload();
	});
});

function navNick(where, passchars, methalt) {
	clearTimeout(merry);
	$.ajax({
		url: './'+where,
		data: passchars,
		method: ((methalt) ? "GET" : methalt),
		error: function() {
			return false;
		},
		success: function(returned) {
			$('#booyah').html(returned);
			FB.Canvas.setSize();
			return true;
		}
	});
}



function checkGameName() {
	var game_name = $('#game_name').val();
	
	$.getJSON('create_game.php?game_name=' + game_name, function(data) {
		if (!data || data.exists) {
			$('#game_exists').html('<span style="color:#f00;">Game name already exists</span>');
		} else {
			$('#game_exists').html('<span style="color:green;">Game name OK!</span>');
		}
	});
}

function addmore(gameid, gamename) {
	FB.ui({
		method: 'apprequests', 
		message: 'I\'d like to challenge you in the Fassassin game "' + gamename + '"', 
		data: gameid
	}, function (response) {
			navNick('listgames.php');
	});
}

function submitNewGame() {
	$.post($('#create_form').attr('action'), $('#create_form').serialize(), function(response) {
		var data = $.parseJSON(response);
		
		if(data.game_id) {
			FB.ui({
					method: 'apprequests', 
					message: 'I\'d like to challenge you in the Fassassin game "' + data.game_name + '"', 
					data: data.game_id
				}, function (response) {
					if (!response) {
						$.get('./delete_game.php', 'gameid='+data.game_id, function() {
							$('#game_name').val('').focus();
							$('#game_exists').html('<span style="color:#666;">Game creation cancelled...</span>');
						});
					} else {
						// Game created, invites sent!
						navNick('listgames.php');
					}
				});
		} else {
			alert('Error');
		}
	});
}
</script>
</head><?php if ($_errorpage) { echo "<body>"; include_once($_errorpage); echo "</body></html>"; die(); } ?>
<body onload="navNick('<?=$handler; ?>', '<?=$data; ?>', 'GET');">
<div id="fb-root"></div>
<div class="header"><div style="float:left;font-size:1.3em;"><span class="hl ml" onclick="navNick('landing.php');">Fassassin</span> <span style="color:#5e6b83;">(315) ASS-ASSN</span></div><div style="float:right;"><a href="javascript:;" onclick="navNick('landing.php');">Home</a> &bull; <a href="javascript:;" onclick="navNick('faq.php');">FAQ &amp; Instructions</a> &bull; <a href="javascript:;" onclick="navNick('create.php');">Create New Game</a> &bull; <a href="javascript:;" onclick="navNick('listgames.php');">Your Games</a></div></div>
<div class="content"><div style="padding:10px;" id="booyah">Loading app, please be patient...</div></div>
</body>
</html>

