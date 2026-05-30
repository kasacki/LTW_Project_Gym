<?php include 'header.php'; ?>

<main class="page-container" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">

    <?php if ($message): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom: 20px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card" style="display: flex; flex-wrap: wrap; gap: 40px; align-items: center; padding: 40px; margin-bottom: 30px; border-top: 5px solid var(--clr-primary);">
        
        <div style="flex-shrink: 0; width: 180px; height: 180px; border-radius: 50%; overflow: hidden; border: 4px solid #E5E7EB; box-shadow: 0 10px 15px rgba(0,0,0,0.1);">
            <img src="images/<?= htmlspecialchars($trainer['profile_photo'] ?: 'pfp.png') ?>"
                 alt="<?= htmlspecialchars($trainer['full_name']) ?>"
                 style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        
        <div style="flex-grow: 1;">
            <h2 style="font-size: 2.5rem; margin-bottom: 5px;"><?= htmlspecialchars($trainer['full_name']) ?></h2>
            <p style="color: var(--clr-primary); font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">Personal Trainer</p>

            <div style="margin-bottom: 20px; font-size: 1.1rem;">
                <?php if ($avgRating): ?>
                    <span style="color: #D97706; letter-spacing: 2px;"><?= str_repeat('★', round($avgRating)) ?><?= str_repeat('☆', 5 - round($avgRating)) ?></span>
                    <strong style="margin-left: 8px;"><?= $avgRating ?></strong>
                    <span style="color: var(--clr-text-muted); font-size: 0.9rem;">(<?= $reviewCount ?> reviews)</span>
                <?php else: ?>
                    <span style="color: var(--clr-text-muted); font-style: italic;">No reviews yet — be the first!</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($trainer['specializations'])): ?>
                <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
                    <?php foreach (explode(',', $trainer['specializations']) as $spec): ?>
                        <span class="tag" style="background-color: rgba(37,99,235,0.1); color: var(--clr-primary); border: none; font-weight: 600;"><?= htmlspecialchars(trim($spec)) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($trainer['certifications'])): ?>
                <p style="margin-bottom: 15px; color: var(--clr-text-main);">
                    <span style="color: var(--clr-text-muted);">Certifications:</span> <strong><?= htmlspecialchars($trainer['certifications']) ?></strong>
                </p>
            <?php endif; ?>

            <?php if (!empty($trainer['bio'])): ?>
                <p style="line-height: 1.8; color: var(--clr-text-muted); max-width: 800px;"><?= nl2br(htmlspecialchars($trainer['bio'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
        
        <div style="grid-column: span 2;">
            
            <div class="card" style="padding: 30px; margin-bottom: 30px;">
                <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #F3F4F6;">Upcoming Classes</h3>
                
                <?php if (empty($trainerClasses)): ?>
                    <p style="color: var(--clr-text-muted); font-style: italic;">No upcoming classes scheduled.</p>
                <?php else: ?>
                    <div style="display: grid; gap: 15px; margin-bottom: 25px;">
                        <?php foreach ($trainerClasses as $c): ?>
                            <?php $spotsLeft = $c['capacity'] - $c['enrolled_count']; ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background-color: #F9FAFB; border-radius: 8px; border: 1px solid #E5E7EB;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                                        <span class="tag tag-<?= strtolower($c['type']) ?>"><?= htmlspecialchars($c['type']) ?></span>
                                        <strong style="font-size: 1.1rem;"><?= htmlspecialchars($c['name']) ?></strong>
                                    </div>
                                    <div style="color: var(--clr-text-muted); font-size: 0.9rem;">
                                        <?= date('D, d M Y', strtotime($c['scheduled_at'])) ?> • <?= date('H:i', strtotime($c['scheduled_at'])) ?> • <?= $c['duration_min'] ?> min
                                        <?php if (!empty($c['room'])): ?> • <strong><?= htmlspecialchars($c['room']) ?></strong><?php endif; ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span style="display: block; font-weight: bold; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; <?= $spotsLeft === 0 ? 'background-color: #FEE2E2; color: #DC2626;' : 'background-color: #D1FAE5; color: #059669;' ?>">
                                        <?= $spotsLeft === 0 ? 'Full' : $spotsLeft . ' spots left' ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="classes.php" class="btn btn-secondary" style="display: inline-block; text-decoration: none;">View Full Schedule</a>
                <?php endif; ?>
            </div>

            <div class="card" style="padding: 30px;">
                <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #F3F4F6;">Member Reviews</h3>
                
                <?php if (empty($reviews)): ?>
                    <p style="color: var(--clr-text-muted); font-style: italic;">No reviews yet.</p>
                <?php else: ?>
                    <div style="display: grid; gap: 20px;">
                        <?php foreach ($reviews as $rev): ?>
                            <div style="padding-bottom: 20px; border-bottom: 1px solid #F3F4F6;">
                                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 10px;">
                                    <strong style="font-size: 1.05rem;"><?= htmlspecialchars($rev['member_name']) ?></strong>
                                    <span style="color: var(--clr-text-muted); font-size: 0.85rem;"><?= date('d M Y', strtotime($rev['created_at'])) ?></span>
                                </div>
                                <div style="margin-bottom: 10px; font-size: 0.9rem;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span style="color: <?= $i <= $rev['rating'] ? '#F59E0B' : '#D1D5DB' ?>;">★</span>
                                    <?php endfor; ?>
                                </div>
                                <?php if (!empty($rev['comment'])): ?>
                                    <p style="color: var(--clr-text-main); line-height: 1.6; font-style: italic;">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="grid-column: span 1;">
            
            <?php if ($memberId): ?>
                
                <div class="card" style="padding: 30px; margin-bottom: 30px; background-color: var(--clr-primary); color: white;">
                    <h3 style="margin-bottom: 15px; color: white;">Book a Session</h3>
                    <p style="margin-bottom: 20px; font-size: 0.9rem; opacity: 0.9;">Request a 1-on-1 session. The trainer will confirm or suggest an alternative time.</p>
                    
                    <form method="POST">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action" value="book_session">
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-size: 0.9rem;">Date & Time</label>
                            <input type="datetime-local" name="requested_at" required min="<?= date('Y-m-d\TH:i') ?>" style="width: 100%; padding: 10px; border: none; border-radius: 4px;">
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-size: 0.9rem;">Duration</label>
                            <select name="duration_min" style="width: 100%; padding: 10px; border: none; border-radius: 4px;">
                                <option value="30">30 minutes</option>
                                <option value="60" selected>60 minutes</option>
                                <option value="90">90 minutes</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; font-size: 0.9rem;">Notes (optional)</label>
                            <textarea name="notes" rows="3" placeholder="Goals, injuries..." style="width: 100%; padding: 10px; border: none; border-radius: 4px; resize: vertical;"></textarea>
                        </div>
                        
                        <button type="submit" class="btn" style="width: 100%; background-color: white; color: var(--clr-primary); font-weight: bold;">Send Request</button>
                    </form>
                </div>

                <div class="card" style="padding: 30px;">
                    <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #F3F4F6;"><?= $myReview ? 'Edit Your Review' : 'Leave a Review' ?></h3>
                    
                    <form method="POST">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action" value="submit_review">
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Rating</label>
                            <div id="star-picker" class="star-picker">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= ($myReview && $myReview['rating'] >= $i) ? 'selected' : '' ?>" data-val="<?= $i ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="rating-input" value="<?= $myReview['rating'] ?? 0 ?>" required>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Comment <span style="font-weight: normal; color: var(--clr-text-muted); font-size: 0.85rem;">(optional)</span></label>
                            <textarea name="comment" rows="3" placeholder="Share your experience..." style="width: 100%; padding: 10px; border: 1px solid #D1D5DB; border-radius: 4px; resize: vertical;"><?= htmlspecialchars($myReview['comment'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <?= $myReview ? 'Update Review' : 'Submit Review' ?>
                        </button>
                    </form>
                </div>

            <?php elseif ($role === 'trainer' || $role === 'admin'): ?>
                <div class="card" style="padding: 30px; text-align: center; background-color: #F9FAFB;">
                    <p style="color: var(--clr-text-muted);">Reviews and session booking are available to members only.</p>
                </div>
            <?php else: ?>
                <div class="card" style="padding: 30px; text-align: center; background-color: #F9FAFB;">
                    <p style="color: var(--clr-text-muted);">
                        <a href="login.php" style="color: var(--clr-primary); font-weight: bold; text-decoration: none;">Log in</a> as a member to leave a review or book a session.
                    </p>
                </div>
            <?php endif; ?>

        </div>
    </div>

</main>


<?php include 'footer.php'; ?>