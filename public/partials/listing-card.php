<article class="listing-card">
    <a href="/listing.php?id=<?= (int) $listing['id'] ?>">
        <img src="<?= e($listing['image_path']) ?>" alt="<?= e($listing['title']) ?>" loading="lazy">
    </a>
    <div class="listing-card-body">
        <div class="row between">
            <span class="badge muted"><?= e($listing['category_name']) ?></span>
            <?= status_badge($listing['verification_level']) ?>
        </div>
        <h3><a href="/listing.php?id=<?= (int) $listing['id'] ?>"><?= e($listing['title']) ?></a></h3>
        <p><?= e($listing['location_township']) ?>, <?= e($listing['location_city']) ?></p>
        <div class="row between">
            <strong><?= money($listing['price']) ?></strong>
            <span><?= e($listing['store_name'] ?? 'Seller') ?></span>
        </div>
    </div>
</article>
