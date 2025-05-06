<header>
    <nav>
        <a href="../" class="logo-link"> 
            <h1>КиноРадар</h1>
        </a>
        <div class="buttons-container">
            <a href="../profile.php">
                <div class="comboButton">
                    <div class="photo">
                        <img src="<?= (strpos($_SESSION['user']['avatar'], 'media/') === 0) ? '../' . $_SESSION['user']['avatar'] : $_SESSION['user']['avatar'] ?>" alt="">
                    </div>
                    <span>Профиль</span>
                </div>
            </a>
            <form action="../php/logout" method="post" style="background-color: transparent; border: 0;">
                <button type="submit" style="background-color: transparent; border: 0;">
                    <div class="comboButton">
                        <div class="photo">
                            <img src="../media/exit.svg" alt="">
                        </div>
                        <span style="color: #E5226B;">Выход</span>
                    </div>
                </button>
            </form>
        </div>
    </nav>
</header>