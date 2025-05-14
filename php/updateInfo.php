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

$userid = $_SESSION['user']['id'];
$newEmail = isset($_POST['new_email']) ? trim(strip_tags($_POST['new_email'])) : '';
$newAbout = isset($_POST['new_about']) ? trim(strip_tags($_POST['new_about'])) : '';

if (empty($newEmail)) {
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'infoModal',
        'message_desc' => 'Поле "Почта" должно быть заполнено'
    ];
    header('Location: ../profile.php');
    exit();
}

try {
    $newEmail = filter_var($newEmail, FILTER_SANITIZE_EMAIL);
    
    $query = "SELECT * FROM movie_search.update_user_info(
        $1, 
        encrypt_data($2, 'kkinoradarr'), 
        $3
    )";
    
    $result = pg_query_params($connection, $query, [
        $userid,
        $newEmail,
        $newAbout
    ]);
    
    if (!$result) {
        throw new Exception('Ошибка выполнения запроса');
    }

    $data = pg_fetch_assoc($result);
    $result = json_decode($data['update_user_info'], true);

    if ($result['status'] == 'success') {
        $_SESSION['user']['about'] = $newAbout;
        $_SESSION['user']['email'] = $newEmail;
        
        $_SESSION['message'] = [
            'message_type' => 'success',
            'message_dialog' => 'infoModal',
            'message_desc' => 'Данные успешно обновлены!'
        ];
    } else {
        $_SESSION['message'] = [
            'message_type' => 'error',
            'message_dialog' => 'infoModal',
            'message_desc' => $result['message'] ?? 'Ошибка обновления данных'
        ];
    }

} catch (Exception $e) {
    error_log('Error updating info: ' . $e->getMessage());
    $_SESSION['message'] = [
        'message_type' => 'error',
        'message_dialog' => 'infoModal',
        'message_desc' => 'Внутренняя ошибка сервера'
    ];
}

header('Location: ../profile.php');
exit();

?>