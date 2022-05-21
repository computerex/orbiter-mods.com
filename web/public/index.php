<?php

use Slim\Http\Response;
use App\Utils\DB;

include '../app/vendor/autoload.php';

error_reporting(E_ALL ^ E_DEPRECATED);

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

$app->get('/todos', function ($request, Response $response, array $args) {
    // fetch all todos from the mysql todos table using pdo
    $pdo = DB::getInstance();
    $stmt = $pdo->query('SELECT * FROM todos');
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $response->withJson($todos, 200);
});
$app->run();