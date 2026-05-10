import { fhirGet, FhirError } from "@/lib/fhir/client";
import { bundleEntries } from "@/lib/fhir/bundle";
import { CardShell } from "./CardShell";
import type { Bundle, MedicationRequest } from "@/lib/fhir/types";

function medicationName(m: MedicationRequest): string {
  return (
    m.medicationCodeableConcept?.text ??
    m.medicationCodeableConcept?.coding?.[0]?.display ??
    m.medicationReference?.display ??
    "(unnamed medication)"
  );
}

function dosageText(m: MedicationRequest): string | null {
  const d = m.dosageInstruction?.[0];
  return d?.text ?? null;
}

export async function Medications({ patientId }: { patientId: string }) {
  let entries: MedicationRequest[];
  try {
    const bundle = await fhirGet<Bundle<MedicationRequest>>(
      `MedicationRequest?patient=${encodeURIComponent(patientId)}&status=active`,
    );
    entries = bundleEntries(bundle);
  } catch (err) {
    return (
      <CardShell title="Active Medications">
        <p className="text-xs text-red-600">
          Could not load medications{err instanceof FhirError ? ` (${err.status})` : ""}.
        </p>
      </CardShell>
    );
  }
  if (entries.length === 0) {
    return (
      <CardShell title="Active Medications" count={0}>
        <p className="text-xs text-gray-500">No active medications.</p>
      </CardShell>
    );
  }
  return (
    <CardShell title="Active Medications" count={entries.length}>
      <ul className="divide-y divide-gray-100 dark:divide-gray-800">
        {entries.map((m) => {
          const dose = dosageText(m);
          return (
            <li key={m.id ?? Math.random()} className="py-2">
              <div className="font-medium">{medicationName(m)}</div>
              {dose && <p className="text-xs text-gray-600 dark:text-gray-400">{dose}</p>}
            </li>
          );
        })}
      </ul>
    </CardShell>
  );
}
