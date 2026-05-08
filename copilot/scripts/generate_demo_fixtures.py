"""Generate clinically-coherent demo fixtures for the W2 demo heroes.

Two patients (Mariela 47F UC1/UC2 hero, Dana 2y UC3 hero), four PDFs:

  - mariela-intake.pdf       — chlorpheniramine, NKDA, family CVD history
  - mariela-lipid-renal.pdf  — LDL 190 H, creatinine 2.72 H (eGFR 22)
  - dana-intake.pdf          — 10 allergies including aspirin (the
                               UC3 hard-block trigger)
  - dana-pediatric-cbc.pdf   — routine pediatric labs, all WNL

Output directory: ../sample-documents/ (relative to this script;
i.e. ``copilot/sample-documents/``). The four PDFs are checked into
the repo so graders can upload them directly without re-running this
script.

Each PDF mirrors the layout of evals/fixtures/documents/lab-lipid-small.pdf
so the VLM extraction prompts pick up the values cleanly. The per-patient
content matches what's in the deployed Railway OpenEMR's chart for these
two Synthea patients (LDL 190 / chlorpheniramine / creatinine 2.72 for
Mariela; 10 allergies including aspirin for Dana).
"""
from __future__ import annotations

from pathlib import Path

from reportlab.lib.pagesizes import LETTER
from reportlab.pdfgen import canvas


# ---------------------------------------------------------------- Mariela

def write_mariela_intake(path: Path) -> None:
    c = canvas.Canvas(str(path), pagesize=LETTER)
    c.setFont("Helvetica-Bold", 16)
    c.drawString(72, 740, "Synthetic Practice — Patient Intake Form")
    c.setFont("Helvetica", 10)
    c.drawString(72, 720, "Form completed: 2026-04-25     Form ID: INTAKE-2026-MA-0042")
    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 690, "Patient")
    c.setFont("Helvetica", 11)
    c.drawString(72, 670, "Name: Mariela Anguiano")
    c.drawString(72, 654, "DOB: 1979-02-14    Age: 47    Sex: F")
    c.drawString(72, 638, "MRN (chart): on file")

    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 608, "Chief Concern")
    c.setFont("Helvetica", 11)
    c.drawString(72, 590, "Follow-up on prior abnormal lipid panel; reports occasional fatigue.")
    c.drawString(72, 574, "Notes worsening swelling in ankles over the past two weeks.")

    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 540, "Current Medications")
    c.setFont("Helvetica", 11)
    c.drawString(96, 522, "- Chlorpheniramine 4 mg PO every 6 hours as needed")
    c.drawString(96, 506, "- Amlodipine 5 mg PO daily")
    c.drawString(96, 490, "- Atorvastatin 20 mg PO daily (started 2025-11)")

    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 460, "Allergies")
    c.setFont("Helvetica", 11)
    c.drawString(96, 442, "- NKDA (no known drug allergies)")

    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 412, "Family History")
    c.setFont("Helvetica", 11)
    c.drawString(96, 394, "- Mother: Type 2 diabetes; hypertension")
    c.drawString(96, 378, "- Father: Myocardial infarction at age 58")
    c.drawString(96, 362, "- Sister: Hyperlipidemia")

    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 332, "Social History")
    c.setFont("Helvetica", 11)
    c.drawString(96, 314, "- Tobacco: Never")
    c.drawString(96, 298, "- Alcohol: Occasional, social only")
    c.drawString(96, 282, "- Exercise: Walks 20 min, 3 days/week")

    c.setFont("Helvetica-Oblique", 9)
    c.drawString(72, 80, "Synthetic data for AgentForge demo. Not a real patient.")
    c.showPage()
    c.save()


