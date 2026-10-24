<?php
// قائمة الخدمات والمنتجات الجاهزة
// الترتيب: kind, type, category, subcategory, name, description, price, stains
function sample_items()
{
    $eco = 'EcoClean (DryClean)';

    return [
        ['service', $eco, 'Bedding', 'Duvets', 'Single Duvet', 'Cleaned, folded & packed', 5.00, 2],
        ['service', $eco, 'Bedding', 'Duvets', 'Double Duvet', 'Cleaned, folded & packed', 7.00, 2],
        ['service', $eco, 'Suits', '', 'Suit (2 pieces)', 'Jacket and trousers, pressed and delivered on a hanger', 6.00, 4],
        ['service', $eco, 'Tops', '', 'Shirt', 'Cleaned, pressed and hung', 1.50, 4],
        ['service', $eco, 'Tops', '', 'T-Shirt', 'Cleaned, folded and packed', 1.00, 3],
        ['service', $eco, 'Bottoms', '', 'Trousers', 'Cleaned and pressed with a sharp crease', 2.00, 3],
        ['service', $eco, 'Dresses and More', '', 'Dress', 'Delicate care for evening and daily dresses', 5.00, 4],
        ['service', 'Iron Only', 'Tops', '', 'Shirt Ironing', 'Crisp, wrinkle-free, delivered on a hanger', 0.80, 1],
        ['service', 'Shoe Care', 'Footwear', '', 'Sneakers Cleaning', 'Deep clean for sneakers and sports shoes', 5.00, 4],
        ['service', 'Carpets', 'Home', '', 'Carpet (per m2)', 'Deep clean with fresh-air drying', 3.00, 4],
        ['service', 'Wash and Fold per KG (weight)', 'Everyday', '', 'Wash & Fold (per kg)', 'Washed, dried and neatly folded', 1.50, 3],

        ['product', 'Shop', 'Laundry Care', '', 'Eco Laundry Detergent 2L', 'Plant-based, gentle on skin and fabrics', 6.50, 3],
        ['product', 'Shop', 'Accessories', '', 'Laundry Bag', 'Strong cotton bag with drawstring', 4.00, 3],
    ];
}

// يعبّي جدول items مرة وحدة إذا كان فاضي
function seed_items_if_empty(PDO $db)
{
    // إذا الجدول فيه بيانات، ما نعمل شي
    if ((int) $db->query('SELECT COUNT(*) FROM items')->fetchColumn() > 0) {
        return;
    }

    $st = $db->prepare(
        'INSERT INTO items (kind, type, category, subcategory, name, description, price, stains)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    foreach (sample_items() as $row) {
        $st->execute($row);
    }
}

// يتأكد في أدمن: admin@cleanslot.com / Admin@12345 (غيّر الباسورد بعد أول دخول)
function ensure_admin(PDO $db)
{
    // إذا في أدمن، ما نعمل شي
    if ((int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() > 0) {
        return;
    }

    $email = 'admin@cleanslot.com';
    $hash  = password_hash('Admin@12345', PASSWORD_DEFAULT);

    // إذا الإيميل موجود كزبون: رقّيه لأدمن
    $up = $db->prepare("UPDATE users SET role = 'admin', password = ? WHERE email = ?");
    $up->execute([$hash, $email]);

    // إذا مش موجود: أنشئ حساب جديد
    if ($up->rowCount() === 0) {
        $db->prepare("INSERT INTO users (name, email, password, role) VALUES ('Admin', ?, ?, 'admin')")->execute([$email, $hash]);
    }
}