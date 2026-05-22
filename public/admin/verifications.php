<?php
require_once __DIR__ . '/../_bootstrap.php';

$admin = require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $requestId = (int) $_POST['request_id'];
    $decision = $_POST['decision'] === 'approved' ? 'approved' : 'rejected';
    $notes = trim($_POST['admin_notes'] ?? '');

    $stmt = db()->prepare('SELECT * FROM verification_requests WHERE id = ?');
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();

    if ($request) {
        $pdo = db();
        $pdo->beginTransaction();
        $update = $pdo->prepare(
            'UPDATE verification_requests
             SET status = ?, admin_notes = ?, reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP
             WHERE id = ?'
        );
        $update->execute([$decision, $notes, (int) $admin['id'], $requestId]);

        if ($decision === 'approved') {
            $level = $request['request_type'] === 'phone' ? 'phone' : 'id_verified';
            $pdo->prepare('UPDATE users SET verification_level = ? WHERE id = ?')->execute([$level, (int) $request['seller_id']]);
        }

        log_audit('review_verification', 'verification_request', $requestId, 'Admin ' . $decision . ' verification.', (int) $admin['id']);
        $pdo->commit();
        flash('success', 'Verification reviewed.');
    }

    redirect('/admin/verifications.php');
}

$requests = db()->query(
    'SELECT vr.*, u.full_name, u.email, sp.store_name
     FROM verification_requests vr
     JOIN users u ON u.id = vr.seller_id
     LEFT JOIN seller_profiles sp ON sp.user_id = u.id
     ORDER BY vr.created_at DESC'
)->fetchAll();

render_header('Admin verification');
?>
<section class="page-heading">
    <p class="eyebrow">Admin</p>
    <h1>Seller verification review</h1>
    <p>Verification decisions update seller trust badges and remain auditable.</p>
</section>

<section class="stack">
    <?php foreach ($requests as $request): ?>
        <form class="panel" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
            <div class="row between wrap">
                <div>
                    <h2><?= e($request['store_name'] ?? $request['full_name']) ?></h2>
                    <p><?= e($request['email']) ?> requested <?= e($request['request_type']) ?> verification.</p>
                </div>
                <?= status_badge($request['status']) ?>
            </div>
            <label>Admin notes <textarea name="admin_notes" rows="3"><?= e($request['admin_notes'] ?? '') ?></textarea></label>
            <div class="actions">
                <button class="button primary" type="submit" name="decision" value="approved">Approve</button>
                <button class="button danger" type="submit" name="decision" value="rejected">Reject</button>
            </div>
        </form>
    <?php endforeach; ?>
</section>
<?php render_footer(); ?>
