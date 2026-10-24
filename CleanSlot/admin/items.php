<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$items  = new Item($db);
$errors = [];

// إذا الرابط فيه ?edit=5 جيب العنصر عشان نعبي فيه الفورم
$editId = 0;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
}

$form = null;
if ($editId) {
    $form = $items->find($editId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // ---- حذف ----
    if (isset($_POST['delete'])) {
        $deleteId = (int) $_POST['delete'];
        $old = $items->find($deleteId);

        try {
            $items->delete($deleteId);

            // احذف صورته من السيرفر
            if ($old) {
                delete_upload($old['image']);
            }
            flash('Deleted.');
        } catch (PDOException $ex) {
            // مثلاً العنصر مرتبط بطلبات قديمة
            flash('This item could not be deleted.', 'error');
        }

        redirect(url('admin/items.php'));
    }

    // ---- إضافة أو تعديل ----
    $id = 0;
    if (isset($_POST['id'])) {
        $id = (int) $_POST['id'];
    }

    // بترجع مصفوفتين: البيانات النظيفة والأخطاء
    [$data, $errors] = Item::validate($_POST);

    // العنصر القديم (إذا تعديل)
    $existing = null;
    if ($id) {
        $existing = $items->find($id);
    }

    // الصورة المرفوعة: بترجع [اسم الملف, رسالة الخطأ]
    $file = [];
    if (isset($_FILES['photo'])) {
        $file = $_FILES['photo'];
    }
    [$uploaded, $uploadError] = save_upload($file);

    if ($uploadError) {
        $errors['image'] = $uploadError;
    }

    // ما في أخطاء: احفظ
    if (!$errors) {

        // الصورة المرفوعة بتحل محل الرابط
        if ($uploaded) {
            $data['image'] = $uploaded;
        }

        if ($id && $existing) {
            $items->update($id, $data);

            // احذف الصورة القديمة إذا انرفعت وحدة جديدة
            if ($uploaded && $existing['image'] !== $uploaded) {
                delete_upload($existing['image']);
            }
            flash('Saved.');
        } else {
            $items->create($data);
            flash('Added.');
        }

        redirect(url('admin/items.php'));
    }

    // في أخطاء: احذف الصورة اللي انرفعت وارجع اعرض الفورم بقيم المستخدم
    if ($uploaded) {
        delete_upload($uploaded);
    }
    $form       = $data;
    $form['id'] = $id;
}

// فورم فاضي للإضافة الجديدة
if (!$form) {
    $form = [
        'id'          => 0,
        'kind'        => 'service',
        'type'        => '',
        'category'    => '',
        'subcategory' => '',
        'name'        => '',
        'description' => '',
        'price'       => '',
        'stains'      => 3,
        'image'       => '',
        'active'      => 1,
    ];
}

$list       = $items->all();
$types      = array_unique(array_column($list, 'type'));      // اقتراحات Type
$categories = array_unique(array_column($list, 'category'));  // اقتراحات Category

$title    = 'Services & Shop | CleanSlot';
$active   = '';
$adminTab = 'items';
require __DIR__ . '/../includes/header.php';
?>

<main id="main" class="section">
    <div class="container stack-lg">
        <h1 class="h2">Services &amp; Shop</h1>

        <?php require __DIR__ . '/../includes/admin_tabs.php'; ?>

        <div class="admin-grid">

            <!-- فورم الإضافة والتعديل (enctype ضروري لرفع الملفات) -->
            <form method="post" enctype="multipart/form-data" class="panel stack">

                <?php if ($form['id']): ?>
                    <h2>Edit item</h2>
                <?php else: ?>
                    <h2>Add item</h2>
                <?php endif; ?>

                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">

                <label class="field"><span>Kind</span>
                    <select name="kind">
                        <option value="service" <?php if ($form['kind'] === 'service') echo 'selected'; ?>>Service</option>
                        <option value="product" <?php if ($form['kind'] === 'product') echo 'selected'; ?>>Shop product</option>
                    </select>
                </label>

                <label class="field"><span>Name</span>
                    <input name="name" value="<?= e($form['name']) ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <small class="field-error"><?= e($errors['name']) ?></small>
                    <?php endif; ?>
                </label>

                <label class="field"><span>Type (tab on the Services page, or "Shop")</span>
                    <input name="type" list="types" value="<?= e($form['type']) ?>" required>
                    <datalist id="types">
                        <?php foreach ($types as $t): ?>
                            <option value="<?= e($t) ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <?php if (isset($errors['type'])): ?>
                        <small class="field-error"><?= e($errors['type']) ?></small>
                    <?php endif; ?>
                </label>

                <div class="two">
                    <label class="field"><span>Category</span>
                        <input name="category" list="categories" value="<?= e($form['category']) ?>" required>
                        <datalist id="categories">
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= e($c) ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <?php if (isset($errors['category'])): ?>
                            <small class="field-error"><?= e($errors['category']) ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="field"><span>Sub-category</span>
                        <input name="subcategory" value="<?= e($form['subcategory']) ?>">
                    </label>
                </div>

                <label class="field"><span>Description</span>
                    <textarea name="description" rows="2" maxlength="255"><?= e($form['description']) ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <small class="field-error"><?= e($errors['description']) ?></small>
                    <?php endif; ?>
                </label>

                <div class="two">
                    <label class="field"><span>Price (JOD)</span>
                        <input type="number" name="price" min="0" step="0.05" value="<?= e($form['price']) ?>" required>
                        <?php if (isset($errors['price'])): ?>
                            <small class="field-error"><?= e($errors['price']) ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="field"><span>Stain rating (1-5)</span>
                        <input type="number" name="stains" min="1" max="5" value="<?= (int) $form['stains'] ?>">
                    </label>
                </div>

                <!-- صورة: إما رفع ملف أو رابط -->
                <label class="field"><span>Photo (JPG, PNG, WEBP, up to 2 MB)</span>
                    <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp">
                </label>

                <label class="field"><span>or photo link</span>
                    <input name="image" value="<?= e($form['image']) ?>" placeholder="https://...">
                    <?php if (isset($errors['image'])): ?>
                        <small class="field-error"><?= e($errors['image']) ?></small>
                    <?php endif; ?>
                </label>

                <img id="preview" class="preview" src="<?= e(item_image($form)) ?>" alt="Preview" <?= img_fallback() ?>>

                <label class="check">
                    <input type="checkbox" name="active" value="1" <?php if ($form['active']) echo 'checked'; ?>>
                    Visible on the website
                </label>

                <div class="actions">
                    <?php if ($form['id']): ?>
                        <button class="btn btn-primary" type="submit">Save changes</button>
                        <a class="btn btn-ghost" href="<?= url('admin/items.php') ?>">Cancel</a>
                    <?php else: ?>
                        <button class="btn btn-primary" type="submit">Add item</button>
                    <?php endif; ?>
                </div>
            </form>

            <!-- جدول كل العناصر -->
            <div class="panel">
                <h2>All items (<?= count($list) ?>)</h2>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Name</th><th>Type</th><th>Price</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($list as $i): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($i['name']) ?></strong>
                                        <?php if (!$i['active']): ?>
                                            <span class="badge badge-maintenance">Hidden</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="muted">
                                            <?php if ($i['kind'] === 'product'): ?>Shop<?php else: ?>Service<?php endif; ?>
                                            &middot; <?= e($i['category']) ?>
                                        </small>
                                    </td>

                                    <td><?= e($i['type']) ?></td>
                                    <td><?= money($i['price']) ?></td>

                                    <td class="row-actions">
                                        <a class="btn btn-outline btn-sm" href="<?= url('admin/items.php?edit=' . (int) $i['id']) ?>">Edit</a>

                                        <form method="post" onsubmit="return confirm('Delete this item?')">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-danger btn-sm" name="delete" value="<?= (int) $i['id'] ?>">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>