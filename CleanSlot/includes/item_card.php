<?php // One service (row) or product (card). Set $item before including. ?>
<?php if ($item['kind'] === 'service'): ?>
    <article class="item">
        <img class="item-photo" src="<?= e(item_image($item)) ?>" alt="<?= e($item['name']) ?>" loading="lazy" <?= img_fallback() ?>>

        <div class="item-body">
            <small class="item-type"><?= e($item['type']) ?></small>
            <h3><?= e($item['name']) ?></h3>
            <p><?= e($item['description']) ?></p>
            <span class="stains" title="Stain removal: <?= (int) $item['stains'] ?> of 5">
                STAINS <b><?= str_repeat('&#9733;', (int) $item['stains']) ?></b><?= str_repeat('&#9734;', 5 - (int) $item['stains']) ?>
            </span>
        </div>

        <form class="item-side" method="post" action="<?= url('cart.php') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="item" value="<?= (int) $item['id'] ?>">
            <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI']) ?>">
            <span class="price"><?= money($item['price']) ?></span>
            <div class="qty">
                <button type="button" data-step="-1" aria-label="Less">&minus;</button>
                <input type="number" name="qty" value="1" min="1" max="20" aria-label="Quantity">
                <button type="button" data-step="1" aria-label="More">+</button>
            </div>
            <button class="btn btn-outline btn-block" name="go" value="add">Add to Cart</button>
            <button class="btn btn-primary btn-block" name="go" value="now">Order Now</button>
            <a class="link" href="<?= url('support.php') ?>">Learn More</a>
        </form>
    </article>
<?php else: ?>
    <article class="product">
        <img src="<?= e(item_image($item)) ?>" alt="<?= e($item['name']) ?>" loading="lazy" <?= img_fallback() ?>>
        <div class="product-body">
            <small class="item-type"><?= e($item['category']) ?></small>
            <h3><?= e($item['name']) ?></h3>
            <p><?= e($item['description']) ?></p>
            <form method="post" action="<?= url('cart.php') ?>" class="product-foot">
                <?= csrf_field() ?>
                <input type="hidden" name="item" value="<?= (int) $item['id'] ?>">
                <input type="hidden" name="qty" value="1">
                <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI']) ?>">
                <span class="price"><?= money($item['price']) ?></span>
                <button class="btn btn-primary btn-sm" name="go" value="add">Add to Cart</button>
            </form>
        </div>
    </article>
<?php endif; ?>
