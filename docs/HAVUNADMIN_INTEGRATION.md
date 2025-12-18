# HavunAdmin Integratie Instructies

> Dit document beschrijft welke API endpoints HavunAdmin moet aanbieden voor HavunVet.

## Overzicht

HavunVet haalt klantgegevens op uit HavunAdmin en stuurt facturen/uitgaven terug.

```
HavunVet                          HavunAdmin
   │                                  │
   │──── GET /api/customers ─────────▶│  Klanten ophalen
   │◀─────── [klanten JSON] ──────────│
   │                                  │
   │──── POST /api/invoices ─────────▶│  Factuur aanmaken
   │◀─────── [invoice_id] ────────────│
   │                                  │
   │──── POST /api/expenses ─────────▶│  Uitgave registreren
   │◀─────── [expense_id] ────────────│
```

---

## Vereiste API Endpoints in HavunAdmin

### 1. Authenticatie

```
POST /api/auth/token
```

HavunVet vraagt een API token aan met service credentials.

**Request:**
```json
{
  "service": "havunvet",
  "secret": "{HAVUNVET_API_SECRET}"
}
```

**Response:**
```json
{
  "token": "Bearer xxx",
  "expires_at": "2025-12-31T23:59:59Z"
}
```

---

### 2. Klanten Ophalen

```
GET /api/v1/customers
```

**Headers:**
```
Authorization: Bearer {token}
```

**Query parameters:**
- `?search=naam` - Zoeken op naam/email
- `?updated_since=2025-01-01` - Alleen gewijzigde klanten
- `?page=1&per_page=50` - Paginatie

**Response:**
```json
{
  "data": [
    {
      "id": 123,
      "name": "Jan Jansen",
      "email": "jan@example.nl",
      "phone": "0612345678",
      "address": {
        "street": "Hoofdstraat 1",
        "postal_code": "1234AB",
        "city": "Amsterdam"
      },
      "updated_at": "2025-01-15T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 150
  }
}
```

---

### 3. Enkele Klant Ophalen

```
GET /api/v1/customers/{id}
```

**Response:**
```json
{
  "id": 123,
  "name": "Jan Jansen",
  "email": "jan@example.nl",
  "phone": "0612345678",
  "address": {
    "street": "Hoofdstraat 1",
    "postal_code": "1234AB",
    "city": "Amsterdam"
  },
  "notes": "Heeft 2 honden",
  "updated_at": "2025-01-15T10:30:00Z"
}
```

---

### 4. Factuur Aanmaken

```
POST /api/v1/invoices
```

HavunVet stuurt behandelgegevens, HavunAdmin maakt factuur aan.

**Request:**
```json
{
  "customer_id": 123,
  "source": "havunvet",
  "source_reference": "treatment_456",
  "category": "Dierenarts",
  "date": "2025-01-15",
  "due_days": 14,
  "items": [
    {
      "description": "Consult hond Max",
      "quantity": 1,
      "unit_price": 45.00,
      "vat_rate": 21
    },
    {
      "description": "Antibiotica kuur (Amoxicilline)",
      "quantity": 1,
      "unit_price": 28.50,
      "vat_rate": 21
    }
  ],
  "notes": "Controle over 2 weken"
}
```

**Response:**
```json
{
  "id": 789,
  "invoice_number": "2025-0042",
  "total": 88.94,
  "status": "concept",
  "pdf_url": "/invoices/789/pdf",
  "created_at": "2025-01-15T14:22:00Z"
}
```

---

### 5. Uitgave Registreren

```
POST /api/v1/expenses
```

Voor medicijn inkoop en materialen.

**Request:**
```json
{
  "source": "havunvet",
  "source_reference": "medication_stock_123",
  "category": "Dierenarts",
  "date": "2025-01-10",
  "description": "Medicijnen inkoop - Diergeneesmiddelen BV",
  "amount": 450.00,
  "vat_rate": 21,
  "supplier": "Diergeneesmiddelen BV"
}
```

**Response:**
```json
{
  "id": 234,
  "created_at": "2025-01-10T09:15:00Z"
}
```

---

### 6. Factuur Status Ophalen

```
GET /api/v1/invoices/{id}
```

Check of factuur betaald is.

**Response:**
```json
{
  "id": 789,
  "invoice_number": "2025-0042",
  "status": "paid",
  "paid_at": "2025-01-20T11:00:00Z",
  "total": 88.94
}
```

---

### 7. Projecten/Categorieën Ophalen

```
GET /api/v1/projects
```

Voor dropdown in HavunVet.

**Response:**
```json
{
  "data": [
    {"id": 1, "name": "Algemeen"},
    {"id": 2, "name": "IT Diensten"},
    {"id": 3, "name": "Dierenarts"}
  ]
}
```

---

## Webhook (optioneel)

HavunAdmin kan HavunVet notificeren bij betalingen.

```
POST {HAVUNVET_WEBHOOK_URL}/api/webhooks/havunadmin
```

**Payload:**
```json
{
  "event": "invoice.paid",
  "invoice_id": 789,
  "source_reference": "treatment_456",
  "paid_at": "2025-01-20T11:00:00Z"
}
```

---

## Configuratie in HavunVet .env

```env
HAVUNADMIN_API_URL=https://havunadmin.havun.nl/api/v1
HAVUNADMIN_API_SECRET=xxx
HAVUNADMIN_DEFAULT_CATEGORY=Dierenarts
```

---

## Checklist voor HavunAdmin

Bij opzetten HavunVet moet HavunAdmin het volgende hebben:

- [ ] API routes aangemaakt onder `/api/v1/`
- [ ] Sanctum of API token authenticatie
- [ ] Service account voor HavunVet
- [ ] Project/categorie "Dierenarts" aangemaakt
- [ ] `source` en `source_reference` velden op invoices/expenses
- [ ] Webhook systeem (optioneel)

---

## Contact

Vragen over de koppeling? Check HavunAdmin codebase of vraag Claude.
