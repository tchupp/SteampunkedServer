<?php
/*
 * Steampunked app creating a game
 */
require_once "db.inc.php";
require_once "auth.inc.php";
echo '<?xml version="1.0" encoding="UTF-8" ?>';

if (!isset($_GET['user']) || !isset($_GET['name']) || !isset($_GET['grid'])) {
    echo '<steam status="no" msg="malformed params"/>';
    exit;
}

if (!isset($_SERVER["HTTP_AUTHTOKEN"])) {
    echo '<steam status="no" msg="no auth token"/>';
    exit;
}

process($_GET['user'], $_SERVER['HTTP_AUTHTOKEN'], $_GET['name'], $_GET['grid']);

/**
 * Process the query
 *
 * @param $user string the user to register for
 * @param $authToken string the authentication token for the user
 * @param $name string the device token for the user
 * @param $grid string size of the playing area
 */
function process($user, $authToken, $name, $grid) {
    $pdo = pdo_connect();

    if (!authenticate($pdo, $user, $authToken)) {
        echo '<steam status="no" msg="auth failed"/>';
        exit;
    }

    $userQ = $pdo->quote($user);
    $query = "SELECT id
              FROM steampunked_user
              WHERE user=$userQ";

    $rows = $pdo->query($query);
    if (!$row = $rows->fetch()) {
        echo '<steam status="no" msg="user error"/>';
        exit;
    }

    // We found the record in the database
    $userId = $row['id'];
    $nameQ = $pdo->quote($name);
    $gridQ = $pdo->quote($grid);
    $creationDate = $pdo->quote(date("Y-m-d H:i:s"));

    $pdo->beginTransaction();

    $query = "INSERT
              INTO steampunked_game(creating_user_id, name, grid, creation_date)
              VALUES($userId, $nameQ, $gridQ, $creationDate)";
    $pdo->query($query);

    $gameId = $pdo->lastInsertId();

    $pdo->commit();

    echo "<steam status='yes' game='$gameId' />";
    exit;
}