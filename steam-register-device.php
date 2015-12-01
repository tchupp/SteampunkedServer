<?php
/*
 * Steampunked app register device token handling
 */
require_once "db.inc.php";
require_once "auth.inc.php";
echo '<?xml version="1.0" encoding="UTF-8" ?>';

if (!isset($_GET['user']) || !isset($_GET['device'])) {
    echo '<steam status="no" msg="malformed params"/>';
    exit;
}
if (!isset($_SERVER["HTTP_AUTHTOKEN"])) {
    echo '<steam status="no" msg="no auth token"/>';
    exit;
}

process($_GET['user'], $_SERVER['HTTP_AUTHTOKEN'], $_GET['device']);

/**
 * Process the query
 *
 *
 * @param $user string the user to register for
 * @param $authToken string the authentication token for the user
 * @param $deviceToken string the device token for the user
 */
function process($user, $authToken, $deviceToken) {
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
    if ($row = $rows->fetch()) {
        $userId = $row['id'];

        $tokenDate = $pdo->quote(date("Y-m-d H:i:s"));
        $deviceTokenQ = $pdo->quote($deviceToken);

        $query = "UPDATE steampunked_device_token
                  SET token_value=$deviceTokenQ, token_date=$tokenDate
                  WHERE user_id=$userId";
        $pdo->query($query);

        echo '<steam status="yes" />';
        exit;
    }

    echo '<steam status="no" msg="user error" />';
    exit;
}