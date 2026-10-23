<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$orders = new Order($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $done = $orders->cancel((int) ($_POST['cancel'] ?? 0), $user['id']);
    flash($done ? 'Order cancelled.' : 'Only pending orders can be cancelled.', $done ? 'success' : 'error');
    redirect(url('orders.php'));
}

$list   = $orders->byUser($user['id']);
$title  = 'My orders | CleanSlot';
$active = '';
require __DIR__ . '/includes/header.php';
?>

<main id="main" class="section">
    <div class="container stack-lg">
        <div>
            <h1 class="h2">My orders</h1>
            <p class="muted">Hello <?= e($user['name']) ?>, here are all your orders.</p>
        </div>

        <?php foreach ($list as $o): ?>
            <article class="panel order">
                <div class="order-top">
                    <strong>Order #<?= (int) $o['id'] ?></strong>
                    <?= status_badge($o['status']) ?>
                </div>
                <p><?= e($o['items']) ?></p>
                <p class="muted"><?= e($o['pickup_date']) ?> &middot; <?= e($o['time_slot']) ?> &middot; <?= e($o['address']) ?></p>
                <div class="order-top">
                    <span class="price"><?= money($o['total']) ?> <small class="muted">(<?= e($o['payment_method']) ?>)</small></span>
                    <?php if ($o['status'] === 'pending'): ?>
                        <form method="post" onsubmit="return confirm('Cancel this order?')">
                            <?= csrf_field() ?>
                            <button class="btn btn-danger btn-sm" name="cancel" value="<?= (int) $o['id'] ?>">Cancel order</button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (!$list): ?>
            <p class="empty">No orders yet. <a class="link" href="<?= url('services.php') ?>">Start with our services</a>.</p>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
