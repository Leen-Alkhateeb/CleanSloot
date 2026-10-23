<?php
date_default_timezone_set('Asia/Amman');

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Item.php';
require_once __DIR__ . '/../classes/Order.php';
require_once __DIR__ . '/../classes/Inquiry.php';
require_once __DIR__ . '/../config/seed.php';

$db   = Database::connect();

// If the services/products table is empty (schema.sql not imported fully), add the sample data once
try {
    seed_items_if_empty($db);
    ensure_admin($db);
} catch (PDOException $e) {
    // table missing: the pages below will show the real database error
}
$user = $_SESSION['user'] ?? null;

// Web path of the project folder, so links work from any sub-folder
$projectPath = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));
$docRootPath = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
define('BASE', rtrim(substr($projectPath, strlen($docRootPath)), '/') . '/');

/* ---------- Settings: change them here ---------- */

const SLOTS = [
    '09:00 AM - 11:00 AM',
    '11:00 AM - 01:00 PM',
    '02:00 PM - 04:00 PM',
    '04:00 PM - 06:00 PM',
];

const CONTACT = [
    'address'   => 'Amman, Jordan',
    'phone'     => '+962 7 0000 0000',
    'phone_raw' => '+96270000000',
    'email'     => 'hello@cleanslot.com',
    'hours'     => 'Every day, 9:00 AM - 6:00 PM',
];

// Real photos from Unsplash (free to use). To use your own picture, put it in
// assets/img/ and write its path here, for example 'assets/img/hero.jpg'.
const PHOTOS = [
    'hero'      => 'gkbAYJIMVDA',   // white shirts on hangers
    'machines'  => 'iAcf41WEtRU',   // washing machines
    'rack'      => '94Tvp4AhlUU',   // shirts on a rack
    'hangers'   => 'Yy4sN6QzboU',   // hangers on a rail
    // One key per page, so you can change each picture alone
    'story'     => 'iAcf41WEtRU',
    'franchise' => 'iAcf41WEtRU',
    'b2b'       => '94Tvp4AhlUU',
    'support'   => 'Yy4sN6QzboU',
    'contact'   => 'gkbAYJIMVDA',
    'van'       => '94Tvp4AhlUU',
    'tech'      => 'gkbAYJIMVDA',
    'team'      => 'Yy4sN6QzboU',
];

// Which photo each service type / shop uses when the item has no picture of its own
const TYPE_PHOTOS = [
    'EcoClean (DryClean)'          => 'rack',
    'Iron Only'                    => 'hangers',
    'Wash and Fold per KG (weight)' => 'machines',
    'Shop'                         => 'hangers',
];

const ORDER_STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];

/* ---------- Small helpers ---------- */

/** Escape text for HTML */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Link to a file inside the project: url('services.php') */
function url($path = '')
{
    return BASE . ltrim($path, '/');
}

function money($value)
{
    return 'JOD ' . number_format((float) $value, 2);
}

function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

/** Save a one-time message to show on the next page */
function flash($message, $type = 'success')
{
    $_SESSION['flash'] = [$message, $type];
}

function pull_flash()
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_field()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function csrf_check()
{
    $token = $_POST['csrf'] ?? '';
    if ($token === '' || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid form token. Please go back, refresh the page and try again.');
    }
}

function status_badge($status)
{
    return '<span class="badge badge-' . e($status) . '">' . e(ucfirst($status)) . '</span>';
}

/** Keeps the other filters in the link: filter_url('services.php', ['type' => 'Shoe Care']) */
function filter_url($page, array $change = [])
{
    $query = array_filter(array_merge($_GET, $change), fn($v) => $v !== '' && $v !== null);
    return url($page . ($query ? '?' . http_build_query($query) : ''));
}

/* ---------- Login ---------- */

function require_login()
{
    if (empty($_SESSION['user'])) {
        redirect(url('login.php?next=' . urlencode($_SERVER['REQUEST_URI'])));
    }
}

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

function login_user($row)
{
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => $row['id'], 'name' => $row['name'], 'role' => $row['role']];
}

/** After 5 wrong tries the login form pauses for 5 minutes */
function login_locked()
{
    $a = $_SESSION['login_fail'] ?? ['n' => 0, 'until' => 0];
    return $a['until'] > time() ? ceil(($a['until'] - time()) / 60) : 0;
}

