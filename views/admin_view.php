<!-- Admin Shell -->
<div class="admin-shell" data-active-admin-section="<?= htmlspecialchars($activeAdminSection, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h3>Admin Panel</h3>
            <p>FitLife Management</p>
        </div>
        <button class="sidebar-nav-btn active" data-admin-section="overview" id="nav-overview">
            <span class="nav-icon">[=]</span> Overview
        </button>
        <button class="sidebar-nav-btn" data-admin-section="members" id="nav-members">
            <span class="nav-icon">[M]</span> Members
        </button>
        <button class="sidebar-nav-btn" data-admin-section="trainers" id="nav-trainers">
            <span class="nav-icon">[T]</span> Trainers
        </button>
        <button class="sidebar-nav-btn" data-admin-section="classes" id="nav-classes">
            <span class="nav-icon">[C]</span> Classes
        </button>
        <button class="sidebar-nav-btn" data-admin-section="equipment" id="nav-equipment">
            <span class="nav-icon">[E]</span> Equipment
        </button>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">

        <!-- Alerts -->
        <?php if ($success): ?>
            <div class="admin-alert success" data-auto-dismiss="5000"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="admin-alert error" data-auto-dismiss="5000"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Stats Bar -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon blue">M</div>
                <div class="stat-info">
                    <div class="stat-value"><?= $statsMembers ?></div>
                    <p class="stat-label">Members</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">T</div>
                <div class="stat-info">
                    <div class="stat-value"><?= $statsTrainers ?></div>
                    <p class="stat-label">Trainers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow">C</div>
                <div class="stat-info">
                    <div class="stat-value"><?= $statsClasses ?></div>
                    <p class="stat-label">Classes</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">E</div>
                <div class="stat-info">
                    <div class="stat-value"><?= $statsEquip ?></div>
                    <p class="stat-label">Equipment</p>
                </div>
            </div>
        </div>

        <!-- SECTION: OVERVIEW -->
        <section id="section-overview" class="admin-section active">
            <div class="section-header">
                <h2>System Overview</h2>
            </div>
            <div class="overview-card">
                <p>
                    Welcome to the <strong>FitLife Admin Panel</strong>. Use the sidebar to navigate between management sections.
                    You can manage members, trainers, the class catalog, gym equipment, and promote users to admin status.
                </p>
                <ul>
                    <li><strong>Members</strong> — Create, edit, and deactivate member accounts.</li>
                    <li><strong>Trainers</strong> — Add and manage trainer profiles and specializations.</li>
                    <li><strong>Classes</strong> — Build the class catalog, assign trainers, and mark featured classes.</li>
                    <li><strong>Equipment</strong> — Track gym floor equipment availability and status.</li>
                </ul>
            </div>
        </section>

        <!-- SECTION: MEMBERS -->
        <section id="section-members" class="admin-section">
            <div class="section-header">
                <h2>Members</h2>
                <button class="btn-admin-primary" data-open-modal="modal-create-member">
                    + Add Member
                </button>
            </div>
            <div class="admin-table-toolbar">
                <input type="search" class="admin-search-input" data-table-filter="members-table" placeholder="Search members by name, username or email">
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table" id="members-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $m): ?>
                        <?php
                            $memberTier = in_array($m['membership_tier'], ['basic', 'premium'], true) ? $m['membership_tier'] : 'basic';
                            $rawMemberStatus = $m['membership_status'] ?? 'active';
                            $memberStatus = in_array($rawMemberStatus, ['active', 'inactive'], true) ? $rawMemberStatus : 'inactive';
                        ?>
                        <tr data-search="<?= htmlspecialchars(strtolower($m['full_name'] . ' ' . $m['username'] . ' ' . $m['email'] . ' ' . $memberTier . ' ' . $memberStatus), ENT_QUOTES, 'UTF-8') ?>">
                            <td><strong><?= htmlspecialchars($m['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($m['username']) ?></td>
                            <td><?= htmlspecialchars($m['email']) ?></td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($memberTier, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars(strtoupper($memberTier)) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($memberStatus, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars(strtoupper($memberStatus)) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn-admin-ghost" data-edit-member="<?= htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8') ?>">Edit</button>
                                    <?php if (($m['membership_status'] ?? 'active') !== 'inactive'): ?>
                                    <form method="POST" class="admin-action-form" data-confirm="Deactivate this member?">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="action" value="deactivate_member">
                                        <input type="hidden" name="user_id" value="<?= (int)$m['user_id'] ?>">
                                        <button type="submit" class="btn-admin-danger">Deactivate</button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" class="admin-action-form" data-confirm="Promote <?= htmlspecialchars($m['username'], ENT_QUOTES, 'UTF-8') ?> to Admin? This removes the member profile.">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="action" value="elevate_admin">
                                        <input type="hidden" name="user_id" value="<?= (int)$m['user_id'] ?>">
                                        <button type="submit" class="btn-admin-promote">Elevate to Admin</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($members)): ?>
                        <tr class="admin-static-empty">
                            <td colspan="6" class="admin-empty-cell">No members yet.</td>
                        </tr>
                        <?php endif; ?>
                        <tr class="admin-filter-empty" hidden>
                            <td colspan="6" class="admin-empty-cell">No members match your search.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SECTION: TRAINERS -->
        <section id="section-trainers" class="admin-section">
            <div class="section-header">
                <h2>Trainers</h2>
                <button class="btn-admin-primary" data-open-modal="modal-create-trainer">
                    + Add Trainer
                </button>
            </div>
            <div class="admin-table-toolbar">
                <input type="search" class="admin-search-input" data-table-filter="trainers-table" placeholder="Search trainers by name, username, email or specialization">
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table" id="trainers-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Specializations</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trainers as $t): ?>
                        <?php $isDeactivated = str_contains($t['bio'] ?? '', '[DEACTIVATED]'); ?>
                        <tr data-search="<?= htmlspecialchars(strtolower($t['full_name'] . ' ' . $t['username'] . ' ' . $t['email'] . ' ' . ($t['specializations'] ?? '') . ' ' . ($isDeactivated ? 'inactive' : 'active')), ENT_QUOTES, 'UTF-8') ?>">
                            <td><strong><?= htmlspecialchars($t['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($t['username']) ?></td>
                            <td><?= htmlspecialchars($t['email']) ?></td>
                            <td><?= htmlspecialchars($t['specializations'] ?? '—') ?></td>
                            <td>
                                <span class="badge badge-<?= $isDeactivated ? 'inactive' : 'active' ?>">
                                    <?= $isDeactivated ? 'INACTIVE' : 'ACTIVE' ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn-admin-ghost" data-edit-trainer="<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8') ?>">Edit</button>
                                    <?php if (!$isDeactivated): ?>
                                    <form method="POST" class="admin-action-form" data-confirm="Deactivate this trainer?">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="action" value="deactivate_trainer">
                                        <input type="hidden" name="user_id" value="<?= (int)$t['user_id'] ?>">
                                        <button type="submit" class="btn-admin-danger">Deactivate</button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" class="admin-action-form" data-confirm="Promote <?= htmlspecialchars($t['username'], ENT_QUOTES, 'UTF-8') ?> to Admin? This removes the trainer profile.">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="action" value="elevate_admin">
                                        <input type="hidden" name="user_id" value="<?= (int)$t['user_id'] ?>">
                                        <button type="submit" class="btn-admin-promote">Elevate to Admin</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($trainers)): ?>
                        <tr class="admin-static-empty">
                            <td colspan="6" class="admin-empty-cell">No trainers yet.</td>
                        </tr>
                        <?php endif; ?>
                        <tr class="admin-filter-empty" hidden>
                            <td colspan="6" class="admin-empty-cell">No trainers match your search.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SECTION: CLASSES -->
        <section id="section-classes" class="admin-section">
            <div class="section-header">
                <h2>Class Catalog</h2>
                <button class="btn-admin-primary" data-open-modal="modal-create-class">
                    + Add Class
                </button>
            </div>
            <div class="admin-table-toolbar">
                <input type="search" class="admin-search-input" data-table-filter="classes-table" placeholder="Search classes by name, type, trainer or date">
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table" id="classes-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Trainer</th>
                            <th>Scheduled</th>
                            <th>Capacity</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $c): ?>
                        <tr data-search="<?= htmlspecialchars(strtolower($c['name'] . ' ' . $c['type'] . ' ' . $c['trainer_name'] . ' ' . date('M j, Y H:i', strtotime($c['scheduled_at']))), ENT_QUOTES, 'UTF-8') ?>">
                            <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                            <td><?= htmlspecialchars($c['type']) ?></td>
                            <td><?= htmlspecialchars($c['trainer_name']) ?></td>
                            <td><?= date('M j, Y H:i', strtotime($c['scheduled_at'])) ?></td>
                            <td><?= (int)$c['capacity'] ?></td>
                            <td>
                                <?php if ($c['is_featured']): ?>
                                    <span class="badge badge-featured">Featured</span>
                                <?php else: ?>
                                    <span class="admin-muted-marker">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn-admin-ghost" data-edit-class="<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>">Edit</button>
                                    <form method="POST" class="admin-action-form" data-confirm="Delete this class?">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="action" value="delete_class">
                                        <input type="hidden" name="class_id" value="<?= (int)$c['id'] ?>">
                                        <button type="submit" class="btn-admin-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($classes)): ?>
                        <tr class="admin-static-empty">
                            <td colspan="7" class="admin-empty-cell">No classes yet.</td>
                        </tr>
                        <?php endif; ?>
                        <tr class="admin-filter-empty" hidden>
                            <td colspan="7" class="admin-empty-cell">No classes match your search.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SECTION: EQUIPMENT -->
        <section id="section-equipment" class="admin-section">
            <div class="section-header">
                <h2>Equipment</h2>
                <button class="btn-admin-primary" data-open-modal="modal-create-equipment">
                    + Add Equipment
                </button>
            </div>
            <div class="admin-table-toolbar">
                <input type="search" class="admin-search-input" data-table-filter="equipment-table" placeholder="Search equipment by name, category or status">
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table" id="equipment-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Available / Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipment as $eq): ?>
                        <?php
                            $availableCount = (int)$eq['available_count'];
                            $totalCount = (int)$eq['total_count'];
                            $pct   = $totalCount > 0 ? round($availableCount / $totalCount * 100) : 0;
                            $pctClass = 'avail-width-' . max(0, min(100, (int)(round($pct / 10) * 10)));
                            $color = $availableCount > 0 ? 'green' : 'red';
                        ?>
                        <tr data-search="<?= htmlspecialchars(strtolower($eq['name'] . ' ' . ($eq['category'] ?? '') . ' ' . ($eq['status'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                            <td><strong><?= htmlspecialchars($eq['name']) ?></strong></td>
                            <td><?= htmlspecialchars($eq['category'] ?? '—') ?></td>
                            <td>
                                <div class="avail-bar-wrap">
                                    <span class="avail-count"><?= $availableCount ?></span>
                                    <span class="avail-total">/ <?= $totalCount ?></span>
                                    <div class="avail-bar-track">
                                        <div class="avail-bar-fill <?= $color ?> <?= $pctClass ?>"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php $equipmentStatusClass = preg_replace('/[^a-z0-9_-]/', '', strtolower($eq['status'] ?? 'retired')); ?>
                                <span class="badge badge-<?= htmlspecialchars($equipmentStatusClass, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars(strtoupper($eq['status'] ?? 'retired')) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn-admin-ghost" data-edit-equipment="<?= htmlspecialchars(json_encode($eq), ENT_QUOTES, 'UTF-8') ?>">Edit</button>
                                    <form method="POST" class="admin-action-form" data-confirm="Remove this equipment?">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="action" value="delete_equipment">
                                        <input type="hidden" name="equipment_id" value="<?= (int)$eq['id'] ?>">
                                        <button type="submit" class="btn-admin-danger">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($equipment)): ?>
                        <tr class="admin-static-empty">
                            <td colspan="5" class="admin-empty-cell">No equipment yet.</td>
                        </tr>
                        <?php endif; ?>
                        <tr class="admin-filter-empty" hidden>
                            <td colspan="5" class="admin-empty-cell">No equipment matches your search.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

