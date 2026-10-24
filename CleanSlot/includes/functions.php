<?php
// ============ 1) تهيئة عامة ============

// ضبط التوقيت على توقيت الأردن
date_default_timezone_set('Asia/Amman');

// إعدادات كوكيز الجلسة:
// httponly = الجافاسكربت ما بيقدر يقرأ الكوكي
// samesite = الكوكي ما بيتبعت من مواقع ثانية
// secure   = بيشتغل على HTTPS فقط (إذا الموقع على HTTPS)
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);

// بدء الجلسة (الذاكرة اللي بتتذكر المستخدم بين الصفحات)
session_start();

// حماية عن طريق الهيدرز
header('X-Content-Type-Options: nosniff');                   // المتصفح ما يخمّن نوع الملف
header('X-Frame-Options: SAMEORIGIN');                       // ما حدا يعرض موقعك داخل iframe
header('Referrer-Policy: strict-origin-when-cross-origin');  // ما نكشف الرابط الكامل لمواقع ثانية

// ============ 2) تحميل الملفات والكلاسات ============
require_once __DIR__ . '/../config/Database.php';   // الاتصال بالقاعدة
require_once __DIR__ . '/../classes/User.php';      // المستخدمين
require_once __DIR__ . '/../classes/Item.php';      // الخدمات والمنتجات
require_once __DIR__ . '/../classes/Order.php';     // الطلبات
require_once __DIR__ . '/../classes/Inquiry.php';   // الرسائل
require_once __DIR__ . '/../config/seed.php';       // البيانات التجريبية

// ============ 3) قاعدة البيانات والمستخدم ============
$db   = Database::connect();

// أول تشغيل: عبّي الخدمات التجريبية إذا الجدول فاضي، وتأكد إنه في أدمن
try {
    seed_items_if_empty($db);
    ensure_admin($db);
} catch (PDOException $e) {
    // الجداول مش موجودة: الصفحات تحت رح تعرض خطأ القاعدة الحقيقي
}

// المستخدم المسجّل دخوله (أو null إذا زائر)
$user = $_SESSION['user'] ?? null;

// ============ 4) مسار المشروع ============
// بنحسب مكان المشروع عشان الروابط تشتغل من أي مجلد (مثل localhost/cleanslot/)
$projectPath = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));
$docRootPath = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
define('BASE', rtrim(substr($projectPath, strlen($docRootPath)), '/') . '/');

/* ---------- الإعدادات: غيّرها من هون ---------- */

// فترات الاستلام المتاحة بصفحة السلة
const SLOTS = [
    '09:00 AM - 11:00 AM',
    '11:00 AM - 01:00 PM',
    '02:00 PM - 04:00 PM',
    '04:00 PM - 06:00 PM',
];

// معلومات التواصل (بتظهر بالفوتر وصفحة اتصل بنا)
// phone_raw للاتصال (بدون مسافات)، phone للعرض
const CONTACT = [
    'address'   => 'Amman, Jordan',
    'phone'     => '+962 7 0000 0000',
    'phone_raw' => '+96270000000',
    'email'     => 'hello@cleanslot.com',
    'hours'     => 'Every day, 9:00 AM - 6:00 PM',
];

// صور الموقع: رمز صورة من Unsplash، أو مسار ملفك مثل 'assets/img/hero.jpg'
const PHOTOS = [
    'hero'      => 'gkbAYJIMVDA',   // قمصان بيضا على علاقات
    'machines'  => 'iAcf41WEtRU',   // غسالات
    'rack'      => '94Tvp4AhlUU',   // قمصان على رف
    'hangers'   => 'Yy4sN6QzboU',   // علاقات
    // مفتاح لكل صفحة، عشان تغيّر كل صورة لحالها
    'story'     => 'iAcf41WEtRU',
    'franchise' => 'iAcf41WEtRU',
    'b2b'       => '94Tvp4AhlUU',
    'support'   => 'Yy4sN6QzboU',
    'contact'   => 'gkbAYJIMVDA',
    'van'       => '94Tvp4AhlUU',
    'tech'      => 'gkbAYJIMVDA',
    'team'      => 'Yy4sN6QzboU',
];

// الصورة الافتراضية لكل نوع خدمة (إذا العنصر ما إله صورة خاصة فيه)
const TYPE_PHOTOS = [
    'EcoClean (DryClean)'          => 'rack',
    'Iron Only'                    => 'hangers',
    'Wash and Fold per KG (weight)' => 'machines',
    'Shop'                         => 'hangers',
];

// حالات الطلب المسموحة
const ORDER_STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];

/* ---------- دوال مساعدة عامة ---------- */

// تنظيف النص قبل طباعته بالصفحة (حماية من XSS)
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// بناء رابط لملف داخل المشروع: url('services.php')
function url($path = '')
{
    return BASE . ltrim($path, '/');
}

