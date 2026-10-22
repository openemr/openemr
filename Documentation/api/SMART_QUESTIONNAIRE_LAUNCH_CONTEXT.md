# SMART Questionnaire Assessment Launch Context

**Author:** Jerry Padgett <sjpadgett@gmail.com>
**Copyright:** Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>

OpenEMR can launch an enabled SMART EHR application from the patient Questionnaire Assessments workspace while preserving the existing native Start, Continue, and View/Edit assessment actions.

## Token response context

An assessment launch returns the normal patient context. It does not carry the session encounter: assessments launched here are patient-level, so no `encounter` is returned for this intent even when an encounter is open in OpenEMR. The assessment resources are returned as standard SMART `fhirContext` references:

```json
{
  "patient": "<patient FHIR id>",
  "intent": "questionnaire.assessment.dialog",
  "fhirContext": [
    {"type": "Questionnaire", "reference": "Questionnaire/<questionnaire FHIR id>"},
    {"type": "QuestionnaireResponse", "reference": "QuestionnaireResponse/<response FHIR id>"}
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
- an active SMART availability rule offers the Questionnaire to the launching app at the user's facility (SMART launch is opt-in per questionnaire, set on the Availability tab of the Questionnaire Assessments workspace);
- the current user can access patient medical information;
- the QuestionnaireResponse belongs to the active patient;
- the QuestionnaireResponse belongs to the selected Questionnaire;
- the QuestionnaireResponse is a patient-level response rather than an encounter response.

The browser cannot provide arbitrary FHIR references or arbitrary `appContext` content.

## Existing launch compatibility

Patient, encounter, and appointment launch behavior for other intents remains in place. Existing Appointment context continues to be returned first in `fhirContext`; additional Questionnaire references are appended without replacing it.

## API Explorer testing

Register the API Explorer SMART client after updating its scopes, then launch it from **Patient → Assessments → Questionnaires → Launch App**. The Explorer displays the token diagnostics and retrieves the launch Patient, Questionnaire, and QuestionnaireResponse resources, plus an Appointment when one is in context. No Encounter is expected for an assessment launch.
