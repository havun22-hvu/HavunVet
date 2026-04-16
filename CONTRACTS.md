# CONTRACTS — HavunVet

> **Onveranderlijke regels van dit project.** Bij elke wijziging eerst raadplegen. Wijzigen mag alleen na schriftelijk akkoord van eigenaar.

## Wat is een contract?

Gedragsregels los van implementatie. Code mag refactoren, externe gedrag in dit document mag NIET wijzigen zonder bewuste beslissing. Bij twijfel: STOP, raadpleeg eigenaar.

---

## C-01: Patient-records (dier + eigenaar) zijn permanent traceerbaar

**Regel:** Een `Patient` (dier) en `Owner` (eigenaar) record kan niet hard worden verwijderd. Soft-delete met audit-log entry is de enige toegestane "verwijdering". Een dier dat is overleden wordt gemarkeerd, niet verwijderd.

**Waarom:** Wettelijke administratieplicht voor dierenartsen. Vaccinatiehistorie, behandelingsdossiers moeten bewaard blijven (minimaal 5 jaar).

**Bewijs:** `SoftDeletes` op Patient/Owner, geen `forceDelete()`-paden, audit-log.

---

## C-02: Behandelingen (Treatment) zijn na 24 uur niet meer wijzigbaar

**Regel:** Een `Treatment`-record kan binnen 24 uur na aanmaak gecorrigeerd worden door de behandelaar. Daarna: read-only. Correcties na 24u gaan via `TreatmentCorrection` met verwijzing naar origineel + reden.

**Waarom:** Medische dossiers moeten betrouwbaar zijn. Achteraf wijzigen ondermijnt juridische bewijswaarde.

**Bewijs:** `Treatment::saving()` lock-window check, `TreatmentCorrection` model + tests.

---

## C-03: Eigenaar van Patient kan wijzigen, maar oude eigenaar blijft in historie

**Regel:** Bij overdracht van een dier (verkoop, adoptie) wordt `Patient.owner_id` bijgewerkt + een `OwnershipTransfer`-record aangemaakt. Oude eigenaar blijft volledig in historie behouden.

**Waarom:** Aansprakelijkheid bij oude behandelingen ligt bij vorige eigenaar. Vaccinatiehistorie moet traceerbaar zijn naar wie destijds heeft betaald.

**Bewijs:** `OwnershipTransfer` model + foreign keys, `tests/Feature/OwnershipTransferTest.php`.

---

## C-04: Receptdiensten gaan via wettelijk vereiste paden

**Regel:** Voorgeschreven medicijnen worden geregistreerd met: middel, dosering, behandelaar (KNMvD-nummer), patient, datum. Receptuur voor diergeneesmiddelen volgt UDD-/CC-/UDA-classificatie.

**Waarom:** Wettelijk verplicht (Wet dieren). Inspectie kan elke receptregel opvragen.

**Bewijs:** `Prescription` model met verplichte velden, validatie, audit-log.

---

## C-05: Facturen koppelen altijd aan een behandeling of consult

**Regel:** Geen `Invoice` zonder gekoppelde `Treatment`/`Consult`/`PrescriptionService` referentie. Vrije/losse facturen alleen via expliciet "vrije factuur"-pad met motivatie.

**Waarom:** Boekhoudkundige verantwoording. Inspectie/belastingdienst eist relatie tussen geleverde dienst en factuur.

**Bewijs:** Foreign keys, `Invoice::saving()` validatie, `tests/Feature/InvoiceLinkingTest.php`.

---

## C-06: PII (NAW + diagnose) versleuteld at-rest

**Regel:** `Owner.address`, `Owner.phone`, `Owner.email`, `Treatment.diagnosis`, `Treatment.notes` worden versleuteld via Laravel's Encrypted cast. Plaintext-velden voor PII zijn verboden.

**Waarom:** AVG/GDPR. Backup-bestanden moeten ook versleuteld zijn.

**Bewijs:** Model casts, encryption keys in vault.

---

## C-07: 2FA verplicht voor alle accounts met patiëntdata-toegang

**Regel:** Elke gebruiker met behandel- of admin-rechten MOET 2FA aan hebben. Geen 2FA = geen toegang.

**Waarom:** Eén gecompromitteerd account = volledig medisch dossier van klanten.

**Bewijs:** `EnforceTwoFactorMiddleware`.

---

## C-08: Backup van database dagelijks + offsite (medisch dossier)

**Regel:** Database wordt dagelijks gebackupt naar Hetzner Storage Box. Bewaartermijn: 5 jaar (matches wettelijke administratieplicht). Restore-test per kwartaal.

**Waarom:** Verlies van medische historie raakt direct dieren-welzijn (vaccinatieschema's, allergieën).

**Bewijs:** Backup-cron, monitoring, restore-runbook.

---

## C-09: GitGuardian pre-commit blokkeert credentials in git

**Regel:** Generieke Havun-regel — geen credentials in versiebeheer.

**Bewijs:** `.gitignore`, `.git/hooks/pre-commit`.

---

## C-10: Productie-deploy via staging — medische data tolereert geen migration-fouten

**Regel:** Wijzigingen aan `database/migrations/` MOETEN eerst op staging draaien met productie-snapshot data. Geen direct-naar-productie migratie. Backup verplicht voor productie-migratie.

**Waarom:** Een migration-bug kan vaccinatiehistorie corrumperen — niet detecteerbaar tot maanden later wanneer een hond ziek wordt.

**Bewijs:** Deploy-runbook, `.github/workflows/deploy-*.yml`.

---

## Wat NIET in dit document hoort

UI/styling, naming, performance-doelen, codestijl.

## Wijzigingsprotocol

1. Eigenaar-akkoord (schriftelijk in commit-message)
2. Reden + datum
3. Update bewakende tests
4. Heronderhoud afhankelijke documenten

## Cross-references

- `CLAUDE.md` — projectregels
- `HavunCore/docs/kb/patterns/contracts-md-template.md` — concept
- `HavunCore/docs/audit/verbeterplan-q2-2026.md` — VP-14
