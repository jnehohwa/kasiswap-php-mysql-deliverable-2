<?php
require_once __DIR__ . '/../_bootstrap.php';

$admin = require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $status = in_array($_POST['status'] ?? '', ['active', 'reserved', 'sold', 'draft', 'hidden'], true) ? $_POST['status'] : 'hidden';
    $listingId = (int) $_POST['listing_id'];
    $stmt = db()->prepare('UPDATE listings SET status = ? WHERE id = ?');
    $stmt->execute([$status, $listingId]);
    log_audit('moderate_listing', 'listing', $listingId, 'Admin changed listing status to ' . $status . '.', (int) $admin['id']);
    flash('success', 'Listing moderated.');
    redirect('/admin/listings.php');
}

$listings = db()->query(
    'SELECT l.*, c.name AS category_name, u.full_name AS seller_name, sp.store_name
     FROM listings l
     JOIN categories c ON c.id = l.category_id
     JOIN users u ON u.id = l.seller_id
     LEFT JOIN seller_profiles sp ON sp.user_id = u.id
     ORDER BY l.created_at DESC'
)->fetchAll();

render_header('Admin listings');
?>
<section class="page-heading">
    <p class="eyebrow">Admin</p>
    <h1>Listing moderation</h1>
</section>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Listing</th><th>Seller</th><th>Price</th><th>Status</th><th>Moderate</th></tr></thead>
            <tbody>
                <?php foreach ($listings as $listing): ?>
                    <tr>
                        <td><?= e($listing['title']) ?><br><span><?= e($listing['category_name']) ?></span></td>
                        <td><?= e($listing['store_name'] ?? $listing['seller_name']) ?></td>
                        <td><?= money($listing['price']) ?></td>
                        <td><?= status_badge($listing['status']) ?></td>
                        <td>
                            <form class="inline-form" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="listing_id" value="<?= (int) $listing['id'] ?>">
                                <select name="status">
                                    <?php foreach (['active', 'reserved', 'sold', 'draft', 'hidden'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= selected($listing['status'], $status) ?>><?= e($status) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="button secondary" type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php render_footer(); ?>
