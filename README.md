# 🍽️ Lunch Food Log

A lightweight, mobile-friendly PHP + SQLite food logging dashboard for localhost, XAMPP, Termux, or a small PHP server.

## ✨ Features

- 🔐 User signup and login system
- 🤖 CAPTCHA security check on signup and login
- 👤 User profile management and password change
- 🔒 Each account can access only its own food records and reports
- 📅 DD-MM-YYYY date display and entry format
- 🍳 Breakfast, morning snack, lunch, evening snack, duty meal and dinner timetable
- 🔔 Scheduled meal notifications + browser alarm + vibration
- 📲 Installable Android-style PWA
- 📊 Monthly, last-6-months, yearly and custom-range reports
- ✏️ Edit existing food entries
- 🗑️ CSRF-protected deletion
- ⬇️ CSV and real `.xlsx` Excel workbook export
- 📱 Responsive mobile and desktop design
- 💾 SQLite — no external database server required

## 📁 Project structure

```text
lunch-food-log/
├── assets/meal-alert.js
├── data/lunch.sqlite
├── auth.php
├── database.php
├── login.php
├── signup.php
├── logout.php
├── profile.php
├── index.php
├── edit.php
├── update.php
├── delete.php
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

Open Chrome and visit:

```text
http://127.0.0.1:8081
```

You will be redirected to **Login**. New users can choose **Create account**, complete the CAPTCHA, and then start using the dashboard.

## 🔐 Account security

Passwords are stored with PHP `password_hash()` and checked with `password_verify()`. Login/signup use a simple server-generated arithmetic CAPTCHA, CSRF tokens protect state-changing actions, sessions are regenerated on successful login, and food queries are restricted to the logged-in user's `user_id`.

When the first account is created on an existing installation, legacy food rows that do not yet have a user owner are assigned to that first account so existing data is not lost.

For public hosting, keep the SQLite database outside the web root or deny direct access to `data/`.

## ⏰ Meal schedule

- 08:00 — Breakfast
- 10:30 — Morning Snack
- 12:00 — Lunch
- 16:30 — Evening Snack
- 20:00 — During Duty
- 22:15 — Dinner

## 🗓️ Date format

The UI uses **DD-MM-YYYY**. SQLite stores dates internally as **YYYY-MM-DD** for reliable sorting and reports.

## 📊 Reports

Open **Reports** and choose Monthly, Last 6 Months, Yearly, or Custom Date Range. Custom dates use DD-MM-YYYY in the interface. Exports contain only the current user's records.

## 📲 Notifications

Meal reminders work while the dashboard/PWA is running or resumed. Android/browser battery saving or force-stop can suspend JavaScript timers, so a normal web app cannot guarantee alarms after complete termination. Guaranteed background alarms require a native Android implementation using Android AlarmManager/WorkManager.
