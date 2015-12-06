<?php

/**
 * @param $pdo PDO for the database
 * @param $user string user name
 * @param $game string game id
 */
function getOpponentDeviceToken($pdo, $user, $game) {
    $gameQ = $pdo->quote($game);

    $query = "SELECT UserA.user AS creating, UserB.user AS joining
              FROM steampunked_game Game, steampunked_user UserA, steampunked_user UserB
              WHERE Game.id = $gameQ
              AND Game.creating_user_id = UserA.id
              AND Game.joining_user_id = UserB.id";

    $rows = $pdo->query($query);
    $row = $rows->fetch();
    if (!$row) {
        exit;
    }

    $creating = $row['creating'];
    $joining = $row['joining'];

    $userName = $pdo->quote(($creating == $user) ? $joining : $creating);

    $query = "SELECT token_value AS token
              FROM steampunked_device_token, steampunked_user User
              WHERE user_id = User.id
              AND User.user = $userName";

    $rows1 = $pdo->query($query);

    $row1 = $rows1->fetch();
    if (!$row1) {
        echo "ERROR";
        exit;
    }

    return $row1['token'];
}

function sendGCM($deviceToken, $title, $message, $data) {

    // API access key from Google API's Console
    define('API_ACCESS_KEY', 'AIzaSyBHodUyJk06HnkQJjcHVYaHc8CrHnoaYys');
    $registrationIds = array($deviceToken);

    // prep the bundle
    $msg = array
    (
        'title' => $title,
        'message' => $message,
        'data' => $data
    );
    $fields = array
    (
        'registration_ids' => $registrationIds,
        'data' => $msg
    );

    $headers = array
    (
        'Authorization: key=' . API_ACCESS_KEY,
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    curl_close($ch);
    echo $result;
}

function showToastKey() {
    return "show_toast";
}

function playerJoinedKey() {
    return "player_joined";
}

function newMoveKey() {
    return "new_move";
}

function endGameKey() {
    return "end_game";
}