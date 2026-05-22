# ITECA3-12 Deliverable 2: KasiSwap Prototype and Design Evidence

## 2.1 Introduction
KasiSwap is a Customer-to-Customer e-commerce prototype for township and informal-economy users in South Africa. The platform allows buyers and sellers to trade goods through searchable listings, in-app messaging, protected order states, seller verification, reviews, disputes, and an administrative website. It was developed using PHP, MySQL, HTML, CSS, and JavaScript, with a low-data responsive interface and role-based access control for buyers, sellers, and administrators.

## 2.2 Prototype Screenshots
The prototype includes both the main C2C website and the admin website.

Main website screenshots:
- `docs/screenshots/main-marketplace-mobile.png`
- `docs/screenshots/main-marketplace-tablet.png`
- `docs/screenshots/main-marketplace-desktop.png`
- `docs/screenshots/main-listing-detail-desktop.png`
- `docs/screenshots/main-checkout-desktop.png`
- `docs/screenshots/buyer-orders-desktop.png`
- `docs/screenshots/buyer-disputes-desktop.png`
- `docs/screenshots/buyer-messages-desktop.png`

Admin website screenshots:
- `docs/screenshots/admin-dashboard-mobile.png`
- `docs/screenshots/admin-dashboard-tablet.png`
- `docs/screenshots/admin-dashboard-desktop.png`
- `docs/screenshots/admin-users-desktop.png`
- `docs/screenshots/admin-verification-desktop.png`
- `docs/screenshots/admin-listings-desktop.png`
- `docs/screenshots/admin-disputes-desktop.png`
- `docs/screenshots/admin-audit-desktop.png`

MySQL evidence screenshots:
- `docs/screenshots/mysql-adminer-tables-desktop.png`
- `docs/screenshots/mysql-adminer-users-table-desktop.png`

## 2.3 Design Diagrams
Use the diagrams in `docs/deliverable-2-evidence-pack.md`:
- CRC cards
- Enhanced Entity Relationship Diagram
- Context Diagram
- Data Flow Diagram
- Use Case Diagram
- Database schema summary

## 2.4 Coding Evidence
Use `docs/code-samples.md` for the final PDF snippets:
- PHP login/RBAC and order state logic
- HTML/PHP listing card template
- JavaScript navigation/form enhancement
- CSS responsive grid
- MySQL `orders` schema

## 2.5 Live Deployment
The prototype is deployed as a live PHP/MySQL website so it can be assessed without using localhost. The deployment guide and verification notes are in `docs/live-deployment-guide.md`.

- Live website URL: `https://kasiswap.free.nf/`
- GitHub repository URL: `https://github.com/jnehohwa/kasiswap-frontend-core`

## 2.6 Conclusion
KasiSwap demonstrates a functional C2C e-commerce platform that responds directly to the South African informal-economy scenario. The system supports buyers, sellers, and administrators, while addressing trust risk through seller verification, in-app messaging, protected order states, dispute handling, and audit logs. The prototype also applies secure development practices such as prepared statements, hashed passwords, CSRF protection, session-based authentication, and role-based access control. This makes the project suitable for the Deliverable 2 requirements and provides a strong foundation for live deployment and future portfolio improvement.
