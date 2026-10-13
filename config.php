<?php
/**
 * InfinityFree / Standard MySQL Database Configuration
 * Apex Sports Club — ISE 2102 Project
 *
 * For InfinityFree:
 * 1. Log into your vPanel / InfinityFree Client Area.
 * 2. Go to "MySQL Databases".
 * 3. Copy your MySQL Hostname (e.g. sql123.infinityfree.com),
 *    MySQL Username (e.g. epiz_12345678), MySQL Password, and Database Name.
 * 4. Paste them into the defines below (or set environment variables).
 */

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'apex_sports_club');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a shared PDO instance configured with error exceptions and utf8mb4.
 */
function get_db_connection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Safe display for production/student submission
        die('<div style="font-family:sans-serif;max-width:600px;margin:40px auto;padding:20px;background:#fee;border:1px solid #f99;border-radius:8px;">' .
            '<h2 style="color:#c00;margin-top:0;">Database Connection Error</h2>' .
            '<p>Unable to connect to the MySQL database. Please verify your <code>config.php</code> credentials from your InfinityFree cPanel.</p>' .
            '<p><strong>Error details:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>' .
            '</div>');
    }

    return $pdo;
}
