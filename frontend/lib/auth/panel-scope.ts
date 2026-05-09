import type { Patient } from "@/lib/fhir/types";

export type PanelDecision = "allow" | "deny" | "unknown-gp-fallthrough";

/**
 * Decide whether a clinician should see a patient based on
 * `Patient.generalPractitioner` references.
 *
 * Mirrors the Co-Pilot service's `_verify_patient_in_panel` logic
 * (`copilot/app/main.py`) including the deliberate empty-GP fallthrough:
 * Synthea data and OpenEMR's R4 transformer often leave
 * `generalPractitioner` empty even when `patient_data.providerID` is set,
 * and a strict deny would 403 the demo. Callers can switch fallthrough
 * to deny via `STRICT_PANEL_SCOPE=true` (see proxy route).
 */
export function inPanel(
  patient: Patient | null,
  openemrUsername: string | undefined,
  adminAllowlist: string[],
): PanelDecision {
  // Admin allowlist short-circuits everything (uniform with the Co-Pilot's
  // COPILOT_ADMIN_USERS bypass). Comparison is exact case-sensitive match
  // because OpenEMR usernames are case-sensitive.
  if (openemrUsername && adminAllowlist.includes(openemrUsername)) {
    return "allow";
  }
  // Without an OpenEMR username, we have no basis to match GPs. Treat as
  // unknown — caller decides via STRICT_PANEL_SCOPE.
  if (!openemrUsername) return "unknown-gp-fallthrough";
  if (!patient) return "deny";

  const refs = (patient.generalPractitioner ?? [])
    .map((gp) => gp.reference ?? "")
    .filter((r) => r.startsWith("Practitioner/"));

  if (refs.length === 0) return "unknown-gp-fallthrough";

  const usernames = refs.map((r) => r.slice("Practitioner/".length));
  return usernames.includes(openemrUsername) ? "allow" : "deny";
}

/** Parse the COPILOT_ADMIN_USERS env into a trimmed username array. */
export function parseAdminAllowlist(env: string | undefined): string[] {
  if (!env) return [];
  return env.split(",").map((s) => s.trim()).filter((s) => s.length > 0);
}
