<?php
require_once __DIR__ . '/includes/functions.php';

[$old, $errors] = process_inquiry('support', new Inquiry($db), 'support.php');

$faq = [
    ['How does CleanSlot work?', 'Choose your items, add them to the cart, pick a pickup date and time, and we collect them from your door. We clean them and bring them back.'],
    ['How long does it take?', 'Most orders come back within 48 hours. Carpets, curtains and bedding can take up to 3 days.'],
    ['How do I pay?', 'You pay when we deliver, in cash or by card. Nothing is charged online.'],
    ['Can I cancel my order?', 'Yes. Open My orders and press Cancel while the order is still pending. After we confirm it, please call us.'],
    ['What if a stain does not come out?', 'We treat every stain with care, but some are permanent. Tell us about stains in the order notes and we will let you know before we start.'],
    ['Which areas do you cover?', 'We cover Amman and nearby areas. Contact us if you are not sure about your area.'],
];

$title     = 'Support | CleanSlot';
$active    = 'support';
$heroLabel = 'We are here to help';
$heroTitle = 'Support';
$heroText  = 'Quick answers to common questions, or send a message to our team.';
$heroPhoto = 'support';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page_hero.php';
?>

<main id="main" class="section">
    <div class="container contact-grid">
        <div class="stack">
            <h2>Frequently asked questions</h2>
            <?php foreach ($faq as [$question, $answer]): ?>
                <details class="faq">
                    <summary><?= e($question) ?></summary>
                    <p><?= e($answer) ?></p>
                </details>
            <?php endforeach; ?>
        </div>

        <div class="panel">
            <h2>Still need help?</h2>
            <p class="muted">Tell us what happened and include your order number if you have one.</p>
            <?php $buttonText = 'Send to support'; require __DIR__ . '/includes/inquiry_form.php'; ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
