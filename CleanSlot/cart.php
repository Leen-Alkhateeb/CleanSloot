<?php
require_once __DIR__ . '/includes/functions.php';

$items  = new Item($db);
$orders = new Order($db);
$error  = '';

// نقبل بس روابط من داخل الموقع كوجهة "ارجع" (حماية من التحويل لمواقع خارجية)
function safe_back($path)
{
    return preg_match('#^/(?!/)[^\\\\]*$#', (string) $path) ? $path : url('services.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // 1) زر "Add to Cart" أو "Order Now" من صفحات الخدمات والمتجر
    if (isset($_POST['go'])) {
        $item = $items->find($_POST['item'] ?? 0);
        $qty  = max(1, min(20, (int) ($_POST['qty'] ?? 1)));   // الكمية بين 1 و20

        // أضف فقط إذا العنصر موجود وظاهر
        if ($item && $item['active']) {
            // السلة بالجلسة: رقم العنصر => الكمية (بتتجمع مع الكمية القديمة، حد أقصى 20)
            $_SESSION['cart'][$item['id']] = min(20, ($_SESSION['cart'][$item['id']] ?? 0) + $qty);
            flash($item['name'] . ' was added to your cart.');
        }
        // now = روح للسلة مباشرة، add = ارجع للصفحة السابقة
        redirect($_POST['go'] === 'now' ? url('cart.php') : safe_back($_POST['back'] ?? ''));
    }

    // 2) حذف عنصر أو تعديل الكميات
    if (isset($_POST['remove'])) {
        unset($_SESSION['cart'][(int) $_POST['remove']]);
        redirect(url('cart.php'));
    }
    if (($_POST['action'] ?? '') === 'update') {
        foreach ($_POST['qty'] ?? [] as $id => $qty) {
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id] = max(1, min(20, (int) $qty));
            }
        }
        flash('Cart updated.');
        redirect(url('cart.php'));
    }

    // 3) تثبيت الطلب (لازم المستخدم يكون مسجّل دخول)
    if (($_POST['action'] ?? '') === 'checkout') {
        require_login();
        try {
            // Order::create بتفحص كل شي وبترمي DomainException إذا في خطأ
            $orders->create(
                $user['id'], cart_rows($items),
                $_POST['date'] ?? '', $_POST['slot'] ?? '',
                trim($_POST['address'] ?? ''), trim($_POST['notes'] ?? ''), $_POST['method'] ?? ''
            );
            unset($_SESSION['cart']);   // فضّي السلة بعد نجاح الطلب
            flash('Thank you! Your order was placed. We will contact you to confirm the pickup.');
            redirect(url('orders.php'));
        } catch (DomainException $ex) {
            $error = $ex->getMessage();   // رسالة الخطأ بتظهر فوق الفورم
        }
    }
}

// بيانات العرض: عناصر السلة ومجموعها
$rows  = cart_rows($items);
$total = array_sum(array_column($rows, 'line'));

$title  = 'Cart | CleanSlot';
$active = '';
require __DIR__ . '/includes/header.php';
?>

