<?php
declare(strict_types=1);

function get_categories(): array
{
    return db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
}

function get_listing_image(int $listingId): string
{
    $stmt = db()->prepare('SELECT image_path FROM listing_images WHERE listing_id = ? ORDER BY is_primary DESC, id ASC LIMIT 1');
    $stmt->execute([$listingId]);
    $image = $stmt->fetchColumn();
    return $image ?: '/assets/img/placeholder.svg';
}

function get_listing(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT l.*, c.name AS category_name, u.full_name AS seller_name, u.verification_level, u.township AS seller_township,
                sp.store_name, sp.store_bio, sp.total_sales
         FROM listings l
         JOIN categories c ON c.id = l.category_id
         JOIN users u ON u.id = l.seller_id
         LEFT JOIN seller_profiles sp ON sp.user_id = u.id
         WHERE l.id = ? LIMIT 1'
    );
    $stmt->execute([$id]);
    $listing = $stmt->fetch();

    if (!$listing) {
        return null;
    }

    $listing['image_path'] = get_listing_image((int) $listing['id']);
    return $listing;
}

function list_marketplace(array $filters = []): array
{
    $where = ['l.status = "active"'];
    $params = [];

    if (!empty($filters['q'])) {
        $where[] = '(l.title LIKE ? OR l.description LIKE ? OR l.location_township LIKE ? OR sp.store_name LIKE ?)';
        $search = '%' . $filters['q'] . '%';
        array_push($params, $search, $search, $search, $search);
    }

    if (!empty($filters['category_id'])) {
        $where[] = 'l.category_id = ?';
        $params[] = (int) $filters['category_id'];
    }

    if (!empty($filters['township'])) {
        $where[] = 'l.location_township = ?';
        $params[] = $filters['township'];
    }

    $sort = match ($filters['sort'] ?? 'newest') {
        'price_asc' => 'l.price ASC',
        'price_desc' => 'l.price DESC',
        default => 'l.created_at DESC',
    };

    $sql = 'SELECT l.*, c.name AS category_name, u.verification_level, sp.store_name
            FROM listings l
            JOIN categories c ON c.id = l.category_id
            JOIN users u ON u.id = l.seller_id
            LEFT JOIN seller_profiles sp ON sp.user_id = u.id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY ' . $sort . ' LIMIT 60';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    foreach ($items as &$item) {
        $item['image_path'] = get_listing_image((int) $item['id']);
    }

    return $items;
}

function list_townships(): array
{
    return db()->query('SELECT DISTINCT location_township FROM listings WHERE status = "active" ORDER BY location_township')->fetchAll(PDO::FETCH_COLUMN);
}

function unique_listing_slug(string $title, ?int $ignoreId = null): string
{
    $base = slugify($title);
    $slug = $base;
    $i = 2;

    while (true) {
        $sql = 'SELECT id FROM listings WHERE slug = ?';
        $params = [$slug];
        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        $stmt = db()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $i++;
    }
}

function save_uploaded_listing_image(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }

    if (($file['size'] ?? 0) > 1_500_000) {
        throw new RuntimeException('Image must be smaller than 1.5MB for the low-data prototype.');
    }

    $info = getimagesize($file['tmp_name']);
    if (!$info || !in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
        throw new RuntimeException('Upload a JPG, PNG, or WebP image.');
    }

    $extension = match ($info['mime']) {
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => 'jpg',
    };
    $name = bin2hex(random_bytes(12)) . '.' . $extension;
    $target = __DIR__ . '/../public/uploads/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Could not save uploaded image.');
    }

    return '/uploads/' . $name;
}