def write_mariela_lab(path: Path) -> None:
    c = canvas.Canvas(str(path), pagesize=LETTER)
    c.setFont("Helvetica-Bold", 16)
    c.drawString(72, 740, "Synthetic Reference Lab — Lipid + Renal Panel")
    c.setFont("Helvetica", 10)
    c.drawString(72, 720, "Patient: Anguiano, Mariela     DOB: 1979-02-14")
    c.drawString(72, 706, "Collected: 2026-04-25     Reported: 2026-04-26")
    c.drawString(72, 692, "Order #: LAB-2026-04-26-MA-0042     Provider: Practice Internal")

    # --- Lipid block ---
    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 654, "Lipid Panel")
    c.setFont("Helvetica-Bold", 11)
    c.drawString(72, 634, "Test                          Value    Unit       Ref Range     Flag")
    c.setFont("Helvetica", 11)
    c.drawString(72, 614, "LDL Cholesterol               190      mg/dL      <100          H")
    c.drawString(72, 598, "HDL Cholesterol                42      mg/dL      >40           N")
    c.drawString(72, 582, "Total Cholesterol             272      mg/dL      <200          H")
    c.drawString(72, 566, "Triglycerides                 220      mg/dL      <150          H")

    # --- Renal block ---
    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 528, "Renal Function")
    c.setFont("Helvetica-Bold", 11)
    c.drawString(72, 508, "Test                          Value    Unit       Ref Range     Flag")
    c.setFont("Helvetica", 11)
    c.drawString(72, 488, "Creatinine                    2.72     mg/dL      0.6-1.1       H")
    c.drawString(72, 472, "eGFR (CKD-EPI)                 22      mL/min     >=60          L")
    c.drawString(72, 456, "BUN                            38      mg/dL      7-20          H")

    c.setFont("Helvetica-Oblique", 9)
    c.drawString(72, 416, "Interpretation: Persistently elevated LDL despite ongoing statin;")
    c.drawString(72, 402, "creatinine and eGFR consistent with stage 4 CKD. Recommend")
    c.drawString(72, 388, "review of medication dosing for renal clearance.")

    c.setFont("Helvetica-Oblique", 9)
    c.drawString(72, 80, "Synthetic data for AgentForge demo. Not a real patient.")
    c.showPage()
    c.save()


# ---------------------------------------------------------------- Dana

def write_dana_intake(path: Path) -> None:
    c = canvas.Canvas(str(path), pagesize=LETTER)
    c.setFont("Helvetica-Bold", 16)
    c.drawString(72, 740, "Synthetic Practice — Pediatric Intake Form")
    c.setFont("Helvetica", 10)
    c.drawString(72, 720, "Form completed: 2026-04-25     Form ID: INTAKE-2026-PED-0011")
    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 690, "Patient")
    c.setFont("Helvetica", 11)
    c.drawString(72, 670, "Name: Dana Pollich")
    c.drawString(72, 654, "DOB: 2024-01-08    Age: 2 years    Sex: F")
    c.drawString(72, 638, "Parent / Guardian: Sandra Pollich")

    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 608, "Chief Concern")
    c.setFont("Helvetica", 11)
    c.drawString(72, 590, "Well-child visit. Parent reports low-grade fever last week, resolved.")

    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 560, "Current Medications")
    c.setFont("Helvetica", 11)
    c.drawString(96, 542, "- None")

    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 512, "Allergies")
    c.setFont("Helvetica", 11)
    c.drawString(96, 494, "- Aspirin (acetylsalicylic acid) -- anaphylaxis")
    c.drawString(96, 478, "- Penicillin -- diffuse rash, age 1")
    c.drawString(96, 462, "- Amoxicillin -- diffuse rash, age 1")
    c.drawString(96, 446, "- Sulfonamides -- urticaria")
    c.drawString(96, 430, "- Codeine -- vomiting (parent suspects intolerance not allergy)")
    c.drawString(96, 414, "- Eggs -- urticaria around the mouth")
    c.drawString(96, 398, "- Peanuts -- urticaria, parent avoiding")
    c.drawString(96, 382, "- Tree nuts -- urticaria, parent avoiding")
    c.drawString(96, 366, "- Latex -- contact rash (noted at prior outpatient procedure)")
    c.drawString(96, 350, "- Dust mites -- rhinitis, eczema flare")

    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 320, "Family History")
    c.setFont("Helvetica", 11)
    c.drawString(96, 302, "- Mother: Atopic dermatitis, asthma")
    c.drawString(96, 286, "- Father: Seasonal allergies")
    c.drawString(96, 270, "- Sibling: None")

    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 240, "Social / Developmental")
    c.setFont("Helvetica", 11)
    c.drawString(96, 222, "- Daycare: 3 days/week")
    c.drawString(96, 206, "- Immunizations: up to date through 18-month")
    c.drawString(96, 190, "- Developmental milestones: meeting age-appropriate")

    c.setFont("Helvetica-Oblique", 9)
    c.drawString(72, 80, "Synthetic data for AgentForge demo. Not a real patient.")
    c.showPage()
    c.save()


