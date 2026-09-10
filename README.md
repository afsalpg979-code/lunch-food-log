# 🍽️ Lunch Food Log

A lightweight, mobile-friendly PHP + SQLite food logging dashboard for localhost, XAMPP, Termux, or a small PHP server.

## ✨ Features

- 📅 DD-MM-YYYY date display and entry format
- 🍳 Breakfast, morning snack, lunch, evening snack, duty meal and dinner timetable
- 🔔 Optional meal reminders while the dashboard is open
- 📊 Monthly, last-6-months, yearly and custom-range reports
- 🔎 Food history with report preview
- ⬇️ CSV and Excel-compatible export
- 🗑️ Secure POST-based entry deletion
- 📱 Responsive mobile and desktop design
- 💾 SQLite — no external database server required
- 🔒 Prepared SQL statements and server-side validation

## 📁 Project structure

```text
lunch-food-log/
├── assets/
│   └── meal-alert.js
├── data/
│   └── lunch.sqlite
├── database.php
├── delete.php
├── export.php
├── index.php
├── reports.php
├── save.php
└── php-termux.ini
```

## ▶️ Run with Termux

```bash
cd ~/lunch-food-log
php -d opcache.enable=0 -d opcache.enable_cli=0 -d opcache.file_cache="" -S 127.0.0.1:8081 -t .
```

Open:

```text
http://127.0.0.1:8081
```

If your PHP installation supports the project configuration directly:

```bash
php -c php-termux.ini -S 127.0.0.1:8081 -t .
```

## 🗓️ Date format

The UI uses **DD-MM-YYYY**. SQLite stores dates internally as **YYYY-MM-DD** so sorting and date-range queries remain reliable.

## 📊 Reports

Open **Reports** and choose:

- Monthly
- Last 6 Months
- Yearly
- Custom Date Range

Custom dates use DD-MM-YYYY in the interface.

## 🔐 Security

Database writes use prepared statements and displayed values are escaped. Destructive actions should use POST requests and CSRF protection. If deployed publicly, keep the SQLite database outside the web root or deny direct access to the `data/` directory.

## 🚀 Recommended next improvements

- Edit existing entries
- Search/filter history directly from the dashboard
- Dashboard charts and nutrition statistics
- PWA/offline support
- Backup and restore
- Optional user accounts for multi-user hosting
