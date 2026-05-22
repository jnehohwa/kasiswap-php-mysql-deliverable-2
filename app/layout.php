<?php
declare(strict_types=1);

function render_header(string $title = APP_NAME): void
{
    $user = current_user();
    $fullTitle = $title === APP_NAME ? APP_NAME : $title . ' | ' . APP_NAME;
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($fullTitle) ?></title>
        <meta name="description" content="KasiSwap helps people browse local deals, message sellers, and track neighbourhood marketplace orders.">
        <link rel="stylesheet" href="/assets/css/styles.css">
        <script src="/assets/js/app.js" defer></script>
    </head>
    <body>
        <header class="site-header">
            <a class="brand" href="/index.php">
                <span class="brand-mark">KS</span>
                <span>KasiSwap</span>
            </a>
            <button class="nav-toggle" type="button" data-nav-toggle aria-label="Open navigation">Menu</button>
            <nav class="site-nav" data-nav>
                <a class="<?= is_active('marketplace.php') ?>" href="/marketplace.php">Marketplace</a>
                <?php if ($user): ?>
                    <a class="<?= is_active('dashboard.php') ?>" href="/dashboard.php">Dashboard</a>
                    <a class="<?= is_active('orders.php') ?>" href="/orders.php">Orders</a>
                    <a class="<?= is_active('messages.php') ?>" href="/messages.php">Messages</a>
                    <?php if ($user['role'] === 'seller' || $user['role'] === 'admin'): ?>
                        <a class="<?= is_active('seller-listings.php') ?>" href="/seller-listings.php">Seller</a>
                    <?php endif; ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="/admin/index.php">Admin</a>
                    <?php endif; ?>
                    <a href="/logout.php">Logout</a>
                <?php else: ?>
                    <a class="<?= is_active('auth.php') ?>" href="/auth.php">Sign in</a>
                <?php endif; ?>
            </nav>
        </header>
        <main>
            <?php foreach (consume_flash() as $message): ?>
                <div class="flash <?= e($message['type']) ?>"><?= e($message['message']) ?></div>
            <?php endforeach; ?>
    <?php
}

function render_footer(): void
{
    ?>
        </main>
        <footer class="site-footer">
            <div class="footer-grid">
                <section>
                    <a class="brand footer-brand" href="/index.php">
                        <span class="brand-mark">KS</span>
                        <span>KasiSwap</span>
                    </a>
                    <p>Local deals, real conversations, and safer handovers in one neighbourhood marketplace.</p>
                </section>
                <section>
                    <h2>Shop</h2>
                    <a href="/marketplace.php">Browse marketplace</a>
                    <a href="/marketplace.php?sort=newest">New listings</a>
                    <a href="/auth.php">Create account</a>
                </section>
                <section>
                    <h2>Sell</h2>
                    <a href="/seller-listings.php">Seller dashboard</a>
                    <a href="/seller-listing-form.php">Post a listing</a>
                    <a href="/dashboard.php">My dashboard</a>
                </section>
                <section>
                    <h2>Support</h2>
                    <a href="/messages.php">Messages</a>
                    <a href="/orders.php">Orders</a>
                    <a href="/disputes.php">Disputes</a>
                </section>
            </div>
            <div class="footer-bottom">
                <span>&copy; <?= date('Y') ?> KasiSwap.</span>
                <span>Student-built marketplace prototype. No real payments are processed.</span>
            </div>
        </footer>
    </body>
    </html>
    <?php
}

function status_badge(string $value): string
{
    $class = match ($value) {
        'active', 'released', 'confirmed', 'approved', 'id_verified', 'premium' => 'success',
        'pending_payment', 'pending', 'under_review', 'in_escrow', 'shipped', 'delivered', 'phone' => 'warning',
        'disputed', 'refunded', 'rejected', 'hidden', 'suspended' => 'danger',
        default => 'muted',
    };

    return '<span class="badge ' . $class . '">' . e(str_replace('_', ' ', $value)) . '</span>';
}
