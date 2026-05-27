<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

require_csrf(true);

if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit;
}

$memberId = (int)$_SESSION['member_id'];
$classId = (int)($_POST['class_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($classId <= 0 || !in_array($action, ['enroll', 'cancel'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

if ($action === 'enroll') {
    // checking teh date and class capacity limit
    
    $stmt = $pdo->prepare("SELECT capacity, scheduled_at, (SELECT COUNT(*) FROM enrollments WHERE class_id = id) as current FROM classes WHERE id = ?");
    $stmt->execute([$classId]);
    $class = $stmt->fetch();

    if (!$class) {
        echo json_encode(['success' => false, 'message' => 'Class not found.']);
        exit;
    }

    if (strtotime($class['scheduled_at']) < time()) {
        echo json_encode(['success' => false, 'message' => 'This class has already taken place.']);
        exit;
    }

    if ($class['current'] < $class['capacity']) {
        $stmt = $pdo->prepare("INSERT INTO enrollments (member_id, class_id) VALUES (?, ?)");
        try {
            $stmt->execute([$memberId, $classId]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'You are already registered.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No vacancies.']);
    }
} else {
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE member_id = ? AND class_id = ?");
    $stmt->execute([$memberId, $classId]);
    echo json_encode(['success' => true]);
}
