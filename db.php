<?php
/**
 * Database connection & setup helper for Apex Sports Club.
 * Supports SQLite (local/zero-config fallback) or PostgreSQL (e.g. Render Postgres).
 */
function get_db_connection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbUrl = getenv('DATABASE_URL');

    if (!empty($dbUrl)) {
        // Parse PostgreSQL URL from Render: postgres://user:pass@host:port/dbname
        $parts = parse_url($dbUrl);
        $host = $parts['host'] ?? 'localhost';
        $port = $parts['port'] ?? '5432';
        $user = $parts['user'] ?? '';
        $pass = $parts['pass'] ?? '';
        $dbname = ltrim($parts['path'] ?? '', '/');

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO_ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO_FETCH_ASSOC
        ]);
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS members (
                id SERIAL PRIMARY KEY,
                submitted_at VARCHAR(50) NOT NULL,
                full_name VARCHAR(150) NOT NULL,
                email VARCHAR(150) NOT NULL,
                course VARCHAR(150) NOT NULL,
                study_year VARCHAR(50) NOT NULL,
                preferred_activity VARCHAR(100) NOT NULL,
                experience_level VARCHAR(50),
                form_purpose VARCHAR(50),
                reason TEXT,
                consent VARCHAR(10) NOT NULL
            );
        ");
    } else {
        // Local SQLite database stored in data/apex.sqlite
        $dataDir = __DIR__ . '/data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
        }
        $dbFile = $dataDir . '/apex.sqlite';
        $dsn = "sqlite:" . $dbFile;
        $pdo = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS members (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                submitted_at TEXT NOT NULL,
                full_name TEXT NOT NULL,
                email TEXT NOT NULL,
                course TEXT NOT NULL,
                study_year TEXT NOT NULL,
                preferred_activity TEXT NOT NULL,
                experience_level TEXT,
                form_purpose TEXT,
                reason TEXT,
                consent TEXT NOT NULL
            );
        ");
    }

    return $pdo;
}
