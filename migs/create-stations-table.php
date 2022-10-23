<?php

use App\Utils\DB;

include '/var/www/html/app/vendor/autoload.php';



$pdo = DB::getInstance();

$pdo->query("CREATE TABLE `stations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `station_links` text NOT NULL,
  `orbit_def` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;");

echo "\Done\n";