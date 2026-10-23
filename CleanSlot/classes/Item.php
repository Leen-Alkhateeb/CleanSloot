<?php
/** Services and shop products (one table, the "kind" column tells them apart) */
class Item
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function find($id)
    {
        $st = $this->db->prepare('SELECT * FROM items WHERE id = ?');
        $st->execute([(int) $id]);
        return $st->fetch() ?: null;
    }

    public function all()
    {
        return $this->db->query('SELECT * FROM items ORDER BY kind, type, category, name')->fetchAll();
    }

    /** Distinct values for the filters: values('type', 'service') */
    public function values($column, $kind, array $where = [])
    {
        $sql    = "SELECT $column FROM items WHERE active = 1 AND kind = ? AND $column != ''";
        $params = [$kind];
        foreach ($where as $col => $val) {
            $sql     .= " AND $col = ?";
            $params[] = $val;
        }
        $st = $this->db->prepare($sql . " GROUP BY $column ORDER BY MIN(id)");
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Filters used by the Services and Shop pages */
    public function search($kind, $f)
    {
        $sql    = 'SELECT * FROM items WHERE active = 1 AND kind = ?';
        $params = [$kind];

        foreach (['type', 'category', 'subcategory'] as $col) {
            if (($f[$col] ?? '') !== '') {
                $sql     .= " AND $col = ?";
                $params[] = $f[$col];
            }
        }
        if (($f['q'] ?? '') !== '') {
            $sql     .= ' AND (name LIKE ? OR description LIKE ?)';
            $params[] = '%' . $f['q'] . '%';
            $params[] = '%' . $f['q'] . '%';
        }
        if (is_numeric($f['max'] ?? '')) {
            $sql     .= ' AND price <= ?';
            $params[] = $f['max'];
        }

        $orders = ['price_asc' => 'price ASC', 'price_desc' => 'price DESC', 'name' => 'name ASC'];
        $sql   .= ' ORDER BY ' . ($orders[$f['sort'] ?? ''] ?? 'category, id');

        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function featured($limit)
    {
        return $this->db->query("SELECT * FROM items WHERE active = 1 AND kind = 'service' ORDER BY id LIMIT " . (int) $limit)->fetchAll();
    }

    /** Cleans the admin form input. Returns [data, errors] */
    public static function validate(array $in)
    {
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
            'active'      => empty($in['active']) ? 0 : 1,
        ];
        $errors = [];

        if (!in_array($data['kind'], ['service', 'product'], true)) {
            $errors['kind'] = 'Choose a valid kind.';
        }
        foreach (['type' => 60, 'category' => 60, 'name' => 100] as $field => $max) {
            if ($data[$field] === '' || mb_strlen($data[$field]) > $max) {
                $errors[$field] = ucfirst($field) . " is required (max $max characters).";
            }
        }
        if (mb_strlen($data['subcategory']) > 60 || mb_strlen($data['description']) > 255) {
            $errors['description'] = 'Sub-category (max 60) or description (max 255) is too long.';
        }
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            $errors['price'] = 'Enter a valid price.';
        }
        $data['stains'] = min(5, max(1, $data['stains']));

        $isLink   = preg_match('#^https?://#i', $data['image']);
        $isUpload = preg_match('#^uploads/[A-Za-z0-9._-]+$#', $data['image']);
        if ($data['image'] !== '' && (!($isLink || $isUpload) || strlen($data['image']) > 255)) {
            $errors['image'] = 'Image must be a link starting with http:// or https://';
        }
        return [$data, $errors];
    }

    public function create($d)
    {
        $st = $this->db->prepare(
            'INSERT INTO items (kind, type, category, subcategory, name, description, price, stains, image, active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        return $st->execute([$d['kind'], $d['type'], $d['category'], $d['subcategory'], $d['name'], $d['description'], $d['price'], $d['stains'], $d['image'] ?: null, $d['active']]);
    }

    public function update($id, $d)
    {
        $st = $this->db->prepare(
            'UPDATE items SET kind = ?, type = ?, category = ?, subcategory = ?, name = ?, description = ?,
             price = ?, stains = ?, image = ?, active = ? WHERE id = ?'
        );
        return $st->execute([$d['kind'], $d['type'], $d['category'], $d['subcategory'], $d['name'], $d['description'], $d['price'], $d['stains'], $d['image'] ?: null, $d['active'], $id]);
    }

    public function delete($id)
    {
        return $this->db->prepare('DELETE FROM items WHERE id = ?')->execute([$id]);
    }
}
