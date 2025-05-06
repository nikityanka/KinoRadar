<?php
session_start();
require_once('connect.php');

$link = $_SERVER['HTTP_REFERER'];

if (isset($_SESSION['user'])) {
    $_SESSION['message'] = [
        'message_type' => 'info',
        'message_dialog' => 'passwordModal',
        'message_desc' => 'Вы уже авторизованы'
    ];
    header("Location: $link");
    exit();
}

if (!isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['checkPassword'])) {
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'passwordModal',
        'message_desc' => 'Пожалуйста, заполните все поля'
    ];
    header("Location: $link");
    exit();
}

$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$checkPassword = trim($_POST['checkPassword']);

if (empty($username) || empty($email) || empty($password) || empty($checkPassword)) {
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'passwordModal',
        'message_desc' => 'Пожалуйста, заполните все поля'
    ];
    header("Location: $link");
    exit();
}

if ($password !== $checkPassword) {
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'passwordModal',
        'message_desc' => 'Пароли не совпадают'
    ];
    header("Location: $link");
    exit();
}

try {
    $query = "SELECT register_user($1, $2, $3, $4) AS reg_result";
    $result = pg_query_params($connection, $query, array($username, $password, $email, ''));
    
    if (!$result) {
        throw new Exception('Ошибка выполнения запроса регистрации');
    }

    $row = pg_fetch_assoc($result);
    $reg_result = json_decode($row['reg_result'], true);

    if ($reg_result['status'] === 'success') {
        $_SESSION['message'] = [
            'message_type' => 'success',
            'message_dialog' => 'modal',
            'message_desc' => 'Регистрация прошла успешно. Теперь вы можете войти.'
        ];
        
        header("Location: $link");
        exit();
    } else {
        $_SESSION['message'] = [
            'message_type' => 'error',
            'message_dialog' => 'passwordModal',
            'message_desc' => $reg_result['message'] ?? 'Ошибка при регистрации'
        ];
        header("Location: $link");
        exit();
    }

} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'passwordModal',
        'message_desc' => 'Внутренняя ошибка сервера при регистрации'
    ];
    header("Location: $link");
    exit();
}
?>