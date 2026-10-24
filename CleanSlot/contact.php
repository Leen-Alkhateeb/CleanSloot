<?php
require_once __DIR__ . '/includes/functions.php';

// معالجة الفورم: بترجع القيم القديمة والأخطاء، وبتحفظ الرسالة بموضوع contact
[$old, $errors] = process_inquiry('contact', new Inquiry($db), 'contact.php');

// متغيرات الهيدر والبانر
$title     = 'Contact | CleanSlot';
$active    = 'contact';          // بيلوّن رابط Contact بالقائمة
$heroLabel = 'Get in touch';
$heroTitle = 'Contact us';
$heroText  = 'Questions, feedback or a special request? Send us a message and we will reply fast.';
$heroPhoto = 'contact';          // مفتاح الصورة من PHOTOS
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page_hero.php';   // البانر العلوي
?>

<main id="main" class="section">
    <div class="container contact-grid">

        <!-- معلومات التواصل: كلها من الثابت CONTACT بـ functions.php (غيّرها من هناك) -->
        <div class="stack">
            <div class="info-card"><h4>Phone</h4><a href="tel:<?= e(CONTACT['phone_raw']) ?>"><?= e(CONTACT['phone']) ?></a></div>
            <div class="info-card"><h4>Email</h4><a href="mailto:<?= e(CONTACT['email']) ?>"><?= e(CONTACT['email']) ?></a></div>
            <div class="info-card"><h4>Address</h4><p><?= e(CONTACT['address']) ?></p></div>
            <div class="info-card"><h4>Working hours</h4><p><?= e(CONTACT['hours']) ?></p></div>
        </div>

        <!-- الفورم المشترك (بدون حقل الشركة لأن showCompany مش معرّف) -->
        <div class="panel">
            <h2>Send us a message</h2>
            <?php $buttonText = 'Send message'; require __DIR__ . '/includes/inquiry_form.php'; ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>