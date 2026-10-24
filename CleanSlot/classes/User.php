<?php
class User
{
    // اتصال القاعدة
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // هل الإيميل مسجّل من قبل؟ (بنستخدمها بالتسجيل لمنع التكرار)
    public function emailExists($email)
    {
        $st = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([$email]);
        return (bool) $st->fetch();   // true إذا لقى صف، false إذا لا
    }

    // إنشاء حساب جديد
    public function register($name, $email, $phone, $password)
    {
        $st = $this->db->prepare('INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)');

        // password_hash: الباسورد بيتخزن مشفّر (مش نص عادي)
        // $phone ?: null: إذا الهاتف فاضي بنخزّن NULL
        return $st->execute([$name, $email, $phone ?: null, password_hash($password, PASSWORD_DEFAULT)]);
    }

    // تسجيل الدخول: بترجع صف المستخدم إذا الإيميل والباسورد صح، وإلا false
    public function login($email, $password)
    {
        $st = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([$email]);
        $row = $st->fetch();

        // password_verify بتقارن الباسورد المكتوب مع النسخة المشفّرة
        return ($row && password_verify($password, $row['password'])) ? $row : false;
    }

    // عدد الزبائن (للكرت بلوحة الأدمن، بدون الأدمن نفسه)
    public function countCustomers()
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    }
}