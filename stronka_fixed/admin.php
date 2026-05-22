<?php
require_once 'db.php';
include 'header.php';
checkLogin();

// Access Guard
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo '<div style="text-align:center;padding:80px;font-family:sans-serif;">
            <h2 style="color:#991B1B;">Access Denied</h2>
            <p>You do not have permission to view this page.</p>
            <a href="index.php">Return home</a>
          </div>';
    include 'footer.php';
    exit();
}

$adminUserId = $_SESSION['user_id'];
$success = '';
$error   = '';

// POST Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        // MEMBERS
        if ($action === 'create_member') {
            $username = trim($_POST['username']);
            $email    = trim($_POST['email']);
            $fullName = trim($_POST['full_name']);
            $password = trim($_POST['password']);
            $tier     = $_POST['membership_tier'] ?? 'basic';
            $hash     = password_hash($password, PASSWORD_DEFAULT);

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO users (username,email,password_hash,role) VALUES (?,?,?,'member')");
            $stmt->execute([$username, $email, $hash]);
            $uid = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO members (user_id,full_name,membership_tier) VALUES (?,?,?)");
            $stmt->execute([$uid, $fullName, $tier]);
            $pdo->commit();
            $success = "Member '{$fullName}' created successfully.";
        }

        elseif ($action === 'update_member') {
            $uid      = (int)$_POST['user_id'];
            $username = trim($_POST['username']);
            $email    = trim($_POST['email']);
            $fullName = trim($_POST['full_name']);
            $status   = $_POST['membership_status'] ?? 'active';
            $tier     = $_POST['membership_tier'] ?? 'basic';

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE users SET username=?,email=? WHERE id=?");
            $stmt->execute([$username, $email, $uid]);
            if (!empty($_POST['new_password'])) {
                $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash, $uid]);
            }
            $stmt = $pdo->prepare("UPDATE members SET full_name=?,membership_tier=?,membership_status=? WHERE user_id=?");
            $stmt->execute([$fullName, $tier, $status, $uid]);
            $pdo->commit();
            $success = "Member updated.";
        }

        elseif ($action === 'deactivate_member') {
            $uid = (int)$_POST['user_id'];
            $pdo->prepare("UPDATE members SET membership_status='inactive' WHERE user_id=?")->execute([$uid]);
            $success = "Member deactivated.";
        }

        // TRAINERS
        elseif ($action === 'create_trainer') {
            $username = trim($_POST['username']);
            $email    = trim($_POST['email']);
            $fullName = trim($_POST['full_name']);
            $password = trim($_POST['password']);
            $bio      = trim($_POST['bio'] ?? '');
            $specs    = trim($_POST['specializations'] ?? '');
            $certs    = trim($_POST['certifications'] ?? '');
            $hash     = password_hash($password, PASSWORD_DEFAULT);

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO users (username,email,password_hash,role) VALUES (?,?,?,'trainer')");
            $stmt->execute([$username, $email, $hash]);
            $uid = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO trainers (user_id,full_name,bio,specializations,certifications) VALUES (?,?,?,?,?)");
            $stmt->execute([$uid, $fullName, $bio, $specs, $certs]);
            $pdo->commit();
            $success = "Trainer '{$fullName}' created successfully.";
        }

        elseif ($action === 'update_trainer') {
            $uid      = (int)$_POST['user_id'];
            $username = trim($_POST['username']);
            $email    = trim($_POST['email']);
            $fullName = trim($_POST['full_name']);
            $bio      = trim($_POST['bio'] ?? '');
            $specs    = trim($_POST['specializations'] ?? '');
            $certs    = trim($_POST['certifications'] ?? '');

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE users SET username=?,email=? WHERE id=?");
            $stmt->execute([$username, $email, $uid]);
            if (!empty($_POST['new_password'])) {
                $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash, $uid]);
            }
            $stmt = $pdo->prepare("UPDATE trainers SET full_name=?,bio=?,specializations=?,certifications=? WHERE user_id=?");
            $stmt->execute([$fullName, $bio, $specs, $certs, $uid]);
            $pdo->commit();
            $success = "Trainer updated.";
        }

        elseif ($action === 'deactivate_trainer') {
            $uid = (int)$_POST['user_id'];
            $pdo->prepare("UPDATE trainers SET bio = COALESCE(bio,'') || ' [DEACTIVATED]' WHERE user_id=?")->execute([$uid]);
            $success = "Trainer account deactivated.";
        }

        // CLASSES
        elseif ($action === 'create_class') {
            $name        = trim($_POST['name']);
            $type        = trim($_POST['type']);
            $trainerId   = (int)$_POST['trainer_id'];
            $room        = trim($_POST['room'] ?? '');
            $capacity    = (int)$_POST['capacity'];
            $scheduledAt = trim($_POST['scheduled_at']);
            $durationMin = (int)$_POST['duration_min'];
            $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;

            $stmt = $pdo->prepare("INSERT INTO classes (name,type,trainer_id,room,capacity,scheduled_at,duration_min,is_featured) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$name,$type,$trainerId,$room,$capacity,$scheduledAt,$durationMin,$isFeatured]);
            $success = "Class '{$name}' created successfully.";
        }

        elseif ($action === 'update_class') {
            $classId     = (int)$_POST['class_id'];
            $name        = trim($_POST['name']);
            $type        = trim($_POST['type']);
            $trainerId   = (int)$_POST['trainer_id'];
            $room        = trim($_POST['room'] ?? '');
            $capacity    = (int)$_POST['capacity'];
            $scheduledAt = trim($_POST['scheduled_at']);
            $durationMin = (int)$_POST['duration_min'];
            $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;

            $stmt = $pdo->prepare("UPDATE classes SET name=?,type=?,trainer_id=?,room=?,capacity=?,scheduled_at=?,duration_min=?,is_featured=? WHERE id=?");
            $stmt->execute([$name,$type,$trainerId,$room,$capacity,$scheduledAt,$durationMin,$isFeatured,$classId]);
            $success = "Class updated.";
        }

        elseif ($action === 'delete_class') {
            $classId = (int)$_POST['class_id'];
            $pdo->prepare("DELETE FROM enrollments WHERE class_id=?")->execute([$classId]);
            $pdo->prepare("DELETE FROM classes WHERE id=?")->execute([$classId]);
            $success = "Class removed.";
        }

        // EQUIPMENT
        elseif ($action === 'create_equipment') {
            $name      = trim($_POST['name']);
            $category  = trim($_POST['category'] ?? '');
            $total     = (int)$_POST['total_count'];
            $available = (int)$_POST['available_count'];
            $status    = $_POST['status'] ?? 'operational';

            $stmt = $pdo->prepare("INSERT INTO equipment (name,category,total_count,available_count,status) VALUES (?,?,?,?,?)");
            $stmt->execute([$name,$category,$total,$available,$status]);
            $success = "Equipment '{$name}' added.";
        }

        elseif ($action === 'update_equipment') {
            $eqId      = (int)$_POST['equipment_id'];
            $name      = trim($_POST['name']);
            $category  = trim($_POST['category'] ?? '');
            $total     = (int)$_POST['total_count'];
            $available = (int)$_POST['available_count'];
            $status    = $_POST['status'] ?? 'operational';

            $stmt = $pdo->prepare("UPDATE equipment SET name=?,category=?,total_count=?,available_count=?,status=? WHERE id=?");
            $stmt->execute([$name,$category,$total,$available,$status,$eqId]);
            $success = "Equipment updated.";
        }

        elseif ($action === 'delete_equipment') {
            $eqId = (int)$_POST['equipment_id'];
            $pdo->prepare("DELETE FROM equipment WHERE id=?")->execute([$eqId]);
            $success = "Equipment removed.";
        }

        // ELEVATE TO ADMIN
        elseif ($action === 'elevate_admin') {
            $uid      = (int)$_POST['user_id'];
            $adminRow = $pdo->prepare("SELECT id FROM admins WHERE user_id=?");
            $adminRow->execute([$adminUserId]);
            $adminRecord = $adminRow->fetch();
            $adminId     = $adminRecord ? $adminRecord['id'] : null;

            $pdo->beginTransaction();
            $pdo->prepare("UPDATE users SET role='admin' WHERE id=?")->execute([$uid]);
            $pdo->prepare("DELETE FROM members WHERE user_id=?")->execute([$uid]);
            $pdo->prepare("DELETE FROM trainers WHERE user_id=?")->execute([$uid]);
            $exists = $pdo->prepare("SELECT id FROM admins WHERE user_id=?");
            $exists->execute([$uid]);
            if (!$exists->fetch()) {
                $pdo->prepare("INSERT INTO admins (user_id,created_by) VALUES (?,?)")->execute([$uid, $adminId]);
            }
            $pdo->commit();
            $success = "User elevated to Admin.";
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Data Fetch
$members = $pdo->query("
    SELECT u.id as user_id, u.username, u.email, m.full_name,
           m.membership_tier, m.membership_status
    FROM users u JOIN members m ON u.id = m.user_id
    ORDER BY m.full_name
")->fetchAll();

$trainers = $pdo->query("
    SELECT u.id as user_id, u.username, u.email, t.id as trainer_id,
           t.full_name, t.bio, t.specializations, t.certifications
    FROM users u JOIN trainers t ON u.id = t.user_id
    ORDER BY t.full_name
")->fetchAll();

$classes = $pdo->query("
    SELECT c.*, t.full_name as trainer_name
    FROM classes c JOIN trainers t ON c.trainer_id = t.id
    ORDER BY c.scheduled_at
")->fetchAll();

$equipment = $pdo->query("SELECT * FROM equipment ORDER BY category, name")->fetchAll();

$nonAdmins = $pdo->query("
    SELECT u.id, u.username, u.email, u.role
    FROM users u WHERE u.role != 'admin'
    ORDER BY u.role, u.username
")->fetchAll();

$statsMembers  = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$statsTrainers = $pdo->query("SELECT COUNT(*) FROM trainers")->fetchColumn();
$statsClasses  = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$statsEquip    = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();

// Load view
include 'views/admin_view.php';
