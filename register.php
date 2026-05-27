<?php
require_once 'db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $full_name = trim($_POST['full_name']);

    if ($username && $email && $password) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'member')")
                ->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $userId = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO members (user_id, full_name, membership_tier) VALUES (?, ?, 'basic')")
                ->execute([$userId, $full_name]);
            $pdo->commit();
            header("Location: login.php?registered=1");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Username or email already exists.";
        }
    } else {
        $error = "All fields are required.";
    }
}

include 'views/register.view.php';
