<?php
<<<<<<< HEAD
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration de l'application
define('APP_NAME', 'Aurora Event');
define('APP_DEBUG', true);

// Activer les erreurs en développement
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Connexion PDO
function getDB() {
    static $db = null;
    
    if ($db === null) {
        try {
            $db = new PDO(
                'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8',
                DB_USER, 
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }
    return $db;
}
?>
=======

class config
{
    private static $pdo = null;
        
    public static function getConnexion()
    {
        if (!self::$pdo) {
            $servername = 'localhost';
            $username = 'root';
            $password = '';
            $dbname = 'aurora';
            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die('Erruer: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
>>>>>>> user
