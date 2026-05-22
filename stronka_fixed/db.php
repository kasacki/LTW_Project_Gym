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

// Auto-migrate: ensure all columns exist
try {
    $pdo->exec("ALTER TABLE classes ADD COLUMN duration_min INTEGER DEFAULT 60");
} catch (Exception $e) { /* column already exists */ }
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN profile_photo TEXT");
} catch (Exception $e) { /* column already exists */ }



} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}

// Helper function to check if user is logged in
function checkLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>