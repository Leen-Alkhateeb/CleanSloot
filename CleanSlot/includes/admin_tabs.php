<?php // قبل ما تدرج الملف، كل صفحة لازم تعرّف $adminTab ?>
<nav class="admin-tabs">

    <!-- الطلبات: active إذا الصفحة الحالية هي الداشبورد -->
    <a href="<?= url('admin/index.php') ?>"
       class="<?php if ($adminTab === 'dashboard') echo 'active'; ?>">Orders</a>

    <!-- الخدمات والمتجر -->
    <a href="<?= url('admin/items.php') ?>"
       class="<?php if ($adminTab === 'items') echo 'active'; ?>">Services &amp; Shop</a>

    <!-- الرسائل -->
    <a href="<?= url('admin/inquiries.php') ?>"
       class="<?php if ($adminTab === 'inquiries') echo 'active'; ?>">Messages</a>

</nav>