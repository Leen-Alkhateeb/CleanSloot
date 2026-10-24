<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="brand" href="<?= url('index.php') ?>">
                <img src="<?= url('assets/img/favicon.svg') ?>" width="40" height="40" alt="">
                <span class="brand-text">Clean<b>Slot</b></span>
            </a>
            <p>Toxin-free, eco-friendly cleaning for a greener world. We pick up, clean and deliver.</p>
        </div>

        <div>
            <h4>Explore</h4>
            <a href="<?= url('services.php') ?>">Services</a>
            <a href="<?= url('shop.php') ?>">Shop</a>
            <a href="<?= url('our-story.php') ?>">Our Story</a>
            <a href="<?= url('b2b.php') ?>">B2B Care</a>
            <a href="<?= url('franchise.php') ?>">Franchise</a>
        </div>

        <div>
            <h4>Help</h4>
            <a href="<?= url('contact.php') ?>">Contact</a>
            <a href="<?= url('support.php') ?>">Support &amp; FAQ</a>
            <a href="tel:<?= e(CONTACT['phone_raw']) ?>"><?= e(CONTACT['phone']) ?></a>
            <a href="mailto:<?= e(CONTACT['email']) ?>"><?= e(CONTACT['email']) ?></a>
        </div>

        <div>
            <h4>Account</h4>
            <a href="<?= url('login.php') ?>">Login</a>
            <a href="<?= url('cart.php') ?>">Cart</a>
            <a href="<?= url('orders.php') ?>">My orders</a>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>&copy; <?= date('Y') ?> CleanSlot. All rights reserved.</span>
    </div>
</footer>

<script src="<?= url('assets/js/app.js') ?>" defer></script>
</body>
</html>