function login_failed()
{
    $a = $_SESSION['login_fail'] ?? ['n' => 0, 'until' => 0];
    $a['n']++;
    if ($a['n'] >= 5) {
        $a = ['n' => 0, 'until' => time() + 300];
    }
    $_SESSION['login_fail'] = $a;
}

function login_ok()
{
    unset($_SESSION['login_fail']);
}

/* ---------- Photos ---------- */

/** Picture of a project photo key: photo('hero', 1400) */
function photo($key, $width = 900)
{
    $value = PHOTOS[$key] ?? PHOTOS['hero'];
    if (strpos($value, '/') !== false) {
        return url($value);           // your own file in assets/img
    }
    // A local copy always wins and never depends on the internet: assets/img/photos/hero.jpg
    foreach (['jpg', 'jpeg', 'webp', 'png'] as $ext) {
        if (is_file(__DIR__ . '/../assets/img/photos/' . $key . '.' . $ext)) {
            return url('assets/img/photos/' . $key . '.' . $ext);
        }
    }
    return 'https://unsplash.com/photos/' . $value . '/download?w=' . (int) $width;
}

/** Picture of an item: uploaded file, image link, or the default photo of its type */
function item_image($item)
{
    $image = $item['image'] ?? '';
    if (preg_match('#^https?://#i', $image)) {
        return $image;
    }
    if (preg_match('#^uploads/[A-Za-z0-9._-]+$#', $image)) {
        return url($image);
    }
    return photo(TYPE_PHOTOS[$item['type']] ?? 'machines', 600);
}

/** If a picture fails to load, show a clean placeholder instead of a broken icon */
function img_fallback()
{
    return "onerror=\"this.onerror=null;this.src='" . e(url('assets/img/placeholder.svg')) . "'\"";
}

/** Saves an uploaded picture into /uploads. Returns [path|null, error|null] */
function save_upload(array $file)
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) {
        return [null, 'The picture must be smaller than 2 MB.'];
    }
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime  = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($types[$mime])) {
        return [null, 'Only JPG, PNG or WEBP pictures are allowed.'];
    }
    $name = bin2hex(random_bytes(8)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../uploads/' . $name)) {
        return [null, 'Could not save the picture. Check the uploads folder permissions.'];
    }
    return ['uploads/' . $name, null];
}

/** Removes a picture that was uploaded earlier (never touches links) */
function delete_upload($path)
{
    if (preg_match('#^uploads/[A-Za-z0-9._-]+$#', (string) $path)) {
        $full = __DIR__ . '/../' . $path;
        if (is_file($full)) {
            @unlink($full);
        }
    }
}

/* ---------- Cart (kept in the session: item id => quantity) ---------- */

function cart_count()
{
    return array_sum($_SESSION['cart'] ?? []);
}

/** Cart rows with item details and a line total. Removed items are dropped. */
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

/* ---------- Contact / Support / B2B / Franchise forms ---------- */

/**
 * Handles the form of a page. Call it before any output.
 * Returns [old values, errors]. On success it saves the message and redirects back.
 */
function process_inquiry($topic, Inquiry $inquiries, $page)
{
    global $user;
    $old    = ['name' => $user['name'] ?? '', 'email' => '', 'phone' => '', 'company' => '', 'message' => ''];
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return [$old, $errors];
    }
    csrf_check();

    // Spam protection: a hidden field bots fill in, and 5 messages per 10 minutes
    if (trim($_POST['website'] ?? '') !== '') {
        redirect(url($page));
    }
    $recent = array_filter($_SESSION['inquiry_times'] ?? [], fn($t) => $t > time() - 600);
    if (count($recent) >= 5) {
        $errors['message'] = 'Too many messages. Please wait a few minutes and try again.';
    }

    foreach ($old as $key => $unused) {
        $old[$key] = trim($_POST[$key] ?? '');
    }
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

    if (!$errors) {
        $recent[] = time();
        $_SESSION['inquiry_times'] = $recent;
        $inquiries->create($topic, $old);
        flash('Thank you! Your message was sent. We will get back to you soon.');
        redirect(url($page));
    }
    return [$old, $errors];
}

/** True when the pickup time starts in the past (only matters for today's date) */
function slot_has_passed($date, $slot)
{
    if ($date !== date('Y-m-d')) {
        return false;
    }
    $start = DateTime::createFromFormat('Y-m-d h:i A', $date . ' ' . trim(explode(' - ', $slot)[0]));
    return $start && $start <= new DateTime();
}
