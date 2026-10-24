<?php

class Inquiry
{
    // المواضيع المسموحة: المفتاح بيتخزن بالقاعدة، والقيمة بتظهر للمستخدم
    const TOPICS = ['contact' => 'Contact', 'support' => 'Support', 'b2b' => 'B2B Care', 'franchise' => 'Franchise'];

    
    // بيشتغل أوتوماتيك عند: new Inquiry($db)
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // حفظ رسالة جديدة (القيم بتنحط مكان الـ ? لحماية من SQL Injection)
    public function create($topic, $d)
    {
        $st = $this->db->prepare('INSERT INTO inquiries (topic, name, email, phone, company, message) VALUES (?, ?, ?, ?, ?, ?)');
        return $st->execute([$topic, $d['name'], $d['email'], $d['phone'], $d['company'], $d['message']]);
    }

    // جلب آخر الرسائل (الأحدث أولاً)، وإذا في $topic بتفلتر عليه
    public function latest($limit, $topic = '')
    {
        // WHERE بتنضاف فقط إذا في موضوع، و(int) للأمان لأن LIMIT مكتوب مباشرة
        $sql    = 'SELECT * FROM inquiries' . ($topic !== '' ? ' WHERE topic = ?' : '') . ' ORDER BY id DESC LIMIT ' . (int) $limit;
        $st     = $this->db->prepare($sql);
        $st->execute($topic !== '' ? [$topic] : []);
        return $st->fetchAll();
    }

    // حذف رسالة برقمها
    public function delete($id)
    {
        return $this->db->prepare('DELETE FROM inquiries WHERE id = ?')->execute([(int) $id]);
    }

    // عدد كل الرسائل
    public function count()
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM inquiries')->fetchColumn();
    }
}