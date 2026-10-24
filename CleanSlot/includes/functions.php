<?php
// ============ 1) تهيئة عامة ============

// ضبط التوقيت على توقيت الأردن
date_default_timezone_set('Asia/Amman');

// إعدادات كوكيز الجلسة:
// httponly = الجافاسكربت ما بيقدر يقرأ الكوكي (حماية)
// samesite = الكوكي ما بيتبعت من مواقع ثانية (حماية)
// secure   = بيشتغل على HTTPS فقط (إذا الموقع على HTTPS)
$isHttps = !empty($_SERVER['HTTPS']);
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => $isHttps]);

// بدء الجلسة (الذاكرة اللي بتتذكر المستخدم بين الصفحات)
session_start();

// حماية بسيطة عن طريق الهيدرز:
header('X-Content-Type-Options: nosniff');                      // المتصفح ما يخمّن نوع الملف
header('X-Frame-Options: SAMEORIGIN');                          // ما حدا يعرض موقعك داخل iframe
header('Referrer-Policy: strict-origin-when-cross-origin');     // ما نكشف الرابط الكامل للمواقع الثانية

// ============ 2) تحميل الملفات والكلاسات ============
require_once __DIR__ . '/../config/Database.php';   // الاتصال بالقاعدة
require_once __DIR__ . '/../classes/User.php';      // المستخدمين
require_once __DIR__ . '/../classes/Item.php';      // الخدمات والمنتجات
require_once __DIR__ . '/../classes/Order.php';     // الطلبات
require_once __DIR__ . '/../classes/Inquiry.php';   // الرسائل
require_once __DIR__ . '/../config/seed.php';       // البيانات التجريبية

// ============ 3) قاعدة البيانات والمستخدم ============
$db = Database::connect();

// أول تشغيل: عبّي الخدمات التجريبية وتأكد إنه في أدمن
try {
    seed_items_if_empty($db);
    ensure_admin($db);
} catch (PDOException $e) {
    // الجداول مش موجودة: الصفحات رح تعرض خطأ القاعدة الحقيقي
}

// المستخدم المسجّل دخوله (أو null إذا ما سجّل)
$user = null;
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
}

// ============ 4) مسار المشروع ============
// بنحسب مكان المشروع عشان الروابط تشتغل من أي مجلد (مثل localhost/cleanslot/)
$projectPath = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));
$docRoot     = '';
if (isset($_SERVER['DOCUMENT_ROOT'])) {
    $docRoot = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT']));
}
define('BASE', rtrim(substr($projectPath, strlen($docRoot)), '/') . '/');