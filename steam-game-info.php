<?php
/*
 * Steampunked app get the info about a game
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

    $gameId = $pdo->quote($game);

    $query = "SELECT name, UserA.user AS creating, UserB.user AS joining, grid
              FROM steampunked_game Game, steampunked_user UserA, steampunked_user UserB
              WHERE Game.id = $gameId
              AND Game.creating_user_id = UserA.id
              AND Game.joining_user_id = UserB.id";

    $rows = $pdo->query($query);
    if ($row = $rows->fetch()) {
        $name = $row['name'];
        $creating = $row['creating'];
        $joining = $row['joining'];
        $grid = $row['grid'];
        echo "<steam status='yes' name='$name' creator='$creating' joining='$joining' grid='$grid' />";
        exit;
    }
    echo "<steam status='no' msg='failed to find game' />";
    exit;
}