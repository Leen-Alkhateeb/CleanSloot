<?php
/**
 * Sample services and shop products (same data as schema.sql).
 * If the items table is completely empty the site fills it by itself, once.
 */

function sample_items()
{
    $eco = 'EcoClean (DryClean)';
    // kind, type, category, subcategory, name, description, price, stains
    return [
        ['service', $eco, 'Bedding', 'Blankets and Bed tops', 'Blanket Single', 'Disinfected, cleaned, folded & packed - size: 2m x 2m or more', 4.00, 2],
        ['service', $eco, 'Bedding', 'Blankets and Bed tops', 'Blanket Double', 'Disinfected, cleaned, folded & packed - size: 2m x 2m or more', 5.50, 2],
        ['service', $eco, 'Bedding', 'Duvets', 'Single Duvet', 'Cleaned, folded & packed', 5.00, 2],
        ['service', $eco, 'Bedding', 'Duvets', 'Double Duvet', 'Cleaned, folded & packed', 7.00, 2],
        ['service', $eco, 'Bedding', 'Pillows', 'Pillow', 'Cleaned, fluffed & packed', 2.00, 2],
        ['service', $eco, 'Suits', '', 'Suit (2 pieces)', 'Jacket and trousers, pressed and delivered on a hanger', 6.00, 4],
        ['service', $eco, 'Suits', '', 'Jacket', 'Gentle dry cleaning, pressed on a hanger', 3.50, 4],
        ['service', $eco, 'Tops', '', 'Shirt', 'Cleaned, pressed and hung', 1.50, 4],
        ['service', $eco, 'Tops', '', 'T-Shirt', 'Cleaned, folded and packed', 1.00, 3],
        ['service', $eco, 'Bottoms', '', 'Trousers', 'Cleaned and pressed with a sharp crease', 2.00, 3],
        ['service', $eco, 'Dresses and More', '', 'Dress', 'Delicate care for evening and daily dresses', 5.00, 4],
        ['service', $eco, 'Curtains', '', 'Curtain (per panel)', 'Cleaned, steamed and ready to hang', 6.00, 2],
        ['service', 'Iron Only', 'Tops', '', 'Shirt Ironing', 'Crisp, wrinkle-free, delivered on a hanger', 0.80, 1],
        ['service', 'Iron Only', 'Bottoms', '', 'Trousers Ironing', 'Pressed with a sharp crease', 1.00, 1],
        ['service', 'Iron Only', 'Dresses and More', '', 'Dress Ironing', 'Careful steam ironing', 2.00, 1],
        ['service', 'Shoe Care', 'Footwear', '', 'Sneakers Cleaning', 'Deep clean for sneakers and sports shoes', 5.00, 4],
        ['service', 'Shoe Care', 'Footwear', '', 'Leather Shoes Polish', 'Cleaned, conditioned and polished', 4.00, 3],
        ['service', 'Carpets', 'Home', '', 'Carpet (per m2)', 'Deep clean with fresh-air drying', 3.00, 4],
        ['service', 'Wash and Fold per KG (weight)', 'Everyday', '', 'Wash & Fold (per kg)', 'Washed, dried and neatly folded', 1.50, 3],
        ['service', 'House deep cleaning', 'Home', '', 'Apartment Deep Clean', 'Full deep cleaning by a trained team', 45.00, 5],
        ['service', 'Tailoring', 'Alterations', '', 'Trousers Hemming', 'Shortening and finishing by an expert tailor', 3.00, 1],
        ['service', 'Tailoring', 'Alterations', '', 'Zip Replacement', 'New zip fitted for jackets, dresses and bags', 4.00, 1],

        ['product', 'Shop', 'Laundry Care', '', 'Eco Laundry Detergent 2L', 'Plant-based, gentle on skin and fabrics', 6.50, 3],
        ['product', 'Shop', 'Laundry Care', '', 'Fabric Softener 2L', 'Soft, fresh scent that lasts', 5.00, 3],
        ['product', 'Shop', 'Laundry Care', '', 'Stain Remover Spray', 'Works on coffee, oil and grass stains', 3.50, 3],
        ['product', 'Shop', 'Accessories', '', 'Laundry Bag', 'Strong cotton bag with drawstring', 4.00, 3],
        ['product', 'Shop', 'Accessories', '', 'Wooden Hangers (set of 6)', 'Smooth wood, keeps shirts in shape', 7.00, 3],
        ['product', 'Shop', 'Accessories', '', 'Garment Cover', 'Keeps suits and dresses dust-free', 2.50, 3],
    ];
}

/** Fills the items table when it has no rows at all. Returns how many rows were added. */
function seed_items_if_empty(PDO $db)
{
    $marker = __DIR__ . '/.seeded';
    if (is_file($marker)) {
        return 0;            // already done once: never bring deleted items back
    }
    if ((int) $db->query('SELECT COUNT(*) FROM items')->fetchColumn() > 0) {
        return 0;
    }

    $st = $db->prepare(
        'INSERT INTO items (kind, type, category, subcategory, name, description, price, stains)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $rows = sample_items();
    foreach ($rows as $row) {
        $st->execute($row);
    }
    @file_put_contents($marker, date('c'));
    return count($rows);
}

/** Makes sure at least one admin exists: admin@cleanslot.com / Admin@12345 (change the password after login) */
function ensure_admin(PDO $db)
{
    $st = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    if ((int) $st->fetchColumn() > 0) {
        return false;
    }
    $email = 'admin@cleanslot.com';
    $hash  = password_hash('Admin@12345', PASSWORD_DEFAULT);

    // The email may already belong to a normal customer: promote it and reset its password
    $up = $db->prepare("UPDATE users SET role = 'admin', password = ? WHERE email = ?");
    $up->execute([$hash, $email]);
    if ($up->rowCount() === 0) {
        $db->prepare("INSERT INTO users (name, email, password, role) VALUES ('Admin', ?, ?, 'admin')")->execute([$email, $hash]);
    }
    return true;
}
