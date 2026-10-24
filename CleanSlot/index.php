<?php
require_once __DIR__ . '/includes/functions.php';

// أول 4 خدمات ظاهرة من القاعدة (لقسم Popular services)
$featured = (new Item($db))->featured(4);

// خطوات "كيف يعمل": [العنوان, الوصف]
$steps    = [
    ['Choose', 'Pick your items from our services and add them to the cart.'],
    ['We collect', 'Choose a pickup time. We come to your door.'],
    ['Delivered fresh', 'Clean, pressed and packed, back to you within 48 hours.'],
];

// نقاط قسم "لماذا CleanSlot"
$why = ['Eco-friendly, toxin-free cleaning', 'Free pickup and delivery', 'Clear prices, pay on delivery', 'Careful handling of every item'];

$title  = 'CleanSlot | Eco-friendly dry cleaning & laundry in Jordan';
$active = 'home';   // بيلوّن رابط Home بالقائمة
require __DIR__ . '/includes/header.php';
?>

<main id="main">
    <!-- 1) البانر الكبير: صورة الخلفية من photo('hero') -->
    <section class="hero-banner hero-big" style="background-image: url('<?= e(photo('hero', 1600)) ?>')">
        <div class="container">
            <span class="hero-label">Eco-friendly cleaning</span>
            <h1>Clean clothes,<br>delivered to your door.</h1>
            <p>Toxin-free dry cleaning, ironing, shoe care and laundry. We pick up, clean and deliver.</p>
            <div class="actions">
                <a class="btn btn-primary btn-lg" href="<?= url('services.php') ?>">Order now</a>
                <a class="btn btn-outline btn-lg" href="<?= url('our-story.php') ?>">Our story</a>
            </div>
        </div>
    </section>

    <!-- 2) كيف يعمل: كرت لكل خطوة ($i + 1 = رقم الخطوة) -->
    <section class="section">
        <div class="container">
            <h2>How it works</h2>
            <div class="cards cards-3">
                <?php foreach ($steps as $i => [$name, $text]): ?>
                    <div class="card card-pad">
                        <span class="step-no"><?= $i + 1 ?></span>
                        <h3><?= e($name) ?></h3>
                        <p><?= e($text) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 3) الخدمات الشائعة: من القاعدة (الصورة عن طريق item_image) -->
    <section class="section section-tint">
        <div class="container">
            <div class="row-head">
                <h2>Popular services</h2>
                <a class="link" href="<?= url('services.php') ?>">View all &rarr;</a>
            </div>
            <div class="cards cards-4">
                <?php foreach ($featured as $item): ?>
                    <article class="card">
                        <img src="<?= e(item_image($item)) ?>" alt="<?= e($item['name']) ?>" loading="lazy" <?= img_fallback() ?>>
                        <div class="card-body">
                            <h3><?= e($item['name']) ?></h3>
                            <p><?= e($item['description']) ?></p>
                            <span class="price"><?= money($item['price']) ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 4) لماذا CleanSlot: قائمة مزايا + صورة -->
    <section class="section">
        <div class="container split">
            <div class="stack">
                <h2>Why <span class="accent">CleanSlot</span>?</h2>
                <ul class="checks">
                    <?php foreach ($why as $w): ?><li><?= e($w) ?></li><?php endforeach; ?>
                </ul>
                <div class="actions">
                    <a class="btn btn-primary" href="<?= url('b2b.php') ?>">Business solutions</a>
                    <a class="btn btn-outline" href="<?= url('franchise.php') ?>">Own a CleanSlot</a>
                </div>
            </div>
            <img class="split-photo" src="<?= e(photo('machines', 800)) ?>" alt="Washing machines" loading="lazy" <?= img_fallback() ?>>
        </div>
    </section>

    <!-- 5) دعوة للتسجيل: الزر بيتغير حسب إذا المستخدم مسجّل دخول أو لا -->
    <section class="section">
        <div class="container">
            <div class="cta">
                <div>
                    <h2>Ready for fresh, clean clothes?</h2>
                    <p>Create your account in a minute and place your first order today.</p>
                </div>
                <a class="btn btn-primary btn-lg" href="<?= url($user ? 'services.php' : 'login.php?tab=register') ?>"><?= $user ? 'Order now' : 'Get started' ?></a>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>