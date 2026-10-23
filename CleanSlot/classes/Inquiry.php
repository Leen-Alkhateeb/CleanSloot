<?php
/** Messages from the Contact, Support, B2B and Franchise forms */
class Inquiry
{
    const TOPICS = ['contact' => 'Contact', 'support' => 'Support', 'b2b' => 'B2B Care', 'franchise' => 'Franchise'];

    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create($topic, $d)
    {
        $st = $this->db->prepare('INSERT INTO inquiries (topic, name, email, phone, company, message) VALUES (?, ?, ?, ?, ?, ?)');
        return $st->execute([$topic, $d['name'], $d['email'], $d['phone'], $d['company'], $d['message']]);
    }

    public function latest($limit, $topic = '')
    {
        $sql    = 'SELECT * FROM inquiries' . ($topic !== '' ? ' WHERE topic = ?' : '') . ' ORDER BY id DESC LIMIT ' . (int) $limit;
        $st     = $this->db->prepare($sql);
        $st->execute($topic !== '' ? [$topic] : []);
        return $st->fetchAll();
    }

    public function delete($id)
    {
        return $this->db->prepare('DELETE FROM inquiries WHERE id = ?')->execute([(int) $id]);
    }

    public function count()
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM inquiries')->fetchColumn();
    }
}
