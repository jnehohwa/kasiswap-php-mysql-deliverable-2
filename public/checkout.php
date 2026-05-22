<?php
require_once __DIR__ . '/_bootstrap.php';

$user = require_login();
$listing = get_listing((int) ($_GET['listing_id'] ?? $_POST['listing_id'] ?? 0));

if (!$listing) {
    flash('danger', 'Choose a valid listing before checkout.');
    redirect('/marketplace.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $orderId = create_order(
            (int) $listing['id'],
            (int) $user['id'],
            $_POST['delivery_method'] === 'courier' ? 'courier' : 'pickup',
            trim($_POST['delivery_address'] ?? '')
        );
        flash('success', 'Order created. Simulate PayFast sandbox payment from your orders page.');
        redirect('/orders.php#order-' . $orderId);
    } catch (Throwable $exception) {
        flash('danger', $exception->getMessage());
    }
}

$fee = round((float) $listing['price'] * BUYER_PROTECTION_RATE, 2);

render_header('Checkout');
?>
<section class="checkout-layout">
    <form class="panel" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="listing_id" value="<?= (int) $listing['id'] ?>">
        <p class="eyebrow">Checkout</p>
        <h1><?= e($listing['title']) ?></h1>
        <p>Payment starts as pending, then moves to in escrow only after sandbox confirmation.</p>
        <label>Delivery method
            <select name="delivery_method">
                <option value="pickup">Meet-up / pickup</option>
                <option value="courier">Courier</option>
            </select>
        </label>
        <label>Delivery or meet-up details
            <textarea name="delivery_address" rows="4" required placeholder="Street address or agreed public meet-up point"></textarea>
        </label>
        <button class="button primary" type="submit">Create protected order</button>
    </form>

    <aside class="panel summary">
        <img src="<?= e($listing['image_path']) ?>" alt="<?= e($listing['title']) ?>">
        <dl>
            <div><dt>Item</dt><dd><?= money($listing['price']) ?></dd></div>
            <div><dt>Buyer protection</dt><dd><?= money($fee) ?></dd></div>
            <div class="total"><dt>Total</dt><dd><?= money((float) $listing['price'] + $fee) ?></dd></div>
        </dl>
        <p class="form-note">This is PayFast sandbox-style demo logic for Deliverable 2.</p>
    </aside>
</section>
<?php render_footer(); ?>
