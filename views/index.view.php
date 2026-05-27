<?php include 'header.php'; ?>

<main>
    <!-- Hero -->
    <section id="hero" class="hero-section">
        <div class="hero-content">
            <h1>ELEVATE YOUR FITNESS</h1>
            <p>State-of-the-art equipment, professional trainers, and a community that motivates you.</p>
            <div class="hero-buttons">
                <a href="classes.php" class="btn btn-primary">See the schedule</a>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="btn btn-secondary">Join us</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Tab Navigation -->
    <div class="home-tabs-wrap">
        <div class="home-tabs page-container">
            <button class="home-tab active" data-home-tab-target="tab-promotions">Promotions</button>
            <button class="home-tab" data-home-tab-target="tab-trainers">Our Trainers</button>
        </div>
    </div>

    <!-- TAB: PROMOTIONS -->
    <div id="tab-promotions" class="home-tab-content active">
        <section class="features-section page-container">
            <header class="page-header">
                <h2>RECOMMENDED PROMOTIONS</h2>
                <p>Selected classes especially for you.</p>
            </header>
            <div class="features-grid">
                <?php if (empty($featured_classes)): ?>
                    <p>No current promotions. Check out the full schedule!</p>
                <?php else: ?>
                    <?php foreach ($featured_classes as $class): ?>
                        <article class="card feature-card">
                            <span class="tag"><?= htmlspecialchars($class['type']) ?></span>
                            <h3><?= htmlspecialchars($class['name']) ?></h3>
                            <p>Coach: <?= htmlspecialchars($class['trainer_name']) ?></p>
                            <p><?= date('H:i', strtotime($class['scheduled_at'])) ?> | <?= $class['duration_min'] ?> min</p>
                            <a href="classes.php" class="btn btn-secondary">Sign up</a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- TAB: TRAINERS -->
    <div id="tab-trainers" class="home-tab-content">
        <section class="features-section page-container">
            <header class="page-header">
                <h2>OUR TRAINERS</h2>
                <p>Meet the professionals who will guide your fitness journey.</p>
            </header>

            <?php if (empty($trainers)): ?>
                <p>No trainers available at the moment.</p>
            <?php else: ?>
            <div class="trainers-grid">
                <?php foreach ($trainers as $t): ?>
                <a href="trainer_profile.php?id=<?= $t['id'] ?>" class="trainer-card card">
                    <div class="trainer-card-photo-wrap">
                        <img src="images/<?= htmlspecialchars($t['profile_photo'] ?: 'pfp.png') ?>"
                             alt="<?= htmlspecialchars($t['full_name']) ?>"
                             class="trainer-card-photo">
                    </div>
                    <div class="trainer-card-body">
                        <h3><?= htmlspecialchars($t['full_name']) ?></h3>

                        <?php if (!empty($t['specializations'])): ?>
                        <div class="trainer-card-specs">
                            <?php foreach (array_slice(explode(',', $t['specializations']), 0, 3) as $spec): ?>
                                <span class="tag"><?= htmlspecialchars(trim($spec)) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($t['bio'])): ?>
                        <p class="trainer-card-bio"><?= htmlspecialchars(mb_substr($t['bio'], 0, 100)) ?>...</p>
                        <?php endif; ?>

                        <div class="trainer-card-rating">
                            <?php if ($t['review_count'] > 0): ?>
                                <span class="trainer-stars"><?= str_repeat('★', round($t['avg_rating'])) ?><?= str_repeat('☆', 5 - round($t['avg_rating'])) ?></span>
                                <span class="trainer-rating-val"><?= $t['avg_rating'] ?></span>
                                <span class="trainer-rating-count">(<?= $t['review_count'] ?> reviews)</span>
                            <?php else: ?>
                                <span class="trainer-no-rating">No reviews yet</span>
                            <?php endif; ?>
                        </div>

                        <span class="btn btn-secondary trainer-card-btn">View Profile</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- CTA -->
    <section class="cta-section">
        <div class="page-container">
            <h2>READY FOR TRAINING?</h2>
            <p>From relaxing yoga to intense HIIT, we've got it all.</p>
            <a href="classes.php" class="btn btn-primary">Check out the full schedule</a>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
