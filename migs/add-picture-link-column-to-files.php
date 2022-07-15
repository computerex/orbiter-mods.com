<?php

use App\Utils\DB;

include '/var/www/html/app/vendor/autoload.php';



$pdo = DB::getInstance();

$pdo->query("ALTER TABLE `files` ADD `picture_link` VARCHAR(512) NULL DEFAULT NULL;");

echo "\Done\n";