<?php
session_start();
require_once('connect.php');

if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'modal',
        'message_desc' => 'Требуется авторизация'
    ];
    header('Location: ../profile.php');
    exit();
}

if (!isset($_POST['new_username']) || empty($_POST['new_username'])) {
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'usernameModal',
        'message_desc' => 'Имя пользователя не может быть пустым'
    ];
    header('Location: ../profile.php');
    exit();
}

$userid = $_SESSION['user']['id'];
$newusername = trim($_POST['new_username']);

try {
    $query = "SELECT * FROM movie_search.update_user_username($1, $2)";
    $result = pg_query_params($connection, $query, array($userid, $newusername));
    
    if (!$result) {
        throw new Exception('Ошибка выполнения запроса');
    }

    $data = pg_fetch_assoc($result);
    $result = json_decode($data['update_user_username'], true);

    if ($result['status'] == 'success') {
        if (strpos($result['message'], 'успешно обновлено') !== false) {
            $_SESSION['user']['username'] = $newusername;
        } else {
            $_SESSION['message'] = [
                'message_type' => 'info',
                'message_dialog' => 'usernameModal',
                'message_desc' => $result['message']
            ];
        }
    } else {
        $_SESSION['message'] = [
            'message_type' => 'error',
            'message_dialog' => 'usernameModal',
            'message_desc' => $result['message'] ?? 'Неизвестная ошибка'
        ];
    }

} catch (Exception $e) {
    error_log('Error updating username: ' . $e->getMessage());
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'usernameModal',
        'message_desc' => 'Внутренняя ошибка сервера'
    ];
}

header('Location: ../profile.php');
exit();
?>