<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
$page_css = 'classes';

// Check if user is logged in
$userId = $_SESSION['member_id'] ?? null;

// Fetch trainers for filters
$trainers = $pdo->query("SELECT id, full_name FROM trainers")->fetchAll();

// Fetch all classes with enrollment count
$stmt = $pdo->prepare("
    SELECT c.*, t.full_name as trainer_name, 
    (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id) as current_enrollments,
    (SELECT 1 FROM enrollments WHERE class_id = c.id AND member_id = ?) as is_user_enrolled
    FROM classes c
    JOIN trainers t ON c.trainer_id = t.id
    ORDER BY c.scheduled_at ASC
");
$stmt->execute([$userId]);
$classes = $stmt->fetchAll();

include 'views/classes.view.php';
