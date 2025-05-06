<?php
session_start();
require_once('connect.php');
require_once('selectel.php'); 

if (empty($_SESSION['user']) || $_SESSION['user']['type'] != 1) {
    header("HTTP/1.1 403 Forbidden");
    exit(json_encode(['status' => 'error', 'message' => 'Доступ запрещен']));
}

$poster_id = null;
if (!empty($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
    $poster = $_FILES['poster'];
    
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo($poster['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        exit(json_encode([
            'status' => 'error', 
            'message' => 'Допустимые форматы: ' . implode(', ', $allowed_extensions)
        ]));
    }
    
    if ($poster['size'] > 5 * 1024 * 1024) {
        exit(json_encode(['status' => 'error', 'message' => 'Максимальный размер файла — 5MB']));
    }
    
    $filename = 'poster_' . uniqid() . '.' . $extension;
    
    try {
        $result = $s3Client->putObject([
            "Bucket" => "movie-posters",
            "Key"    => "posters/" . $filename,
            "Body"   => file_get_contents($poster['tmp_name'])
        ]);
        
        
        pg_query($connection, "BEGIN");
        
        $poster_url = "https://9f45e7e7-2d7a-469c-b79e-7ebde63001b7.selstorage.ru/posters/" . basename($result['ObjectURL']);
        $query = "INSERT INTO poster (poster) VALUES ($1) RETURNING id";
        $result = pg_query_params($connection, $query, [$poster_url]);
        $poster_id = pg_fetch_result($result, 0, 0);
        
        pg_query($connection, "COMMIT");
        
    } catch (Exception $e) {
        pg_query($connection, "ROLLBACK");
        exit(json_encode(['status' => 'error', 'message' => 'Ошибка загрузки постера']));
    }
}

$required_fields = ['title', 'year', 'description', 'original_title', 'country_id', 'director_id', 'genres'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        exit(json_encode(['status' => 'error', 'message' => 'Заполните все обязательные поля']));
    }
}

$title = pg_escape_string($connection, $_POST['title']);
$year = (int)$_POST['year'];
$description = pg_escape_string($connection, $_POST['description']);
$original_title = pg_escape_string($connection, $_POST['original_title']);
$country_id = (int)$_POST['country_id'];
$director_id = (int)$_POST['director_id'];
$genres = array_map('intval', explode(',', $_POST['genres']));
$link = pg_escape_string($connection, $_POST['link'] ?? '');

$validationResult = validateReferences($connection, $country_id, $director_id, $genres);
if ($validationResult !== true) {
    exit(json_encode(['status' => 'error', 'message' => $validationResult]));
}

$genre_ids_str = '{' . implode(',', $genres) . '}';

$admin_id = $_SESSION['user']['id'];
$query = "SELECT admin_create_movie($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)";
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
    exit(json_encode([
        'status' => 'error', 
        'message' => 'Ошибка базы данных: ' . pg_last_error($connection)
    ]));
}

$response = pg_fetch_assoc($result);
$json_response = json_decode($response['admin_create_movie'], true);

if ($json_response['status'] === 'success') {
    header("Location: ../admin/movies");
    exit(json_encode([
        'status' => 'success', 
        'message' => 'Фильм добавлен',
        'movie_id' => $json_response['movie_id']
    ]));
} else {
    exit(json_encode([
        'status' => 'error', 
        'message' => $json_response['message'] ?? 'Неизвестная ошибка'
    ]));
}

function validateReferences($connection, $country_id, $director_id, $genres) {
    $country_exists = pg_fetch_result(
        pg_query_params($connection, "SELECT EXISTS(SELECT 1 FROM country WHERE id = $1)", [$country_id]),
        0, 0
    );
    if (!$country_exists) {
        return "Страна не существует";
    }

    $director_exists = pg_fetch_result(
        pg_query_params($connection, "SELECT EXISTS(SELECT 1 FROM director WHERE id = $1)", [$director_id]),
        0, 0
    );
    if (!$director_exists) {
        return "Режиссер не существует";
    }

    foreach ($genres as $genre_id) {
        $genre_exists = pg_fetch_result(
            pg_query_params($connection, "SELECT EXISTS(SELECT 1 FROM genre WHERE id = $1)", [$genre_id]),
            0, 0
        );
        if (!$genre_exists) {
            return "Жанр с ID $genre_id не существует";
        }
    }

    return true;
}

?>
