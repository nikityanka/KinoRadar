<?php
session_start();
require_once('connect.php');


try {
    if (!isset($_SESSION['user']['id'])) {
        throw new Exception('Требуется авторизация');
    }

    $required_fields = ['userid', 'movieid', 'rating'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field])) {
            throw new Exception("Отсутствует обязательное поле: $field");
        }
    }

    $userid = (int)$_POST['userid'];
    $movieid = (int)$_POST['movieid'];
    $rating = (int)$_POST['rating'];

    if ($_SESSION['user']['id'] !== $userid) {
        throw new Exception('Несоответствие идентификатора пользователя');
    }

    if ($rating < 0 || $rating > 5) {
        throw new Exception('Недопустимое значение оценки. Допустимый диапазон: 0-5');
    }

    $movieCheck = pg_query_params($connection, 
        "SELECT 1 FROM $schema_name.movie WHERE id = $1",
        [$movieid]
    );
    if (pg_num_rows($movieCheck) === 0) {
        throw new Exception('Фильм не найден');
    }

    $result = pg_query_params($connection, 
        "SELECT * FROM movie_search.add_rating($1, $2, $3)",
        [$userid, $movieid, $rating]
    );

    if (!$result) {
        throw new Exception(pg_last_error($connection));
    }

    $row = pg_fetch_assoc($result);
    $resultData = json_decode($row['add_rating'], true);

    if ($resultData['status'] !== 'success') {
        throw new Exception($resultData['message']);
    }

    $_SESSION['rating_message'] = [
        'text' => $resultData['message'],
        'type' => 'success',
        'operation' => ($rating === 0) ? 'deleted' : 'updated'
    ];

} catch (Exception $e) {
    $_SESSION['rating_message'] = [
        'text' => 'Ошибка: ' . $e->getMessage(),
        'type' => 'error'
    ];
} finally {
    header("Location: ../movie.php?movie=" . $movieid);
    exit();
}
?>