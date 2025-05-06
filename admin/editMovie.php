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


$movieid = $_POST["movieid"];

$query = "SELECT * FROM get_movie_details($1)";
$result = pg_query_params($connection, $query, array($movieid));

$result = pg_fetch_assoc($result);

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
            <h2>Изменение фильма</h2>
            <div class="separator"></div>
            <form action="../php/editMovie" method="post" enctype="multipart/form-data">
                <input type="hidden" name="movie_id" value="<?= $result['movie_id'] ?>">
                <div class="form-part">
                    <label for="title">Название фильма</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($result['movie_title']) ?>" required>
                </div>
                <div class="form-part">
                    <label for="year">Год выпуска</label>
                    <input type="text" id="year" name="year" value="<?= htmlspecialchars($result['release_year']) ?>" required maxlength="4">
                </div>
                <div class="form-part">
                    <label for="description">Описание</label>
                    <textarea rows="5" cols="50" id="description" name="description" required><?= htmlspecialchars($result['movie_description']) ?></textarea>
                </div>
                <div class="form-part">
                    <label for="original_title">Оригинальное название фильма</label>
                    <input type="text" id="original_title" name="original_title" value="<?= htmlspecialchars($result['original_title']) ?>" required>
                </div>
                <div class="form-part">
                    <label for="country">Страна</label>
                    <div class="dropdown">
                        <input type="text" id="country-display" placeholder="Выберите страну" class="with-arrow" value="<?= htmlspecialchars($result['country_name']) ?>">
                        <input type="hidden" name="country_id" id="country" value="<?= array_values(array_filter($countries, function ($c) use ($result) {
                                                                                        return $c['name'] === $result['country_name'];
                                                                                    }))[0]['id'] ?? '' ?>">
                    </div>
                </div>
                <div class="form-part">
                    <label for="director">Режиссёр</label>
                    <div class="dropdown">
                        <input type="text" id="director-display" placeholder="Выберите режиссёра" class="with-arrow" value="<?= htmlspecialchars($result['director_name']) ?>">
                        <input type="hidden" name="director_id" id="director" value="<?= $result['director_id'] ?>">
                    </div>
                </div>
                <div class="form-part">
                    <label for="genres">Жанр(ы)</label>
                    <div class="dropdown">
                        <input type="text" id="genre-display" placeholder="Выберите жанр" class="with-arrow" value="<?= implode(', ', array_map(function ($gid) use ($genres) {
                                                                                                                        $genre = array_values(array_filter($genres, function ($g) use ($gid) {
                                                                                                                            return $g['id'] == $gid;
                                                                                                                        }))[0] ?? [];
                                                                                                                        return $genre['name'] ?? '';
                                                                                                                    }, explode(',', trim($result['movie_genres'], '{}')))) ?>">
                        <input type="hidden" name="genre_ids" id="genres" value="<?= implode(',', explode(',', trim($result['movie_genres'], '{}'))) ?>">
                    </div>
                </div>
                <div class="form-part">
                    <div class="file-upload">
                        <label for="avatar" class="file-upload-label">
                            Выбрать файл
                        </label>
                        <span class="file-name" id="file-name"><?= basename($result['poster_image']) ?></span>
                    </div>
                    <input type="file" id="avatar" name="avatar" accept="image/*">
                    <input type="hidden" name="existing_poster" value="<?= $result['poster_image'] ?>">
                </div>
                <div class="form-part">
                    <label for="link">Ссылка</label>
                    <input type="text" id="link" name="link" value="<?= htmlspecialchars($result['movie_link']) ?>" required>
                </div>
                <div class="form-part submit">
                    <button type="submit">Сохранить изменения</button>
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

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                if (document.getElementById('country').value) {
                    const countryId = document.getElementById('country').value;
                    const country = sampleData.countries.find(c => c.id == countryId);
                    if (country) {
                        document.getElementById('country-display').value = country.name;
                    }
                }

                if (document.getElementById('director').value) {
                    const directorId = document.getElementById('director').value;
                    const director = sampleData.directors.find(d => d.id == directorId);
                    if (director) {
                        document.getElementById('director-display').value = director.name;
                    }
                }

                if (document.getElementById('genres').value) {
                    const genreIds = document.getElementById('genres').value.split(',');
                    const genreNames = genreIds.map(id => {
                        const genre = sampleData.genres.find(g => g.id == id);
                        return genre ? genre.name : '';
                    }).filter(name => name);
                    document.getElementById('genre-display').value = genreNames.join(', ');
                }
            }, 100);
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