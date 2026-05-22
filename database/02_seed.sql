SET @demo_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

INSERT INTO users (id, full_name, username, email, phone, password_hash, role, verification_level, township, city)
VALUES
  (1, 'KasiSwap Admin', 'admin', 'admin@kasiswap.test', '0800000001', @demo_hash, 'admin', 'premium', 'Mowbray', 'Cape Town'),
  (2, 'Lerato Mokoena', 'leratoshop', 'seller@kasiswap.test', '0820000002', @demo_hash, 'seller', 'id_verified', 'Soweto', 'Johannesburg'),
  (3, 'Joshua Buyer', 'joshuabuyer', 'buyer@kasiswap.test', '0820000003', @demo_hash, 'buyer', 'phone', 'Khayelitsha', 'Cape Town'),
  (4, 'Sipho Dlamini', 'siphotech', 'sipho@kasiswap.test', '0820000004', @demo_hash, 'seller', 'phone', 'Umlazi', 'Durban');

INSERT INTO seller_profiles (user_id, store_name, store_bio, response_time_hours, total_sales)
VALUES
  (2, 'Kasi Threads', 'Handmade streetwear and beadwork from Soweto.', 2, 42),
  (4, 'Sipho Tech Hub', 'Refurbished phones, speakers, and accessories with honest grading.', 1, 88);

INSERT INTO categories (id, name, slug)
VALUES
  (1, 'Fashion', 'fashion'),
  (2, 'Electronics', 'electronics'),
  (3, 'Beauty', 'beauty'),
  (4, 'Home', 'home'),
  (5, 'Services', 'services');

INSERT INTO listings (id, seller_id, category_id, title, slug, description, price, item_condition, status, location_township, location_city, accepts_escrow, accepts_delivery)
VALUES
  (1, 2, 1, 'Hand-printed Ankara bomber jacket', 'ankara-bomber-jacket', 'Limited-run bomber jacket with hand-cut Ankara panels. Unisex fit and lined for cooler evenings.', 850.00, 'new', 'active', 'Soweto', 'Johannesburg', 1, 1),
  (2, 4, 2, 'iPhone 12 Pro refurbished 256GB', 'iphone-12-pro-refurbished-256gb', 'Refurbished, unlocked, new battery, and tested camera. Includes charger and 30-day seller warranty.', 8499.00, 'like_new', 'active', 'Umlazi', 'Durban', 1, 1),
  (3, 2, 1, 'Beaded statement necklace', 'beaded-statement-necklace', 'Hand-strung beadwork in earth tones. One-of-one piece from a local maker.', 320.00, 'new', 'active', 'Soweto', 'Johannesburg', 1, 0),
  (4, 4, 2, 'Bluetooth speaker waterproof', 'bluetooth-speaker-waterproof', 'Loud portable speaker with strong battery life. Minor cosmetic scratches, works perfectly.', 950.00, 'good', 'active', 'Umlazi', 'Durban', 1, 1);

INSERT INTO listing_images (listing_id, image_path, alt_text, is_primary)
VALUES
  (1, 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=520&q=55', 'Ankara bomber jacket', 1),
  (2, 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=520&q=55', 'Refurbished phone', 1),
  (3, 'https://images.unsplash.com/photo-1535632787350-4e68ef0ac584?auto=format&fit=crop&w=520&q=55', 'Beaded necklace', 1),
  (4, 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?auto=format&fit=crop&w=520&q=55', 'Bluetooth speaker', 1);

INSERT INTO orders (id, buyer_id, seller_id, listing_id, amount, buyer_protection_fee, state, delivery_method, delivery_address, tracking_ref)
VALUES
  (1, 3, 2, 1, 850.00, 21.25, 'in_escrow', 'courier', '12 Market Street, Khayelitsha, Cape Town', NULL),
  (2, 3, 4, 2, 8499.00, 212.48, 'shipped', 'courier', '12 Market Street, Khayelitsha, Cape Town', 'PEP-7783412'),
  (3, 3, 2, 3, 320.00, 8.00, 'released', 'pickup', 'Soweto taxi rank meetup', NULL);

INSERT INTO payments (order_id, provider, sandbox_reference, amount, status, confirmed_at)
VALUES
  (1, 'PayFast Sandbox', 'PF-DEMO-1001', 871.25, 'confirmed', CURRENT_TIMESTAMP),
  (2, 'PayFast Sandbox', 'PF-DEMO-1002', 8711.48, 'confirmed', CURRENT_TIMESTAMP),
  (3, 'PayFast Sandbox', 'PF-DEMO-1003', 328.00, 'confirmed', CURRENT_TIMESTAMP);

INSERT INTO messages (sender_id, receiver_id, listing_id, body)
VALUES
  (3, 2, 1, 'Hi Lerato, is the bomber jacket still available in medium?'),
  (2, 3, 1, 'Yes, medium is available. I can ship or meet at Maponya Mall.'),
  (3, 4, 2, 'Can I collect the phone tomorrow if payment is held on KasiSwap?');

INSERT INTO disputes (order_id, opened_by, reason, details, status)
VALUES
  (2, 3, 'Item condition needs review', 'The phone works, but the back cover has a crack that was not clear in the listing photos.', 'under_review');

INSERT INTO verification_requests (seller_id, request_type, status, document_path)
VALUES
  (4, 'id', 'pending', 'uploads/demo-id-placeholder.pdf');

INSERT INTO reviews (order_id, author_id, target_id, rating, comment)
VALUES
  (3, 3, 2, 5, 'Smooth handover and great communication.');

INSERT INTO audit_logs (actor_id, action, entity_type, entity_id, details)
VALUES
  (1, 'seed_database', 'system', NULL, 'Initial demo data loaded for Deliverable 2 testing.'),
  (3, 'open_dispute', 'order', 2, 'Buyer opened a demo dispute for admin review.'),
  (4, 'request_verification', 'verification_request', 1, 'Seller requested ID verification.');
