<?php
require_once __DIR__ . '/_bootstrap.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        create_dispute((int) $_POST['order_id'], (int) $user['id'], $_POST['reason'] ?? '', $_POST['details'] ?? '');
        flash('success', 'Dispute opened for admin review.');
        redirect('/disputes.php');
    } catch (Throwable $exception) {
        flash('danger', $exception->getMessage());
    }
}

$orders = get_user_orders($user);
$orderId = (int) ($_GET['order_id'] ?? 0);
$disputes = db()->prepare(
    'SELECT d.*, o.amount, l.title AS listing_title, u.full_name AS opened_by_name
     FROM disputes d
     JOIN orders o ON o.id = d.order_id
     JOIN listings l ON l.id = o.listing_id
     JOIN users u ON u.id = d.opened_by
     WHERE o.buyer_id = ? OR o.seller_id = ?
     ORDER BY d.created_at DESC'
);
$disputes->execute([(int) $user['id'], (int) $user['id']]);

render_header('Disputes');
?>
<section class="page-heading">
    <p class="eyebrow">Disputes</p>
    <h1>Traceable dispute handling</h1>
    <p>Every dispute is linked to an order and visible to admin moderation.</p>
</section>

<section class="dashboard-grid">
    <form class="panel" method="post">
        <?= csrf_field() ?>
        <h2>Open dispute</h2>
        <label>Order
            <select name="order_id" required>
                <?php foreach ($orders as $order): ?>
                    <?php if (in_array($order['state'], ['in_escrow', 'shipped', 'delivered'], true)): ?>
                        <option value="<?= (int) $order['id'] ?>" <?= selected((string) $orderId, (string) $order['id']) ?>>
                            #<?= (int) $order['id'] ?> - <?= e($order['listing_title']) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Reason <input name="reason" required placeholder="Item not as described"></label>
        <label>Details <textarea name="details" rows="5" required></textarea></label>
        <button class="button danger" type="submit">Submit dispute</button>
    </form>

    <div class="stack">
        <?php foreach ($disputes->fetchAll() as $dispute): ?>
            <article class="panel">
                <div class="row between wrap">
                    <h2><?= e($dispute['reason']) ?></h2>
                    <?= status_badge($dispute['status']) ?>
                </div>
                <p><?= e($dispute['details']) ?></p>
                <p class="form-note">Order #<?= (int) $dispute['order_id'] ?>, <?= e($dispute['listing_title']) ?>, opened by <?= e($dispute['opened_by_name']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php render_footer(); ?>
