# Nursing Care Bundle Form (`cuidados`)

Form for recording ICU nursing care bundle compliance in inpatient settings.

## Features
- Ventilator bundle checklist tracking
- Hand hygiene compliance recording
- Oral rinse documentation
- Secretion suctioning with gloves and assistant
- Daily sedation suspension and extubation evaluation
- Patient positioning recording
- Cuff pressure measurement
- Observations per care item
- PDF report generation (mPDF)
- Full encounter integration

## Recorded Fields
| Field | Description |
|---|---|
| Hand hygiene pre/post suctioning | Compliance yes/no + observations |
| Oral rinse | Performed yes/no + observations |
| Secretion suctioning | With gloves and assistant yes/no |
| Daily sedation suspension | Evaluation status |
| Patient position | Current position + observations |
| Cuff pressure measurement | Performed yes/no + value |
| Care time | Time of bundle check |

## Installation
Run the SQL file to create the table and load translations:
```sql
SOURCE interface/forms/cuidados/table.sql;
```

## Languages
Translations included: English (base), Spanish (es-ES, es-419), German, French, French-CA, Portuguese (pt-PT, pt-BR), Italian.

## Requirements
- OpenEMR 6.0+
- mPDF (included in OpenEMR)
- Patient encounter required before use
