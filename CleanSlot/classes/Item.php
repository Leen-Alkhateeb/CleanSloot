<?php

class Item
{
    // اتصال القاعدة
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // جلب عنصر واحد برقمه (إذا مش موجود بيرجع null)
    public function find($id)
    {
        $st = $this->db->prepare('SELECT * FROM items WHERE id = ?');
        $st->execute([(int) $id]);
        return $st->fetch() ?: null;
    }

    // كل العناصر (الظاهرة والمخفية) لجدول الأدمن
    public function all()
    {
        return $this->db->query('SELECT * FROM items ORDER BY kind, type, category, name')->fetchAll();
    }

    /** Distinct values for the filters: values('type', 'service') */
    // القيم المميزة لعمود معين (مثل كل الـ types) لأزرار الفلترة، للعناصر الظاهرة فقط
    public function values($column, $kind, array $where = [])
    {
        $sql    = "SELECT $column FROM items WHERE active = 1 AND kind = ? AND $column != ''";
        $params = [$kind];

        // شروط إضافية اختيارية (مثلاً type معين)
        foreach ($where as $col => $val) {
            $sql     .= " AND $col = ?";
            $params[] = $val;
        }

        // GROUP BY بيشيل التكرار، والترتيب حسب أول ظهور
        $st = $this->db->prepare($sql . " GROUP BY $column ORDER BY MIN(id)");
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Filters used by the Services and Shop pages */
    // بحث وفلترة للزبائن (active = 1: الظاهر فقط)
    public function search($kind, $f)
    {
        $sql    = 'SELECT * FROM items WHERE active = 1 AND kind = ?';
        $params = [$kind];

        // نضيف شرط فقط للفلاتر اللي المستخدم اختارها
        foreach (['type', 'category', 'subcategory'] as $col) {
            if (($f[$col] ?? '') !== '') {
                $sql     .= " AND $col = ?";
                $params[] = $f[$col];
            }
        }

        // بحث بالاسم أو الوصف
        if (($f['q'] ?? '') !== '') {
            $sql     .= ' AND (name LIKE ? OR description LIKE ?)';
            $params[] = '%' . $f['q'] . '%';
            $params[] = '%' . $f['q'] . '%';
        }

        // أعلى سعر (فقط إذا القيمة رقم)
        if (is_numeric($f['max'] ?? '')) {
            $sql     .= ' AND price <= ?';
            $params[] = $f['max'];
        }

        // الترتيب من قائمة بيضاء (أمان): أي قيمة غريبة بتصير الترتيب الافتراضي
        $orders = ['price_asc' => 'price ASC', 'price_desc' => 'price DESC', 'name' => 'name ASC'];
        $sql   .= ' ORDER BY ' . ($orders[$f['sort'] ?? ''] ?? 'category, id');

        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    // أول عدد من الخدمات الظاهرة (للصفحة الرئيسية)
    public function featured($limit)
    {
        return $this->db->query("SELECT * FROM items WHERE active = 1 AND kind = 'service' ORDER BY id LIMIT " . (int) $limit)->fetchAll();
    }

    /** Cleans the admin form input. Returns [data, errors] */
    // تنظيف وفحص مدخلات فورم الأدمن، وبترجع [البيانات, الأخطاء]
    public static function validate(array $in)
    {
        // trim بتشيل المسافات، و?? بتعطي قيمة افتراضية إذا الحقل ناقص
        $data = [
            'kind'        => $in['kind'] ?? 'service',
            'type'        => trim($in['type'] ?? ''),
            'category'    => trim($in['category'] ?? ''),
            'subcategory' => trim($in['subcategory'] ?? ''),
            'name'        => trim($in['name'] ?? ''),
            'description' => trim($in['description'] ?? ''),
            'price'       => trim($in['price'] ?? ''),
            'stains'      => (int) ($in['stains'] ?? 3),
            'image'       => trim($in['image'] ?? ''),
            // المربع غير المؤشّر ما بينبعت أصلاً، فبيصير 0 (مخفي)
            'active'      => empty($in['active']) ? 0 : 1,
        ];
        $errors = [];

        // kind لازم يكون service أو product فقط
        if (!in_array($data['kind'], ['service', 'product'], true)) {
            $errors['kind'] = 'Choose a valid kind.';
        }

        // الحقول الإلزامية مع أقصى طول لكل وحد
        foreach (['type' => 60, 'category' => 60, 'name' => 100] as $field => $max) {
            if ($data[$field] === '' || mb_strlen($data[$field]) > $max) {
                $errors[$field] = ucfirst($field) . " is required (max $max characters).";
            }
        }

        if (mb_strlen($data['subcategory']) > 60 || mb_strlen($data['description']) > 255) {
            $errors['description'] = 'Sub-category (max 60) or description (max 255) is too long.';
        }

        // السعر لازم رقم وما يكون سالب
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            $errors['price'] = 'Enter a valid price.';
        }

        // تقييم البقع محصور بين 1 و5
        $data['stains'] = min(5, max(1, $data['stains']));

        // الصورة مقبولة بحالتين فقط: رابط http(s) أو ملف مرفوع بصيغة uploads/اسم-الملف
        $isLink   = preg_match('#^https?://#i', $data['image']);
        $isUpload = preg_match('#^uploads/[A-Za-z0-9._-]+$#', $data['image']);
        if ($data['image'] !== '' && (!($isLink || $isUpload) || strlen($data['image']) > 255)) {
            $errors['image'] = 'Image must be a link starting with http:// or https://';
        }

        return [$data, $errors];
    }

    // إضافة عنصر جديد (الصورة الفاضية بتتخزن NULL)
    public function create($d)
    {
        $st = $this->db->prepare(
            'INSERT INTO items (kind, type, category, subcategory, name, description, price, stains, image, active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        return $st->execute([$d['kind'], $d['type'], $d['category'], $d['subcategory'], $d['name'], $d['description'], $d['price'], $d['stains'], $d['image'] ?: null, $d['active']]);
    }

    // تعديل عنصر موجود (آخر قيمة $id للـ WHERE)
    public function update($id, $d)
    {
        $st = $this->db->prepare(
            'UPDATE items SET kind = ?, type = ?, category = ?, subcategory = ?, name = ?, description = ?,
             price = ?, stains = ?, image = ?, active = ? WHERE id = ?'
        );
        return $st->execute([$d['kind'], $d['type'], $d['category'], $d['subcategory'], $d['name'], $d['description'], $d['price'], $d['stains'], $d['image'] ?: null, $d['active'], $id]);
    }

    // حذف عنصر (إذا مرتبط بطلبات القاعدة بترفض، وصفحة الأدمن بتمسك الخطأ)
    public function delete($id)
    {
        return $this->db->prepare('DELETE FROM items WHERE id = ?')->execute([$id]);
    }
}