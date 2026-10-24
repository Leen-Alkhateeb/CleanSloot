<?php // بانر أعلى الصفحات الداخلية. الصفحة تعرّف قبل ما تدرجه: $heroLabel, $heroTitle, $heroText, $heroPhoto ?>

<!-- صورة الخلفية بتجي من photo(): بتاخد مفتاح من PHOTOS (مثل 'rack') وعرض 1600 -->
<!-- إذا الصفحة ما عرّفت $heroPhoto بتستخدم صورة 'hero' الافتراضية -->
<section class="hero-banner" style="background-image: url('<?= e(photo($heroPhoto ?? 'hero', 1600)) ?>')">
    <div class="container">

        <!-- النص الصغير فوق العنوان -->
        <span class="hero-label"><?= e($heroLabel ?? '') ?></span>

        <!-- العنوان الرئيسي للصفحة -->
        <h1><?= e($heroTitle ?? '') ?></h1>

        <!-- جملة وصف تحت العنوان -->
        <p><?= e($heroText ?? '') ?></p>
    </div>
</section>