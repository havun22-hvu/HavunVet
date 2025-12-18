# HavunVet Server Setup

## Server

- **IP:** 188.245.159.115
- **Staging path:** /var/www/havunvet/staging
- **Production path:** /var/www/havunvet/production

## MySQL Database

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=havunvet_staging
DB_USERNAME=havunvet
DB_PASSWORD=<see .env on server>
```

## URLs

- **Staging:** https://staging.havunvet.havun.nl
- **Production:** https://havunvet.havun.nl (nog niet actief)

## Status Staging

- [x] Nginx config aangemaakt
- [x] SSL certificaat actief
- [x] Code gedeployed
- [x] MySQL database aangemaakt
- [x] .env geconfigureerd voor MySQL
- [x] Migrations gedraaid

## Deploy Commands

```bash
cd /var/www/havunvet/staging
git pull
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
```
