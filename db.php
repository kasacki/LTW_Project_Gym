<?php
// db.php - SQLite database connection

try {
    // Path to database file
    $db_path = __DIR__ . '/database/database.db';
    
    $pdo = new PDO("sqlite:" . $db_path);
    
    // Set error mode to throw exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative arrays
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Enable foreign key support in SQLite
    $pdo->exec("PRAGMA foreign_keys = ON;");

} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}

function ensureSessionStarted() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function csrf_token() {
    ensureSessionStarted();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf_token($token) {
    ensureSessionStarted();
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function require_csrf($json = false) {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf_token($token)) {
        return;
    }

    http_response_code(403);
    if ($json) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page.']);
    } else {
        die('Invalid security token. Please go back, refresh the page, and try again.');
    }
    exit();
}

// Helper function to check if user is logged in
function checkLogin() {
    ensureSessionStarted();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>