<main id="main" class="section">
    <div class="container stack-lg">
        <h1 class="h2">Your cart</h1>

        <?php if (!$rows): ?>
            <!-- السلة فاضية -->
            <p class="empty">Your cart is empty. <a class="link" href="<?= url('services.php') ?>">Browse services</a> or <a class="link" href="<?= url('shop.php') ?>">visit the shop</a>.</p>
        <?php else: ?>
            <div class="cart-grid">

                <!-- العمود 1: جدول السلة (تعديل الكميات وحذف) -->
                <form method="post" class="panel">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <td><strong><?= e($r['name']) ?></strong><br><small class="muted"><?= e($r['type']) ?></small></td>
                                        <td><?= money($r['price']) ?></td>
                                        <!-- qty[رقم العنصر]: مصفوفة بتوصل لـ $_POST['qty'] -->
                                        <td><input class="qty-input" type="number" name="qty[<?= (int) $r['id'] ?>]" value="<?= (int) $r['qty'] ?>" min="1" max="20"></td>
                                        <td><?= money($r['line']) ?></td>
                                        <!-- formnovalidate: الحذف ما يتأثر بفحص الحقول -->
                                        <td><button class="btn btn-danger btn-sm" name="remove" value="<?= (int) $r['id'] ?>" formnovalidate>Remove</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="cart-foot">
                        <button class="btn btn-outline" type="submit">Update cart</button>
                        <strong>Total: <span class="price"><?= money($total) ?></span></strong>
                    </div>
                </form>

                <!-- العمود 2: بيانات الاستلام والدفع -->
                <div class="panel stack">
                    <h2>Pickup &amp; delivery</h2>

                    <?php if (!$user): ?>
                        <!-- الزائر لازم يسجّل دخول، والسلة بتضل محفوظة -->
                        <p>Please log in or create an account to place your order. Your cart will be waiting.</p>
                        <a class="btn btn-primary btn-block" href="<?= url('login.php?next=' . urlencode(url('cart.php'))) ?>">Login</a>
                    <?php else: ?>
                        <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>

                        <!-- فورم تثبيت الطلب -->
                        <form method="post" class="stack">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="checkout">

                            <!-- تاريخ الاستلام: من اليوم وطالع، والافتراضي بكرا -->
                            <label class="field"><span>Pickup date</span>
                                <input type="date" name="date" min="<?= date('Y-m-d') ?>" value="<?= e($_POST['date'] ?? date('Y-m-d', strtotime('+1 day'))) ?>" required>
                            </label>

                            <!-- فترات الاستلام من SLOTS (الأولى مختارة افتراضياً) -->
                            <div class="field"><span>Pickup time</span>
                                <div class="slots">
                                    <?php foreach (SLOTS as $i => $slot): ?>
                                        <label class="slot">
                                            <input type="radio" name="slot" value="<?= e($slot) ?>" <?= ($_POST['slot'] ?? SLOTS[0]) === $slot ? 'checked' : '' ?>>
                                            <span><?= e($slot) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <label class="field"><span>Address</span>
                                <textarea name="address" rows="2" maxlength="255" placeholder="Area, street, building, floor" required><?= e($_POST['address'] ?? '') ?></textarea>
                            </label>

                            <label class="field"><span>Notes (optional)</span>
                                <input name="notes" maxlength="255" value="<?= e($_POST['notes'] ?? '') ?>" placeholder="Stains, special care, gate code...">
                            </label>

                            <div class="field"><span>Payment method</span>
                                <!-- القيمة الحقيقية دايماً cash، وأزرار pay_ui للعرض فقط (app.js بيظهر/بيخفي الصناديق) -->
                                <input type="hidden" name="method" value="cash">
                                <div class="pay-options">
                                    <label class="pay"><input type="radio" name="pay_ui" value="cash" checked><span>Cash on delivery</span></label>
                                    <label class="pay"><input type="radio" name="pay_ui" value="card"><span>Credit / Debit card <em class="soon">Soon</em></span></label>
                                    <label class="pay"><input type="radio" name="pay_ui" value="cliq"><span>CliQ <em class="soon">Soon</em></span></label>
                                </div>

                                <!-- صناديق الدفع (مخفية ومعطّلة): الدفع الإلكتروني مش مفعّل بعد -->
                                <div class="pay-card" id="pay-card" hidden>
                                    <input placeholder="Card number" disabled>
                                    <div class="two">
                                        <input placeholder="MM / YY" disabled>
                                        <input placeholder="CVV" disabled>
                                    </div>
                                    <small class="muted">Online payment is not active yet. Your order will be paid on delivery.</small>
                                </div>
                                <div class="pay-card" id="pay-cliq" hidden>
                                    <small class="muted">CliQ payment is not active yet. Your order will be paid on delivery.</small>
                                </div>
                            </div>

                            <div class="summary"><span>Total</span><span><?= money($total) ?></span></div>
                            <button class="btn btn-primary btn-lg btn-block" type="submit">Place order</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>