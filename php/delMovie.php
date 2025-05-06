<?php
session_start();
require_once('connect.php');
require_once('selectel.php'); 

use Aws\Exception\AwsException;

if (!isset($_SESSION['user'])) {
    die(json_encode(['status' => 'error', 'message' => 'Not authorized!']));
}

$userType = $_SESSION['user']['type'] ?? 0;
if ($userType != 1) {
    die(json_encode(['status' => 'error', 'message' => 'No roots']));
}

$adminId = $_SESSION['user']['id'];
$movieId = $_POST['id'] ?? 0;

try {
    $posterQuery = "SELECT poster_image FROM get_movie_details($1)";
    $posterResult = pg_query_params($connection, $posterQuery, [$movieId]);
    
    if (!$posterResult) {
        throw new Exception('Ошибка получения данных фильма: ' . pg_last_error($connection));
    }

    $posterData = pg_fetch_assoc($posterResult);
    $poster = $posterData['poster_image'] ?? null;

    if ($poster) {
        $s3Client->deleteObject([
            'Bucket' => 'movie-posters',
            'Key' => "posters/" . basename($poster)
        ]);
    } 

    $query = "SELECT * FROM admin_delete_movie($1, $2)";
    $result = pg_query_params($connection, $query, [$adminId, $movieId]);

    if ($result) {
        $data = pg_fetch_assoc($result);
        echo json_encode($data);
    } else {
        throw new Exception('Ошибка БД: ' . pg_last_error($connection));
    }

} catch (AwsException $e) {
    error_log('S3 Error: ' . $e->getMessage());
    die(json_encode([
        'status' => 'error',
        'message' => 'Ошибка удаления файла: ' . $e->getAwsErrorMessage()
    ]));
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    die(json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]));
}
?>