<?php
session_start();
require_once('../php/connect.php');

$userid;
if (isset($_SESSION['user']) && $_SESSION['user']['type'] == 2) {
    include('../error.php');
    exit;
} else {
    $userid = $_SESSION['user']['id'];
}

$comments = pg_query($connection, "SELECT * FROM $schema_name.user_comments_view ORDER BY comment_id");
$count = pg_num_rows($comments);

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
    <header style="justify-content: left; border-radius: 0; padding-left: 100px;">
        <h1 class="link" style="text-align: left; font-size: 40px;">КиноРадар</h1>
    </header>
    <main>
        <nav class="side-nav">
            <ul>
                <a href="./">
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
                <a href="comments.php" class="active">
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
            <h2>Комментарии (<?= $count ?>)</h2>
            <div class="separator"></div>
            <div class="table-div">
                <table class="users-collection">
                    <thead>
                        <tr id="headers">
                            <th width="5%">ID</th>
                            <th width="20%">Фильм</th>
                            <th width="15%">Имя пользователя</th>
                            <th width="45%">Комментарий</th>
                            <th width="20%"></th>
                        </tr>
                    </thead>
                    <?php while ($row = pg_fetch_assoc($comments)) {
                        if ($row['user_avatar'] == 'media/default_avatar.jpg') {
                            $avatar = "../media/default_avatar.jpg";
                        } else {
                            $avatar = $row['user_avatar'];
                        }
                    ?>

                        <tr data-id="<?= $row['comment_id'] ?>" data-movie="<?= $row['movie_id'] ?>">
                            <td><?= $row['comment_id'] ?></td>
                            <td style="color: coral;"><?= $row['movie_title'] ?></td>
                            <td><?= $row['username'] ?></td>
                            <td style="text-align: justify; padding: 0 100px;"><?= $row['comment_text'] ?></td>
                            <td>
                                <div class="buttons">
                                    <a href="../movie.php?movie=<?= $row['movie_id'] ?>">
                                        <div class="editButton enter" title="Открыть страницу с фильмом">
                                            <img src="../media/link.svg" id="edit_button">
                                            <span>Перейти</span>
                                        </div>
                                    </a>
                                    <div class="editButton delete" title="Удалить комментарий">
                                        <img src="../media/bucket.svg" id="edit_button">
                                        <span>Удалить</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <? } ?>
                </table>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('table').addEventListener('click', event => {
                const deleteBtn = event.target.closest('.editButton.delete');
                if (!deleteBtn) return;

                if (!confirm('Вы уверены, что хотите удалить комментарий?')) return;

                const row = deleteBtn.closest('tr');
                const commentId = row.dataset.id;

                delCommentAdmin(commentId, <?= $userid ?>);
            });
        });
    </script>

    <script>
        document.querySelectorAll('tr').forEach(row => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('.buttons, a, button')) return;

                if (row.id === 'headers') return;

                window.location.href = "../movie.php?movie=" + row.dataset.movie;
            });
        });
    </script>
</body>

</html>

<pre style="margin-left: 300px;">
    <?php
    ?>
</pre>