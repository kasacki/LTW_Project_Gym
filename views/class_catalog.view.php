<?php include 'header.php'; ?>

<main class="page-container">
    <header class="page-header">
        <h2>CLASSES</h2>
        <p>Explore each training style and check its overall member feedback.</p>
    </header>

    <?php if (empty($classTypes)): ?>
        <section class="card class-catalog-empty">
            <p>No class types are available yet.</p>
        </section>
    <?php else: ?>
        <section class="class-catalog-grid" aria-label="Available class types">
            <?php foreach ($classTypes as $classType): ?>
                <?php
                    $type = $classType['type'] ?? 'Other';
                    $imageUrl = $classTypeImages[$type] ?? $classTypeImages['Other'];
                    $description = $classTypeDescriptions[$type] ?? $classTypeDescriptions['Other'];
                    $reviewCount = (int)$classType['review_count'];
                    $averageRating = $reviewCount > 0 ? number_format((float)$classType['average_rating'], 1) : null;
                    $trainerNames = array_filter(array_map('trim', explode(',', $classType['trainer_names'] ?? '')));
                    $visibleTrainers = array_slice($trainerNames, 0, 3);
                    $nextSession = $classType['next_session'] ? strtotime($classType['next_session']) : null;
                    $classReviews = $classReviewsByType[$type] ?? [];
                ?>
                <article class="card class-type-card">
                    <div class="class-type-image-wrap">
                        <img
                            src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($type) ?> class"
                            class="class-type-image"
                            loading="lazy"
                        >
                    </div>
                    <div class="class-type-body">
                        <div class="class-type-heading">
                            <span class="tag"><?= htmlspecialchars($type) ?></span>
                            <h3><?= htmlspecialchars($type) ?></h3>
                        </div>

                        <p class="class-type-description"><?= htmlspecialchars($description) ?></p>

                        <div class="class-type-stats">
                            <span><?= (int)$classType['session_count'] ?> <?= (int)$classType['session_count'] === 1 ? 'session' : 'sessions' ?></span>
                            <span><?= (int)$classType['trainer_count'] ?> <?= (int)$classType['trainer_count'] === 1 ? 'trainer' : 'trainers' ?></span>
                        </div>

                        <div class="class-type-rating">
                            <?php if ($reviewCount > 0): ?>
                                <span>Rating: <strong><?= htmlspecialchars($averageRating) ?>/5</strong></span>
                                <span><?= $reviewCount ?> <?= $reviewCount === 1 ? 'review' : 'reviews' ?></span>
                            <?php else: ?>
                                <span>No reviews yet</span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($visibleTrainers)): ?>
                            <p class="class-type-trainers">
                                Coaches:
                                <?= htmlspecialchars(implode(', ', $visibleTrainers)) ?>
                                <?php if (count($trainerNames) > count($visibleTrainers)): ?>
                                    +<?= count($trainerNames) - count($visibleTrainers) ?> more
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>

                        <p class="class-type-next">
                            <?php if ($nextSession): ?>
                                Next session: <?= htmlspecialchars(date('D, d M Y H:i', $nextSession)) ?>
                            <?php else: ?>
                                No upcoming sessions scheduled.
                            <?php endif; ?>
                        </p>

                        <div class="class-type-actions">
                            <a href="classes.php?type=<?= urlencode($type) ?>" class="btn btn-primary">View Schedule</a>
                            <?php if (!empty($classReviews)): ?>
                                <details class="class-type-reviews">
                                    <summary data-open-label="Hide Comments" data-closed-label="View Comments">View Comments</summary>
                                    <div class="class-type-review-list">
                                        <?php foreach ($classReviews as $review): ?>
                                            <?php
                                                $reviewerName = trim($review['full_name'] ?? '') ?: $review['username'];
                                                $comment = trim($review['comment'] ?? '');
                                                $reviewDate = date('d M Y', strtotime($review['created_at']));
                                            ?>
                                            <article class="class-type-review">
                                                <header>
                                                    <strong>
                                                        <?= htmlspecialchars($reviewerName) ?>
                                                        (<?= htmlspecialchars($review['username']) ?>)
                                                    </strong>
                                                    <span><?= (int)$review['rating'] ?>/5</span>
                                                </header>
                                                <small>
                                                    <?= htmlspecialchars($review['class_name']) ?> · <?= htmlspecialchars($reviewDate) ?>
                                                </small>
                                                <p><?= htmlspecialchars($comment !== '' ? $comment : 'No comment provided.') ?></p>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </details>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>
