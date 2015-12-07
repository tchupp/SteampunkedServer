<?php
/*
 * Steampunked app join a game with one player
 */
require_once "db.inc.php";
require_once "auth.inc.php";
require_once "gcm.inc.php";
echo '<?xml version="1.0" encoding="UTF-8" ?>';

if (!isset($_GET['game'])) {
    echo '<steam status="no" msg="malformed params"/>';
    exit;
}
if (!isset($_SERVER['HTTP_AUTHUSER']) || !isset($_SERVER["HTTP_AUTHTOKEN"])) {
    echo '<steam status="no" msg="malformed auth header"/>';
    exit;
}

process($_SERVER['HTTP_AUTHUSER'], $_SERVER['HTTP_AUTHTOKEN'], $_GET['game']);

/**
 * Process the query
 *
 * @param $user string the user to register for
 * @param $authToken string the authentication token for the user
 * @param $game string id of the game to load
 */
function process($user, $authToken, $game) {
    $pdo = pdo_connect();

    if (!authenticate($pdo, $user, $authToken)) {
        echo '<steam status="no" msg="auth failed"/>';
        exit;
    }

    $userQ = $pdo->quote($user);
    $gameId = $pdo->quote($game);

    $pdo->beginTransaction();

    $query = "UPDATE steampunked_game
              SET joining_user_id=
                (SELECT id
                FROM steampunked_user
                WHERE user=$userQ)
              WHERE id=$gameId
              AND creating_user_id <>
                (SELECT id
                FROM steampunked_user
                WHERE user=$userQ)
              AND joining_user_id = -1";

    $result = $pdo->query($query);

    if ($result->rowCount() == 0) {
        $pdo->rollBack();

        echo "<steam status=\"no\" msg='error joining game' />";
        exit;
    }

    $query = "UPDATE steampunked_game_info, steampunked_game_status
              SET game_status = name
              WHERE game_id=$gameId
              AND name LIKE '%STEAMING%'";

    $result = $pdo->query($query);

    if (!$result) {
        $pdo->rollBack();

        echo "<steam status=\"no\" msg='error updating game info' />";
        exit;
    }

    $pdo->commit();

    $deviceToken = getOpponentDeviceToken($pdo, $user, $game);

    sendGCM($deviceToken, playerJoinedKey(), "Opponent Joined The Game", $user);

    echo "<steam status=\"yes\" />";
    exit;
}