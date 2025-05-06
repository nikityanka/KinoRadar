<header>
    <div class="wrapper">
        <h1 style="font-size: 40px;"><a href="./">КиноРадар</a></h1>
        <nav id="header-nav" class="header-nav">
            <ul>
                <div id="burger-close" class="burger-close-icon">
                    <span></span>
                    <span></span>
                </div>

                <?php
                if (isset($_SESSION['user'])) {
                    if ($_SESSION['user']['type'] == 1) {
                ?>
                        <li style="margin-right: 20px;">
                            <a href="admin/">
                                <div class="comboButton">
                                    <div class="photo">
                                        <img src=media/dashboard.svg alt="">
                                    </div>
                                    <span>Дашборд</span>
                                </div>
                            </a>
                        </li>
                    <?php } ?>
                    <li style="margin-right: 20px;">
                        <a href="favorite.php">
                            <div class="comboButton">
                                <div class="photo">
                                    <img src=media/favorite.svg alt="">
                                </div>
                                <span>Избранное</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="profile.php">
                            <div class="comboButton">
                                <div class="photo">
                                    <img src=<?= $_SESSION['user']['avatar'] ?> alt="">
                                </div>
                                <span>Профиль</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <form action="php/logout" method="post">
                            <button type="submit">
                                <div class="comboButton">
                                    <div class="photo">
                                        <img src=media/exit.svg alt="">
                                    </div>
                                    <span style="color: #E5226B;">Выход</span>
                                </div>
                            </button>
                        </form>
                    </li>
                <?
                } else { ?>
                    <li>
                        <div class="comboButton" id="loginBtn">
                            <div class="photo">
                                <img src="media/profile.svg" alt="">
                            </div>
                            <span>Вход</span>
                        </div>
                    </li>
                <? } ?>
            </ul>
        </nav>
        <div id="burger" class="burger-icon">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</header>