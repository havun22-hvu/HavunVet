# HavunVet Staging - Setup Request

## MySQL Database

Graag aanmaken op server 188.245.159.115:

```sql
CREATE DATABASE havunvet_staging CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'havunvet'@'localhost' IDENTIFIED BY 'aAOon9yeBuNTjJdKt3Q';
GRANT ALL PRIVILEGES ON havunvet_staging.* TO 'havunvet'@'localhost';
FLUSH PRIVILEGES;
```

## .env Staging

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=havunvet_staging
DB_USERNAME=havunvet
DB_PASSWORD=aAOon9yeBuNTjJdKt3Q
```

Daarna draaien:
```bash
cd /var/www/havunvet/staging
php artisan migrate --force
php artisan config:clear
```

## Status

- [x] Nginx config aangemaakt
- [x] SSL certificaat actief
- [x] Code gedeployed
- [ ] MySQL database aanmaken
- [ ] .env configureren voor MySQL
- [ ] Migrations draaien op MySQL
