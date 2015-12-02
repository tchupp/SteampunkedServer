<?php
/*
 * Steampunked app handle a POST to save pipes
 */
require_once "db.inc.php";
require_once "auth.inc.php";
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

    $query = "INSERT
              INTO steampunked_pipe(game_id, xml)
              VALUES($gameQ, $xmlQ)";
    $pdo->query($query);

    $pipeId = $pdo->lastInsertId();

    $pdo->commit();

    echo "<steam status='yes' pipe='$pipeId' />";
    exit;
}