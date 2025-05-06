<?php
session_start();
require_once('connect.php');

$movieid = $_POST["id"];
$userid = $_SESSION["user"]["id"];
$message = array();

$query = "SELECT remove_from_favorites($1, $2) as result";
$result = pg_query_params($connection, $query, array($userid, $movieid));

if (!$result) {
    $message["text"] = pg_last_error($connection);
    $message["type"] = "error";
    echo json_encode($message);
    exit;
}

$data = json_decode(pg_fetch_assoc($result)['result'], true);

if ($data['status'] === 'success') {
    $message["text"] = $data['message'];
    $message["type"] = "removed";
} 
elseif ($data['status'] === 'info') {
    $query = "SELECT add_to_favorites($1, $2) as result";
    $result = pg_query_params($connection, $query, array($userid, $movieid));
    
    if (!$result) {
        $message["text"] = pg_last_error($connection);
        $message["type"] = "error";
        echo json_encode($message);
        exit;
    }
    
    $data = json_decode(pg_fetch_assoc($result)['result'], true);
    
    if ($data['status'] === 'success') {
        $message["text"] = $data['message'];
        $message["type"] = "added";
    } else {
        $message["text"] = $data['message'];
        $message["type"] = "error";
    }
} 
else {
    $message["text"] = $data['message'];
    $message["type"] = "error";
}

echo json_encode($message);
exit;
?>