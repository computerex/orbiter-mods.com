<?php

use Slim\Http\Response;
use App\Utils\DB;

include '../app/vendor/autoload.php';

error_reporting(E_ALL ^ E_DEPRECATED ^ E_WARNING);

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

$app->get('/', function ($request, Response $response) {
    // serve static index html file
    return $response
        ->withHeader('Content-Type', 'text/html')
        ->write(file_get_contents('index.html'));
});

$app->get('/todos', function ($request, Response $response, array $args) {
    // fetch all todos from the mysql todos table using pdo
    $pdo = DB::getInstance();
    $stmt = $pdo->query('SELECT * FROM todos');
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $response->withJson($todos, 200);
});

$app->post('/todo', function ($request, Response $response, array $args) {
    // get todo data from request body
    $data = $request->getParsedBody();
    // insert todo into the mysql todos table using pdo
    $pdo = DB::getInstance();
    $stmt = $pdo->prepare(
        'INSERT INTO todos (task_desc) 
        VALUES (:task_desc)');
    $stmt->bindValue(':task_desc', $data['task_desc']);
    $stmt->execute();
    return $response->withJson(['success' => true], 200);
});

$app->put('/todo', function ($request, Response $response, array $args) {
    // get todo data from request body
    $data = $request->getParsedBody();
    // update todo in the mysql todos table using pdo
    $pdo = DB::getInstance();
    // get todo with the request todo id from the database
    $stmt = $pdo->prepare(
        'SELECT * FROM todos WHERE id = :id');
    $stmt->bindValue(':id', $data['id']);
    $stmt->execute();
    $todo = $stmt->fetch(PDO::FETCH_ASSOC);
    // if todo exists
    if ($todo) {
        // update todo in the database
        $todo['task_desc'] = $data['task_desc'] ?: $todo['task_desc'];
        $todo['done'] = isset($data['done']) ? $data['done'] : $todo['done'];
        $stmt = $pdo->prepare(
            'UPDATE todos SET task_desc = :task_desc, done = :done WHERE id = :id');
        $stmt->bindValue(':task_desc', $todo['task_desc']);
        $stmt->bindValue(':id', $todo['id'], PDO::PARAM_INT);
        $stmt->bindValue(':done', $todo['done'], PDO::PARAM_INT);
        $stmt->execute();
        return $response->withJson(['success' => true], 200);
    } else {
        return $response->withJson(['success' => false], 404);
    }
    return $response->withJson(['success' => true], 200);
});

// create delete /todo endpoint that accepts todo id and deletes the todo from the database
$app->delete('/todo', function ($request, Response $response, array $args) {
    // get todo id from request body
    $data = $request->getParsedBody();
    // delete todo from the mysql todos table using pdo
    $pdo = DB::getInstance();
    // get todo with the request todo id from the database
    $stmt = $pdo->prepare(
        'SELECT * FROM todos WHERE id = :id');
    $stmt->bindValue(':id', $data['id']);
    $stmt->execute();
    $todo = $stmt->fetch(PDO::FETCH_ASSOC);
    // if todo exists
    if ($todo) {
        // delete todo from the database
        $stmt = $pdo->prepare(
            'DELETE FROM todos WHERE id = :id');
        $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);
        $stmt->execute();
        return $response->withJson(['success' => true], 200);
    } else {
        return $response->withJson(['success' => false], 404);
    }
    return $response->withJson(['success' => true], 200);
});
$app->run();