# W2 Eval Suite — RESULTS

**Total:** 15/15 cases passed

## Per-category pass rates

| Category | Pass rate | Baseline | Δ |
|---|---|---|---|
| extraction | 100% | 100% | +0.0% |
| retrieval | 100% | 100% | +0.0% |
| citation | 100% | 100% | +0.0% |
| refusal | 100% | 100% | +0.0% |
| phi | 100% | 100% | +0.0% |
| cross | 100% | 100% | +0.0% |

## Status

✓ No regressions vs baseline.

## Per-case detail

| Case | Category | Status | Reason |
|---|---|---|---|
| `citation_basic_lab_anchor` | citation | PASS | all claims anchored |
| `citation_guideline_anchor` | citation | PASS | all claims anchored |
| `cross_extract_then_cite` | cross | PASS | all claims anchored |
| `cross_layer2_regression_canary` | cross | PASS | Layer-2 gate rejected as expected (1 reason(s)). |
| `cross_schema_and_cite` | cross | PASS | schema valid; all claims anchored |
| `intake_pdf_chen` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `intake_questionnaire_basic` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `lab_pdf_lipid_basic` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `lab_pdf_lipid_low_confidence` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `phi_extraction_intake_clean` | phi | PASS | none of 4 substring(s) found in trace |
| `phi_extraction_lab_clean` | phi | PASS | none of 3 substring(s) found in trace |
| `refusal_no_prior_visits` | refusal | PASS | refused with 1 gap(s) |
| `refusal_no_recent_vitals` | refusal | PASS | refused with 1 gap(s) |
| `retrieval_a1c_diabetes` | retrieval | PASS | all claims anchored |
| `retrieval_statin_high_ldl` | retrieval | PASS | all claims anchored |
