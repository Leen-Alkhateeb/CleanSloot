<?php
class Order
{
    // اتصال القاعدة
    private $db;

    // استعلام جاهز بيجيب الطلبات: صف لكل طلب، مع اسم الزبون وهاتفه
    // GROUP_CONCAT بتجمع عناصر الطلب بنص واحد مثل: "Wash x 2, Ironing x 1"
    private $listSql = "SELECT o.*, u.name AS customer, u.phone,
            GROUP_CONCAT(CONCAT(oi.name, ' x ', oi.qty) SEPARATOR ', ') AS items
        FROM orders o
        JOIN users u ON u.id = o.user_id
        JOIN order_items oi ON oi.order_id = o.id ";

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ---------- إنشاء طلب جديد ----------
    // $rows هي محتويات السلة (الاسم، السعر، الكمية، مجموع السطر)
    public function create($userId, array $rows, $date, $slot, $address, $notes, $method)
    {
        // تحويل التاريخ لكائن عشان نتأكد إنه تاريخ حقيقي
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $date);

        // --- فحص المدخلات: أي خطأ بيطلع رسالة وبيوقف ---
        if (!$rows) {
            throw new DomainException('Your cart is empty.');
        }
        // التاريخ لازم يكون صحيح ومش بالماضي
        if (!$parsed || $parsed->format('Y-m-d') !== $date || $date < date('Y-m-d')) {
            throw new DomainException('Choose a valid pickup date.');
        }
        // الفترة لازم تكون من الفترات المسموحة (SLOTS)
        if (!in_array($slot, SLOTS, true)) {
            throw new DomainException('Choose a pickup time.');
        }
        // الفترة مش فايتة (مثلاً اليوم الساعة 3 والفترة الساعة 10)
        if (slot_has_passed($date, $slot)) {
            throw new DomainException('That time has already passed. Please choose a later one.');
        }
        if (mb_strlen($address) < 5 || mb_strlen($address) > 255) {
            throw new DomainException('Please enter your address.');
        }
        // طريقة الدفع: كاش أو بطاقة فقط
        if (!in_array($method, ['cash', 'card'], true)) {
            throw new DomainException('Choose a payment method.');
        }

        // مجموع الطلب = مجموع أسعار كل الأسطر
        $total = array_sum(array_column($rows, 'line'));

        // Transaction: يا بتنحفظ الطلب مع عناصره كلهم، يا ولا شي
        $this->db->beginTransaction();
        try {
            // 1) حفظ الطلب نفسه (الملاحظات بتنقص لـ 255 حرف)
            $this->db->prepare('INSERT INTO orders (user_id, pickup_date, time_slot, address, notes, payment_method, total) VALUES (?, ?, ?, ?, ?, ?, ?)')
                     ->execute([$userId, $date, $slot, $address, mb_substr($notes, 0, 255), $method, $total]);

            // رقم الطلب اللي انضاف للتو
            $id = $this->db->lastInsertId();

            // 2) حفظ كل عنصر بالطلب
            $st = $this->db->prepare('INSERT INTO order_items (order_id, name, price, qty) VALUES (?, ?, ?, ?)');
            foreach ($rows as $r) {
                $st->execute([$id, $r['name'], $r['price'], $r['qty']]);
            }

            // كله تمام: ثبّت التغييرات
            $this->db->commit();
            return $id;

        } catch (Throwable $e) {
            // صار خطأ: ألغِ كل شي انحفظ، وارمي الخطأ لفوق
            $this->db->rollBack();
            throw $e;
        }
    }

    // ---------- طلبات زبون معين (الأحدث أولاً) ----------
    public function byUser($userId)
    {
        $st = $this->db->prepare($this->listSql . 'WHERE o.user_id = ? GROUP BY o.id, u.name, u.phone ORDER BY o.id DESC');
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    // ---------- كل الطلبات (للأدمن) ----------
    public function all()
    {
        return $this->db->query($this->listSql . 'GROUP BY o.id, u.name, u.phone ORDER BY o.id DESC')->fetchAll();
    }

    // ---------- إلغاء طلب ----------
    // الزبون بيلغي طلبه هو بس، وبشرط يكون لسا pending
    public function cancel($id, $userId)
    {
        $st = $this->db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
        $st->execute([$id, $userId]);

        // rowCount: كم صف تعدّل. إذا 0 يعني الشروط ما انطبقت (فشل الإلغاء)
        return $st->rowCount() > 0;
    }

    // ---------- تغيير حالة طلب (للأدمن) ----------
    public function setStatus($id, $status)
    {
        // لازم الحالة تكون من الحالات المسموحة، وإلا بترجع false
        if (!in_array($status, ORDER_STATUSES, true)) {
            return false;
        }

        $this->db->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
        return true;
    }

    // ---------- أرقام لوحة التحكم ----------
    public function stats()
    {
        // عدد كل الطلبات
        $orders = (int) $this->db->query('SELECT COUNT(*) FROM orders')->fetchColumn();

        // عدد الطلبات المعلّقة
        $pending = (int) $this->db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

        // إيراد الطلبات المكتملة (COALESCE: إذا ما في طلبات بيرجع 0 بدل NULL)
        $revenue = (float) $this->db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'completed'")->fetchColumn();

        return [
            'orders'  => $orders,
            'pending' => $pending,
            'revenue' => $revenue,
        ];
    }
}