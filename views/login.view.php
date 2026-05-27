<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FitLife | Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Nunito:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body class="auth-body">
    <div class="card auth-card">
        <h2>Login</h2>

        <?php if (isset($_GET['registered'])): ?>
            <p class="auth-success">Registration successful! Log in.</p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="auth-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <?= csrf_input() ?>
            <div class="form-group">
                <label>User</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Log in</button>
        </form>

        <div class="auth-links">
            <p>Don't have an account? <a href="register.php">Register</a></p>
            <p><a href="index.php">Continue without logging in →</a></p>
        </div>
    </div>
</body>
</html>
