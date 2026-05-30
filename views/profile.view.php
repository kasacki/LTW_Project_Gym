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
                
                <a href="upgrade.php" class="btn btn-primary btn-full" style="display: block; text-align: center; text-decoration: none; margin-top: 10px; box-sizing: border-box;">View Plan Options</a>
            </div>
        </aside>

        <!-- Edit Form -->
        <section class="card profile-content">
            <form method="POST" enctype="multipart/form-data">
                <?= csrf_input() ?>
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
                <?php $hasAttended = strtotime($ec['scheduled_at']) <= time(); ?>
                <article class="enrolled-item">
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

                    <?php if ($hasAttended): ?>
                        <?php $reviewId = 'class-review-' . (int)$ec['id']; ?>
                        <form method="POST" class="class-review-form">
                            <?= csrf_input() ?>
                            <input type="hidden" name="class_id" value="<?= (int)$ec['id'] ?>">
                            <div class="class-review-fields">
                                <div class="form-group class-review-rating">
                                    <label for="<?= $reviewId ?>-rating">Your rating</label>
                                    <select id="<?= $reviewId ?>-rating" name="rating" required>
                                        <option value="">Select</option>
                                        <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                                            <option value="<?= $rating ?>" <?= (int)($ec['review_rating'] ?? 0) === $rating ? 'selected' : '' ?>>
                                                <?= $rating ?> / 5
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-group class-review-comment">
                                    <label for="<?= $reviewId ?>-comment">Comment <span class="password-hint">(optional)</span></label>
                                    <textarea id="<?= $reviewId ?>-comment" name="comment" rows="2" placeholder="How was this class?"><?= htmlspecialchars($ec['review_comment'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="class-review-actions">
                                <?php if (!empty($ec['review_rating'])): ?>
                                    <span class="class-review-status">Last updated <?= date('d M Y', strtotime($ec['review_created_at'])) ?></span>
                                <?php endif; ?>
                                <button type="submit" name="submit_class_review" class="btn btn-primary">
                                    <?= !empty($ec['review_rating']) ? 'Update Review' : 'Submit Review' ?>
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p class="class-review-locked">Reviews unlock after the class takes place.</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php include 'footer.php'; ?>
