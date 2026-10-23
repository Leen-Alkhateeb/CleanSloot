<?php // Menu shared by the admin pages. Set $adminTab = 'dashboard' | 'items' | 'inquiries' first. ?>
<nav class="admin-tabs">
    <a href="<?= url('admin/index.php') ?>" class="<?= $adminTab === 'dashboard' ? 'active' : '' ?>">Orders</a>
    <a href="<?= url('admin/items.php') ?>" class="<?= $adminTab === 'items' ? 'active' : '' ?>">Services &amp; Shop</a>
    <a href="<?= url('admin/inquiries.php') ?>" class="<?= $adminTab === 'inquiries' ? 'active' : '' ?>">Messages</a>
</nav>
