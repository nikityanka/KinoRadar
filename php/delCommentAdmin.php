<?php
session_start();
require_once('connect.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['type'] !== 1) {
    die(json_encode(['status' => 'error', 'message' => 'Доступ запрещен']));
}

$adminId = $_POST['adminid'];
if ($_SESSION['user']['id'] != $adminId) {
    die(json_encode(['status' => 'error', 'message' => 'Неверные права доступа']));
}

$commentId = $_POST['commentid'];
$movieQuery = "SELECT movie FROM comment WHERE id = $1";
$movieResult = pg_query_params($connection, $movieQuery, array($commentId));
$movieData = pg_fetch_assoc($movieResult);
$movieId = $movieData['movie'];

if (pg_num_rows($movieResult) === 0) {
    die(json_encode(['status' => 'error', 'message' => 'Комментарий не найден']));
}

$query = "SELECT * FROM delete_comment($1, $2, $3)";
$result = pg_query_params($connection, $query, array($adminId, $movieId, $commentId));

if ($result) {
    $data = pg_fetch_assoc($result);
    echo json_encode($data);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Ошибка при удалении комментария'
    ]);
}
?>