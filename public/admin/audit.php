<?php
require_once __DIR__ . '/../_bootstrap.php';

require_role('admin');
$logs = db()->query(
    'SELECT a.*, u.full_name AS actor_name
     FROM audit_logs a
     LEFT JOIN users u ON u.id = a.actor_id
     ORDER BY a.created_at DESC
     LIMIT 200'
)->fetchAll();

render_header('Audit logs');
?>
<section class="page-heading">
    <p class="eyebrow">Admin</p>
    <h1>Audit logs</h1>
    <p>Traceable actions for accounts, orders, disputes, verification, and listings.</p>
</section>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Time</th><th>Actor</th><th>Action</th><th>Entity</th><th>Details</th></tr></thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= e($log['created_at']) ?></td>
                        <td><?= e($log['actor_name'] ?? 'System') ?></td>
                        <td><?= e($log['action']) ?></td>
                        <td><?= e($log['entity_type']) ?> #<?= e((string) $log['entity_id']) ?></td>
                        <td><?= e($log['details']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php render_footer(); ?>
