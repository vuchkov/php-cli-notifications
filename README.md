# Push and send email notification

## Create and send PHP/CLI (Cron ready) and MySQL mail notifications

### User Scenarios
- PHP CLI new notification creation (save in MySQL) 
- PHP CLI execution (sending) of email notification/s

### Instructions
1. Update `$email_from` value in `Notification.php`
2. Import `db.sql` (DB table `notifications`)

#### Push new notification in DB:
```
php notif.php push <email> <subject> <body>
```
Replace `<email>`, `<subject>` and `<body>` above with real data.

#### Execute <step> notifications at ones:
```
php notif.php exec <how_many>
```
Replace `<how_many>` (default: 1) with what number you want.

### MySQL database
- Table `notifications` is in `db.sql` 
- Update MySQL / MariaDB server settings in `Db.php`
- `id` - primary key, autoincrement
- `email`
- `subject`
- `body`
- `created` (current timestamp)
- `updated` (timestamp)
- `status` (0-new notification/default, 1-archived/already-sent)

### Integrated logic
- Class `Notification` with methods:
- `__construct()`
- `create()`
- `save()`
- `valid()`
- `exec()`
- `send_mail()`
- `mail_headers()` for PHP mail() only

### Extras
- Docksal - Docker based local development environment 
incl. Nginx / Apache, PHP 8.x,  MySQL 8.x / MariaDB 11.x docker images
