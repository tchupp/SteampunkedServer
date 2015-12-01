<?php

function generateToken() {
    return bin2hex(openssl_random_pseudo_bytes(16));
}

/**
 * @param $pdo pdo PHP Data Object
 * @param $user string the user name
 * @param $authToken string the auth token
 * @return bool
 */
function authenticate($pdo, $user, $authToken) {
    $userQ = $pdo->quote($user);
    $query = "SELECT id
              FROM steampunked_user
              WHERE user=$userQ";

    $rows = $pdo->query($query);
    if ($row = $rows->fetch()) {
        $userId = $row['id'];

        $query = "SELECT token_value
                  FROM steampunked_auth_token
                  WHERE user_id=$userId";

        $rows = $pdo->query($query);
        if ($row = $rows->fetch()) {
            return $row['token_value'] == $authToken;
        }
    }

    return false;
}