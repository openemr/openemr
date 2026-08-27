# LBF Form Statements

Rules look at LBF field values and build one paragraph. That paragraph is
saved on a textarea that already exists on the layout. Stock LBF Print
(`interface/forms/LBF/printable.php`) includes it in the form PDF, so you
do not need a second print path or a table just for the text. It is
ordinary `lbf_data` for the field you choose.

Nothing in the PHP is echo-specific or cardiology-specific. Layouts and
rules live in the database, not in this tree.

## Why this is not Questionnaires or LOINC

Questionnaire Assessments are a different form engine. They store FHIR
Questionnaire and QuestionnaireResponse. They do not read `lbf_data` or
`forms.formdir = 'LBF...'`. Existing visit layouts, and the instances
already saved on them, stay LBF. Changing an LBF field type wipes the old
values.

LOINC names the observation (for example `78176-5` aortic root diameter).
It does not ship clinic sentences such as "The aortic root is mildly
dilated," and it does not encode ASE-style numeric bands. You can put a
LOINC code on `layout_options.codes` for export. That metadata does not
write the paragraph. The sentence and the cutoff still go in the rules
editor.

## Install

1. Copy this directory to `interface/modules/custom_modules/oe-module-lbf-statements`.
2. Administration > Modules: Register and enable **LBF Form Statements**.
3. `table.sql` creates `module_lbf_statement_forms`, `module_lbf_statement_rules`,
   and `module_lbf_statement_runs`.

## Use

Open **Modules > Form statements**. You get Generate, and Rules if you have
`admin|super`. On Generate, type a patient name. Encounter forms that have
rules also get a **Form statements** button next to Print.

Generate fills an editable paragraph and writes it to the field you
configured. Open the encounter form and use **Print** for the PDF.

## Add a ruleset

1. Pick any LBF (`layout_options.form_id` starting with `LBF`).
2. On the Rules tab, set the **paragraph field** (a textarea on that layout).
   If the layout has none, the module can add `stmt_paragraph`.
3. Add rule rows: `source_field_id`, `op`, bands or `match_token`, and
   `statement_text` for the sentence. `{source}` and `{source_2}` are
   filled in from the source values.

### Operations

| op | Meaning |
|----|---------|
| `band` | Numeric source is in `[min_value, max_value]` (the inclusive flags apply). If `source_field_id_2` is set, that field has to be present too. |
| `ratio_lt` | Both sources are greater than `min_value` (when that is set) and `source/source_2` is less than `max_value`. |
| `ratio_gt` | `source/source_2` is greater than `min_value`. |
| `parse_severity` | Match a token in the source string (`match_token`, comma-separated): exact option_id, then pipe membership, then a word-boundary match. |

Numeric bands on the same source field cannot overlap. Save is rejected if they do.

## Tests

```bash
vendor/bin/phpunit --testsuite isolated --filter LbfStatements
```

That suite does not boot OpenEMR. The fixtures are synthetic.

## ACL

- Generate: `encounters|notes`
- Rules: `admin|super`

Needs PHP 8.1 or newer. License is GPLv3. Queries are parameterized.
