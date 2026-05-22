# KasiSwap Demo and Testing Runbook

## 1. Start the App
From the repo:

```bash
cd "/Users/joshuanehohwa/Documents/New project/kasiswap-frontend-core/php-app"
docker-compose up -d
```

Open:

- App: `http://localhost:8080`
- Database/Adminer: `http://localhost:8081`

Demo accounts:

| Role | Email | Password |
| --- | --- | --- |
| Buyer | `buyer@kasiswap.test` | `password` |
| Seller | `seller@kasiswap.test` | `password` |
| Admin | `admin@kasiswap.test` | `password` |

## 2. Quick Health Checks
Run these before practicing the demo:

```bash
docker-compose exec -T app sh -lc 'find /var/www/html -name "*.php" -print0 | xargs -0 -n1 php -l'
docker-compose exec -T db mysql -hdb -ukasiswap -pkasiswap kasiswap -e 'SELECT COUNT(*) AS users FROM users; SELECT COUNT(*) AS listings FROM listings; SELECT COUNT(*) AS orders FROM orders;'
```

Expected seeded state:

- `users`: 4
- `listings`: 4
- `orders`: 3

## 3. Demo Walkthrough Order
Use this exact order during your presentation.

### A. Public Marketplace
1. Open `http://localhost:8080`.
2. Explain the project aim: low-data, trust-first C2C marketplace for township/informal-economy users.
3. Open Marketplace.
4. Show search/filter/sort.
5. Open a listing.
6. Point out seller verification, township location, price, delivery, and payment-hold messaging.

### B. Buyer Flow
1. Sign in as `buyer@kasiswap.test`.
2. Open Marketplace and choose an active item.
3. Click **Buy with protection**.
4. Create a protected order.
5. Open Orders.
6. Click **Simulate PayFast payment**.
7. Explain: the order moves from `pending_payment` to `in_escrow`.
8. Open Disputes and show how an issue can be logged.
9. Open Messages and show in-app communication.

### C. Seller Flow
1. Logout.
2. Sign in as `seller@kasiswap.test`.
3. Open Seller workspace.
4. Create or edit a listing.
5. Open Orders.
6. Show that seller actions depend on payment confirmation.
7. If an order is `in_escrow`, mark it shipped with a tracking reference.

### D. Admin Flow
1. Logout.
2. Sign in as `admin@kasiswap.test`.
3. Open Admin.
4. Show the dashboard stats.
5. Open User Types and explain buyer/seller/admin RBAC.
6. Open Verification and approve/reject a seller verification request.
7. Open Listings and show moderation.
8. Open Disputes and show admin resolution.
9. Open Audit Logs and explain traceability.

## 4. Features to Explicitly Mention
- Built with PHP, MySQL, HTML, CSS, and JavaScript.
- Uses PDO prepared statements.
- Passwords are hashed with PHP password hashing.
- Forms use CSRF tokens.
- Admin pages are RBAC-protected.
- Order state machine reduces fake-payment handover risk.
- Disputes and audit logs support accountability.
- Interface is responsive and low-data conscious.
- Payment-hold flow is a sandbox prototype, not real regulated escrow.

## 5. Things to Test Before Submission
- Anonymous user cannot open `/admin/index.php`; they are redirected to sign-in.
- Buyer can log in and create an order.
- Buyer can confirm sandbox payment.
- Seller can mark shipped only after payment is confirmed.
- Admin can create/update user types.
- Admin can approve/reject seller verification.
- Admin can moderate listings.
- Admin can resolve disputes.
- Admin can see audit logs.
- Adminer shows populated MySQL tables.

## 6. Reset Demo Data
If your demo data gets messy, reset to the original seeded state:

```bash
docker-compose down -v
docker-compose up -d
```

Wait until MySQL is healthy, then refresh the site.

## 7. Live Deployment Reminder
For the final submission, localhost is not enough. The live deployment is available here:

- Live website URL: `https://kasiswap.free.nf/`
- GitHub repository URL: `https://github.com/jnehohwa/kasiswap-frontend-core`
- Screenshots from the live hosted site if required

Use `docs/live-deployment-guide.md` for deployment details and final verification checks.
