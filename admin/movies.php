<?php
session_start();
require_once('../php/connect.php');

if (isset($_SESSION['user']) && $_SESSION['user']['type'] == 2) {
    include('../error.php');
    exit;
}

$movies = pg_query($connection, "SELECT * FROM $schema_name.movies_with_rating ORDER BY id");
$count = pg_num_rows($movies);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления</title>

    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="../dialog.css">
</head>

<body>
    <header>
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
                <a href="movies.php" class="active">
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
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Фильмы (<?= $count ?>)</h2>
                <a href="addMovie.php">
                    <div class="editButton enter" style="border: 2px solid green; margin: 15px;" title="Добавить фильм">
                        <span>Добавить фильм</span>
                    </div>
                </a>
            </div>
            <div class="separator"></div>
            <div class="table-div">
                <table class="users-collection">
                    <thead>
                        <tr id="headers">
                            <th width="10%">ID</th>
                            <th width="25%">Постер</th>
                            <th width="20%">Название</th>
                            <th width="20%">Год выпуска</th>
                            <th width="25%"></th>
                        </tr>
                    </thead>
                    <?php while ($row = pg_fetch_assoc($movies)) { ?>
                        <tr data-id="<?= $row['id'] ?>">
                            <td>#<?= $row['id'] ?></td>
                            <td class="avatar">
                                <img style="width: 50%;"
                                    src=<?= $row['poster'] ?>
                                    alt="">
                            </td>
                            <td><?= $row['title'] ?></td>
                            <td><?= $row['year'] ?></td>
                            <td>
                                <div class="buttons">
                                    <a href="../movie.php?movie=<?= $row['id'] ?>">
                                        <div class="editButton enter" title="Открыть страницу с фильмом">
                                            <img src="../media/link.svg" id="edit_button">
                                            <span>Перейти</span>
                                        </div>
                                    </a>
                                    <form action="./editMovie" method="post">
                                        <input type="hidden" name="movieid" value="<?= $row['id'] ?>">
                                        <button type="submit">
                                            <div class="editButton edit" title="Изменить данные фильма">
                                                <img src="../media/pencil.svg" id="edit_button">
                                                <span>Изменить</span>
                                            </div>
                                        </button>
                                    </form>
                                    <div class="editButton delete" title="Удалить фильм">
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

                if (!confirm('Вы уверены, что хотите удалить фильм?')) return;

                const row = deleteBtn.closest('tr');
                const movieId = row.dataset.id;

                delMovie(movieId);
            });
        });
    </script>

    <script>
        document.querySelectorAll('tr').forEach(row => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('.buttons, a, button')) return;

                if (row.id === 'headers') return;

                window.location.href = "../movie.php?movie=" + row.dataset.id;
            });
        });
    </script>
</body>

</html>

<pre style="margin-left: 300px;">
    <?php
    ?>
</pre>