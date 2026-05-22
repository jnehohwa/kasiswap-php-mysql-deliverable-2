<?php
require_once __DIR__ . '/../_bootstrap.php';

$admin = require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create') {
            $userId = register_account([
                'full_name' => $_POST['full_name'],
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'] ?? '',
                'password' => $_POST['password'],
                'role' => $_POST['role'],
                'township' => $_POST['township'] ?? '',
                'city' => $_POST['city'] ?? '',
                'store_name' => $_POST['store_name'] ?? '',
            ]);
            log_audit('admin_create_user', 'user', $userId, 'Admin created user account.', (int) $admin['id']);
            flash('success', 'User created.');
        }

        if ($action === 'update') {
            $role = in_array($_POST['role'] ?? '', ['buyer', 'seller', 'admin'], true) ? $_POST['role'] : 'buyer';
            $status = in_array($_POST['status'] ?? '', ['active', 'suspended'], true) ? $_POST['status'] : 'active';
            $verification = in_array($_POST['verification_level'] ?? '', ['none', 'phone', 'id_verified', 'premium'], true) ? $_POST['verification_level'] : 'none';
            $userId = (int) $_POST['user_id'];

            $stmt = db()->prepare('UPDATE users SET role = ?, status = ?, verification_level = ? WHERE id = ?');
            $stmt->execute([$role, $status, $verification, $userId]);

            if ($role === 'seller') {
                $storeName = trim($_POST['store_name'] ?? '') ?: 'Seller Store';
                $upsert = db()->prepare(
                    'INSERT INTO seller_profiles (user_id, store_name, store_bio)
                     VALUES (?, ?, "Seller profile created by admin.")
                     ON DUPLICATE KEY UPDATE store_name = VALUES(store_name)'
                );
                $upsert->execute([$userId, $storeName]);
            }

            log_audit('admin_update_user', 'user', $userId, 'Admin updated user role/status.', (int) $admin['id']);
            flash('success', 'User updated.');
        }

        if ($action === 'delete') {
            $userId = (int) $_POST['user_id'];
            if ($userId === (int) $admin['id']) {
                throw new RuntimeException('Admin cannot delete their own account.');
            }
            try {
                $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
                $stmt->execute([$userId]);
                log_audit('admin_delete_user', 'user', $userId, 'Admin deleted user.', (int) $admin['id']);
            } catch (Throwable) {
                $stmt = db()->prepare('UPDATE users SET status = "suspended" WHERE id = ?');
                $stmt->execute([$userId]);
                log_audit('admin_suspend_user', 'user', $userId, 'User had linked records, so admin suspended instead of hard delete.', (int) $admin['id']);
            }
            flash('success', 'User removed or suspended.');
        }
    } catch (Throwable $exception) {
        flash('danger', $exception->getMessage());
    }

    redirect('/admin/users.php');
}

$users = db()->query(
    'SELECT u.*, sp.store_name
     FROM users u
     LEFT JOIN seller_profiles sp ON sp.user_id = u.id
     ORDER BY u.created_at DESC'
)->fetchAll();

render_header('Admin users');
?>
<section class="page-heading">
    <p class="eyebrow">Admin</p>
    <h1>User type CRUD</h1>
    <p>Create, display, update, and remove buyer, seller, and admin users.</p>
</section>

<section class="dashboard-grid">
    <form class="panel" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <h2>Create user</h2>
        <label>Full name <input name="full_name" required></label>
        <label>Username <input name="username" required></label>
        <label>Email <input type="email" name="email" required></label>
        <label>Phone <input name="phone"></label>
        <label>Township <input name="township"></label>
        <label>City <input name="city"></label>
        <label>Role
            <select name="role" data-role-select>
                <option value="buyer">Buyer</option>
                <option value="seller">Seller</option>
                <option value="admin">Admin</option>
            </select>
        </label>
        <label data-store-field hidden>Store name <input name="store_name"></label>
        <label>Password <input type="password" name="password" minlength="8" required></label>
        <button class="button primary" type="submit">Create user</button>
    </form>

    <div class="stack">
        <?php foreach ($users as $item): ?>
            <form class="panel" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" value="<?= (int) $item['id'] ?>">
                <div class="row between wrap">
                    <div>
                        <h2><?= e($item['full_name']) ?></h2>
                        <p><?= e($item['email']) ?>, @<?= e($item['username']) ?></p>
                    </div>
                    <?= status_badge($item['status']) ?>
                </div>
                <div class="form-grid three">
                    <label>Role
                        <select name="role">
                            <?php foreach (['buyer', 'seller', 'admin'] as $role): ?>
                                <option value="<?= e($role) ?>" <?= selected($item['role'], $role) ?>><?= e($role) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Status
                        <select name="status">
                            <option value="active" <?= selected($item['status'], 'active') ?>>active</option>
                            <option value="suspended" <?= selected($item['status'], 'suspended') ?>>suspended</option>
                        </select>
                    </label>
                    <label>Verification
                        <select name="verification_level">
                            <?php foreach (['none', 'phone', 'id_verified', 'premium'] as $level): ?>
                                <option value="<?= e($level) ?>" <?= selected($item['verification_level'], $level) ?>><?= e($level) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Store name <input name="store_name" value="<?= e($item['store_name'] ?? '') ?>"></label>
                </div>
                <div class="actions">
                    <button class="button secondary" type="submit">Update</button>
                    <button class="button danger" type="submit" name="action" value="delete" data-confirm="Delete or suspend this user?">Delete</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>
</section>
<?php render_footer(); ?>
