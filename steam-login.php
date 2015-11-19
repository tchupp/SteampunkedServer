<?php
/*
 * Steampunked app login handling
 */
require_once "db.inc.php";
echo '<?xml version="1.0" encoding="UTF-8" ?>';

if (!isset($_GET['magic']) || $_GET['magic'] != "TechItHa6RuzeM8") {
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

    getUser($pdo, $user, $password);
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
    $query = "SELECT id, password FROM steampunkeduser WHERE user=$userQ";

    $rows = $pdo->query($query);
    if ($row = $rows->fetch()) {
        // We found the record in the database
        // Check the password
        if ($row['password'] != $password) {
            echo '<steam status="no" msg="password error"/>';
            exit;
        }

        echo '<steam status="yes" msg="login successful"/>';
        exit;
    }

    echo '<steam status="no" msg="user error" />';
    exit;
}
