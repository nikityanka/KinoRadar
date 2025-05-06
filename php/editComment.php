<?php
session_start();
require_once('connect.php');

if (!isset($_SESSION['user'])) {
    die(json_encode(['status' => 'error', 'message' => 'Not authorized!']));
}

$userid = $_SESSION['user']['id'];
$commentid = $_POST['id'];
$comment = $_POST['comment'];

if (empty($comment)) {
    $_SESSION['message'] = 'Комментарий не может быть пустым';
    header("Location: ../movie.php?movie=$movieid");
    exit();
}

$movieQuery = "SELECT movie FROM comment WHERE id = $1 AND \"user\" = $2";
$movieResult = pg_query_params($connection, $movieQuery, array($commentid, $userid));

if (pg_num_rows($movieResult) === 0) {
    die(json_encode(['status' => 'error', 'message' => 'Comment not found']));
}

$movieData = pg_fetch_assoc($movieResult);
$movieid = $movieData['movie'];

$query = "SELECT * FROM update_comment($1, $2, $3)";
$result = pg_query_params($connection, $query, array($userid, $movieid, $comment));

if ($result) {
    $data = pg_fetch_assoc($result);
    echo json_encode($data);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error']);
}
?>