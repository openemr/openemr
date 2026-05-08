# Demo sample documents

Four synthetic clinical documents used in the Week 2 Co-Pilot demo. None
of these contain real PHI — all values are fabricated, the patient names
match Synthea-generated chart subjects, and the layouts mimic typical
US lab + intake form formats so the VLM extraction prompts pick up
cleanly.

| File | Patient | Doc type | Demo storyline |
|---|---|---|---|
| `mariela-intake.pdf` | Mariela Anguiano (47F) | Intake form | UC1 / UC2 — hyperlipidemia + renal context. Meds: chlorpheniramine, amlodipine, atorvastatin. Family hx: father MI age 58. |
| `mariela-lipid-renal.pdf` | Mariela Anguiano | Lipid + renal panel | LDL 190 H, HDL 42, TC 272 H, Trig 220 H, **creatinine 2.72 H, eGFR 22 L**, BUN 38 H. Drives "should this patient be on a statin per USPSTF" + renal-dose UC2. |
| `dana-intake.pdf` | Dana Pollich (2y) | Pediatric intake | UC3 hard-block — **10 allergies including aspirin (anaphylaxis)**. Drives "is aspirin safe for Dana?" → contraindicated verdict. |
| `dana-pediatric-cbc.pdf` | Dana Pollich | Pediatric CBC + BMP | All WNL — establishes baseline labs without distracting from the UC3 storyline. |

## How graders / reviewers can use them

Upload paths in the deployed Co-Pilot:

1. **Iframe drop-zone (recommended for first try):** open the deployed
   OpenEMR at `https://openemr-production-0c8c.up.railway.app/`,
   navigate to a patient's chart (e.g., Mariela — Synthea name
   `Mariela993 Arlette667`, pid 5; or Dana — `Dana512 Fadel536`, pid 9),
   then drag any of these PDFs onto the Co-Pilot iframe rail on the
   right side of the demographics page.

2. **OpenEMR Documents tab (front-desk simulated path):** navigate to a
   patient's chart, click the Documents tab, upload there. The Co-Pilot
   pending-intake banner will surface the upload on next iframe load
   (FHIR `DocumentReference` is the source of truth).

   *(Note: the Documents tab UI has a known rendering quirk on the
   deployed image — see `copilot/HANDOFF.md` if you hit an empty
   uploader. The drop-zone path above always works.)*

## Demo questions to ask after upload

After uploading **`mariela-intake.pdf`** and **`mariela-lipid-renal.pdf`**:

- *"What was the LDL?"* — focused single-value answer with bbox-clickable citation
- *"Should this patient be on a statin per USPSTF?"* — multi-claim answer with Guideline + Patient + Observation evidence cards
- *"Anything to worry about with chlorpheniramine and her renal function?"* — UC2 cross-reference

After uploading **`dana-intake.pdf`**:

- *"Is aspirin safe for Dana?"* — UC3 hard-block via Layer-2 allergy contraindication rule

## Re-generating these files

```bash
cd copilot
docker run --rm -v "$(pwd):/srv" -w /srv copilot-copilot \
  sh -c "pip install -q reportlab && python scripts/generate_demo_fixtures.py"
```

Source: `scripts/generate_demo_fixtures.py`. Output is deterministic.
