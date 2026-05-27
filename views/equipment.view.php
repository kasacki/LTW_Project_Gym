<?php include 'header.php'; ?>

<main class="page-container">
    <header class="page-header">
        <h2>Equipment Availability</h2>
        <p>Check the status of the machines in the training room in real time.</p>
    </header>

    <section class="card equipment-refresh-panel">
        <div>
            <h3>Training Room</h3>
            <p id="equipment-refresh-status">Availability loaded from the database.</p>
        </div>
        <button type="button" class="btn btn-secondary" id="equipment-refresh-button">Refresh</button>
    </section>

    <div class="equipment-grid" id="equipment-grid">
        <?php foreach ($equipment as $item): ?>
            <?php
                $status = $item['status'] ?? 'unknown';
                $statusClass = $status === 'operational' ? 'tag-yoga' : ($status === 'maintenance' ? 'tag-spinning' : 'tag-hiit');
            ?>
            <article class="card equipment-card" data-equipment-id="<?= (int)$item['id'] ?>">
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p>Category: <?= htmlspecialchars($item['category'] ?? 'Other') ?></p>
                <div class="equipment-availability">
                    Available: <?= (int)$item['available_count'] ?> / <?= (int)$item['total_count'] ?>
                </div>
                <div class="equipment-status">
                    <span class="tag <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars(strtoupper($status)) ?>
                    </span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
