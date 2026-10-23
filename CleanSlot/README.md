# CleanSlot - laundry & dry cleaning website (PHP + PDO + MySQL)

## Run it (3 steps)
1. In phpMyAdmin open the **Import** tab and import `schema.sql`. It creates the database, all tables, sample services, shop products and the admin account.
2. Check `config/Database.php` (host, port, user, password).
3. Put the `CleanSlot` folder in `htdocs` and open `http://localhost/CleanSlot/`
   (or run `php -S localhost:8000` inside the folder).

**Admin:** `admin@cleanslot.com` / `Admin@12345`. Log in on the normal Login page, admins are sent to the dashboard automatically. Change the password after the first login.

Your phone, email, address, working hours and pickup times are in one place: `CONTACT` and `SLOTS` in `includes/functions.php`.

## Pages
| Page | File | Who |
|------|------|-----|
| Home | `index.php` | everyone |
| Services (types, categories, sub-categories, search, price, sort) | `services.php` | everyone |
| Shop | `shop.php` | everyone |
| Our Story | `our-story.php` | everyone |
| B2B Care (offer form) | `b2b.php` | everyone |
| Franchise (application form) | `franchise.php` | everyone |
| Contact | `contact.php` | everyone |
| Support (FAQ + form) | `support.php` | everyone |
| Login + Register (one page, also for admins) | `login.php` | everyone |
| Cart + checkout (date, time, address, payment) | `cart.php` | cart for everyone, checkout needs login |
| My orders | `orders.php` | logged-in customers |
| Admin: orders and status | `admin/index.php` | admin |
| Admin: services and shop products | `admin/items.php` | admin |
| Admin: messages from all forms | `admin/inquiries.php` | admin |

## Folders
- `config/`   database connection
- `classes/`  `User`, `Item`, `Order`, `Inquiry` (all database work)
- `includes/` header, footer, banner, shared forms, helper functions
- `assets/css/style.css` the whole design, light and dark theme (colors are variables at the top)
- `assets/js/app.js` small script: theme button, quantity buttons, counters

## Tables
`users`, `items` (services and products in one table, `kind` = service | product), `orders`, `order_items`, `inquiries` (topic = contact | support | b2b | franchise).

A service has three levels, like the filters on the Services page: `type` (the top buttons), `category`, `subcategory`.
Add or edit them in **Admin > Services & Shop**, new types and categories appear on the website by themselves.

## Photos
Photos are real photographs from Unsplash, set in one list: `PHOTOS` in `includes/functions.php`.
To use your own picture, put the file in `assets/img/` and write its path in that list, for example `'hero' => 'assets/img/hero.jpg'`.
For one service or product, upload a photo in **Admin > Services & Shop**.
If a picture cannot load, a neutral placeholder is shown instead of a broken image.

## Security included
CSRF tokens on every form, prepared statements (PDO), hashed passwords, session cookie flags, login pause after 5 wrong tries,
form spam protection (hidden field + limit), uploads checked by real file type and blocked from running as code.

## If Services / Shop look empty
The site now fills the sample services and products by itself the first time it finds an empty `items` table (see `config/seed.php`).
Photos: for a picture that never depends on the internet, save it as `assets/img/photos/<key>.jpg` (keys: hero, machines, rack, hangers, story, ...). A local file always wins over the Unsplash link.
