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

if (!$result) {
    $_SESSION['message'] = 'Ошибка при выполнении запроса к базе данных';
    error_log("Ошибка PostgreSQL: " . pg_last_error($connection));
    header("Location: ../movie.php?movie=$movieid");
    exit();
}

$data = pg_fetch_assoc($result);


if (!isset($data['add_comment'])) {
    $_SESSION['message'] = 'Некорректный ответ от сервера';
    header("Location: ../movie.php?movie=$movieid");
    exit();
}

$commentResponse = json_decode($data['add_comment'], true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $_SESSION['message'] = 'Ошибка обработки данных сервера';
    header("Location: ../movie.php?movie=$movieid");
    exit();
}

if ($commentResponse['status'] === 'error') {
    $_SESSION['message'] = $commentResponse['message'];
} else {
    $_SESSION['message'] = 'Комментарий успешно добавлен!';
}

header("Location: ../movie.php?movie=$movieid");
exit();
?>