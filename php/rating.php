<?php
require_once('connect.php');

$userid = $_POST["userid"];
$movieid = $_POST["movieid"];
$rating = $_POST["rating"];

$message = array();

$query = "SELECT * FROM movie_search.add_rating($1, $2, $3)";
$result = pg_query_params($connection, $query, array($userid, $movieid, $rating));

if (!$result) {
    $message["text"] = pg_last_error($connection);
    $message["type"] = "error";
} else {
    $row = pg_fetch_assoc($result);
    $resultData = json_decode($row['add_rating'], true);
    
    $message["text"] = $resultData['message'];
    $message["type"] = strpos($resultData['status'], 'success') !== false ? 'success' : 'error';
    
    if ($rating == 0 && $resultData['status'] == 'success') {
        $message["operation"] = "deleted";
    } elseif ($resultData['status'] == 'success') {
        $checkQuery = "SELECT 1 FROM $schema_name.rating WHERE user_id = $1 AND movie = $2";
        $checkResult = pg_query_params($connection, $checkQuery, array($userid, $movieid));
        $message["operation"] = pg_num_rows($checkResult) > 0 ? "updated" : "inserted";
    }
}

header("Location: ../movie.php?movie=$movieid");
exit;
?>