<?php

use Slim\Http\Response;
use App\Utils\DB;
use App\Utils\Auth;

include '../app/vendor/autoload.php';

error_reporting(E_ALL ^ E_DEPRECATED ^ E_WARNING);

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

$app->get('/ip', function($request, Response $response) {
    $ip = get_client_ip();
    $response->getBody()->write($ip);
    return $response;
});

$app->get('/', function ($request, Response $response) {
    // serve static index html file
    return $response
        ->withHeader('Content-Type', 'text/html')
        ->write(file_get_contents('index.html'));
});

// create endpoint post /api/user to create new user, save user to users table in mysql db using pdo
$app->post('/user', function ($request, $response) {
    $data = $request->getParsedBody();

    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $password2 = $data['password2'];

    // validate that name is not empty and at least 3 characters long
    if (strlen($name) < 3) {
        return $response->withJson(['error' => 'Name must be at least 3 characters long']);
    }

    if (strlen($password) < 6 || $password != $password2) {
        return $response->withJson(['error' => 'Password must be greater than 6 characters, or passwords do not match']);
    }

    // validate email is a valid email address
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $response->withJson(['error' => 'Email must be a valid email address']);
    }

    $pdo = DB::getInstance();
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE name = :name');
    $stmt->bindParam(':name', $name);
    $stmt->execute();
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!empty($result)) {
        return $response->withJson(['error' => 'Name already exists']);
    }

    // check if a user exists in the users table with the matching email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch();

    if (!empty($user)) {
        return $response->withJson(['error' => 'Email already exists']);
    }

    // if user does not exist, insert new user into users table
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT));
    $stmt->execute();
    $user_id = $pdo->lastInsertId();
    $api_key = Auth::generateApiKey();
    Auth::save_api_key($api_key, $user_id);
    return $response->withJson([
        'success' => true,
        'api_key' => $api_key
    ], 200);
});

// add endpoint /experiences to return all currently available experiences as json array
$app->get('/fetch_experiences', function ($request, $response) {
    $host = 'https://orbiter-mods.com';

    if (getenv('DEBUG') == '1') {
        $host = 'http://localhost:8000';
    }
    $pdo = DB::getInstance();
    $stmt = $pdo->prepare('SELECT * FROM experiences');
    $stmt->execute();
    $experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = null;
    return $response->withJson(
        $experiences
    );
});

// create post /login endpoint
$app->post('/login', function ($request, $response) {
    $data = $request->getParsedBody();

    $email = $data['email'];
    $password = $data['password'];

    $db = DB::getInstance();

    // check if a user exists in the users table with the matching email
    $stmt = $db->prepare('SELECT `id`, `name`, `password` FROM users WHERE email = :email');
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($user)) {
        // if user exists, check if password matches
        if (password_verify($password, $user['password'])) {
            // if password matches, generate api key and save to api_keys table
            $api_key = Auth::generateApiKey();
            Auth::save_api_key($api_key, $user['id']);
            return $response->withJson([
                'success' => true,
                'api_key' => $api_key,
                'username' => $user['name'],
                'email' => $email
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

// create endpoint is_key_valid to check if api_key is valid
$app->get('/is_key_valid', function ($request, $response) {
    if (!Auth::authenticate($request)) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }
    return $response->withJson(['success' => true], 200);
});

// create endpoint to update the experiences table
$app->post('/update_experience', function ($request, $response) {
    $experience_id = $request->getQueryParam('id');
    $api_key = $request->getQueryParam('api_key');
    if (empty($api_key) || empty($experience_id)) {
        return $response->withJson(['status' => 'false'], 400);
    }
    
    if (!Auth::authenticate($request)) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }

    $user_id = Auth::get_user_from_api_key($api_key);
    if (empty($user_id)) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }

    $user_id = $user_id['user_id'];
    
    // get experiences.owner_id for the experience_id

    $pdo = DB::getInstance();
    $stmt = $pdo->prepare('SELECT owner_id FROM experiences WHERE id = :id');
    $stmt->bindValue(':id', $experience_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($result)) {
        return $response->withJson(['error' => 'Experience does not exist'], 400);
    }

    // check if the user_id matches the owner_id
    if ($user_id != $result['owner_id']) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }

    $data = $request->getParsedBody();

    $name = $data['name'];
    $links = $data['links'];
    $script = $data['experience_script'];
    $description = $data['description'];

    if (
        empty($name)
        || empty($links)
        || empty($script)
        || empty($description)
        ) {
        return $response->withJson(['error' => 'please specify valid `name`, `links`, `experience_script`, `description`'], 400);
    }

    $db = DB::getInstance();
    $stmt = $db->prepare('UPDATE experiences
                            SET name = :name, links = :links, experience_script = :experience_script,
                            description = :description WHERE id = :id'
    );
    $stmt->bindValue(':id', $experience_id, PDO::PARAM_INT);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':links', $links);
    $stmt->bindValue(':experience_script', $script);
    $stmt->bindValue(':description', $description);
    $stmt->execute();
    return $response->withJson(['success' => true]);
});

