<?php

// 1. Database configuration (Updated for InfinityFree)
$host = "sql310.infinityfree.com";
$dbname = "if0_43206184_collage";
$username = "if0_43206184";
$password = "Sudhir0286"; // Replace with your main hosting account/vPanel password

// 2. Connect to MySQL database
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Database connection failed. Check your MySQL settings.");
}

// 3. Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. Escape output to prevent XSS
if (!function_exists('h')) {
    function h($value)
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}

// 5. Generate CSRF security token
if (!function_exists('csrfToken')) {
    function csrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(
                random_bytes(32)
            );
        }

        return $_SESSION['csrf_token'];
    }
}

// 6. Verify CSRF token
if (!function_exists('verifyCsrf')) {
    function verifyCsrf()
    {
        $token = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            !is_string($sessionToken) ||
            $sessionToken === '' ||
            !hash_equals($sessionToken, $token)
        ) {
            http_response_code(403);
            exit("Invalid request token. Please refresh the page.");
        }
    }
}

// 7. Check administrator login
if (!function_exists('requireAdmin')) {
    function requireAdmin()
    {
        if (empty($_SESSION['admin_id'])) {
            http_response_code(403);
            exit(
                'Please log in as an administrator. '
                . '<a href="login.php">Login here</a>'
            );
        }
    }
}