# HavunVet

Dierenarts praktijkbeheer voor freelance dierenarts (ZZP).

## Tech Stack

- Laravel 11
- Livewire 3
- TailwindCSS
- SQLite

## Installatie

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Development

```bash
php artisan serve
npm run dev
```

## URLs

- **Production:** https://havunvet.havun.nl
- **Staging:** https://staging.havunvet.havun.nl

## Koppeling

Synchroniseert met [HavunAdmin](https://github.com/HavunAdmin) voor facturatie en klantbeheer.

## Licentie

Proprietary - Alle rechten voorbehouden.
