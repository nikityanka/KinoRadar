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

$query = "SELECT * FROM $schema_name.admin_delete_movie($1, $2)";
$result = pg_query_params($connection, $query, [$adminId, $movieId]);

if ($result) {
    $data = pg_fetch_assoc($result);
    echo json_encode($GLOBALS);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'DB error: ' . pg_last_error($connection)
    ]);
}
?>