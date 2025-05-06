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

$currentPassword = trim($_POST['current_password'] ?? '');
$newPassword = trim($_POST['new_password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'passwordModal',
        'message_desc' => 'Все поля должны быть заполнены'
    ];
    header('Location: ../profile.php');
    exit();
}

if ($newPassword !== $confirmPassword) {
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'passwordModal',
        'message_desc' => 'Новый пароль и подтверждение не совпадают'
    ];
    header('Location: ../profile.php');
    exit();
}

$userid = $_SESSION['user']['id'];

try {
    $query = "SELECT * FROM movie_search.update_user_password($1, $2, $3)";
    $result = pg_query_params($connection, $query, [$userid, $currentPassword, $newPassword]);
    
    if (!$result) {
        throw new Exception('Ошибка выполнения запроса к базе данных');
    }

    $data = pg_fetch_assoc($result);
    $result = json_decode($data['update_user_password'], true);

    if ($result['status'] === 'success') {
        $_SESSION['message'] = [
            'message_type' => 'success',
            'message_dialog' => 'passwordModal',
            'message_desc' => 'Пароль успешно изменен!'
        ];
    } else {
        $errorMessage = preg_replace('/Попытка сменить пароль\((.*?)\)/', '$1', $result['message']);
        $_SESSION['message'] = [
            'message_type' => 'error',
            'message_dialog' => 'passwordModal',
            'message_desc' => $errorMessage ?? 'Неизвестная ошибка'
        ];
    }

} catch (Exception $e) {
    error_log('Error updating password: ' . $e->getMessage());
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'passwordModal',
        'message_desc' => 'Внутренняя ошибка сервера'
    ];
}

header('Location: ../profile.php');
exit();
?>
<pre>
    <?php
        print_r($GLOBALS);
    ?>
</pre>