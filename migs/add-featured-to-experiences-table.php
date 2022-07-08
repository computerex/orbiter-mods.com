<?php

use App\Utils\DB;

include '/var/www/html/app/vendor/autoload.php';



$pdo = DB::getInstance();

$pdo->query("ALTER TABLE `experiences` ADD `featured` TINYINT NOT NULL DEFAULT '0' ;");
$pdo->query("ALTER TABLE `experiences` ADD `owner_id` INT NULL;");
$pdo->query("update `experiences` set featured = 1, owner_id = 1;");

$pdo->query("CREATE TABLE IF NOT EXISTS `api_keys` (
    `api_key` varchar(64) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`api_key`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci");

$pdo->query("CREATE TABLE `users` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `email` varchar(512) NOT NULL,
    `password` varchar(128) NOT NULL,
    `name` varchar(256) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci");
  
echo "\Done\n";