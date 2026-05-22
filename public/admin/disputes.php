<?php
require_once __DIR__ . '/../_bootstrap.php';

$admin = require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $status = in_array($_POST['status'] ?? '', ['open', 'under_review', 'resolved_buyer', 'resolved_seller', 'closed'], true) ? $_POST['status'] : 'under_review';
    $disputeId = (int) $_POST['dispute_id'];
    $notes = trim($_POST['outcome_notes'] ?? '');

    $pdo = db();
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('UPDATE disputes SET status = ?, outcome_notes = ?, resolved_at = IF(? IN ("resolved_buyer", "resolved_seller", "closed"), CURRENT_TIMESTAMP, resolved_at) WHERE id = ?');
    $stmt->execute([$status, $notes, $status, $disputeId]);

    $orderStmt = $pdo->prepare('SELECT order_id FROM disputes WHERE id = ?');
    $orderStmt->execute([$disputeId]);
    $orderId = (int) $orderStmt->fetchColumn();

    if ($status === 'resolved_buyer') {
        $pdo->prepare('UPDATE orders SET state = "refunded" WHERE id = ?')->execute([$orderId]);
        $pdo->prepare('UPDATE payments SET status = "refunded" WHERE order_id = ?')->execute([$orderId]);
    } elseif ($status === 'resolved_seller') {
        $pdo->prepare('UPDATE orders SET state = "released" WHERE id = ?')->execute([$orderId]);
    }

    log_audit('resolve_dispute', 'dispute', $disputeId, 'Admin set dispute to ' . $status . '.', (int) $admin['id']);
    $pdo->commit();
    flash('success', 'Dispute updated.');
    redirect('/admin/disputes.php');
}

$disputes = db()->query(
    'SELECT d.*, o.amount, o.state AS order_state, l.title AS listing_title, u.full_name AS opened_by_name
     FROM disputes d
     JOIN orders o ON o.id = d.order_id
     JOIN listings l ON l.id = o.listing_id
     JOIN users u ON u.id = d.opened_by
     ORDER BY d.created_at DESC'
)->fetchAll();

render_header('Admin disputes');
?>
<section class="page-heading">
    <p class="eyebrow">Admin</p>
    <h1>Dispute resolution</h1>
</section>

<section class="stack">
    <?php foreach ($disputes as $dispute): ?>
        <form class="panel" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="dispute_id" value="<?= (int) $dispute['id'] ?>">
            <div class="row between wrap">
                <div>
                    <h2><?= e($dispute['reason']) ?></h2>
                    <p><?= e($dispute['listing_title']) ?>, <?= money($dispute['amount']) ?>, opened by <?= e($dispute['opened_by_name']) ?></p>
                </div>
                <div class="order-badges">
                    <?= status_badge($dispute['status']) ?>
                    <?= status_badge($dispute['order_state']) ?>
                </div>
            </div>
            <p><?= e($dispute['details']) ?></p>
            <label>Status
                <select name="status">
                    <?php foreach (['open', 'under_review', 'resolved_buyer', 'resolved_seller', 'closed'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= selected($dispute['status'], $status) ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Outcome notes <textarea name="outcome_notes" rows="3"><?= e($dispute['outcome_notes'] ?? '') ?></textarea></label>
            <button class="button primary" type="submit">Save outcome</button>
        </form>
    <?php endforeach; ?>
</section>
<?php render_footer(); ?>
