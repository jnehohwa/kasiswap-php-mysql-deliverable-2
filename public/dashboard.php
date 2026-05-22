<?php
require_once __DIR__ . '/_bootstrap.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request_verification') {
    verify_csrf();
    if ($user['role'] !== 'seller') {
        flash('danger', 'Only sellers can request verification.');
    } else {
        $stmt = db()->prepare('INSERT INTO verification_requests (seller_id, request_type, document_path) VALUES (?, "id", ?)');
        $stmt->execute([(int) $user['id'], 'uploads/seller-' . (int) $user['id'] . '-id-placeholder.pdf']);
        log_audit('request_verification', 'verification_request', (int) db()->lastInsertId(), 'Seller requested ID verification.');
        flash('success', 'Verification request submitted for admin review.');
    }
    redirect('/dashboard.php');
}

$orders = get_user_orders($user);
$activeOrders = array_filter($orders, fn ($order) => in_array($order['state'], ['pending_payment', 'in_escrow', 'shipped', 'delivered', 'disputed'], true));
$protectedValue = array_reduce($orders, fn ($sum, $order) => in_array($order['state'], ['in_escrow', 'shipped', 'delivered'], true) ? $sum + (float) $order['amount'] : $sum, 0.0);

render_header('Dashboard');
?>
<section class="page-heading">
    <p class="eyebrow">Dashboard</p>
    <h1>Welcome, <?= e($user['full_name']) ?></h1>
    <p><?= e(ucfirst($user['role'])) ?> account, <?= status_badge($user['verification_level']) ?></p>
</section>

<section class="stats-grid">
    <article><strong><?= count($activeOrders) ?></strong><span>Active orders</span></article>
    <article><strong><?= money($protectedValue) ?></strong><span>Protected value</span></article>
    <article><strong><?= count($orders) ?></strong><span>Total orders</span></article>
</section>

<section class="dashboard-grid">
    <article class="panel">
        <div class="section-heading compact">
            <h2>Recent orders</h2>
            <a href="/orders.php">View all</a>
        </div>
        <?php if (!$orders): ?>
            <p>No orders yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Listing</th><th>Amount</th><th>State</th></tr></thead>
                    <tbody>
                        <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                            <tr>
                                <td><?= e($order['listing_title']) ?></td>
                                <td><?= money($order['amount']) ?></td>
                                <td><?= status_badge($order['state']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>

    <article class="panel dark">
        <?php if ($user['role'] === 'seller'): ?>
            <h2>Seller tools</h2>
            <p>Manage listings and request verification for stronger buyer trust.</p>
            <div class="actions">
                <a class="button light" href="/seller-listings.php">Manage listings</a>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="request_verification">
                    <button class="button ghost-light" type="submit">Request ID review</button>
                </form>
            </div>
        <?php elseif ($user['role'] === 'admin'): ?>
            <h2>Admin workspace</h2>
            <p>Review users, verification requests, disputes, listings, and audit logs.</p>
            <a class="button light" href="/admin/index.php">Open admin</a>
        <?php else: ?>
            <h2>Start selling</h2>
            <p>Create a seller account to list goods and build trust through verified transactions.</p>
            <a class="button light" href="/auth.php">Create seller account</a>
        <?php endif; ?>
    </article>
</section>
<?php render_footer(); ?>
