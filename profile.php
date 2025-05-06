<?php
session_start();
require_once('php/connect.php');

$isAuth = false;
$isUserAccount = false;
if (isset($_SESSION['user'])) {
    $isAuth = true;
    $userid = $_SESSION['user']['id'];
    $isUserAccount = true;
}

if (isset($_GET['userid']) && $isAuth == true) {
    $userid = filter_var($_GET['userid'], FILTER_VALIDATE_INT);
    if ($userid === false || $userid <= 0) {
        header("Location: ../error.php");
        exit;
    }
    $isUserAccount = false;
    if ($_SESSION['user']['id'] == $userid) {
        $isUserAccount = true;
    }
} else if (empty($_SESSION['user'])) {
    header("Location: ../");
    exit;
}

$query = "SELECT get_user_info($1)";
$result = pg_query_params($connection, $query, array($userid));

if ($result) {
    $userData = pg_fetch_assoc($result);
    $userInfo = json_decode($userData['get_user_info'], true);

    if ($userInfo['status'] === 'success') {
        $user = $userInfo['data'];
        $username = htmlspecialchars($user['username']);
        if (isset($user['about'])) {
            $about = htmlspecialchars($user['about']);
        } else {
            $about = null;
        }
        $avatar = htmlspecialchars($user['avatar']);
        $registrationDate = date('d.m.Y H:i', strtotime($user['registration_date']));
        $email = htmlspecialchars($user['email']);

        $ratings = $user['ratings'];
        $ratingsCount = count($ratings);

        $comments = $user['comments'];
        $commentsCount = count($comments);
    } else {
        include('./error.php');
        exit;
    }
}

$dialogError = ($_SESSION['message']['message_type'] ?? null) != 'success'
    ? ($_SESSION['message']['message_dialog'] ?? null)
    : null;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>

    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="dialog.css">
</head>

