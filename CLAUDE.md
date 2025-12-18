# HavunVet - Dierenarts Praktijk Management

> **Type:** Laravel 11 dierenarts praktijkbeheer
> **Status:** Initialisatie
> **Gekoppeld aan:** HavunAdmin (facturatie & administratie)

## Concept

Standalone praktijkbeheer voor freelance dierenarts (ZZP), met koppeling naar HavunAdmin voor alle financiële/administratieve zaken.

## Architectuur

```
┌─────────────────────────────────────────────────────────────┐
│                        HavunVet                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  Patiënten   │  │ Behandelingen│  │  Medicijnen  │      │
│  │  (dieren)    │  │  & Historie  │  │  & Recepten  │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                           │                                 │
│                           ▼                                 │
│                  ┌──────────────┐                           │
│                  │   API Sync   │                           │
│                  └──────────────┘                           │
└─────────────────────────│───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                      HavunAdmin                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Klanten    │  │  Facturatie  │  │     BTW      │      │
│  │  (eigenaren) │  │              │  │   Uitgaven   │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
```

## Koppeling HavunAdmin

### Wat blijft in HavunAdmin
- **Klanten** (diereigenaren) - master data
- **Facturatie** - alle facturen, ook voor behandelingen
- **Uitgaven** - medicijnen inkoop, materialen
- **BTW aangifte** - centrale administratie
- **Offertes** - indien nodig voor grote behandelingen

### Wat gaat naar HavunVet
- **Patiënten** (dieren) - gekoppeld aan HavunAdmin klant
- **Behandelingen** - medische historie
- **Medicijnen** - voorraad en toedieningen
- **Recepten** - voor eigenaren
- **Vaccinaties** - schema's en herinneringen
- **Afspraken** - agenda/planning

### API Endpoints (HavunAdmin → HavunVet)

```php
// HavunAdmin biedt aan:
GET    /api/v1/customers                 // Klanten ophalen
GET    /api/v1/customers/{id}            // Klant details
POST   /api/v1/invoices                  // Factuur aanmaken
GET    /api/v1/invoices?source=havunvet  // Facturen filteren
POST   /api/v1/expenses                  // Uitgave registreren
```

### API Endpoints (HavunVet → HavunAdmin)

```php
// HavunVet biedt aan:
GET    /api/v1/patients?customer_id={id} // Dieren van klant
GET    /api/v1/treatments/{patient_id}   // Behandelhistorie
POST   /api/v1/treatments                // Behandeling + factuur trigger
```

### Sync Strategie

| Data | Master | Sync richting |
|------|--------|---------------|
| Klanten (eigenaren) | HavunAdmin | Admin → Vet |
| Patiënten (dieren) | HavunVet | Vet → Admin (read-only) |
| Facturen | HavunAdmin | Vet → Admin (create) |
| Uitgaven | HavunAdmin | Vet → Admin (create) |
| Behandelingen | HavunVet | Geen sync |

### Authenticatie

- **Shared auth** via HavunAdmin OAuth/API tokens
- Of: zelfde WebAuthn credentials (passkeys)
- Single Sign-On tussen beide systemen

## Database Schema (HavunVet)

### patients (dieren)
```sql
- id
- havunadmin_customer_id  -- FK naar HavunAdmin klant
- name                    -- Naam dier
- species                 -- Soort (hond, kat, etc.)
- breed                   -- Ras
- date_of_birth
- gender
- chip_number             -- Identificatie chip
- weight                  -- Laatste gewicht
- allergies               -- JSON array
- notes
- photo_path
- deceased_at
- timestamps
```

### treatments (behandelingen)
```sql
- id
- patient_id
- date
- complaint              -- Klacht/reden bezoek
- diagnosis
- treatment_description
- follow_up_needed
- follow_up_date
- veterinarian           -- Behandelend arts
- havunadmin_invoice_id  -- FK naar factuur (nullable)
- timestamps
```

### treatment_items (factuurregels)
```sql
- id
- treatment_id
- description
- quantity
- unit_price
- vat_rate               -- 21% of 0%
- timestamps
```

### medications (medicijnen voorraad)
```sql
- id
- name
- active_ingredient
- dosage_form            -- Tablet, injectie, zalf, etc.
- strength
- stock_quantity
- min_stock_level
- expiry_date
- supplier
- purchase_price
- timestamps
```

### prescriptions (recepten)
```sql
- id
- patient_id
- treatment_id
- medication_id
- dosage
- frequency
- duration_days
- instructions
- dispensed_quantity
- dispensed_at
- timestamps
```

### vaccinations (vaccinaties)
```sql
- id
- patient_id
- vaccine_name
- batch_number
- administered_at
- next_due_date
- administered_by
- timestamps
```

### appointments (afspraken)
```sql
- id
- patient_id
- scheduled_at
- duration_minutes
- type                   -- Consult, operatie, vaccinatie, etc.
- status                 -- Gepland, bevestigd, afgerond, geannuleerd
- notes
- timestamps
```

## Features Roadmap

### Fase 1 - MVP
- [ ] Patiënten CRUD
- [ ] Behandelingen registreren
- [ ] Koppeling HavunAdmin klanten
- [ ] Factuur aanmaken via API

### Fase 2 - Medicijnen
- [ ] Medicijnen voorraad
- [ ] Recepten genereren (PDF)
- [ ] Voorraad alerts

### Fase 3 - Planning
- [ ] Afspraken agenda
- [ ] Vaccinatie herinneringen
- [ ] SMS/email notificaties

### Fase 4 - Uitbreiding
- [ ] Foto's bij patiënt
- [ ] Documenten upload
- [ ] Rapportages
- [ ] Export voor verzekeringen

## Tech Stack

- Laravel 11
- Livewire 3
- TailwindCSS
- SQLite of MySQL
- API: Laravel Sanctum

## URLs

- **Production:** https://havunvet.havun.nl
- **Staging:** https://staging.havunvet.havun.nl

## Deployment

Zelfde server als HavunAdmin:
- **Server:** 188.245.159.115
- **Staging:** /var/www/havunvet/staging
- **Production:** /var/www/havunvet/production

## Notities

### Privacy/Compliance
- Medische diergegevens vallen niet onder AVG maar wel onder beroepsgeheim
- Recepten bewaren: minimaal 5 jaar
- Backup strategie: dagelijks

### Facturatie Flow
1. Behandeling afronden in HavunVet
2. Behandelitems worden factuurregels
3. API call naar HavunAdmin: `POST /api/v1/invoices`
4. HavunAdmin maakt factuur aan met categorie "Dierenarts"
5. Factuur ID wordt teruggekoppeld naar behandeling
