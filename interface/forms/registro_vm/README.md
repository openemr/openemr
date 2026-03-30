# Mechanical Ventilation Record Form (`registro_vm`)

Form for recording mechanical ventilation parameters and settings for ICU patients in inpatient settings.

## Features
- Ventilation mode selection (Spontaneous / Mechanical Ventilation)
- Full ventilation parameter checklist with observations
- Mode-specific parameter tracking
- Observations per parameter
- Active parameter summary
- PDF report generation (mPDF)
- Full encounter integration

## Recorded Fields
| Field | Description |
|---|---|
| Ventilation mode | Spontaneous or Mechanical Ventilation |
| Pressure | Mode active yes/no + observations |
| Volume | Mode active yes/no + observations |
| SIMV | Active yes/no + observations |
| PSV | Active yes/no + observations |
| Other | Active yes/no + observations |
| Respiratory Rate | Active yes/no + observations |
| P.Inspiratory / T.Inspiratory | Active yes/no + observations |
| P.Mean / PEEP | Active yes/no + observations |
| P.Max / P.Plateau | Active yes/no + observations |
| CHST / CDIN | Active yes/no + observations |
| Trigger F/P | Active yes/no + observations |
| F / VT | Active yes/no + observations |
| Tidal Volume / Flow | Active yes/no + observations |
| Programmed/Measured MV | Active yes/no + observations |
| PETCO2 | Active yes/no + observations |
| VD / VT | Active yes/no + observations |
| KO2 | Active yes/no + observations |
| Record time | Time of ventilation check |

## Installation
Run the SQL file to create the table and load translations:
```sql
SOURCE interface/forms/registro_vm/table.sql;
```

## Languages
Translations included: English (base), Spanish (es-ES, es-419), German, French, French-CA, Portuguese (pt-PT, pt-BR), Italian.

## Requirements
- OpenEMR 6.0+
- mPDF (included in OpenEMR)
- Patient encounter required before use
- ICU/inpatient setting recommended
