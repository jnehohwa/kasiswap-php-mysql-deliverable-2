<?php
require_once __DIR__ . '/_bootstrap.php';

$pdo = db();
$stats = [
    'listings' => (int) $pdo->query('SELECT COUNT(*) FROM listings WHERE status = "active"')->fetchColumn(),
    'sellers' => (int) $pdo->query('SELECT COUNT(*) FROM users WHERE role = "seller" AND status = "active"')->fetchColumn(),
    'protected' => (float) $pdo->query('SELECT COALESCE(SUM(amount + buyer_protection_fee), 0) FROM orders WHERE state IN ("in_escrow", "shipped", "delivered")')->fetchColumn(),
];
$categories = get_categories();
$featured = list_marketplace(['sort' => 'newest']);
$spotlight = $featured[0] ?? null;
$quickCategories = array_slice($categories, 0, 5);

render_header(APP_NAME);
?>
<section class="hero marketplace-hero">
    <div class="hero-copy">
        <p class="hero-kicker">Fresh finds around your area</p>
        <h1>Find it nearby. Buy it with confidence.</h1>
        <p class="hero-lead">Search local deals, message the seller, and keep the whole order trail in one place from first chat to handover.</p>

        <form class="hero-search" action="/marketplace.php" method="get">
            <label class="sr-only" for="home-search">Search KasiSwap</label>
            <input id="home-search" name="q" placeholder="Search phones, sneakers, couches..." autocomplete="off">
            <button class="button primary" type="submit">Search</button>
        </form>

        <div class="quick-categories" aria-label="Popular categories">
            <span>Popular now</span>
            <?php foreach ($quickCategories as $category): ?>
                <a href="/marketplace.php?category_id=<?= (int) $category['id'] ?>"><?= e($category['name']) ?></a>
            <?php endforeach; ?>
        </div>

        <div class="hero-actions">
            <a class="button secondary" href="/marketplace.php">Browse everything</a>
            <a class="button secondary" href="/auth.php">List an item</a>
        </div>
    </div>

    <aside class="deal-board" aria-label="Trending local listings">
        <div class="deal-board-header">
            <div>
                <span>Trending nearby</span>
                <strong>Hot local deals</strong>
            </div>
            <a href="/marketplace.php">View all</a>
        </div>

        <?php if ($spotlight): ?>
            <a class="spotlight-card" href="/listing.php?id=<?= (int) $spotlight['id'] ?>">
                <img src="<?= e($spotlight['image_path']) ?>" alt="<?= e($spotlight['title']) ?>">
                <div>
                    <span><?= e($spotlight['category_name']) ?></span>
                    <h2><?= e($spotlight['title']) ?></h2>
                    <p><?= e($spotlight['location_township']) ?>, <?= e($spotlight['location_city']) ?></p>
                    <strong><?= money($spotlight['price']) ?></strong>
                </div>
            </a>
        <?php endif; ?>

        <div class="deal-list">
            <?php foreach (array_slice($featured, 1, 3) as $listing): ?>
                <a href="/listing.php?id=<?= (int) $listing['id'] ?>">
                    <img src="<?= e($listing['image_path']) ?>" alt="<?= e($listing['title']) ?>">
                    <span>
                        <strong><?= e($listing['title']) ?></strong>
                        <small><?= money($listing['price']) ?> · <?= e($listing['location_township']) ?></small>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </aside>
</section>

<section class="stats-grid home-stats">
    <article><strong><?= $stats['listings'] ?></strong><span>Listings live today</span></article>
    <article><strong><?= $stats['sellers'] ?></strong><span>Local sellers onboard</span></article>
    <article><strong><?= money($stats['protected']) ?></strong><span>Orders currently tracked</span></article>
    <article><strong>3 steps</strong><span>Search, chat, hand over</span></article>
</section>

<section class="section">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Featured</p>
            <h2>Fresh from local sellers</h2>
        </div>
        <a href="/marketplace.php">View all</a>
    </div>
    <div class="listing-grid">
        <?php foreach (array_slice($featured, 0, 4) as $listing): ?>
            <?php include __DIR__ . '/partials/listing-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<section class="trust-band">
    <article>
        <h2>Meet up, buy smart, keep proof.</h2>
        <p>KasiSwap keeps the important parts of a local deal together: seller profiles, chat history, order updates, dispute records, and admin review when something does not feel right.</p>
    </article>
    <ul class="trust-list">
        <li><strong>Chat first</strong><span>Ask questions before committing.</span></li>
        <li><strong>Check the seller</strong><span>See profile and verification status.</span></li>
        <li><strong>Track the order</strong><span>Follow payment, shipping, and delivery steps.</span></li>
        <li><strong>Raise a dispute</strong><span>Keep a record if a deal goes sideways.</span></li>
    </ul>
</section>
<?php render_footer(); ?>
