<?php

include_once 'library.php';

$cookie = get_facebook_cookie();

if ($cookie) {
	$user = get_user($cookie);
	
	createUser($user->id, $user->name);
}

$msgs = readLogByUser($user->id);

?>
<ul>
<?php foreach ($msgs as $msg) { ?>
<li><?php echo $msg; ?></li>
<?php } ?>
</ul>