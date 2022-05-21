<?php

namespace App\Utils;

class DB
{
    public static function getInstance() {
        $dsn = 'mysql:host=' . getenv('MYSQL_HOST') . ';dbname=' . getenv('MYSQL_DATABASE') . ';port=' . getenv('MYSQL_PORT');
        $user = getenv('MYSQL_ROOT_USER');
        $password = getenv('MYSQL_ROOT_PASSWORD');
        $pdo = new \PDO($dsn, $user, $password);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}