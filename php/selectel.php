<?php
require __DIR__ . "../vendor/autoload.php";

use Aws\S3\S3Client;

// Создание клиента
$s3Client = new S3Client([
   "version"    => "latest",
   "region"     => "ru-1",
   "use_path_style_endpoint" => true,
   "credentials" => [
      "key"   => "0cfb5780f49247e59cbf60cd29788a27",
      "secret" => "e82068e7a5e04004b6b1ca5ac715ba63",
   ],
   "endpoint" => "https://s3.storage.selcloud.ru"
]);

/*
// Загрузка объекта из строки
$result = $s3Client->putObject([
   "Bucket" => "school-uploads",
   "Key"	=> "video.mp4",
   "Body"   => file_get_contents("../video.mp4")
]);
 */

// Скачивание объекта
/*
$result = $s3Client->getObject([
   "Bucket" => "school-uploads",
   "Key"	=> "ObjectName"
]);
*/

// Удаление объекта
/*
$result = $s3Client->deleteObject([
   "Bucket" => "school-uploads",
   "Key"	=> "index.html"
]);*/

// Получение списка объектов
/*
$result = $s3Client->listObjects([
   'Bucket' => "school-uploads",
   'Prefix' => "1_5",
]);*/

?>
