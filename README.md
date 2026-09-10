# 🍽️ Lunch Food Log

A lightweight, mobile-friendly PHP + SQLite food logging dashboard. Built to run easily on localhost, XAMPP, Termux, or a small PHP server.

## ✨ Features

- 📅 Add food entries using **DD-MM-YYYY** display format
- 🍳 Breakfast, snacks, lunch, duty meal and dinner timetable
- 🔔 Optional meal reminders while the dashboard is open
- 📊 Monthly, last-6-months, yearly and custom-range reports
- 👀 Report preview with meal, quantity, time and notes
- ⬇️ CSV / Excel-compatible export
- 🗑️ Delete individual food entries
- 📱 Responsive mobile and desktop design
- 💾 SQLite database — no external database server required
- 🔒 Prepared SQL statements and server-side date validation

## 📁 Main files

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

## ▶️ Run locally

### PHP built-in server

```bash
cd lunch-food-log
php -S 127.0.0.1:8081 -t .
```

Then open:

```text
http://127.0.0.1:8081
```

### Termux

If PHP needs the Termux-specific configuration used by this project:

```bash
php -c php-termux.ini -S 127.0.0.1:8081 -t .
```

## 🗓️ Date format

The interface uses **DD-MM-YYYY** for people entering and viewing dates. The database continues to store dates internally as `YYYY-MM-DD`, which keeps sorting and date-range queries reliable.

## 📊 Reports

Open **Reports** and choose:

- Monthly
- Last 6 Months
- Yearly
- Custom Date Range

Custom ranges also use DD-MM-YYYY in the interface.

## 🔐 Data and security

The app stores data in a local SQLite database. User-entered values are escaped when displayed and database writes use prepared statements. Keep the `data/` directory protected if the project is deployed publicly.

## 🚀 Planned improvements

- Edit existing entries
- Search and filter food history
- Dashboard charts and meal statistics
- Optional PWA/offline support
- Backup and restore database
- User accounts if multi-user hosting is needed