function create_or_update_listing(array $data, array $user, ?int $listingId = null, ?array $imageFile = null): int
{
    $pdo = db();
    $isEdit = $listingId !== null;
    $slug = unique_listing_slug($data['title'], $listingId);
    $status = in_array($data['status'] ?? 'active', ['active', 'reserved', 'sold', 'draft', 'hidden'], true) ? $data['status'] : 'active';
    $condition = in_array($data['item_condition'] ?? 'good', ['new', 'like_new', 'good', 'fair'], true) ? $data['item_condition'] : 'good';
    $imagePath = $imageFile ? save_uploaded_listing_image($imageFile) : null;

    if ($isEdit) {
        $stmt = $pdo->prepare(
            'UPDATE listings
             SET category_id = ?, title = ?, slug = ?, description = ?, price = ?, item_condition = ?, status = ?,
                 location_township = ?, location_city = ?, accepts_escrow = ?, accepts_delivery = ?
             WHERE id = ? AND (seller_id = ? OR ? = "admin")'
        );
        $stmt->execute([
            (int) $data['category_id'],
            trim($data['title']),
            $slug,
            trim($data['description']),
            (float) $data['price'],
            $condition,
            $status,
            trim($data['location_township']),
            trim($data['location_city']),
            !empty($data['accepts_escrow']) ? 1 : 0,
            !empty($data['accepts_delivery']) ? 1 : 0,
            $listingId,
            (int) $user['id'],
            $user['role'],
        ]);
        if ($imagePath) {
            $img = $pdo->prepare('INSERT INTO listing_images (listing_id, image_path, alt_text, is_primary) VALUES (?, ?, ?, 1)');
            $img->execute([$listingId, $imagePath, trim($data['title'])]);
        }
        log_audit('update_listing', 'listing', $listingId, 'Listing updated.');
        return $listingId;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO listings (seller_id, category_id, title, slug, description, price, item_condition, status, location_township, location_city, accepts_escrow, accepts_delivery)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        (int) $user['id'],
        (int) $data['category_id'],
        trim($data['title']),
        $slug,
        trim($data['description']),
        (float) $data['price'],
        $condition,
        $status,
        trim($data['location_township']),
        trim($data['location_city']),
        !empty($data['accepts_escrow']) ? 1 : 0,
        !empty($data['accepts_delivery']) ? 1 : 0,
    ]);
    $newId = (int) $pdo->lastInsertId();

    $img = $pdo->prepare('INSERT INTO listing_images (listing_id, image_path, alt_text, is_primary) VALUES (?, ?, ?, 1)');
    $img->execute([$newId, $imagePath ?: 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=520&q=55', trim($data['title'])]);

    log_audit('create_listing', 'listing', $newId, 'Seller created a listing.');
    return $newId;
}

function get_user_orders(array $user): array
{
    $where = $user['role'] === 'admin' ? '1 = 1' : '(o.buyer_id = ? OR o.seller_id = ?)';
    $params = $user['role'] === 'admin' ? [] : [(int) $user['id'], (int) $user['id']];

    $stmt = db()->prepare(
        "SELECT o.*, l.title AS listing_title, buyer.full_name AS buyer_name, seller.full_name AS seller_name,
                p.status AS payment_status, p.provider, p.sandbox_reference
         FROM orders o
         JOIN listings l ON l.id = o.listing_id
         JOIN users buyer ON buyer.id = o.buyer_id
         JOIN users seller ON seller.id = o.seller_id
         LEFT JOIN payments p ON p.order_id = o.id
         WHERE $where
         ORDER BY o.updated_at DESC"
    );
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function create_order(int $listingId, int $buyerId, string $deliveryMethod, string $deliveryAddress): int
{
    $listing = get_listing($listingId);
    if (!$listing || $listing['status'] !== 'active') {
        throw new RuntimeException('Listing is not available.');
    }
    if ((int) $listing['seller_id'] === $buyerId) {
        throw new RuntimeException('You cannot buy your own listing.');
    }

    $fee = round((float) $listing['price'] * BUYER_PROTECTION_RATE, 2);
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $order = $pdo->prepare(
            'INSERT INTO orders (buyer_id, seller_id, listing_id, amount, buyer_protection_fee, delivery_method, delivery_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $order->execute([
            $buyerId,
            (int) $listing['seller_id'],
            $listingId,
            (float) $listing['price'],
            $fee,
            $deliveryMethod,
            trim($deliveryAddress),
        ]);
        $orderId = (int) $pdo->lastInsertId();

        $payment = $pdo->prepare('INSERT INTO payments (order_id, sandbox_reference, amount) VALUES (?, ?, ?)');
        $payment->execute([$orderId, 'PF-DEMO-' . str_pad((string) $orderId, 5, '0', STR_PAD_LEFT), (float) $listing['price'] + $fee]);

        $pdo->prepare('UPDATE listings SET status = "reserved" WHERE id = ?')->execute([$listingId]);
        log_audit('create_order', 'order', $orderId, 'Order created in pending payment state.', $buyerId);
        $pdo->commit();
        return $orderId;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function confirm_sandbox_payment(int $orderId, int $buyerId): void
{
    $stmt = db()->prepare('SELECT id FROM orders WHERE id = ? AND buyer_id = ? AND state = "pending_payment"');
    $stmt->execute([$orderId, $buyerId]);
    if (!$stmt->fetch()) {
        throw new RuntimeException('Only the buyer can confirm a pending sandbox payment.');
    }

    $pdo = db();
    $pdo->beginTransaction();
    $pdo->prepare('UPDATE payments SET status = "confirmed", confirmed_at = CURRENT_TIMESTAMP WHERE order_id = ?')->execute([$orderId]);
    $pdo->prepare('UPDATE orders SET state = "in_escrow" WHERE id = ?')->execute([$orderId]);
    log_audit('confirm_payment', 'order', $orderId, 'Sandbox PayFast payment confirmed.', $buyerId);
    $pdo->commit();
}

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
    log_audit('mark_shipped', 'order', $orderId, 'Seller marked order as shipped.', $sellerId);
}

function buyer_mark_delivered(int $orderId, int $buyerId): void
{
    $stmt = db()->prepare('UPDATE orders SET state = "delivered" WHERE id = ? AND buyer_id = ? AND state = "shipped"');
    $stmt->execute([$orderId, $buyerId]);
    if ($stmt->rowCount() === 0) {
        throw new RuntimeException('Order must be shipped before it can be marked delivered.');
    }
    log_audit('mark_delivered', 'order', $orderId, 'Buyer marked order delivered.', $buyerId);
}

function buyer_release_funds(int $orderId, int $buyerId): void
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT listing_id, seller_id FROM orders WHERE id = ? AND buyer_id = ? AND state = "delivered"');
    $stmt->execute([$orderId, $buyerId]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new RuntimeException('Order must be delivered before funds are released.');
    }

    $pdo->prepare('UPDATE orders SET state = "released" WHERE id = ?')->execute([$orderId]);
    $pdo->prepare('UPDATE listings SET status = "sold" WHERE id = ?')->execute([(int) $order['listing_id']]);
    $pdo->prepare('UPDATE seller_profiles SET total_sales = total_sales + 1 WHERE user_id = ?')->execute([(int) $order['seller_id']]);
    log_audit('release_funds', 'order', $orderId, 'Buyer released sandbox-held funds.', $buyerId);
}

