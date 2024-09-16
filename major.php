<?php
error_reporting(0);
$list_query = array_filter(@explode("\n", str_replace(array("\r", " "), "", @file_get_contents(readline("[?] List Query       ")))));
echo "[*] Total Query : ".count($list_query)."\n";
for ($i = 0; $i < count($list_query); $i++) {
    $c = $i + 1;
    echo "\n[$c]\n";
    $auth = get_auth($list_query[$i]);
    echo "[*] Get Auth : ";
    if($auth){
        echo "success\n";
        $task = get_task($auth);
        echo "[*] Get Task : ";
        if($task){
            echo "success\n\n";
            for ($a = 0; $a < count($task); $a++) {
                $ex = explode("|", $task[$a]);
                echo "[*] $ex[1] => ".solve_task($ex[0], $auth)."\n";
                sleep(5);
            }
        }
        else{
            echo "failed\n\n";
        }
    }
    else{
        echo "failed\n\n";
    }
}


function get_auth($query){
    $curl = curl("auth/tg/", false, "{\"init_data\":\"$query\"}")['access_token'];
    return $curl;
}

function get_task($auth){
    $curl = curl("tasks/?is_daily=false", $auth, false);
    for ($i = 0; $i < count($curl); $i++) {
        $list[] = $curl[$i]['id']."|".$curl[$i]['title'];
    }
    $curl = curl("tasks/?is_daily=true", $auth, false);
    for ($i = 0; $i < count($curl); $i++) {
        $list[] = $curl[$i]['id']."|".$curl[$i]['title'];
    }
    return $list;
}

function solve_task($id, $auth){
    $curl = curl("tasks/", $auth, "{\"task_id\":$id}");
    if($curl['is_completed'] == 1){
        $final = "success";
    }
    elseif($curl['detail']){
        $final = $curl['detail'];
    }
    else{
        $final = "failed";
    }
    return $final;
}

function curl($path, $auth = false, $body = false){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://major.bot/api/'.$path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if($body){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $headers = array();
    $headers[] = 'Accept: application/json, text/plain, */*';
    $headers[] = 'Accept-Language: en-US,en;q=0.9';
    if($auth){
        $headers[] = 'Authorization: Bearer '.$auth;
    }
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Origin: https://major.bot';
    $headers[] = 'Referer: https://major.bot/earn';
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    $decode = json_decode($result, true);
    return $decode;
}