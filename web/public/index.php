<?php

include '../app/vendor/autoload.php';

use Slim\Http\Response;
use App\Utils\DB;
use App\Utils\Auth;

error_reporting(0);

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

function did_user_upload() {
    return isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK;
}

function validate_file($response) {
    $fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
    $fileName = $_FILES['uploadedFile']['name'];
    $fileSize = $_FILES['uploadedFile']['size'];
    $fileType = $_FILES['uploadedFile']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // check if file is empty
    if ($fileSize == 0) {
        return $response->withJson(['error' => 'file is empty'], 400);
    }

    // check file is no bigger than 1024 MB
    if ($fileSize > 1024 * 1024 * 1024) {
        return $response->withJson(['error' => 'file is too big'], 400);
    }

    // make sure the file name in English characters, numbers and (_-.) symbols
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $fileName)) {
        return $response->withJson(['error' => 'file name must be in English characters, numbers and (_-.) symbols'], 400);
    }

    // make sure that the file name not bigger than 250 characters.
    if (strlen($fileName) > 250) {
        return $response->withJson(['error' => 'file name must be less than 250 characters'], 400);
    }

    $allowedfileExtensions = array('jpg', 'gif', 'png', 'zip', 'txt', 'xls', 'doc', 'rar', 'pdf', 'dll');

    // mimetypes corresponding to $allowedFileExtensions
    $mimeTypes = array(
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'zip' => 'application/zip',
        'txt' => 'text/plain',
        'xls' => 'application/vnd.ms-excel',
        'doc' => 'application/msword',
        'rar' => 'application/rar',
        'pdf' => 'application/pdf',
        'dll' => 'application/x-msdownload'
    );

    // validate $fileType against $mimeTypes
    if (!in_array($fileType, $mimeTypes)) {
        return $response->withJson(['error' => 'file type is not allowed'], 400);
    }

    if (!in_array($fileExtension, $allowedfileExtensions)) {
        return $response->withJson(['error' => 'file extension is not allowed'], 400);
    }
    return null;
}

