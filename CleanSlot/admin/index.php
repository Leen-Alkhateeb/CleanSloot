<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$orders = new Order($db);

// إذا الأدمن غيّر حالة طلب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // تأكد إن الفورم جاي من موقعنا
    csrf_check();

    // رقم الطلب (إذا مش موجود يصير 0)
    $id = 0;
    if (isset($_POST['id'])) {
        $id = (int) $_POST['id'];
    }

    // الحالة الجديدة (إذا مش موجودة تصير فاضية)
    $status = '';
    if (isset($_POST['status'])) {
        $status = $_POST['status'];
    }

    // احفظ الحالة: true إذا نجح， false إذا الحالة غلط
    $done = $orders->setStatus($id, $status);

    if ($done) {
        flash('Order status updated.', 'success');//رسالة مؤقتة بتظهر مرة وحدة بس، وبعدين بتختفي.
    } else {
        flash('Invalid status.', 'error');
    }

    // ارجع للصفحة عشان ما يتكرر التعديل عند Refresh
    redirect(url('admin/index.php'));
}

// جيب الإحصائيات وكل الطلبات
$stats = $orders->stats();
$list  = $orders->all();

// جيب عدد الزبائن
$users = new User($db);
$customersCount = $users->countCustomers();

// الكروت الأربعة: [عنوان, قيمة]
$cards = [
    ['Total orders', $stats['orders']],
    ['Pending', $stats['pending']],
    ['Customers', $customersCount],
    ['Revenue (completed)', money($stats['revenue'])],
];


$adminTab = 'dashboard';
require __DIR__ . '/../includes/header.php';
?>

<main id="main" class="section">
    <div class="container stack-lg">

        <h1 class="h2">Admin dashboard</h1>
        <p class="muted">Welcome back, <?= e($user['name']) ?>.</p>

        <?php require __DIR__ . '/../includes/admin_tabs.php'; ?>

        <!-- كروت الإحصائيات -->
        <div class="stats">
            <?php foreach ($cards as $card): ?>
                <div class="stat">
                    <span><?= e($card[0]) ?></span>
                    <strong><?= e($card[1]) ?></strong>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="panel">
            <h2>Orders</h2>

            <!-- table-wrap: تمرير أفقي على الموبايل -->
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>Customer</th><th>Items</th><th>Pickup</th>
                            <th>Address</th><th>Total</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($list as $o): ?>
                            <tr>
                                <td><?= $o['id'] ?></td>

                                <td>
                                    <?= e($o['customer']) ?><br>  <!-- اسم الزبون + رقم الهاتف -->
                                    <small class="muted"><?= e($o['phone']) ?></small>
                                </td>

                                <td><?= e($o['items']) ?></td> <!-- عدد الأصناف + أسماء الأصناف -->

                                <td>
                                    <?= e($o['pickup_date']) ?><br> <!-- تاريخ الاستلام + وقت الاستلام -->
                                    <small class="muted"><?= e($o['time_slot']) ?></small>
                                </td>

                                <!-- العنوان + ملاحظات الزبون إذا موجودة -->
                                <td>
                                    <?= e($o['address']) ?>
                                    <?php if ($o['notes'] !== ''): ?>
                                        <br><small class="muted"><?= e($o['notes']) ?></small>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= money($o['total']) ?><br> <!-- المبلغ الإجمالي + طريقة الدفع -->
                                    <small class="muted"><?= e($o['payment_method']) ?></small>
                                </td>

                                <!-- تغيير الحالة: بيتبعت تلقائياً عند الاختيار -->
                                <td>
                                    <form method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">

                                        <select name="status" class="select-sm" onchange="this.form.submit()"> <!-- فورم بيتبعت لما يختار الأدمن حالة جديدة -->
                                            <?php foreach (ORDER_STATUSES as $s): ?>
                                                <?php
                                                $selected = '';
                                                if ($o['status'] === $s) {
                                                    $selected = 'selected';
                                                }
                                                ?>
                                                <option value="<?= $s ?>" <?= $selected ?>> <!-- قيمة الحالة + إذا هي الحالة الحالية تكون مختارة -->
                                                    <?= ucfirst($s) ?> <!-- ucfirst: أول حرف كبير -->
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!$list): ?>
                <p class="empty">No orders yet.</p> <!-- إذا ما في أي طلبات -->
            <?php endif; ?>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>