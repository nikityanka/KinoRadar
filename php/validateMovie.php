<?php

function validateReferences($connection, $country_id, $director_id, $genres) {
    $countryExists = pg_fetch_result(pg_query_params($connection, "SELECT EXISTS(SELECT 1 FROM country WHERE id = $1)", [$country_id]), 0, 0);
    if (!$countryExists) {
        return "Country with ID $country_id does not exist.";
    }

    $directorExists = pg_fetch_result(pg_query_params($connection, "SELECT EXISTS(SELECT 1 FROM director WHERE id = $1)", [$director_id]), 0, 0);
    if (!$directorExists) {
        return "Director with ID $director_id does not exist.";
    }

    $genreIds = explode(',', $genres);
    foreach ($genreIds as $genre_id) {
        $genreExists = pg_fetch_result(pg_query_params($connection, "SELECT EXISTS(SELECT 1 FROM genre WHERE id = $1)", [$genre_id]), 0, 0);
        if (!$genreExists) {
            return "Genre with ID $genre_id does not exist.";
        }
    }

    return true;
}
?>