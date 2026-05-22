<?php include 'header.php'; ?>

<main class="page-container">
    <header class="page-header">
        <h2>CLASS SCHEDULE</h2>
        <p>Find a class for yourself and book your spot.</p>
    </header>

    <section class="card filters-section">
        <div class="filters-row">
            <div class="form-group">
                <label>Type</label>
                <select id="filter-type">
                    <option value="all">All</option>
                    <option value="Yoga">Yoga</option>
                    <option value="HIIT">HIIT</option>
                    <option value="Pilates">Pilates</option>
                    <option value="Spinning">Spinning</option>
                </select>
            </div>
            <div class="form-group">
                <label>Coach</label>
                <select id="filter-trainer">
                    <option value="all">All</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?= htmlspecialchars($t['full_name']) ?>">
                            <?= htmlspecialchars($t['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </section>

    <section id="classes-list">
        <?php foreach ($classes as $class): ?>
            <article class="card class-card"
                     data-type="<?= htmlspecialchars($class['type']) ?>"
                     data-trainer="<?= htmlspecialchars($class['trainer_name']) ?>">
                <div class="class-card-info">
                    <span class="tag"><?= htmlspecialchars($class['type']) ?></span>
                    <h3><?= htmlspecialchars($class['name']) ?></h3>
                    <p class="class-meta">
                        <?= date('l', strtotime($class['scheduled_at'])) ?> |
                        <?= date('H:i', strtotime($class['scheduled_at'])) ?> |
                        Coach: <?= htmlspecialchars($class['trainer_name']) ?> |
                        Spots: <?= $class['current_enrollments'] ?>/<?= $class['capacity'] ?>
                    </p>
                </div>
                <div class="class-actions">
                    <?php if (!$userId): ?>
                        <a href="login.php" class="btn btn-secondary">Log in to join</a>
                    <?php elseif ($class['is_user_enrolled']): ?>
                        <button class="btn btn-secondary" onclick="handleEnroll(<?= $class['id'] ?>, 'cancel')">Unsubscribe</button>
                    <?php elseif ($class['current_enrollments'] >= $class['capacity']): ?>
                        <button class="btn btn-secondary" disabled>No spots available</button>
                    <?php else: ?>
                        <button class="btn btn-primary" onclick="handleEnroll(<?= $class['id'] ?>, 'enroll')">Join us</button>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<script>
const typeFilter    = document.getElementById('filter-type');
const trainerFilter = document.getElementById('filter-trainer');
const classCards    = document.querySelectorAll('.class-card');

function filterClasses() {
    const type    = typeFilter.value;
    const trainer = trainerFilter.value;
    classCards.forEach(card => {
        const matchesType    = type    === 'all' || card.dataset.type    === type;
        const matchesTrainer = trainer === 'all' || card.dataset.trainer === trainer;
        card.style.display = (matchesType && matchesTrainer) ? 'flex' : 'none';
    });
}

typeFilter.addEventListener('change', filterClasses);
trainerFilter.addEventListener('change', filterClasses);

function handleEnroll(classId, action) {
    const formData = new FormData();
    formData.append('class_id', classId);
    formData.append('action', action);
    fetch('enroll_action.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || "Error");
        });
}
</script>

<?php include 'footer.php'; ?>
