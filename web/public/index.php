<?php

include '../app/vendor/autoload.php';

error_reporting(E_ALL ^ E_DEPRECATED);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

function get_pdo() {
    $dsn = "mysql:host=mysql;dbname=app;charset=UTF8";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
	return new PDO($dsn, "root", "root", $options);
}

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $pdo = get_pdo();
    $statement = $pdo->prepare("SELECT * FROM todos");
    $statement->execute();
    $todos = $statement->fetchAll(PDO::FETCH_ASSOC);
    // return todos as json
    return $response->withJson($todos);
});
$app->run();