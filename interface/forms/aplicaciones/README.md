# Nursing Applications Form (`aplicaciones`)

Form for recording nursing medication and fluid applications in inpatient settings.

## Features
- Medication administration tracking
- IV fluid and solution management
- Blood product recording
- Plasma expander documentation
- Saline solution tracking
- Observations per application type
- PDF report generation (mPDF)
- Full encounter integration

## Recorded Fields
| Field | Description |
|---|---|
| Medications | Name, dose, route, observations |
| Saline solutions | Type and volume |
| Blood products | Type and observations |
| Plasma expanders | Type and observations |
| Application time | Time of administration |

## Installation
Run the SQL file to create the table and load translations:
```sql
SOURCE interface/forms/aplicaciones/table.sql;
```

## Languages
Translations included: English (base), Spanish (es-ES, es-419), German, French, French-CA, Portuguese (pt-PT, pt-BR), Italian.

## Requirements
- OpenEMR 6.0+
- mPDF (included in OpenEMR)
- Patient encounter required before use
