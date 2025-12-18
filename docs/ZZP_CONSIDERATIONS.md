# HavunVet - ZZP Dierenarts Overwegingen

> Aanvullende architectuur voor freelance/ZZP dierenarts werkzaam bij klinieken én thuisbezoeken.

## Twee Werkstromen

### 1. Kliniek-inhuur
Je wordt ingehuurd door dierenklinieken → zij factureren de klant.

```
Kliniek huurt jou in → Jij behandelt → Kliniek factureert klant
                                     → Jij factureert kliniek (uren)
```

**Nodig:**
- Urenregistratie per kliniek
- Maandelijkse factuur naar kliniek (via HavunAdmin)
- Patiëntgegevens blijven bij kliniek (privacy!)

### 2. Particulier / Thuisbezoek
Je behandelt rechtstreeks → jij factureert de klant.

```
Klant belt jou → Thuisbezoek → Jij factureert klant direct
```

**Nodig:**
- Volledige patiëntadministratie
- Directe facturatie via HavunAdmin
- Adres/GPS tracking per bezoek

## Database Uitbreidingen

### work_locations (werklocaties)
```sql
- id
- type                  -- 'clinic' | 'home_visit' | 'own_practice'
- name                  -- Kliniek naam of 'Thuisbezoek'
- address
- city
- postal_code
- contact_person
- phone
- email
- hourly_rate           -- Uurtarief voor deze kliniek
- contract_notes
- active
- timestamps
```

### work_sessions (uren voor klinieken)
```sql
- id
- work_location_id
- date
- start_time
- end_time
- break_minutes
- notes
- havunadmin_invoice_id -- Gekoppelde factuur
- status               -- 'draft' | 'submitted' | 'invoiced'
- timestamps
```

### home_visits (thuisbezoeken)
```sql
- id
- patient_id
- treatment_id
- scheduled_at
- address              -- Kan afwijken van klantadres
- city
- postal_code
- latitude             -- GPS voor routeplanning
- longitude
- travel_distance_km   -- Voor reiskostenberekening
- travel_time_minutes
- notes
- timestamps
```

## Facturatie Stromen

### Stroom A: Kliniek-inhuur
```
HavunVet                          HavunAdmin
────────                          ──────────
work_sessions          ───API───► Factuur naar kliniek
(maandelijks bundelen)            - Type: "Inhuur dierenarts"
                                  - Per uur/dag tarief
                                  - BTW: 21%
```

### Stroom B: Particulier
```
HavunVet                          HavunAdmin
────────                          ──────────
treatment + items      ───API───► Factuur naar klant
                                  - Behandelkosten
                                  - Medicijnen
                                  - Voorrijkosten
                                  - BTW: 21% (of vrijgesteld?)
```

## BTW Aandachtspunten

| Dienst | BTW |
|--------|-----|
| Veterinaire behandeling | 21% (geen vrijstelling voor dieren) |
| Medicijnen verkoop | 21% |
| Inhuur aan kliniek | 21% (B2B, verlegd indien EU) |
| Voorrijkosten | 21% |

**Let op:** Anders dan humane gezondheidszorg is diergeneeskunde NIET vrijgesteld van BTW!

## Landbouwhuisdieren

Als je ook landbouwhuisdieren behandelt:

### Verplichte registraties
- **UBN-nummer** (Uniek Bedrijfsnummer) van het bedrijf
- **I&R registratie** voor runderen, varkens, schapen, geiten
- **Antibiotica gebruik** → meldplicht via SDa (Autoriteit Diergeneesmiddelen)

### farm_registrations
```sql
- id
- havunadmin_customer_id
- ubn_number
- farm_type             -- 'dairy' | 'beef' | 'pig' | 'poultry' | 'sheep' | 'goat'
- animal_count
- sda_registration
- timestamps
```

## Offline-First Overwegingen

Thuisbezoeken = mogelijk geen internet.

**Oplossing:**
- PWA met service worker
- Local SQLite cache (IndexedDB)
- Sync queue wanneer online
- Conflict resolution strategie

```javascript
// Sync priority
1. Behandelingen (kritiek)
2. Medicatie toedieningen
3. Foto's (kan later)
```

## Voorrijkosten Berekening

```php
// Standaard formule
$voorrijkosten = max(
    $minimumBedrag,           // bijv. €15
    $afstandKm * $kilometerTarief  // bijv. €0.40/km
);

// Of zones
$zones = [
    '0-10km'  => 15.00,
    '10-25km' => 25.00,
    '25-50km' => 40.00,
    '50+km'   => 'op aanvraag'
];
```

## HavunCore Integratie

- **Task Queue:** Voor achtergrond sync, herinneringen
- **Vault API:** Gevoelige data (recepten, medicatie logs)
- **SSL Monitoring:** Automatisch voor vet.havun.nl

## Agenda/Planning Features

### Kliniek rooster
- Vaste dagen per kliniek
- Blokken reserveren
- Automatisch uren optellen

### Thuisbezoek planning
- Route optimalisatie (meerdere bezoeken op één dag)
- Reistijd inschatting
- Buffer tussen afspraken

## Privacy Scheiding

**BELANGRIJK:** Bij kliniek-inhuur zijn patiëntgegevens van de KLINIEK.

```
┌─────────────────────────────────────────────────┐
│              HavunVet Database                   │
├─────────────────────────────────────────────────┤
│  EIGEN PATIËNTEN        │  KLINIEK WERK         │
│  (particulier)          │  (alleen uren)        │
│  ─────────────────      │  ──────────────       │
│  ✓ Volledige data       │  ✗ Geen patiëntdata   │
│  ✓ Behandelingen        │  ✓ Alleen uren        │
│  ✓ Medicatie            │  ✓ Alleen locatie     │
│  ✓ Facturen             │  ✓ Factuur naar kliniek│
└─────────────────────────────────────────────────┘
```

## Checklist voor Start

- [ ] KvK inschrijving als ZZP dierenarts
- [ ] BIG-registratie / diergeneeskundige bevoegdheid
- [ ] Aansprakelijkheidsverzekering
- [ ] Beroepsaansprakelijkheid
- [ ] KNMVD lidmaatschap (optioneel maar aanbevolen)
- [ ] BTW-nummer actief
- [ ] Afspraken met klinieken vastleggen (tarieven, dagen)
