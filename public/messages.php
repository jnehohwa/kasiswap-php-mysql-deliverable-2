<?php
require_once __DIR__ . '/_bootstrap.php';

$user = require_login();
$listingId = (int) ($_GET['listing_id'] ?? $_POST['listing_id'] ?? 0);
$sellerId = (int) ($_GET['seller_id'] ?? $_POST['receiver_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $receiverId = (int) ($_POST['receiver_id'] ?? 0);
    $body = trim($_POST['body'] ?? '');

    if ($receiverId && $body !== '') {
        $stmt = db()->prepare('INSERT INTO messages (sender_id, receiver_id, listing_id, body) VALUES (?, ?, ?, ?)');
        $stmt->execute([(int) $user['id'], $receiverId, $listingId ?: null, $body]);
        log_audit('send_message', 'message', (int) db()->lastInsertId(), 'User sent in-app message.');
        flash('success', 'Message sent.');
    }
    redirect('/messages.php');
}

$messages = db()->prepare(
    'SELECT m.*, sender.full_name AS sender_name, receiver.full_name AS receiver_name, l.title AS listing_title
     FROM messages m
     JOIN users sender ON sender.id = m.sender_id
     JOIN users receiver ON receiver.id = m.receiver_id
     LEFT JOIN listings l ON l.id = m.listing_id
     WHERE m.sender_id = ? OR m.receiver_id = ?
     ORDER BY m.created_at DESC'
);
$messages->execute([(int) $user['id'], (int) $user['id']]);

$listing = $listingId ? get_listing($listingId) : null;
$receiverId = $sellerId ?: ($listing ? (int) $listing['seller_id'] : 0);

render_header('Messages');
?>
<section class="page-heading">
    <p class="eyebrow">Messages</p>
    <h1>In-app conversations</h1>
    <p>Keeping trade messages on-platform reduces off-platform scam risk.</p>
</section>

<section class="dashboard-grid">
    <form class="panel" method="post">
        <?= csrf_field() ?>
        <h2>Send message</h2>
        <?php if ($listing): ?>
            <p class="form-note">Listing: <?= e($listing['title']) ?></p>
        <?php endif; ?>
        <input type="hidden" name="listing_id" value="<?= $listingId ?: '' ?>">
        <label>Receiver user ID <input type="number" name="receiver_id" value="<?= $receiverId ?: '' ?>" required></label>
        <label>Message <textarea name="body" rows="5" required></textarea></label>
        <button class="button primary" type="submit">Send</button>
    </form>

    <div class="stack">
        <?php foreach ($messages->fetchAll() as $message): ?>
            <article class="panel message-card">
                <div class="row between wrap">
                    <strong><?= e($message['sender_name']) ?> to <?= e($message['receiver_name']) ?></strong>
                    <span><?= e($message['created_at']) ?></span>
                </div>
                <?php if ($message['listing_title']): ?>
                    <p class="form-note"><?= e($message['listing_title']) ?></p>
                <?php endif; ?>
                <p><?= e($message['body']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php render_footer(); ?>
