<?php

use App\Utils\DB;

include '/var/www/html/app/vendor/autoload.php';



$pdo = DB::getInstance();

$pdo->query("CREATE TABLE IF NOT EXISTS `files` (
    `id` int NOT NULL AUTO_INCREMENT,
    `filename` varchar(256) NOT NULL,
    `user_id` int NOT NULL,
    `name` varchar(128) NOT NULL,
    `description` text NOT NULL,
    `restricted` tinyint NOT NULL,
    `orbiter_version` smallint NOT NULL DEFAULT '2016',
    `version` varchar(32) DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;");

echo "\Done\n";