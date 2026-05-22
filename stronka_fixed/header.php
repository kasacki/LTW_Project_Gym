<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitLife Gym</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Nunito:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/admin.css">


    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="css/<?= $page_css ?>.css">
    <?php endif; ?>
</head>
<body>

<header>
    <nav class="navbar">
        <div class="logo">FITLIFE</div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="classes.php">Classes</a></li>
            <link rel="stylesheet" href="css/<?= $page_css ?>.css">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="equipment.php">Equipment</a></li>
                <li><a href="profile.php">Profile</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="admin.php" class="nav-admin">Admin</a></li>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'trainer'): ?>
                 <li><a href="trainer.php" class="nav-trainer">My Panel</a></li>
                <?php endif; ?>
                <li><a href="logout.php" class="btn btn-secondary" style="padding: 5px 15px;">Log Out</a></li>
            <?php else: ?>
                <li><a href="login.php" class="btn btn-primary" style="padding: 5px 15px;">Log In</a></li>
            <?php endif; ?>
            
        </ul>
    </nav>
</header>
