<?php include 'header.php'; ?>

<main class="page-container">
    <header class="page-header">
        <h2>Equipment Availability</h2>
        <p>Check the status of the machines in the training room in real time.</p>
    </header>

    <div class="equipment-grid">
        <?php foreach ($equipment as $item): ?>
            <div class="card equipment-card">
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p>Category: <?= htmlspecialchars($item['category']) ?></p>
                <div class="equipment-availability">
                    Available: <?= $item['available_count'] ?> / <?= $item['total_count'] ?>
                </div>
                <div class="equipment-status">
                    <span class="tag <?= $item['status'] === 'operational' ? 'tag-yoga' : 'tag-hiit' ?>">
                        <?= strtoupper($item['status']) ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
