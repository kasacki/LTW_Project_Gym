<?php include 'header.php'; ?>

<main class="page-container">

    <header class="page-header">
        <h2>TRAINER PANEL</h2>
        <p>Manage your profile, class schedule, enrolled members and session requests.</p>
    </header>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <div class="trainer-tabs">
        <button class="trainer-tab active" data-trainer-tab-target="tab-profile">My Profile</button>
        <button class="trainer-tab" data-trainer-tab-target="tab-schedule">My Schedule</button>
        <button class="trainer-tab" data-trainer-tab-target="tab-rosters">Member Rosters</button>
        <button class="trainer-tab" data-trainer-tab-target="tab-sessions">
            Session Requests
            <?php if ($pendingCount > 0): ?>
                <span class="tab-badge"><?= $pendingCount ?></span>
            <?php endif; ?>
        </button>
    </div>

    <!-- TAB: PROFILE -->
    <div id="tab-profile" class="trainer-tab-content active">
        <div class="profile-layout">
            <aside class="card profile-sidebar">
                <div class="profile-photo-container">
                    <img src="images/<?= htmlspecialchars($trainer['profile_photo'] ?: 'pfp.png') ?>"
                         alt="Profile Photo" class="profile-photo">
                </div>
                <h3><?= htmlspecialchars($trainer['full_name'] ?? $trainer['username']) ?></h3>
                <p class="profile-role">Trainer</p>

                <?php if (!empty($trainer['specializations'])): ?>
                <div class="trainer-specs-block">
                    <h4>Specializations</h4>
                    <div class="trainer-specs-tags">
                        <?php foreach (explode(',', $trainer['specializations']) as $spec): ?>
                            <span class="tag"><?= htmlspecialchars(trim($spec)) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($trainer['certifications'])): ?>
                <div class="trainer-specs-block">
                    <h4>Certifications</h4>
                    <p class="trainer-cert-text"><?= htmlspecialchars($trainer['certifications']) ?></p>
                </div>
                <?php endif; ?>
            </aside>

            <section class="card profile-content">
                <h3>Edit Public Profile</h3>
                <form method="POST" enctype="multipart/form-data">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($trainer['full_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($trainer['username']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($trainer['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Bio</label>
                        <textarea name="bio" rows="4" placeholder="Tell members about yourself..."><?= htmlspecialchars($trainer['bio'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Specializations <span class="password-hint">(comma-separated)</span></label>
                        <input type="text" name="specializations" value="<?= htmlspecialchars($trainer['specializations'] ?? '') ?>" placeholder="Yoga, HIIT, Pilates">
                    </div>
                    <div class="form-group">
                        <label>Certifications</label>
                        <input type="text" name="certifications" value="<?= htmlspecialchars($trainer['certifications'] ?? '') ?>" placeholder="ACE, NASM">
                    </div>
                    <div class="form-group">
                        <label>New Password <span class="password-hint">(leave blank to keep)</span></label>
                        <input type="password" name="new_password">
                    </div>
                    <div class="form-group">
                        <label>Profile Photo</label>
                        <input type="file" name="profile_photo" accept="image/*">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <!-- TAB: SCHEDULE -->
    <div id="tab-schedule" class="trainer-tab-content">
        <?php if (empty($myClasses)): ?>
            <div class="card">
                <h3>My Class Schedule</h3>
                <p class="enrolled-empty">No classes assigned yet.</p>
            </div>
        <?php else: ?>
            <section class="card trainer-schedule-tools">
                <div>
                    <h3>My Class Schedule</h3>
                    <p><?= count($myClasses) ?> assigned <?= count($myClasses) === 1 ? 'class' : 'classes' ?> with <?= $totalRosterMembers ?> enrolled <?= $totalRosterMembers === 1 ? 'member' : 'members' ?>.</p>
                </div>
                <div class="trainer-schedule-stats">
                    <span><?= $upcomingClassesCount ?> upcoming</span>
                    <span><?= $completedClassesCount ?> completed</span>
                    <span><?= $fullClassesCount ?> full</span>
                </div>
                <div class="trainer-schedule-filters" role="group" aria-label="Filter assigned classes">
                    <button type="button" class="trainer-schedule-filter active" data-schedule-filter="all">All</button>
                    <button type="button" class="trainer-schedule-filter" data-schedule-filter="upcoming">Upcoming</button>
                    <button type="button" class="trainer-schedule-filter" data-schedule-filter="completed">Completed</button>
                    <button type="button" class="trainer-schedule-filter" data-schedule-filter="full">Full</button>
                </div>
            </section>

            <div class="trainer-schedule-list">
                <?php foreach ($myClasses as $class): ?>
                <?php
                    $scheduledAt = strtotime($class['scheduled_at']);
                    $isPast = $scheduledAt < time();
                    $enrolledCount = (int)$class['enrolled_count'];
                    $capacity = (int)$class['capacity'];
                    $spotsLeft = max(0, $capacity - $enrolledCount);
                    $isFull = $enrolledCount >= $capacity;
                    $classTypeClass = preg_replace('/[^a-z0-9_-]/', '', strtolower($class['type']));
                    $scheduleStatus = $isPast ? 'completed' : 'upcoming';
                    $classReviews = $classReviewsByClassId[$class['id']] ?? [];
                    $reviewSummary = $classReviewSummaries[$class['id']] ?? ['average_rating' => null, 'review_count' => 0];
                    $reviewsPanelId = 'schedule-reviews-' . (int)$class['id'];
                ?>
                <article class="card trainer-class-row <?= $isPast ? 'past' : '' ?>"
                         data-schedule-row
                         data-schedule-status="<?= htmlspecialchars($scheduleStatus, ENT_QUOTES, 'UTF-8') ?>"
                         data-schedule-full="<?= $isFull ? 'true' : 'false' ?>">
                    <div class="trainer-class-main">
                        <div class="trainer-class-date">
                            <span><?= htmlspecialchars(date('d M', $scheduledAt)) ?></span>
                            <small><?= htmlspecialchars(date('D', $scheduledAt)) ?></small>
                        </div>
                        <div class="trainer-class-left">
                            <div class="trainer-class-top">
                                <span class="tag tag-<?= htmlspecialchars($classTypeClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($class['type']) ?></span>
                                <?php if ($class['is_featured']): ?>
                                    <span class="trainer-featured-badge">Featured</span>
                                <?php endif; ?>
                                <strong class="trainer-class-name"><?= htmlspecialchars($class['name']) ?></strong>
                            </div>
                            <div class="trainer-class-meta">
                                <span><?= htmlspecialchars(date('H:i', $scheduledAt)) ?></span>
                                <span><?= (int)$class['duration_min'] ?> min</span>
                                <?php if (!empty($class['room'])): ?>
                                    <span>Room: <?= htmlspecialchars($class['room']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($isPast): ?>
                                <div class="trainer-class-rating">
                                    <?php if ($reviewSummary['review_count'] > 0): ?>
                                        <span>Rating: <strong><?= htmlspecialchars(number_format((float)$reviewSummary['average_rating'], 1)) ?>/5</strong></span>
                                        <span><?= (int)$reviewSummary['review_count'] ?> <?= (int)$reviewSummary['review_count'] === 1 ? 'review' : 'reviews' ?></span>
                                    <?php else: ?>
                                        <span>No reviews yet</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="trainer-class-right">
                            <div class="trainer-spots <?= $isFull ? 'full' : '' ?>">
                                <span class="trainer-spots-num"><?= $enrolledCount ?></span>
                                <span class="trainer-spots-sep">/</span>
                                <span class="trainer-spots-total"><?= $capacity ?></span>
                                <span class="trainer-spots-label">enrolled</span>
                            </div>
                            <?php if ($isPast): ?>
                                <span class="trainer-past-label">Completed</span>
                            <?php elseif ($isFull): ?>
                                <span class="trainer-full-label">Full</span>
                            <?php else: ?>
                                <span class="trainer-open-label"><?= $spotsLeft ?> spots left</span>
                            <?php endif; ?>
                            <div class="trainer-class-actions">
                                <button type="button" class="btn btn-secondary trainer-roster-jump" data-open-roster="<?= (int)$class['id'] ?>">View roster</button>
                                <?php if ($isPast): ?>
                                    <button
                                        type="button"
                                        class="btn btn-secondary trainer-reviews-toggle"
                                        data-review-toggle="<?= htmlspecialchars($reviewsPanelId, ENT_QUOTES, 'UTF-8') ?>"
                                        aria-controls="<?= htmlspecialchars($reviewsPanelId, ENT_QUOTES, 'UTF-8') ?>"
                                        aria-expanded="false"
                                    >
                                        View reviews
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($isPast): ?>
                        <div id="<?= htmlspecialchars($reviewsPanelId, ENT_QUOTES, 'UTF-8') ?>" class="trainer-class-reviews" hidden>
                            <?php if (empty($classReviews)): ?>
                                <p class="trainer-class-review-empty">No reviews yet for this class.</p>
                            <?php else: ?>
                                <?php foreach ($classReviews as $review): ?>
                                    <?php
                                        $comment = trim($review['comment'] ?? '');
                                        $reviewDate = date('d M Y', strtotime($review['created_at']));
                                    ?>
                                    <article class="trainer-class-review">
                                        <header>
                                            <strong><?= htmlspecialchars($review['username']) ?></strong>
                                            <span><?= (int)$review['rating'] ?>/5 · <?= htmlspecialchars($reviewDate) ?></span>
                                        </header>
                                        <p><?= htmlspecialchars($comment !== '' ? $comment : 'No comment provided.') ?></p>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </article>
                <?php endforeach; ?>
            </div>

            <div class="card trainer-schedule-empty" id="schedule-filter-empty" hidden>
                <p class="enrolled-empty">No assigned classes match this filter.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- TAB: ROSTERS -->
    <div id="tab-rosters" class="trainer-tab-content">
        <?php if (empty($myClasses)): ?>
            <div class="card"><p class="enrolled-empty">No classes assigned yet.</p></div>
        <?php else: ?>
            <section class="card trainer-roster-tools">
                <div>
                    <h3>Member Rosters</h3>
                    <p><?= $totalRosterMembers ?> enrolled <?= $totalRosterMembers === 1 ? 'member' : 'members' ?> across <?= count($myClasses) ?> assigned <?= count($myClasses) === 1 ? 'class' : 'classes' ?>.</p>
                </div>
                <div class="trainer-roster-stats">
                    <span><?= $upcomingClassesCount ?> upcoming</span>
                    <span><?= count($myClasses) - $upcomingClassesCount ?> completed</span>
                </div>
                <div class="form-group trainer-roster-search">
                    <label for="roster-search">Search roster</label>
                    <input type="search" id="roster-search" placeholder="Member, username, email, or class">
                </div>
            </section>

            <?php foreach ($myClasses as $class): ?>
            <?php
                $rosterMembers = $classRosters[$class['id']] ?? [];
                $classTypeClass = preg_replace('/[^a-z0-9_-]/', '', strtolower($class['type']));
                $rosterSearchText = strtolower($class['name'] . ' ' . $class['type'] . ' ' . implode(' ', array_map(
                    fn($member) => $member['full_name'] . ' ' . $member['username'] . ' ' . $member['email'],
                    $rosterMembers
                )));
            ?>
            <div class="card trainer-roster-card" id="roster-class-<?= (int)$class['id'] ?>" data-roster-card data-roster-text="<?= htmlspecialchars($rosterSearchText, ENT_QUOTES, 'UTF-8') ?>">
                <div class="trainer-roster-header">
                    <div>
                        <span class="tag tag-<?= htmlspecialchars($classTypeClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($class['type']) ?></span>
                        <strong><?= htmlspecialchars($class['name']) ?></strong>
                        <span class="trainer-roster-date">— <?= date('d M Y, H:i', strtotime($class['scheduled_at'])) ?></span>
                        <?php if (!empty($class['room'])): ?>
                            <span class="trainer-roster-room">Room <?= htmlspecialchars($class['room']) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="trainer-roster-count"><?= count($rosterMembers) ?> / <?= (int)$class['capacity'] ?> members</span>
                </div>
                <?php if (empty($rosterMembers)): ?>
                    <p class="enrolled-empty trainer-roster-empty">No members enrolled yet.</p>
                <?php else: ?>
                    <div class="trainer-roster-table-wrap">
                        <table class="trainer-roster-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Enrolled At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rosterMembers as $member): ?>
                                <?php
                                    $memberSearchText = strtolower($class['name'] . ' ' . $member['full_name'] . ' ' . $member['username'] . ' ' . $member['email']);
                                    $memberStatus = $member['membership_status'] ?? 'active';
                                ?>
                                <tr data-roster-row data-roster-text="<?= htmlspecialchars($memberSearchText, ENT_QUOTES, 'UTF-8') ?>">
                                    <td><strong><?= htmlspecialchars($member['full_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($member['username']) ?></td>
                                    <td><a href="mailto:<?= htmlspecialchars($member['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($member['email']) ?></a></td>
                                    <td>
                                        <span class="tag <?= $member['membership_tier'] === 'premium' ? 'tag-hiit' : 'tag-yoga' ?>">
                                            <?= htmlspecialchars(strtoupper($member['membership_tier'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="trainer-member-status <?= $memberStatus === 'active' ? 'active' : 'inactive' ?>">
                                            <?= htmlspecialchars(strtoupper($memberStatus)) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y, H:i', strtotime($member['enrolled_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="trainer-roster-no-match" hidden>No members match this search.</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <div class="card trainer-roster-global-empty" id="roster-global-empty" hidden>
                <p class="enrolled-empty">No classes or members match this search.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- TAB: SESSIONS -->
    <div id="tab-sessions" class="trainer-tab-content">
        <?php if (empty($sessions)): ?>
            <div class="card"><p class="enrolled-empty">No session requests yet.</p></div>
        <?php else: ?>
            <?php foreach ($sessions as $s): ?>
            <div class="card session-card status-<?= $s['status'] ?>">
                <div class="session-card-header">
                    <div class="session-card-info">
                        <strong><?= htmlspecialchars($s['member_name']) ?></strong>
                        <span class="session-email"><?= htmlspecialchars($s['member_email']) ?></span>
                    </div>
                    <span class="session-status-badge badge-<?= $s['status'] ?>">
                        <?= strtoupper($s['status']) ?>
                    </span>
                </div>

                <div class="session-card-meta">
                    <span>Requested: <strong><?= date('D, d M Y', strtotime($s['requested_at'])) ?> at <?= date('H:i', strtotime($s['requested_at'])) ?></strong></span>
                    <span>Duration: <strong><?= $s['duration_min'] ?> min</strong></span>
                    <span>Sent: <?= date('d M Y', strtotime($s['created_at'])) ?></span>
                </div>

                <?php if (!empty($s['notes'])): ?>
                <div class="session-notes">
                    <strong>Member notes:</strong> <?= htmlspecialchars($s['notes']) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($s['trainer_note'])): ?>
                <div class="session-trainer-note">
                    <strong>Your response:</strong> <?= htmlspecialchars($s['trainer_note']) ?>
                </div>
                <?php endif; ?>

                <?php if ($s['status'] === 'pending'): ?>
                <form method="POST" class="session-respond-form">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="respond_session">
                    <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                    <div class="form-group">
                        <label>Your note <span class="password-hint">(optional — e.g. suggest different time)</span></label>
                        <textarea name="trainer_note" rows="2" placeholder="I'll see you then! / Could we do Tuesday instead?"></textarea>
                    </div>
                    <div class="session-respond-btns">
                        <button type="submit" name="status" value="confirmed" class="btn btn-primary">Confirm</button>
                        <button type="submit" name="status" value="rejected" class="btn btn-secondary">Decline</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</main>

<?php include 'footer.php'; ?>
