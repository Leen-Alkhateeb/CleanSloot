<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$inquiries = new Inquiry($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $inquiries->delete((int) ($_POST['delete'] ?? 0));
    flash('Message deleted.');
    redirect(url('admin/inquiries.php'));
}

$topic = array_key_exists($_GET['topic'] ?? '', Inquiry::TOPICS) ? $_GET['topic'] : '';
$list  = $inquiries->latest(200, $topic);

$title    = 'Messages | CleanSlot';
$active   = '';
$adminTab = 'inquiries';
require __DIR__ . '/../includes/header.php';
?>

<main id="main" class="section">
    <div class="container stack-lg">
        <h1 class="h2">Messages</h1>

        <?php require __DIR__ . '/../includes/admin_tabs.php'; ?>

        <div class="chips">
            <a href="<?= url('admin/inquiries.php') ?>" class="chip <?= $topic === '' ? 'active' : '' ?>">All</a>
            <?php foreach (Inquiry::TOPICS as $key => $label): ?>
                <a href="<?= url('admin/inquiries.php?topic=' . $key) ?>" class="chip <?= $topic === $key ? 'active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>

        <?php foreach ($list as $m): ?>
            <article class="panel message">
                <div class="order-top">
                    <div>
                        <span class="badge badge-confirmed"><?= e(Inquiry::TOPICS[$m['topic']]) ?></span>
                        <strong><?= e($m['name']) ?></strong>
                        <?php if ($m['company'] !== ''): ?><span class="muted">(<?= e($m['company']) ?>)</span><?php endif; ?>
                    </div>
                    <span class="muted"><?= e($m['created_at']) ?></span>
                </div>
                <p class="muted">
                    <a class="link" href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>
                    <?php if ($m['phone'] !== ''): ?> &middot; <a class="link" href="tel:<?= e($m['phone']) ?>"><?= e($m['phone']) ?></a><?php endif; ?>
                </p>
                <p><?= nl2br(e($m['message'])) ?></p>
                <form method="post" onsubmit="return confirm('Delete this message?')">
                    <?= csrf_field() ?>
                    <button class="btn btn-danger btn-sm" name="delete" value="<?= (int) $m['id'] ?>">Delete</button>
                </form>
            </article>
        <?php endforeach; ?>

        <?php if (!$list): ?><p class="empty">No messages here yet.</p><?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
