<?php

$data = json_decode(file_get_contents('php://input'),1);
if(!isset($data["type"])) {
    http_response_code(406);
    exit();
}

$type = $data["type"];

switch ($type)
{
    case "url_verification":
        echo $data["challenge"];
        break;

    default:
        logging($data);
        break;
}


// -------------
// Functions



function logging($data)
{
    if(file_exists("log.txt")) {
        $log = file_get_contents("log.txt");
    }
    else {
        $log = "";
    }
    $log = "[".date("d-m-Y H:i:s")."] ".json_encode($data).PHP_EOL.PHP_EOL.$log;
    file_put_contents("log.txt", $log);
}

function ENV($index, $default = NULL)
{
    $file = file_get_contents(".env");
    $file = explode(PHP_EOL, $file);
    $params = [];
    foreach($file as $item)
    {
        $item = explode("=", $item);
        $params[trim($item[0])] = trim($item[1]);
    }
    if(isset($params[$index])) {
        return $params[$index];
    }
    return $default;
}