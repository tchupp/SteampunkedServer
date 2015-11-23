<?php
/*
 * Steampunked app login handling
 */
require_once "db.inc.php";
require_once "auth.inc.php";
echo '<?xml version="1.0" encoding="UTF-8" ?>';

if (!isset($_GET['magic']) || $_GET['magic'] != $SERVER_MAGIC) {
    echo '<steam status="no" msg="magic"/>';
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

    $userId = getUser($pdo, $user, $password);

    $tokenDate = $pdo->quote(date("Y-m-d H:i:s"));
    $authToken = $pdo->quote(generateToken());

    $query = "UPDATE steampunked_auth_token
              SET token_value=$authToken, token_date=$tokenDate
              WHERE user_id=$userId";
    $pdo->query($query);

    $query = "SELECT series, token_value FROM steampunked_auth_token WHERE user_id=$userId";
    $rows = $pdo->query($query);
    if ($row = $rows->fetch()) {
        $series = $row['series'];
        $token = $row['token_value'];
        echo "<steam status=\"yes\" msg=\"login successful\" series=\"$series\" auth=\"$token\"/>";
        exit;
    }

    echo "<steam status=\"no\" msg=\"authentication error\"/>";
    exit;
}

/**
 * Ask the database for the user ID. If the user exists, the password
 * must match.
 *
 * @param $pdo pdo PHP Data Object
 * @param $user string The user name
 * @param $password string Password
 * @return string id if successful or exits if not
 */
function getUser($pdo, $user, $password) {
    // Does the user exist in the database?
    $userQ = $pdo->quote($user);
    $query = "SELECT id, password FROM steampunked_user WHERE user=$userQ";

    $rows = $pdo->query($query);
    if ($row = $rows->fetch()) {
        // We found the record in the database
        // Check the password
        if ($row['password'] != $password) {
            echo '<steam status="no" msg="password error"/>';
            exit;
        }

        return $row['id'];
    }

    echo '<steam status="no" msg="user error" />';
    exit;
}
