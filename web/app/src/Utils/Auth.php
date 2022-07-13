<?php 

namespace App\Utils;

class Auth {
    public static function generateApiKey() {
        $bytes = openssl_random_pseudo_bytes(32, $cstrong);
        $hex = bin2hex($bytes);
        return $hex;
    }

    public static function is_key_valid($api_key) {
        // get matching $api_key from api_keys table if the created_at is less than 24 hours
        $db = DB::getInstance();
        $sql = "SELECT *
            FROM api_keys 
            WHERE api_key = :api_key 
            AND created_at > DATE_SUB(NOW(), INTERVAL 4 DAY)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':api_key', $api_key);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return !empty($result);
    }

    public static function is_super_admin($request) {
        // get the super_admin query parameter
        $super_admin = $request->getQueryParam('super_admin');
        // check if $super_admin is equal to the SUPER_ADMIN_SECRET env var
        return $super_admin === getenv('SUPER_ADMIN_SECRET');
    }

    public static function authenticate($request) {
        // get api_key from query parameters
        $api_key = $request->getQueryParam('api_key');
        if (empty($api_key)) {
            return false;
        }
        return self::is_key_valid($api_key);
    }

    public static function get_user_from_api_key($api_key) {
        // find user_id from api_keys table where api_keys.api_key = $api_key
        $db = DB::getInstance();
        $sql = "SELECT user_id
            FROM api_keys 
            WHERE api_key = :api_key";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':api_key', $api_key);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        return $result;
    }

    public static function save_api_key($api_key, $user_id) {
        $db = DB::getInstance();
        $sql = "INSERT INTO api_keys (api_key, user_id) VALUES (:api_key, :user_id)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':api_key', $api_key);
        $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    // create function to delete specified $api_key from the database
    public static function delete_api_key($api_key) {
        $db = DB::getInstance();
        $sql = "DELETE FROM api_keys WHERE api_key = :api_key";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':api_key', $api_key);
        $stmt->execute();
        return $stmt->rowCount();
    }
}