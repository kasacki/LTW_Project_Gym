<?php
require_once 'db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $stmt = $pdo->prepare("
        SELECT u.*, m.full_name, m.id as member_id 
        FROM users u LEFT JOIN members m ON u.id = m.user_id 
        WHERE u.username = ?
    ");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
        $_SESSION['full_name']= $user['full_name'];
        $_SESSION['member_id']= $user['member_id'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Incorrect login or password.";
    }
}

include 'views/login.view.php';
