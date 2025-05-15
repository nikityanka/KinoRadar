<?php
session_start();
require_once('connect.php');
require_once('selectel.php');
require "../vendor/autoload.php";

use Aws\Exception\AwsException;


if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'modal',
        'message_desc' => 'Требуется авторизация'
    ];
    header('Location: ../profile.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    try {
        $file = $_FILES['avatar'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Ошибка загрузки файла');
        }

        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new Exception('Файл слишком большой. Максимальный размер: 5MB');
        }

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Допустимые форматы: JPG, JPEG, PNG, GIF');
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . uniqid() . '.' . $extension;

        $result = $s3Client->putObject([
            "Bucket" => "movie-posters",
            "Key" => "avatars/" . $filename,
            "Body" => fopen($file['tmp_name'], 'rb')
        ]);

        $avatarUrl = "https://9f45e7e7-2d7a-469c-b79e-7ebde63001b7.selstorage.ru/avatars/" . basename($result['ObjectURL']);

        $query = "SELECT * FROM movie_search.update_user_avatar($1, $2)";
        $result = pg_query_params($connection, $query, array($_SESSION['user']['id'], $avatarUrl));

        if (!$result) {
            throw new Exception('Ошибка обновления аватара');
        }

        $data = pg_fetch_assoc($result);
        $dbResult = json_decode($data['update_user_avatar'], true);

        if ($dbResult['status'] == 'success') {
            $_SESSION['user']['avatar'] = $avatarUrl;
            $_SESSION['message'] = [
                'message_type' => 'success',
                'message_dialog' => 'avatarModal',
                'message_desc' => 'Аватар успешно обновлен!'
            ];
        } else {
            throw new Exception($dbResult['message'] ?? 'Ошибка обновления аватара');
        }

    } catch (AwsException $e) {
        error_log("AWS Error: " . $e->getMessage());
        $_SESSION['message'] = [
            'message_type' => 'error',
            'message_dialog' => 'avatarModal',
            'message_desc' => 'Ошибка загрузки файла'
        ];
    } catch (Exception $e) {
        $_SESSION['message'] = [
            'message_type' => 'error',
            'message_dialog' => 'avatarModal',
            'message_desc' => $e->getMessage()
        ];
    }
    
    header('Location: ../profile.php');
    exit();
}
?>
