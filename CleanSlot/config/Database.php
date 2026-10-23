<?php
// Database settings: change these to match your local MySQL (XAMPP)
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'cleanslot_db');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database
{
    public static function connect()
    {
        try {
            return new PDO(
                'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            exit('Database connection failed: ' . $e->getMessage());
        }
    }
}
