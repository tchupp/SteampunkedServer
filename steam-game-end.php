<?php
/*
 * Steampunked app end the game
 */
require_once "db.inc.php";
require_once "auth.inc.php";
require_once "gcm.inc.php";
echo '<?xml version="1.0" encoding="UTF-8" ?>';

if (!isset($_GET['game']) || !isset($_GET['won'])) {
    echo '<steam status="no" msg="malformed params"/>';
    exit;
}
if (!isset($_SERVER['HTTP_AUTHUSER']) || !isset($_SERVER["HTTP_AUTHTOKEN"])) {
    echo '<steam status="no" msg="malformed auth header"/>';
    exit;
}

process($_SERVER['HTTP_AUTHUSER'], $_SERVER['HTTP_AUTHTOKEN'], $_GET['game'], $_GET['won']);

function process($user, $authToken, $game, $won) {
    $pdo = pdo_connect();

    if (!authenticate($pdo, $user, $authToken)) {
        echo '<steam status="no" msg="auth failed"/>';
        exit;
    }

    $gameId = $pdo->quote($game);

    $pdo->beginTransaction();

    $query = "UPDATE steampunked_game_info, steampunked_game_status
              SET game_status = name
              WHERE game_id=$gameId
              AND name LIKE '%FINISHED%'";

    $result = $pdo->query($query);

    if (!$result) {
        $pdo->rollBack();

        echo "<steam status=\"no\" msg='error updating game info' />";
        exit;
    }

    $pdo->commit();

    $deviceToken = getOpponentDeviceToken($pdo, $user, $game);
    $winner = $won == 'true' ? $user : getOpponentUser($pdo, $user, $game);

    sendGCM($deviceToken, endGameKey(), "GAME OVER", $winner);

    echo "<steam status=\"yes\" />";
    exit;
}
