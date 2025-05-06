<?php

session_start();
include("selectel.php");

$file = $_FILES["file"];

if (empty($file)) {
    echo "нит файла";
    exit();
}

/*
$normalizeImages = [];
foreach ($files as $key_name => $value) {
    foreach ($value as $key => $item) {
        $normalizeImages[$key][$key_name] = $item;
    }
}*/


$result = $s3Client->putObject([
    "Bucket" => "movie-posters",
    "Key"    =>  "posters/" . $file['name'],
    "Body"   => file_get_contents($file['tmp_name'])
]);

//header("Location: ../lesson?idLesson=$idLesson");

?>

<pre>
    <?php
        print_r($result['ObjectURL']);
    ?>
</pre>