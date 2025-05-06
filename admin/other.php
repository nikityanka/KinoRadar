<?php
session_start();
require_once('../php/connect.php');

if (isset($_SESSION['user']) && $_SESSION['user']['type'] == 2) {
    include('../error.php');
    exit;
}
$countries = pg_query($connection, "SELECT * FROM $schema_name.country ORDER BY id");
$directors = pg_query($connection, "SELECT * FROM $schema_name.director ORDER BY id");
$genres = pg_query($connection, "SELECT * FROM $schema_name.genre ORDER BY id");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width" />
    <title>Панель управления</title>
    <link rel="stylesheet" href="../header.css" />
    <link rel="stylesheet" href="../admin.css" />
    <link rel="stylesheet" href="../dialog.css" />
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
                            <img src="../media/dashboard.svg" alt="" />
                        </div>
                        <span>Главная</span>
                    </li>
                </a>
                <a href="users.php">
                    <li>
                        <div class="icon">
                            <img src="../media/profile.svg" alt="" />
                        </div>
                        <span>Пользователи</span>
                    </li>
                </a>
                <a href="movies.php">
                    <li>
                        <div class="icon">
                            <img src="../media/movie_white.svg" alt="" />
                        </div>
                        <span>Фильмы</span>
                    </li>
                </a>
                <a href="comments.php">
                    <li>
                        <div class="icon">
                            <img src="../media/comment.svg" alt="" />
                        </div>
                        <span>Комментарии</span>
                    </li>
                </a>
                <a href="other.php" class="active">
                    <li>
                        <div class="icon">
                            <img src="../media/genres.svg" alt="" />
                        </div>
                        <span>Данные</span>
                    </li>
                </a>
            </ul>
        </nav>
        <div class="main-wrapper">
            <h2>Данные</h2>
            <div class="separator"></div>
            <div class="data-collection">
                <div class="data">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h4>Страны (<?= pg_num_rows($countries) ?>)</h4>
                        <button onclick="addRow(this)">
                            <img src="../media/plus.svg" alt="" />
                        </button>
                    </div>
                    <div class="separator"></div>
                    <div class="table" id="countriesTable">
                        <?php $counter = 1;
                        while ($row = pg_fetch_assoc($countries)) {
                        ?>
                            <div class="row" data-id="<?= $row['id'] ?>">
                                <div>
                                    <span class="counter"><?= $counter ?>. </span>
                                    <span class="name"><?= $row['country_name'] ?></span>
                                </div>
                                <div class="icon delData" style="width: 20px;">
                                    <img src="../media/krestik.svg" alt="">
                                </div>
                            </div>
                        <? $counter++;
                        } ?>
                    </div>
                </div>
                <div class="data">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h4>Режиссёры (<?= pg_num_rows($directors) ?>)</h4>
                        <button onclick="addRow(this)">
                            <img src="../media/plus.svg" alt="" />
                        </button>
                    </div>
                    <div class="separator"></div>
                    <div class="table" id="directorsTable">
                        <?php $counter = 1;
                        while ($row = pg_fetch_assoc($directors)) {
                        ?>
                            <div class="row" data-id="<?= $row['id'] ?>">
                                <div>
                                    <span class="counter"><?= $counter ?>. </span>
                                    <span class="name"><?= $row['name'] ?></span>
                                </div>
                                <div class="icon delData" style="width: 20px;">
                                    <img src="../media/krestik.svg" alt="">
                                </div>
                            </div>
                        <? $counter++;
                        } ?>
                    </div>
                </div>
                <div class="data">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h4>Жанры (<?= pg_num_rows($genres) ?>)</h4>
                        <button onclick="addRow(this)">
                            <img src="../media/plus.svg" alt="" />
                        </button>
                    </div>
                    <div class="separator"></div>
                    <div class="table" id="genresTable">
                        <?php $counter = 1;
                        while ($row = pg_fetch_assoc($genres)) {

                        ?>
                            <div class="row" data-id="<?= $row['id'] ?>">
                                <div>
                                    <span class="counter"><?= $counter ?>. </span>
                                    <span class="name"><?= $row['genre'] ?></span>
                                </div>
                                <div class="icon delData" style="width: 20px;">
                                    <img src="../media/krestik.svg" alt="">
                                </div>
                            </div>
                        <? $counter++;
                        } ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delData').forEach(icon => {
                icon.addEventListener('click', function() {
                    const row = this.closest('.row');
                    const value = row.querySelector('.name').innerText;
                    const table = this.closest('div.table');
                    const tableId = table.id;
                    let dbTableName;

                    switch (tableId) {
                        case 'countriesTable':
                            dbTableName = 'countries';
                            break;
                        case 'directorsTable':
                            dbTableName = 'directors';
                            break;
                        case 'genresTable':
                            dbTableName = 'genres';
                            break;
                        default:
                            dbTableName = '';
                    }

                    row.remove();
                    console.log(value)
                    delData(value, dbTableName);
                });
            });
        });

        function addRow(button) {
            const table = button.parentNode.parentNode.querySelector('.table');
            const tableId = table.id;

            if (document.querySelector('.new-row')) {
                return;
            }

            const newRow = document.createElement('div');
            newRow.classList.add('row', 'new-row');

            const rowContent = document.createElement('div');
            rowContent.style.display = 'flex';
            rowContent.style.alignItems = 'center';

            const counterSpan = document.createElement('span');
            counterSpan.classList.add('counter');
            counterSpan.textContent = (table.children.length + 1) + '. ';

            const inputField = document.createElement('input');
            inputField.type = 'text';
            inputField.style.margin = '0 5px';

            const buttonsContainer = document.createElement('div');
            buttonsContainer.style.display = 'flex';

            const saveButton = document.createElement('button');
            saveButton.innerHTML = '<img src="../media/mark.svg" alt="Сохранить" />';
            saveButton.onclick = function() {
                let dbTableName;
                switch (tableId) {
                    case 'countriesTable':
                        dbTableName = 'countries';
                        break;
                    case 'directorsTable':
                        dbTableName = 'directors';
                        break;
                    case 'genresTable':
                        dbTableName = 'genres';
                        break;
                    default:
                        dbTableName = '';
                }
                saveRow(newRow, inputField.value, dbTableName);
            };

            const cancelButton = document.createElement('button');
            cancelButton.innerHTML = '<img src="../media/krestik.svg" alt="Отменить" />';
            cancelButton.onclick = function() {
                table.removeChild(newRow);
            };

            buttonsContainer.appendChild(saveButton);
            buttonsContainer.appendChild(cancelButton);

            rowContent.appendChild(counterSpan);
            rowContent.appendChild(inputField);
            newRow.appendChild(rowContent);
            newRow.appendChild(buttonsContainer);

            table.insertBefore(newRow, table.firstChild);

        }

        function saveRow(row, value, tableName) {
            if (!value.trim()) {
                alert('Введите название.');
                return;
            }

            const counterText = row.querySelector('.counter').textContent;
            row.classList.remove('new-row');
            row.innerHTML = `
        <div>
            <span class="counter">${counterText}</span>
            <span class="name">${value}</span>
        </div>
        <div class="icon" style="width: 20px;">
            <img src="../media/krestik.svg" alt="">
        </div>
    `;

            const table = row.parentNode;
            table.appendChild(row);

            row.scrollIntoView({
                behavior: 'smooth',
                block: 'end'
            });


            addData(value, tableName);
        }
    </script>
</body>

</html>