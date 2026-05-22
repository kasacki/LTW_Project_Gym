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
        <button class="trainer-tab active" onclick="showTab('tab-profile', this)">My Profile</button>
        <button class="trainer-tab" onclick="showTab('tab-schedule', this)">My Schedule</button>
        <button class="trainer-tab" onclick="showTab('tab-rosters', this)">Member Rosters</button>
        <button class="trainer-tab" onclick="showTab('tab-sessions', this)">
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
        <div class="card">
            <h3>My Class Schedule</h3>
            <?php if (empty($myClasses)): ?>
                <p class="enrolled-empty">No classes assigned yet.</p>
            <?php else: ?>
                <div class="trainer-schedule-list">
                    <?php foreach ($myClasses as $class): ?>
                    <?php
                        $isPast    = strtotime($class['scheduled_at']) < time();
                        $spotsLeft = $class['capacity'] - $class['enrolled_count'];
                    ?>
                    <div class="trainer-class-row <?= $isPast ? 'past' : '' ?>">
                        <div class="trainer-class-left">
                            <div class="trainer-class-top">
                                <span class="tag tag-<?= strtolower($class['type']) ?>"><?= htmlspecialchars($class['type']) ?></span>
                                <?php if ($class['is_featured']): ?>
                                    <span class="trainer-featured-badge">Featured</span>
                                <?php endif; ?>
                                <strong class="trainer-class-name"><?= htmlspecialchars($class['name']) ?></strong>
                            </div>
                            <div class="trainer-class-meta">
                                <?= date('l, d M Y', strtotime($class['scheduled_at'])) ?> | <?= date('H:i', strtotime($class['scheduled_at'])) ?> | <?= $class['duration_min'] ?> min
                                <?php if (!empty($class['room'])): ?> | Room: <?= htmlspecialchars($class['room']) ?><?php endif; ?>
                            </div>
                        </div>
                        <div class="trainer-class-right">
                            <div class="trainer-spots <?= $spotsLeft === 0 ? 'full' : '' ?>">
                                <span class="trainer-spots-num"><?= $class['enrolled_count'] ?></span>
                                <span class="trainer-spots-sep">/</span>
                                <span class="trainer-spots-total"><?= $class['capacity'] ?></span>
                                <span class="trainer-spots-label">enrolled</span>
                            </div>
                            <?php if ($isPast): ?>
                                <span class="trainer-past-label">Completed</span>
                            <?php elseif ($spotsLeft === 0): ?>
                                <span class="trainer-full-label">Full</span>
                            <?php else: ?>
                                <span class="trainer-open-label"><?= $spotsLeft ?> spots left</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB: ROSTERS -->
    <div id="tab-rosters" class="trainer-tab-content">
        <?php if (empty($myClasses)): ?>
            <div class="card"><p class="enrolled-empty">No classes assigned yet.</p></div>
        <?php else: ?>
            <?php foreach ($myClasses as $class): ?>
            <div class="card trainer-roster-card">
                <div class="trainer-roster-header">
                    <div>
                        <span class="tag tag-<?= strtolower($class['type']) ?>"><?= htmlspecialchars($class['type']) ?></span>
                        <strong><?= htmlspecialchars($class['name']) ?></strong>
                        <span class="trainer-roster-date">— <?= date('d M Y, H:i', strtotime($class['scheduled_at'])) ?></span>
                    </div>
                    <span class="trainer-roster-count"><?= count($classRosters[$class['id']]) ?> / <?= $class['capacity'] ?> members</span>
                </div>
                <?php if (empty($classRosters[$class['id']])): ?>
                    <p class="enrolled-empty" style="margin-top:12px;">No members enrolled yet.</p>
                <?php else: ?>
                    <table class="trainer-roster-table">
                        <thead>
                            <tr><th>Name</th><th>Username</th><th>Email</th><th>Plan</th><th>Enrolled At</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classRosters[$class['id']] as $member): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($member['full_name']) ?></strong></td>
                                <td><?= htmlspecialchars($member['username']) ?></td>
                                <td><?= htmlspecialchars($member['email']) ?></td>
                                <td><span class="tag <?= $member['membership_tier'] === 'premium' ? 'tag-hiit' : 'tag-yoga' ?>"><?= strtoupper($member['membership_tier']) ?></span></td>
                                <td><?= date('d M Y, H:i', strtotime($member['enrolled_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
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

<script>
function showTab(id, btn) {
    document.querySelectorAll('.trainer-tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.trainer-tab').forEach(b => b.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    btn.classList.add('active');
}
</script>

<?php include 'footer.php'; ?>