function create_dispute(int $orderId, int $userId, string $reason, string $details): void
{
    $stmt = db()->prepare(
        'SELECT id FROM orders
         WHERE id = ? AND (buyer_id = ? OR seller_id = ?) AND state IN ("in_escrow", "shipped", "delivered")'
    );
    $stmt->execute([$orderId, $userId, $userId]);
    if (!$stmt->fetch()) {
        throw new RuntimeException('Only active protected orders can be disputed.');
    }

    $pdo = db();
    $pdo->beginTransaction();
    $insert = $pdo->prepare('INSERT INTO disputes (order_id, opened_by, reason, details, status) VALUES (?, ?, ?, ?, "under_review")');
    $insert->execute([$orderId, $userId, trim($reason), trim($details)]);
    $pdo->prepare('UPDATE orders SET state = "disputed" WHERE id = ?')->execute([$orderId]);
    log_audit('open_dispute', 'order', $orderId, 'Dispute opened for admin mediation.', $userId);
    $pdo->commit();
}

function create_review(int $orderId, int $authorId, int $targetId, int $rating, string $comment): void
{
    $stmt = db()->prepare('SELECT id FROM orders WHERE id = ? AND buyer_id = ? AND state = "released"');
    $stmt->execute([$orderId, $authorId]);
    if (!$stmt->fetch()) {
        throw new RuntimeException('Reviews are allowed only after completed orders.');
    }

    $stmt = db()->prepare('INSERT INTO reviews (order_id, author_id, target_id, rating, comment) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$orderId, $authorId, $targetId, max(1, min(5, $rating)), trim($comment)]);
    log_audit('create_review', 'order', $orderId, 'Buyer reviewed completed order.', $authorId);
}
