<?php
require_once 'db.php';

$sql = "
-- Users and members tables
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT DEFAULT 'member',
    profile_photo TEXT
);

CREATE TABLE IF NOT EXISTS members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    full_name TEXT NOT NULL,
    membership_tier TEXT DEFAULT 'basic',
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Trainers and classes tables
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

-- Enrollments and equipment tables
CREATE TABLE IF NOT EXISTS enrollments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER,
    class_id INTEGER,
    enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(member_id, class_id),
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

CREATE TABLE IF NOT EXISTS equipment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    category TEXT,
    status TEXT DEFAULT 'operational',
    total_count INTEGER,
    available_count INTEGER
);

-- Insert sample equipment
INSERT OR IGNORE INTO equipment (name, category, total_count, available_count) VALUES 
('Matrix V2 Treadmill', 'Cardio', 10, 8),
('dubels 2-20kg', 'Weights', 5, 5),
('Leg Press Machine', 'Strength', 3, 2);
";

try {

    $pdo->exec($sql);
    echo "<h1>DATABASE STRUCTURE READY!</h1>";
    echo "<p>All tables (schedule, enrollments, equipment, profiles) have been created.</p>";
    echo "<a href='index.php'>Go to the website</a>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}