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
    <style>
        .search-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-field-select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .no-results-message {
            text-align: center;
            padding: 20px;
            font-size: 18px;
            color: #666;
            display: none;
        }
    </style>
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
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Комментарии (<?= $count ?>)</h2>
                <div class="search-container">
                    <select id="searchField" class="search-field-select">
                        <option value="username">По пользователю</option>
                        <option value="comment">По комментарию</option>
                        <option value="movie">По фильму</option>
                    </select>
                    <input type="text" id="commentSearch" placeholder="Поиск..." style="padding: 8px; width: 300px;">
                </div>
                <div></div>
            </div>
            <div class="separator"></div>
            <div class="table-div">
                <table class="users-collection">
                    <thead id="tableHeaders">
                        <tr id="headers">
                            <th width="5%">ID</th>
                            <th width="20%">Фильм</th>
                            <th width="15%">Имя пользователя</th>
                            <th width="45%">Комментарий</th>
                            <th width="20%"></th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php $counter = 1;
                        while ($row = pg_fetch_assoc($comments)) {
                            if ($row['user_avatar'] == 'media/default_avatar.jpg') {
                                $avatar = "../media/default_avatar.jpg";
                            } else {
                                $avatar = $row['user_avatar'];
                            }
                        ?>
                            <tr data-id="<?= $row['comment_id'] ?>" data-movie="<?= $row['movie_id'] ?>">
                                <td><?= $counter ?></td>
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
                        <?php $counter++;
                        } ?>
                    </tbody>
                </table>
                <div id="noResults" class="no-results-message" style="display: none;">
                    Комментарии не найдены
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const commentSearch = document.getElementById('commentSearch');
            const searchField = document.getElementById('searchField');
            const commentRows = document.querySelectorAll('.users-collection tbody tr');
            const commentCountElement = document.querySelector('h2');
            const noResultsMessage = document.getElementById('noResults');
            const tableHeaders = document.getElementById('tableHeaders');
            const tableBody = document.getElementById('tableBody');
            const originalCount = <?= $count ?>;

            commentSearch.addEventListener('input', function() {
                const searchText = this.value.trim().toLowerCase();
                const searchBy = searchField.value;
                let visibleCount = 0;

                commentRows.forEach(row => {
                    let searchCell;
                    
                    if (searchBy === 'username') {
                        searchCell = row.querySelector('td:nth-child(3)'); // Имя пользователя
                    } else if (searchBy === 'comment') {
                        searchCell = row.querySelector('td:nth-child(4)'); // Комментарий
                    } else if (searchBy === 'movie') {
                        searchCell = row.querySelector('td:nth-child(2)'); // Фильм
                    }

                    const cellText = searchCell.textContent.toLowerCase();
                    const isMatch = cellText.includes(searchText);

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
                commentCountElement.textContent = `Комментарии (${countText})`;
            });

            commentSearch.addEventListener('keyup', function(e) {
                if (this.value === '') {
                    commentRows.forEach(row => row.style.display = 'table-row');
                    noResultsMessage.style.display = 'none';
                    tableHeaders.style.display = 'table-header-group';
                    tableBody.style.display = 'table-row-group';
                    commentCountElement.textContent = `Комментарии (${originalCount})`;
                }
            });

            document.addEventListener('click', event => {
                const deleteBtn = event.target.closest('.editButton.delete');
                if (!deleteBtn) return;

                if (!confirm('Вы уверены, что хотите удалить комментарий?')) return;

                const row = deleteBtn.closest('tr');
                const commentId = row.dataset.id;

                delCommentAdmin(commentId, <?= $userid ?>);
            });

            document.querySelectorAll('tr').forEach(row => {
                row.addEventListener('click', (event) => {
                    if (event.target.closest('.buttons, a, button')) return;
                    if (row.id === 'headers') return;

                    window.location.href = "../movie.php?movie=" + row.dataset.movie;
                });
            });
        });
    </script>
</body>

</html>