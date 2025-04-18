<?php
session_start();
require_once('../php/connect.php');

if (empty($_SESSION['user'])) {
    include('../error.php');
    exit;
}

if ($_SESSION['user']['type'] != 1) {
    header("HTTP/1.1 403 Forbidden");
    exit(json_encode(['status' => 'error', 'message' => 'Permission Denied']));
}

$movie_id = (int)($_POST['movie_id'] ?? 0);
$title = pg_escape_string($connection, $_POST['title'] ?? '');
$year = (int)($_POST['year'] ?? 0);
$description = pg_escape_string($connection, $_POST['description'] ?? '');
$original_title = pg_escape_string($connection, $_POST['original_title'] ?? '');
$country_id = (int)($_POST['country_id'] ?? 0);
$director_id = (int)($_POST['director_id'] ?? 0);
$genres = isset($_POST['genre_ids']) ? explode(',', $_POST['genre_ids']) : [];
$link = pg_escape_string($connection, $_POST['link'] ?? '');

if (empty($movie_id) || empty($title) || empty($year) || empty($description) || empty($original_title)) {
    exit(json_encode(['status' => 'error', 'message' => 'Please, fill all required fields']));
}

$validationResult = validateReferences($connection, $country_id, $director_id, $genres);
if ($validationResult !== true) {
    exit(json_encode(['status' => 'error', 'message' => $validationResult]));
}

$poster_id = 1;
/*
if (!empty($_FILES['poster1']['name'])) {
    // Загрузка нового постера
    $uploadResult = handlePosterUpload($connection, $_FILES['poster1']);
    if ($uploadResult['status'] !== 'success') {
        exit(json_encode($uploadResult));
    }
    $poster_id = $uploadResult['poster_id'];
} elseif (!empty($_POST['existing_poster'])) {
    // Использование существующего постера
}*/

$poster_id = getPosterIdByUrl($connection, $_POST['existing_poster']);


$admin_id = $_SESSION['user']['id'];
$genre_ids_str = '{' . implode(',', $genres) . '}';

$query = "SELECT $schema_name.admin_update_movie($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)";
$params = [
    $admin_id,
    $movie_id,
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
        'message' => 'Database error: ' . pg_last_error($connection)
    ]));
}

$response = pg_fetch_assoc($result);
$json_response = json_decode($response['admin_update_movie'], true);

if ($json_response['status'] == 'success') {
    header("Location: ../admin/movies.php");
    exit();
} else {
    header("HTTP/1.1 400 Bad Request");
    exit(json_encode([
        'status' => 'error', 
        'message' => $json_response['message'] ?? 'Unknown error'
    ]));
}


function validateReferences($connection, $country_id, $director_id, $genres) {
    $countryExists = pg_fetch_result(
        pg_query_params($connection, "SELECT EXISTS(SELECT 1 FROM country WHERE id = $1)", [$country_id]), 
        0, 0
    );
    if ($countryExists !== 't') {
        return "Country with ID $country_id does not exist.";
    }

    $directorExists = pg_fetch_result(
        pg_query_params($connection, "SELECT EXISTS(SELECT 1 FROM director WHERE id = $1)", [$director_id]), 
        0, 0
    );
    if ($directorExists !== 't') {
        return "Director with ID $director_id does not exist.";
    }

    foreach ($genres as $genre_id) {
        $genreExists = pg_fetch_result(
            pg_query_params($connection, "SELECT EXISTS(SELECT 1 FROM genre WHERE id = $1)", [$genre_id]), 
            0, 0
        );
        if ($genreExists !== 't') {
            return "Genre with ID $genre_id does not exist.";
        }
    }

    return true;
}

/**
 * Обработка загрузки постера
 *//*
function handlePosterUpload($connection, $file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        return ['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG and GIF are allowed.'];
    }

    if ($file['size'] > $maxSize) {
        return ['status' => 'error', 'message' => 'File is too large. Maximum size is 5MB.'];
    }

    // Генерация уникального имени файла
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('poster_') . '.' . $extension;
    $uploadPath = '/path/to/upload/directory/' . $filename; // Укажите правильный путь

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['status' => 'error', 'message' => 'Failed to upload file.'];
    }

    // Сохранение в базе данных
    $url = 'https://yourdomain.com/uploads/' . $filename; // Укажите правильный URL
    $result = pg_query_params($connection, "INSERT INTO poster (poster) VALUES ($1) RETURNING id", [$url]);
    
    if (!$result) {
        unlink($uploadPath); // Удаляем загруженный файл в случае ошибки
        return ['status' => 'error', 'message' => 'Database error: ' . pg_last_error($connection)];
    }

    $poster_id = pg_fetch_result($result, 0, 0);
    return ['status' => 'success', 'poster_id' => $poster_id, 'url' => $url];
}*/

function getPosterIdByUrl($connection, $url) {
    $result = pg_query_params($connection, "SELECT id FROM poster WHERE poster = $1", [$url]);
    if ($result && pg_num_rows($result) > 0) {
        return pg_fetch_result($result, 0, 0);
    }
    return null;
}