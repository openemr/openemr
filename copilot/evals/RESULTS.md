# W2 Eval Suite — RESULTS

**Total:** 50/50 cases passed

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
| `citation_allergy_anchor` | citation | PASS | all claims anchored |
| `citation_basic_lab_anchor` | citation | PASS | all claims anchored |
| `citation_doc_extracted_lab` | citation | PASS | all claims anchored |
| `citation_encounter_anchor` | citation | PASS | all claims anchored |
| `citation_guideline_anchor` | citation | PASS | all claims anchored |
| `citation_med_anchor` | citation | PASS | all claims anchored |
| `citation_summary_anchor` | citation | PASS | all claims anchored |
| `citation_two_claims_different_tools` | citation | PASS | all claims anchored |
| `citation_two_claims_same_tool` | citation | PASS | all claims anchored |
| `citation_vitals_anchor` | citation | PASS | all claims anchored |
| `cross_extract_then_cite` | cross | PASS | all claims anchored |
| `cross_multi_tool_aggregation` | cross | PASS | all claims anchored |
| `cross_refusal_no_phi_leak` | cross | PASS | refused with 1 gap(s); none of 2 substring(s) found in trace |
| `cross_retrieval_then_cite` | cross | PASS | all claims anchored |
| `cross_schema_and_cite` | cross | PASS | schema valid; all claims anchored |
| `intake_pdf_chen` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `intake_pdf_kowalski` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `intake_pdf_reyes` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `intake_pdf_whitaker` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `intake_questionnaire_basic` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `intake_questionnaire_obese` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `intake_questionnaire_smoker` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `lab_pdf_a1c` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `lab_pdf_creatinine` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `lab_pdf_glucose` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `lab_pdf_lipid_basic` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `lab_pdf_lipid_low_confidence` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `lab_pdf_potassium` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `lab_pdf_total_chol` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `lab_pdf_triglycerides` | extraction | PASS | schema valid; all 2 fields within ±5% |
| `phi_address_telecom_redacted` | phi | PASS | none of 3 substring(s) found in trace |
| `phi_extraction_intake_clean` | phi | PASS | none of 4 substring(s) found in trace |
| `phi_extraction_lab_clean` | phi | PASS | none of 3 substring(s) found in trace |
| `phi_provider_name_pseudonymized` | phi | PASS | none of 2 substring(s) found in trace |
| `phi_pseudonyms_only_in_trace` | phi | PASS | none of 3 substring(s) found in trace |
| `refusal_med_safety_unknown_drug` | refusal | PASS | refused with 1 gap(s) |
| `refusal_no_active_problems` | refusal | PASS | refused with 1 gap(s) |
| `refusal_no_prior_visits` | refusal | PASS | refused with 1 gap(s) |
| `refusal_no_recent_vitals` | refusal | PASS | refused with 1 gap(s) |
| `refusal_off_panel_patient` | refusal | PASS | refused with 1 gap(s) |
| `retrieval_a1c_diabetes` | retrieval | PASS | all claims anchored |
| `retrieval_aspirin_cvd` | retrieval | PASS | all claims anchored |
| `retrieval_bp_target` | retrieval | PASS | all claims anchored |
| `retrieval_glycemic_target_treated` | retrieval | PASS | all claims anchored |
| `retrieval_htn_screening` | retrieval | PASS | all claims anchored |
| `retrieval_metformin_first_line` | retrieval | PASS | all claims anchored |
| `retrieval_shellfish_iodine_ambiguity` | retrieval | PASS | all claims anchored |
| `retrieval_statin_high_ldl` | retrieval | PASS | all claims anchored |
| `retrieval_statin_monitoring` | retrieval | PASS | all claims anchored |
| `retrieval_triglycerides` | retrieval | PASS | all claims anchored |
