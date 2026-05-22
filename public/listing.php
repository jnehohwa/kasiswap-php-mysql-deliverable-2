<?php
require_once __DIR__ . '/_bootstrap.php';

$listing = get_listing((int) ($_GET['id'] ?? 0));
if (!$listing) {
    http_response_code(404);
    render_header('Listing not found');
    echo '<section class="panel narrow"><h1>Listing not found</h1><a class="button" href="/marketplace.php">Back to marketplace</a></section>';
    render_footer();
    exit;
}

render_header($listing['title']);
?>
<section class="detail-layout">
    <div class="detail-media">
        <img src="<?= e($listing['image_path']) ?>" alt="<?= e($listing['title']) ?>">
    </div>
    <article class="detail-panel">
        <div class="row between wrap">
            <span class="badge muted"><?= e($listing['category_name']) ?></span>
            <?= status_badge($listing['verification_level']) ?>
        </div>
        <h1><?= e($listing['title']) ?></h1>
        <p class="price"><?= money($listing['price']) ?></p>
        <p><?= e($listing['description']) ?></p>

        <dl class="meta-list">
            <div><dt>Condition</dt><dd><?= e(str_replace('_', ' ', $listing['item_condition'])) ?></dd></div>
            <div><dt>Location</dt><dd><?= e($listing['location_township']) ?>, <?= e($listing['location_city']) ?></dd></div>
            <div><dt>Seller</dt><dd><?= e($listing['store_name'] ?? $listing['seller_name']) ?></dd></div>
            <div><dt>Payment</dt><dd><?= $listing['accepts_escrow'] ? 'Sandbox payment hold accepted' : 'Direct arrangement only' ?></dd></div>
        </dl>

        <div class="actions">
            <a class="button primary" href="/checkout.php?listing_id=<?= (int) $listing['id'] ?>">Buy with protection</a>
            <a class="button secondary" href="/messages.php?listing_id=<?= (int) $listing['id'] ?>&seller_id=<?= (int) $listing['seller_id'] ?>">Message seller</a>
        </div>
    </article>
</section>
<?php render_footer(); ?>
