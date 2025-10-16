<?php

require_once '../config.php';
require_once '../function.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Tehran');
ini_set('default_charset', 'UTF-8');
ini_set('error_log', 'error_log');


$method = $_SERVER['REQUEST_METHOD'];

function datevalid($data_unsafe)
{
    global $APIKEY;

    if (!isset($data_unsafe)) {
        return false;
    }

    $decoded_query = html_entity_decode($data_unsafe);
    parse_str($decoded_query, $initData);

    if (!isset($initData['hash'])) {
        return false;
    }

    $receivedHash = $initData['hash'];
    unset($initData['hash']);

    if (!isset($initData['user'])) {
        return false;
    }

    $dataCheckArray = [];
    foreach ($initData as $key => $value) {
        if (!empty($value)) {
            $dataCheckArray[] = "$key=$value";
        }
    }

    sort($dataCheckArray);
    $dataCheckString = implode("\n", $dataCheckArray);

    // Telegram mini app verification requires the bot token to be used as the key
    // when generating the secret key. The previous implementation swapped the
    // parameters which resulted in an invalid secret key and all verification
    // attempts failing with "User verification failed".
    $secretKey = hash_hmac('sha256', 'WebAppData', $APIKEY, true);
    $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

    if (!hash_equals($calculatedHash, $receivedHash)) {
        http_response_code(403);
        return json_encode(array(
            'token' => null,
        ));
    }

    $userData = json_decode($initData['user'], true);
    if (!is_array($userData) || !isset($userData['id'])) {
        http_response_code(403);
        return json_encode(array(
            'token' => null,
        ));
    }

    $randomString = bin2hex(random_bytes(20));
    update("user", "token", $randomString, "id", $userData['id']);

    return json_encode(array(
        'token' => $randomString,
    ));
}

$rawInput = file_get_contents("php://input");

if ($rawInput === false) {
    echo json_encode(array(
        'status' => false,
        'msg' => "Failed to read request body",
        'obj' => []
    ));
    return;
}

$rawInput = trim($rawInput);

if ($rawInput === '') {
    echo json_encode(array(
        'status' => false,
        'msg' => "data invalid",
        'obj' => []
    ));
    return;
}

// Support both plain initData strings and JSON payloads containing the initData value.
$decodedJson = json_decode($rawInput, true);
if (json_last_error() === JSON_ERROR_NONE) {
    if (isset($decodedJson['initData']) && is_string($decodedJson['initData'])) {
        $rawInput = $decodedJson['initData'];
    } else {
        echo json_encode(array(
            'status' => false,
            'msg' => "data invalid",
            'obj' => []
        ));
        return;
    }
}

$datavalid = datevalid($rawInput);
if ($datavalid === false) {
    http_response_code(400);
    echo json_encode(array(
        'token' => null,
    ));
    return;
}

echo $datavalid;
