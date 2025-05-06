<?php
session_start();
require_once('php/connect.php');

$isAuth;
if (isset($_SESSION['user'])) {
    $isAuth = true;
}

$searchQuery = isset($_GET['query']) ? $_GET['query'] : null;
$countryName = isset($_GET['country']) ? $_GET['country'] : null;
$genreName = isset($_GET['genre']) ? $_GET['genre'] : null;
$yearFrom = isset($_GET['year_from']) ? (int)$_GET['year_from'] : null;
$yearTo = isset($_GET['year_to']) ? (int)$_GET['year_to'] : null;
$directorId = isset($_GET['director_id']) ? (int)$_GET['director_id'] : null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$sql = "SELECT * FROM get_filtered_movies(";
$params = [];
$paramValues = [];

if ($countryName !== null) {
    $params[] = "p_country_name => $" . (count($params) + 1);
    $paramValues[] = $countryName;
}

if ($genreName !== null) {
    $params[] = "p_genre_name => $" . (count($params) + 1);
    $paramValues[] = $genreName;
}

if ($yearFrom !== null) {
    $params[] = "p_year_from => $" . (count($params) + 1);
    $paramValues[] = $yearFrom;
}

if ($yearTo !== null) {
    $params[] = "p_year_to => $" . (count($params) + 1);
    $paramValues[] = $yearTo;
}

if ($directorId !== null) {
    $params[] = "p_director_id => $" . (count($params) + 1);
    $paramValues[] = $directorId;
}

$params[] = "p_limit => $" . (count($params) + 1);
$paramValues[] = $limit;

$params[] = "p_offset => $" . (count($params) + 1);
$paramValues[] = $offset;

$sql .= implode(', ', $params) . ")";

if ($searchQuery !== null && $searchQuery !== '') {
    $sql .= " WHERE title ILIKE $" . (count($paramValues) + 1);
    $paramValues[] = '%' . $searchQuery . '%';
}

$result = pg_query_params($connection, $sql, $paramValues);

$countries = pg_fetch_all(pg_query($connection, 'SELECT country_name AS name FROM country ORDER BY country_name'));
$directors = pg_fetch_all(pg_query($connection, 'SELECT id, name FROM director ORDER BY name'));
$genres = pg_fetch_all(pg_query($connection, 'SELECT genre AS name FROM genre ORDER BY genre'));

