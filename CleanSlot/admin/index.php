<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$orders = new Order($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $done = $orders->setStatus((int) ($_POST['id'] ?? 0), $_POST['status'] ?? '');
    flash($done ? 'Order status updated.' : 'Invalid status.', $done ? 'success' : 'error');
    redirect(url('admin/index.php'));
}

$stats = $orders->stats();
$list  = $orders->all();
$cards = [
    ['Total orders', $stats['orders']],
    ['Pending', $stats['pending']],
    ['Customers', (new User($db))->countCustomers()],
    ['Revenue (completed)', money($stats['revenue'])],
];

$title    = 'Dashboard | CleanSlot';
$active   = '';
$adminTab = 'dashboard';
require __DIR__ . '/../includes/header.php';
?>

<main id="main" class="section">
    <div class="container stack-lg">
        <div>
            <h1 class="h2">Admin dashboard</h1>
            <p class="muted">Welcome back, <?= e($user['name']) ?>.</p>
        </div>

        <?php require __DIR__ . '/../includes/admin_tabs.php'; ?>

        <div class="stats">
            <?php foreach ($cards as [$label, $value]): ?>
                <div class="stat"><span><?= e($label) ?></span><strong><?= e($value) ?></strong></div>
            <?php endforeach; ?>
        </div>

        <div class="panel">
            <h2>Orders</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>#</th><th>Customer</th><th>Items</th><th>Pickup</th><th>Address</th><th>Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($list as $o): ?>
                            <tr>
                                <td><?= (int) $o['id'] ?></td>
                                <td><?= e($o['customer']) ?><br><small class="muted"><?= e($o['phone']) ?></small></td>
                                <td><?= e($o['items']) ?></td>
                                <td><?= e($o['pickup_date']) ?><br><small class="muted"><?= e($o['time_slot']) ?></small></td>
                                <td><?= e($o['address']) ?><?php if ($o['notes'] !== ''): ?><br><small class="muted"><?= e($o['notes']) ?></small><?php endif; ?></td>
                                <td><?= money($o['total']) ?><br><small class="muted"><?= e($o['payment_method']) ?></small></td>
                                <td>
                                    <form method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                                        <select name="status" class="select-sm" onchange="this.form.submit()">
                                            <?php foreach (ORDER_STATUSES as $s): ?>
                                                <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!$list): ?><p class="empty">No orders yet.</p><?php endif; ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
