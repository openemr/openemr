# Nursing Module for Inpatient Care

This module adds a complete nursing workflow for inpatient management within OpenEMR.
It provides an inpatient dashboard (`lista_internados`) and five specialized nursing forms
designed for ICU and general ward environments.

---

## Module Overview

### Inpatient Dashboard (`lista_internados.php`)
Central view listing all active inpatients with quick access to nursing forms.
- Real-time inpatient list (patients with open encounters in inpatient category)
- Patient discharge management
- Death registration with date recording
- One-click access to all nursing forms per patient
- DataTables search and sorting
- Fully translated UI in 8 languages

### Nursing Forms

| Form | Folder | Description |
|---|---|---|
| Wound Care | `curaciones` | Wounds, tracheostomy, ostomies, pressure sores, IV lines |
| Nursing Applications | `aplicaciones` | Medications, IV fluids, blood products |
| Nursing Care Bundle | `cuidados` | ICU bundle: hygiene, suctioning, sedation, positioning |
| Nursing Evaluations | `evaluaciones` | Glasgow scale, vital signs, pain assessment |
| Ventilation Record | `registro_vm` | Mechanical ventilation modes and parameters |

Each form includes:
- `new.php` — create new record
- `view.php` — view/edit existing record
- `save.php` — CSRF-protected save handler
- `report.php` — embedded report in encounter summary
- `print.php` — PDF generation via mPDF
- `table.sql` — database table + translations (8 languages)
- `info.txt` — form registration name

---

## Installation

### 1. Run SQL files
Execute each form's `table.sql` to create tables and load translations:

```sql
SOURCE interface/forms/curaciones/table.sql;
SOURCE interface/forms/aplicaciones/table.sql;
SOURCE interface/forms/cuidados/table.sql;
SOURCE interface/forms/evaluaciones/table.sql;
SOURCE interface/forms/registro_vm/table.sql;
SOURCE interface/tableros/lista_internados_lang.sql;
```

### 2. Register forms in OpenEMR
OpenEMR auto-registers forms via `addForm()` on first save. No manual registration needed.

### 3. Configure inpatient category
The dashboard queries encounters with `pc_catid = 16` (inpatient category).
Ensure this category exists in your OpenEMR installation.

---

## Languages Supported

| ID | Language |
|---|---|
| 3 | Spanish (es-ES) |
| 4 | Spanish Latin America (es-419) |
| 5 | German (de) |
| 8 | French (fr) |
| 9 | French Canada (fr-CA) |
| 17 | Portuguese Portugal (pt-PT) |
| 18 | Portuguese Brazil (pt-BR) |
| 23 | Italian (it) |

---

## Standards Compliance

- Follows OpenEMR coding standards (PSR-12)
- Uses OpenEMR escaping functions: `xlt()`, `text()`, `attr()`, `js_escape()`
- CSRF protection on all POST endpoints (`CsrfUtils`)
- Uses `formHeader()` + `formJump()` for redirects (no `header("Location:")`)
- PDF generation via mPDF (OpenEMR native library)
- `require_once` throughout (no `include_once`)
- No external CDN dependencies

---

## Requirements

- OpenEMR 6.0+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+
- mPDF (included in OpenEMR)
