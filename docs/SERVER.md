# Server Configuratie

## Server

- **IP:** 188.245.159.115
- **OS:** Ubuntu
- **Web server:** Nginx
- **PHP:** 8.2-fpm
- **Database:** MySQL

## Paden

| Omgeving | Pad |
|----------|-----|
| Staging | /var/www/havunvet/staging |
| Production | /var/www/havunvet/production |

## URLs

| Omgeving | URL |
|----------|-----|
| Staging | https://staging.havunvet.havun.nl |
| Production | https://havunvet.havun.nl |

## Database Credentials

### Staging
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=havunvet_staging
DB_USERNAME=havunvet
DB_PASSWORD=<zie .env op server>
```

## Deploy Commands

```bash
cd /var/www/havunvet/staging
git pull
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Nginx Configuratie

Locatie: `/etc/nginx/sites-available/staging.havunvet.havun.nl`

Let op: `/vendor/livewire` is toegestaan voor Livewire assets.

## SSL

Certificaten via Let's Encrypt (certbot).
