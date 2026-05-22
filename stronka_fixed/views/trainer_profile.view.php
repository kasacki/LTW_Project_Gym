<?php include 'header.php'; ?>

<main class="page-container">

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Trainer Header -->
    <div class="tp-hero card">
        <div class="tp-hero-left">
            <img src="images/<?= htmlspecialchars($trainer['profile_photo'] ?: 'pfp.png') ?>"
                 alt="<?= htmlspecialchars($trainer['full_name']) ?>"
                 class="tp-photo">
        </div>
        <div class="tp-hero-right">
            <h2><?= htmlspecialchars($trainer['full_name']) ?></h2>
            <p class="tp-role">Personal Trainer</p>

            <?php if ($avgRating): ?>
            <div class="tp-rating">
                <span class="tp-stars"><?= str_repeat('★', round($avgRating)) ?><?= str_repeat('☆', 5 - round($avgRating)) ?></span>
                <span class="tp-rating-val"><?= $avgRating ?></span>
                <span class="tp-rating-count">(<?= $reviewCount ?> reviews)</span>
            </div>
            <?php else: ?>
            <p class="tp-no-rating">No reviews yet — be the first!</p>
            <?php endif; ?>

            <?php if (!empty($trainer['specializations'])): ?>
            <div class="tp-specs">
                <?php foreach (explode(',', $trainer['specializations']) as $spec): ?>
                    <span class="tag"><?= htmlspecialchars(trim($spec)) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($trainer['certifications'])): ?>
            <p class="tp-certs">Certifications: <strong><?= htmlspecialchars($trainer['certifications']) ?></strong></p>
            <?php endif; ?>

            <?php if (!empty($trainer['bio'])): ?>
            <p class="tp-bio"><?= nl2br(htmlspecialchars($trainer['bio'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="tp-layout">

        <!-- Left Column -->
        <div class="tp-left">

            <!-- Upcoming Classes -->
            <div class="card tp-section">
                <h3>Upcoming Classes</h3>
                <?php if (empty($trainerClasses)): ?>
                    <p class="enrolled-empty">No upcoming classes scheduled.</p>
                <?php else: ?>
                    <div class="tp-classes-list">
                    <?php foreach ($trainerClasses as $c): ?>
                    <?php $spotsLeft = $c['capacity'] - $c['enrolled_count']; ?>
                    <div class="tp-class-row">
                        <div>
                            <span class="tag tag-<?= strtolower($c['type']) ?>"><?= htmlspecialchars($c['type']) ?></span>
                            <strong><?= htmlspecialchars($c['name']) ?></strong>
                            <div class="tp-class-meta">
                                <?= date('D, d M Y', strtotime($c['scheduled_at'])) ?>
                                &nbsp;|&nbsp; <?= date('H:i', strtotime($c['scheduled_at'])) ?>
                                &nbsp;|&nbsp; <?= $c['duration_min'] ?> min
                                <?php if (!empty($c['room'])): ?>&nbsp;|&nbsp; <?= htmlspecialchars($c['room']) ?><?php endif; ?>
                            </div>
                        </div>
                        <div class="tp-class-spots <?= $spotsLeft === 0 ? 'full' : '' ?>">
                            <?= $spotsLeft === 0 ? 'Full' : $spotsLeft . ' spots' ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                    <a href="classes.php" class="btn btn-secondary" style="margin-top:16px;display:inline-block;">View Full Schedule</a>
                <?php endif; ?>
            </div>

            <!-- Reviews -->
            <div class="card tp-section">
                <h3>Member Reviews</h3>
                <?php if (empty($reviews)): ?>
                    <p class="enrolled-empty">No reviews yet.</p>
                <?php else: ?>
                    <div class="tp-reviews-list">
                    <?php foreach ($reviews as $rev): ?>
                    <div class="tp-review-row">
                        <div class="tp-review-header">
                            <strong><?= htmlspecialchars($rev['member_name']) ?></strong>
                            <span class="tp-review-stars">
                                <?= str_repeat('★', $rev['rating']) ?><?= str_repeat('☆', 5 - $rev['rating']) ?>
                            </span>
                            <span class="tp-review-date"><?= date('d M Y', strtotime($rev['created_at'])) ?></span>
                        </div>
                        <?php if (!empty($rev['comment'])): ?>
                        <p class="tp-review-comment"><?= htmlspecialchars($rev['comment']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Right Column -->
        <div class="tp-right">

            <?php if ($memberId): ?>

            <!-- Leave a Review -->
            <div class="card tp-section">
                <h3><?= $myReview ? 'Edit Your Review' : 'Leave a Review' ?></h3>
                <form method="POST" class="tp-form">
                    <input type="hidden" name="action" value="submit_review">
                    <div class="form-group">
                        <label>Rating</label>
                        <div class="star-picker" id="star-picker">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= ($myReview && $myReview['rating'] >= $i) ? 'active' : '' ?>"
                                  data-val="<?= $i ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="rating-input" value="<?= $myReview['rating'] ?? 0 ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Comment <span class="password-hint">(optional)</span></label>
                        <textarea name="comment" rows="3" placeholder="Share your experience..."><?= htmlspecialchars($myReview['comment'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <?= $myReview ? 'Update Review' : 'Submit Review' ?>
                    </button>
                </form>
            </div>

            <!-- Book Private Session -->
            <div class="card tp-section">
                <h3>Book a Private Session</h3>
                <p style="color:var(--clr-text-muted);font-size:0.9rem;margin-bottom:16px;">
                    Request a 1-on-1 session with this trainer. They will confirm or suggest an alternative time.
                </p>
                <form method="POST" class="tp-form">
                    <input type="hidden" name="action" value="book_session">
                    <div class="form-group">
                        <label>Preferred Date & Time</label>
                        <input type="datetime-local" name="requested_at" required
                               min="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                    <div class="form-group">
                        <label>Duration</label>
                        <select name="duration_min">
                            <option value="30">30 minutes</option>
                            <option value="60" selected>60 minutes</option>
                            <option value="90">90 minutes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes <span class="password-hint">(optional)</span></label>
                        <textarea name="notes" rows="3" placeholder="Goals, injuries, preferences..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>

            <?php elseif ($role === 'trainer' || $role === 'admin'): ?>
                <div class="card tp-section">
                    <p style="color:var(--clr-text-muted);">Reviews and session booking are available to members only.</p>
                </div>
            <?php else: ?>
                <div class="card tp-section">
                    <p style="color:var(--clr-text-muted);">
                        <a href="login.php">Log in</a> as a member to leave a review or book a session.
                    </p>
                </div>
            <?php endif; ?>

        </div>
    </div>

</main>

<script>
// Star picker
const stars    = document.querySelectorAll('.star');
const ratingIn = document.getElementById('rating-input');
if (stars.length) {
    stars.forEach(s => {
        s.addEventListener('mouseover', () => {
            stars.forEach(x => x.classList.toggle('active', x.dataset.val <= s.dataset.val));
        });
        s.addEventListener('click', () => {
            ratingIn.value = s.dataset.val;
            stars.forEach(x => x.classList.toggle('selected', x.dataset.val <= s.dataset.val));
        });
    });
    document.getElementById('star-picker').addEventListener('mouseleave', () => {
        stars.forEach(x => x.classList.toggle('active', x.dataset.val <= ratingIn.value));
    });
}
</script>

<?php include 'footer.php'; ?>