function safeEcho($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

if (isset($isAuth)) {
    $userid = $_SESSION['user']['id'];
    $recommendations = pg_query_params($connection, 'SELECT * FROM get_movie_recommendations($1)', array($userid));
}

$dialogError = (($_SESSION['message']['message_type'] ?? null) == 'success' || ($_SESSION['message']['message_type'] ?? null) == 'error')
    ? ($_SESSION['message']['message_dialog'] ?? null)
    : null;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>КиноРадар | Главная</title>
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="movies.css">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="dialog.css">
</head>

<body>
    <dialog id="modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="formTitle">Вход</h2>
            <div id="authForm" class="authForm">
                <form id="loginForm" class="form" action="php/login" method="post">
                    <div class="form-part">
                        <label for="username">Имя пользователя</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-part">
                        <label for="password">Пароль</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-part submit">
                        <button type="submit">Войти</button>
                    </div>
                    <?php if (isset($_SESSION['message']['message_desc']) && $dialogError == 'modal' && $_SESSION['message']['message_type'] != 'success') { ?>
                        <p class="message"><?= $_SESSION['message']['message_desc'] ?></p>
                    <?php
                        unset($_SESSION['message']);
                    } else if (isset($_SESSION['message']['message_desc']) && $dialogError == 'modal' && $_SESSION['message']['message_type'] == 'success') {
                    ?>
                        <p class="message" style="color: green;"><?= $_SESSION['message']['message_desc'] ?></p>
                    <?php unset($_SESSION['message']);
                    } ?>
                    <p id="toggleText">Нет аккаунта? <button type="button" id="toggleBtn">Зарегистрироваться</button></p>
                </form>
                <form id="registrationForm" class="form" action="php/registration" method="post" style="display:none;">
                    <div class="form-part">
                        <label for="username">Имя пользователя:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-part">
                        <label for="email">Почта:</label>
                        <input type="text" id="email" name="email" required>
                    </div>
                    <div class="form-part">
                        <label for="password">Пароль:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-part">
                        <label for="checkPassword">Подтвердите пароль:</label>
                        <input type="password" id="checkPassword" name="checkPassword" required>
                    </div>
                    <div class="form-part submit">
                        <button type="submit">Зарегистрироваться</button>
                    </div>
                    <?php if (isset($_SESSION['message']['message_desc']) && $dialogError == 'passwordModal') { ?>
                        <p class="message"><?= $_SESSION['message']['message_desc'] ?></p>
                    <?php
                        unset($_SESSION['message']);
                    }
                    ?>
                    <p id="toggleTextReg">Уже есть аккаунт? <button type="button" id="toggleBtnReg">Войти</button></p>
                </form>
            </div>
        </div>
    </dialog>

    <?php
    include("header.php")
    ?>

    <?php if (isset($isAuth)) { ?>
        <div class="recomendations">
            <div class="wrapper">
                <div class="panel">
                    <h2>Ваши рекомендации</h2>
                </div>
                <div class="movies" id="movies">
                    <?php
                    while ($row = pg_fetch_assoc($recommendations)) {
                        $movieid = $row["movie_id"];
                        $isFav = false;

                        if (isset($_SESSION['user'])) {
                            $query = "SELECT * FROM $schema_name.favorite WHERE user_id = $userid AND movie_id = $movieid";
                            $checkFav = pg_query_params($connection, $query, array());
                            $isFav = pg_num_rows($checkFav) > 0;
                        }
                    ?>
                        <a class="movie_href" href="movie.php?movie=<?= $movieid ?>">
                            <div class="movie">
                                <?php if (isset($_SESSION["user"])): ?>
                                    <button class="addFav" data-id="<?= $movieid ?>">
                                        <img src="media/<?= $isFav ? 'heart-fill' : 'heart' ?>.svg" alt="">
                                    </button>
                                <?php endif; ?>
                                <img src="<?= safeEcho($row["poster_image"]) ?>" alt="<?= safeEcho($row["title"]) ?>">
                                <div class="title"><?= safeEcho($row["title"] . " (" . $row["year"] . ")") ?></div>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php }; ?>

    <main>
        <h2>Поиск по сайту</h2>
        <?php if (isset($_GET["query"]) || isset($_GET["year_from"]) || isset($_GET["year_to"]) || isset($_GET["country"]) || isset($_GET["director_id"]) || isset($_GET["genre"])): ?>
            <div class="filter-info">
                <?php if ($result && pg_num_rows($result) === 0): ?>
                    <div class="no-results">По заданным фильтрам фильмов не найдено</div>
                <?php endif; ?>
                <a style="color: coral; font-weight: 600;" href="<?= $_SERVER['PHP_SELF'] ?>">Сбросить фильтры (<?= count($_GET) ?>)</a>
            </div>
        <?php endif; ?>
        <div class="wrapper">
            <div class="filter-wrapper">
                <div class="filter">
                    <div>
                        <h3>Фильтр</h3>
                    </div>

                    <form method="get" action="" id="filter-form">
                        <div class="part search-part">
                            <label for="query">Поиск</label>
                            <input type="text" name="query" id="query" placeholder="Введите название фильма" value="<?= safeEcho($_GET['query'] ?? '') ?>">
                        </div>

                        <div class="part">
                            <label for="year">Год</label>
                            <div class="interval">
                                <div class="dropdown">
                                    <input type="text" id="year-from-display" placeholder="От" class="with-arrow" value="<?= safeEcho($_GET['year_from'] ?? '') ?>">
                                    <input type="hidden" name="year_from" id="year-from" value="<?= safeEcho($_GET['year_from'] ?? '') ?>">
                                </div>
                                <div class="separator"></div>
                                <div class="dropdown">
                                    <input type="text" id="year-to-display" placeholder="До" class="with-arrow" value="<?= safeEcho($_GET['year_to'] ?? '') ?>">
                                    <input type="hidden" name="year_to" id="year-to" value="<?= safeEcho($_GET['year_to'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="part">
                            <label for="country">Страна</label>
                            <div class="dropdown">
                                <input type="text" id="country-display" placeholder="Выберите страну" class="with-arrow" value="<?= safeEcho($_GET['country'] ?? '') ?>">
                                <input type="hidden" name="country" id="country" value="<?= safeEcho($_GET['country'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="part">
                            <label for="director">Режиссёр</label>
                            <div class="dropdown">
                                <?php
                                $directorName = '';
                                if (isset($_GET['director_id'])) {
                                    foreach ($directors as $director) {
                                        if ($director['id'] == $_GET['director_id']) {
                                            $directorName = $director['name'];
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <input type="text" id="director-display" placeholder="Выберите режиссёра" class="with-arrow" value="<?= safeEcho($directorName) ?>">
                                <input type="hidden" name="director_id" id="director" value="<?= safeEcho($_GET['director_id'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="part">
                            <label for="genre">Жанр</label>
                            <div class="dropdown">
                                <input type="text" id="genre-display" placeholder="Выберите жанр" class="with-arrow" value="<?= safeEcho($_GET['genre'] ?? '') ?>">
                                <input type="hidden" name="genre" id="genre" value="<?= safeEcho($_GET['genre'] ?? '') ?>">
                            </div>
                        </div>

                        <div>
                            <button type="submit">Применить</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Список фильмов -->
            <div class="movies" id="movies">
                <?php
                while ($row = pg_fetch_assoc($result)) {
                    $movieid = $row["movie_id"];
                    $isFav = false;

                    if (isset($_SESSION['user'])) {
                        $query = "SELECT * FROM $schema_name.favorite WHERE user_id = $userid AND movie_id = $movieid";
                        $checkFav = pg_query_params($connection, $query, array());
                        $isFav = pg_num_rows($checkFav) > 0;
                    }
                ?>
                    <a class="movie_href" href="movie.php?movie=<?= $movieid ?>">
                        <div class="movie">
                            <?php if (isset($_SESSION["user"])): ?>
                                <button class="addFav" data-id="<?= $movieid ?>">
                                    <img src="media/<?= $isFav ? 'heart-fill' : 'heart' ?>.svg" alt="">
                                </button>
                            <?php endif; ?>
                            <img src="<?= safeEcho($row["poster_image"]) ?>" alt="<?= safeEcho($row["title"]) ?>">
                            <div class="title"><?= safeEcho($row["title"] . " (" . $row["year"] . ")") ?></div>
                        </div>
                    </a>
                <?php } ?>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="modal.js"></script>
    <script src="script.js"></script>
    <script src="burger.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sampleData = {
                years: Array.from({
                    length: 50
                }, (_, i) => ({
                    id: 2023 - i,
                    name: (2023 - i).toString()
                })),
                countries: <?= json_encode($countries ?? []); ?>,
                directors: <?= json_encode($directors ?? []); ?>,
                genres: <?= json_encode($genres ?? []); ?>
            };

            function initDropdown(displayInputId, hiddenInputId, items, isNameAsId = false) {
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
                            div.dataset.id = isNameAsId ? item.name : item.id;
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
                        hiddenInput.value = isNameAsId ? foundItem.name : foundItem.id;
                    } else {
                        hiddenInput.value = '';
                    }
                    updateDropdownItems(inputValue);
                    dropdown.classList.add('active');
                });

                dropdownContent.addEventListener('click', function(e) {
                    if (e.target.tagName === 'DIV') {
                        displayInput.value = e.target.textContent;
                        hiddenInput.value = e.target.dataset.id;
                        dropdown.classList.remove('active');
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target)) {
                        dropdown.classList.remove('active');
                    }
                });
            }

            initDropdown('year-from-display', 'year-from', sampleData.years);
            initDropdown('year-to-display', 'year-to', sampleData.years);
            initDropdown('country-display', 'country', sampleData.countries, true);
            initDropdown('director-display', 'director', sampleData.directors);
            initDropdown('genre-display', 'genre', sampleData.genres, true);

            document.getElementById('filter-form').addEventListener('submit', function(e) {
                const form = e.target;
                const inputs = form.querySelectorAll('input[type="hidden"], input[type="text"]');

                inputs.forEach(input => {
                    if (!input.value && input.type === 'hidden') {
                        input.removeAttribute('name');
                    }
                });
            });
        });
    </script>

    <script>
        document.querySelectorAll('.addFav').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                movieid = this.dataset.id;
                const img = this.querySelector('img');
                favorite(movieid, img)

                img.classList.add('pulse');

                setTimeout(() => {
                    img.classList.remove('pulse');
                }, 300);
            });
        });
    </script>

    <script>
        function showModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.showModal();
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) modal.close();
                });
            });

            <?php if ($dialogError != null): ?>
                showModal('<?= $dialogError ?>');
            <?php endif; ?>
        });
    </script>
</body>

</html>

<pre>
<?php
?>
</pre>