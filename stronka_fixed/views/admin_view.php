<link rel="stylesheet" href="admin.css">

<!-- Admin Shell -->
<div class="admin-shell">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h3>Admin Panel</h3>
            <p>FitLife Management</p>
        </div>
        <button class="sidebar-nav-btn active" onclick="showSection('overview')" id="nav-overview">
            <span class="nav-icon">[=]</span> Overview
        </button>
        <button class="sidebar-nav-btn" onclick="showSection('members')" id="nav-members">
            <span class="nav-icon">[M]</span> Members
        </button>
        <button class="sidebar-nav-btn" onclick="showSection('trainers')" id="nav-trainers">
            <span class="nav-icon">[T]</span> Trainers
        </button>
        <button class="sidebar-nav-btn" onclick="showSection('classes')" id="nav-classes">
            <span class="nav-icon">[C]</span> Classes
        </button>
        <button class="sidebar-nav-btn" onclick="showSection('equipment')" id="nav-equipment">
            <span class="nav-icon">[E]</span> Equipment
        </button>
        <button class="sidebar-nav-btn" onclick="showSection('elevate')" id="nav-elevate">
            <span class="nav-icon">[A]</span> Elevate to Admin
        </button>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">

        <!-- Alerts -->
        <?php if ($success): ?>
            <div class="admin-alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="admin-alert error"><?= htmlspecialchars($error) ?></div>
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
                    <li><strong>Elevate to Admin</strong> — Promote any user to admin role.</li>
                </ul>
            </div>
        </section>

        <!-- SECTION: MEMBERS -->
        <section id="section-members" class="admin-section">
            <div class="section-header">
                <h2>Members</h2>
                <button class="btn-admin-primary" onclick="openModal('modal-create-member')">
                    + Add Member
                </button>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
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
                        <tr>
                            <td><strong><?= htmlspecialchars($m['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($m['username']) ?></td>
                            <td><?= htmlspecialchars($m['email']) ?></td>
                            <td>
                                <span class="badge badge-<?= $m['membership_tier'] ?>">
                                    <?= strtoupper($m['membership_tier']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= ($m['membership_status'] === 'active') ? 'active' : 'inactive' ?>">
                                    <?= strtoupper($m['membership_status'] ?? 'active') ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn-admin-ghost" onclick='openEditMember(<?= json_encode($m) ?>)'>Edit</button>
                                    <?php if (($m['membership_status'] ?? 'active') !== 'inactive'): ?>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Deactivate this member?')">
                                        <input type="hidden" name="action" value="deactivate_member">
                                        <input type="hidden" name="user_id" value="<?= $m['user_id'] ?>">
                                        <button type="submit" class="btn-admin-danger">Deactivate</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($members)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;color:var(--admin-muted);padding:32px;">No members yet.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SECTION: TRAINERS -->
        <section id="section-trainers" class="admin-section">
            <div class="section-header">
                <h2>Trainers</h2>
                <button class="btn-admin-primary" onclick="openModal('modal-create-trainer')">
                    + Add Trainer
                </button>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
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
                        <tr>
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
                                    <button class="btn-admin-ghost" onclick='openEditTrainer(<?= json_encode($t) ?>)'>Edit</button>
                                    <?php if (!$isDeactivated): ?>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Deactivate this trainer?')">
                                        <input type="hidden" name="action" value="deactivate_trainer">
                                        <input type="hidden" name="user_id" value="<?= $t['user_id'] ?>">
                                        <button type="submit" class="btn-admin-danger">Deactivate</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($trainers)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;color:var(--admin-muted);padding:32px;">No trainers yet.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SECTION: CLASSES -->
        <section id="section-classes" class="admin-section">
            <div class="section-header">
                <h2>Class Catalog</h2>
                <button class="btn-admin-primary" onclick="openModal('modal-create-class')">
                    + Add Class
                </button>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
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
                        <tr>
                            <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                            <td><?= htmlspecialchars($c['type']) ?></td>
                            <td><?= htmlspecialchars($c['trainer_name']) ?></td>
                            <td><?= date('M j, Y H:i', strtotime($c['scheduled_at'])) ?></td>
                            <td><?= $c['capacity'] ?></td>
                            <td>
                                <?php if ($c['is_featured']): ?>
                                    <span class="badge badge-featured">Featured</span>
                                <?php else: ?>
                                    <span style="color:var(--admin-muted);font-size:0.82rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn-admin-ghost" onclick='openEditClass(<?= json_encode($c) ?>)'>Edit</button>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this class?')">
                                        <input type="hidden" name="action" value="delete_class">
                                        <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn-admin-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($classes)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;color:var(--admin-muted);padding:32px;">No classes yet.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SECTION: EQUIPMENT -->
        <section id="section-equipment" class="admin-section">
            <div class="section-header">
                <h2>Equipment</h2>
                <button class="btn-admin-primary" onclick="openModal('modal-create-equipment')">
                    + Add Equipment
                </button>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
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
                            $pct   = $eq['total_count'] > 0 ? round($eq['available_count'] / $eq['total_count'] * 100) : 0;
                            $color = $eq['available_count'] > 0 ? 'green' : 'red';
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($eq['name']) ?></strong></td>
                            <td><?= htmlspecialchars($eq['category'] ?? '—') ?></td>
                            <td>
                                <div class="avail-bar-wrap">
                                    <span class="avail-count"><?= $eq['available_count'] ?></span>
                                    <span class="avail-total">/ <?= $eq['total_count'] ?></span>
                                    <div class="avail-bar-track">
                                        <div class="avail-bar-fill <?= $color ?>" style="width:<?= $pct ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?= $eq['status'] ?>">
                                    <?= strtoupper($eq['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn-admin-ghost" onclick='openEditEquipment(<?= json_encode($eq) ?>)'>Edit</button>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Remove this equipment?')">
                                        <input type="hidden" name="action" value="delete_equipment">
                                        <input type="hidden" name="equipment_id" value="<?= $eq['id'] ?>">
                                        <button type="submit" class="btn-admin-danger">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($equipment)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;color:var(--admin-muted);padding:32px;">No equipment yet.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SECTION: ELEVATE -->
        <section id="section-elevate" class="admin-section">
            <div class="section-header">
                <h2>Elevate to Admin</h2>
            </div>
            <p style="color:var(--admin-muted);margin-bottom:20px;font-size:0.92rem;">
                Select a user to promote to admin status. This action is irreversible without direct database access.
            </p>
            <?php if (empty($nonAdmins)): ?>
                <p style="color:var(--admin-muted);">All users are already admins.</p>
            <?php else: ?>
            <div class="elevate-grid">
                <?php foreach ($nonAdmins as $u): ?>
                <div class="elevate-card">
                    <div class="elevate-user-info">
                        <strong><?= htmlspecialchars($u['username']) ?></strong>
                        <small><?= htmlspecialchars($u['email']) ?></small>
                    </div>
                    <span class="badge badge-<?= $u['role'] ?>"><?= strtoupper($u['role']) ?></span>
                    <form method="POST" onsubmit="return confirm('Promote <?= htmlspecialchars(addslashes($u['username'])) ?> to Admin? This cannot be undone.')">
                        <input type="hidden" name="action" value="elevate_admin">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn-admin-primary" style="padding:7px 14px;font-size:0.8rem;">
                            Elevate
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>

    </main>
</div>

<!-- MODALS -->

<!-- CREATE MEMBER -->
<div class="modal-backdrop" id="modal-create-member">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Member</h3>
            <button class="modal-close" onclick="closeModal('modal-create-member')">X</button>
        </div>
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="create_member">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required placeholder="Jane Kowalska">
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="janekowalska">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="jane@email.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
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
                <button type="button" class="btn-admin-ghost" onclick="closeModal('modal-create-member')">Cancel</button>
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
            <button class="modal-close" onclick="closeModal('modal-edit-member')">X</button>
        </div>
        <form method="POST" class="admin-form" id="form-edit-member">
            <input type="hidden" name="action" value="update_member">
            <input type="hidden" name="user_id" id="edit-member-uid">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="edit-member-fullname" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit-member-username" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit-member-email" required>
                </div>
                <div class="form-group">
                    <label>New Password (leave blank to keep)</label>
                    <input type="password" name="new_password">
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
                <button type="button" class="btn-admin-ghost" onclick="closeModal('modal-edit-member')">Cancel</button>
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
            <button class="modal-close" onclick="closeModal('modal-create-trainer')">X</button>
        </div>
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="create_trainer">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="johndoe">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="john@fitlife.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Specializations</label>
                    <input type="text" name="specializations" placeholder="Yoga, HIIT, Pilates">
                </div>
                <div class="form-group">
                    <label>Certifications</label>
                    <input type="text" name="certifications" placeholder="ACE, NASM">
                </div>
                <div class="form-group span2">
                    <label>Bio</label>
                    <textarea name="bio" placeholder="Short trainer biography..."></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-admin-ghost" onclick="closeModal('modal-create-trainer')">Cancel</button>
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
            <button class="modal-close" onclick="closeModal('modal-edit-trainer')">X</button>
        </div>
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="update_trainer">
            <input type="hidden" name="user_id" id="edit-trainer-uid">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="edit-trainer-fullname" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit-trainer-username" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit-trainer-email" required>
                </div>
                <div class="form-group">
                    <label>New Password (leave blank to keep)</label>
                    <input type="password" name="new_password">
                </div>
                <div class="form-group">
                    <label>Specializations</label>
                    <input type="text" name="specializations" id="edit-trainer-specs">
                </div>
                <div class="form-group">
                    <label>Certifications</label>
                    <input type="text" name="certifications" id="edit-trainer-certs">
                </div>
                <div class="form-group span2">
                    <label>Bio</label>
                    <textarea name="bio" id="edit-trainer-bio"></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-admin-ghost" onclick="closeModal('modal-edit-trainer')">Cancel</button>
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
            <button class="modal-close" onclick="closeModal('modal-create-class')">X</button>
        </div>
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="create_class">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Class Name</label>
                    <input type="text" name="name" required placeholder="Power HIIT">
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
                            <option value="<?= $t['trainer_id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Room</label>
                    <input type="text" name="room" placeholder="Studio A">
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
                    <input type="number" name="duration_min" value="60" min="5">
                </div>
                <div class="form-group span2">
                    <div class="checkbox-row">
                        <input type="checkbox" name="is_featured" id="create-featured" value="1">
                        <label for="create-featured" style="text-transform:none;font-size:0.9rem;">Mark as Featured (shown on homepage)</label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-admin-ghost" onclick="closeModal('modal-create-class')">Cancel</button>
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
            <button class="modal-close" onclick="closeModal('modal-edit-class')">X</button>
        </div>
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="update_class">
            <input type="hidden" name="class_id" id="edit-class-id">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Class Name</label>
                    <input type="text" name="name" id="edit-class-name" required>
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
                    <select name="trainer_id" id="edit-class-trainer">
                        <?php foreach ($trainers as $t): ?>
                        <option value="<?= $t['trainer_id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Room</label>
                    <input type="text" name="room" id="edit-class-room">
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" id="edit-class-capacity" min="1">
                </div>
                <div class="form-group">
                    <label>Scheduled At</label>
                    <input type="datetime-local" name="scheduled_at" id="edit-class-scheduled">
                </div>
                <div class="form-group">
                    <label>Duration (minutes)</label>
                    <input type="number" name="duration_min" id="edit-class-duration" min="5">
                </div>
                <div class="form-group span2">
                    <div class="checkbox-row">
                        <input type="checkbox" name="is_featured" id="edit-class-featured" value="1">
                        <label for="edit-class-featured" style="text-transform:none;font-size:0.9rem;">Mark as Featured</label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-admin-ghost" onclick="closeModal('modal-edit-class')">Cancel</button>
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
            <button class="modal-close" onclick="closeModal('modal-create-equipment')">X</button>
        </div>
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="create_equipment">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Equipment Name</label>
                    <input type="text" name="name" required placeholder="Treadmill X7">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="Cardio">Cardio</option>
                        <option value="Weights">Weights</option>
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
                <button type="button" class="btn-admin-ghost" onclick="closeModal('modal-create-equipment')">Cancel</button>
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
            <button class="modal-close" onclick="closeModal('modal-edit-equipment')">X</button>
        </div>
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="update_equipment">
            <input type="hidden" name="equipment_id" id="edit-eq-id">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Equipment Name</label>
                    <input type="text" name="name" id="edit-eq-name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" id="edit-eq-category">
                        <option value="Cardio">Cardio</option>
                        <option value="Weights">Weights</option>
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
                    <input type="number" name="total_count" id="edit-eq-total" min="0">
                </div>
                <div class="form-group">
                    <label>Available Count</label>
                    <input type="number" name="available_count" id="edit-eq-available" min="0">
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-admin-ghost" onclick="closeModal('modal-edit-equipment')">Cancel</button>
                <button type="submit" class="btn-admin-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// Sidebar Navigation
function showSection(name) {
    document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.sidebar-nav-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('section-' + name).classList.add('active');
    document.getElementById('nav-' + name).classList.add('active');
}

// Auto-open section from URL hash
const hash = location.hash.replace('#', '');
if (['overview','members','trainers','classes','equipment','elevate'].includes(hash)) {
    showSection(hash);
}

// Modal helpers
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

// Close on backdrop click
document.querySelectorAll('.modal-backdrop').forEach(b => {
    b.addEventListener('click', e => { if (e.target === b) closeModal(b.id); });
});

// Esc to close
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-backdrop.open').forEach(m => closeModal(m.id));
    }
});

// Populate Edit Modals
function openEditMember(m) {
    document.getElementById('edit-member-uid').value      = m.user_id;
    document.getElementById('edit-member-fullname').value = m.full_name;
    document.getElementById('edit-member-username').value = m.username;
    document.getElementById('edit-member-email').value    = m.email;
    document.getElementById('edit-member-tier').value     = m.membership_tier;
    document.getElementById('edit-member-status').value   = m.membership_status || 'active';
    openModal('modal-edit-member');
}

function openEditTrainer(t) {
    document.getElementById('edit-trainer-uid').value      = t.user_id;
    document.getElementById('edit-trainer-fullname').value = t.full_name;
    document.getElementById('edit-trainer-username').value = t.username;
    document.getElementById('edit-trainer-email').value    = t.email;
    document.getElementById('edit-trainer-specs').value    = t.specializations || '';
    document.getElementById('edit-trainer-certs').value    = t.certifications  || '';
    document.getElementById('edit-trainer-bio').value      = (t.bio || '').replace(' [DEACTIVATED]', '');
    openModal('modal-edit-trainer');
}

function openEditClass(c) {
    document.getElementById('edit-class-id').value         = c.id;
    document.getElementById('edit-class-name').value       = c.name;
    document.getElementById('edit-class-type').value       = c.type;
    document.getElementById('edit-class-trainer').value    = c.trainer_id;
    document.getElementById('edit-class-room').value       = c.room || '';
    document.getElementById('edit-class-capacity').value   = c.capacity;
    document.getElementById('edit-class-scheduled').value  = (c.scheduled_at || '').replace(' ', 'T').slice(0, 16);
    document.getElementById('edit-class-duration').value   = c.duration_min || 60;
    document.getElementById('edit-class-featured').checked = c.is_featured == 1;
    openModal('modal-edit-class');
}

function openEditEquipment(eq) {
    document.getElementById('edit-eq-id').value        = eq.id;
    document.getElementById('edit-eq-name').value      = eq.name;
    document.getElementById('edit-eq-category').value  = eq.category || 'Other';
    document.getElementById('edit-eq-status').value    = eq.status || 'operational';
    document.getElementById('edit-eq-total').value     = eq.total_count;
    document.getElementById('edit-eq-available').value = eq.available_count;
    openModal('modal-edit-equipment');
}
</script>

<?php include 'footer.php'; ?>
