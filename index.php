<?php
require_once 'db.php';
$page_css = 'index';
$page_js = 'index';

// Fetch featured classes
$featured_classes = $pdo->query("
    SELECT c.*, t.full_name as trainer_name
    FROM classes c
    JOIN trainers t ON c.trainer_id = t.id
    WHERE c.is_featured = 1
")->fetchAll();

// Fetch all trainers with avg rating
$trainers = $pdo->query("
    SELECT t.*, u.profile_photo,
           ROUND(AVG(r.rating), 1) as avg_rating,
           COUNT(r.id) as review_count
    FROM trainers t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN trainer_reviews r ON r.trainer_id = t.id
    WHERE t.is_active = 1
    GROUP BY t.id
    ORDER BY t.full_name
")->fetchAll();

include 'views/index.view.php';
