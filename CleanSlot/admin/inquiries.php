<?php

require_once __DIR__ . '/../includes/functions.php';

// للأدمن فقط
require_admin();


$inquiries = new Inquiry($db);

// ---------- حذف رسالة ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();   // تأكد إن الفورم جاي من موقعنا

    // رقم الرسالة (إذا مش موجود يصير 0)
    $id = 0;
    if (isset($_POST['delete'])) {
        $id = (int) $_POST['delete'];
    }

    $inquiries->delete($id);
    flash('Message deleted.');                 // رسالة نجاح بتظهر بعد الـ redirect
    redirect(url('admin/inquiries.php'));      // ارجع للصفحة عشان ما يتكرر الحذف عند Refresh
}

// ---------- الفلترة حسب الموضوع ----------
// فاضي = All (كل الرسائل)
// تنبيه: القيمة ثابتة هون، فأزرار الفلترة ما بتشتغل (راجع الملاحظة تحت)
$topic = '';

// آخر 200 رسالة (مفلترة حسب $topic إذا مش فاضي)
$list = $inquiries->latest(200, $topic);

// متغيرات الهيدر وشريط التبويبات
$title    = 'Messages | CleanSlot';
$active   = '';
$adminTab = 'inquiries';   // بيلوّن تبويب Messages
require __DIR__ . '/../includes/header.php';
?>

<main id="main" class="section">
    <div class="container stack-lg">

        <h1 class="h2">Messages</h1>

        <!-- تبويبات الأدمن -->
        <?php require __DIR__ . '/../includes/admin_tabs.php'; ?>

        <!-- أزرار الفلترة: الزر المختار بياخد كلاس active -->
        <div class="chips">
            <a href="<?= url('admin/inquiries.php') ?>" class="chip <?php if ($topic === '') echo 'active'; ?>">All</a>

            <!-- زر لكل موضوع من Inquiry::TOPICS (contact, support, b2b, franchise) -->
            <?php foreach (Inquiry::TOPICS as $key => $label): ?>
                <a href="<?= url('admin/inquiries.php?topic=' . $key) ?>" class="chip <?php if ($topic === $key) echo 'active'; ?>">
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- كرت لكل رسالة ($m = الرسالة الحالية) -->
        <?php foreach ($list as $m): ?>
            <article class="panel message">

                <div class="order-top">
                    <div>
                        <!-- اسم الموضوع للعرض (مثلاً b2b ← B2B Care) -->
                        <span class="badge badge-confirmed"><?= e(Inquiry::TOPICS[$m['topic']]) ?></span>

                        <!-- اسم المرسل (e() لحماية الموقع من XSS) -->
                        <strong><?= e($m['name']) ?></strong>

                        <!-- الشركة تظهر فقط إذا موجودة -->
                        <?php if ($m['company'] !== ''): ?>
                            <span class="muted">(<?= e($m['company']) ?>)</span>
                        <?php endif; ?>
                    </div>

                    <!-- تاريخ ووقت الإرسال -->
                    <span class="muted"><?= e($m['created_at']) ?></span>
                </div>

                <p class="muted">
                    <!-- الإيميل: بيفتح برنامج البريد عند الضغط -->
                    <a class="link" href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>

                    <!-- الهاتف يظهر فقط إذا موجود، وبيتصل من الموبايل -->
                    <?php if ($m['phone'] !== ''): ?>
                        &middot;
                        <a class="link" href="tel:<?= e($m['phone']) ?>"><?= e($m['phone']) ?></a>
                    <?php endif; ?>
                </p>

                <!-- نص الرسالة: nl2br بتحوّل الأسطر الجديدة لـ <br> (بعد e() للأمان) -->
                <p><?= nl2br(e($m['message'])) ?></p>

                <!-- زر الحذف مع نافذة تأكيد، والقيمة = رقم الرسالة -->
                <form method="post" onsubmit="return confirm('Delete this message?')">
                    <?= csrf_field() ?>
                    <button class="btn btn-danger btn-sm" name="delete" value="<?= (int) $m['id'] ?>">Delete</button>
                </form>

            </article>
        <?php endforeach; ?>

        <!-- إذا ما في رسائل -->
        <?php if (!$list): ?>
            <p class="empty">No messages here yet.</p>
        <?php endif; ?>

    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>