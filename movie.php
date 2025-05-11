<?php
session_start();
require_once('php/connect.php');

$movieid = $_GET["movie"];

$query = "SELECT * FROM get_movie_details($1)";
$result = pg_query_params($connection, $query, array($movieid));

if (!$result || pg_num_rows($result) == 0) {
    include('./error.php');
    exit;
}

$result = pg_fetch_assoc($result);

$genres = trim($result["movie_genres"], '{}');
$genresArray = array_map('intval', explode(',', $genres));

$genresWordsArray = [];
foreach ($genresArray as $genre) {
    $item = pg_fetch_assoc(pg_query($connection, "SELECT genre FROM $schema_name.genre WHERE id = '$genre'"))["genre"];
    $genresWordsArray[] = $item;
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
    <title><?= $result["movie_title"] ?> (<?= $result["release_year"] ?>)</title>

    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="movie.css">
    <link rel="stylesheet" href="dialog.css">
</head>

<body>
    <?php
    include("header.php")
    ?>

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
                    <?php if (isset($_SESSION['message']['message_desc']) && $dialogError == 'modal') { ?>
                        <p class="message"><?= $_SESSION['message']['message_desc'] ?></p>
                    <?php
                        unset($_SESSION['message']);
                    }
                    ?>
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

    <main>
        <div class="movie-about">
            <div class="movie-about__photo">
                <?php
                $rating = round($result['avg_rating'], 2);
                $rotation = round(($rating / 5) * 100);
                ?>
                <img src="<?= $result["poster_image"] ?>" alt="">
                <div class="userRaing">
                    <div class="progress-bar" style="--value: <?= $rotation ?>;" data-number="<?= $rating ?>">
                        <progress min="0" max="100" style="visibility:hidden; height:0; width:0;"></progress>
                    </div>
                </div>
                <h4>Оценка пользователей</h4>
            </div>



            <div class="movie-about__info">
                <div class="movie-about__info__main">
                    <h2><?= $result["movie_title"] ?></h2>
                    <p><?= $result["movie_description"] ?></p>
                </div>
                <div class="movie-about__info__secondary">
                    <p>Оригинальное название: <?= $result["original_title"] ?></p>
                    <p>Год выхода: <a class="filter"
                            href="./?year_from=<?= $result['release_year'] ?>&year_to=<?= $result['release_year'] ?>"><?= $result['release_year'] ?></a></p>
                    <p>Страна: <a class="filter"
                            href="./?country=<?= $result["country_name"] ?>"><?= $result["country_name"] ?></a></p>
                    <p>Режиссер: <a class="filter"
                            href="./?director_id=<?= $result["director_id"] ?>"><?= $result["director_name"] ?></a></p>
                    <div class="genres">
                        <p>Жанр: </p>
                        <?php
                        $count = count($genresWordsArray);
                        foreach ($genresWordsArray as $index => $genre) {
                        ?>
                            <a class="filter" href="./?genre=<?= $genre ?>"><?= $genre ?></a>
                            <?php if ($index < $count - 1) { ?>
                                <p> || </p>
                            <?php } ?>
                        <?php
                        }
                        ?>
                    </div>

                    <?php

                    if (isset($_SESSION['user'])) {
                        $userid = $_SESSION['user']['id'];

                        $userRatingQuery = "SELECT * FROM $schema_name.user_ratings_view WHERE user_id = $1 AND movie_id = $2;";
                        $userRating = pg_query_params($connection, $userRatingQuery, array($userid, $movieid));

                        if (pg_num_rows($userRating) == 0) {
                    ?>
                            <div class="rating goRate">
                                <h3 style="text-align: center;">Оцените фильм:</h3>
                                <form action=""></form>
                                <form action="php/rating" method="post" id="goRateForm">
                                    <input type="hidden" name="userid" value="<?= $userid ?>">
                                    <input type="hidden" name="movieid" value="<?= $movieid ?>">

                                    <input type="submit" name="rating" value="5" id="star-5" style="display:none">
                                    <input type="submit" name="rating" value="4" id="star-4" style="display:none">
                                    <input type="submit" name="rating" value="3" id="star-3" style="display:none">
                                    <input type="submit" name="rating" value="2" id="star-2" style="display:none">
                                    <input type="submit" name="rating" value="1" id="star-1" style="display:none">

                                    <div class="rating-area">
                                        <label for="star-5" title="Оценка «5»"></label>
                                        <label for="star-4" title="Оценка «4»"></label>
                                        <label for="star-3" title="Оценка «3»"></label>
                                        <label for="star-2" title="Оценка «2»"></label>
                                        <label for="star-1" title="Оценка «1»"></label>
                                    </div>
                                </form>
                            </div>
                        <?php
                        } else {
                            $userRatingValue = pg_fetch_assoc($userRating)["rating"];
                        ?>
                            <div class="rating rated">
                                <h3>Ваша оценка:</h3>
                                <form action=""></form>
                                <form action="php/rating" method="post" id="ratingForm">
                                    <input type="hidden" name="userid" value="<?= $userid ?>">
                                    <input type="hidden" name="movieid" value="<?= $movieid ?>">

                                    <input type="submit" name="rating" value="5" id="star-5" style="display:none">
                                    <input type="submit" name="rating" value="4" id="star-4" style="display:none">
                                    <input type="submit" name="rating" value="3" id="star-3" style="display:none">
                                    <input type="submit" name="rating" value="2" id="star-2" style="display:none">
                                    <input type="submit" name="rating" value="1" id="star-1" style="display:none">

                                    <div class="rating-area">
                                        <label for="star-5" title="Оценка «5»" <?= ($userRatingValue >= 5) ? 'style="color: coral"' : '' ?>></label>
                                        <label for="star-4" title="Оценка «4»" <?= ($userRatingValue >= 4) ? 'style="color: coral"' : '' ?>></label>
                                        <label for="star-3" title="Оценка «3»" <?= ($userRatingValue >= 3) ? 'style="color: coral"' : '' ?>></label>
                                        <label for="star-2" title="Оценка «2»" <?= ($userRatingValue >= 2) ? 'style="color: coral"' : '' ?>></label>
                                        <label for="star-1" title="Оценка «1»" <?= ($userRatingValue >= 1) ? 'style="color: coral"' : '' ?>></label>
                                    </div>
                                </form>
                                <form action="php/rating" method="post">
                                    <input type="hidden" name="userid" value="<?= $userid ?>">
                                    <input type="hidden" name="movieid" value="<?= $movieid ?>">
                                    <input type="hidden" name="rating" value="0">
                                    <button type="submit" class="delete-rating" id="delRating" title="Удалить оценку">Удалить</button>
                                </form>
                            </div>
                    <?php
                        }
                    }
                    ?>

                    <div class="links">
                        <h2>Смотреть</h2>
                        <a class="link" target="_blank" href="<?= $result["movie_link"] ?>"><img
                                src="media/logo_kinopoisk.png" alt=""></a>
                    </div>

                </div>
            </div>
        </div>

        <div class="comments">
            <?php
            $query = "SELECT * FROM $schema_name.user_comments_view WHERE movie_id = $1;";
            $comments = pg_query_params($connection, $query, array($movieid));

            if (isset($_SESSION["user"]) && isset($_SESSION["user"]["id"])) {
                $user_comment = pg_query_params(
                    $connection,
                    "SELECT * FROM movie_search.user_comments_view WHERE movie_id = $1 AND user_id = $2;",
                    array($movieid, $userid)
                );
                $kol = pg_num_rows($user_comment);
            }
            ?>
            <?php if (pg_num_rows($comments) == 0) { ?>
                <h2>Комментариев пока нет, будьте первыми</h2>
            <? } else { ?>
                <h2>Комментарии (<?= pg_num_rows($comments) ?>)</h2>
            <? } ?>

            <div class="comments-collection">
                <div class="user-comment">
                    <?php
                    if (isset($_SESSION["user"]["id"])) {
                        if ($kol > 0) {
                            $user_comment = pg_fetch_assoc($user_comment);
                            $commentid = $user_comment["comment_id"];
                            $commentuser = $commentid;

                            $dateTime = new DateTime(datetime: $user_comment["comment_date"]);
                            $date = $dateTime->format('d.m.Y H:i');
                    ?>
                            <h3>Ваш комментарий</h3>
                            <div class="comment" data-id=<?= $commentuser ?>>
                                <div class="comment-title">
                                    <div class="user">
                                        <a class="user-link" href="profile?userid=<?= $user_comment["user_id"] ?>">
                                            <div class="avatar">
                                                <img src="<?= $user_comment["user_avatar"] ?>">
                                            </div>
                                        </a>
                                        <a class="user-link" href="profile?userid=<?= $user_comment["user_id"] ?>">
                                            <p class="username"><?= htmlspecialchars($user_comment["username"]) ?></p>
                                        </a>
                                    </div>
                                    <p class="comment__date"><?= htmlspecialchars($date) ?></p>
                                </div>
                                <p class="comment__text"><?= htmlspecialchars($user_comment["comment_text"]) ?></p>
                                <div class="buttons">
                                    <div class="buttons secondary" style="display: none;">
                                        <div class="button cancelButton">
                                            <img src="media/krestik.svg" id="save_button">
                                        </div>
                                        <div class="button saveButton">
                                            <img src="media/mark.svg" id="save_button">
                                        </div>
                                    </div>
                                    <div class="buttons primary">
                                        <div class="button editButton">
                                            <img src="media/pencil.svg" id="edit_button">
                                        </div>
                                        <div class="button delButton">
                                            <img src="media/bucket.svg" id="del_button">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <h3>Оставь свой отзыв</h3>
                            <form action="php/sendComment" class="send-comment" method="post">
                                <input type="hidden" name="movieid" value="<?= $movieid ?>">
                                <input type="hidden" name="userid" value="<?= $userid ?>">
                                <textarea name="description" placeholder="Отличный фильм!..." rows="5" cols="50"></textarea>
                                <div style="display: flex; justify-content: center;">
                                    <input type="submit" value="Отправить">
                                </div>
                            </form>
                            <?php if (isset($_SESSION["message"])) { ?>
                                <span style="color: red; font-weight: 500; text-align: center"><?= $_SESSION["message"] ?></span>
                            <?php unset($_SESSION["message"]);
                            } ?>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="auth-required-message">
                            <h3>Авторизируйтесь, чтобы оставлять комментарии и оценки</h3>
                            <button id="loginBtn2" class="auth-button">Войти</button>
                        </div>
                    <?php } ?>
                </div>

                <?php
                $otherCommentsExist = false;
                pg_result_seek($comments, 0);
                while ($row = pg_fetch_assoc($comments)) {
                    if (!isset($_SESSION["user"]["id"]) || (isset($_SESSION["user"]["id"]) && $row["user_id"] != $userid)) {
                        $otherCommentsExist = true;
                        break;
                    }
                }

                if ($otherCommentsExist) {
                    pg_result_seek($comments, 0);
                ?>
                    <h3 class="other-users-title">Комментарии других пользователей</h3>
                    <?php
                    while ($row = pg_fetch_assoc($comments)) {
                        if (!isset($_SESSION["user"]["id"]) || (isset($_SESSION["user"]["id"]) && $row["user_id"] != $userid)) {
                            $commentid = $row["comment_id"];
                            $dateTime = new DateTime($row["comment_date"]);
                            $date = $dateTime->format('d.m.Y H:i'); ?>
                            <div class="comment" data-id=<?= $commentid ?>>
                                <div class="comment-title">
                                    <div class="user">
                                        <a class="user-link" href="profile?userid=<?= $row["user_id"] ?>">
                                            <div class="avatar">
                                                <img src="<?= $row["user_avatar"] ?>">
                                            </div>
                                        </a>
                                        <a class="user-link" href="profile?userid=<?= $row["user_id"] ?>">
                                            <p class="username"><?= $row["username"] ?></p>
                                        </a>
                                    </div>
                                    <p class="comment__date"><?= $date ?></p>
                                </div>
                                <p class="comment__text"><?= $row["comment_text"] ?></p>
                                <?php
                                if (isset($_SESSION["user"]["id"]) && $row["user_id"] == $userid) {
                                ?>
                                    <div class="button delButton">
                                        <img onclick="delComment(<?= $commentid ?>)" src="media/bucket.svg">
                                    </div>
                                <?php } ?>
                            </div>
                <?php }
                    }
                } ?>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="modal.js"></script>
    <script src="script.js"></script>
    <script src="burger.js"></script>

    <script>
        <?php if (isset($commentuser)) { ?>
            document.getElementById('del_button').addEventListener('click', function() {
                delComment(<?= $commentuser ?>);
            });
        <? } ?>

        <?php if (isset($commentuser)) { ?>
            document.querySelectorAll('.editButton').forEach(button => {
                button.addEventListener('click', function() {
                    const comment = this.closest('.comment');
                    const textElement = comment.querySelector('.comment__text');
                    const primaryButtons = comment.querySelector('.buttons.primary');
                    const secondaryButtons = comment.querySelector('.buttons.secondary');

                    const textarea = document.createElement('textarea');
                    textarea.value = textElement.textContent;
                    textarea.className = 'comment__text';
                    textarea.style.width = '100%';
                    textarea.style.height = '100px';
                    textarea.style.minHeight = '50px';
                    textarea.style.resize = 'vertical';
                    textarea.style.marginTop = '25px';
                    textarea.style.boxSizing = 'border-box';
                    textarea.style.fontFamily = 'Montserrat';
                    textarea.style.padding = '10px';

                    primaryButtons.style.display = 'none';
                    secondaryButtons.style.display = 'flex';

                    textElement.replaceWith(textarea);
                    textarea.focus();

                    const errorElement = document.createElement('p');
                    errorElement.id = 'errorMessage'
                    errorElement.style.color = 'red';
                    errorElement.style.marginTop = '5px';
                    errorElement.style.display = 'none';
                    errorElement.style.textAlign = 'center';
                    comment.appendChild(errorElement);

                    secondaryButtons.querySelector('.saveButton').addEventListener('click', function() {
                        const newText = textarea.value.trim();

                        if (newText === '') {
                            errorElement.textContent = 'Сообщение не может быть пустым';
                            errorElement.style.display = 'block';
                            return;
                        }

                        editComment(<?= $commentuser ?>, newText);
                        textarea.replaceWith(textElement);
                        textElement.textContent = newText;
                        primaryButtons.style.display = 'flex';
                        secondaryButtons.style.display = 'none';
                        errorElement.remove();
                    });

                    secondaryButtons.querySelector('.cancelButton').addEventListener('click', function() {
                        textarea.replaceWith(textElement);
                        primaryButtons.style.display = 'flex';
                        secondaryButtons.style.display = 'none';
                        errorElement.remove();
                    });
                });
            });
        <? } ?>
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