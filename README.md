# 🍽️ Lunch Food Log

A lightweight, mobile-friendly PHP + SQLite food logging dashboard for localhost, XAMPP, Termux, or a small PHP server.

## ✨ Features

- 📅 DD-MM-YYYY date display and entry format
- 🍳 Breakfast, morning snack, lunch, evening snack, duty meal and dinner timetable
- 🔔 Optional meal reminders while the dashboard is open
- 📊 Monthly, last-6-months, yearly and custom-range reports
- ✏️ Edit existing food entries
- 🗑️ CSRF-protected deletion from the dashboard
- 🔎 Report preview with meal, quantity, time and notes
- ⬇️ CSV and Excel-compatible export
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
├── edit.php
├── export.php
├── index.php
├── reports.php
├── save.php
├── update.php
├── php-termux.ini
└── .gitignore
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

## 🔐 Security notes

Database writes use prepared statements and displayed values are escaped. Save and update actions validate CSRF tokens and server-side input lengths. Delete actions require a valid session CSRF token before removing an entry. For public hosting, keep the SQLite database outside the web root or configure the web server to deny direct access to the `data/` directory.

The `.gitignore` prevents newly created local SQLite database files and temporary exports from being committed. Existing personal database history already tracked by Git should be removed from version control before public deployment if it contains private records.

## 🚀 Recommended next improvements

- Dashboard search/filter controls
- Dashboard charts and nutrition statistics
- Real `.xlsx` workbook export using a spreadsheet library
- PWA/offline support and reliable background reminders
- Backup and restore
- Optional user accounts for multi-user hosting