<!-- MODALS -->

<!-- CREATE MEMBER -->
<div class="modal-backdrop" id="modal-create-member">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Member</h3>
            <button class="modal-close" data-close-modal="modal-create-member">X</button>
        </div>
        <form method="POST" class="admin-form">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="create_member">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Full Name</label>
                    <input type="text" name="full_name" maxlength="100" required placeholder="Jane Kowalska">
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" maxlength="30" pattern="[A-Za-z0-9_.-]{3,30}" required placeholder="janekowalska">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" maxlength="120" required placeholder="jane@email.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" minlength="6" required>
                </div>
                <div class="form-group">
                    <label>Membership Plan</label>
                    <select name="membership_tier">
                        <option value="basic">Basic</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-admin-ghost" data-close-modal="modal-create-member">Cancel</button>
                <button type="submit" class="btn-admin-primary">Create Member</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MEMBER -->
<div class="modal-backdrop" id="modal-edit-member">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Member</h3>
            <button class="modal-close" data-close-modal="modal-edit-member">X</button>
        </div>
        <form method="POST" class="admin-form" id="form-edit-member">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="update_member">
            <input type="hidden" name="user_id" id="edit-member-uid">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="edit-member-fullname" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit-member-username" maxlength="30" pattern="[A-Za-z0-9_.-]{3,30}" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit-member-email" maxlength="120" required>
                </div>
                <div class="form-group">
                    <label>New Password (leave blank to keep)</label>
                    <input type="password" name="new_password" minlength="6">
                </div>
                <div class="form-group">
                    <label>Membership Plan</label>
                    <select name="membership_tier" id="edit-member-tier">
                        <option value="basic">Basic</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="membership_status" id="edit-member-status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-admin-ghost" data-close-modal="modal-edit-member">Cancel</button>
                <button type="submit" class="btn-admin-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- CREATE TRAINER -->
