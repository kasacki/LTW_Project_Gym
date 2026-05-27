<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
$page_css = 'classes';
$page_js = 'classes';

// Check if user is logged in
$userId = $_SESSION['member_id'] ?? null;

// Fetch trainers for filters
$trainers = $pdo->query("SELECT id, full_name FROM trainers")->fetchAll();

// Fetch all classes with enrollment and review summaries
$stmt = $pdo->prepare("
    SELECT c.*, t.full_name as trainer_name, 
    (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id) as current_enrollments,
    (SELECT 1 FROM enrollments WHERE class_id = c.id AND member_id = ?) as is_user_enrolled,
    (SELECT ROUND(AVG(rating), 1) FROM reviews WHERE class_id = c.id) as average_rating,
    (SELECT COUNT(*) FROM reviews WHERE class_id = c.id) as review_count
    FROM classes c
    JOIN trainers t ON c.trainer_id = t.id
    WHERE c.scheduled_at >= DATETIME('now', 'localtime')
    ORDER BY c.scheduled_at ASC
");
$stmt->execute([$userId]);
$classes = $stmt->fetchAll();

$classReviewsByClassId = [];
$classIds = array_column($classes, 'id');

if (!empty($classIds)) {
    $placeholders = implode(',', array_fill(0, count($classIds), '?'));
    $stmt = $pdo->prepare("
        SELECT r.class_id, r.rating, r.comment, r.created_at,
               u.username
        FROM reviews r
        JOIN members m ON r.member_id = m.id
        JOIN users u ON m.user_id = u.id
        WHERE r.class_id IN ($placeholders)
        ORDER BY r.created_at DESC
    ");
    $stmt->execute($classIds);

    foreach ($stmt->fetchAll() as $review) {
        $classReviewsByClassId[$review['class_id']][] = $review;
    }
}

include 'views/classes.view.php';
