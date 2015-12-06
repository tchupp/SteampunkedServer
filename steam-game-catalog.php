<?php
/*
 * Steampunked app loading all open games
 */
require_once "db.inc.php";
require_once "auth.inc.php";
echo '<?xml version="1.0" encoding="UTF-8" ?>';

if (!isset($_SERVER['HTTP_AUTHUSER']) || !isset($_SERVER["HTTP_AUTHTOKEN"])) {
    echo '<steam status="no" msg="malformed auth header"/>';
    exit;
}

process($_SERVER['HTTP_AUTHUSER'], $_SERVER['HTTP_AUTHTOKEN']);

/**
 * Process the query
 *
 * @param $user string the user to register for
 * @param $authToken string the authentication token for the user
 */
function process($user, $authToken) {
    $pdo = pdo_connect();

    if (!authenticate($pdo, $user, $authToken)) {
        echo '<steam status="no" msg="auth failed"/>';
        exit;
    }

    $query = "SELECT Game.id, name, user, grid
              FROM steampunked_game Game, steampunked_user User, steampunked_game_info Info
              WHERE Game.creating_user_id = User.id
              AND Game.id = Info.game_id
              AND Info.game_status =
                (SELECT name
                 FROM steampunked_game_status
                 WHERE name LIKE '%CREATED%')";

    $rows = $pdo->query($query);

    echo "<steam status=\"yes\">\n";
    foreach ($rows as $row) {
        $id = $row['id'];
        $name = $row['name'];
        $creator = $row['user'];
        $grid = $row['grid'];

        echo "<game id=\"$id\" name=\"$name\" creator=\"$creator\" grid=\"$grid\"/>\r\n";
    }
    echo "</steam>";
    exit;
}