<?php
class User
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function emailExists($email)
    {
        $st = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([$email]);
        return (bool) $st->fetch();
    }

    public function register($name, $email, $phone, $password)
    {
        $st = $this->db->prepare('INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)');
        return $st->execute([$name, $email, $phone ?: null, password_hash($password, PASSWORD_DEFAULT)]);
    }

    /** Returns the user row when email and password match, otherwise false */
    public function login($email, $password)
    {
        $st = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([$email]);
        $row = $st->fetch();
        return ($row && password_verify($password, $row['password'])) ? $row : false;
    }

    public function countCustomers()
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    }
}