// تنسيق السعر: JOD 5.50
function money($value)
{
    return 'JOD ' . number_format((float) $value, 2);
}

// تحويل لصفحة ثانية وإيقاف الصفحة الحالية
function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

// تخزين رسالة مؤقتة بتظهر بالصفحة اللي بعد الـ redirect
function flash($message, $type = 'success')
{
    $_SESSION['flash'] = [$message, $type];
}

// قراءة الرسالة المؤقتة وحذفها (header.php بيستدعيها)
function pull_flash()
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// حقل مخفي فيه توكن الحماية، بيتحط داخل كل فورم POST
function csrf_field()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

// التحقق من التوكن عند استقبال الفورم، وإذا غلط بيوقف الطلب
function csrf_check()
{
    $token = $_POST['csrf'] ?? '';
    if ($token === '' || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid form token. Please go back, refresh the page and try again.');
    }
}

// وسم ملوّن لحالة الطلب (pending, completed...)
function status_badge($status)
{
    return '<span class="badge badge-' . e($status) . '">' . e(ucfirst($status)) . '</span>';
}

// رابط بيحافظ على الفلاتر الحالية ويغيّر وحد منهم
// filter_url('services.php', ['type' => 'Shoe Care'])
function filter_url($page, array $change = [])
{
    $query = array_filter(array_merge($_GET, $change), fn($v) => $v !== '' && $v !== null);
    return url($page . ($query ? '?' . http_build_query($query) : ''));
}

/* ---------- تسجيل الدخول ---------- */

// الصفحة للمسجّلين فقط (الزائر بيتحول لصفحة الدخول)
function require_login()
{
    if (empty($_SESSION['user'])) {
        redirect(url('login.php?next=' . urlencode($_SERVER['REQUEST_URI'])));
    }
}

// الصفحة للأدمن فقط
function require_admin()
{
    if (empty($_SESSION['user'])) {
        redirect(url('login.php'));
    }
    if ($_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        exit('Admins only.');
    }
}

// تسجيل دخول المستخدم (بنخزّن بس الأساسيات بالجلسة)
function login_user($row)
{
    session_regenerate_id(true);   // معرّف جلسة جديد للأمان
    $_SESSION['user'] = ['id' => $row['id'], 'name' => $row['name'], 'role' => $row['role']];
}

// بعد 5 محاولات غلط الدخول بيتقفل 5 دقائق. بترجع الدقائق المتبقية (أو 0)
function login_locked()
{
    $a = $_SESSION['login_fail'] ?? ['n' => 0, 'until' => 0];
    return $a['until'] > time() ? ceil(($a['until'] - time()) / 60) : 0;
}

// تسجيل محاولة دخول فاشلة
function login_failed()
{
    $a = $_SESSION['login_fail'] ?? ['n' => 0, 'until' => 0];
    $a['n']++;
    if ($a['n'] >= 5) {
        $a = ['n' => 0, 'until' => time() + 300];   // قفل 5 دقائق
    }
    $_SESSION['login_fail'] = $a;
}

// دخول ناجح: مسح عدّاد الفشل
function login_ok()
{
    unset($_SESSION['login_fail']);
}

/* ---------- الصور ---------- */

// رابط صورة من إعدادات PHOTOS: photo('hero', 1400)
function photo($key, $width = 900)
{
    $value = PHOTOS[$key] ?? PHOTOS['hero'];

    // إذا القيمة مسار ملف (فيها /) بنستخدمه مباشرة
    if (strpos($value, '/') !== false) {
        return url($value);
    }

    // نسخة محلية بمجلد assets/img/photos/ بتغلب دايماً وما بتحتاج نت
    foreach (['jpg', 'jpeg', 'webp', 'png'] as $ext) {
        if (is_file(__DIR__ . '/../assets/img/photos/' . $key . '.' . $ext)) {
            return url('assets/img/photos/' . $key . '.' . $ext);
        }
    }

    // وإلا بنحمّلها من Unsplash (بتحتاج نت، وغالباً هاد سبب الصور المكسورة)
    return 'https://unsplash.com/photos/' . $value . '/download?w=' . (int) $width;
}

// صورة عنصر: ملف مرفوع، أو رابط خارجي، أو الصورة الافتراضية لنوعه
function item_image($item)
{
    $image = $item['image'] ?? '';

    // رابط خارجي
    if (preg_match('#^https?://#i', $image)) {
        return $image;
    }
    // ملف مرفوع (بصيغة uploads/اسم.jpg)
    if (preg_match('#^uploads/[A-Za-z0-9._-]+$#', $image)) {
        return url($image);
    }
    // الصورة الافتراضية حسب نوع الخدمة
    return photo(TYPE_PHOTOS[$item['type']] ?? 'machines', 600);
}

