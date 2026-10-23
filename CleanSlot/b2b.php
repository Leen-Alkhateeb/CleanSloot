<?php
require_once __DIR__ . '/includes/functions.php';

[$old, $errors] = process_inquiry('b2b', new Inquiry($db), 'b2b.php');

$sectors = [
    ['Hotels & Guesthouses', 'Bed linen, towels and uniforms collected and returned on your schedule.'],
    ['Restaurants & Cafes', 'Aprons, tablecloths and staff uniforms washed, pressed and ready every morning.'],
    ['Clinics & Salons', 'Hygienic washing for towels, gowns and work wear, with careful handling.'],
    ['Offices & Companies', 'Staff uniforms and corporate wear cleaned and delivered on hangers.'],
];
$benefits = ['Fixed monthly pricing', 'Scheduled pickups and deliveries', 'One invoice for everything', 'A dedicated account manager'];

$title     = 'B2B Care | CleanSlot';
$active    = 'b2b';
$heroLabel = 'CleanSlot for business';
$heroTitle = 'B2B Care';
$heroText  = 'Reliable, eco-friendly laundry and dry cleaning for hotels, restaurants, clinics and companies.';
$heroPhoto = 'b2b';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page_hero.php';
?>

<main id="main">
    <section class="section">
        <div class="container">
            <h2>Who we work with</h2>
            <div class="cards cards-4">
                <?php foreach ($sectors as [$name, $text]): ?>
                    <div class="card card-pad">
                        <h3><?= e($name) ?></h3>
                        <p><?= e($text) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section section-tint">
        <div class="container contact-grid">
            <div class="stack">
                <h2>Why companies choose us</h2>
                <ul class="checks">
                    <?php foreach ($benefits as $b): ?><li><?= e($b) ?></li><?php endforeach; ?>
                </ul>
                <p>Tell us what you need and we will send a clear offer within one working day.</p>
            </div>

            <div class="panel">
                <h2>Request an offer</h2>
                <?php $showCompany = true; $buttonText = 'Request offer'; require __DIR__ . '/includes/inquiry_form.php'; ?>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
