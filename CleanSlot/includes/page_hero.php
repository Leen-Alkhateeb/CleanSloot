<?php // Banner at the top of inner pages. Set $heroLabel, $heroTitle, $heroText, $heroPhoto before including. ?>
<section class="hero-banner" style="background-image: url('<?= e(photo($heroPhoto ?? 'hero', 1600)) ?>')">
    <div class="container">
        <span class="hero-label"><?= e($heroLabel ?? '') ?></span>
        <h1><?= e($heroTitle ?? '') ?></h1>
        <p><?= e($heroText ?? '') ?></p>
    </div>
</section>
