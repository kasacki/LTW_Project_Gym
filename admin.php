<?php
require_once 'db.php';
$page_css = 'admin';
$page_js = 'admin';
include 'header.php';
checkLogin();

// Access Guard
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo '<div class="access-denied">
            <h2>Access Denied</h2>
            <p>You do not have permission to view this page.</p>
            <a href="index.php">Return home</a>
          </div>';
    include 'footer.php';
    exit();
}

$adminUserId = $_SESSION['user_id'];
$success = '';
$error   = '';
$validAdminSections = ['overview', 'members', 'trainers', 'classes', 'equipment'];
$activeAdminSection = 'overview';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedSection = $_POST['admin_section'] ?? '';
    if (in_array($postedSection, $validAdminSections, true)) {
        $activeAdminSection = $postedSection;
    }
}

function readNonNegativeInt(string $field, string $label): int {
    $value = filter_input(INPUT_POST, $field, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0],
    ]);

    if ($value === false || $value === null) {
        throw new InvalidArgumentException("$label must be a non-negative whole number.");
    }

    return $value;
}

function readPositiveInt(string $field, string $label, int $min = 1): int {
    $value = filter_input(INPUT_POST, $field, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => $min],
    ]);

    if ($value === false || $value === null) {
        throw new InvalidArgumentException("$label must be at least $min.");
    }

    return $value;
}

function readRequiredString(string $field, string $label, int $maxLength): string {
    $value = trim($_POST[$field] ?? '');

    if ($value === '') {
        throw new InvalidArgumentException("$label is required.");
    }

    if (strlen($value) > $maxLength) {
        throw new InvalidArgumentException("$label must be $maxLength characters or fewer.");
    }

    return $value;
}

function readOptionalString(string $field, int $maxLength): string {
    $value = trim($_POST[$field] ?? '');

    if (strlen($value) > $maxLength) {
        throw new InvalidArgumentException("This field must be $maxLength characters or fewer.");
    }

    return $value;
}

function readEmail(string $field = 'email'): string {
    $email = trim($_POST[$field] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Please enter a valid email address.');
    }

    if (strlen($email) > 120) {
        throw new InvalidArgumentException('Email must be 120 characters or fewer.');
    }

    return $email;
}

function readUsername(): string {
    $username = readRequiredString('username', 'Username', 30);

    if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $username)) {
        throw new InvalidArgumentException('Username must be 3-30 characters and may only contain letters, numbers, dots, underscores, or hyphens.');
    }

    return $username;
}

function readPassword(string $field, bool $required): ?string {
    $password = trim($_POST[$field] ?? '');

    if ($password === '') {
        if ($required) {
            throw new InvalidArgumentException('Password is required.');
        }

        return null;
    }

    if (strlen($password) < 6) {
        throw new InvalidArgumentException('Password must be at least 6 characters.');
    }

    return $password;
}

function ensureUniqueUser(PDO $pdo, string $username, string $email, ?int $excludeUserId = null): void {
    $stmt = $pdo->prepare("
        SELECT id, username, email
        FROM users
        WHERE (LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?))
          AND (? IS NULL OR id != ?)
        LIMIT 1
    ");
    $stmt->execute([$username, $email, $excludeUserId, $excludeUserId]);
    $existingUser = $stmt->fetch();

    if (!$existingUser) {
        return;
    }

    if (strcasecmp($existingUser['username'], $username) === 0) {
        throw new InvalidArgumentException('Username is already in use.');
    }

    throw new InvalidArgumentException('Email is already in use.');
}

