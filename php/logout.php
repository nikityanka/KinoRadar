<?php
    session_start();
    unset($_SESSION['user'], $_SESSION['message']);

    $link = $_SERVER['HTTP_REFERER'];

    header("Location: ../");
?>