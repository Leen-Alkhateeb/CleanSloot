<?php
require_once __DIR__ . '/includes/functions.php';

$items = new Item($db);
$types = $items->values('type', 'service');

// الفلاتر من الرابط
$f = [];
foreach (['type', 'category', 'subcategory', 'q', 'max', 'sort'] as $key) {
    $f[$key] = trim($_GET[$key] ?? '');
}

// النوع لازم يكون موجود، وإلا أول نوع
if (!in_array($f['type'], $types, true)) {
    $f['type'] = $types[0] ?? '';
}

$categories    = $items->values('category', 'service', ['type' => $f['type']]);
$subcategories = $f['category'] ? $items->values('subcategory', 'service', ['type' => $f['type'], 'category' => $f['category']]) : [];
$list          = $items->search('service', $f);

// دالة صغيرة: بترجع "chip active" إذا الشرط صح
function chip($on) { return $on ? 'chip active' : 'chip'; }

$title     = 'Services | CleanSlot';
$active    = 'services';
$heroLabel = 'CleanSlot services';
$heroTitle = $f['type'];
$heroText  = 'Toxin-free, eco-friendly cleaning for a greener world. Pick your items, we collect and deliver.';
$heroPhoto = 'hero';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page_hero.php';
?>

<main id="main" class="section">
    <div class="container">
        <h2>Services</h2>

        <div class="chips chips-lg">
            <?php foreach ($types as $t): ?>
                <a href="<?= filter_url('services.php', ['type' => $t, 'category' => '', 'subcategory' => '']) ?>" class="<?= chip($f['type'] === $t) ?>"><?= e($t) ?></a>
            <?php endforeach; ?>
        </div>

        <div class="shop">
            <aside class="side-stack">
                <div class="panel">
                    <h4>Category</h4>
                    <div class="chips">
                        <a href="<?= filter_url('services.php', ['category' => '', 'subcategory' => '']) ?>" class="<?= chip($f['category'] === '') ?>">All</a>
                        <?php foreach ($categories as $c): ?>
                            <a href="<?= filter_url('services.php', ['category' => $c, 'subcategory' => '']) ?>" class="<?= chip($f['category'] === $c) ?>"><?= e($c) ?></a>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($subcategories): ?>
                        <hr>
                        <h4>Sub-category</h4>
                        <div class="chips">
                            <a href="<?= filter_url('services.php', ['subcategory' => '']) ?>" class="<?= chip($f['subcategory'] === '') ?>">All</a>
                            <?php foreach ($subcategories as $s): ?>
                                <a href="<?= filter_url('services.php', ['subcategory' => $s]) ?>" class="<?= chip($f['subcategory'] === $s) ?>"><?= e($s) ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="panel">
                    <h4>Price</h4>
                    <label class="price-field">JOD
                        <input type="number" name="max" form="filters" min="0" step="0.5" value="<?= e($f['max']) ?>" placeholder="Max price">
                    </label>
                </div>
            </aside>

            <div>
                <form id="filters" method="get" class="toolbar">
                    <input type="hidden" name="type" value="<?= e($f['type']) ?>">
                    <input type="hidden" name="category" value="<?= e($f['category']) ?>">
                    <input type="hidden" name="subcategory" value="<?= e($f['subcategory']) ?>">
                    <input type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Search services">
                    <select name="sort" onchange="this.form.submit()">
                        <option value="">Best Match</option>
                        <option value="price_asc" <?= $f['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: low to high</option>
                        <option value="price_desc" <?= $f['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
                        <option value="name" <?= $f['sort'] === 'name' ? 'selected' : '' ?>>Name</option>
                    </select>
                    <button class="btn btn-primary" type="submit">Apply</button>
                </form>

                <div class="list">
                    <?php $last = null; ?>
                    <?php foreach ($list as $item): ?>
                        <?php if ($f['sort'] === '' && $item['category'] !== $last): $last = $item['category']; ?>
                            <h3 class="list-title"><?= e($last) ?></h3>
                        <?php endif; ?>
                        <?php require __DIR__ . '/includes/item_card.php'; ?>
                    <?php endforeach; ?>

                    <?php if (!$list): ?>
                        <p class="empty">Nothing matches your search. <a class="link" href="<?= url('services.php') ?>">Show all services</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>