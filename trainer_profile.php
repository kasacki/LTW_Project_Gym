<?php
require_once 'db.php';
$page_css = 'trainer_profile';
$page_js = 'trainer_profile';
checkLogin();

$trainerId = (int)($_GET['id'] ?? 0);
if (!$trainerId) { header("Location: index.php"); exit(); }

$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'];
$message = '';
$error   = '';

// Fetch trainer
$stmt = $pdo->prepare("
    SELECT t.*, u.profile_photo, u.email
    FROM trainers t JOIN users u ON t.user_id = u.id
    WHERE t.id = ?
");
$stmt->execute([$trainerId]);
$trainer = $stmt->fetch();
if (!$trainer) { header("Location: index.php"); exit(); }

// Block access to deactivated trainer profiles (unless admin)
if (!($trainer['is_active'] ?? 1) && ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: trainers.php");
    exit();
}

// Get member_id of current user (if member)
$memberId = null;
if ($role === 'member') {
    $row = $pdo->prepare("SELECT id FROM members WHERE user_id = ?");
    $row->execute([$userId]);
    $r = $row->fetch();
    $memberId = $r ? $r['id'] : null;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    if (!$memberId) {
        http_response_code(403);
        $error = "Only members can submit reviews or book sessions.";
    } else {
        $action = $_POST['action'] ?? '';

        // Submit review
        if ($action === 'submit_review') {
            $rating  = (int)$_POST['rating'];
            $comment = trim($_POST['comment'] ?? '');
            if ($rating < 1 || $rating > 5) {
                $error = "Rating must be between 1 and 5.";
            } else {
                try {
                    $pdo->prepare("
                        INSERT INTO trainer_reviews (trainer_id, member_id, rating, comment)
                        VALUES (?, ?, ?, ?)
                        ON CONFLICT(trainer_id, member_id) DO UPDATE SET rating=excluded.rating, comment=excluded.comment, created_at=CURRENT_TIMESTAMP
                    ")->execute([$trainerId, $memberId, $rating, $comment]);
                    $message = "Review submitted!";
                } catch (Exception $e) {
                    $error = "Error: " . $e->getMessage();
                }
            }
        }

        // Book private session
        if ($action === 'book_session') {
            $requestedAt = trim($_POST['requested_at']);
            $duration    = (int)($_POST['duration_min'] ?? 60);
            $notes       = trim($_POST['notes'] ?? '');
            if (empty($requestedAt)) {
                $error = "Please select a date and time.";
            } else {
                try {
                    $pdo->prepare("
                        INSERT INTO private_sessions (trainer_id, member_id, requested_at, duration_min, notes)
                        VALUES (?, ?, ?, ?, ?)
                    ")->execute([$trainerId, $memberId, $requestedAt, $duration, $notes]);
                    $message = "Session request sent! Wait for trainer confirmation.";
                } catch (Exception $e) {
                    $error = "Error: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch reviews
$reviews = $pdo->prepare("
    SELECT r.*, m.full_name as member_name
    FROM trainer_reviews r
    JOIN members m ON r.member_id = m.id
    WHERE r.trainer_id = ?
    ORDER BY r.created_at DESC
");
$reviews->execute([$trainerId]);
$reviews = $reviews->fetchAll();

$avgRating    = count($reviews) ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : null;
$reviewCount  = count($reviews);

// My existing review
$myReview = null;
if ($memberId) {
    $stmt = $pdo->prepare("SELECT * FROM trainer_reviews WHERE trainer_id=? AND member_id=?");
    $stmt->execute([$trainerId, $memberId]);
    $myReview = $stmt->fetch();
}

// Trainer's upcoming classes
$trainerClasses = $pdo->prepare("
    SELECT c.*,
           (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id) as enrolled_count
    FROM classes c
    WHERE c.trainer_id = ? AND c.scheduled_at > datetime('now')
    ORDER BY c.scheduled_at ASC
    LIMIT 5
");
$trainerClasses->execute([$trainerId]);
$trainerClasses = $trainerClasses->fetchAll();

include 'views/trainer_profile.view.php';
