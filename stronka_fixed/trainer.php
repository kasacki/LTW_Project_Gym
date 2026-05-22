<?php
require_once 'db.php';
$page_css = 'trainer';
checkLogin();

if ($_SESSION['role'] !== 'trainer') {
    header("Location: index.php");
    exit();
}

$userId  = $_SESSION['user_id'];
$message = '';
$error   = '';

// Fetch trainer record
$stmt = $pdo->prepare("
    SELECT t.*, u.username, u.email, u.profile_photo
    FROM trainers t JOIN users u ON t.user_id = u.id
    WHERE t.user_id = ?
");
$stmt->execute([$userId]);
$trainer = $stmt->fetch();

if (!$trainer) { die("Trainer record not found."); }
$trainerId = $trainer['id'];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE users SET username=?, email=? WHERE id=?")
                ->execute([trim($_POST['username']), trim($_POST['email']), $userId]);
            $pdo->prepare("UPDATE trainers SET full_name=?, bio=?, specializations=?, certifications=? WHERE user_id=?")
                ->execute([trim($_POST['full_name']), trim($_POST['bio'] ?? ''), trim($_POST['specializations'] ?? ''), trim($_POST['certifications'] ?? ''), $userId]);
            if (!empty($_POST['new_password'])) {
                $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")
                    ->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), $userId]);
            }
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (in_array($_FILES['profile_photo']['type'], $allowed)) {
                    $fileName = time() . '_' . basename($_FILES['profile_photo']['name']);
                    move_uploaded_file($_FILES['profile_photo']['tmp_name'], 'images/' . $fileName);
                    $pdo->prepare("UPDATE users SET profile_photo=? WHERE id=?")->execute([$fileName, $userId]);
                } else {
                    throw new Exception("Invalid file type.");
                }
            }
            $pdo->commit();
            $message = "Profile updated successfully!";
            header("Refresh:1");
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }

    if ($action === 'respond_session') {
        $sessionId   = (int)$_POST['session_id'];
        $status      = $_POST['status'];
        $trainerNote = trim($_POST['trainer_note'] ?? '');
        if (in_array($status, ['confirmed', 'rejected'])) {
            $pdo->prepare("UPDATE private_sessions SET status=?, trainer_note=? WHERE id=? AND trainer_id=?")
                ->execute([$status, $trainerNote, $sessionId, $trainerId]);
            $message = "Session " . ucfirst($status) . ".";
        }
    }
}

// Re-fetch trainer
$stmt = $pdo->prepare("
    SELECT t.*, u.username, u.email, u.profile_photo
    FROM trainers t JOIN users u ON t.user_id = u.id WHERE t.user_id = ?
");
$stmt->execute([$userId]);
$trainer = $stmt->fetch();

// Fetch classes
$classesStmt = $pdo->prepare("
    SELECT c.*, (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id) as enrolled_count
    FROM classes c WHERE c.trainer_id = ? ORDER BY c.scheduled_at ASC
");
$classesStmt->execute([$trainerId]);
$myClasses = $classesStmt->fetchAll();

// Fetch rosters
$classRosters = [];
foreach ($myClasses as $class) {
    $stmt = $pdo->prepare("
        SELECT m.full_name, u.email, u.username, m.membership_tier, e.enrolled_at
        FROM enrollments e
        JOIN members m ON e.member_id = m.id
        JOIN users u ON m.user_id = u.id
        WHERE e.class_id = ? ORDER BY e.enrolled_at ASC
    ");
    $stmt->execute([$class['id']]);
    $classRosters[$class['id']] = $stmt->fetchAll();
}

// Fetch private sessions
$sessionsStmt = $pdo->prepare("
    SELECT ps.*, m.full_name as member_name, u.email as member_email
    FROM private_sessions ps
    JOIN members m ON ps.member_id = m.id
    JOIN users u ON m.user_id = u.id
    WHERE ps.trainer_id = ?
    ORDER BY CASE ps.status WHEN 'pending' THEN 0 ELSE 1 END, ps.requested_at ASC
");
$sessionsStmt->execute([$trainerId]);
$sessions = $sessionsStmt->fetchAll();

$pendingCount = count(array_filter($sessions, fn($s) => $s['status'] === 'pending'));

include 'views/trainer.view.php';
