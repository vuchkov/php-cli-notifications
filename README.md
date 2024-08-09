# Push and send email notification

## Create and send PHP/CLI (Cron ready) and MySQL email notifications

### Integrations
- Integrated `Mailgun` Web API
- Integrated PHP `mail()` (optionally)

### User Scenarios
- PHP CLI new notification creation (save in MySQL) 
- PHP CLI execution (sending) of the email notification/s

### Installation
1. Import `db.sql` (DB table `notifications`)
2. Update `$email_from` value in class `Notification.php`
3. (Mailgun only) Install official mailgun/mailgun-php library: <br>
`composer install`
4. (Mailgun only) Set up your mailgun: API key, domain and endpoint <br>
in `Notification.php`

<br>

#### Push / Save new notification into DB:
```
php notif.php push <email> <subject> <body>
```
Replace `<email>`, `<subject>` and `<body>` above with real data.

<br>

#### Execute / Send notifications:
Run:
```
php notif.php exec
```

or:
```
php notif.php exec <how_many>
```
replace `<how_many>` (default: 1) with how many notifications you want to send <br>
at ones.

or .log both output & error:
```
php notif.php exec 100 &>> notif.log
```

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
- `list()` DB notifications array

### Extras
- Docksal - Docker based local development environment 
incl. Nginx / Apache, PHP 8.x,  MySQL 8.x / MariaDB 11.x docker images