// إذا الصورة انكسرت بنعرض صورة بديلة بدل الأيقونة المكسورة
function img_fallback()
{
    return "onerror=\"this.onerror=null;this.src='" . e(url('assets/img/placeholder.svg')) . "'\"";
}

// حفظ صورة مرفوعة بمجلد uploads. بترجع [المسار, الخطأ]
function save_upload(array $file)
{
    // ما انرفع ولا ملف: مش خطأ
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    // خطأ بالرفع أو الحجم أكبر من 2MB
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) {
        return [null, 'The picture must be smaller than 2 MB.'];
    }
    // فحص النوع الحقيقي للملف (مش بس الامتداد)
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime  = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($types[$mime])) {
        return [null, 'Only JPG, PNG or WEBP pictures are allowed.'];
    }
    // اسم عشوائي عشان ما يتكرر
    $name = bin2hex(random_bytes(8)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../uploads/' . $name)) {
        return [null, 'Could not save the picture. Check the uploads folder permissions.'];
    }
    return ['uploads/' . $name, null];
}

// حذف صورة مرفوعة سابقاً (الروابط الخارجية ما بتنلمس)
function delete_upload($path)
{
    if (preg_match('#^uploads/[A-Za-z0-9._-]+$#', (string) $path)) {
        $full = __DIR__ . '/../' . $path;
        if (is_file($full)) {
            @unlink($full);
        }
    }
}

/* ---------- السلة (مخزنة بالجلسة: رقم العنصر => الكمية) ---------- */

// عدد القطع بالسلة
function cart_count()
{
    return array_sum($_SESSION['cart'] ?? []);
}

// عناصر السلة بتفاصيلها ومجموع كل سطر (العناصر المحذوفة أو المخفية بتنشال)
function cart_rows(Item $items)
{
    $rows = [];
    foreach ($_SESSION['cart'] ?? [] as $id => $qty) {
        $item = $items->find($id);
        if ($item && $item['active']) {
            $item['qty']   = $qty;
            $item['line']  = $item['price'] * $qty;
            $rows[]        = $item;
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }
    return $rows;
}

/* ---------- فورمات اتصل بنا / الدعم / B2B / الفرنشايز ---------- */

// معالجة فورم الصفحة (نادِها قبل أي طباعة). بترجع [القيم القديمة, الأخطاء]
// وإذا نجح الإرسال بتحفظ الرسالة وبترجع للصفحة
function process_inquiry($topic, Inquiry $inquiries, $page)
{
    global $user;
    $old    = ['name' => $user['name'] ?? '', 'email' => '', 'phone' => '', 'company' => '', 'message' => ''];
    $errors = [];

    // إذا الصفحة انفتحت عادي (مش إرسال) رجّع الفورم الفاضي
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return [$old, $errors];
    }
    csrf_check();

    // حماية من السبام: حقل مخفي بيعبّيه البوت (الإنسان ما بيشوفه)
    if (trim($_POST['website'] ?? '') !== '') {
        redirect(url($page));
    }
    // حد أقصى 5 رسائل كل 10 دقائق
    $recent = array_filter($_SESSION['inquiry_times'] ?? [], fn($t) => $t > time() - 600);
    if (count($recent) >= 5) {
        $errors['message'] = 'Too many messages. Please wait a few minutes and try again.';
    }

    // قراءة الحقول من الفورم
    foreach ($old as $key => $unused) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    // فحص كل حقل
    if (mb_strlen($old['name']) < 2 || mb_strlen($old['name']) > 100) {
        $errors['name'] = 'Please enter your name.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email.';
    }
    if ($old['phone'] !== '' && !preg_match('/^[0-9+\s-]{7,20}$/', $old['phone'])) {
        $errors['phone'] = 'Enter a valid phone number.';
    }
    if (mb_strlen($old['company']) > 100) {
        $errors['company'] = 'Company name is too long.';
    }
    if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 2000) {
        $errors['message'] = 'Message must be between 10 and 2000 characters.';
    }

    // ما في أخطاء: احفظ الرسالة وارجع للصفحة برسالة شكر
    if (!$errors) {
        $recent[] = time();
        $_SESSION['inquiry_times'] = $recent;
        $inquiries->create($topic, $old);
        flash('Thank you! Your message was sent. We will get back to you soon.');
        redirect(url($page));
    }
    return [$old, $errors];
}

/* ---------- المواعيد ---------- */

// هل وقت الاستلام فات؟ (بيهم بس إذا التاريخ هو اليوم)
function slot_has_passed($date, $slot)
{
    if ($date !== date('Y-m-d')) {
        return false;
    }
    // بداية الفترة: من '09:00 AM - 11:00 AM' بناخد '09:00 AM'
    $start = DateTime::createFromFormat('Y-m-d h:i A', $date . ' ' . trim(explode(' - ', $slot)[0]));
    return $start && $start <= new DateTime();
}