<?php
$connection = pg_connect("host=172.20.7.53 port=5432 dbname=db2991_04 user=st2991 password=pwd2991 connect_timeout=20");
$schema_name = "movie_search";
pg_query($connection, "SET search_path TO $schema_name");

if (!$connection) {
    header('Location: ../error.php');
    exit;
}
?>
