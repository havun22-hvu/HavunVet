# HavunAdmin Categorie Setup voor Dierenarts

## Overzicht

HavunVet dierenarts-facturen worden in HavunAdmin onderverdeeld in twee stromen:

```
                    Project: Dierenarts
                           │
           ┌───────────────┴───────────────┐
           │                               │
    customer_type:                  customer_type:
      'business'                    'patient_owner'
           │                               │
  ┌────────┴────────┐                      │
  │                 │                      │
Klinieken    Overheden            Particulieren
Bedrijven                         (via HavunVet)
  │                 │                      │
  └────────┬────────┘                      │
           │                               │
    Direct invoer                   API sync van
    in HavunAdmin                    HavunVet
```

---

## 1. Project Aanmaken in HavunAdmin

Voeg toe aan `database/seeders/ProjectSeeder.php`:

```php
[
    'name' => 'Dierenarts',
    'slug' => 'dierenarts',
    'code' => 'VET',
    'description' => 'Freelance dierenarts diensten',
    'color' => '#10B981', // Groen
    'status' => 'active',
    'start_date' => null,
    'is_active' => true,
],
```

Of direct in database:
```sql
INSERT INTO projects (name, slug, code, description, color, status, is_active, created_at, updated_at)
VALUES ('Dierenarts', 'dierenarts', 'VET', 'Freelance dierenarts diensten', '#10B981', 'active', 1, NOW(), NOW());
```

---

## 2. Customer Type Veld Toevoegen

### Migration

```php
// database/migrations/xxxx_add_customer_type_to_customers_table.php

Schema::table('customers', function (Blueprint $table) {
    // Type klant
    $table->enum('customer_type', ['business', 'patient_owner', 'other'])
          ->default('other')
          ->after('is_active');

    // Bron van de klant
    $table->string('source')->nullable()->after('customer_type');
    // 'havunadmin' = direct ingevoerd
    // 'havunvet' = gesynceerd van HavunVet

    // Referentie naar HavunVet (indien van toepassing)
    $table->unsignedBigInteger('havunvet_customer_id')->nullable()->after('source');

    // Bedrijfstype voor zakelijke klanten
    $table->string('business_category')->nullable()->after('havunvet_customer_id');
    // 'dierenkliniek', 'overheid', 'bedrijf', etc.
});
```

### Customer Types

| Type | Omschrijving | Bron |
|------|--------------|------|
| `business` | Dierenklinieken, bedrijven, overheden | Direct HavunAdmin |
| `patient_owner` | Particuliere diereigenaren | Via HavunVet |
| `other` | Overige klanten (IT, etc.) | Direct HavunAdmin |

### Business Categories (voor type=business)

| Category | Voorbeelden |
|----------|-------------|
| `dierenkliniek` | Dierenkliniek Noord, Spoedpraktijk Dieren |
| `overheid` | NVWA, Gemeente, RVO |
| `bedrijf` | Boerderijen, fokkers, asielen |
| `verzekering` | Dierenverzekeraars |

---

## 3. Invoice Uitbreidingen

Invoice model heeft al `source` en `external_reference` velden. Gebruik:

| Veld | Waarde voor HavunVet |
|------|---------------------|
| `source` | `'havunvet'` |
| `external_reference` | `'treatment_123'` (HavunVet treatment ID) |
| `project_id` | ID van project 'Dierenarts' |

---

## 4. API Filtering

### Klanten ophalen per type

```
GET /api/v1/customers?customer_type=business
GET /api/v1/customers?customer_type=patient_owner
GET /api/v1/customers?source=havunvet
```

### Facturen ophalen per bron

```
GET /api/v1/invoices?source=havunvet
GET /api/v1/invoices?project=dierenarts
GET /api/v1/invoices?project=dierenarts&source=havunvet
```

---

## 5. Workflow Voorbeelden

### A. Dierenkliniek factureert (zakelijk)

1. **In HavunAdmin**: Klant aanmaken met `customer_type: 'business'`, `business_category: 'dierenkliniek'`
2. **In HavunAdmin**: Factuur aanmaken, project = Dierenarts
3. Geen sync met HavunVet nodig

### B. Particulier komt met hond (via HavunVet)

1. **In HavunVet**: Patiënt (hond) aanmaken
2. **In HavunVet**: Behandeling registreren
3. **HavunVet → HavunAdmin API**: Klant aanmaken/updaten met `customer_type: 'patient_owner'`, `source: 'havunvet'`
4. **HavunVet → HavunAdmin API**: Factuur aanmaken met behandelregels

### C. NVWA controle (overheid)

1. **In HavunAdmin**: Klant "NVWA" met `customer_type: 'business'`, `business_category: 'overheid'`
2. **In HavunAdmin**: Factuur voor inspectie-uren

---

## 6. Rapportage Mogelijkheden

Met deze structuur kun je in HavunAdmin rapporteren op:

- **Totaal Dierenarts omzet**: `WHERE project = 'dierenarts'`
- **Zakelijke klanten**: `WHERE customer_type = 'business'`
- **Particulieren (HavunVet)**: `WHERE customer_type = 'patient_owner'`
- **Per business category**: Omzet per kliniek, overheid, etc.
- **Sync status**: Facturen van HavunVet vs. direct ingevoerd

---

## 7. Dashboard Widgets (optioneel)

Suggestie voor HavunAdmin dashboard:

```
┌─────────────────────────────────────────────────────┐
│ Dierenarts Overzicht (deze maand)                   │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Zakelijk        €2.450    ████████████ 70%        │
│  Particulier     €1.050    █████ 30%               │
│                  ───────                            │
│  Totaal          €3.500                             │
│                                                     │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐               │
│  │ 12      │ │ 45      │ │ 3       │               │
│  │ Facturen│ │ Patiënten│ │ Klinieken│              │
│  └─────────┘ └─────────┘ └─────────┘               │
└─────────────────────────────────────────────────────┘
```

---

## 8. Checklist Implementatie

### HavunAdmin (eenmalig)
- [ ] Project "Dierenarts" aanmaken
- [ ] Migration voor customer_type, source, business_category
- [ ] Customer model updaten met nieuwe velden
- [ ] API endpoints uitbreiden met filters

### Per nieuwe zakelijke klant
- [ ] Customer aanmaken in HavunAdmin
- [ ] `customer_type` = 'business'
- [ ] `business_category` invullen (kliniek/overheid/bedrijf)

### HavunVet (bij opzet)
- [ ] Bij klant-sync: `customer_type` = 'patient_owner' meesturen
- [ ] Bij factuur-sync: `source` = 'havunvet' meesturen
