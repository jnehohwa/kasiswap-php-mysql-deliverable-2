# KasiSwap Code Samples for Deliverable 2

Use these as the source snippets for the Coding section of the report. Keep each pasted sample short in the final PDF and explain what it does.

## Sample PHP Code: Secure Login and RBAC
Source file: `app/security.php`

```php
function sign_in(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND status = "active" LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    log_audit('login', 'user', (int) $user['id'], 'User signed in.');
    return true;
}
```

Explanation: This shows prepared statements, password hashing verification, session regeneration, and audit logging.

## Sample PHP Code: Fake-Payment Prevention
Source file: `app/services.php`

```php
function seller_mark_shipped(int $orderId, int $sellerId, string $trackingRef): void
{
    $stmt = db()->prepare(
        'SELECT o.*, p.status AS payment_status
         FROM orders o
         LEFT JOIN payments p ON p.order_id = o.id
         WHERE o.id = ? AND o.seller_id = ?'
    );
    $stmt->execute([$orderId, $sellerId]);
    $order = $stmt->fetch();

    if (!$order || $order['state'] !== 'in_escrow' || $order['payment_status'] !== 'confirmed') {
        throw new RuntimeException('Seller can ship only after platform payment is confirmed in escrow.');
    }

    $stmt = db()->prepare('UPDATE orders SET state = "shipped", tracking_ref = ? WHERE id = ?');
    $stmt->execute([trim($trackingRef), $orderId]);
}
```

Explanation: This directly addresses the proposal objective of preventing item handover before platform-confirmed payment.

## Sample HTML/PHP Code: Listing Card
Source file: `public/partials/listing-card.php`

```php
<article class="listing-card">
    <a href="/listing.php?id=<?= (int) $listing['id'] ?>">
        <img src="<?= e($listing['image_path']) ?>" alt="<?= e($listing['title']) ?>" loading="lazy">
    </a>
    <div class="listing-card-body">
        <h3><a href="/listing.php?id=<?= (int) $listing['id'] ?>"><?= e($listing['title']) ?></a></h3>
        <p><?= e($listing['location_township']) ?>, <?= e($listing['location_city']) ?></p>
        <strong><?= money($listing['price']) ?></strong>
    </div>
</article>
```

Explanation: The template escapes output with `e()` to reduce XSS risk and uses lazy-loaded images to support low-data browsing.

## Sample JavaScript Code: Mobile Navigation and Form Behavior
Source file: `public/assets/js/app.js`

```js
document.addEventListener("DOMContentLoaded", () => {
  const navToggle = document.querySelector("[data-nav-toggle]");
  const nav = document.querySelector("[data-nav]");

  navToggle?.addEventListener("click", () => {
    nav?.classList.toggle("open");
  });
});
```

Explanation: JavaScript is intentionally small. The app uses JS for progressive enhancement rather than heavy client-side rendering.

## Sample CSS Code: Responsive Marketplace Grid
Source file: `public/assets/css/styles.css`

```css
.listing-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

@media (max-width: 980px) {
  .listing-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 720px) {
  .listing-grid {
    grid-template-columns: 1fr;
  }
}
```

Explanation: The grid adapts from desktop to tablet and mobile, supporting the required responsive prototype screenshots.

## Sample MySQL Code: Order and Payment Tables
Source file: `database/01_schema.sql`

```sql
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  buyer_id INT NOT NULL,
  seller_id INT NOT NULL,
  listing_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  state ENUM('pending_payment', 'in_escrow', 'shipped', 'delivered', 'released', 'disputed', 'refunded', 'cancelled') NOT NULL DEFAULT 'pending_payment',
  CONSTRAINT fk_orders_buyer FOREIGN KEY (buyer_id) REFERENCES users(id),
  CONSTRAINT fk_orders_seller FOREIGN KEY (seller_id) REFERENCES users(id),
  CONSTRAINT fk_orders_listing FOREIGN KEY (listing_id) REFERENCES listings(id)
);
```

Explanation: The database enforces relationships between buyers, sellers, listings, and orders using foreign keys.
