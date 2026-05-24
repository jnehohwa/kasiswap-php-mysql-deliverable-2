# KasiSwap PHP/MySQL Deliverable 2

This repository contains the files for KasiSwap. It is the submission implementation for Deliverable 2 

Live deployment: https://kasiswap.free.nf/

## Required Stack

- PHP for server-side pages, authentication, role-based access control, order workflow, disputes, admin moderation, and services.
- MySQL for users, listings, orders, payments, messages, disputes, reviews, verification requests, and audit logs.
- HTML rendered through PHP templates.
- CSS for the responsive low-data interface.
- JavaScript for small progressive enhancements such as mobile navigation.

## Local Setup

```bash
cd kasiswap-php-mysql-deliverable-2
docker-compose up --build
```

Open:

- App: http://localhost:8080
- Database UI: http://localhost:8081

Adminer login:

- System: MySQL
- Server: `db`
- Username: `kasiswap`
- Password: `kasiswap`
- Database: `kasiswap`

## Seeded Accounts

All demo users use the password `password`.

| Role | Email |
| --- | --- |
| Admin | admin@kasiswap.test |
| Seller | seller@kasiswap.test |
| Buyer | buyer@kasiswap.test |

## Marking Evidence


- `docs/deliverable-2-submission-draft.md` — report draft 
- `docs/deliverable-2-evidence-pack.md` — diagrams, design evidence, screenshot inventory, and test scenarios.
- `docs/code-samples.md` — PHP, HTML, JavaScript, CSS, and MySQL snippets for the coding section.
- `docs/deliverable-2-final-checklist.md` — final build, demo, deployment, and PDF readiness checks.
- `docs/live-deployment-guide.md` — live-hosting steps for the final non-localhost submission.
- `docs/screenshots/` — captured visual evidence.

## Important Prototype Note

The payment-hold flow is a sandbox simulation for academic demonstration. It models escrow-like order states, but it is not a real regulated escrow service.
