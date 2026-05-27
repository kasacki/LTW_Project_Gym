<?php include 'header.php'; ?>

<main class="page-container" data-csrf-token="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <header class="page-header">
        <h2>CLASS SCHEDULE</h2>
        <p>Find a class for yourself and book your spot.</p>
    </header>

    <section class="card filters-section">
        <div class="filters-row">
            <div class="form-group">
                <label for="filter-type">Type</label>
                <select id="filter-type">
                    <option value="all">All</option>
                    <option value="Yoga">Yoga</option>
                    <option value="HIIT">HIIT</option>
                    <option value="Pilates">Pilates</option>
                    <option value="Spinning">Spinning</option>
                </select>
            </div>
            <div class="form-group">
                <label for="filter-trainer">Coach</label>
                <select id="filter-trainer">
                    <option value="all">All</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?= htmlspecialchars($t['full_name']) ?>">
                            <?= htmlspecialchars($t['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter-day">Day</label>
                <select id="filter-day">
                    <option value="all">All</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </div>
            <div class="form-group">
                <label for="filter-time-start">From</label>
                <input type="text" id="filter-time-start" inputmode="numeric" maxlength="5" pattern="^([01]?[0-9]|2[0-3]):[0-5][0-9]$" placeholder="HH:MM">
            </div>
            <div class="form-group">
                <label for="filter-time-end">To</label>
                <input type="text" id="filter-time-end" inputmode="numeric" maxlength="5" pattern="^([01]?[0-9]|2[0-3]):[0-5][0-9]$" placeholder="HH:MM">
            </div>
        </div>
    </section>

    <section id="classes-list" class="classes-list">
        <?php foreach ($classes as $class): ?>
            <?php
                $scheduledAt = strtotime($class['scheduled_at']);
                $classDay = date('l', $scheduledAt);
                $classTime = date('H:i', $scheduledAt);
                $classDate = date('d.m.Y', $scheduledAt); // <-- DODANO: Formatowanie pełnej daty
                $reviewCount = (int)$class['review_count'];
                $averageRating = $reviewCount > 0 ? number_format((float)$class['average_rating'], 1) : null;
                $classReviews = $classReviewsByClassId[$class['id']] ?? [];
                $reviewsPanelId = 'class-reviews-' . (int)$class['id'];
            ?>
            <article class="card class-card"
                     data-type="<?= htmlspecialchars($class['type']) ?>"
                     data-trainer="<?= htmlspecialchars($class['trainer_name']) ?>"
                     data-day="<?= htmlspecialchars($classDay) ?>"
                     data-time="<?= htmlspecialchars($classTime) ?>">
                <div class="class-card-info">
                    <span class="tag"><?= htmlspecialchars($class['type']) ?></span>
                    <h3><?= htmlspecialchars($class['name']) ?></h3>
                    <p class="class-meta">
                        <?= $classDay ?> (<?= $classDate ?>) | <?= $classTime ?> |
                        Coach: <?= htmlspecialchars($class['trainer_name']) ?> |
                        Spots: <?= $class['current_enrollments'] ?>/<?= $class['capacity'] ?>
                    </p>
                    <div class="class-rating-summary">
                        <?php if ($reviewCount > 0): ?>
                            <span class="class-rating-score">
                                Rating: <strong><?= htmlspecialchars($averageRating) ?>/5</strong>
                            </span>
                            <span class="class-rating-count">
                                <?= $reviewCount ?> <?= $reviewCount === 1 ? 'review' : 'reviews' ?>
                            </span>
                            <button
                                type="button"
                                class="class-reviews-toggle"
                                data-review-toggle="<?= htmlspecialchars($reviewsPanelId, ENT_QUOTES, 'UTF-8') ?>"
                                aria-controls="<?= htmlspecialchars($reviewsPanelId, ENT_QUOTES, 'UTF-8') ?>"
                                aria-expanded="false"
                            >
                                Show reviews
                            </button>
                        <?php else: ?>
                            <span class="class-rating-empty">No class reviews yet</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($classReviews)): ?>
                        <div id="<?= htmlspecialchars($reviewsPanelId, ENT_QUOTES, 'UTF-8') ?>" class="class-reviews-panel" hidden>
                            <?php foreach ($classReviews as $review): ?>
                                <?php
                                    $reviewDate = date('d M Y', strtotime($review['created_at']));
                                    $comment = trim($review['comment'] ?? '');
                                ?>
                                <article class="class-review-item">
                                    <header class="class-review-header">
                                        <div class="class-review-user">
                                            <strong><?= htmlspecialchars($review['username']) ?></strong>
                                        </div>
                                        <div class="class-review-meta">
                                            <span><?= (int)$review['rating'] ?>/5</span>
                                            <time datetime="<?= htmlspecialchars($review['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($reviewDate) ?>
                                            </time>
                                        </div>
                                    </header>
                                    <p><?= htmlspecialchars($comment !== '' ? $comment : 'No comment provided.') ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="class-actions">
                    <?php if (!$userId): ?>
                        <a href="login.php" class="btn btn-secondary">Log in to join</a>
                    <?php elseif ($class['is_user_enrolled']): ?>
                        <button class="btn btn-secondary class-enroll-action" data-class-id="<?= $class['id'] ?>" data-action="cancel">Unsubscribe</button>
                    <?php elseif ($class['current_enrollments'] >= $class['capacity']): ?>
                        <button class="btn btn-secondary" disabled>No spots available</button>
                    <?php else: ?>
                        <button class="btn btn-primary class-enroll-action" data-class-id="<?= $class['id'] ?>" data-action="enroll">Join us</button>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<?php include 'footer.php'; ?>
