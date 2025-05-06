<?php
session_start();
require_once('connect.php');

if (!isset($_SESSION['user'])) {
    die(json_encode(['status' => 'error', 'message' => 'Not authorized!']));
}

$userType = $_SESSION['user']['type'] ?? 0;
if ($userType != 1) {
    die(json_encode(['status' => 'error', 'message' => 'No permissions']));
}

$adminId = $_SESSION['user']['id'];
$userId = $_POST['id'] ?? 0;

if (empty($userId)) {
    die(json_encode(['status' => 'error', 'message' => 'User ID is required']));
}

$query = "SELECT * FROM admin_clear_user_about($1, $2)";
$result = pg_query_params($connection, $query, [$adminId, $userId]);

if ($result) {
    $data = pg_fetch_assoc($result);
    echo json_encode($data);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database error: ' . pg_last_error($connection)
    ]);
}
?>