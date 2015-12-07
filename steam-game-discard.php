<?php
/*
 * Steampunked app player discard
 */
require_once "db.inc.php";
require_once "auth.inc.php";
require_once "gcm.inc.php";

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
 * @param $game string the game id
 */
function process($user, $authToken, $game) {
    $pdo = pdo_connect();

    if (!authenticate($pdo, $user, $authToken)) {
        echo '<steam status="no" msg="auth failed"/>';
        exit;
    }

    $deviceToken = getOpponentDeviceToken($pdo, $user, $game);

    sendGCM($deviceToken, discardMoveKey(), "Opponent Discarded Pipe", "-1");

    echo '<steam status="yes" />';
    exit;
}