def write_dana_lab(path: Path) -> None:
    c = canvas.Canvas(str(path), pagesize=LETTER)
    c.setFont("Helvetica-Bold", 16)
    c.drawString(72, 740, "Synthetic Reference Lab — Pediatric CBC + BMP")
    c.setFont("Helvetica", 10)
    c.drawString(72, 720, "Patient: Pollich, Dana     DOB: 2024-01-08")
    c.drawString(72, 706, "Collected: 2026-04-25     Reported: 2026-04-26")
    c.drawString(72, 692, "Order #: LAB-2026-04-26-PED-0011")

    # --- CBC block ---
    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 654, "Complete Blood Count")
    c.setFont("Helvetica-Bold", 11)
    c.drawString(72, 634, "Test                          Value    Unit       Ref Range     Flag")
    c.setFont("Helvetica", 11)
    c.drawString(72, 614, "Hemoglobin                    12.5     g/dL       11.0-13.5     N")
    c.drawString(72, 598, "Hematocrit                    37.2     %          33-39         N")
    c.drawString(72, 582, "WBC                            9.2     10^3/uL    5.5-15.5      N")
    c.drawString(72, 566, "Platelets                      280     10^3/uL    150-450       N")

    # --- BMP block ---
    c.setFont("Helvetica-Bold", 12)
    c.drawString(72, 528, "Basic Metabolic Panel")
    c.setFont("Helvetica-Bold", 11)
    c.drawString(72, 508, "Test                          Value    Unit       Ref Range     Flag")
    c.setFont("Helvetica", 11)
    c.drawString(72, 488, "Glucose (random)               92      mg/dL      60-100        N")
    c.drawString(72, 472, "Creatinine                    0.40     mg/dL      0.2-0.6       N")
    c.drawString(72, 456, "BUN                             8      mg/dL      5-18          N")
    c.drawString(72, 440, "Sodium                        138      mmol/L     135-145       N")
    c.drawString(72, 424, "Potassium                     4.2      mmol/L     3.5-5.0       N")

    c.setFont("Helvetica-Oblique", 9)
    c.drawString(72, 384, "Interpretation: All values within pediatric reference ranges.")

    c.setFont("Helvetica-Oblique", 9)
    c.drawString(72, 80, "Synthetic data for AgentForge demo. Not a real patient.")
    c.showPage()
    c.save()


def main() -> None:
    # Default output: copilot/sample-documents/ relative to this script.
    out = (Path(__file__).resolve().parent.parent / "sample-documents").resolve()
    out.mkdir(parents=True, exist_ok=True)
    write_mariela_intake(out / "mariela-intake.pdf")
    write_mariela_lab(out / "mariela-lipid-renal.pdf")
    write_dana_intake(out / "dana-intake.pdf")
    write_dana_lab(out / "dana-pediatric-cbc.pdf")
    print(f"OK: 4 fixtures written under {out}")
    for p in sorted(out.glob("*.pdf")):
        print(f"  {p.name}  ({p.stat().st_size} bytes)")


if __name__ == "__main__":
    main()
