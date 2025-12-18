# HavunVet

> Dierenarts praktijkbeheer (Laravel 11 + Livewire 3)

## Quick Info

| Item | Waarde |
|------|--------|
| Type | Standalone praktijkbeheer voor ZZP dierenarts |
| Stack | Laravel 11, Livewire 3, Tailwind CSS, MySQL |
| Staging | https://staging.havunvet.havun.nl |
| Server | 188.245.159.115 |

## Architectuur

```
HavunVet                          HavunAdmin
├── Eigenaren                     ├── Klanten (master)
├── Patiënten (dieren)      ←→    ├── Facturatie
├── Behandelingen                 └── BTW/Uitgaven
├── Medicijnen
└── Afspraken
```

## Documentatie

Zie `/docs/` folder:

| Document | Inhoud |
|----------|--------|
| [INDEX.md](docs/INDEX.md) | Overzicht alle documentatie |
| [DATABASE.md](docs/DATABASE.md) | Database schema en relaties |
| [SERVER.md](docs/SERVER.md) | Server configuratie en deployment |
| [FEATURES.md](docs/FEATURES.md) | Features en roadmap |
| [HAVUNADMIN_INTEGRATION.md](docs/HAVUNADMIN_INTEGRATION.md) | API koppeling met HavunAdmin |

## Development

```bash
composer install && npm install
npm run dev          # Terminal 1
php artisan serve    # Terminal 2
```

## Deploy

```bash
cd /var/www/havunvet/staging
git pull
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan config:clear && php artisan cache:clear
```