<div class="modal-backdrop" id="modal-create-trainer">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Trainer</h3>
            <button class="modal-close" data-close-modal="modal-create-trainer">X</button>
        </div>
        <form method="POST" class="admin-form">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="create_trainer">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Full Name</label>
                    <input type="text" name="full_name" maxlength="100" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" maxlength="30" pattern="[A-Za-z0-9_.-]{3,30}" required placeholder="johndoe">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" maxlength="120" required placeholder="john@fitlife.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" minlength="6" required>
                </div>
                <div class="form-group">
                    <label>Specializations</label>
                    <input type="text" name="specializations" maxlength="255" placeholder="Yoga, HIIT, Pilates">
                </div>
                <div class="form-group">
                    <label>Certifications</label>
                    <input type="text" name="certifications" maxlength="255" placeholder="ACE, NASM">
                </div>
                <div class="form-group span2">
                    <label>Bio</label>
                    <textarea name="bio" maxlength="1000" placeholder="Short trainer biography..."></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-admin-ghost" data-close-modal="modal-create-trainer">Cancel</button>
                <button type="submit" class="btn-admin-primary">Create Trainer</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT TRAINER -->
<div class="modal-backdrop" id="modal-edit-trainer">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Trainer</h3>
            <button class="modal-close" data-close-modal="modal-edit-trainer">X</button>
        </div>
        <form method="POST" class="admin-form">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="update_trainer">
            <input type="hidden" name="user_id" id="edit-trainer-uid">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="edit-trainer-fullname" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit-trainer-username" maxlength="30" pattern="[A-Za-z0-9_.-]{3,30}" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit-trainer-email" maxlength="120" required>
                </div>
                <div class="form-group">
                    <label>New Password (leave blank to keep)</label>
                    <input type="password" name="new_password" minlength="6">
                </div>
                <div class="form-group">
                    <label>Specializations</label>
                    <input type="text" name="specializations" id="edit-trainer-specs" maxlength="255">
                </div>
                <div class="form-group">
                    <label>Certifications</label>
                    <input type="text" name="certifications" id="edit-trainer-certs" maxlength="255">
                </div>
                <div class="form-group span2">
                    <label>Bio</label>
                    <textarea name="bio" id="edit-trainer-bio" maxlength="1000"></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-admin-ghost" data-close-modal="modal-edit-trainer">Cancel</button>
                <button type="submit" class="btn-admin-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- CREATE CLASS -->
