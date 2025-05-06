<?php
session_start();
require_once('../php/connect.php');

if (isset($_SESSION['user']) && $_SESSION['user']['type'] == 2) {
    include('../error.php');
    exit;
}

function timeLeft($deletionDate)
{
    if (empty($deletionDate)) return '';

    $now = new DateTime();
    $deleteTime = new DateTime($deletionDate);
    $interval = $now->diff($deleteTime);

    if ($now > $deleteTime) {
        return "00:00:00";
    }

    $days = $interval->days;
    $hours = str_pad($interval->h, 2, '0', STR_PAD_LEFT);
    $minutes = str_pad($interval->i, 2, '0', STR_PAD_LEFT);
    $seconds = str_pad($interval->s, 2, '0', STR_PAD_LEFT);

    return "{$days}д {$hours}:{$minutes}:{$seconds}";
}

$movies = pg_query($connection, "SELECT * FROM $schema_name.movies_with_rating ORDER BY id");
$count = pg_num_rows($movies);
/*
$movies = pg_query(
    $connection,
    "SELECT m.*, 
            c.country_name, 
            d.name AS director_name, 
            p.poster AS poster_image,
            COALESCE(AVG(r.rating), 0) AS avg_rating,
            COUNT(r.rating) AS rating_count
     FROM movie m
     JOIN country c ON m.country = c.id
     JOIN director d ON m.director = d.id
     LEFT JOIN poster p ON m.poster = p.id
     LEFT JOIN rating r ON m.id = r.movie
     GROUP BY m.id, c.country_name, d.name, p.poster
     ORDER BY m.id"
);
$count = pg_num_rows($movies);*/
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
    <?php include("header.php"); ?>
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
                <!-- Добавленный блок поиска -->
                <div class="search-container" style="margin: 15px 0;">
                    <input type="text" id="movieSearch" placeholder="Поиск по названию..." style="padding: 8px; width: 300px;">
                </div>
                <a href="addMovie.php">
                    <div class="editButton enter" style="border: 2px solid green; margin: 15px;" title="Добавить фильм">
                        <span>Добавить фильм</span>
                    </div>
                </a>
            </div>
            <div class="separator"></div>
            <div class="table-div">
                <table class="users-collection">
                    <thead id="tableHeaders">
                        <tr id="headers">
                            <th width="10%">ID</th>
                            <th width="25%">Постер</th>
                            <th width="20%">Название</th>
                            <th width="15%">Год</th>
                            <th width="15%">Статус</th>
                            <th width="25%">Действия</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php $counter = 1;
                        while ($row = pg_fetch_assoc($movies)):
                            $isScheduled = !empty($row['deletion_date']);
                        ?>
                            <tr data-id="<?= $row['id'] ?>" class="<?= $isScheduled ? 'scheduled-deletion' : '' ?>">
                                <td>#<?= $counter ?></td>
                                <td class="avatar">
                                    <img style="width: 50%;" src="<?= $row['poster'] ?>" alt="">
                                </td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= $row['year'] ?></td>
                                <td>
                                    <?php if ($isScheduled): ?>
                                        <span style="color: red;">На удалении</span>
                                    <?php else: ?>
                                        <span style="color: green;">Отображается</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="buttons">
                                        <?php if (!$isScheduled): ?>
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
                                        <?php else: ?>
                                            <div class="deletion-controls">
                                                <div class="timer" data-end="<?= $row['deletion_date'] ?>">
                                                    Удаление через: <?= timeLeft($row['deletion_date']) ?>
                                                </div>
                                                <button class="cancel-delete" data-id="<?= $row['id'] ?>">
                                                    Отменить удаление
                                                </button>
                                                <button class="force-delete" data-id="<?= $row['id'] ?>">
                                                    Удалить сейчас
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php $counter++;
                        endwhile; ?>
                    </tbody>
                </table>
                <div id="noResults" class="no-results-message" style="display: none;">
                    Фильмы не найдены
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const movieSearch = document.getElementById('movieSearch');
            const movieRows = document.querySelectorAll('.users-collection tbody tr');
            const movieCountElement = document.querySelector('h2');
            const noResultsMessage = document.getElementById('noResults');
            const tableHeaders = document.getElementById('tableHeaders');
            const tableBody = document.getElementById('tableBody');
            const originalCount = <?= $count ?>;

            movieSearch.addEventListener('input', function() {
                const searchText = this.value.trim().toLowerCase();
                let visibleCount = 0;

                movieRows.forEach(row => {
                    const titleCell = row.querySelector('td:nth-child(3)');
                    const movieTitle = titleCell.textContent.toLowerCase();
                    const isMatch = movieTitle.includes(searchText);

                    row.style.display = isMatch ? 'table-row' : 'none';
                    if (isMatch) visibleCount++;
                });

                if (searchText && visibleCount === 0) {
                    noResultsMessage.style.display = 'block';
                    tableHeaders.style.display = 'none';
                    tableBody.style.display = 'none';
                } else {
                    noResultsMessage.style.display = 'none';
                    tableHeaders.style.display = 'table-header-group';
                    tableBody.style.display = 'table-row-group';
                }

                const countText = searchText ? visibleCount : originalCount;
                movieCountElement.textContent = `Фильмы (${countText})`;
            });

            movieSearch.addEventListener('keyup', function(e) {
                if (this.value === '') {
                    movieRows.forEach(row => row.style.display = 'table-row');
                    noResultsMessage.style.display = 'none';
                    tableHeaders.style.display = 'table-header-group';
                    tableBody.style.display = 'table-row-group';
                    movieCountElement.textContent = `Фильмы (${originalCount})`;
                }
            });

            function updateTimers() {
                document.querySelectorAll('.timer').forEach(timer => {
                    const endTime = new Date(timer.dataset.end).getTime();
                    const now = Date.now();
                    const distance = endTime - now;

                    if (distance <= 0) {
                        timer.textContent = "00:00:00";
                        return;
                    }

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance / (1000 * 60 * 60)) % 24);
                    const minutes = Math.floor((distance / (1000 * 60)) % 60);
                    const seconds = Math.floor((distance / 1000) % 60);
                    timer.textContent = `Удаление через: ${days}д ${[
                hours, minutes, seconds
            ].map(n => String(n).padStart(2, '0')).join(':')}`;
                });
            }

            setInterval(updateTimers, 1000);
            updateTimers();

            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('editButton') && event.target.classList.contains('delete')) {
                    const row = event.target.closest('tr');
                    const movieId = row.dataset.id;

                    if (!confirm('Вы уверены, что хотите удалить фильм? У вас будет 3 дня на отмену.')) return;

                    row.style.opacity = '0.5';
                    row.style.pointerEvents = 'none';

                    scheduleDelete(movieId, row);
                }
            });

            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('cancel-delete')) {
                    const button = event.target;
                    const movieId = button.getAttribute('data-id');
                    const row = button.closest('tr');

                    row.style.opacity = '0.5';
                    row.style.pointerEvents = 'none';

                    cancelDelete(movieId, row)
                        .catch(() => {
                            row.style.opacity = '';
                            row.style.pointerEvents = '';
                        });
                }
            });

            document.addEventListener('click', function(event) {
                const target = event.target;
                if (target.classList.contains('force-delete')) {
                    const movieId = target.getAttribute('data-id');
                    if (!confirm('Вы уверены, что хотите полностью удалить фильм без возможности восстановления?')) {
                        return;
                    }

                    const row = target.closest('tr');
                    if (row) {
                        row.style.opacity = '0.5';
                        row.style.pointerEvents = 'none';
                        delMovie(movieId, row);
                    }
                }
            });

            document.addEventListener('click', function(event) {
                const target = event.target;
                const row = target.closest('tr[data-id]');
                if (!row) return;

                if (!target.closest('.buttons') && !target.closest('a') && !target.closest('button')) {
                    const movieId = row.getAttribute('data-id');
                    window.location.href = "../movie.php?movie=" + movieId;
                }
            });
        });
    </script>
</body>

</html>