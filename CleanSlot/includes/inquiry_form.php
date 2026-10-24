<?php
// Shared form for Contact, Support, B2B and Franchise.
// Set before including: $old and $errors (from process_inquiry), $showCompany (true/false), $buttonText.
$showCompany = $showCompany ?? false;
$buttonText  = $buttonText ?? 'Send message';
?>
<form method="post" class="stack" novalidate>
    <?= csrf_field() ?>
    <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

    <div class="two">
        <label class="field"><span>Your name</span>
            <input name="name" value="<?= e($old['name']) ?>" required>
            <?php if (isset($errors['name'])): ?><small class="field-error"><?= e($errors['name']) ?></small><?php endif; ?>
        </label>
        <label class="field"><span>Email</span>
            <input type="email" name="email" value="<?= e($old['email']) ?>" required>
            <?php if (isset($errors['email'])): ?><small class="field-error"><?= e($errors['email']) ?></small><?php endif; ?>
        </label>
    </div>

    <div class="two">
        <label class="field"><span>Phone (optional)</span>
            <input type="tel" name="phone" value="<?= e($old['phone']) ?>">
            <?php if (isset($errors['phone'])): ?><small class="field-error"><?= e($errors['phone']) ?></small><?php endif; ?>
        </label>
        <?php if ($showCompany): ?>
            <label class="field"><span>Company name</span>
                <input name="company" value="<?= e($old['company']) ?>">
                <?php if (isset($errors['company'])): ?><small class="field-error"><?= e($errors['company']) ?></small><?php endif; ?>
            </label>
        <?php endif; ?>
    </div>

    <label class="field"><span>Message</span>
        <textarea name="message" rows="5" maxlength="2000" data-count required><?= e($old['message']) ?></textarea>
        <?php if (isset($errors['message'])): ?><small class="field-error"><?= e($errors['message']) ?></small><?php endif; ?>
        <small class="muted"><span id="count">0</span> / 2000</small>
    </label>

    <button class="btn btn-primary" type="submit"><?= e($buttonText) ?></button>
</form>
