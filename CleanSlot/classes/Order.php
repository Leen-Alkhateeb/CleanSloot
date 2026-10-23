<?php
class Order
{
    private $db;

    // One row per order, with the customer name and the ordered items as text
    private $listSql = "SELECT o.*, u.name AS customer, u.phone,
            GROUP_CONCAT(CONCAT(oi.name, ' x ', oi.qty) SEPARATOR ', ') AS items
        FROM orders o
        JOIN users u ON u.id = o.user_id
        JOIN order_items oi ON oi.order_id = o.id ";

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /** Saves the order and its items in one transaction. $rows come from cart_rows() */
    public function create($userId, array $rows, $date, $slot, $address, $notes, $method)
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $date);
        if (!$rows) {
            throw new DomainException('Your cart is empty.');
        }
        if (!$parsed || $parsed->format('Y-m-d') !== $date || $date < date('Y-m-d')) {
            throw new DomainException('Choose a valid pickup date.');
        }
        if (!in_array($slot, SLOTS, true)) {
            throw new DomainException('Choose a pickup time.');
        }
        if (slot_has_passed($date, $slot)) {
            throw new DomainException('That time has already passed. Please choose a later one.');
        }
        if (mb_strlen($address) < 5 || mb_strlen($address) > 255) {
            throw new DomainException('Please enter your address.');
        }
        if (!in_array($method, ['cash', 'card'], true)) {
            throw new DomainException('Choose a payment method.');
        }

        $total = array_sum(array_column($rows, 'line'));

        $this->db->beginTransaction();
        try {
            $this->db->prepare('INSERT INTO orders (user_id, pickup_date, time_slot, address, notes, payment_method, total) VALUES (?, ?, ?, ?, ?, ?, ?)')
                     ->execute([$userId, $date, $slot, $address, mb_substr($notes, 0, 255), $method, $total]);
            $id = $this->db->lastInsertId();

            $st = $this->db->prepare('INSERT INTO order_items (order_id, name, price, qty) VALUES (?, ?, ?, ?)');
            foreach ($rows as $r) {
                $st->execute([$id, $r['name'], $r['price'], $r['qty']]);
            }
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function byUser($userId)
    {
        $st = $this->db->prepare($this->listSql . 'WHERE o.user_id = ? GROUP BY o.id, u.name, u.phone ORDER BY o.id DESC');
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    public function all()
    {
        return $this->db->query($this->listSql . 'GROUP BY o.id, u.name, u.phone ORDER BY o.id DESC')->fetchAll();
    }

    /** A customer can cancel only their own pending orders */
    public function cancel($id, $userId)
    {
        $st = $this->db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
        $st->execute([$id, $userId]);
        return $st->rowCount() > 0;
    }

    public function setStatus($id, $status)
    {
        if (!in_array($status, ORDER_STATUSES, true)) {
            return false;
        }
        $this->db->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
        return true;
    }

    /** Numbers for the admin dashboard */
    public function stats()
    {
        $count = fn($sql) => $this->db->query($sql)->fetchColumn();
        return [
            'orders'  => (int) $count('SELECT COUNT(*) FROM orders'),
            'pending' => (int) $count("SELECT COUNT(*) FROM orders WHERE status = 'pending'"),
            'revenue' => (float) $count("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'completed'"),
        ];
    }
}
