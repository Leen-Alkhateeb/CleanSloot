<?php
require_once __DIR__ . '/includes/functions.php';

// معالجة الفورم: بترجع القيم القديمة والأخطاء، وبتحفظ الرسالة بموضوع franchise
[$old, $errors] = process_inquiry('franchise', new Inquiry($db), 'franchise.php');

// أسباب اختيار CleanSlot: [مفتاح الصورة من PHOTOS, العنوان, النقاط]
$reasons = [
    ['van', 'Premium Quality', ['Eco-friendly, toxin-free cleaning with premium packaging.', 'Branded vans and stylish packaging materials.', 'Many cleaning services to suit every need.']],
    ['support', 'Customer Service Focus', ['Targeted training and support for customer satisfaction.', 'Help setting up cloud telephony.', 'Complaint system and call centre staff training.']],
    ['tech', 'World Class Technology', ['Complete IT systems and our own tech stack.', 'Mobile app for simple customer ordering.', 'Live reports on orders, sales and customers.']],
    ['team', 'Training', ['Training at CleanSlot headquarters.', 'Full documentation and standard operating procedures.', 'Ongoing visits and advice from our team.']],
];

// متغيرات الهيدر والبانر
$title     = 'Franchise | CleanSlot';
$active    = 'franchise';        // بيلوّن رابط Franchise بالقائمة
$heroLabel = 'Franchise';
$heroTitle = 'Ready to own a CleanSlot?';
$heroText  = 'Unlock the door to financial freedom with a franchise in the growing cleaning services industry.';
$heroPhoto = 'franchise';        // مفتاح صورة الخلفية من PHOTOS
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page_hero.php';   // البانر العلوي
?>

<main id="main">
    <!-- قسم: لماذا CleanSlot (4 كروت) -->
    <section class="section">
        <div class="container">
            <h2 class="center-title">Why <span class="accent">CleanSlot</span>?</h2>
            <div class="cards cards-2">
                <!-- كرت لكل سبب: [$key = مفتاح الصورة, $name = العنوان, $points = النقاط] -->
                <?php foreach ($reasons as [$key, $name, $points]): ?>
                    <article class="card">
                        <!-- الصورة من photo() مع صورة بديلة إذا انكسرت -->
                        <img src="<?= e(photo($key, 700)) ?>" alt="" loading="lazy" <?= img_fallback() ?>>
                        <div class="card-body">
                            <h3 class="accent"><?= e($name) ?></h3>
                            <ul class="dashes">
                                <!-- حلقة داخلية: نقطة لكل عنصر -->
                                <?php foreach ($points as $p): ?><li><?= e($p) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- قسم: فورم التقديم -->
    <section class="section section-tint">
        <div class="container narrow">
            <div class="panel">
                <h2>Apply for a franchise</h2>
                <p class="muted">Tell us about yourself and your city. Our franchise team will call you.</p>
                <!-- الفورم المشترك: بدون حقل شركة، ونص الزر Send application -->
                <?php $buttonText = 'Send application'; require __DIR__ . '/includes/inquiry_form.php'; ?>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>