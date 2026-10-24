<?php
// فورم مشترك لصفحات: اتصل بنا، الدعم، B2B، الفرنشايز
// الصفحة لازم تعرّف قبل ما تدرجه: $old و $errors (من process_inquiry)
// واختيارياً: $showCompany (إظهار حقل الشركة) و $buttonText (نص الزر)

// القيم الافتراضية إذا الصفحة ما عرّفتها
if (!isset($showCompany)) {
    $showCompany = false;
}
if (!isset($buttonText)) {
    $buttonText = 'Send message';
}
?>
<form method="post" class="stack" novalidate>

    <!-- توكن الحماية من CSRF -->
    <?= csrf_field() ?>

    <!-- حقل فخ للبوتات: مخفي بالـ CSS، الإنسان ما بيشوفه فما بيعبّيه، والبوت بيعبّيه فنرفض الرسالة -->
    <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

    <!-- الصف الأول: الاسم والإيميل -->
    <div class="two">
        <label class="field"><span>Your name</span>
            <!-- e() بتنظف القيمة، والقيمة القديمة بترجع عشان المستخدم ما يكتب من جديد عند الخطأ -->
            <input name="name" value="<?= e($old['name']) ?>" required>
            <?php if (isset($errors['name'])): ?>
                <small class="field-error"><?= e($errors['name']) ?></small>
            <?php endif; ?>
        </label>

        <label class="field"><span>Email</span>
            <input type="email" name="email" value="<?= e($old['email']) ?>" required>
            <?php if (isset($errors['email'])): ?>
                <small class="field-error"><?= e($errors['email']) ?></small>
            <?php endif; ?>
        </label>
    </div>

    <!-- الصف الثاني: الهاتف (اختياري) والشركة (بس إذا مفعّلة) -->
    <div class="two">
        <label class="field"><span>Phone (optional)</span>
            <input type="tel" name="phone" value="<?= e($old['phone']) ?>">
            <?php if (isset($errors['phone'])): ?>
                <small class="field-error"><?= e($errors['phone']) ?></small>
            <?php endif; ?>
        </label>

        <!-- حقل الشركة بيظهر بصفحتي B2B والفرنشايز مثلاً -->
        <?php if ($showCompany): ?>
            <label class="field"><span>Company name</span>
                <input name="company" value="<?= e($old['company']) ?>">
                <?php if (isset($errors['company'])): ?>
                    <small class="field-error"><?= e($errors['company']) ?></small>
                <?php endif; ?>
            </label>
        <?php endif; ?>
    </div>

    <!-- نص الرسالة (أقصى 2000 حرف) -->
    <label class="field"><span>Message</span>
        <!-- data-count: علامة بيقرأها app.js ليشغّل عدّاد الأحرف -->
        <textarea name="message" rows="5" maxlength="2000" data-count required><?= e($old['message']) ?></textarea>
        <?php if (isset($errors['message'])): ?>
            <small class="field-error"><?= e($errors['message']) ?></small>
        <?php endif; ?>

        <!-- العدّاد: app.js بيحدّث الرقم داخل id="count" -->
        <small class="muted"><span id="count">0</span> / 2000</small>
    </label>

    <!-- زر الإرسال بنص متغيّر -->
    <button class="btn btn-primary" type="submit"><?= e($buttonText) ?></button>
</form>