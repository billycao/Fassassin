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



// This is the index page. Are they actually logged into Facebook?
require_once('libraries/library.php');

$cookie = get_facebook_cookie();

if ($cookie) {
	$user = get_user($cookie);

} 


// Do we still need their phone number?
$_data = new mysqli('localhost', 'hackathon', 'deneve', 'hackathon');
$checksql = $_data->query("select phone from face_users where facebook_id = '".$uid."' limit 0,1");

if ($checksql->num_rows !== 1) exit();

$checkresult = $checksql->fetch_assoc();

if ($checkresult["phone"] !== "") {
	echo "<script>clearTimeout('glorious');navNick('landing.php',window.location.search.substring(1));</script>";
	exit();
}

?>

<h2 class="blue">One more thing...</h2>

<p>This app requires your phone number in order for us to recognize who's getting killed, who's killing who, and where to text your next target. To confirm your phone number, we need you to text in:</p>
<p style="font-size:1.5em;text-align:center;"><span style="padding:7px 15px 7px 15px;background-color:#000;color:#fff;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;">Text <strong style="color:#ffee87;"><?php echo base_convert($uid,10,36); ?></strong> to <strong style="color:#ffee87;">(315) ASS-ASSN</strong></span></p>
<p>We <strong>strongly suggest</strong> that you save this number in your phone for easy future reference. That's (315) 277-2776. After we receive your text, this page will <strong>automatically reload</strong>.</p>
<p style="color:#4a5d95;font-weight:bold;">It'll take the server about 30 seconds to recognize your text. Just be patient, we promise this page will activate on its own ;)</p>

<script>
var glorious = setTimeout("navNick('no_phone_number.php')", 2000);
</script>