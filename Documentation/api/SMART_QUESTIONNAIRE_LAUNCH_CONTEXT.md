# SMART Questionnaire Assessment Launch Context

**Author:** Jerry Padgett <sjpadgett@gmail.com>
**Copyright:** Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>

OpenEMR can launch an enabled SMART EHR application from the patient FHIR Assessments workspace while preserving the existing native Start, Continue, and View/Edit assessment actions.

## Token response context

An assessment launch returns the normal patient and optional encounter context. The assessment resources are returned as standard SMART `fhirContext` references:

```json
{
  "patient": "<patient FHIR id>",
  "intent": "questionnaire.assessment.dialog",
  "fhirContext": [
    {"reference": "Questionnaire/<questionnaire FHIR id>"},
    {"reference": "QuestionnaireResponse/<response FHIR id>"}
  ],
  "appContext": "{\"workflow\":\"questionnaire-assessment\",\"action\":\"continue\",\"returnContext\":\"patient-fhir-assessments\"}"
}
```

`QuestionnaireResponse` is omitted when starting a new assessment. The server-derived workflow action is:

- `start` when only a Questionnaire is supplied;
- `continue` for an in-progress QuestionnaireResponse;
- `review` for other existing QuestionnaireResponses.

## Server-side validation

The browser submits only OpenEMR internal Questionnaire and QuestionnaireResponse row identifiers. The SMART launch controller resolves the FHIR UUIDs and validates that:

- the Questionnaire exists and is active;
- the current user can access patient medical information;
- the QuestionnaireResponse belongs to the active patient;
- the QuestionnaireResponse belongs to the selected Questionnaire;
- the QuestionnaireResponse is a patient-level response rather than an encounter response.

The browser cannot provide arbitrary FHIR references or arbitrary `appContext` content.

## Existing launch compatibility

Patient, encounter, and appointment launch behavior remains in place. Existing Appointment context continues to be returned first in `fhirContext`; additional Questionnaire references are appended without replacing it.

## API Explorer testing

Register the API Explorer SMART client after updating its scopes, then launch it from **Patient → Assessments → FHIR Assessments → Launch SMART App**. The Explorer displays the token diagnostics and retrieves the launch Patient, optional Encounter, Appointment, Questionnaire, and QuestionnaireResponse resources when present.
