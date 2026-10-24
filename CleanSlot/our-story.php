<?php
require_once __DIR__ . '/includes/functions.php';

// بيانات الصفحة
$title     = 'Our Story | CleanSlot';
$active    = 'story';              // بيلوّن رابط Our Story بالقائمة
$heroLabel = 'About CleanSlot';
$heroTitle = 'Our Story';
$heroText  = 'We started CleanSlot to make clean clothes simple, eco-friendly and always on time.';
$heroPhoto = 'story';              // مفتاح الصورة من PHOTOS
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page_hero.php';   // البانر العلوي
?>

<main id="main">

    <!-- 1) القصة: صورة + نص -->
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

    <!-- 2) أرقام عن الشركة -->
    <section class="section section-tint">
        <div class="container">
            <div class="stats">
                <div class="stat"><strong>2019</strong><span>Founded in Amman</span></div>
                <div class="stat"><strong>50,000+</strong><span>Items cleaned</span></div>
                <div class="stat"><strong>4,000+</strong><span>Happy customers</span></div>
                <div class="stat"><strong>48h</strong><span>Typical turnaround</span></div>
            </div>
        </div>
    </section>

    <!-- 3) قيمنا -->
    <section class="section">
        <div class="container">
            <h2>What we believe in</h2>
            <div class="cards cards-4">
                <div class="card card-pad"><h3>Eco-friendly</h3><p>Gentle, toxin-free products that are safe for your family and kind to the planet.</p></div>
                <div class="card card-pad"><h3>Care for every item</h3><p>Each piece is checked, treated and finished by hand, never rushed.</p></div>
                <div class="card card-pad"><h3>Convenience</h3><p>We pick up and deliver, so you get your time back.</p></div>
                <div class="card card-pad"><h3>Honest prices</h3><p>Clear prices per item. You know the total before you order.</p></div>
            </div>
        </div>
    </section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>