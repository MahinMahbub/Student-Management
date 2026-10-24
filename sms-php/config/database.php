<?php
/**
 * config/database.php
 * ---------------------
 * Central database connection using PDO (MySQL).
 * Edit the constants below to match your local MySQL/MariaDB setup.
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '4306');
define('DB_NAME', 'student_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST
             . ";port=" . DB_PORT
             . ";dbname=" . DB_NAME
             . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // use real prepared statements
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(
                "<div style='font-family: sans-serif; padding: 40px; max-width: 640px; margin: 40px auto; " .
                "border: 1px solid #e3c2b6; background: #f5ddd5; border-radius: 8px;'>" .
                "<h2 style='margin-top:0;'>Database connection failed</h2>" .
                "<p>Could not connect to MySQL/MariaDB. Please check:</p>" .
                "<ul>" .
                "<li>MySQL/MariaDB server is running</li>" .
                "<li>Credentials in <code>config/database.php</code> are correct</li>" .
                "<li>The <code>student_management</code> database has been created " .
                "(run <code>schema.sql</code> first)</li>" .
                "</ul>" .
                "<p style='font-family: monospace; font-size: 13px; color:#7a3324;'>" .
                htmlspecialchars($e->getMessage()) . "</p>" .
                "</div>"
            );
        }
    }

    return $pdo;
}
