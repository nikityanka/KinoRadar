<?php
session_start();
require_once('php/connect.php');

if (isset($_SESSION['user'])) {
    $userid = $_SESSION['user']['id'];
} else {
    header("Location: ../");
    exit;
}

$query = "SELECT * FROM get_user_favorites($1)";
$result = pg_query_params($connection, $query, array($userid));
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>КиноРадар | Избранное</title>

    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="favorite.css">
    <link rel="stylesheet" href="movies.css">
    <link rel="stylesheet" href="dialog.css">
</head>

<body>
    <?php
     include("header.php")
    ?>
    </header>

    <main>
        <h1>Избранное (<?= count(pg_fetch_all($result)) ?>)</h1>
        <div class="movies">
            <?php
            while ($row = pg_fetch_assoc($result)) {
                $movieid = $row["movie_id"];
            ?>
                <a class="movie_href" href="movie.php?movie=<?= $movieid ?>">
                    <div class="movie">
                        <?php if (isset($_SESSION["user"])) { ?>
                            <button class="addFav" data-id="<?= $movieid ?>">
                                <img src="media/heart-fill.svg" alt="">
                            </button>
                        <?php } ?>
                        <img src="<?= $row["poster_image"] ?>">
                        <div class="title"><?= $row["title"] . " (" . $row["year"] . ")" ?></div>
                    </div>
                </a>
            <?php } ?>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="modal.js"></script>
    <script src="script.js"></script>
    <script src="burger.js"></script>
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
</body>

</html>