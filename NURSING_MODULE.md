# Nursing / Inpatient Module for OpenEMR 8.1

## Background and Motivation

OpenEMR is widely used in outpatient settings, but many hospitals — especially in Latin America — run it in mixed environments where the same system must support both outpatient clinics and inpatient wards (ICU, general wards, etc.).

Out of the box, OpenEMR 8.1 has no built-in workflow for:

- Tracking admitted patients (who is in which bed/ward right now)
- Recording nursing clinical activities at the bedside
- Managing ICU-specific documentation (mechanical ventilation, Glasgow score, wound care, etc.)
- Registering discharges and deaths linked to an encounter

This module was developed to fill that gap, keeping full compatibility with the OpenEMR encounter system and requiring no changes to core files.

---

## What Was Built

### 1. Inpatient Dashboard (`interface/tableros/`)

| File | Purpose |
|------|---------|
| `lista_internados.php` | Main inpatient list — shows all currently admitted patients with their bed, ward, and service. Provides actions: Edit, Discharge, Deceased, and Nursing forms. |
| `editar_internado.php` | New admission form and edit form. Links a patient to a `form_encounter` with `pc_catid = 16` (Inpatient category). Captures department, service, ward, bed, and registration number. |
| `save_internado.php` | Saves/updates the admission encounter. |

**How the list works:** A patient appears in the list when they have a `form_encounter` row with `pc_catid = 16` (the "Inpatient" calendar category) and `date_end IS NULL`. Discharging or registering a death sets `date_end` and removes the patient from the active list.

---

### 2. Nursing Encounter Forms (`interface/forms/`)

Each form follows the standard OpenEMR encounter form structure (`new.php`, `save.php`, `view.php`, `print.php`, `report.php`, `table.sql`, `info.txt`).

| Directory | Clinical Purpose |
|-----------|-----------------|
| `curaciones/` | Wound care — tracks surgical wounds, tracheostomy, ostomies, pressure sores, IV lines |
| `aplicaciones/` | Medication and fluid applications — medications, IV fluids, vaccines, blood products, volume expanders |
| `cuidados/` | Care bundle — positioning, oral hygiene, secretion management, eye/skin care (primarily for ventilated patients) |
| `evaluaciones/` | Neurological evaluation — Glasgow Coma Scale (Eye, Verbal, Motor), pupil reactivity, limb response, conscious state |
| `registro_vm/` | Mechanical ventilation record — mode, FiO₂, PEEP, tidal volume, respiratory rate, plateau pressure, SpO₂, trends over time |

All forms:
- Are registered in the OpenEMR form registry under the **Nursing** category
- Use `class="body_top"` on the `<body>` tag (required for OpenEMR iframe embedding)
- Initialize `SessionWrapperFactory` for CSRF protection
- Redirect back to `lista_internados.php` after save via `formJump()`

---

### 3. Patient Dashboard Card (`src/Patient/Cards/`)

| File | Purpose |
|------|---------|
| `src/Patient/Cards/NursingCard.php` | PHP card class — queries active inpatient admissions for the current patient and displays them in the patient dashboard secondary section |
| `templates/patient/partials/nursing.html.twig` | Twig template for the nursing card — shows current bed, ward, service, and a link to edit the admission |

---

## Database Changes

All schema changes are guarded with `#IfNotColumn` / `#IfNotTable` directives so they are safe to run multiple times and are applied automatically by OpenEMR's upgrade system.

### New Tables

| Table | Purpose |
|-------|---------|
| `form_curaciones` | Wound care form data |
| `form_aplicaciones` | Medication applications form data |
| `form_cuidados` | Care bundle form data |
| `form_evaluaciones` | Neurological evaluation form data |
| `form_registro_vm` | Mechanical ventilation record data |

### New Columns on Existing Tables

#### `form_encounter`

| Column | Type | Purpose |
|--------|------|---------|
| `departamento` | `VARCHAR(55)` | Hospital department (e.g. "UTI ADULTOS CLÍNICO") |
| `servicio` | `VARCHAR(55)` | Clinical service (e.g. "U.T.I. ADULTOS") |
| `cama` | `VARCHAR(55)` | Bed number/identifier |
| `out_date` | `DATE` | Expected or actual discharge date |
| `cuarto` | `VARCHAR(55)` | Ward/room name (e.g. "UTI A_UCO") |
| `nro_registro` | `VARCHAR(40)` | Hospital registration / admission number |
| `carga_ws` | `VARCHAR(3)` | WS import flag — marks records loaded from an external system (`'si'`) |
| `death_date` | `DATE` | Date of death, set when the discharge reason is "Deceased" |

#### `patient_data`

| Column | Type | Purpose |
|--------|------|---------|
| `carga_ws` | `VARCHAR(3)` | WS import flag — marks patients imported from an external hospital system |

---

## How Inpatient Admission Works

