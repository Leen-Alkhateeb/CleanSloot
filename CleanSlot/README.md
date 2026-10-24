<div align="center">

# 🧺 CleanSlot

**Eco-friendly dry cleaning, ironing, shoe care and laundry website.**
Customers order online, we pick up and deliver.

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-PDO-4479A1?logo=mysql&logoColor=white)
![Theme](https://img.shields.io/badge/Theme-Light%20%2F%20Dark-2d3e4f)
![Status](https://img.shields.io/badge/Status-Ready%20to%20run-7cc15f)

</div>

---

## ✨ Features

- 🧼 **Services catalog** with three levels (type → category → subcategory), search, price filter and sorting
- 🛍️ **Shop** for products, with cart and checkout (date, time slot, address, payment)
- 👤 **Customer accounts**: register, login, and a personal **My orders** page
- 🛠️ **Admin dashboard**: manage orders and statuses, services and products, and all incoming messages
- 📨 **Forms**: Contact, Support (FAQ + form), B2B offer, Franchise application
- 🌗 **Light and dark theme** (follows the device setting, and remembers the user's choice)
- 🔒 **Security built in** (see [Security](#-security))

---

## 🚀 Quick start

1. **Import the database.** In phpMyAdmin open the **Import** tab and import `schema.sql`.
   It creates the database, all tables, sample services, shop products and the admin account.
2. **Check the connection.** Open `config/Database.php` and confirm host, port, user and password.
3. **Run the site.** Put the project folder inside `htdocs` and open it in the browser:

   ```text
   http://localhost/CleanSlot/
   ```

   Or use PHP's built-in server from inside the project folder:

   ```bash
   php -S localhost:8000
   ```

> 💡 The project path is detected automatically, so links work from any folder name.

### 🔑 Admin login

| Email | Password |
|-------|----------|
| `admin@cleanslot.com` | `Admin@12345` |

Log in from the normal **Login** page. Admins are sent to the dashboard automatically.
**Change the password after the first login.**

---

## 🗺️ Pages

| Page | File | Access |
|------|------|--------|
| Home | `index.php` | Everyone |
| Services (types, categories, subcategories, search, price, sort) | `services.php` | Everyone |
| Shop | `shop.php` | Everyone |
| Our Story | `our-story.php` | Everyone |
| B2B Care (offer form) | `b2b.php` | Everyone |
| Franchise (application form) | `franchise.php` | Everyone |
| Contact | `contact.php` | Everyone |
| Support (FAQ + form) | `support.php` | Everyone |
| Login + Register (one page, also for admins) | `login.php` | Everyone |
| Cart + checkout | `cart.php` | Cart: everyone · Checkout: login required |
| My orders | `orders.php` | Logged-in customers |
| Admin: orders and status | `admin/index.php` | Admin |
| Admin: services and shop products | `admin/items.php` | Admin |
| Admin: messages from all forms | `admin/inquiries.php` | Admin |

---

## 📁 Project structure

```text
CleanSlot/
├── admin/                  Admin pages (orders, items, inquiries)
├── assets/
│   ├── css/style.css       The whole design (colors are variables at the top)
│   ├── js/app.js           Theme button, quantity buttons, counters
│   └── img/                Logo, favicon and optional local photos
├── classes/                All database work: User, Item, Order, Inquiry
├── config/
│   ├── Database.php        Database connection
│   └── seed.php            Sample data, filled automatically on first run
├── includes/               Header, footer, banner, shared forms, helper functions
├── uploads/                Uploaded photos (blocked from running as code)
├── *.php                   Public pages
└── schema.sql              Database structure + sample data
```

---

## 🗄️ Database

| Table | Purpose |
|-------|---------|
| `users` | Customers and admins |
| `items` | Services **and** products in one table (`kind` = `service` or `product`) |
| `orders` | Customer orders |
| `order_items` | Items inside each order |
| `inquiries` | Messages from all forms (`topic` = `contact`, `support`, `b2b`, `franchise`) |

A service has three levels, matching the filters on the Services page:
`type` (top buttons) → `category` → `subcategory`.
Add or edit them in **Admin → Services & Shop**. New types and categories appear on the website by themselves.

---

## 🎨 Customizing

### Business details
Phone, email, address, working hours and pickup times live in one place:
`CONTACT` and `SLOTS` in `includes/functions.php`.

### Photos
- All site photos are set in one list: `PHOTOS` in `includes/functions.php` (Unsplash photos by default).
- **Use your own picture:** put the file in `assets/img/` and write its path in the list,
  for example `'hero' => 'assets/img/hero.jpg'`.
- **Work offline:** save a picture as `assets/img/photos/<key>.jpg`
  (keys: `hero`, `machines`, `rack`, `hangers`, `story`, ...). A local file always wins over the Unsplash link.
- **One service or product:** upload a photo in **Admin → Services & Shop**.
- If a picture cannot load, a neutral placeholder is shown instead of a broken image.

### Colors and theme
Edit the color variables at the top of `assets/css/style.css`. Light and dark themes both use them.

---

## 🔒 Security

- CSRF tokens on every form
- Prepared statements (PDO)
- Hashed passwords
- Session cookie flags (`httponly`, `samesite`, `secure` on HTTPS)
- Login pause after 5 wrong tries
- Form spam protection (hidden field + rate limit)
- Uploads checked by real file type and blocked from running as code

---

## 🧯 Troubleshooting

| Problem | Fix |
|---------|-----|
| **Services / Shop look empty** | The site fills sample data by itself when it finds an empty `items` table (see `config/seed.php`). Make sure `schema.sql` was imported first. |
| **Database error on every page** | Check host, port, user and password in `config/Database.php`, and that MySQL is running in XAMPP. |
| **`Call to undefined function ...`** | A helper function is missing. All helpers (`e()`, `url()`, `pull_flash()`, `cart_count()`, `csrf_field()`, ...) belong in `includes/functions.php`, which every page must load before the header. |
| **Page shows only the header** | Turn on error display while developing: `ini_set('display_errors', '1'); error_reporting(E_ALL);` at the top of `includes/functions.php`. Also check the browser console for a missing `app.js`. |
| **Broken links or missing CSS** | Open the site through `http://localhost/...`, not by double-clicking the file. |

---

<div align="center">

Made with 🧡 in Jordan

</div>
