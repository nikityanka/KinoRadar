<?php
session_start();
require_once('../php/connect.php');

if (isset($_SESSION['user']) && $_SESSION['user']['type'] == 2) {
    include('../error.php');
    exit;
}

$users = pg_query($connection, "SELECT * FROM $schema_name.user_profile_view ORDER BY id");
$count = pg_num_rows($users);

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
                <a href="./">
                    <li>
                        <div class="icon">
                            <img src="../media/dashboard.svg" alt="">
                        </div>
                        <span>Главная</span>
                    </li>
                </a>
                <a href="users.php" class="active">
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
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Пользователи (<?= $count ?>)</h2>
                <div class="search-container" style="margin: 15px 0;">
                    <input type="text" id="userSearch" placeholder="Поиск по имени..." style="padding: 8px; width: 300px;">
                </div>
                <div></div>
            </div>
            <div class="separator"></div>
            <div class="table-div">
                <table class="users-collection">
                    <thead id="tableHeaders">
                        <tr id="headers">
                            <th width="10%">ID</th>
                            <th width="25%">Аватар</th>
                            <th width="15%">Имя пользователя</th>
                            <th width="20%">Тип пользователя</th>
                            <th width="20%">Описание</th>
                            <th width="25%"></th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php $counter = 1;
                        while ($row = pg_fetch_assoc($users)) {
                            if ($row['avatar'] == 'media/default_avatar.jpg') {
                                $avatar = "../media/default_avatar.jpg";
                            } else {
                                $avatar = $row['avatar'];
                            }
                        ?>
                            <tr data-id="<?= $row['id'] ?>">
                                <td>#<?= $counter ?></td>
                                <td class="avatar">
                                    <img style="width: 30%;" src=<?= $avatar ?> alt="">
                                </td>
                                <td><?= $row['username'] ?></td>
                                <td><?= $row['type_name'] ?></td>
                                <?php
                                if (isset($row['about']) && !empty($row['about'])) { ?>
                                    <td class="avatar">
                                        <img class="mark" width="15%" src="../media/mark.svg" alt="Есть">
                                    </td>
                                    <td>
                                        <div class="buttons" style="flex-direction: column;">
                                            <div class="editButton delete enabled" title="Очистить описание">
                                                <img src="../media/bucket.svg" id="edit_button">
                                                <span>Очистить описание</span>
                                            </div>

                                            <a href="../profile.php?userid=<?= $row['id'] ?>">
                                                <div class="editButton enter" title="Открыть профиль">
                                                    <img src="../media/link.svg" id="edit_button">
                                                    <span>Перейти</span>
                                                </div>
                                            </a>
                                        </div>
                                    </td>
                                <?php
                                } else { ?>
                                    <td class="avatar">
                                        <img class="mark" width="15%" src="../media/krestik.svg" alt="Нет">
                                    </td>
                                    <td>
                                        <div class="buttons" style="flex-direction: column;">
                                            <div class="editButton delete" title="Очистить описание" style="opacity: .3;">
                                                <img src="../media/bucket.svg" id="edit_button">
                                                <span>Очистить описание</span>
                                            </div>

                                            <a href="../profile.php?userid=<?= $row['id'] ?>">
                                                <div class="editButton enter" title="Открыть профиль">
                                                    <img src="../media/link.svg" id="edit_button">
                                                    <span>Перейти</span>
                                                </div>
                                            </a>
                                        </div>
                                    </td>
                                <?php
                                }
                                ?>
                            </tr>
                        <? $counter++;
                        } ?>
                    </tbody>
                </table>
                <div id="noResults" class="no-results-message" style="display: none;">
                    Пользователи не найдены
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userSearch = document.getElementById('userSearch');
            const userRows = document.querySelectorAll('.users-collection tbody tr');
            const userCountElement = document.querySelector('h2');
            const noResultsMessage = document.getElementById('noResults');
            const tableHeaders = document.getElementById('tableHeaders');
            const tableBody = document.getElementById('tableBody');
            const originalCount = <?= $count ?>;

            userSearch.addEventListener('input', function() {
                const searchText = this.value.trim().toLowerCase();
                let visibleCount = 0;

                userRows.forEach(row => {
                    const usernameCell = row.querySelector('td:nth-child(3)');
                    const username = usernameCell.textContent.toLowerCase();
                    const isMatch = username.includes(searchText);

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
                userCountElement.textContent = `Пользователи (${countText})`;
            });

            userSearch.addEventListener('keyup', function(e) {
                if (this.value === '') {
                    userRows.forEach(row => row.style.display = 'table-row');
                    noResultsMessage.style.display = 'none';
                    tableHeaders.style.display = 'table-header-group';
                    tableBody.style.display = 'table-row-group';
                    userCountElement.textContent = `Пользователи (${originalCount})`;
                }
            });

            document.querySelector('table').addEventListener('click', event => {
                const deleteBtn = event.target.closest('.enabled');
                if (!deleteBtn) return;

                if (!confirm('Вы уверены, что хотите очистить описание профиля?')) return;

                const row = deleteBtn.closest('tr');
                const userId = row.dataset.id;

                delUserInfo(userId, row);
            });

            document.querySelectorAll('tr').forEach(row => {
                row.addEventListener('click', (event) => {
                    if (event.target.closest('.buttons, a, button')) return;

                    if (row.id === 'headers') return;

                    window.location.href = "../profile.php?userid=" + row.dataset.id;
                });
            });
        });
    </script>
</body>

</html>

<pre style="margin-left: 300px;">
    <?php
    ?>
</pre>