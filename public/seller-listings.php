<?php
require_once __DIR__ . '/_bootstrap.php';

$user = require_role(['seller', 'admin']);
$where = $user['role'] === 'admin' ? '1 = 1' : 'l.seller_id = ?';
$params = $user['role'] === 'admin' ? [] : [(int) $user['id']];
$stmt = db()->prepare(
    "SELECT l.*, c.name AS category_name
     FROM listings l
     JOIN categories c ON c.id = l.category_id
     WHERE $where
     ORDER BY l.created_at DESC"
);
$stmt->execute($params);

render_header('Seller listings');
?>
<section class="page-heading">
    <p class="eyebrow">Seller workspace</p>
    <h1>Manage listings</h1>
    <p>Create, update, hide, or monitor C2C listings.</p>
    <a class="button primary" href="/seller-listing-form.php">Create listing</a>
</section>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Category</th><th>Price</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($stmt->fetchAll() as $listing): ?>
                    <tr>
                        <td><?= e($listing['title']) ?></td>
                        <td><?= e($listing['category_name']) ?></td>
                        <td><?= money($listing['price']) ?></td>
                        <td><?= status_badge($listing['status']) ?></td>
                        <td><a href="/seller-listing-form.php?id=<?= (int) $listing['id'] ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php render_footer(); ?>