function ensureUserRoleExists(PDO $pdo, int $userId, string $role): void {
    if ($userId <= 0) {
        throw new InvalidArgumentException('Invalid user record.');
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = ?");
    $stmt->execute([$userId, $role]);

    if (!$stmt->fetch()) {
        throw new InvalidArgumentException('User record not found.');
    }
}

function ensureTrainerExists(PDO $pdo, int $trainerId): void {
    $stmt = $pdo->prepare("
        SELECT t.id
        FROM trainers t
        JOIN users u ON t.user_id = u.id
        WHERE t.id = ?
          AND u.role = 'trainer'
          AND COALESCE(t.bio, '') NOT LIKE '%[DEACTIVATED]%'
    ");
    $stmt->execute([$trainerId]);

    if (!$stmt->fetch()) {
        throw new InvalidArgumentException('Please choose an active trainer.');
    }
}

function validateMemberInput(PDO $pdo, bool $isCreate, ?int $userId = null): array {
    $allowedTiers = ['basic', 'premium'];
    $allowedStatuses = ['active', 'inactive'];

    $username = readUsername();
    $email = readEmail();
    $fullName = readRequiredString('full_name', 'Full name', 100);
    $tier = $_POST['membership_tier'] ?? 'basic';
    $status = $_POST['membership_status'] ?? 'active';
    $password = readPassword($isCreate ? 'password' : 'new_password', $isCreate);

    if (!in_array($tier, $allowedTiers, true)) {
        throw new InvalidArgumentException('Please choose a valid membership plan.');
    }

    if (!in_array($status, $allowedStatuses, true)) {
        throw new InvalidArgumentException('Please choose a valid membership status.');
    }

    ensureUniqueUser($pdo, $username, $email, $userId);

    return [$username, $email, $fullName, $tier, $status, $password];
}

function validateTrainerInput(PDO $pdo, bool $isCreate, ?int $userId = null): array {
    $username = readUsername();
    $email = readEmail();
    $fullName = readRequiredString('full_name', 'Full name', 100);
    $bio = readOptionalString('bio', 1000);
    $specs = readOptionalString('specializations', 255);
    $certs = readOptionalString('certifications', 255);
    $password = readPassword($isCreate ? 'password' : 'new_password', $isCreate);

    ensureUniqueUser($pdo, $username, $email, $userId);

    return [$username, $email, $fullName, $bio, $specs, $certs, $password];
}

function validateClassInput(PDO $pdo, ?int $classId = null): array {
    $allowedTypes = ['HIIT', 'Yoga', 'Pilates', 'Spinning', 'Strength', 'Cardio', 'Other'];

    $name = readRequiredString('name', 'Class name', 100);
    $type = trim($_POST['type'] ?? '');
    $trainerId = readPositiveInt('trainer_id', 'Trainer');
    $room = readOptionalString('room', 80);
    $capacity = readPositiveInt('capacity', 'Capacity', 1);
    $scheduledAt = trim($_POST['scheduled_at'] ?? '');
    $durationMin = readPositiveInt('duration_min', 'Duration', 5);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    if (!in_array($type, $allowedTypes, true)) {
        throw new InvalidArgumentException('Please choose a valid class type.');
    }

    ensureTrainerExists($pdo, $trainerId);

    $timestamp = strtotime($scheduledAt);
    if ($timestamp === false) {
        throw new InvalidArgumentException('Please choose a valid class date and time.');
    }

    $scheduledAt = date('Y-m-d H:i:s', $timestamp);

    if ($classId !== null) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE class_id = ?");
        $stmt->execute([$classId]);
        $enrolledCount = (int)$stmt->fetchColumn();

        if ($capacity < $enrolledCount) {
            throw new InvalidArgumentException('Capacity cannot be lower than the number of enrolled members.');
        }
    }

    return [$name, $type, $trainerId, $room, $capacity, $scheduledAt, $durationMin, $isFeatured];
}

function validateEquipmentInput(): array {
    $allowedCategories = ['Cardio', 'Weights', 'Strength', 'Functional', 'Stretching', 'Other'];
    $allowedStatuses = ['operational', 'maintenance', 'retired'];

    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $total = readNonNegativeInt('total_count', 'Total count');
    $available = readNonNegativeInt('available_count', 'Available count');

    if ($name === '') {
        throw new InvalidArgumentException('Equipment name is required.');
    }

    if (strlen($name) > 100) {
        throw new InvalidArgumentException('Equipment name must be 100 characters or fewer.');
    }

    if (!in_array($category, $allowedCategories, true)) {
        throw new InvalidArgumentException('Please choose a valid equipment category.');
    }

    if (!in_array($status, $allowedStatuses, true)) {
        throw new InvalidArgumentException('Please choose a valid equipment status.');
    }

    if ($available > $total) {
        throw new InvalidArgumentException('Available count cannot be greater than total count.');
    }

    return [$name, $category, $total, $available, $status];
}

// POST Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $action = $_POST['action'] ?? '';

    try {
        // MEMBERS
        if ($action === 'create_member') {
            [$username, $email, $fullName, $tier, $_status, $password] = validateMemberInput($pdo, true);
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
            $uid = readPositiveInt('user_id', 'User');
            ensureUserRoleExists($pdo, $uid, 'member');
            [$username, $email, $fullName, $tier, $status, $password] = validateMemberInput($pdo, false, $uid);

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE users SET username=?,email=? WHERE id=?");
            $stmt->execute([$username, $email, $uid]);
            if ($password !== null) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash, $uid]);
            }
            $stmt = $pdo->prepare("UPDATE members SET full_name=?,membership_tier=?,membership_status=? WHERE user_id=?");
            $stmt->execute([$fullName, $tier, $status, $uid]);
            $pdo->commit();
            $success = "Member updated.";
        }

        elseif ($action === 'deactivate_member') {
            $uid = readPositiveInt('user_id', 'User');
            ensureUserRoleExists($pdo, $uid, 'member');
            $stmt = $pdo->prepare("UPDATE members SET membership_status='inactive' WHERE user_id=?");
            $stmt->execute([$uid]);
            $success = "Member deactivated.";
        }

        // TRAINERS
        elseif ($action === 'create_trainer') {
            [$username, $email, $fullName, $bio, $specs, $certs, $password] = validateTrainerInput($pdo, true);
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
            $uid = readPositiveInt('user_id', 'User');
            ensureUserRoleExists($pdo, $uid, 'trainer');
            [$username, $email, $fullName, $bio, $specs, $certs, $password] = validateTrainerInput($pdo, false, $uid);

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE users SET username=?,email=? WHERE id=?");
            $stmt->execute([$username, $email, $uid]);
            if ($password !== null) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash, $uid]);
            }
            $stmt = $pdo->prepare("UPDATE trainers SET full_name=?,bio=?,specializations=?,certifications=? WHERE user_id=?");
            $stmt->execute([$fullName, $bio, $specs, $certs, $uid]);
            $pdo->commit();
            $success = "Trainer updated.";
        }

        elseif ($action === 'deactivate_trainer') {
            $uid = readPositiveInt('user_id', 'User');
            ensureUserRoleExists($pdo, $uid, 'trainer');
            $stmt = $pdo->prepare("
                UPDATE trainers
                SET bio = CASE
                    WHEN COALESCE(bio, '') LIKE '%[DEACTIVATED]%' THEN bio
                    ELSE COALESCE(bio, '') || ' [DEACTIVATED]'
                END
                WHERE user_id=?
            ");
            $stmt->execute([$uid]);
            $success = "Trainer account deactivated.";
        }

        // CLASSES
        elseif ($action === 'create_class') {
            [$name, $type, $trainerId, $room, $capacity, $scheduledAt, $durationMin, $isFeatured] = validateClassInput($pdo);

            $stmt = $pdo->prepare("INSERT INTO classes (name,type,trainer_id,room,capacity,scheduled_at,duration_min,is_featured) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$name,$type,$trainerId,$room,$capacity,$scheduledAt,$durationMin,$isFeatured]);
            $success = "Class '{$name}' created successfully.";
        }

        elseif ($action === 'update_class') {
            $classId = readPositiveInt('class_id', 'Class');

            $exists = $pdo->prepare("SELECT id FROM classes WHERE id=?");
            $exists->execute([$classId]);
            if (!$exists->fetch()) {
                throw new InvalidArgumentException('Class record not found.');
            }

            [$name, $type, $trainerId, $room, $capacity, $scheduledAt, $durationMin, $isFeatured] = validateClassInput($pdo, $classId);

            $stmt = $pdo->prepare("UPDATE classes SET name=?,type=?,trainer_id=?,room=?,capacity=?,scheduled_at=?,duration_min=?,is_featured=? WHERE id=?");
            $stmt->execute([$name,$type,$trainerId,$room,$capacity,$scheduledAt,$durationMin,$isFeatured,$classId]);
            $success = "Class updated.";
        }

        elseif ($action === 'delete_class') {
            $classId = readPositiveInt('class_id', 'Class');

            $exists = $pdo->prepare("SELECT id FROM classes WHERE id=?");
            $exists->execute([$classId]);
            if (!$exists->fetch()) {
                throw new InvalidArgumentException('Class record not found.');
            }

            $pdo->prepare("DELETE FROM enrollments WHERE class_id=?")->execute([$classId]);
            $pdo->prepare("DELETE FROM classes WHERE id=?")->execute([$classId]);
            $success = "Class removed.";
        }

        // EQUIPMENT
        elseif ($action === 'create_equipment') {
            [$name, $category, $total, $available, $status] = validateEquipmentInput();

            $stmt = $pdo->prepare("INSERT INTO equipment (name,category,total_count,available_count,status) VALUES (?,?,?,?,?)");
            $stmt->execute([$name,$category,$total,$available,$status]);
            $success = "Equipment '{$name}' added.";
        }

        elseif ($action === 'update_equipment') {
            $eqId      = (int)$_POST['equipment_id'];
            [$name, $category, $total, $available, $status] = validateEquipmentInput();

            if ($eqId <= 0) {
                throw new InvalidArgumentException('Invalid equipment record.');
            }

            $exists = $pdo->prepare("SELECT id FROM equipment WHERE id=?");
            $exists->execute([$eqId]);
            if (!$exists->fetch()) {
                throw new InvalidArgumentException('Equipment record not found.');
            }

            $stmt = $pdo->prepare("UPDATE equipment SET name=?,category=?,total_count=?,available_count=?,status=? WHERE id=?");
            $stmt->execute([$name,$category,$total,$available,$status,$eqId]);

            $success = "Equipment updated.";
        }

        elseif ($action === 'delete_equipment') {
            $eqId = (int)$_POST['equipment_id'];
            if ($eqId <= 0) {
                throw new InvalidArgumentException('Invalid equipment record.');
            }

            $stmt = $pdo->prepare("DELETE FROM equipment WHERE id=?");
            $stmt->execute([$eqId]);

            if ($stmt->rowCount() === 0) {
                throw new InvalidArgumentException('Equipment record not found.');
            }

            $success = "Equipment removed.";
        }

        // ELEVATE TO ADMIN
        elseif ($action === 'elevate_admin') {
            $uid = readPositiveInt('user_id', 'User');
            $targetUser = $pdo->prepare("SELECT id, username, role FROM users WHERE id=?");
            $targetUser->execute([$uid]);
            $targetUserRecord = $targetUser->fetch();

            if (!$targetUserRecord) {
                throw new InvalidArgumentException('User record not found.');
            }

            if ($targetUserRecord['role'] === 'admin') {
                throw new InvalidArgumentException('This user is already an admin.');
            }

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
            $success = "User '{$targetUserRecord['username']}' elevated to Admin.";
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

$statsMembers  = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$statsTrainers = $pdo->query("SELECT COUNT(*) FROM trainers")->fetchColumn();
$statsClasses  = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$statsEquip    = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();

// Load view
include 'views/admin_view.php';
