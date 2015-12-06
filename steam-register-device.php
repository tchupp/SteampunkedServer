<?php
/*
 * Steampunked app register device token handling
 */
require_once "db.inc.php";
require_once "auth.inc.php";
echo '<?xml version="1.0" encoding="UTF-8" ?>';

if (!isset($_GET['device'])) {
    echo '<steam status="no" msg="malformed params"/>';
    exit;
}
if (!isset($_SERVER['HTTP_AUTHUSER']) || !isset($_SERVER["HTTP_AUTHTOKEN"])) {
    echo '<steam status="no" msg="malformed auth header"/>';
    exit;
}

process($_SERVER['HTTP_AUTHUSER'], $_SERVER['HTTP_AUTHTOKEN'], $_GET['device']);

/**
 * Process the query
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
    $tokenDate = $pdo->quote(date("Y-m-d H:i:s"));
    $deviceTokenQ = $pdo->quote($deviceToken);

    $query = "INSERT
              INTO steampunked_device_token(token_value, token_date, user_id)
              VALUES($deviceTokenQ, $tokenDate,
              (SELECT id
               FROM steampunked_user
               WHERE user=$userQ))
              ON DUPLICATE KEY UPDATE
               token_value=$deviceTokenQ,
               token_date=$tokenDate";
    $result = $pdo->query($query);

    if ($result->rowCount() != 0) {
        echo '<steam status="yes" />';
        exit;
    }

    echo '<steam status="no" msg="failed to register device"/>';
    exit;
}