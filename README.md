# 🍽️ Lunch Food Log

A lightweight, mobile-friendly PHP + SQLite food logging dashboard for localhost, XAMPP, Termux, or a small PHP server.

## ✨ Features

- 📅 DD-MM-YYYY date display and entry format
- 🍳 Breakfast, morning snack, lunch, evening snack, duty meal and dinner timetable
- 🔔 Scheduled meal notifications + browser alarm + vibration
- 📲 Installable Android-style PWA
- 📡 PWA app shell with offline fallback
- 📊 Monthly, last-6-months, yearly and custom-range reports
- ✏️ Edit existing food entries
- 🗑️ CSRF-protected deletion from the dashboard
- 🔎 Report preview with meal, quantity, time and notes
- ⬇️ CSV export and real `.xlsx` Excel workbook export
- 📱 Responsive mobile and desktop design
- 💾 SQLite — no external database server required
- 🔒 Prepared SQL statements and server-side validation

## 📁 Project structure

```text
lunch-food-log/
├── assets/
│   ├── icon.svg
│   └── meal-alert.js
├── data/
│   └── lunch.sqlite
├── manifest.json
├── sw.js
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

## 📲 Install as an Android-style app

1. Open the Food Log in Chrome on Android.
2. Tap **Enable Meal Notifications + Alarm** and allow notifications.
3. When **Install Food Log App** appears, tap it and choose **Install**.
4. Open the installed Food Log from your Android home screen.
5. Keep notifications enabled for the app/site and avoid battery restrictions if you want the best reminder reliability.

The PWA uses a service worker for app-shell caching and Android-style notification cards with vibration and an **Open Food Log** action.

### ⚠️ Alarm limitation

A normal web/PWA app cannot guarantee an exact alarm after Android completely terminates the browser/PWA. JavaScript timers may be paused by Android, battery saver, browser policies, or force-stop. When the app is running or resumed, the scheduler catches up and triggers the appropriate reminder. For guaranteed alarms even after force-stop, the next step would be a native Android app using Android AlarmManager/WorkManager.

## ⏰ Meal schedule

- 08:00 — Breakfast
- 10:30 — Morning Snack
- 12:00 — Lunch
- 16:30 — Evening Snack
- 20:00 — During Duty
- 22:15 — Dinner

## 🗓️ Date format

The UI uses **DD-MM-YYYY**. SQLite stores dates internally as **YYYY-MM-DD** so sorting and date-range queries remain reliable.

## 📊 Reports

Open **Reports** and choose Monthly, Last 6 Months, Yearly, or Custom Date Range. Custom dates use DD-MM-YYYY in the interface.

## 🔐 Security notes

Database writes use prepared statements and displayed values are escaped. Save and update actions validate CSRF tokens and server-side input lengths. Delete actions require a valid session CSRF token before removing an entry. For public hosting, keep the SQLite database outside the web root or configure the web server to deny direct access to the `data/` directory.

The `.gitignore` prevents newly created local SQLite database files and temporary exports from being committed. Existing personal database history already tracked by Git should be removed from version control before public deployment if it contains private records.
