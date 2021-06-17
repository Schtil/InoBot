<?php
include 'rb.php';
$data = json_decode(file_get_contents('php://input'),1);
if(!isset($data["type"])) {
    http_response_code(406);
    exit();
}

$type = $data["type"];
logging($data);


R::setup( 'mysql:host='.ENV("MYSQL_HOST", "localhost").';dbname='.ENV("MYSQL_DATABASE", "InoBot").'', ENV("MYSQL_USERNAME", "InoBot") , ENV("MYSQL_PASSWORD", "InoBot") );

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
                $pushingUsers = getPushingUsers($text, $author);
                $dbAuthor = getDBUser($author, $team);
                if(count($pushingUsers) == 0) {
                    break;
                }
                if(!isComplimentMessage($text)) {
                    break;
                }
                if(!isAvailableCompliment($dbAuthor, $team, count($pushingUsers))) {
                    request("chat.postEphemeral", [
                        "channel" => $channel,
                        "as_user" => true,
                        "user" => $author,
                        "text" => "Извини, ты исчерпал лимит на сегодня :(",
                    ]);
                    request("reactions.add", [
                        "channel" => $channel,
                        "name" => "no_entry_sign",
                        "timestamp" => $timestamp,
                    ]);
                    break;
                }
                foreach($pushingUsers as $pushUser) {
                    $dbUser = getDBUser($pushUser, $team);
                    $dbUser->credits++;
                    R::store($dbUser);

                    $log = R::dispense("log");
                    $log->author_slack_id = $dbAuthor->slack_id;
                    $log->from_slack_id = $pushUser;
                    $log->type = "add_compliment";
                    $log->team_id = $team;
                    R::store($log);
                }
                request("reactions.add", [
                    "channel" => $channel,
                    "name" => ENV("NAME_REACTION", "taco"),
                    "timestamp" => $timestamp,
                ]);
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

function getDBUser($slack_id, $team_id)
{
    $dbUser = R::findOne( 'users', ' slack_id = ? AND team_id = ?', [ $slack_id , $team_id ] );
    if(!$dbUser) {
        $dbUser = R::dispense("users");
        $apiAuthor = request("users.info", ["user" => $slack_id]);
        $dbUser->slack_id = $slack_id;
        $dbUser->email = $apiAuthor["user"]["profile"]["email"];
        $dbUser->name = $apiAuthor["user"]["profile"]["real_name_normalized"];
        $dbUser->team_id = $team_id;
        R::store($dbUser);
    }
    return $dbUser;
}

function isAvailableCompliment($dbUser, $teamId, $count)
{
    $log = R::find("log", "author_slack_id = ? AND created_at > ? AND team_id = ?", [$dbUser->slack_id, date("Y-m-d")." 00:00:00", $teamId]);
    if(ENV("LIMIT_COMPLIMENT", 5) >= $count + count($log)) {
        return true;
    }
    return false;
}

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
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Bearer '.ENV("TOKEN"),
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response,1);
}


function isComplimentMessage($text)
{
    if (mb_stripos($text, ENV("KEY_COMPLIMENT_TEXT", ":taco:")) === false) {
        return false;
    }
    return true;
}

function getPushingUsers($text, $author): array
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
    return array_diff( array_unique($users), [$author]);
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
