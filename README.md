# Lakun

Lakun is an apartment-community ordering and delivery platform. Customers place requests with nearby vendors, vendors submit bids, and delivery agents fulfill confirmed orders. Administrators manage users, buildings, orders, credits, and financial reporting.

## Features

- Customer registration, order creation, bid selection, payment-proof upload, and order tracking
- Vendor order queues, bidding, and order history
- Delivery-agent task management, credits, earnings, and delivery status updates
- Admin tools for orders, users, buildings, credit packages, settings, and financial reports
- Notifications, chat, ratings, and location-aware vendor discovery
- Versioned JSON API under `api/v1/`

## Tech Stack

- PHP 8.0 or later
- MySQL or MariaDB with PDO MySQL enabled
- Node.js and npm for the Tailwind CSS build
- Apache with `mod_rewrite` for the included URL-routing rules

## Getting Started

1. Clone the repository and install the Node.js dependencies:

   ```sh
   npm install
   ```

2. Create a MySQL/MariaDB database named `taskdoor`, then import [`database/schema.sql`](database/schema.sql) using your database client or phpMyAdmin. This schema-only dump does not include sample users or other seed records.

3. Create a root `.env` file with your local settings:

   ```dotenv
   APP_NAME=Lakun
   APP_URL=http://localhost/lakun
   APP_ENV=development
   APP_DEBUG=true
   JWT_SECRET=replace-with-a-long-random-secret
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=taskdoor
   DB_USER=root
   DB_PASS=
   ```

   Change the database credentials and `APP_URL` to match your environment. Keep real secrets out of version control; use a unique `JWT_SECRET` outside local development.

4. Build the CSS:

   ```sh
   npm run build:css
   ```

5. Configure Apache to serve this directory, enable `mod_rewrite`, and apply the rules in `.htaccess.production`. Set `RewriteBase` in those rules to the path where the app is hosted; the checked-in value is `/lakun/`. Open the configured `APP_URL` in a browser.

For active CSS development, run `npm run watch:css` in a separate terminal.

## Configuration

The database connection reads `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` from `.env`. The app also reads `APP_NAME`, `APP_URL`, `APP_ENV`, `APP_DEBUG`, `JWT_SECRET`, `JWT_EXPIRY`, and `SESSION_NAME`. If database settings are omitted, the defaults are `localhost:3306`, database `taskdoor`, user `root`, and an empty password.

## Project Layout

- `index.php` - Web entry point and page routing
- `api/` - JSON API endpoints, including the versioned API in `api/v1/`
- `app/` - Configuration, controllers, models, helpers, and services
- `views/` - Authentication and role-specific application pages
- `assets/` - CSS and JavaScript assets
- `database/schema.sql` - Database schema without sample records
- `uploads/` - User-uploaded files such as payment proofs
- `vendor/` - PHP QR-code decoder used by the receipt parser
- `user-flow.html` - Visual overview of the user workflows
- `TaskDoor_API_Docs.pdf` - API reference

## Notes

- Configure uploads and permissions appropriately for your hosting environment.
- Replace development secrets and review database sample records before deploying.
- The original data-bearing SQL dump is kept local and excluded from Git.