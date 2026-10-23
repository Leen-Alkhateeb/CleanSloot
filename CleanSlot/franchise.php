<?php
require_once __DIR__ . '/includes/functions.php';

[$old, $errors] = process_inquiry('franchise', new Inquiry($db), 'franchise.php');

// [photo key, title, points]
$reasons = [
    ['van', 'Premium Quality', ['Eco-friendly, toxin-free cleaning with premium packaging.', 'Branded vans and stylish packaging materials.', 'Many cleaning services to suit every need.']],
    ['support', 'Customer Service Focus', ['Targeted training and support for customer satisfaction.', 'Help setting up cloud telephony.', 'Complaint system and call centre staff training.']],
    ['tech', 'World Class Technology', ['Complete IT systems and our own tech stack.', 'Mobile app for simple customer ordering.', 'Live reports on orders, sales and customers.']],
    ['team', 'Training', ['Training at CleanSlot headquarters.', 'Full documentation and standard operating procedures.', 'Ongoing visits and advice from our team.']],
];

$title     = 'Franchise | CleanSlot';
$active    = 'franchise';
$heroLabel = 'Franchise';
$heroTitle = 'Ready to own a CleanSlot?';
$heroText  = 'Unlock the door to financial freedom with a franchise in the growing cleaning services industry.';
$heroPhoto = 'franchise';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page_hero.php';
?>

<main id="main">
    <section class="section">
        <div class="container">
            <h2 class="center-title">Why <span class="accent">CleanSlot</span>?</h2>
            <div class="cards cards-2">
                <?php foreach ($reasons as [$key, $name, $points]): ?>
                    <article class="card">
                        <img src="<?= e(photo($key, 700)) ?>" alt="" loading="lazy" <?= img_fallback() ?>>
                        <div class="card-body">
                            <h3 class="accent"><?= e($name) ?></h3>
                            <ul class="dashes">
                                <?php foreach ($points as $p): ?><li><?= e($p) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section section-tint">
        <div class="container narrow">
            <div class="panel">
                <h2>Apply for a franchise</h2>
                <p class="muted">Tell us about yourself and your city. Our franchise team will call you.</p>
                <?php $buttonText = 'Send application'; require __DIR__ . '/includes/inquiry_form.php'; ?>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
