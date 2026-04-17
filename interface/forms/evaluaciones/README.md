# Nursing Evaluations Form (`evaluaciones`)

Form for recording nursing patient evaluations including Glasgow Coma Scale, vital signs, and clinical assessments in inpatient settings.

## Features
- Glasgow Coma Scale (GCS) assessment (eye, verbal, motor responses)
- Vital signs recording (BP, HR, RR, temperature, O2 saturation)
- Pain scale assessment (numeric 0–10)
- Neurological status evaluation
- Skin integrity assessment
- Nutritional status recording
- Elimination pattern documentation
- Observations and clinical notes
- PDF report generation (mPDF)
- Full encounter integration

## Recorded Fields
| Field | Description |
|---|---|
| Glasgow Eye | Eye opening response (1–4) |
| Glasgow Verbal | Verbal response (1–5) |
| Glasgow Motor | Motor response (1–6) |
| Glasgow Total | Calculated total score |
| Blood pressure | Systolic/diastolic (mmHg) |
| Heart rate | Beats per minute |
| Respiratory rate | Breaths per minute |
| Temperature | Degrees Celsius |
| O2 saturation | Percentage |
| Pain scale | Numeric 0–10 |
| Evaluation time | Time of assessment |

## Installation
Run the SQL file to create the table and load translations:
```sql
SOURCE interface/forms/evaluaciones/table.sql;
```

## Languages
Translations included: English (base), Spanish (es-ES, es-419), German, French, French-CA, Portuguese (pt-PT, pt-BR), Italian.

## Requirements
- OpenEMR 6.0+
- mPDF (included in OpenEMR)
- Patient encounter required before use
