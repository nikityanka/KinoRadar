<?php
session_start();
require_once('connect.php');

if (!isset($_SESSION['user'])) {
    die(json_encode(['status' => 'error', 'message' => 'Необходима авторизация']));
}

$userid = $_SESSION['user']['id'];
$commentid = $_POST['id'];

$movieQuery = "SELECT movie FROM comment WHERE id = $1 AND \"user\" = $2";
$movieResult = pg_query_params($connection, $movieQuery, array($commentid, $userid));

if (pg_num_rows($movieResult) === 0) {
    die(json_encode(['status' => 'error', 'message' => 'Комментарий не найден']));
}

$movieData = pg_fetch_assoc($movieResult);
$movieid = $movieData['movie'];

$query = "SELECT * FROM delete_comment($1, $2, $3)";
$result = pg_query_params($connection, $query, array($userid, $movieid, $commentid));

if ($result) {
    $data = pg_fetch_assoc($result);
    echo json_encode($data);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка при удалении комментария']);
}
?>