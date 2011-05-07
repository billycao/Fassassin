<?php

require_once('libraries/library.php');

$cookie = get_facebook_cookie();

if ($cookie) {
    $user = get_user($cookie);
} else {
    // User is not logged in
    die('You are not logged in.');
}

// Connect to mysql
connect();
?>
<!doctype html>
<html>
<head>
    <title>List Games</title>
    <style type="text/css">
    	.status {
    		padding: 4px 8px 4px 8px;
    		-moz-border-radius: 3px;
    		-webkit-border-radius: 3px;
    		border-radius: 3px;
    		margin-left: 8px;
    		background-color: #f3e7eb;
    	}
        .dead {
            text-decoration: line-through;
            color: #50555b;
        }
        .target {
            background-color: red;
            color: #fff;
        }
        .died {
            font-weight: bold;
            color: red;
        }
        .won {
            background-color: green;
            color: white;
        }
        .game-title {
            background: #d8ebff;
            border-radius: 5px;
            font-weight: bold;
            display: block;
            width: 720px;
            height: 20px;
            padding: 5px 10px;
            margin: 5px 0px;
            list-style-type: none;
            position: relative;           
        }
        .options {
            float: right;
        }
        .game-status {
        	left: 175px;
            position: absolute;
            font-weight: normal;
        }
        .owner {
        	font-weight: bold;
        	font-style: italic;
        }
    </style>
    
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js"></script>

</head>
<body>
    <h2 class="blue">Your Games</h2>
    <p>Legend: <span class="status target">Your Target</span> <span class="status dead">Eliminated</span> <span class="status won">Winner</span> <span class="status owner">Game Admin</span></p><p>&nbsp;</p>
<?php
$query = "SELECT * FROM `face_usersgames` WHERE `user_id` = ".$user->id;
$result = mysql_query($query);
if ($result) {
    // For each game,
    while ($row = mysql_fetch_assoc($result)) {
        // Get game name
        $query = "SELECT * FROM `face_games` WHERE `id` = ".$row['game_id'];
        $game = mysql_fetch_assoc(mysql_query($query));
        $target_id = $row['target_id'];
        
        // Admin options
        $options = '<span class="options">';
        if ($target_id == 0) {
        	$options .= " <a href='javascript:;' onclick='addmore(\"".$row['game_id']."\", \"".$row['name']."\");' id='add-".$row['game_id']."'>[Add More Players]</a>&nbsp;&nbsp;";
        }
        if ($row['admin'] == 1) {
            $options .= " <a href='#' onclick='newgame(".$row['game_id'].");' id='start-".$row['game_id']."'>[".(($target_id == 0) ? "Start Game" : "Restart Game" )."]</a>&nbsp;&nbsp;";
            $options .= " <a href='#' onclick='deletegame(".$row['game_id'].");' id='del-".$row['game_id']."'>[Delete]</a>";
        }
        $options .= '</span>';
            // Get users in this game
            $query = "SELECT `face_usersgames`.*, `face_users`.`name` as name FROM `face_usersgames`, `face_users` WHERE `face_usersgames`.`user_id` = `face_users`.`facebook_id` AND `game_id` = ".$row['game_id']." order by name ASC";
            $games = mysql_query($query);
            $game_status = '&nbsp;&nbsp;&nbsp;';
            if ($target_id == 0) {
                $game_status .= '<span class="game-status"><em>This game has not yet started</em></span>';
            } else if ($target_id == -1) {
                $game_status .=  '<span class="game-status died">You have been eliminated!</span>';
            } else if ($target_id == -2) {
                $game_status .=  '<span class="game-status"><span class="won">Congratulations! You won this game!</span> The game has ended.</span>';
            } else {
                $game_status .=  '<span class="game-status"><em>Game in progress...</em></span>';
            }
            
        echo '<p class="game-title" style="line-height:18px;"><span style="font-size:16px;">'.$game['name'].'</span> '.$game_status.$options.'</p>';
        echo '<p style="margin-bottom:25px;">';
            while ($player = mysql_fetch_assoc($games)) {
                $query = "SELECT * FROM `face_users` WHERE `facebook_id` = ".$player['user_id']." ORDER BY name DESC";
                $user = mysql_fetch_assoc(mysql_query($query));
                
                $class = ''; $status = ''; $prepend = '';
                if ($user['facebook_id'] == $target_id) {
                    $class = 'target';
                } else if ($player['dead'] == 1 || $player['dead'] == 2) {
                    $class = 'dead';
                } else if ($player['target_id'] == -2) {
                    $class = 'won';
                } 
                if ($player['admin'] == 1) {
                    $class .= " owner";
                } else if ($row['admin'] == 1) {
                    $prepend .= ' <a href="#" onclick="promote('.$player['user_id'].','.$player['game_id'].')">[Promote]</a>';
                }
                echo '<span class="status '.$class.'"><span class="ml" onclick="window.open(\'http://www.facebook.com/profile.php?id='.$player['user_id'].'\');"><img src="https://graph.facebook.com/'.$player['user_id'].'/picture?type=small&access_token='.$cookie["access_token"].'" style="width:16px;height:16px;vertical-align:middle;" /> '.$status.$user['name'].'</span>'.$prepend.'</span>';
            }
        echo '</p>';
    }
    echo '</ul>';
} else {
    die('Mysql error: '.mysql_error());
}
?>
    
<script type="text/javascript">
function newgame(gameid) {
	$('#start-'+gameid).html('Launching...');
    $.ajax({
       type: "GET",
       url: 'http://hack.yectep.com/nick/assign_targets.php',
       data: 'gameid='+gameid+'&newgame=true',
       success: function(msg) {
           if (msg !== "")
           navNick('listgames.php');
       }
    });
}
function promote(userid, gameid) {
    $.ajax({
       type: "GET",
       url: 'http://hack.yectep.com/nick/promote.php',
       data: 'gameid='+gameid+'&userid='+userid,
       success: function(msg) {
           navNick('listgames.php');
       }
    });
}
function deletegame(gameid) {
	$('#del-'+gameid).html('Removing...');
    $.ajax({
       type: "GET",
       url: 'http://hack.yectep.com/nick/delete_game.php',
       data: 'gameid='+gameid,
       success: function(msg) {
           navNick('listgames.php');
       }
    });
}
$(function() {
	merry = setTimeout("navNick('listgames.php')", 5000);
})
</script>

</body>