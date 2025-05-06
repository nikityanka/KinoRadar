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

$value = trim($_POST['value'] ?? '');
$table = $_POST['table'] ?? '';

switch ($table) {
    case 'country':
        $column = 'country_name';
        break;
    case 'director':
        $column = 'name';
        break;
    case 'genre':
        $column = 'genre';
        break;
    default:
        die(json_encode(['status' => 'error', 'message' => 'Invalid table name']));
}

$checkQuery = "SELECT * FROM $table WHERE $column = $1";
$checkResult = pg_query_params($connection, $checkQuery, [$value]);
if (!$checkResult) {
    die(json_encode(['status' => 'error', 'message' => 'Database error: ' . pg_last_error($connection)]));
}

if (pg_num_rows($checkResult) == 0) {
    die(json_encode(['status' => 'error', 'message' => 'This value is not exists']));
}

$query = "DELETE FROM $table WHERE $column = $1";
$result = pg_query_params($connection, $query, [$value]);

if ($result) {
    $data = pg_fetch_assoc($result);
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . pg_last_error($connection)
    ]);
}
?>