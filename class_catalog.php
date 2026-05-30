<?php
require_once 'db.php';

$page_css = 'class_catalog';
$page_js = 'class_catalog';

$classTypeImages = [
    'Yoga' => 'images/classes/yoga.jpg',
    'HIIT' => 'images/classes/hiit.jpg',
    'Pilates' => 'images/classes/pilates.jpg',
    'Spinning' => 'images/classes/spinning.jpg',
    'Strength' => 'images/classes/strength.jpg',
    'Cardio' => 'images/classes/cardio.jpg',
    'Other' => 'images/classes/other.jpg',
];

$classTypeDescriptions = [
    'Yoga' => 'Low-impact sessions focused on mobility, breathing, balance, and controlled strength.',
    'HIIT' => 'High-intensity interval training built around short bursts of effort and active recovery.',
    'Pilates' => 'Core-focused training that improves posture, stability, flexibility, and body control.',
    'Spinning' => 'Indoor cycling sessions with structured intervals for endurance and cardiovascular fitness.',
    'Strength' => 'Resistance-based classes designed to build muscle, improve technique, and develop power.',
    'Cardio' => 'Energy-focused workouts that improve stamina, conditioning, and overall fitness.',
    'Other' => 'Additional group training sessions offered by the gym.',
];

$stmt = $pdo->query("
    SELECT c.type,
           COUNT(DISTINCT CASE WHEN c.scheduled_at >= datetime('now', 'localtime') THEN c.id END) as session_count,
           COUNT(DISTINCT c.trainer_id) as trainer_count,
           GROUP_CONCAT(DISTINCT t.full_name) as trainer_names,
           MIN(CASE WHEN c.scheduled_at >= datetime('now', 'localtime') THEN c.scheduled_at END) as next_session,
           ROUND(AVG(r.rating), 1) as average_rating,
           COUNT(r.id) as review_count
    FROM classes c
    JOIN trainers t ON c.trainer_id = t.id
    LEFT JOIN reviews r ON r.class_id = c.id
    WHERE t.is_active = 1 AND COALESCE(c.is_cancelled, 0) = 0
    GROUP BY c.type
    ORDER BY c.type
");

$classTypes = $stmt->fetchAll();
$classReviewsByType = [];
$types = array_column($classTypes, 'type');

if (!empty($types)) {
    $placeholders = implode(',', array_fill(0, count($types), '?'));
    $stmt = $pdo->prepare("
        SELECT c.type,
               c.name as class_name,
               r.rating,
               r.comment,
               r.created_at,
               m.full_name,
               u.username
        FROM reviews r
        JOIN classes c ON r.class_id = c.id
        JOIN members m ON r.member_id = m.id
        JOIN users u ON m.user_id = u.id
        WHERE c.type IN ($placeholders)
        ORDER BY c.type, r.created_at DESC
    ");
    $stmt->execute($types);

    foreach ($stmt->fetchAll() as $review) {
        $classReviewsByType[$review['type']][] = $review;
    }
}

include 'views/class_catalog.view.php';
