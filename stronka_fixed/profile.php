<?php
require_once 'db.php';
$page_css = 'profile';
checkLogin();

$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch user data
$stmt = $pdo->prepare("
    SELECT u.*, m.full_name, m.membership_tier, m.id as member_id 
    FROM users u 
    LEFT JOIN members m ON u.id = m.user_id 
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$userData = $stmt->fetch();

// Auto-create member record if missing
if (empty($userData['full_name'])) {
    $pdo->prepare("INSERT OR IGNORE INTO members (user_id, full_name, membership_tier) VALUES (?, ?, 'basic')")
        ->execute([$userId, $userData['username']]);
    $stmt = $pdo->prepare("
        SELECT u.*, m.full_name, m.membership_tier, m.id as member_id 
        FROM users u LEFT JOIN members m ON u.id = m.user_id WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['update_profile'])) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?")
                ->execute([trim($_POST['username']), trim($_POST['email']), $userId]);
            $pdo->prepare("UPDATE members SET full_name = ? WHERE user_id = ?")
                ->execute([trim($_POST['full_name']), $userId]);

            if (!empty($_POST['new_password'])) {
                $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
                    ->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), $userId]);
            }
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (in_array($_FILES['profile_photo']['type'], $allowed)) {
                    $fileName = time() . '_' . basename($_FILES['profile_photo']['name']);
                    move_uploaded_file($_FILES['profile_photo']['tmp_name'], 'images/' . $fileName);
                    $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?")
                        ->execute([$fileName, $userId]);
                } else {
                    throw new Exception("Invalid file type. Only images are allowed.");
                }
            }
            $pdo->commit();
            $message = "Profile updated successfully!";
            header("Refresh:1");
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error while updating: " . $e->getMessage();
        }
    }

    if (isset($_POST['upgrade_membership'])) {
        $newTier = $_POST['tier'];
        if (in_array($newTier, ['basic', 'premium'])) {
            $pdo->prepare("UPDATE members SET membership_tier = ? WHERE user_id = ?")
                ->execute([$newTier, $userId]);
            $message = "Plan changed to " . strtoupper($newTier);
            header("Refresh:1");
        }
    }
}

// Re-fetch after update
if ($message) {
    $stmt = $pdo->prepare("
        SELECT u.*, m.full_name, m.membership_tier, m.id as member_id 
        FROM users u LEFT JOIN members m ON u.id = m.user_id WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
}

// Fetch enrolled classes
$memberId = $userData['member_id'] ?? null;
$enrolledClasses = [];
if ($memberId) {
    $stmt = $pdo->prepare("
        SELECT c.name, c.type, c.scheduled_at, c.duration_min, t.full_name as trainer_name
        FROM enrollments e
        JOIN classes c ON e.class_id = c.id
        JOIN trainers t ON c.trainer_id = t.id
        WHERE e.member_id = ?
        ORDER BY c.scheduled_at ASC
    ");
    $stmt->execute([$memberId]);
    $enrolledClasses = $stmt->fetchAll();
}

include 'views/profile.view.php';