<div class="modal-backdrop" id="modal-create-class">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Class</h3>
            <button class="modal-close" data-close-modal="modal-create-class">X</button>
        </div>
        <form method="POST" class="admin-form">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="create_class">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Class Name</label>
                    <input type="text" name="name" maxlength="100" required placeholder="Power HIIT">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        <option value="HIIT">HIIT</option>
                        <option value="Yoga">Yoga</option>
                        <option value="Pilates">Pilates</option>
                        <option value="Spinning">Spinning</option>
                        <option value="Strength">Strength</option>
                        <option value="Cardio">Cardio</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Trainer</label>
                    <select name="trainer_id" required>
                        <?php foreach ($trainers as $t): ?>
                            <?php if (!str_contains($t['bio'] ?? '', '[DEACTIVATED]')): ?>
                            <option value="<?= (int)$t['trainer_id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Room</label>
                    <input type="text" name="room" maxlength="80" placeholder="Studio A">
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" value="20" min="1" required>
                </div>
                <div class="form-group">
                    <label>Scheduled At</label>
                    <input type="datetime-local" name="scheduled_at" required>
                </div>
                <div class="form-group">
                    <label>Duration (minutes)</label>
                    <input type="number" name="duration_min" value="60" min="5" required>
                </div>
                <div class="form-group span2">
                    <div class="checkbox-row">
                        <input type="checkbox" name="is_featured" id="create-featured" value="1">
                        <label for="create-featured" class="admin-checkbox-label">Mark as Featured (shown on homepage)</label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-admin-ghost" data-close-modal="modal-create-class">Cancel</button>
                <button type="submit" class="btn-admin-primary">Create Class</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT CLASS -->
