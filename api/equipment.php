<?php
require_once __DIR__ . '/../db.php';

ensureSessionStarted();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to view equipment availability.',
    ]);
    exit();
}

try {
    $stmt = $pdo->query("
        SELECT id, name, category, total_count, available_count, status
        FROM equipment
        ORDER BY category, name
    ");

    echo json_encode([
        'success' => true,
        'equipment' => $stmt->fetchAll(),
        'updated_at' => date('H:i:s'),
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Could not load equipment availability.',
    ]);
}
