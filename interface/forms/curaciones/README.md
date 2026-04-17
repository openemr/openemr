# Nursing Wound Care Form (`curaciones`)

Form for recording nursing wound care procedures in inpatient settings.

## Features
- Wound care tracking (surgical wounds, pressure sores, burns, traumatic wounds)
- Tracheostomy care management
- Ostomy care recording
- Pressure sore assessment and staging
- IV line management (peripheral and central venous lines)
- Observations and clinical notes per procedure
- PDF report generation (mPDF)
- Full encounter integration

## Recorded Fields
| Field | Description |
|---|---|
| Wound care | Type, location, observations |
| Tracheostomy | Status and observations |
| Ostomies | Type and observations |
| Pressure sores | Stage and location |
| Peripheral IV line | Access and observations |
| Central venous line | Access and observations |
| Care time | Time of procedure |

## Installation
Run the SQL file to create the table and load translations:
```sql
SOURCE interface/forms/curaciones/table.sql;
```

## Languages
Translations included: English (base), Spanish (es-ES, es-419), German, French, French-CA, Portuguese (pt-PT, pt-BR), Italian.

## Requirements
- OpenEMR 6.0+
- mPDF (included in OpenEMR)
- Patient encounter required before use
