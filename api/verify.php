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
    if (!isset($data_unsafe))
        return false;
    $decoded_query = html_entity_decode($data_unsafe);
    parse_str($decoded_query, $initData);
    if (!isset($initData['hash']))
        return false;
    $receivedHash = $initData['hash'];
    unset($initData['hash']);
    $randomString = bin2hex(random_bytes(20));
    update("user","token",$randomString,"id",json_decode($initData['user'],true)['id']);
    $dataCheckArray = [];
    if (!isset($initData['user']))
        return false;
    foreach ($initData as $key => $value) {
        if (!empty($value)) {
            $dataCheckArray[] = "$key=$value";
        }
    }
    sort($dataCheckArray);
    $dataCheckString = implode("\n", $dataCheckArray);
    $secretKey = hash_hmac('sha256', $APIKEY, 'WebAppData', true);
    $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);
    $valid_check = hash_equals($calculatedHash, $receivedHash);
    $obj = [];
    if($valid_check){
    return json_encode(array(
    'token' => $randomString,
    ));
    }else{
        http_response_code(403);
        return json_encode(array(
            'token' => null,
        ));
    }
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
    if (is_array($decodedJson)) {
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
    } elseif (is_string($decodedJson) && $decodedJson !== '') {
        $rawInput = $decodedJson;
    } else {
        echo json_encode(array(
            'status' => false,
            'msg' => "data invalid",
            'obj' => []
        ));
        return;
    }
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

$datavalid = datevalid($rawInput);
echo $datavalid;
