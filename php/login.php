<?php
session_start();
require_once('connect.php');

$link = $_SERVER['HTTP_REFERER'];

if (isset($_SESSION['user'])) {
    $_SESSION['message'] = [
        'message_type' => 'info',
        'message_dialog' => 'modal',
        'message_desc' => 'Вы уже авторизованы'
    ];
    header("Location: $link");
    exit();
}

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'modal',
        'message_desc' => 'Требуется ввести логин и пароль'
    ];
    header("Location: $link");
    exit();
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);

if (empty($username) || empty($password)) {
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'modal',
        'message_desc' => 'Пожалуйста, заполните все поля'
    ];
    header("Location: $link");
    exit();
}

try {
    $query = "SELECT authenticate_user($1, $2) AS auth_result";
    $result = pg_query_params($connection, $query, array($username, $password));
    
    if (!$result) {
        throw new Exception('Ошибка выполнения запроса аутентификации');
    }

    $row = pg_fetch_assoc($result);
    $auth_result = json_decode($row['auth_result'], true);

    if ($auth_result['status'] === 'success') {
        $user_data = $auth_result['user_data'];
        
        $_SESSION['user'] = array(
            'id' => $user_data['id'],
            'username' => $user_data['username'],
            'avatar' => $user_data['avatar'],
            'type' => $user_data['type']['id']
        );
        
        header("Location: $link");
        exit();
    } else {
        $_SESSION['message'] = [
            'message_type' => 'error',
            'message_dialog' => 'modal',
            'message_desc' => $auth_result['message'] ?? 'Неверное имя пользователя или пароль'
        ];
        header("Location: $link");
        exit();
    }

} catch (Exception $e) {
    error_log('Authentication error: ' . $e->getMessage());
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'modal',
        'message_desc' => 'Внутренняя ошибка сервера при аутентификации'
    ];
    header("Location: $link");
    exit();
}
?>