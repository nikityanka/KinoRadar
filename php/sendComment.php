<?php
session_start();
require_once('connect.php');

if (!isset($_SESSION['user'])) {
    die(json_encode(['status' => 'error', 'message' => 'Not authorized!']));
}

$userid = $_SESSION['user']['id'];
$movieid = $_POST['movieid'];
$comment = trim($_POST['description']);

if (empty($comment)) {
    $_SESSION['message'] = 'Комментарий не может быть пустым';
    header("Location: ../movie.php?movie=$movieid");
    exit();
}

$query = "SELECT * FROM add_comment($1, $2, $3)";
$result = pg_query_params($connection, $query, array($userid, $movieid, $comment));

if ($result) {
    $data = pg_fetch_assoc($result);
    if ($data['status'] === 'error') {
        $_SESSION['message'] = $data['message'];
    }
    header("Location: ../movie.php?movie=$movieid");
} else {
    $_SESSION['message'] = 'Ошибка при добавлении комментария';
    header("Location: ../movie.php?movie=$movieid");
}
?>