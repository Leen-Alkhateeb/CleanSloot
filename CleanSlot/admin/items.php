<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$items  = new Item($db);
$errors = [];
$editId = (int) ($_GET['edit'] ?? 0);
$form   = $editId ? $items->find($editId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // Delete
    if (isset($_POST['delete'])) {
        $old = $items->find((int) $_POST['delete']);
        try {
            $items->delete((int) $_POST['delete']);
            if ($old) {
                delete_upload($old['image']);
            }
            flash('Deleted.');
        } catch (PDOException $ex) {
            flash('This item could not be deleted.', 'error');
        }
        redirect(url('admin/items.php'));
    }

    // Add or edit
    $id           = (int) ($_POST['id'] ?? 0);
    [$data, $errors] = Item::validate($_POST);
    $existing     = $id ? $items->find($id) : null;

    // New picture uploaded? It replaces the link
    [$uploaded, $uploadError] = save_upload($_FILES['photo'] ?? []);
    if ($uploadError) {
        $errors['image'] = $uploadError;
    }

    if (!$errors) {
        if ($uploaded) {
            $data['image'] = $uploaded;
        }
        if ($id && $existing) {
            $items->update($id, $data);
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
    if ($uploaded) {
        delete_upload($uploaded);
    }
    $form = $data + ['id' => $id];
}

$form = $form ?: ['id' => 0, 'kind' => 'service', 'type' => '', 'category' => '', 'subcategory' => '', 'name' => '', 'description' => '', 'price' => '', 'stains' => 3, 'image' => '', 'active' => 1];
$list = $items->all();

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
            <form method="post" enctype="multipart/form-data" class="panel stack">
                <h2><?= $form['id'] ? 'Edit item' : 'Add item' ?></h2>
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">

                <label class="field"><span>Kind</span>
                    <select name="kind">
                        <option value="service" <?= $form['kind'] === 'service' ? 'selected' : '' ?>>Service</option>
                        <option value="product" <?= $form['kind'] === 'product' ? 'selected' : '' ?>>Shop product</option>
                    </select>
                </label>

                <label class="field"><span>Name</span>
                    <input name="name" value="<?= e($form['name']) ?>" required>
                    <?php if (isset($errors['name'])): ?><small class="field-error"><?= e($errors['name']) ?></small><?php endif; ?>
                </label>

                <label class="field"><span>Type (tab on the Services page, or "Shop")</span>
                    <input name="type" list="types" value="<?= e($form['type']) ?>" required>
                    <datalist id="types"><?php foreach (array_unique(array_column($list, 'type')) as $t): ?><option value="<?= e($t) ?>"><?php endforeach; ?></datalist>
                    <?php if (isset($errors['type'])): ?><small class="field-error"><?= e($errors['type']) ?></small><?php endif; ?>
                </label>

                <div class="two">
                    <label class="field"><span>Category</span>
                        <input name="category" list="categories" value="<?= e($form['category']) ?>" required>
                        <datalist id="categories"><?php foreach (array_unique(array_column($list, 'category')) as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
                        <?php if (isset($errors['category'])): ?><small class="field-error"><?= e($errors['category']) ?></small><?php endif; ?>
                    </label>
                    <label class="field"><span>Sub-category</span>
                        <input name="subcategory" value="<?= e($form['subcategory']) ?>">
                    </label>
                </div>

                <label class="field"><span>Description</span>
                    <textarea name="description" rows="2" maxlength="255"><?= e($form['description']) ?></textarea>
                    <?php if (isset($errors['description'])): ?><small class="field-error"><?= e($errors['description']) ?></small><?php endif; ?>
                </label>

                <div class="two">
                    <label class="field"><span>Price (JOD)</span>
                        <input type="number" name="price" min="0" step="0.05" value="<?= e($form['price']) ?>" required>
                        <?php if (isset($errors['price'])): ?><small class="field-error"><?= e($errors['price']) ?></small><?php endif; ?>
                    </label>
                    <label class="field"><span>Stain rating (1-5)</span>
                        <input type="number" name="stains" min="1" max="5" value="<?= (int) $form['stains'] ?>">
                    </label>
                </div>

                <label class="field"><span>Photo (JPG, PNG, WEBP, up to 2 MB)</span>
                    <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp">
                </label>
                <label class="field"><span>or photo link</span>
                    <input name="image" value="<?= e($form['image']) ?>" placeholder="https://...">
                    <?php if (isset($errors['image'])): ?><small class="field-error"><?= e($errors['image']) ?></small><?php endif; ?>
                </label>
                <img id="preview" class="preview" src="<?= e(item_image($form + ['type' => $form['type']])) ?>" alt="Preview" <?= img_fallback() ?>>

                <label class="check"><input type="checkbox" name="active" value="1" <?= $form['active'] ? 'checked' : '' ?>> Visible on the website</label>

                <div class="actions">
                    <button class="btn btn-primary" type="submit"><?= $form['id'] ? 'Save changes' : 'Add item' ?></button>
                    <?php if ($form['id']): ?><a class="btn btn-ghost" href="<?= url('admin/items.php') ?>">Cancel</a><?php endif; ?>
                </div>
            </form>

            <div class="panel">
                <h2>All items (<?= count($list) ?>)</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Name</th><th>Type</th><th>Price</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($list as $i): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($i['name']) ?></strong> <?= $i['active'] ? '' : '<span class="badge badge-maintenance">Hidden</span>' ?><br>
                                        <small class="muted"><?= $i['kind'] === 'product' ? 'Shop' : 'Service' ?> &middot; <?= e($i['category']) ?></small>
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
