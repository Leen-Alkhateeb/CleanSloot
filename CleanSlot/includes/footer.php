
<footer class="site-footer">
    <div class="container footer-grid">

        <!-- العمود 1: اللوغو ووصف قصير -->
        <div>
            <!-- اللوغو: صورة + نص (Clean عادي و Slot عريض) وبيرجّع للرئيسية -->
            <a class="brand" href="<?= url('index.php') ?>">
                <img src="<?= url('assets/img/favicon.svg') ?>" width="40" height="40" alt="">
                <span class="brand-text">Clean<b>Slot</b></span>
            </a>
            <p>Toxin-free, eco-friendly cleaning for a greener world. We pick up, clean and deliver.</p>
        </div>

        <!-- العمود 2: روابط صفحات الموقع -->
        <div>
            <h4>Explore</h4>
            <a href="<?= url('services.php') ?>">Services</a>
            <a href="<?= url('shop.php') ?>">Shop</a>
            <a href="<?= url('our-story.php') ?>">Our Story</a>
            <a href="<?= url('b2b.php') ?>">B2B Care</a>
            <a href="<?= url('franchise.php') ?>">Franchise</a>
        </div>

        <!-- العمود 3: المساعدة ومعلومات التواصل -->
        <div>
            <h4>Help</h4>
            <a href="<?= url('contact.php') ?>">Contact</a>
            <a href="<?= url('support.php') ?>">Support &amp; FAQ</a>

            <!-- tel: بيتصل من الموبايل (الرقم بدون مسافات)، والنص المعروض الرقم المنسّق -->
            <a href="tel:<?= e(CONTACT['phone_raw']) ?>"><?= e(CONTACT['phone']) ?></a>

            <!-- mailto: بيفتح برنامج البريد -->
            <a href="mailto:<?= e(CONTACT['email']) ?>"><?= e(CONTACT['email']) ?></a>
        </div>

        <!-- العمود 4: حساب الزبون -->
        <div>
            <h4>Account</h4>
            <a href="<?= url('login.php') ?>">Login</a>
            <a href="<?= url('cart.php') ?>">Cart</a>
            <a href="<?= url('orders.php') ?>">My orders</a>
        </div>
    </div>

    <!-- الشريط السفلي: حقوق النشر، والسنة بتتحدث لحالها -->
    <div class="container footer-bottom">
        <span>&copy; <?= date('Y') ?> CleanSlot. All rights reserved.</span>
    </div>
</footer>

<!-- ملف الجافاسكربت الرئيسي (defer: يتحمّل بعد ما الصفحة تنعرض) -->
<script src="<?= url('assets/js/app.js') ?>" defer></script>

<!-- إغلاق الصفحة (انفتحوا بـ header.php) -->
</body>
</html>