<?php
require_once 'db.php';
// This code creates tables if they don't exist
$query = "
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT DEFAULT 'member',
    profile_photo TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trainers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    full_name TEXT NOT NULL,
    bio TEXT,
    specializations TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    type TEXT,
    trainer_id INTEGER,
    scheduled_at DATETIME,
    duration_min INTEGER DEFAULT 60,
    capacity INTEGER DEFAULT 20,
    is_featured BOOLEAN DEFAULT 0,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id)
);
";

try {
    $pdo->exec($query);
    echo "Done";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}