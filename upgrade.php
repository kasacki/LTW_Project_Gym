<?php
require_once 'db.php';
$page_css = 'profile';
checkLogin();

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT m.membership_tier 
    FROM users u 
    LEFT JOIN members m ON u.id = m.user_id 
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$userData = $stmt->fetch();

include 'views/upgrade.view.php';
?>