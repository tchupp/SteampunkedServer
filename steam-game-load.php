<?php
/*
 * Steampunked app loading all pipes from one game
 */
require_once "db.inc.php";
require_once "auth.inc.php";
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

    $gameQ = $pdo->quote($game);
    $query = "SELECT xml
              FROM steampunked_pipe
              WHERE game_id=$gameQ";

    if (isset($_GET['pipe'])) {
        $pipeQ = $pdo->quote($_GET['pipe']);
        $query .= " AND id=$pipeQ";
    }

    $rows = $pdo->query($query);

    echo "<steam status=\"yes\">\n";
    foreach ($rows as $row) {
        $xml = $row['xml'];
        echo "$xml";
    }
    echo "</steam>";
    exit;
}

