<?php
require_once __DIR__ . '/_bootstrap.php';

$filters = [
    'q' => trim($_GET['q'] ?? ''),
    'category_id' => $_GET['category_id'] ?? '',
    'township' => $_GET['township'] ?? '',
    'sort' => $_GET['sort'] ?? 'newest',
];
$categories = get_categories();
$townships = list_townships();
$listings = list_marketplace($filters);

render_header('Marketplace');
?>
<section class="page-heading">
    <p class="eyebrow">Marketplace</p>
    <h1>Browse trusted local listings</h1>
    <p><?= count($listings) ?> listings match your current filters.</p>
</section>

<form class="filter-bar" method="get">
    <label>
        Search
        <input name="q" value="<?= e($filters['q']) ?>" placeholder="Item, seller, township">
    </label>
    <label>
        Category
        <select name="category_id">
            <option value="">All categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= selected((string) $filters['category_id'], (string) $category['id']) ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Township
        <select name="township">
            <option value="">All townships</option>
            <?php foreach ($townships as $township): ?>
                <option value="<?= e($township) ?>" <?= selected((string) $filters['township'], (string) $township) ?>><?= e($township) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Sort
        <select name="sort">
            <option value="newest" <?= selected($filters['sort'], 'newest') ?>>Newest</option>
            <option value="price_asc" <?= selected($filters['sort'], 'price_asc') ?>>Price low to high</option>
            <option value="price_desc" <?= selected($filters['sort'], 'price_desc') ?>>Price high to low</option>
        </select>
    </label>
    <button class="button primary" type="submit">Apply</button>
</form>

<?php if (!$listings): ?>
    <section class="panel narrow">
        <h2>No listings found</h2>
        <p>Try a different search or category.</p>
    </section>
<?php else: ?>
    <section class="listing-grid">
        <?php foreach ($listings as $listing): ?>
            <?php include __DIR__ . '/partials/listing-card.php'; ?>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
<?php render_footer(); ?>
