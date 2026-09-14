# 🍽️ Lunch Food Log

A secure, mobile-first PHP + SQLite personal food logging PWA for localhost, XAMPP, Termux, or a small PHP server.

## 🚀 Current upgraded features

- 🔐 Secure signup/login with arithmetic CAPTCHA
- 🛡️ CSRF protection and security headers
- ⏱️ Inactive-session timeout + session ID regeneration
- 👤 Professional profile with profile-photo upload/remove and password change
- 🔒 Per-user food records and reports
- 📅 DD-MM-YYYY date entry/display for food records
- 🍳 Six meal windows: Breakfast, Morning Snack, Lunch, Evening Snack, During Duty and Dinner
- 📈 Daily meal-completion progress
- 🔎 Instant food-entry search
- ✏️ Edit and 🗑️ secure delete
- 🔔 Meal notifications + PWA notification support
- 📲 Installable PWA with upgraded offline shell
- 🥗 Nutrition tracking: calories, protein, carbohydrates and fat
- 💪 Protein progress and protein-based recommendations
- ⚖️ Weight-progress history with date-based measurements
- 🤖 Smart rule-based meal recommendations based on recorded nutrition/logging activity
- 🔥 Automatic healthy-eating/logging streaks
- 🏆 Automatic achievements for streaks, logging days, protein and six-meal completion
- 🧠 Personalized Health Dashboard
- 📋 Weekly nutrition and logging summary
- ☁️ Personal JSON backup and safe merge/restore without intentionally deleting existing records
- 📊 Monthly, last-6-months, yearly and custom-range reports
- 👀 Report preview before export
- ⬇️ CSV and real `.xlsx` Excel export
- 📱 Responsive mobile + desktop interface
- 💾 SQLite WAL mode and indexes for local performance

## 🛡️ Existing data protection

The nutrition and weight upgrades use non-destructive SQLite migrations. New nutrition columns default to `0`, and a new `weights` table is created only when needed. Existing food rows are not dropped or replaced. **Always make a backup before updating a phone installation.** The repository currently contains a historical `data/lunch.sqlite`, so the safest phone update procedure is to back up the local database before pulling code.

## 📁 Project structure

```text
lunch-food-log/
├── assets/app.css
├── assets/icon.svg
├── assets/meal-alert.js
├── data/lunch.sqlite
├── uploads/
├── auth.php
├── database.php
├── login.php
├── signup.php
├── logout.php
├── profile.php
├── index.php
├── health.php
├── backup.php
├── edit.php
├── update.php
├── delete.php
├── save.php
├── reports.php
├── export.php
├── manifest.json
├── sw.js
├── php-termux.ini
└── .gitignore
```

## ▶️ Run with Termux

```bash
cd ~/lunch-food-log
php -d opcache.enable=0 -d opcache.enable_cli=0 -d opcache.file_cache="" -S 127.0.0.1:8081 -t .
```

Open Chrome at `http://127.0.0.1:8081`.

## 🧠 Health Dashboard

Open **Health Dashboard** from `health.php` to see today's calories/macros, weekly totals, current logging streak, achievements, personalized recommendations, weight progress, and backup/restore tools. Nutrition numbers are optional so older entries remain usable.

## ☁️ Backup / Restore

`backup.php?action=download` creates a JSON backup for the logged-in account. Importing a backup performs a safe merge: existing matching food/weight records are skipped and existing records are not deliberately deleted.

## 🔐 Security

Passwords use PHP `password_hash()` / `password_verify()`. Food queries are restricted to the logged-in user's `user_id`. State-changing requests use CSRF tokens. Profile photos use randomized filenames and MIME/image validation. For public hosting, keep SQLite and uploads outside the web root or explicitly deny direct access.

## ⏰ Default meal schedule

| Time | Meal |
|---|---|
| 08:00 | Breakfast |
| 10:30 | Morning Snack |
| 12:00 | Lunch |
| 16:30 | Evening Snack |
| 20:00 | During Duty |
| 22:15 | Dinner |

## 🗓️ Date handling

Food-log interface dates use **DD-MM-YYYY**. SQLite stores dates as **YYYY-MM-DD** for reliable sorting/reporting. Weight entry uses the browser's date control.

## 📊 Reports & exports

Reports support Monthly, Last 6 Months, Yearly and Custom Range. Custom ranges use DD-MM-YYYY. CSV and `.xlsx` exports contain only the current user's records.

## 📲 Notifications / offline mode

The service worker caches the application shell and health/report pages for faster repeat access and offline fallback. Meal notifications depend on browser/OS permissions and Android battery behavior; guaranteed alarms after force-stop require a native Android alarm implementation.
