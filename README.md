# 🍽️ Lunch Food Log

A secure, mobile-first PHP + SQLite personal food logging PWA for localhost, XAMPP, Termux, or a small PHP server.

## 🚀 Current upgraded features

- 🔐 Secure signup/login with arithmetic CAPTCHA
- 🛡️ CSRF protection on state-changing actions
- ⏱️ 4-hour inactive-session timeout + session ID regeneration on login
- 👤 Professional profile page with profile-photo upload/remove and password change
- 🔒 Per-user food records and reports
- 📅 DD-MM-YYYY date entry/display throughout the UI
- 🍳 Six meal windows: Breakfast, Morning Snack, Lunch, Evening Snack, During Duty and Dinner
- 📈 Dashboard meal-completion progress (0–100%)
- 🔎 Instant search for today's food entries
- ✏️ Edit and 🗑️ secure delete of food records
- 🔔 Meal notifications, alarm sound, vibration and PWA notification support
- 📲 Installable Android-style PWA with service worker/offline shell
- 📊 Monthly, last-6-months, yearly and custom-range reports
- 👀 Report preview before export
- ⬇️ CSV and real `.xlsx` Excel export
- 📱 Responsive mobile + desktop interface with mobile bottom navigation
- 💾 SQLite with WAL mode, indexes and no external database server
- 🧹 Local database, export files and uploaded profile photos are protected by `.gitignore`

## 📁 Project structure

```text
lunch-food-log/
├── assets/
│   ├── app.css
│   ├── icon.svg
│   └── meal-alert.js
├── data/lunch.sqlite          # local only; ignored by Git
├── uploads/                   # profile photos; ignored by Git
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

Open Chrome:

```text
http://127.0.0.1:8081
```

## 🔐 Security

Passwords use PHP `password_hash()` / `password_verify()`. Food queries are restricted to the logged-in user's `user_id`. State-changing requests use CSRF tokens. Successful login regenerates the session ID, and inactive sessions expire after four hours. User profile photos are stored with randomized filenames and validated as JPG, PNG or WebP images.

For public hosting, keep the SQLite database outside the web root or explicitly deny direct access to `data/` and user-upload directories.

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

The interface accepts and displays **DD-MM-YYYY**. SQLite stores dates as **YYYY-MM-DD** for reliable sorting and reporting.

## 📊 Reports & exports

Use **Reports** for Monthly, Last 6 Months, Yearly or Custom Range. Custom ranges also use DD-MM-YYYY. CSV and `.xlsx` exports contain only the current user's records.

## 📲 Notifications

The PWA can show meal reminders, play an alarm and vibrate on supported devices. Browser/JavaScript timers can be suspended by Android battery optimization or force-stop, so guaranteed alarms after complete app termination require a native Android AlarmManager/WorkManager implementation.
