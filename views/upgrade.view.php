<?php include 'header.php'; ?>

<main class="page-container" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh; padding: 40px 20px;">
    
    <header style="text-align: center; margin-bottom: 50px;">
        <h2 style="margin-bottom: 10px;">CHOOSE YOUR PLAN</h2>
        <p style="color: var(--clr-text-muted);">Select the membership tier that fits your goals.</p>
    </header>

    <div style="display: flex; gap: 20px; justify-content: center; align-items: stretch; flex-wrap: wrap; max-width: 900px; width: 100%;">
        
        <div class="card" style="text-align: center; padding: 40px; flex: 1; min-width: 280px; max-width: 350px; display: flex; flex-direction: column;">
            
            <h3 style="margin-bottom: 10px;">Basic Plan</h3>
            <h4 style="font-size: 2.5rem; color: var(--clr-primary-dark); margin-bottom: 24px;">€14.99<span style="font-size: 1rem; color: var(--clr-text-muted);">/mo</span></h4>
            
            <div style="flex-grow: 1; text-align: left; margin-bottom: 32px; line-height: 2;">
                <p>✅ Access to all regular classes</p>
                <p>✅ Gym equipment access</p>
                <p>✅ Standard locker room</p>
                <p style="opacity: 0.5;">❌ Towel service included</p>
                <p style="opacity: 0.5;">❌ Free lounge vitamin water</p>
                <p style="opacity: 0.5;">❌ PT sessions & Assessments</p>
                <p style="opacity: 0.5;">❌ Weekend guest pass (Bring a Friend)</p>
            </div>

            <?php if (($userData['membership_tier'] ?? 'basic') === 'basic'): ?>
                <button type="button" class="btn btn-secondary" style="width: 100%; cursor: not-allowed; opacity: 0.6;" disabled>Current Plan</button>
            <?php else: ?>
                <form action="profile.php" method="POST">
                    <?= csrf_input() ?>
                    <input type="hidden" name="upgrade_membership" value="1">
                    <input type="hidden" name="tier" value="basic">
                    <button type="submit" class="btn btn-secondary" style="width: 100%;">Select Basic</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="card" style="text-align: center; padding: 50px 40px; flex: 1; min-width: 280px; max-width: 350px; display: flex; flex-direction: column; background-color: var(--clr-primary); color: white; box-shadow: 0 20px 25px -5px rgba(37,99,235,0.3);">
            
            <div style="background-color: white; color: var(--clr-primary); font-size: 0.8rem; font-weight: bold; text-transform: uppercase; padding: 6px 16px; border-radius: 20px; display: inline-block; margin: 0 auto 20px auto; letter-spacing: 1px;">
                Recommended
            </div>
            
            <h3 style="margin-bottom: 10px; color: white;">Premium Plan</h3>
            <h4 style="font-size: 2.5rem; color: white; margin-bottom: 24px;">€29.99<span style="font-size: 1rem; color: rgba(255,255,255,0.8);">/mo</span></h4>
            
            <div style="flex-grow: 1; text-align: left; margin-bottom: 32px; line-height: 2;">
                <p>✅ Access to all regular classes</p>
                <p>✅ Gym equipment access</p>
                <p>✅ Towel service (1 bath & 1 training towel/visit)</p>
                <p>✅ Free vitamin water in lounge area</p>
                <p>✅ 1 Free PT session/month</p>
                <p>✅ 1 Monthly body composition analysis</p>
                <p>✅ Bring a Friend (Free on weekends)</p>
            </div>

            <?php if (($userData['membership_tier'] ?? 'basic') === 'premium'): ?>
                <button type="button" class="btn" style="width: 100%; background-color: rgba(255,255,255,0.2); color: white; cursor: not-allowed;" disabled>Current Plan</button>
            <?php else: ?>
                <form action="profile.php" method="POST">
                    <?= csrf_input() ?>
                    <input type="hidden" name="upgrade_membership" value="1">
                    <input type="hidden" name="tier" value="premium">
                    <button type="submit" class="btn" style="width: 100%; background-color: white; color: var(--clr-primary); font-weight: bold;">Select Premium</button>
                </form>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php include 'footer.php'; ?>