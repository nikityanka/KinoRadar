<?php
session_start();
require_once('connect.php');
require_once('selectel.php');

use Aws\Exception\AwsException;

if (empty($_SESSION['user'])) {
    include('../error.php');
    exit;
}

if ($_SESSION['user']['type'] != 1) {
    $_SESSION["message"] = 'Permission Denied';
    header("Location: ../admin/movies.php");
    exit;
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
    $_SESSION["message"] = 'Пожалуйста, заполните все обязательные поля';
    header("Location: ../admin/editMovie.php?movie_id=$movie_id");
    exit;
}

try {
    $posterQuery = "SELECT poster FROM movie WHERE id = $1";
    $posterResult = pg_query_params($connection, $posterQuery, [$movie_id]);
    
    if (!$posterResult) {
        throw new Exception('Ошибка получения данных фильма: ' . pg_last_error($connection));
    }

    $posterData = pg_fetch_assoc($posterResult);
    $poster_id = $posterData['poster'] ?? null;

    if (!empty($_FILES['poster']['name'])) {
        if ($poster_id) {
            $oldFileQuery = "SELECT poster FROM poster WHERE id = $1";
            $oldFileResult = pg_query_params($connection, $oldFileQuery, [$poster_id]);
            
            if ($oldFileResult) {
                $oldFilePath = pg_fetch_result($oldFileResult, 0, 0);
                if (!empty($oldFilePath)) {
                    try {
                        $s3Client->deleteObject([
                            'Bucket' => 'movie-posters',
                            'Key' => "posters/" . basename($oldFilePath)
                        ]);
                    } catch (AwsException $e) {
                        error_log('S3 delete error: ' . $e->getMessage());
                    }
                }
            }
        }

        $uploadResult = handlePosterUpload($connection, $s3Client, $_FILES['poster']);
        if ($uploadResult['status'] !== 'success') {
            $_SESSION["message"] = $uploadResult['message'];
            header("Location: ../admin/editMovie.php", true, 307);
            exit;
        }

        if ($poster_id) {
            pg_query_params($connection, 
                "UPDATE poster SET poster = $1 WHERE id = $2", 
                [$uploadResult['url'], $poster_id]
            );
        } else {
            $poster_id = $uploadResult['poster_id'];
        }
    }

    $admin_id = $_SESSION['user']['id'];
    $genre_ids_str = '{' . implode(',', $genres) . '}';

    $query = "SELECT admin_update_movie($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)";
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
        throw new Exception('Ошибка базы данных: ' . pg_last_error($connection));
    }

    $response = pg_fetch_assoc($result);
    $json_response = json_decode($response['admin_update_movie'], true);

    if ($json_response['status'] == 'success') {
        header("Location: ../admin/movies.php");
        exit;
    } else {
        throw new Exception($json_response['message'] ?? 'Неизвестная ошибка');
    }

} catch (Exception $e) {
    $_SESSION["message"] = $e->getMessage();
    header("Location: ../admin/editMovie.php?movie_id=$movie_id");
    exit;
}

function handlePosterUpload($connection, $s3Client, $file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024;

    if (!in_array($file['type'], $allowedTypes)) {
        return ['status' => 'error', 'message' => 'Недопустимый тип файла'];
    }

    if ($file['size'] > $maxSize) {
        return ['status' => 'error', 'message' => 'Файл слишком большой (максимум 5MB)'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'poster_' . uniqid() . '.' . $extension;
    $s3Path = "posters/" . $filename;

    try {
        $s3Client->putObject([
            "Bucket" => "movie-posters",
            "Key"    => $s3Path,
            "Body"   => file_get_contents($file['tmp_name']),
            "ACL"    => "public-read"
        ]);

        $s3Url = "https://9f45e7e7-2d7a-469c-b79e-7ebde63001b7.selstorage.ru/" . $s3Path;

        return [
            'status' => 'success',
            'url' => $s3Url
        ];

    } catch (AwsException $e) {
        return ['status' => 'error', 'message' => 'Ошибка загрузки файла: ' . $e->getMessage()];
    }
}