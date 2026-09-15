# Synthetic EHI export samples

These CSV files demonstrate how related clinical records can appear in an
OpenEMR EHI export. Every person, identifier, address, date, and clinical event
in this directory is fictional and was created only for documentation.

The samples are intentionally small and focus on commonly used columns. An
actual export may contain additional columns documented in the generated EHI
schema reference.

## Relationships

| File | Key relationship |
| --- | --- |
| `patient_data.csv` | `pid` identifies the fictional patient |
| `form_encounter.csv` | `pid` links to the patient; `encounter` identifies the visit |
| `lists.csv` | `pid` links problems and allergies to the patient |
| `prescriptions.csv` | `patient_id` and `encounter` link medication orders to the patient and visit |
| `procedure_order.csv` | `patient_id` and `encounter_id` link lab orders to the patient and visit |

For example, patient `1001` has encounter `5001`, a hypertension problem, an
active lisinopril prescription, and a basic metabolic panel order. Patient
`1002` has encounter `5002`, a penicillin allergy, and a complete blood count
order.

## Important limitations

- These files are examples, not an import package or a complete OpenEMR export.
- Blank values demonstrate nullable or unavailable data.
- Codes are illustrative and should be validated against the terminology and
  configuration used by a real installation.
- Never use real patient or protected health information in documentation,
  tests, screenshots, or bug reports.
