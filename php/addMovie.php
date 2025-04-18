<?php
session_start();
require_once('../php/connect.php');

if (empty($_SESSION['user']) || $_SESSION['user']['type'] != 1) {
    header("HTTP/1.1 403 Forbidden");
    exit(json_encode(['status' => 'error', 'message' => 'Permission Denied']));
}

$title = pg_escape_string($connection, $_POST['title'] ?? '');
$year = (int)($_POST['year'] ?? 0);
$description = pg_escape_string($connection, $_POST['description'] ?? '');
$original_title = pg_escape_string($connection, $_POST['original_title'] ?? '');
$country_id = pg_escape_string($connection, $_POST['country_id'] ?? '');
$director_id = (int)($_POST['director_id'] ?? 0);
$genres = isset($_POST['genres']) ? explode(',', $_POST['genres']) : [];
$link = pg_escape_string($connection, $_POST['link'] ?? '');

if (empty($title) || empty($year) || empty($description) || empty($original_title)) {
    exit(json_encode(['status' => 'error', 'message' => 'Please, fill all the inputs']));
}

$validationResult = validateReferences($connection, $country_id, $director_id, $genres);
if ($validationResult !== true) {
    exit(json_encode(['status' => 'error', 'message' => $validationResult]));
}

$admin_id = $_SESSION['user']['id'];

$poster_id = 1;

$genre_ids_str = '{' . implode(',', $genres) . '}';

$query = "SELECT $schema_name.admin_create_movie($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)";
$params = [
    $admin_id,
    $title,
    $year,
    $description,
    $original_title,
    $country_id,
    $director_id,
    $genre_ids_str,
    $poster_id,
    $link
];

$result = pg_query_params($connection, $query, $params);

if (!$result) {
    header("HTTP/1.1 500 Internal Server Error");
    exit(json_encode([
        'status' => 'error', 
        'message' => 'Error: ' . pg_last_error($connection)
    ]));
}

$response = pg_fetch_assoc($result);
if (json_decode($response['admin_create_movie'], true)['status'] == 'success') {
    //header('Content-Type: application/json');
    header("Location: https://kinoradar/admin/movies");
    exit(json_encode(['status' => 'success', 'message' => 'Movie has been added']));
} else {
    $error = json_decode($response['admin_create_movie'], true)['message'] ?? 'Неизвестная ошибка';
    header("HTTP/1.1 400 Bad Request");
    exit(json_encode(['status' => 'error', 'message' => $error]));
}

function validateReferences($connection, $country_id, $director_id, $genres) {
    $country_id = pg_fetch_result(pg_query_params($connection, "SELECT id FROM country WHERE id = $1", [$country_id]), 0, 0);
    if (!$country_id) {
        return "Country with ID '$country_id' does not exist.";
    }

    $directorExists = pg_fetch_result(pg_query_params($connection, "SELECT EXISTS(SELECT 1 FROM director WHERE id = $1)", [$director_id]), 0, 0);
    if (!$directorExists) {
        return "Director with ID $director_id does not exist.";
    }

    foreach ($genres as $genre_id) {
        $genreExists = pg_fetch_result(pg_query_params($connection, "SELECT EXISTS(SELECT 1 FROM genre WHERE id = $1)", [$genre_id]), 0, 0);
        if (!$genreExists) {
            return "Genre with ID $genre_id does not exist.";
        }
    }

    return true;
}
?>
