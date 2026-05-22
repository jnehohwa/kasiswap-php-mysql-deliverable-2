<?php
require_once __DIR__ . '/../_bootstrap.php';

require_role('admin');
$pdo = db();
$stats = [
    'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'listings' => (int) $pdo->query('SELECT COUNT(*) FROM listings')->fetchColumn(),
    'pending_verifications' => (int) $pdo->query('SELECT COUNT(*) FROM verification_requests WHERE status = "pending"')->fetchColumn(),
    'open_disputes' => (int) $pdo->query('SELECT COUNT(*) FROM disputes WHERE status IN ("open", "under_review")')->fetchColumn(),
];

render_header('Admin');
?>
<section class="page-heading">
    <p class="eyebrow">Admin</p>
    <h1>Platform management</h1>
    <p>RBAC-protected admin workspace for users, listings, verification, disputes, and audit logs.</p>
</section>

<section class="stats-grid">
    <article><strong><?= $stats['users'] ?></strong><span>Users</span></article>
    <article><strong><?= $stats['listings'] ?></strong><span>Listings</span></article>
    <article><strong><?= $stats['pending_verifications'] ?></strong><span>Pending reviews</span></article>
    <article><strong><?= $stats['open_disputes'] ?></strong><span>Open disputes</span></article>
</section>

<section class="admin-links">
    <a class="panel" href="/admin/users.php"><strong>User types</strong><span>Create, update, suspend, and delete user records.</span></a>
    <a class="panel" href="/admin/verifications.php"><strong>Verification</strong><span>Approve or reject seller phone/ID verification.</span></a>
    <a class="panel" href="/admin/listings.php"><strong>Listings</strong><span>Moderate marketplace visibility.</span></a>
    <a class="panel" href="/admin/disputes.php"><strong>Disputes</strong><span>Resolve buyer/seller order issues.</span></a>
    <a class="panel" href="/admin/audit.php"><strong>Audit logs</strong><span>Trace security and admin actions.</span></a>
</section>
<?php render_footer(); ?>