<div class="modal-backdrop" id="modal-edit-class">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Class</h3>
            <button class="modal-close" data-close-modal="modal-edit-class">X</button>
        </div>
        <form method="POST" class="admin-form">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="update_class">
            <input type="hidden" name="class_id" id="edit-class-id">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Class Name</label>
                    <input type="text" name="name" id="edit-class-name" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" id="edit-class-type">
                        <option value="HIIT">HIIT</option>
                        <option value="Yoga">Yoga</option>
                        <option value="Pilates">Pilates</option>
                        <option value="Spinning">Spinning</option>
                        <option value="Strength">Strength</option>
                        <option value="Cardio">Cardio</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Trainer</label>
                    <select name="trainer_id" id="edit-class-trainer" required>
                        <?php foreach ($trainers as $t): ?>
                        <option value="<?= (int)$t['trainer_id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Room</label>
                    <input type="text" name="room" id="edit-class-room" maxlength="80">
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" id="edit-class-capacity" min="1" required>
                </div>
                <div class="form-group">
                    <label>Scheduled At</label>
                    <input type="datetime-local" name="scheduled_at" id="edit-class-scheduled" required>
                </div>
                <div class="form-group">
                    <label>Duration (minutes)</label>
                    <input type="number" name="duration_min" id="edit-class-duration" min="5" required>
                </div>
                <div class="form-group span2">
                    <div class="checkbox-row">
                        <input type="checkbox" name="is_featured" id="edit-class-featured" value="1">
                        <label for="edit-class-featured" class="admin-checkbox-label">Mark as Featured</label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-admin-ghost" data-close-modal="modal-edit-class">Cancel</button>
                <button type="submit" class="btn-admin-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- CREATE EQUIPMENT -->
<div class="modal-backdrop" id="modal-create-equipment">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add Equipment</h3>
            <button class="modal-close" data-close-modal="modal-create-equipment">X</button>
        </div>
        <form method="POST" class="admin-form">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="create_equipment">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Equipment Name</label>
                    <input type="text" name="name" maxlength="100" required placeholder="Treadmill X7">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="Cardio">Cardio</option>
                        <option value="Weights">Weights</option>
                        <option value="Strength">Strength</option>
                        <option value="Functional">Functional</option>
                        <option value="Stretching">Stretching</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="operational">Operational</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Total Count</label>
                    <input type="number" name="total_count" value="1" min="0" required>
                </div>
                <div class="form-group">
                    <label>Available Count</label>
                    <input type="number" name="available_count" value="1" min="0" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-admin-ghost" data-close-modal="modal-create-equipment">Cancel</button>
                <button type="submit" class="btn-admin-primary">Add Equipment</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT EQUIPMENT -->
<div class="modal-backdrop" id="modal-edit-equipment">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Equipment</h3>
            <button class="modal-close" data-close-modal="modal-edit-equipment">X</button>
        </div>
        <form method="POST" class="admin-form">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="update_equipment">
            <input type="hidden" name="equipment_id" id="edit-eq-id">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Equipment Name</label>
                    <input type="text" name="name" id="edit-eq-name" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" id="edit-eq-category">
                        <option value="Cardio">Cardio</option>
                        <option value="Weights">Weights</option>
                        <option value="Strength">Strength</option>
                        <option value="Functional">Functional</option>
                        <option value="Stretching">Stretching</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit-eq-status">
                        <option value="operational">Operational</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Total Count</label>
                    <input type="number" name="total_count" id="edit-eq-total" min="0" required>
                </div>
                <div class="form-group">
                    <label>Available Count</label>
                    <input type="number" name="available_count" id="edit-eq-available" min="0" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-admin-ghost" data-close-modal="modal-edit-equipment">Cancel</button>
                <button type="submit" class="btn-admin-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
