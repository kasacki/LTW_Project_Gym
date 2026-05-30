<?php
require_once 'db.php';
checkLogin(); 

$page_css = 'trainers';
$role = $_SESSION['role'] ?? 'member';

$activeCondition = ($role === 'admin') ? "1=1" : "t.is_active = 1";

$trainers = $pdo->query("
    SELECT t.id,
           t.full_name,
           t.bio,
           t.specializations,
           t.certifications,
           t.is_active,
           u.profile_photo,
           ROUND(AVG(tr.rating), 1) as average_rating,
           COUNT(tr.id) as review_count
    FROM trainers t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN trainer_reviews tr ON tr.trainer_id = t.id
    WHERE $activeCondition
    GROUP BY t.id
    ORDER BY t.full_name
")->fetchAll();

$classesByTrainer = [];
$trainerIds = array_column($trainers, 'id');

if (!empty($trainerIds)) {
    $placeholders = implode(',', array_fill(0, count($trainerIds), '?'));
    $stmt = $pdo->prepare("
        SELECT trainer_id, name, type, scheduled_at
        FROM classes
        WHERE trainer_id IN ($placeholders)
        ORDER BY scheduled_at ASC
    ");
    $stmt->execute($trainerIds);

    foreach ($stmt->fetchAll() as $class) {
        $classesByTrainer[$class['trainer_id']][] = $class;
    }
}

include 'views/trainers.view.php';