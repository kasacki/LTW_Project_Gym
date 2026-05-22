<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit;
}

$memberId = $_SESSION['member_id'];
$classId = $_POST['class_id'];
$action = $_POST['action'];

if ($action === 'enroll') {
    // Sprawdzenie limitu miejsc
    $stmt = $pdo->prepare("SELECT capacity, (SELECT COUNT(*) FROM enrollments WHERE class_id = id) as current FROM classes WHERE id = ?");
    $stmt->execute([$classId]);
    $class = $stmt->fetch();

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