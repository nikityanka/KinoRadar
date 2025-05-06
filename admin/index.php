<?php
session_start();
require_once('../php/connect.php');

if (empty($_SESSION['user']) || $_SESSION['user']['type'] == 2) {
    include('../error.php');
    exit;
}

$moviesCount = pg_fetch_assoc(pg_query($connection, "SELECT COUNT(*) FROM $schema_name.movie"))['count'];
$usersCount = pg_fetch_assoc(pg_query($connection, "SELECT COUNT(*) FROM $schema_name.user"))['count'];
$commentsCount = pg_fetch_assoc(pg_query($connection, "SELECT COUNT(*) FROM $schema_name.comment"))['count'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления</title>

    <link rel="stylesheet" href="../header.css">
    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="../dialog.css">
</head>

<body>
    <?php
    include("header.php");
    ?>
    <main>
        <nav class="side-nav">
            <ul>
                <a href="./" class="active">
                    <li>
                        <div class="icon">
                            <img src="../media/dashboard.svg" alt="">
                        </div>
                        <span>Главная</span>
                    </li>
                </a>
                <a href="users.php">
                    <li>
                        <div class="icon">
                            <img src="../media/profile.svg" alt="">
                        </div>
                        <span>Пользователи</span>
                    </li>
                </a>
                <a href="movies.php">
                    <li>
                        <div class="icon">
                            <img src="../media/movie_white.svg" alt="">
                        </div>
                        <span>Фильмы</span>
                    </li>
                </a>
                <a href="comments.php">
                    <li>
                        <div class="icon">
                            <img src="../media/comment.svg" alt="">
                        </div>
                        <span>Комментарии</span>
                    </li>
                </a>
                <a href="other.php">
                    <li>
                        <div class="icon">
                            <img src="../media/genres.svg" alt="">
                        </div>
                        <span>Данные</span>
                    </li>
                </a>
            </ul>
        </nav>
        <div class="main-wrapper">
            <h2>Главная</h2>
            <div class="separator"></div>
            <div class="indicators-collection">
                <div class="indicator">
                    <div class="info">
                        <p class="title">Количество фильмов</p>
                        <span class="counter"><?= $moviesCount ?></span>
                    </div>
                    <div class="icon">
                        <img src="../media/movie_orange.svg" alt="">
                    </div>
                </div>
                <div class="indicator">
                    <div class="info">
                        <p class="title">Количество пользователей</p>
                        <span class="counter"><?= $usersCount ?></span>
                    </div>
                    <div class="icon">
                        <img src="../media/profile_orange.svg" alt="">
                    </div>
                </div>
                <div class="indicator">
                    <div class="info">
                        <p class="title">Количество комментариев</p>
                        <span class="counter"><?= $commentsCount ?></span>
                    </div>
                    <div class="icon">
                        <img src="../media/pencil.svg" alt="">
                    </div>
                </div>
            </div>
        </div>
    </main>


</body>

</html>

<pre style="margin-left: 300px;">
    <?php
    ?>
</pre>