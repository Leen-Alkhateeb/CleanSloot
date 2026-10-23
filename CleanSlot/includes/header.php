<?php
// Set before including: $title (page title) and $active (page key used in the menu)
$title  = $title ?? 'CleanSlot';
$active = $active ?? '';
$flash  = pull_flash();
$links  = [
    'home'      => ['index.php', 'Home'],
    'services'  => ['services.php', 'Services'],
    'shop'      => ['shop.php', 'Shop'],
    'story'     => ['our-story.php', 'Our Story'],
    'b2b'       => ['b2b.php', 'B2B Care'],
    'franchise' => ['franchise.php', 'Franchise'],
    'contact'   => ['contact.php', 'Contact'],
    'support'   => ['support.php', 'Support'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <meta name="description" content="CleanSlot: eco-friendly dry cleaning, ironing, shoe care and laundry in Jordan. Order online, we pick up and deliver.">
    <link rel="icon" type="image/svg+xml" href="<?= url('assets/img/favicon.svg') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <script>
        // Set the theme before the page paints, so there is no flash
        document.documentElement.classList.add('js');
        document.documentElement.dataset.theme = localStorage.getItem('theme') ||
            (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    </script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header">
    <div class="container nav">
        <a class="brand" href="<?= url('index.php') ?>">
            <img src="<?= url('assets/img/logo.svg') ?>" width="44" height="44" alt="">
            <span class="brand-text">Clean<b>Slot</b></span>
        </a>

        <input type="checkbox" id="nav-toggle" class="nav-toggle" hidden>
        <label for="nav-toggle" class="burger" aria-label="Open menu"><span></span></label>

        <div class="nav-panel">
            <nav class="menu">
                <?php foreach ($links as $key => [$file, $label]): ?>
                    <a href="<?= url($file) ?>" class="<?= $active === $key ? 'active' : '' ?>">
                        <?= $label ?><?php if ($key === 'shop'): ?><em class="new">NEW</em><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="nav-actions">
                <button class="icon-btn" type="button" id="theme-toggle" aria-label="Switch light / dark mode">
                    <svg class="i-moon" viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M20.5 14.5A8.5 8.5 0 0 1 9.5 3.5a8.5 8.5 0 1 0 11 11z"/></svg>
                    <svg class="i-sun" viewBox="0 0 24 24" width="20" height="20"><circle cx="12" cy="12" r="4.5" fill="currentColor"/><path stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8"/></svg>
                </button>

                <a class="icon-btn cart-link" href="<?= url('cart.php') ?>" aria-label="Cart">
                    <svg viewBox="0 0 24 24" width="22" height="22"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 9h18l-2 10a2 2 0 0 1-2 1.6H7A2 2 0 0 1 5 19L3 9zM8 9l3-5M16 9l-3-5"/></svg>
                    <?php if (cart_count()): ?><span class="cart-badge"><?= cart_count() ?></span><?php endif; ?>
                </a>

                <?php if ($user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a class="btn btn-outline btn-sm" href="<?= url('admin/index.php') ?>">Dashboard</a>
                    <?php else: ?>
                        <a class="btn btn-outline btn-sm" href="<?= url('orders.php') ?>">My orders</a>
                    <?php endif; ?>
                    <form method="post" action="<?= url('logout.php') ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-dark btn-sm" type="submit">Logout</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-primary btn-sm" href="<?= url('login.php') ?>">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<?php if ($flash): ?>
    <div class="container">
        <div class="flash flash-<?= e($flash[1]) ?>" role="status"><?= e($flash[0]) ?></div>
    </div>
<?php endif; ?>
