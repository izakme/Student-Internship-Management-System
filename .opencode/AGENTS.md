# AGENTS.md - Project state for OpenCode agents

## Project
Student Internship Management System (SIMS)

### Deploy commands
```bash
# Sync files (after editing locally)
chown ubuntu:ubuntu -R /var/www/html/sims
rsync -avzR -e "ssh -i zak-key.pem" /opt/lampp/htdocs/S-I-M-S/./path/to/file ubuntu@13.126.58.64:/var/www/html/sims/
chown www-data:www-data -R /var/www/html/sims
systemctl reload apache2
```

### VPS
- IP: 13.126.58.64
- SSH key: `zak-key.pem`
- OS: Ubuntu 24.04, 414MB RAM + 1GB swap
- LAMP: Apache 2.4.58, PHP 8.3.6, MariaDB 10.11
- App root: `/var/www/html/sims`
- Document root: `/var/www/html/sims/frontend`
- DB: `internship_db`, user `sims_user` / `sims_pass_2026`
- Admin login: `admin@internship.com` / `admin123`
- Student test: `changawaisaac016@gmail.com` / `student123`

### Key architecture
- `.env` at project root parsed by `Database::loadEnv()`
- PDF: pure PHP, no external libs
- Email: PHP `mail()` via SMTP in `.env` (placeholders currently)
- Rate limiting: session-based (`App::rateLimitCheck`), 5 attempts/15min
- CSRF: hidden fields on all destructive operations, POST-only deletes

### Blocked
- SMTP credentials in `.env` are placeholders - emails won't send until real values configured
- No cron for deadline reminders yet (`scripts/remind_deadlines.php` needs cron job)

### Implemented features
1. Email notifications (Mailer + Application hooks)
2. Resume upload (PDF, 5MB max, finfo MIME validation)
3. Withdraw applications (pending only)
4. Filtering (role/status/search on admin pages)
5. Bulk actions (checkboxes + Select All + bulk delete)
6. PDF export (styled table, header, alternating rows, truncation)
7. .env loader (auto-parse in Database)
8. CSRF on all deletes + POST-only destructive actions
9. Password policy (≥8 chars, uppercase, digit) + email validation
10. Rate limiting (5 attempts/15min per IP, session-based)
11. Password reset (token in password_resets table, email link)
12. Dynamic base URL (App::baseUrl from HTTP_HOST)
13. Cover letter (optional text, displayed in company/admin views)
14. Role-based registration links (?role=student or ?role=company)
15. Loading states (buttons disabled + "Processing…" on submit)
16. Deadline reminders script (scripts/remind_deadlines.php)
