# Database Schema

## Relaties

```
owners (eigenaren)
  └── patients (patiënten/dieren)
        ├── treatments (behandelingen)
        │     └── treatment_items (factuurregels)
        ├── vaccinations (vaccinaties)
        ├── prescriptions (recepten)
        └── appointments (afspraken)

medications (medicijnen voorraad)
```

## Tabellen

### owners
| Veld | Type | Beschrijving |
|------|------|--------------|
| name | string | Naam eigenaar |
| email | string | Email |
| phone | string | Telefoon 1 |
| phone2 | string | Telefoon 2 |
| address | string | Straat |
| house_number | string | Huisnummer |
| postal_code | string | Postcode |
| city | string | Plaats |
| ubn | string | UBN nummer (fokkers) |
| notes | text | Notities |
| active | boolean | Actief |

### patients
| Veld | Type | Beschrijving |
|------|------|--------------|
| owner_id | FK | Eigenaar |
| name | string | Naam dier |
| species | string | Diersoort |
| breed | string | Ras |
| date_of_birth | date | Geboortedatum |
| gender | enum | male/female/unknown |
| neutered | boolean | Gecastreerd/gesteriliseerd |
| chip_number | string | Chipnummer |
| weight | decimal | Gewicht (kg) |
| color | string | Vachtkleur |
| coat_type | string | Vachttype |
| allergies | json | Allergieën |
| notes | text | Bijzonderheden |
| photo_path | string | Foto |
| deceased_at | date | Overleden datum |

### treatments
| Veld | Type | Beschrijving |
|------|------|--------------|
| patient_id | FK | Patiënt |
| date | date | Datum |
| complaint | string | Klacht |
| anamnesis | text | Voorgeschiedenis |
| examination | text | Onderzoek |
| diagnosis | text | Diagnose |
| treatment_description | text | Behandeling |
| follow_up_needed | boolean | Follow-up nodig |
| follow_up_date | date | Follow-up datum |
| veterinarian | string | Behandelend arts |
| status | enum | draft/completed/invoiced |
| havunadmin_invoice_id | bigint | Factuur ID in HavunAdmin |

### vaccinations
| Veld | Type | Beschrijving |
|------|------|--------------|
| patient_id | FK | Patiënt |
| vaccine_name | string | Vaccin naam |
| batch_number | string | Batch nummer |
| administered_at | datetime | Toegediend op |
| next_due_date | date | Volgende datum |
| administered_by | string | Toegediend door |

### appointments
| Veld | Type | Beschrijving |
|------|------|--------------|
| patient_id | FK | Patiënt |
| scheduled_at | datetime | Gepland op |
| duration_minutes | int | Duur |
| type | string | Type afspraak |
| status | enum | scheduled/confirmed/completed/cancelled |
| notes | text | Notities |

### medications
| Veld | Type | Beschrijving |
|------|------|--------------|
| name | string | Naam |
| active_ingredient | string | Werkzame stof |
| dosage_form | string | Vorm (tablet, injectie, etc) |
| strength | string | Sterkte |
| stock_quantity | int | Voorraad |
| min_stock_level | int | Minimum voorraad |
| expiry_date | date | Vervaldatum |
| supplier | string | Leverancier |
| purchase_price | decimal | Inkoopprijs |

### prescriptions
| Veld | Type | Beschrijving |
|------|------|--------------|
| patient_id | FK | Patiënt |
| treatment_id | FK | Behandeling |
| medication_id | FK | Medicijn |
| dosage | string | Dosering |
| frequency | string | Frequentie |
| duration_days | int | Dagen |
| instructions | text | Instructies |
| dispensed_quantity | int | Verstrekt aantal |
| dispensed_at | datetime | Verstrekt op |
