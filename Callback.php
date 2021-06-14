<?php

$data = json_decode(file_get_contents('php://input'),1);
if(!isset($data["type"])) {
    http_response_code(406);
    exit();
}

$type = $data["type"];
logging($data);
switch ($type)
{
    case "url_verification":
        echo $data["challenge"];
        break;

    case "event_callback":
        $event = $data["event"];

        switch($event["type"]) {
            case "message":
                $text = $event["text"];
                $author = $event["user"];
                $team = $event["team"];
                $channel = $event["channel"];
                $timestamp = $event["event_ts"];
                $isComplimentMessage = isComplimentMessage($text);

                if($isComplimentMessage) {
                    $api = request("reactions.add",
                        [
                            "token"     => ENV("TOKEN"),
                            "channel"   => $channel,
                            "name"      => ENV("NAME_REACTION", "taco"),
                            "timestamp" => $timestamp,
                        ]);
                }
//                $pushingUsers = getPushingUsers($text);
                break;

            default:

                break;
        }

        break;

    default:
        break;
}


// -------------
// Functions

function request($method, $params = [])
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://slack.com/api/'.$method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}


function isComplimentMessage($text)
{
    if (mb_stripos($text, ENV("KEY_COMPLIMENT_TEXT", ":taco:")) === false) {
        return false;
    }
    return true;
}

function getPushingUsers($text): array
{
    $users = [];
    while(true) {
        $numStart = mb_stripos($text, "<@");
        if($numStart === false) {
            break;
        }
        $text = mb_substr($text, $numStart+2);

        $numEnd = mb_stripos($text, ">");
        if($numEnd === false) {
            break;
        }
        $users[] = mb_substr($text,0,$numEnd);
        $text = mb_substr($text, $numEnd+1);
    }
    return $users;
}

function logging($data)
{
    if(file_exists("log.txt")) {
        $log = file_get_contents("log.txt");
    }
    else {
        $log = "";
    }
    $log = "[".date("d-m-Y H:i:s")."] ".json_encode($data, JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL.$log;
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
