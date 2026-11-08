# LForms Migration: Native FHIR Questionnaire Runtime

Status: the native FHIR Questionnaire runtime is the sole questionnaire
renderer in OpenEMR. LForms has been removed, not deprecated — there is no
rollback path by design. Maintaining a second renderer would mean testing and
securing two rendering stacks indefinitely; committing to one FHIR-native
runtime keeps questionnaire storage, rendering, and the FHIR API on a single
representation.

## What works unchanged

Every questionnaire whose repository record carries a FHIR Questionnaire —
that is, `questionnaire_repository.questionnaire` holds valid FHIR JSON —
renders in the native runtime with no migration step. This covers all
questionnaires imported as FHIR and all imports where an LForms source was
converted to FHIR at import time. Existing patient responses are stored as
FHIR QuestionnaireResponse resources and open in the native runtime for
review or continuation; legacy response status values (`incomplete`,
`active`) are honored as in-progress, so partially completed assessments
resume in edit mode rather than opening read-only.

Import validation is now strict: a questionnaire whose items have malformed
shapes is rejected at import with an error naming the item path and field,
rather than being stored and failing later at render or API time.
Double-encoded array fields (a defect produced by some earlier conversions)
are repaired automatically on import; existing affected rows can be repaired
in place with `contrib/util/repair_questionnaire_json.php` (dry-run by
default, backups on `--fix`).

## Legacy LForms-only questionnaires

A repository record that has only LForms content — `lform` populated but no
FHIR JSON in `questionnaire` — cannot be rendered. Opening one produces a
clear error ("The repository record does not contain a FHIR Questionnaire.")
and no data is touched: the LForms definition remains stored, and any
historical responses remain in the database untouched.

To find affected records:

```sql
SELECT id, name FROM questionnaire_repository
WHERE (questionnaire IS NULL OR questionnaire = '')
  AND lform IS NOT NULL AND lform != '';
```

A conversion tool that translates stored LForms definitions to FHIR
Questionnaires (validated through the same strict import path) is planned as
a follow-up pull request. Until a record is converted, it is inert but
preserved. Sites with no rows returned by the query above have nothing to do.

## For questionnaire authors

Author and import questionnaires as FHIR. The runtime supports the item types
`group`, `display`, `boolean`, `decimal`, `integer`, `date`, `dateTime`,
`time`, `string`, `text`, `url`, `choice`, `open-choice`, and `quantity`,
conditional display via `enableWhen`/`enableBehavior`, and SDC calculated
expressions (`sdc-questionnaire-calculatedExpression` with `variable`
extensions, FHIRPath `text/fhirpath`). Unsupported item types render an
explicit warning naming the type rather than failing silently.
