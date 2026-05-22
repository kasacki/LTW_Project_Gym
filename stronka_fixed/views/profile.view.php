<?php include 'header.php'; ?>

<main class="page-container">
    <header class="page-header">
        <h2>YOUR PROFILE</h2>
        <p>Manage your account and membership plan.</p>
    </header>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="profile-layout">

        <!-- Sidebar -->
        <aside class="card profile-sidebar">
            <div class="profile-photo-container">
                <img src="images/<?= htmlspecialchars($userData['profile_photo'] ?: 'pfp.png') ?>"
                     alt="Profile Photo" class="profile-photo">
            </div>
            <h3><?= htmlspecialchars($userData['full_name'] ?? $userData['username']) ?></h3>
            <p class="profile-role"><?= strtoupper(htmlspecialchars($userData['role'])) ?></p>

            <div class="plan-section">
                <h4>YOUR PLAN</h4>
                <div class="tag plan-tag <?= ($userData['membership_tier'] ?? 'basic') === 'premium' ? 'tag-hiit' : 'tag-yoga' ?>">
                    <?= strtoupper(htmlspecialchars($userData['membership_tier'] ?? 'basic')) ?>
                </div>
                <form method="POST">
                    <?php if (($userData['membership_tier'] ?? 'basic') === 'basic'): ?>
                        <input type="hidden" name="tier" value="premium">
                        <button type="submit" name="upgrade_membership" class="btn btn-primary btn-full">Upgrade to Premium</button>
                    <?php else: ?>
                        <input type="hidden" name="tier" value="basic">
                        <button type="submit" name="upgrade_membership" class="btn btn-secondary btn-full">Switch to Basic</button>
                    <?php endif; ?>
                </form>
            </div>
        </aside>

        <!-- Edit Form -->
        <section class="card profile-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Name and Surname</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($userData['full_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($userData['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>New Password <span class="password-hint">(leave blank to keep current)</span></label>
                    <input type="password" name="new_password">
                </div>
                <div class="form-group">
                    <label>Profile Photo</label>
                    <input type="file" name="profile_photo" accept="image/*">
                </div>
                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </section>
    </div>

    <!-- Enrolled Classes -->
    <section class="card enrolled-section">
        <h3>Your Enrolled Classes</h3>
        <?php if (empty($enrolledClasses)): ?>
            <p class="enrolled-empty">You haven't signed up for any classes yet. <a href="classes.php">Browse the schedule</a>.</p>
        <?php else: ?>
            <?php foreach ($enrolledClasses as $ec): ?>
                <div class="enrolled-row">
                    <div class="enrolled-row-info">
                        <span class="tag"><?= htmlspecialchars($ec['type']) ?></span>
                        <strong><?= htmlspecialchars($ec['name']) ?></strong>
                        <span class="enrolled-row-trainer"><?= htmlspecialchars($ec['trainer_name']) ?></span>
                    </div>
                    <div class="enrolled-row-date">
                        <?= date('D, d M Y', strtotime($ec['scheduled_at'])) ?> |
                        <?= date('H:i', strtotime($ec['scheduled_at'])) ?> |
                        <?= $ec['duration_min'] ?> min
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php include 'footer.php'; ?>