// create php file upload handler
$app->post('/upload_mod', function ($request, $response) {
    $api_key = $request->getQueryParam('api_key');
    if (!Auth::authenticate($request)) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }
    
    $user_id = Auth::get_user_from_api_key($api_key);
    if (empty($user_id)) {
        return $response->withJson(['error' => 'Unauthorized'], 401);
    }
    
    $user_id = $user_id['user_id'];
    $updating_mod = false;
    $did_user_upload = did_user_upload();
    $restricted = 0;
    $mod_info = null;
    $db = DB::getInstance();

    // check if mod_id is passed in request
    if (isset($_POST['mod_id'])) {
        $mod_id = $_POST['mod_id'];
        // get the file with matching mod_id
        $stmt = $db->prepare('SELECT * FROM files WHERE id = :mod_id');
        $stmt->bindValue(':mod_id', $mod_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return $response->withJson(['error' => 'no file found with that mod_id'], 400);
        }
        // check if the file's user_id matches the user_id of the user who is trying to update the file
        if ($result['user_id'] != $user_id) {
            return $response->withJson(['error' => 'you are not authorized to update this file'], 400);
        }
        $restricted = intval($result['restricted']);
        $updating_mod = true;
        $mod_info = $result;
    }

    if ($did_user_upload) {
        $result  = validate_file($response);
        if ($result) {
            return $result;
        }
    }

    if (!$did_user_upload && !$updating_mod) {
        return $response->withJson(['error' => 'no file uploaded'], 400);
    }

    // check that mod name, description and version are passed in
    if (
        !isset($_POST['mod_name']) || 
        !isset($_POST['mod_description']) || 
        !isset($_POST['mod_version'])) {
        return $response->withJson(['error' => 'you must specify a valid `mod_name`, `mod_description`, and `mod_version`'], 400);
    }

    // get mod_name, mod_description, mod_version
    $mod_name = $_POST['mod_name'];
    $mod_description = $_POST['mod_description'];
    $mod_version = $_POST['mod_version'];
    $orbiter_version = !isset($_POST['orbiter_version']) ? 2016 : intval($_POST['orbiter_version']);
    $picture_link = !isset($_POST['picture_link']) ? '' : $_POST['picture_link'];

    if ($did_user_upload) {
        // get the number of restricted files
        $stmt = $db->prepare('SELECT COUNT(*) FROM files WHERE user_id = :user_id AND restricted = 1');
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $num_restricted_files = $result['COUNT(*)'];

        if ($num_restricted_files >= 3) {
            return $response->withJson(['error' => 'you currently have mods undergoing review, please wait before uploading'], 400);
        }

        // find number of files by $user_id where restricted = 0
        $stmt = $db->prepare('SELECT COUNT(*) FROM files WHERE user_id = :user_id AND restricted = 0');
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $num_files = $result['COUNT(*)'];

        // check if number of unrestricted mods is less than 3
        if (($num_files < 3 && !$updating_mod) || $restricted === 1) {
            $restricted = 1;
            // check if file size is larger than 300 MB
            if ($_FILES['uploadedFile']['size'] > 300 * 1024 * 1024) {
                return $response->withJson(['error' => 'new users are initially restricted to 300 MB uploads'], 400);
            }
        }
    }

    $Parsedown = new Parsedown();
    $Parsedown->setSafeMode(true);

    if (!$updating_mod) {
        $stmt = $db->prepare('INSERT INTO `files`
        (`filename`, `name`, `description`, `version`, `user_id`, `orbiter_version`, `restricted`, `description_html`, `picture_link`) 
        VALUES (:filename, :name, :description, :version, :user_id, :orbiter_version, :restricted, :description_html, :picture_link)');
        $stmt->bindValue(':name', $mod_name);
        $stmt->bindValue(':description', $mod_description);
        $stmt->bindValue(':version', $mod_version);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue(':orbiter_version', $orbiter_version);
        $stmt->bindValue(':filename', $_FILES['uploadedFile']['name']);
        $stmt->bindValue(':restricted', $restricted);
        $stmt->bindValue(':description_html', $Parsedown->text($mod_description));
        $stmt->bindValue(':picture_link', $picture_link);
        $stmt->execute();
        $mod_id = $db->lastInsertId();
    } else {
        // update files
        $stmt = $db->prepare('UPDATE files SET name = :name, 
        description = :description, version = :version, orbiter_version = :orbiter_version, 
        restricted = :restricted, description_html = :description_html, picture_link = :picture_link, `filename` = :filename WHERE id = :mod_id');
        $stmt->bindValue(':name', $mod_name);
        $stmt->bindValue(':description', $mod_description);
        $stmt->bindValue(':version', $mod_version);
        $stmt->bindValue(':orbiter_version', $orbiter_version);
        $stmt->bindValue(':restricted', $restricted);
        $stmt->bindValue(':mod_id', $mod_id);
        $stmt->bindValue(':description_html', $Parsedown->text($mod_description));
        $stmt->bindValue(':picture_link', $picture_link);
        $stmt->bindValue(':filename', $did_user_upload ? $_FILES['uploadedFile']['name'] : $mod_info['filename']);
        $stmt->execute();

    }
    
    if ($did_user_upload) {
        $fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
        $fileName = $_FILES['uploadedFile']['name'];

        // create mods/$user_id directory if it doesn't exist
        $mods_dir = 'mods/' . $user_id;
        if (!file_exists($mods_dir)) {
            mkdir($mods_dir, 0777, true);
        }

        // create mods/$user_id/$file_id directory if it doesn't exist
        $mod_dir = $mods_dir . '/' . $mod_id;
        if (!file_exists($mod_dir)) {
            mkdir($mod_dir, 0777, true);
        }
        
        // output file path in mod_dir
        $file_path = $mod_dir . '/' . $fileName;
        
        if(move_uploaded_file($fileTmpPath, $file_path)) {
            return $response->withJson(['success' => true, 'file_name' => $fileName, 'id' => $mod_id], 200);
        } else {
            return $response->withJson(['error' => 'could not upload file: ' . $_FILES['uploadedFile']['error']], 400);
        }
    }
    
    return $response->withJson(['success' => true], 200);
});

// serve the mod file given the file_id
$app->get('/mod/{file_id}', function ($request, $response, $args) {
    $file_id = $args['file_id'];
    
    $db = DB::getInstance();
    $stmt = $db->prepare(
        'SELECT `filename`,
            `restricted`,
            `user_id`,
            `version`
        FROM `files`
        WHERE `id` = :file_id
        LIMIT 1'
    );
    $stmt->bindValue(':file_id', $file_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $file_name = $result['filename'];
        $restricted = $result['restricted'];
        $user_id = $result['user_id'];
        $file_path = 'mods/' . $user_id . '/' . $file_id . '/' . $file_name;

        if (intval($restricted) == 1) {
            $response->getBody()->write("<h1>This mod is currently restricted!</h1>");
            return $response;
        }

        if (file_exists($file_path)) {
            $file_size = filesize($file_path);
            $file_type = mime_content_type($file_path);
            // return the static file with correct headers
            return $response->withHeader('Content-Type', $file_type)
                ->withHeader('Content-Length', $file_size)
                ->withHeader('Content-Disposition', 'attachment; filename="' . $file_name . '"')
                ->withHeader('Content-Transfer-Encoding', 'binary')
                ->withHeader('Expires', '0')
                ->withHeader('Cache-Control', 'must-revalidate')
                ->withHeader('Pragma', 'public')
                ->write(file_get_contents($file_path));
        } else {
            // redirect user to /files/$file_path
            return $response->withStatus(302)->withHeader('Location', '/files/' . $file_path);
        }
    } else {
        throw new \Slim\Exception\NotFoundException($request, $response);
    }
});

$app->get('/view/{mod_id}/{slug}', function ($request, $response, $args) {
    $file_id = $args['mod_id'];
    return $response
        ->withHeader('Content-Type', 'text/html')
        ->write(file_get_contents('view.html'));
});

$app->get('/mod/{mod_id}/info', function ($request, $response, $args) {
    $file_id = $args['mod_id'];
    $api_key = $request->getQueryParam('api_key');
    $markdown_desc = $request->getQueryParam('markdown_desc');
    $authenticated = Auth::authenticate($request);
    $user_id = null;

    if ($authenticated) {
        $result = Auth::get_user_from_api_key($api_key);
        if (!empty($result)) {
            $user_id = intval($result['user_id']);
        }
    }

    // get the files's info from the database
    $db = DB::getInstance();
    $stmt = $db->prepare(
        'SELECT `files`.`name`,
            `description_html`,
            `description`,
            `version`,
            `orbiter_version`,
            `restricted`,
            `user_id`,
            `picture_link`,
            `users`.`name` as `owner`
        FROM `files`
        JOIN `users` ON `files`.`user_id` = `users`.`id`
        WHERE `files`.`id` = :file_id
        LIMIT 1'
    );
    $stmt->bindValue(':file_id', $file_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($result)) {
        return $response->withJson(['error' => 'mod not found'], 404);
    }

    $file_user_id = intval($result['user_id']);

    $result = [
        'name' => $result['name'],
        'description' => $markdown_desc == 1 ? $result['description'] : $result['description_html'],
        'version' => $result['version'],
        'orbiter_version' => $result['orbiter_version'],
        'restricted' => $result['restricted'],
        'picture_link' => $result['picture_link'],
        'is_owner' => ($user_id === $file_user_id) ? true : false,
        'owner' => $result['owner']
    ];
    // return $result
    return $response->withJson($result, 200);
});
$app->run();