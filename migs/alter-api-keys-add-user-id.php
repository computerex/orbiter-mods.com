<?php

use App\Utils\DB;

include '/var/www/html/app/vendor/autoload.php';



$pdo = DB::getInstance();

$pdo->query("ALTER TABLE `api_keys` ADD `user_id` INT NOT NULL;");

echo "\Done\n";