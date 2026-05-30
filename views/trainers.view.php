<?php include 'header.php'; ?>

<main class="page-container" style="padding: 40px 20px;">
    
    <header style="text-align: center; margin-bottom: 50px;">
        <h2 style="margin-bottom: 10px;">MEET OUR EXPERTS</h2>
        <p style="color: var(--clr-text-muted);">The coaches behind our classes and personal training sessions.</p>
    </header>

    <?php if (empty($trainers)): ?>
        <section class="card" style="text-align: center; padding: 40px;">
            <p>No trainers are available at the moment.</p>
        </section>
    <?php else: ?>
        <section style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 30px; max-width: 1200px; margin: 0 auto;">
            
            <?php foreach ($trainers as $trainer): ?>
                <?php
                    $trainerClasses = $classesByTrainer[$trainer['id']] ?? [];
                    // Limitamos a apenas 2 aulas na visualização para não deformar o cartão
                    $visibleClasses = array_slice($trainerClasses, 0, 2);
                    $specializations = array_filter(array_map('trim', explode(',', $trainer['specializations'] ?? '')));
                    $reviewCount = (int)$trainer['review_count'];
                    $averageRating = $reviewCount > 0 ? number_format((float)$trainer['average_rating'], 1) : null;
                ?>
                
                <article class="card" style="display: flex; flex-direction: column; padding: 30px; text-align: center; transition: transform 0.2s; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); <?= empty($trainer['is_active']) ? 'opacity: 0.6;' : '' ?>">
                    
                    <?php if (empty($trainer['is_active'])): ?>
                        <div style="background-color: #FEE2E2; color: #DC2626; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; margin: -10px auto 15px auto; letter-spacing: 1px;">
                            Inactive (Hidden)
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin: 0 auto 20px auto; width: 120px; height: 120px; border-radius: 50%; overflow: hidden; border: 3px solid var(--clr-primary); box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <img
                            src="images/<?= htmlspecialchars($trainer['profile_photo'] ?: 'pfp.png') ?>"
                            alt="<?= htmlspecialchars($trainer['full_name']) ?>"
                            loading="lazy"
                            style="width: 100%; height: 100%; object-fit: cover;"
                        >
                    </div>

                    <h3 style="margin-bottom: 5px;"><?= htmlspecialchars($trainer['full_name']) ?></h3>
                    
                    <div style="color: #D97706; font-weight: bold; margin-bottom: 15px; font-size: 0.95rem;">
                        <?php if ($reviewCount > 0): ?>
                            ⭐ <?= htmlspecialchars($averageRating) ?>/5 <span style="color: var(--clr-text-muted); font-weight: normal; font-size: 0.85rem;">(<?= $reviewCount ?> <?= $reviewCount === 1 ? 'review' : 'reviews' ?>)</span>
                        <?php else: ?>
                            <span style="color: var(--clr-text-muted); font-weight: normal; font-size: 0.85rem;">No reviews yet</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($specializations)): ?>
                        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-bottom: 20px;">
                            <?php foreach ($specializations as $specialization): ?>
                                <span class="tag" style="font-size: 0.75rem; padding: 4px 10px; background-color: rgba(37,99,235,0.05); color: var(--clr-primary); border: 1px solid rgba(37,99,235,0.2);"><?= htmlspecialchars($specialization) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($trainer['bio'])): ?>
                        <p style="font-size: 0.9rem; color: var(--clr-text-muted); margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">
                            <?= htmlspecialchars($trainer['bio']) ?>
                        </p>
                    <?php endif; ?>

                    <div style="flex-grow: 1;"></div>

                    <?php if (!empty($visibleClasses)): ?>
                        <div style="background-color: #F9FAFB; border-radius: 8px; padding: 12px; margin-bottom: 20px; border: 1px solid #E5E7EB;">
                            <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--clr-text-muted); text-align: left; margin-bottom: 8px; letter-spacing: 0.5px;">Upcoming Classes</h4>
                            <ul style="list-style: none; padding: 0; margin: 0; text-align: left; font-size: 0.85rem;">
                                <?php foreach ($visibleClasses as $class): ?>
                                    <li style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <strong style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 60%;"><?= htmlspecialchars($class['name']) ?></strong>
                                        <span style="color: var(--clr-primary); font-weight: 600;"><?= htmlspecialchars(date('d M H:i', strtotime($class['scheduled_at']))) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php if (count($trainerClasses) > count($visibleClasses)): ?>
                                <p style="font-size: 0.75rem; color: var(--clr-text-muted); margin-top: 6px; text-align: center;">+<?= count($trainerClasses) - count($visibleClasses) ?> more scheduled</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; gap: 10px;">
                        <a href="trainer_profile.php?id=<?= (int)$trainer['id'] ?>" class="btn btn-primary" style="flex: 1; padding: 10px; font-size: 0.9rem;">Profile</a>
                        <a href="classes.php?trainer=<?= urlencode($trainer['full_name']) ?>" class="btn btn-secondary" style="flex: 1; padding: 10px; font-size: 0.9rem;">Classes</a>
                    </div>
                    
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>