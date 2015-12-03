<?php
/*
 * Steampunked app creating a game
 */
require_once "db.inc.php";
require_once "auth.inc.php";
echo '<?xml version="1.0" encoding="UTF-8" ?>';

if (!isset($_GET['name']) || !isset($_GET['grid'])) {
    echo '<steam status="no" msg="malformed params"/>';
    exit;
}
if (!isset($_SERVER['HTTP_AUTHUSER']) || !isset($_SERVER["HTTP_AUTHTOKEN"])) {
    echo '<steam status="no" msg="malformed auth header"/>';
    exit;
}

process($_SERVER['HTTP_AUTHUSER'], $_SERVER['HTTP_AUTHTOKEN'], $_GET['name'], $_GET['grid']);

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
    $nameQ = $pdo->quote($name);
    $gridQ = $pdo->quote($grid);
    $creationDate = $pdo->quote(date("Y-m-d H:i:s"));

    $pdo->beginTransaction();

    $query = "INSERT
              INTO steampunked_game(name, grid, creation_date, creating_user_id, joining_user_id)
              VALUES($nameQ, $gridQ, $creationDate,
              (SELECT id
              FROM steampunked_user
              WHERE user=$userQ),
              -1)";
    $result = $pdo->query($query);

    if ($result->rowCount() == 0) {
        $pdo->rollBack();

        echo "<steam status='no' msg='failed to create game' />";
        exit;
    }

    $gameId = $pdo->lastInsertId();

    $pdo->commit();

    echo "<steam status='yes' game='$gameId' />";
    exit;
}