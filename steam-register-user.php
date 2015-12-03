<?php
/*
 * Steampunked app register handling
 */
require_once "db.inc.php";
require_once "auth.inc.php";
echo '<?xml version="1.0" encoding="UTF-8" ?>';

if (!isset($_GET['magic']) || $_GET['magic'] != $SERVER_MAGIC) {
    echo '<steam status="no" msg="magic" />';
    exit;
}

// Process in a function
process($_GET['user'], $_GET['pw']);

/**
 * Process the query
 *
 * @param $user string the user to look for
 * @param $password string the user password
 */
function process($user, $password) {
    // Connect to the database
    $pdo = pdo_connect();

    // Check to see if the user already exists
    getUser($pdo, $user);

    $userQ = $pdo->quote($user);
    $pwQ = $pdo->quote($password);

    $pdo->beginTransaction();

    $query = "INSERT
              INTO steampunked_user(user, password)
              VALUES($userQ, $pwQ)";
    $result = $pdo->query($query);
    if ($result->rowCount() == 0) {
        echo "<steam status='no' msg='failed to insert user' />";
        exit;
    }

    $userId = $pdo->lastInsertId();
    $authSeries = $pdo->quote(generateToken());

    $query = "INSERT
              INTO steampunked_auth_token(series, user_id)
              VALUES($authSeries, $userId)";
    $result = $pdo->query($query);
    if ($result->rowCount() == 0) {
        $pdo->rollBack();

        echo "<steam status='no' msg='failed to insert auth' />";
        exit;
    }

    $pdo->commit();

    echo "<steam status='yes' />";
    exit;
}

/**
 * Ask the database for the user ID. If the user exists, the password
 * must match.
 *
 * @param $pdo pdo PHP Data Object
 * @param $user string The user name
 * @return string id if successful or exits if not
 */
function getUser($pdo, $user) {
    // Does the user exist in the database?
    $userQ = $pdo->quote($user);
    $query = "SELECT id FROM steampunked_user WHERE user=$userQ";

    $rows = $pdo->query($query);
    if ($row = $rows->fetch()) {
        // We found the record in the database
        echo '<steam status="no" msg="user already exists"/>';
        exit;
    }
}
