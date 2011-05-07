<?php

require_once('libraries/library.php');
require_once('libraries/googlevoice.php');
require_once('/home/webby/fbhackathon/credentials.php');
$gv = new GoogleVoice(VOICE_USER, VOICE_PASS);

// get all new SMSs
$sms = $gv->getNewSMS();
$msgIDs = array();
foreach (array_reverse($sms) as $s) {
	
	if (!in_array($s['msgID'], $msgIDs)) {
		$possible_fbid = base_convert(strtolower(trim($s['message'])), 36, 10);
		if(userExists($possible_fbid)) {
			updatePhoneNumber($possible_fbid, $s['phoneNumber']);
			$gv->sendSMS(substr($s['phoneNumber'], 2), "Thanks for activating. Your account now reflects this mobile number.");
			echo 'Updated ' . $s['message'] . ' number with ' . $s['phoneNumber'];
		} else if($s['message'] !== "Error: this message was not successfully delivered.") {
			print_r($s);
			
			$dead_number = $s['phoneNumber'];
			$game_name = $s['message'];
			
			$result = killUser($dead_number, $game_name);
			
			if ($result)  {
				$killer = substr($result['killer']['phone'], 2);
				$new_target = $result['newtarget']['name'];
				$msgs = "";
				
				if ($result['victory']) {
					$msgs = "Congratulations, master assassin! You're the winner of " . $game_name . "!";
				} else {
					$msgs = "Congratulations, assassin! Your new target is " . $new_target . ". Good luck!";
				}
				
				// send killer new target
				$gv->sendSMS($killer, $msgs);
				echo $msgs;
			}
		}
		
		// mark the conversation as "read" in google voice
		$gv->markSMSRead($s['msgID']);
		$msgIDs[] = $s['msgID'];
	}
}

?>