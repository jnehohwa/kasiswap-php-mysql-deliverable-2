<?php
require_once __DIR__ . '/_bootstrap.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = (int) ($_POST['order_id'] ?? 0);

    try {
        match ($_POST['action'] ?? '') {
            'confirm_payment' => confirm_sandbox_payment($orderId, (int) $user['id']),
            'mark_shipped' => seller_mark_shipped($orderId, (int) $user['id'], $_POST['tracking_ref'] ?? ''),
            'mark_delivered' => buyer_mark_delivered($orderId, (int) $user['id']),
            'release_funds' => buyer_release_funds($orderId, (int) $user['id']),
            'review' => create_review($orderId, (int) $user['id'], (int) $_POST['target_id'], (int) $_POST['rating'], $_POST['comment'] ?? ''),
            default => throw new RuntimeException('Unknown order action.'),
        };
        flash('success', 'Order updated.');
    } catch (Throwable $exception) {
        flash('danger', $exception->getMessage());
    }

    redirect('/orders.php#order-' . $orderId);
}

$orders = get_user_orders($user);
render_header('Orders');
?>
<section class="page-heading">
    <p class="eyebrow">Orders</p>
    <h1>Payment-hold state machine</h1>
    <p>Orders move through pending payment, in escrow, shipped, delivered, and released states.</p>
</section>

<section class="order-list">
    <?php foreach ($orders as $order): ?>
        <article class="panel order-card" id="order-<?= (int) $order['id'] ?>">
            <div class="row between wrap">
                <div>
                    <h2><?= e($order['listing_title']) ?></h2>
                    <p>#<?= (int) $order['id'] ?> buyer: <?= e($order['buyer_name']) ?> seller: <?= e($order['seller_name']) ?></p>
                </div>
                <div class="order-badges">
                    <?= status_badge($order['state']) ?>
                    <?= status_badge($order['payment_status'] ?? 'initiated') ?>
                </div>
            </div>
            <dl class="meta-list compact-list">
                <div><dt>Amount</dt><dd><?= money($order['amount']) ?></dd></div>
                <div><dt>Protection fee</dt><dd><?= money($order['buyer_protection_fee']) ?></dd></div>
                <div><dt>Provider</dt><dd><?= e($order['provider'] ?? 'PayFast Sandbox') ?></dd></div>
                <div><dt>Reference</dt><dd><?= e($order['sandbox_reference'] ?? 'Pending') ?></dd></div>
                <div><dt>Tracking</dt><dd><?= e($order['tracking_ref'] ?? 'Not added') ?></dd></div>
            </dl>

            <div class="actions wrap">
                <?php if ((int) $order['buyer_id'] === (int) $user['id'] && $order['state'] === 'pending_payment'): ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                        <input type="hidden" name="action" value="confirm_payment">
                        <button class="button primary" type="submit">Simulate PayFast payment</button>
                    </form>
                <?php endif; ?>

                <?php if ((int) $order['seller_id'] === (int) $user['id'] && $order['state'] === 'in_escrow'): ?>
                    <form class="inline-form" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                        <input type="hidden" name="action" value="mark_shipped">
                        <input name="tracking_ref" placeholder="Tracking reference" required>
                        <button class="button secondary" type="submit">Mark shipped</button>
                    </form>
                <?php endif; ?>

                <?php if ((int) $order['buyer_id'] === (int) $user['id'] && $order['state'] === 'shipped'): ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                        <input type="hidden" name="action" value="mark_delivered">
                        <button class="button secondary" type="submit">Mark delivered</button>
                    </form>
                <?php endif; ?>

                <?php if ((int) $order['buyer_id'] === (int) $user['id'] && $order['state'] === 'delivered'): ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                        <input type="hidden" name="action" value="release_funds">
                        <button class="button primary" type="submit">Release funds</button>
                    </form>
                <?php endif; ?>

                <?php if (in_array($order['state'], ['in_escrow', 'shipped', 'delivered'], true)): ?>
                    <a class="button danger" href="/disputes.php?order_id=<?= (int) $order['id'] ?>">Open dispute</a>
                <?php endif; ?>
            </div>

            <?php if ((int) $order['buyer_id'] === (int) $user['id'] && $order['state'] === 'released'): ?>
                <form class="review-form" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                    <input type="hidden" name="target_id" value="<?= (int) $order['seller_id'] ?>">
                    <input type="hidden" name="action" value="review">
                    <label>Rating
                        <select name="rating">
                            <option value="5">5 stars</option>
                            <option value="4">4 stars</option>
                            <option value="3">3 stars</option>
                            <option value="2">2 stars</option>
                            <option value="1">1 star</option>
                        </select>
                    </label>
                    <label>Review <input name="comment" placeholder="Quick review"></label>
                    <button class="button secondary" type="submit">Save review</button>
                </form>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
<?php render_footer(); ?>
