# BarakahOS Productivity Tracker

Personal productivity and discipline tracking tool.

Live URL: https://sakibweb.com/tools/barakahos

## Features

- Dashboard with daily productivity summary
- Routine templates and fixed task tracking
- Daily checklist and task activity
- Focus session tracker
- Distraction and wasted time logging
- Prayer tracking
- Sleep tracker
- Daily review
- Daily, weekly, and monthly reports
- Weekly discipline score

## Requirements

- PHP 8.2+
- MySQL or MariaDB
- Web server with URL rewriting enabled

## Local Setup

1. Create a database.
2. Import `database/schema.sql`.
3. Copy `.env.example` to `.env`.
4. Update database credentials and app URL in `.env`.
5. Run the app from the `public` directory.

Example local server:

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

## Production `.env`

Use production-safe values before going live:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL="https://sakibweb.com/tools/barakahos"
```

Also update `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS`.

## Deployment Notes

- Enable HTTPS.
- Keep `.env` private and inaccessible from the web.
- Import or migrate the database before first use.
- Keep regular database backups.
- Verify login, dashboard, reports, prayers, focus sessions, distractions, sleep tracker, and daily review after deployment.

## Notes

This project is optimized for personal use. Some models include small automatic schema repair helpers for existing databases. If the app becomes multi-user/public, replace those with formal migrations.