```
New Admission
─────────────
1. Click "New Admission" on lista_internados.php
2. Patient picker opens (dlgopen) → select patient
3. editar_internado.php opens for that patient
4. Fill: Department, Service, Ward, Bed, Registration No., Admission date
5. Save → form_encounter is created with pc_catid=16, date_end=NULL
6. Patient now appears in the inpatient list

Discharge
─────────
1. Click "Discharge" button on the patient's row
2. Modal asks for discharge date
3. On confirm → form_encounter.date_end is set to today
4. Patient disappears from the active list

Deceased
────────
1. Click "Deceased" button on the patient's row
2. Modal asks for date of death
3. On confirm → form_encounter.date_end AND form_encounter.death_date are both set
4. Patient disappears from the active list

Nursing Forms
─────────────
1. Click "Nursing" button → modal opens with 5 form options
2. Select a form → redirected to that form's new.php with pid and encounter
3. Form is saved and linked to the patient's inpatient encounter
4. After save → redirected back to lista_internados.php
```

---

## Translations

The module ships with translations for:

| Language | Code |
|----------|------|
| Spanish (Spain) | `es` |
| Spanish (Latin America) | `es_419` |
| German | `de` |
| Portuguese (Portugal) | `pt` |
| Portuguese (Brazil) | `pt_BR` |
| Portuguese (Angola) | `pt_AO` |

All strings are inserted via the upgrade SQL using `ON DUPLICATE KEY UPDATE` so existing translations are never overwritten.

---

## Installation

The module is included in the standard OpenEMR upgrade path from **8.1.1 → 8.1.2**.

The upgrade script `sql/8_1_1-to-8_1_2_upgrade.sql` handles:
- Creating all nursing form tables (guarded with `#IfNotTable`)
- Adding all new columns to `form_encounter` and `patient_data` (guarded with `#IfNotColumn`)
- Inserting all translations (guarded with `#IfNotRow` / `ON DUPLICATE KEY UPDATE`)
- Registering all 5 nursing forms in the OpenEMR form registry (guarded with `#IfNotRow`)

For a fresh installation, all changes are already included in `sql/database.sql`.

---

## Navigation / Menu Entry

The inpatient list is accessible from the main OpenEMR navigation under:

> **Nursing → Inpatient List**

The menu entry is registered in the standard OpenEMR menu system and respects the existing ACL (`patients` / `med`).

---

## Design Decisions

**Why `form_encounter` with `pc_catid=16`?**
Using the existing `form_encounter` table keeps the inpatient record fully integrated with the rest of OpenEMR — billing, encounter notes, and other forms can all be attached to the same encounter. The "Inpatient" category (`pc_catid=16`) distinguishes admissions from regular appointments.

**Why not a separate `internaciones` table?**
The encounter table already has a `date_end` column and full patient/provider linkage. A separate table would duplicate data and break the encounter-centric architecture of OpenEMR.

**Why `date_end IS NULL` to detect active admissions?**
This is the simplest and most robust filter. A patient is "admitted" until their encounter is closed. This also means the standard OpenEMR encounter locking and billing workflows apply naturally.

**Why custom columns on `form_encounter`?**
Columns like `departamento`, `cama`, `cuarto` are hospital-specific data that belongs to the encounter record. Adding them directly avoids a join to a separate table and keeps queries simple.

---

## Files Changed / Added

```
Modified:
  sql/database.sql
  sql/8_1_1-to-8_1_2_upgrade.sql
  src/Patient/Cards/NursingCard.php

Added:
  interface/tableros/lista_internados.php
  interface/tableros/editar_internado.php
  interface/tableros/save_internado.php
  interface/forms/curaciones/   (new.php, save.php, view.php, print.php, report.php, table.sql, info.txt, README.md)
  interface/forms/aplicaciones/ (new.php, save.php, view.php, print.php, report.php, table.sql, info.txt, README.md)
  interface/forms/cuidados/     (new.php, save.php, view.php, print.php, report.php, table.sql, info.txt, README.md)
  interface/forms/evaluaciones/ (new.php, save.php, view.php, print.php, report.php, table.sql, info.txt, README.md)
  interface/forms/registro_vm/  (new.php, save.php, view.php, print.php, report.php, table.sql, info.txt, README.md)
  templates/patient/partials/nursing.html.twig
  NURSING_MODULE.md
```

---

## Contributing

If you extend this module (new forms, new columns, new translations), please:

1. Add the schema change to both `sql/database.sql` and `sql/8_1_1-to-8_1_2_upgrade.sql` with the appropriate `#IfNotColumn` / `#IfNotTable` guard.
2. Add translations using the existing `INSERT INTO lang_constants / lang_definitions` pattern in the upgrade SQL.
3. Register any new form in the `registry` table using `#IfNotRow registry directory <form_dir>`.
4. Follow OpenEMR coding standards: 4-space indentation, LF line endings, `declare(strict_types=1)` in new PHP files.

---

*Developed for OpenEMR 8.1.2 — Hospital inpatient / ICU workflow extension.*
