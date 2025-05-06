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

$countries = pg_fetch_all(pg_query($connection, 'SELECT id, country_name AS name FROM country ORDER BY country_name'));
$directors = pg_fetch_all(pg_query($connection, 'SELECT id, name FROM director ORDER BY name'));
$genres = pg_fetch_all(pg_query($connection, 'SELECT id, genre AS name FROM genre ORDER BY genre'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления</title>

    <link rel="stylesheet" href="../admin.css">
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
            <h2>Добавление фильма</h2>
            <div class="separator"></div>
            <form action="../php/addMovie" method="post" enctype="multipart/form-data">
                <div class="form-part">
                    <label for="title">Название фильма</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-part">
                    <label for="year">Год выпуска</label>
                    <input type="text" id="year" name="year" required maxlength="4">
                </div>
                <div class="form-part">
                    <label for="description">Описание</label>
                    <textarea rows="5" cols="50" id="description" name="description" required></textarea>
                </div>
                <div class="form-part">
                    <label for="original_title">Оригинальное название фильма</label>
                    <input type="text" id="original_title" name="original_title" required>
                </div>
                <div class="form-part">
                    <label for="country">Страна</label>
                    <div class="dropdown">
                        <input type="text" id="country-display" placeholder="Выберите страну" class="with-arrow">
                        <input type="hidden" name="country_id" id="country">
                    </div>
                </div>
                <div class="form-part">
                    <label for="director">Режиссёр</label>
                    <div class="dropdown">
                        <input type="text" id="director-display" placeholder="Выберите режиссёра" class="with-arrow">
                        <input type="hidden" name="director_id" id="director">
                    </div>
                </div>
                <div class="form-part">
                    <label for="genres">Жанр(ы)</label>
                    <div class="dropdown">
                        <input type="text" id="genre-display" placeholder="Выберите жанр" class="with-arrow">
                        <input type="hidden" name="genres" id="genres">
                    </div>
                </div>
                <div class="form-part">
                    <label for="poster">Постер</label>
                    <div class="file-upload">
                        <label for="poster" class="file-upload-label">
                            Выбрать файл
                        </label>
                        <span class="file-name" id="file-name">Файл не выбран</span>
                    </div>
                    <input type="file" id="poster" name="poster" required accept="image/*">
                </div>
                <div class="form-part">
                    <label for="link">Ссылка</label>
                    <input type="text" id="link" name="link" required>
                </div>
                <div class="form-part submit">
                    <button type="submit">Добавить</button>
                </div>
            </form>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sampleData = {
                countries: <?= json_encode($countries ?? []); ?>,
                directors: <?= json_encode($directors ?? []); ?>,
                genres: <?= json_encode($genres ?? []); ?>
            };

            function initDropdown(displayInputId, hiddenInputId, items, isMultiple = false) {
                const displayInput = document.getElementById(displayInputId);
                const hiddenInput = document.getElementById(hiddenInputId);
                const dropdown = displayInput.closest('.dropdown');
                const dropdownContent = document.createElement('div');
                dropdownContent.className = 'dropdown-content';
                dropdown.appendChild(dropdownContent);

                const clearButton = document.createElement('button');
                clearButton.textContent = '×';
                clearButton.className = 'clear-button';
                clearButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    displayInput.value = '';
                    hiddenInput.value = '';
                    dropdown.classList.remove('active');
                });
                dropdown.appendChild(clearButton);

                function updateDropdownItems(filterText = '') {
                    dropdownContent.innerHTML = '';
                    const filteredItems = items.filter(item =>
                        item.name.toLowerCase().includes(filterText.toLowerCase())
                    );

                    if (filteredItems.length === 0) {
                        const div = document.createElement('div');
                        div.textContent = 'Ничего не найдено';
                        div.style.color = '#999';
                        div.style.cursor = 'default';
                        dropdownContent.appendChild(div);
                    } else {
                        filteredItems.forEach(item => {
                            const div = document.createElement('div');
                            div.textContent = item.name;
                            div.dataset.id = item.id;
                            dropdownContent.appendChild(div);
                        });
                    }
                }

                displayInput.addEventListener('click', function() {
                    dropdown.classList.toggle('active');
                    updateDropdownItems();
                });

                displayInput.addEventListener('input', function() {
                    const inputValue = this.value;
                    const foundItem = items.find(item => item.name.toLowerCase() === inputValue.toLowerCase());
                    if (foundItem) {
                        hiddenInput.value = foundItem.id;
                    } else {
                        hiddenInput.value = '';
                    }
                    updateDropdownItems(inputValue);
                    dropdown.classList.add('active');
                });

                dropdownContent.addEventListener('click', function(e) {
                    if (e.target.tagName === 'DIV') {
                        if (isMultiple) {
                            const selectedValues = hiddenInput.value ? hiddenInput.value.split(',') : [];
                            const selectedNames = displayInput.value ? displayInput.value.split(', ') : [];
                            const selectedId = e.target.dataset.id;
                            const selectedName = e.target.textContent;

                            if (!selectedValues.includes(selectedId)) {
                                if (selectedValues.length < 3) {
                                    selectedValues.push(selectedId);
                                    selectedNames.push(selectedName);
                                    hiddenInput.value = selectedValues.join(',');
                                    displayInput.value = selectedNames.join(', ');
                                }
                            } else {
                                const index = selectedValues.indexOf(selectedId);
                                selectedValues.splice(index, 1);
                                selectedNames.splice(index, 1);
                                hiddenInput.value = selectedValues.join(',');
                                displayInput.value = selectedNames.join(', ');
                            }
                        } else {
                            displayInput.value = e.target.textContent;
                            hiddenInput.value = e.target.dataset.id;
                            dropdown.classList.remove('active');
                        }
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target)) {
                        dropdown.classList.remove('active');
                    }
                });
            }

            initDropdown('country-display', 'country', sampleData.countries);
            initDropdown('director-display', 'director', sampleData.directors);
            initDropdown('genre-display', 'genres', sampleData.genres, true);
        });
    </script>

    <script>
        document.getElementById('poster').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Файл не выбран';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>

    <script>
        const yearInput = document.getElementById('year');

        yearInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });

        yearInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text');
            this.value = text.replace(/\D/g, '');
        });

        yearInput.addEventListener('drop', function(e) {
            e.preventDefault();
            const text = e.dataTransfer.getData('text');
            this.value = text.replace(/\D/g, '');
        });
    </script>
</body>

</html>