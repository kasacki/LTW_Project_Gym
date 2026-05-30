<?php
require_once 'db.php';
$page_css = 'trainer';
$page_js = 'trainer';
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
    require_csrf();

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


    if ($action === 'update_class') {
        $classId = (int)$_POST['class_id'];
        // Verify this class belongs to the trainer
        $check = $pdo->prepare("SELECT id, scheduled_at FROM classes WHERE id = ? AND trainer_id = ?");
        $check->execute([$classId, $trainerId]);
        $classRow = $check->fetch();
        if (!$classRow) {
            $error = "Class not found or access denied.";
        } elseif (strtotime($classRow['scheduled_at']) < time()) {
            $error = "Cannot edit a class that has already taken place.";
        } else {
            $name        = trim($_POST['class_name'] ?? '');
            $room        = trim($_POST['room'] ?? '');
            $scheduledAt = trim($_POST['scheduled_at'] ?? '');
            $durationMin = (int)($_POST['duration_min'] ?? 60);
            if (empty($name) || empty($scheduledAt) || $durationMin < 1) {
                $error = "Class name, date/time and duration are required.";
            } else {
                $pdo->prepare("UPDATE classes SET name=?, room=?, scheduled_at=?, duration_min=? WHERE id=? AND trainer_id=?")
                    ->execute([$name, $room, $scheduledAt, $durationMin, $classId, $trainerId]);
                $message = "Class updated successfully.";
            }
        }
    }

    if ($action === 'cancel_class') {
        $classId = (int)$_POST['class_id'];
        $reason  = trim($_POST['cancel_reason'] ?? '');
        // Verify this class belongs to the trainer
        $check = $pdo->prepare("SELECT id, scheduled_at FROM classes WHERE id = ? AND trainer_id = ?");
        $check->execute([$classId, $trainerId]);
        $classRow = $check->fetch();
        if (!$classRow) {
            $error = "Class not found or access denied.";
        } elseif (strtotime($classRow['scheduled_at']) < time()) {
            $error = "Cannot cancel a class that has already taken place.";
        } else {
            $pdo->prepare("UPDATE classes SET is_cancelled=1, cancelled_reason=? WHERE id=? AND trainer_id=?")
                ->execute([$reason, $classId, $trainerId]);
            $message = "Class cancelled. Enrolled members will see it as cancelled.";
        }
    }

    if ($action === 'restore_class') {
        $classId = (int)$_POST['class_id'];
        $check = $pdo->prepare("SELECT id FROM classes WHERE id = ? AND trainer_id = ?");
        $check->execute([$classId, $trainerId]);
        if ($check->fetch()) {
            $pdo->prepare("UPDATE classes SET is_cancelled=0, cancelled_reason=NULL WHERE id=? AND trainer_id=?")
                ->execute([$classId, $trainerId]);
            $message = "Class restored.";
        } else {
            $error = "Class not found or access denied.";
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

$classReviewsByClassId = [];
$classReviewSummaries = [];

// Fetch rosters in one query to avoid loading members class by class
$classRosters = [];
$classIds = array_column($myClasses, 'id');

foreach ($classIds as $classId) {
    $classRosters[$classId] = [];
    $classReviewsByClassId[$classId] = [];
    $classReviewSummaries[$classId] = ['average_rating' => null, 'review_count' => 0];
}

if (!empty($classIds)) {
    $placeholders = implode(',', array_fill(0, count($classIds), '?'));
    $stmt = $pdo->prepare("
        SELECT e.class_id, m.id as member_id, m.full_name, m.membership_tier,
               m.membership_status, u.email, u.username, e.enrolled_at
        FROM enrollments e
        JOIN members m ON e.member_id = m.id
        JOIN users u ON m.user_id = u.id
        WHERE e.class_id IN ($placeholders)
        ORDER BY e.class_id, m.full_name ASC
    ");
    $stmt->execute($classIds);

    foreach ($stmt->fetchAll() as $member) {
        $classRosters[$member['class_id']][] = $member;
    }

    $stmt = $pdo->prepare("
        SELECT r.class_id, r.rating, r.comment, r.created_at, u.username
        FROM reviews r
        JOIN members m ON r.member_id = m.id
        JOIN users u ON m.user_id = u.id
        WHERE r.class_id IN ($placeholders)
        ORDER BY r.class_id, r.created_at DESC
    ");
    $stmt->execute($classIds);

    foreach ($stmt->fetchAll() as $review) {
        $classReviewsByClassId[$review['class_id']][] = $review;
    }

    foreach ($classReviewsByClassId as $classId => $reviews) {
        if (empty($reviews)) {
            continue;
        }

        $ratings = array_column($reviews, 'rating');
        $classReviewSummaries[$classId] = [
            'average_rating' => round(array_sum($ratings) / count($ratings), 1),
            'review_count' => count($ratings),
        ];
    }
}

$totalRosterMembers = array_sum(array_map('count', $classRosters));
$upcomingClassesCount = count(array_filter($myClasses, fn($class) => strtotime($class['scheduled_at']) >= time()));
$completedClassesCount = count($myClasses) - $upcomingClassesCount;
$fullClassesCount = count(array_filter($myClasses, fn($class) => (int)$class['enrolled_count'] >= (int)$class['capacity']));

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
