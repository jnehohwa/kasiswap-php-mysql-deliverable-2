# KasiSwap Live Deployment Guide

## Deployment Target
The assignment does not allow a localhost-only submission, so the final submission must include a live hosted URL. Because this project is PHP/MySQL, the safest student-friendly deployment target is a shared PHP/MySQL host such as InfinityFree, 000webhost-style hosting, cPanel hosting, or any LAMP-compatible provider.

Current hosted deployment:

- Live website URL: `https://kasiswap.free.nf/`
- Hosting provider: InfinityFree
- Database name: `if0_41912191_kasiswap`

## Recommended Hosting Shape
- PHP 8.x enabled.
- MySQL database available.
- Public web root points to the `public/` folder, or files from `public/` are copied into the host's `htdocs` / `public_html` folder.
- The `app/` folder must not be publicly browsable. It is protected with `.htaccess`, but best practice is to keep it outside the public web root if the host allows that.

## Files to Upload
An upload-ready package can be generated in `build/kasiswap-infinityfree-upload.zip`. Its intended structure is:

```text
hosting-account-root/
  app/
  database/
  htdocs/             <- public website files
```

Use this structure if the host lets you place files outside the web root:

```text
hosting-account-root/
  app/
  database/
  public_html/        <- contents of php-app/public go here
```

If the host only allows uploading into one web folder, upload the contents of `public/` into that folder and place `app/` inside the same folder. The public bootstrap file checks both `../app/bootstrap.php` and `app/bootstrap.php`, so either layout works. Keep the `.htaccess` deny files in protected folders.

## Database Setup
1. Create a MySQL database in the hosting control panel.
2. Create or copy the database username, password, host, and database name.
3. Import `database/01_schema.sql`.
4. Import `database/02_seed.sql`.
5. Confirm the tables exist: `users`, `listings`, `orders`, `payments`, `disputes`, `audit_logs`, and the other supporting tables.

## Live Config
1. Copy `app/config.example.php` to `app/config.local.php`.
2. Replace the placeholder database credentials with the host's real credentials.
3. Do not commit `config.local.php`; it is ignored by Git.

Example:

```php
return [
    'db_host' => 'sqlXXX.infinityfree.com',
    'db_port' => '3306',
    'db_name' => 'if0_00000000_kasiswap',
    'db_user' => 'if0_00000000',
    'db_pass' => 'your-hosting-password',
];
```

## Final Submission Links
Use these in the final PDF:

- Live website URL: `https://kasiswap.free.nf/`
- GitHub repository URL: `https://github.com/jnehohwa/kasiswap-frontend-core`

## Live Verification Checklist
- Home page opens without localhost.
- Marketplace page lists seeded products.
- Buyer can log in with `buyer@kasiswap.test` / `password`.
- Seller can log in with `seller@kasiswap.test` / `password`.
- Admin can log in with `admin@kasiswap.test` / `password`.
- `/admin/index.php` redirects to sign-in when logged out.
- Adminer/database screenshots are captured before submission, or equivalent hosting control-panel screenshots are captured after deployment.