// create endpoint to post /experience and create new experience
$app->post('/experience', function ($request, $response) {
    $api_key = $request->getQueryParam('api_key');
    if (!Auth::authenticate($request)) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }
    
    $user_id = Auth::get_user_from_api_key($api_key);
    if (empty($user_id)) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }

    $user_id = $user_id['user_id'];

    $data = $request->getParsedBody();
    $name = $data['name'];
    $links = $data['links'];
    $script = $data['experience_script'];
    $description = $data['description'];

    if (
        empty($name)
        || empty($links)
        || empty($script)
        || empty($description)
        ) {
        return $response->withJson(['error' => 'you must specify a valid `name`, `links`, `description`, and `experience_script`'], 400);
    }
    $db = DB::getInstance();
    $stmt = $db->prepare('INSERT INTO experiences (`name`, `links`, `experience_script`, `description`, `owner_id`)
                            VALUES (:name, :links, :experience_script, :description, :owner_id)');
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':links', $links);
    $stmt->bindValue(':experience_script', $script);
    $stmt->bindValue(':description', $description);
    $stmt->bindValue(':owner_id', $user_id);
    $stmt->execute();
    $experience_id = $db->lastInsertId();
    return $response->withJson(['success' => true, 'id' => $experience_id], 200);
});

// create endpoint to get all experiences for specified user_id
$app->get('/user_experiences', function ($request, $response) {
    $api_key = $request->getQueryParam('api_key');
    if (!Auth::authenticate($request)) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }
    
    $user_id = Auth::get_user_from_api_key($api_key);
    if (empty($user_id)) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }
    
    $user_id = $user_id['user_id'];

    $db = DB::getInstance();
    $stmt = $db->prepare('SELECT id FROM experiences WHERE owner_id = :owner_id');
    $stmt->bindValue(':owner_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $response->withJson(['success' => true, 'ids' => array_column($result, 'id')], 200);
});

// create endpoint to return number of addons in addons.json
$app->get('/mods_count', function ($request, $response) {
    $addons = json_decode(file_get_contents('addons.json'), true);
    return $response->withJson(['success' => true, 'count' => count($addons)], 200);
});

// create php file upload handler
$app->post('/upload', function ($request, $response) {
    $api_key = $request->getQueryParam('api_key');
    if (!Auth::authenticate($request)) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }
    
    $user_id = Auth::get_user_from_api_key($api_key);
    if (empty($user_id)) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }
    
    $user_id = $user_id['user_id'];
    if (isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
        $fileName = $_FILES['uploadedFile']['name'];
        $fileSize = $_FILES['uploadedFile']['size'];
        $fileType = $_FILES['uploadedFile']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        var_dump($fileTmpPath);
        // vardump cwd
        var_dump(getcwd());
        var_dump($fileName);
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'zip', 'txt', 'xls', 'doc');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            if(move_uploaded_file($fileTmpPath, "{$fileName}")) {
                return $response->withJson(['success' => true, 'file_name' => $fileName], 200);
            } else {
                return $response->withJson(['error' => 'could not upload file: ' . $_FILES['uploadedFile']['error']], 400);
            }   
        }
    }
    return $response->withJson(['success' => true], 200);
});


$app->run();