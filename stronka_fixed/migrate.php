<?php
require_once 'db.php';

$migrations = [
    "CREATE TABLE IF NOT EXISTS trainer_reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        trainer_id INTEGER NOT NULL,
        member_id INTEGER NOT NULL,
        rating INTEGER NOT NULL CHECK(rating BETWEEN 1 AND 5),
        comment TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(trainer_id, member_id)
    )",

    "CREATE TABLE IF NOT EXISTS private_sessions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        trainer_id INTEGER NOT NULL,
        member_id INTEGER NOT NULL,
        requested_at DATETIME NOT NULL,
        duration_min INTEGER DEFAULT 60,
        notes TEXT,
        status TEXT DEFAULT 'pending' CHECK(status IN ('pending','confirmed','rejected','cancelled')),
        trainer_note TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
];

foreach ($migrations as $sql) {
    try {
        $pdo->exec($sql);
        echo "OK: " . substr($sql, 0, 60) . "...<br>";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "<br>";
    }
}

echo "<br><strong>Done! Delete this file.</strong>";
?>
