# E-commerce

A bilingual (English / Greek) online store with a custom admin panel, built on Laravel and Filament.

## Features

**Storefront**
- Home page with banners, offers and recommended products
- Category pages, search, outlet and best sellers
- Product pages with images, specification fields and reviews
- Cart and checkout with delivery and store pickup options
- Contact form, B2B account request, customer login and registration
- Static pages (shipping, FAQ, cookie policy, privacy policy, terms)
- Localized routes (`/en/...`, `/el/...`)

**Admin panel** (`/admin`)
- Category and product tree with bulk move / delete
- Product editor, quick add and quick price / stock update
- Excel import and export of the catalog
- Manufacturers, stock statuses, extra fields and reviews
- Menu manager, home page banners, offers and recommended products
- Customers with pricing groups
- Supplier feeds: register CSV, XML or JSON feeds, map remote categories to local ones and sync prices, stock and product data

## Tech stack

- PHP 8.3+, Laravel 13
- Filament 5
- Tailwind CSS, Alpine.js, Vite
- SQLite by default (MySQL supported)

## Getting started

```bash
git clone <repository-url> e-commerce
cd e-commerce
composer setup
composer dev
```

`composer setup` installs the PHP and Node dependencies, creates `.env`, generates the app key, runs the migrations and builds the assets. `composer dev` starts the web server, queue worker, log viewer and Vite together.

Create a user and give it admin access (only users marked as admin can open `/admin`):

```bash
php artisan make:filament-user
php artisan user:make-admin you@example.com
```

The storefront runs at `http://localhost:8000` and the admin panel at `http://localhost:8000/admin`.

## Configuration

Copy `.env.example` to `.env` and adjust at least:

| Variable | Description |
| --- | --- |
| `APP_NAME` | Store name shown in titles and emails |
| `APP_URL` | Public URL of the store |
| `DB_*` | Database connection |
| `MAIL_*` | Mail transport for contact and order emails |
| `CONTACT_MAIL_TO` | Address that receives contact and order requests |

### Supplier feeds

Suppliers can be added from the admin panel (**Supplier Bridges**) or registered in `config/services.php` under the `suppliers` key, with URLs and credentials read from `.env`. Supported feed types are `csv`, `xml` and `json`.

## Artisan commands

| Command | Description |
| --- | --- |
| `catalog:import-categories {file}` | Import categories from an Excel export |
| `catalog:import-products {file}` | Import products from an Excel export |
| `catalog:import-product-images-xml {path}` | Import product images from an XML file |
| `catalog:localize-product-images` | Download remote product images to local storage |
| `catalog:normalize-brands` | Merge duplicate manufacturers (`--apply` to write changes) |
| `catalog:normalize-products` | Clean up product titles and specification fields |
| `catalog:restore-brand-backup {timestamp}` | Restore manufacturers from a backup |
| `catalog:clear-products --force` | Delete all catalog products |
| `user:make-admin {email}` | Grant admin panel access (`--revoke` to remove it) |
| `supplier:scan-categories {supplier}` | Read remote categories from a supplier feed |
| `supplier:sync-products {supplier}` | Sync products, prices and stock from a supplier feed |

## Tests

```bash
composer test
```

## License

Released under the [MIT license](https://opensource.org/licenses/MIT).
