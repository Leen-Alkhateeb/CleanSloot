<?php
require_once __DIR__ . '/includes/functions.php';

$items      = new Item($db);
$categories = $items->values('category', 'product');
$f = [
    'category' => $_GET['category'] ?? '',
    'q'        => trim($_GET['q'] ?? ''),
    'sort'     => $_GET['sort'] ?? '',
];
$list = $items->search('product', $f);

$title     = 'Shop | CleanSlot';
$active    = 'shop';
$heroLabel = 'CleanSlot shop';
$heroTitle = 'Shop';
$heroText  = 'Laundry care products and accessories we trust, delivered with your order.';
$heroPhoto = 'hangers';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page_hero.php';
?>

<main id="main" class="section">
    <div class="container">
        <div class="chips chips-lg">
            <a href="<?= filter_url('shop.php', ['category' => '']) ?>" class="chip <?= $f['category'] === '' ? 'active' : '' ?>">All</a>
            <?php foreach ($categories as $c): ?>
                <a href="<?= filter_url('shop.php', ['category' => $c]) ?>" class="chip <?= $f['category'] === $c ? 'active' : '' ?>"><?= e($c) ?></a>
            <?php endforeach; ?>
        </div>

        <form method="get" class="toolbar">
            <input type="hidden" name="category" value="<?= e($f['category']) ?>">
            <input type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Search products" aria-label="Search products">
            <select name="sort" aria-label="Sort" onchange="this.form.submit()">
                <option value="">Best Match</option>
                <option value="price_asc" <?= $f['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: low to high</option>
                <option value="price_desc" <?= $f['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
                <option value="name" <?= $f['sort'] === 'name' ? 'selected' : '' ?>>Name</option>
            </select>
            <button class="btn btn-primary" type="submit">Apply</button>
        </form>

        <div class="products">
            <?php foreach ($list as $item): ?>
                <?php require __DIR__ . '/includes/item_card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php if (!$list): ?><p class="empty">No products found.</p><?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
