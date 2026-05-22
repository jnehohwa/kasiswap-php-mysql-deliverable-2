<?php
require_once __DIR__ . '/_bootstrap.php';

$user = require_role(['seller', 'admin']);
$listingId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$listing = $listingId ? get_listing($listingId) : null;

if ($listing && $user['role'] !== 'admin' && (int) $listing['seller_id'] !== (int) $user['id']) {
    http_response_code(403);
    exit('You cannot edit this listing.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        foreach (['category_id', 'title', 'description', 'price', 'location_township', 'location_city'] as $field) {
            if (trim($_POST[$field] ?? '') === '') {
                throw new RuntimeException('Please complete every required listing field.');
            }
        }
        $id = create_or_update_listing($_POST, $user, $listingId ?: null, $_FILES['image'] ?? null);
        flash('success', 'Listing saved.');
        redirect('/seller-listings.php#listing-' . $id);
    } catch (Throwable $exception) {
        flash('danger', $exception->getMessage());
    }
}

$categories = get_categories();
render_header($listing ? 'Edit listing' : 'Create listing');
?>
<section class="page-heading">
    <p class="eyebrow">Seller workspace</p>
    <h1><?= $listing ? 'Edit listing' : 'Create listing' ?></h1>
</section>

<form class="panel wide" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $listingId ?>">
    <div class="form-grid two">
        <label>Category
            <select name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= selected((string) ($listing['category_id'] ?? ''), (string) $category['id']) ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Status
            <select name="status">
                <?php foreach (['active', 'reserved', 'sold', 'draft', 'hidden'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= selected((string) ($listing['status'] ?? 'active'), $status) ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Title <input name="title" value="<?= e($listing['title'] ?? '') ?>" required></label>
        <label>Price <input type="number" step="0.01" min="1" name="price" value="<?= e((string) ($listing['price'] ?? '')) ?>" required></label>
        <label>Condition
            <select name="item_condition">
                <?php foreach (['new', 'like_new', 'good', 'fair'] as $condition): ?>
                    <option value="<?= e($condition) ?>" <?= selected((string) ($listing['item_condition'] ?? 'good'), $condition) ?>><?= e(str_replace('_', ' ', $condition)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Image upload <input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
        <label>Township <input name="location_township" value="<?= e($listing['location_township'] ?? $user['township'] ?? '') ?>" required></label>
        <label>City <input name="location_city" value="<?= e($listing['location_city'] ?? $user['city'] ?? '') ?>" required></label>
    </div>
    <label>Description <textarea name="description" rows="6" required><?= e($listing['description'] ?? '') ?></textarea></label>
    <div class="checkbox-row">
        <label><input type="checkbox" name="accepts_escrow" value="1" <?= checked((bool) ($listing['accepts_escrow'] ?? true)) ?>> Accept sandbox payment hold</label>
        <label><input type="checkbox" name="accepts_delivery" value="1" <?= checked((bool) ($listing['accepts_delivery'] ?? true)) ?>> Accept delivery</label>
    </div>
    <button class="button primary" type="submit">Save listing</button>
</form>
<?php render_footer(); ?>
