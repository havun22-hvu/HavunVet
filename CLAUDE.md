# HavunVet — Claude Instructions

> Dierenartspraktijk-beheer (Laravel 11 + Livewire 3 + Tailwind, MySQL)
> **Staging:** https://staging.havunvet.havun.nl
> **Onveranderlijke regels:** [`CONTRACTS.md`](CONTRACTS.md) — eerst raadplegen.
> **Detail-docs:** `docs/INDEX.md`

## De 5 Onschendbare Regels

1. NOOIT code schrijven zonder KB + kwaliteitsnormen te raadplegen
2. NOOIT features/UI-elementen verwijderen zonder instructie
3. NOOIT credentials/keys/env aanraken
4. ALTIJD tests draaien voor én na wijzigingen (coverage >80%)
5. ALTIJD toestemming vragen bij grote wijzigingen

## Architectuur (samenvatting)

HavunVet (eigenaren, patiënten, behandelingen, medicijnen, afspraken) ↔ HavunAdmin (klanten, facturatie, BTW). Volledig diagram + relaties: `docs/DATABASE.md`.

## Kerndocs

- `docs/SERVER.md` — server + deploy
- `docs/HAVUNADMIN_INTEGRATION.md` — API-koppeling
- `docs/FEATURES.md` — features & roadmap

## Development snelreferentie

```bash
composer install && npm install
npm run dev                      # Terminal 1
php artisan serve --port=8008    # Terminal 2
```

## Deploy (staging)

```bash
cd /var/www/havunvet/staging && git pull
composer install --no-dev --optimize-autoloader
npm run build && php artisan migrate --force
php artisan config:clear && php artisan cache:clear
```
