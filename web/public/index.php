<?php

use Slim\Http\Response;
use App\Utils\DB;
use App\Utils\Auth;

include '../app/vendor/autoload.php';

error_reporting(E_ALL ^ E_DEPRECATED ^ E_WARNING);

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

$app->get('/', function ($request, Response $response) {
    // serve static index html file
    return $response
        ->withHeader('Content-Type', 'text/html')
        ->write(file_get_contents('index.html'));
});

$app->get('/redirect', function ($request, Response $response) {
    // get url from query params
    $url = $request->getQueryParam('url');
    // redirect to url with referrer set to orbiter-forum.com
    ob_start();
    header('Referer: https://orbiter-forum.com');
    header('Location: '.$url);
    ob_end_flush();
    die();
    return $response->withRedirect($url, 301, ['Referrer' => 'https://orbiter-forum.com/']);
});

// create endpoint post /api/user to create new user, save user to users table in mysql db using pdo
$app->post('/user', function ($request, $response) {
    $data = $request->getParsedBody();

    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];

    $db = DB::getInstance();

    // check if a user exists in the users table with the matching email
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch();

    if (empty($user)) {
        // if user does not exist, insert new user into users table
        $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT));
        $stmt->execute();
        $api_key = Auth::generateApiKey();
        Auth::save_api_key($api_key);
        return $response->withJson([
            'success' => true,
            'api_key' => $api_key
        ], 200);
    } else {
        // if user exists, return error
        return $response->withJson(['error' => 'User already exists'], 400);
    }
    return $response->withJson(['status' => 'success']);
});

// create post /login endpoint
$app->post('/login', function ($request, $response) {
    $data = $request->getParsedBody();

    $email = $data['email'];
    $password = $data['password'];

    $db = DB::getInstance();

    // check if a user exists in the users table with the matching email
    $stmt = $db->prepare('SELECT id, password FROM users WHERE email = :email');
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch();

    if (!empty($user)) {
        // if user exists, check if password matches
        if (password_verify($password, $user['password'])) {
            // if password matches, generate api key and save to api_keys table
            $api_key = Auth::generateApiKey();
            Auth::save_api_key($api_key);
            return $response->withJson([
                'success' => true,
                'api_key' => $api_key
            ], 200);
        } else {
            // if password does not match, return error
            return $response->withJson(['error' => 'Invalid password'], 400);
        }
    } else {
        // if user does not exist, return error
        return $response->withJson(['error' => 'User does not exist'], 400);
    }
    return $response->withJson(['status' => 'success']);
});

// create post /logout endpoint to log user out by deleting their api_key from api_keys table
$app->post('/logout', function ($request, $response) {

    if (!Auth::authenticate($request)) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }

    $api_key = $request->getQueryParam('api_key');
    $db = DB::getInstance();

    // if api_key exists, delete api_key from api_keys table
    $stmt = $db->prepare('DELETE FROM api_keys WHERE api_key = :api_key');
    $stmt->bindValue(':api_key', $api_key);
    $stmt->execute();

    return $response->withJson(['status' => 'success'], 200);
});

// create endpoint is_valid_key to check if api_key is valid
$app->get('/is_valid_key', function ($request, $response) {
    if (!Auth::authenticate($request)) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }
    return $response->withJson(['success' => true], 200);
});

$app->run();