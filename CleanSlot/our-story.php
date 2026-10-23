<?php
require_once __DIR__ . '/includes/functions.php';

$values = [
    ['Eco-friendly', 'Gentle, toxin-free products that are safe for your family and kind to the planet.'],
    ['Care for every item', 'Each piece is checked, treated and finished by hand, never rushed.'],
    ['Convenience', 'We pick up and deliver, so you get your time back.'],
    ['Honest prices', 'Clear prices per item. You know the total before you order.'],
];
$numbers = [['2019', 'Founded in Amman'], ['50,000+', 'Items cleaned'], ['4,000+', 'Happy customers'], ['48h', 'Typical turnaround']];

$title     = 'Our Story | CleanSlot';
$active    = 'story';
$heroLabel = 'About CleanSlot';
$heroTitle = 'Our Story';
$heroText  = 'We started CleanSlot to make clean clothes simple, eco-friendly and always on time.';
$heroPhoto = 'story';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page_hero.php';
?>

<main id="main">
    <section class="section">
        <div class="container split">
            <img class="split-photo" src="<?= e(photo('story', 800)) ?>" alt="Washing machines at a CleanSlot store" loading="lazy" <?= img_fallback() ?>>
            <div class="stack">
                <h2>Clean clothes, without the hassle</h2>
                <p>CleanSlot began with a simple idea: laundry should not take over your week. We built a service where you choose what you need, pick a time, and we take care of the rest.</p>
                <p>Today our team cleans thousands of items every month, from everyday shirts to wedding dresses and carpets, using eco-friendly products and modern machines.</p>
                <div class="actions">
                    <a class="btn btn-primary" href="<?= url('services.php') ?>">See our services</a>
                    <a class="btn btn-outline" href="<?= url('contact.php') ?>">Talk to us</a>
                </div>
            </div>
        </div>
    </section>

    <section class="section section-tint">
        <div class="container">
            <div class="stats">
                <?php foreach ($numbers as [$value, $label]): ?>
                    <div class="stat"><strong><?= e($value) ?></strong><span><?= e($label) ?></span></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2>What we believe in</h2>
            <div class="cards cards-4">
                <?php foreach ($values as [$name, $text]): ?>
                    <div class="card card-pad"><h3><?= e($name) ?></h3><p><?= e($text) ?></p></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