<body>
    <dialog id="avatarModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('avatarModal')">&times;</span>
            <h2>Смена аватара</h2>
            <form class="form" action="php/updateAvatar" method="post" enctype="multipart/form-data">
                <label for="avatar">Выберите изображение:</label>
                <div class="file-upload" style="display: flex; justify-content: center; flex-direction: column;">
                    <label for="avatar" class="file-upload-label">
                        Выбрать файл
                    </label>
                    <span class="file-name" id="file-name"></span>
                </div>
                <input type="file" id="avatar" name="avatar" accept="image/*">

                <div class="form-part submit">
                    <button type="submit">Обновить аватар</button>
                </div>
                <?php if (isset($_SESSION['message']['message_desc']) && $dialogError == 'avatarModal') { ?>
                    <p class="message"><?= $_SESSION['message']['message_desc'] ?></p>
                <?php
                    unset($_SESSION['message']);
                }
                ?>
            </form>
        </div>
    </dialog>

    <dialog id="usernameModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('usernameModal')">&times;</span>
            <h2>Изменение имени</h2>
            <form class="form" action="php/updateUsername" method="post">
                <div class="form-part">
                    <label for="new_username">Новое имя:</label>
                    <input type="text" id="new_username" name="new_username" required>
                </div>
                <div class="form-part submit">
                    <button type="submit">Сохранить</button>
                </div>
                <?php if (isset($_SESSION['message']['message_desc']) && $dialogError == 'usernameModal') { ?>
                    <p class="message"><?= $_SESSION['message']['message_desc'] ?></p>
                <?php
                    unset($_SESSION['message']);
                }
                ?>
            </form>
        </div>
    </dialog>

    <dialog id="infoModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('infoModal')">&times;</span>
            <h2>Изменение email</h2>
            <form class="form" action="php/updateInfo" method="post">
                <div class="form-part">
                    <label for="new_about">Новое описание:</label>
                    <textarea rows="7" cols="50" id="new_about" name="new_about"></textarea>
                </div>
                <div class="form-part">
                    <label for="new_email">Новый email:</label>
                    <input type="email" id="new_email" name="new_email" required value="<?= $email ?>">
                </div>
                <div class="form-part submit">
                    <button type="submit">Сохранить</button>
                </div>
                <?php if (isset($_SESSION['message']['message_desc']) && $dialogError == 'infoModal') { ?>
                    <p class="message"><?= $_SESSION['message']['message_desc'] ?></p>
                <?php
                    unset($_SESSION['message']);
                }
                ?>
            </form>
        </div>
    </dialog>

    <dialog id="passwordModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('passwordModal')">&times;</span>
            <h2>Смена пароля</h2>
            <form class="form" action="php/updatePassword" method="post">
                <div class="form-part">
                    <label for="current_password">Текущий пароль:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-part">
                    <label for="new_password">Новый пароль:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-part">
                    <label for="confirm_password">Подтвердите пароль:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-part submit">
                    <button type="submit">Изменить пароль</button>
                </div>
                <?php if (isset($_SESSION['message']['message_desc']) && $dialogError == 'passwordModal') { ?>
                    <p class="message"><?= $_SESSION['message']['message_desc'] ?></p>
                <?php
                    unset($_SESSION['message']);
                }
                ?>
            </form>
        </div>
    </dialog>

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
                    <p id="toggleText">Нет аккаунта? <button type="button" id="toggleBtn">Зарегистрироваться</button>
                    </p>
                </form>
                <form id="registrationForm" class="form" action="php/registration" method="post"
                    style="display:none;">
                    <div class="form-part">
                        <label for="username">Имя пользователя:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-part">
                        <label for="email">Почта:</label>
                        <input type="password" id="email" name="email" required>
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
                    <p id="toggleTextReg">Уже есть аккаунт? <button type="button" id="toggleBtnReg">Войти</button></p>
                </form>
            </div>
        </div>
    </dialog>

    <?php
    include("header.php")
    ?>

    <main>
        <div class="wrapper">
            <div class="wrapper-main">
                <div class="avatar">
                    <div class="photo" style="position: relative;">
                        <img src="<?php echo $avatar ?>" alt="Аватар пользователя">
                        <?php if ($isUserAccount): ?>
                            <div class="editButton" onclick="showModal('avatarModal')" title="Изменить аватар"
                                style="position: absolute; top: 0; right: 0; transform: translate(30%, -30%); ">
                                <img src="media/pencil.svg" id="edit_button"
                                    style="border: 0; border-radius: 0; box-shadow: none;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info">
                    <div class="username_info">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <span class="username"><?php echo $username ?></span>
                            <?php if ($isUserAccount): ?>
                                <div class="editButton" onclick="showModal('usernameModal')" title="Изменить имя пользователя">
                                    <img src="media/pencil.svg" id="edit_button">
                                </div>
                                <div style="width: 30px;" class="editButton" onclick="showModal('passwordModal')" title="Изменить пароль">
                                    <img src="media/password.svg" id="edit_button">
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($isUserAccount): ?>
                            <div style="width: 40px;" class="editButton" onclick="showModal('infoModal')" title="Изменить почту и 'О себе'">
                                <img src="media/change_info.svg" id="edit_button">
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($about)): ?>
                        <p class="desc"><?php echo $about; ?></p>
                    <?php endif; ?>
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between;">
                        <?php if (!empty($registrationDate)): ?>
                            <span class="date">На сайте с <span style="color: coral;"><?php echo $registrationDate; ?></span></span>
                        <?php endif; ?>
                        <?php if (!empty($email)): ?>
                            <span class="date">Почта: <span style="color: coral;"><?php echo $email; ?></span></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="ratings">
                <h2>Оценки (<?php echo $ratingsCount ?? 0; ?>)</h2>
                <div class="separator"></div>

                <?php if (empty($ratings)): ?>
                    <p style="text-align: center; font-weight: 600;">Пользователь пока не оставил ни одной оценки</p>
                <?php else: ?>
                    <div class="collection">
                        <?php foreach ($ratings as $rating):
                            switch (true) {
                                case ($rating['rating_value'] <= 2):
                                    $color = "#dc3545";
                                    break;
                                case ($rating['rating_value'] == 3):
                                    $color = "#ffc107";
                                    break;
                                case ($rating['rating_value'] >= 4):
                                    $color = "#28a745";
                                    break;
                            }
                        ?>
                            <a href="movie.php?movie=<?php echo $rating['movie_id']; ?>">
                                <div class="rating">
                                    <h4><?php echo htmlspecialchars($rating['movie_title']); ?></h4>
                                    <span class="rate" style="background-color: <?= $color ?>;"><?php echo $rating['rating_value']; ?>/5</span>
                                    <span class="date"><?php echo date('d.m.Y H:i', strtotime($rating['date'])); ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>


            <div class="comments">
                <h2>Комментарии (<?php echo $commentsCount ?? 0; ?>)</h2>
                <div class="separator"></div>

                <?php if (empty($comments)): ?>
                    <p style="text-align: center; font-weight: 600;">Пользователь пока не оставил ни одного комментария</p>
                <?php endif; ?>

                <div class="collection">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <a href="movie.php?movie=<?php echo $comment['movie_id']; ?>">
                                <div class="comment">
                                    <h4><?php echo htmlspecialchars($comment['movie_title']); ?></h4>
                                    <p class="desc"><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                                    <span class="date"><?php echo date('d.m.Y H:i', strtotime($comment['date'])); ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <script src="modal.js"></script>
    <script src="script.js"></script>
    <script src="burger.js"></script>
    <script>
        function showModal(modalId) {
            const modal = document.getElementById(modalId);

            if (modalId === 'infoModal') {
                const textarea = document.getElementById('new_about');
                const email = document.getElementById('new_email');
                const charLimit = 255;

                textarea.value = <?= json_encode($about) ?>;
                email.value = <?= json_encode($email) ?>;
                let counter = textarea.parentNode.querySelector('.counter');
                if (!counter) {
                    counter = document.createElement('div');
                    counter.className = 'counter';
                    counter.style.cssText = 'text-align: right; font-size: 0.8em; color: #666;';
                    textarea.parentNode.insertBefore(counter, textarea.nextSibling);
                }

                const updateCounter = () => {
                    const currentLength = textarea.value.length;
                    counter.textContent = `${currentLength}/${charLimit}`;
                    counter.style.color = currentLength >= charLimit ? 'red' :
                        currentLength > charLimit - 20 ? 'orange' : '#666';
                };

                textarea.oninput = updateCounter;

                updateCounter();
            }

            modal.showModal();
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.close();
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) modal.close();
                });
            });

            <?php if ($dialogError != null): ?>
                showModal('<?= $dialogError ?>');
                <?php if ($dialogError === 'infoModal'): ?>
                    setTimeout(() => {
                        const textarea = document.getElementById('new_about');
                        if (textarea) textarea.dispatchEvent(new Event('input'));
                    }, 50);
                <?php endif; ?>
            <?php endif; ?>
        });

        document.getElementById('infoModal')?.addEventListener('submit', function(e) {
            const textarea = document.getElementById('new_about');
            if (textarea.value.length > 255) {
                e.preventDefault();
                alert('Превышен лимит в 255 символов!');
            }
        });
    </script>

    <script>
        document.getElementById('avatar').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Файл не выбран';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
</body>

</html>