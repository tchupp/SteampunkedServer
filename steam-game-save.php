<?php
/*
 * Steampunked app handle a POST to save pipes
 */
require_once "db.inc.php";
require_once "auth.inc.php";
require_once "gcm.inc.php";
echo '<?xml version="1.0" encoding="UTF-8" ?>';

if (!isset($_GET['game'])) {
    echo '<steam status="no" msg="malformed params"/>';
    exit;
}
if (!isset($_POST['xml'])) {
    echo '<steam status="no" msg="missing xml"/>';
    exit;
}
if (!isset($_SERVER['HTTP_AUTHUSER']) || !isset($_SERVER["HTTP_AUTHTOKEN"])) {
    echo '<steam status="no" msg="malformed auth header"/>';
    exit;
}

process($_SERVER['HTTP_AUTHUSER'], $_SERVER['HTTP_AUTHTOKEN'], $_GET['game'], stripslashes($_POST['xml']));

function process($user, $authToken, $game, $xml) {
    $pdo = pdo_connect();

    if (!authenticate($pdo, $user, $authToken)) {
        echo '<steam status="no" msg="auth failed"/>';
        exit;
    }

    $gameQ = $pdo->quote($game);
    $xmlQ = $pdo->quote($xml);

    $pdo->beginTransaction();

    $moveDate = $pdo->quote(date("Y-m-d H:i:s"));

    $query = "UPDATE steampunked_game_info
              SET move_date = $moveDate
              WHERE game_id=$gameQ";
    $result = $pdo->query($query);

    if ($result->rowCount() == 0) {
        $pdo->rollBack();

        echo "<steam status='no' msg='failed to save pipe' />";
        exit;
    }

    $query = "INSERT
              INTO steampunked_pipe(game_id, xml)
              VALUES($gameQ, $xmlQ)";
    $result = $pdo->query($query);

    if ($result->rowCount() == 0) {
        $pdo->rollBack();

        echo "<steam status='no' msg='failed to save pipe' />";
        exit;
    }

    $pipeId = $pdo->lastInsertId();

    $pdo->commit();

    $deviceToken = getOpponentDeviceToken($pdo, $user, $game);

    sendGCM($deviceToken, newMoveKey(), "player saved", $pipeId);

    echo "<steam status='yes' pipe='$pipeId' />";
    exit;
}