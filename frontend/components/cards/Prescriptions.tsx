import { fhirGet, FhirError } from "@/lib/fhir/client";
import { bundleEntries } from "@/lib/fhir/bundle";
import { CardShell } from "./CardShell";
import type { Bundle, MedicationRequest } from "@/lib/fhir/types";

function statusBadge(status: string | undefined): { label: string; cls: string } {
  switch (status) {
    case "active":
      return { label: "Active", cls: "bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300" };
    case "completed":
      return { label: "Completed", cls: "bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300" };
    case "stopped":
    case "cancelled":
      return { label: status, cls: "bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300" };
    case "on-hold":
      return { label: "On hold", cls: "bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300" };
    default:
      return { label: status ?? "Unknown", cls: "bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400" };
  }
}

function medName(m: MedicationRequest): string {
  return (
    m.medicationCodeableConcept?.text ??
    m.medicationCodeableConcept?.coding?.[0]?.display ??
    m.medicationReference?.display ??
    "(unnamed medication)"
  );
}

export async function Prescriptions({ patientId }: { patientId: string }) {
  let entries: MedicationRequest[];
  try {
    const bundle = await fhirGet<Bundle<MedicationRequest>>(
      `MedicationRequest?patient=${encodeURIComponent(patientId)}&_sort=-authored`,
    );
    entries = bundleEntries(bundle);
  } catch (err) {
    return (
      <CardShell title="Prescriptions">
        <p className="text-xs text-red-600">
          Could not load prescriptions{err instanceof FhirError ? ` (${err.status})` : ""}.
        </p>
      </CardShell>
    );
  }
  if (entries.length === 0) {
    return (
      <CardShell title="Prescriptions" count={0}>
        <p className="text-xs text-gray-500">No prescriptions on file.</p>
      </CardShell>
    );
  }
  return (
    <CardShell title="Prescriptions" count={entries.length}>
      <ul className="divide-y divide-gray-100 dark:divide-gray-800">
        {entries.map((m) => {
          const badge = statusBadge(m.status);
          return (
            <li key={m.id ?? Math.random()} className="flex items-baseline justify-between gap-2 py-2">
              <div className="min-w-0 flex-1">
                <span className="font-medium">{medName(m)}</span>
                {m.authoredOn && (
                  <span className="ml-2 text-[10px] text-gray-500">{m.authoredOn.slice(0, 10)}</span>
                )}
              </div>
              <span className={`shrink-0 rounded px-1.5 py-0.5 text-[10px] ${badge.cls}`}>{badge.label}</span>
            </li>
          );
        })}
      </ul>
    </CardShell>
  );
}
