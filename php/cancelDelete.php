<?php
session_start();
require_once('connect.php');

if (!isset($_SESSION['user'])) {
    die(json_encode(['status' => 'error', 'message' => 'Not authorized!']));
}

$userType = $_SESSION['user']['type'] ?? 0;
if ($userType != 1) {
    die(json_encode(['status' => 'error', 'message' => 'No roots']));
}

$adminId = $_SESSION['user']['id'];
$movieId = $_POST['id'] ?? 0;

$query = "SELECT * FROM cancel_movie_deletion($1, $2)";
$result = pg_query_params($connection, $query, [$adminId, $movieId]);

if ($result) {
    $data = pg_fetch_assoc($result);
    echo json_encode([
        'status' => 'success',
        'message' => 'Timer removed'
    ]);
} else {
    die(json_encode([
        'status' => 'error',
        'message' => 'Ошибка БД: ' . pg_last_error($connection)
    ]));
}
?>