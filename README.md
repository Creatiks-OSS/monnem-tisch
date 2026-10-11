# Monnemtisch Catering

Premium Catering Platform – Laravel 12  
**Monnemtisch Catering** | Stuttgart & Umgebung

---

## Requirements / Voraussetzungen

- PHP **8.2+** (extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`)
- [Composer](https://getcomposer.org/)
- Node.js **18+** & npm
- MySQL **8+** (or MariaDB 10.4+)

---

## Installation

### English

1. Clone the repository  
   `git clone <repo-url> catering && cd catering`
2. Install PHP dependencies  
   `composer install`
3. Install frontend dependencies  
   `npm install`
4. Copy environment file  
   `cp .env.example .env`  
   (Windows: `copy .env.example .env`)
5. Configure the database in `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, …)
6. Generate app key  
   `php artisan key:generate`
7. Run migrations and seeders  
   `php artisan migrate --seed`
8. Link storage  
   `php artisan storage:link`
9. Build frontend assets  
   `npm run build`
10. Start the development server  
    `php artisan serve`

Open: [http://127.0.0.1:8000](http://127.0.0.1:8000)  
Admin: [http://127.0.0.1:8000/admin/login](http://127.0.0.1:8000/admin/login)

### Deutsch

1. Repository klonen  
   `git clone <repo-url> catering && cd catering`
2. PHP-Abhängigkeiten installieren  
   `composer install`
3. Frontend-Abhängigkeiten installieren  
   `npm install`
4. Umgebungsdatei kopieren  
   `cp .env.example .env`  
   (Windows: `copy .env.example .env`)
5. Datenbank in `.env` konfigurieren (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, …)
6. App-Key erzeugen  
   `php artisan key:generate`
7. Migrationen und Seeders ausführen  
   `php artisan migrate --seed`
8. Storage verlinken  
   `php artisan storage:link`
9. Assets bauen  
   `npm run build`
10. Entwicklungsserver starten  
    `php artisan serve`

---

## Admin Login

| Field | Value |
|-------|--------|
| E-Mail | `admin@example.com` |
| Passwort | `password` |

On first login, `must_change_password` is set to `true` – change the password in production.

A secondary account `admin@velora-catering.de` / `password` is also seeded with the **Super Admin** role.

---

## Queue / Warteschlange

Queued mail and jobs use `QUEUE_CONNECTION=database` by default.

```bash
php artisan queue:work
```

For local development you can also use:

```bash
php artisan queue:listen --tries=1
```

Or set `QUEUE_CONNECTION=sync` in `.env` to process jobs immediately.

---

## Scheduler / Cron

Register Laravel’s scheduler (production):

```cron
* * * * * cd /path/to/catering && php artisan schedule:run >> /dev/null 2>&1
```

On Windows (Task Scheduler) run every minute:

```bat
php C:\xampp\htdocs\catering\artisan schedule:run
```

---

## Tests

PHPUnit is configured for **SQLite in-memory** (`phpunit.xml`).

```bash
php artisan test
```

---

## Useful commands

```bash
npm run dev          # Vite HMR
php artisan migrate:fresh --seed
php artisan optimize:clear
```

---

## License

Proprietary – Monnemtisch Catering.
