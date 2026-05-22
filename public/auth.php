<?php
require_once __DIR__ . '/_bootstrap.php';

if (current_user()) {
    redirect('/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $mode = $_POST['mode'] ?? 'login';

    try {
        if ($mode === 'register') {
            $required = ['full_name', 'username', 'email', 'password'];
            foreach ($required as $field) {
                if (trim($_POST[$field] ?? '') === '') {
                    throw new RuntimeException('Please complete all required registration fields.');
                }
            }
            if (strlen($_POST['password']) < 8) {
                throw new RuntimeException('Password must be at least 8 characters.');
            }
            $userId = register_account($_POST);
            $_SESSION['user_id'] = $userId;
            flash('success', 'Account created. Welcome to KasiSwap.');
            redirect('/dashboard.php');
        }

        if (sign_in(trim($_POST['email'] ?? ''), $_POST['password'] ?? '')) {
            flash('success', 'Signed in successfully.');
            redirect('/dashboard.php');
        }

        flash('danger', 'Invalid email or password.');
    } catch (Throwable $exception) {
        flash('danger', $exception->getMessage());
    }
}

render_header('Sign in');
?>
<section class="auth-grid">
    <form class="panel" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="mode" value="login">
        <p class="eyebrow">Welcome back</p>
        <h1>Sign in</h1>
        <label>Email <input type="email" name="email" value="buyer@kasiswap.test" required></label>
        <label>Password <input type="password" name="password" value="password" required></label>
        <button class="button primary" type="submit">Sign in</button>
        <p class="form-note">Demo accounts use password: <strong>password</strong></p>
    </form>

    <form class="panel" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="mode" value="register">
        <p class="eyebrow">New account</p>
        <h2>Create account</h2>
        <div class="form-grid two">
            <label>Full name <input name="full_name" required></label>
            <label>Username <input name="username" required></label>
            <label>Email <input type="email" name="email" required></label>
            <label>Phone <input name="phone" placeholder="082 000 0000"></label>
            <label>Township <input name="township" placeholder="Soweto"></label>
            <label>City <input name="city" placeholder="Johannesburg"></label>
            <label>Role
                <select name="role" data-role-select>
                    <option value="buyer">Buyer</option>
                    <option value="seller">Seller</option>
                </select>
            </label>
            <label data-store-field hidden>Store name <input name="store_name"></label>
            <label>Password <input type="password" name="password" minlength="8" required></label>
        </div>
        <button class="button secondary" type="submit">Register</button>
    </form>
</section>
<?php render_footer(); ?>
