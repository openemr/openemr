import { fhirGet, FhirError } from "@/lib/fhir/client";
import { bundleEntries } from "@/lib/fhir/bundle";
import { CardShell } from "./CardShell";
import type { Bundle, Condition } from "@/lib/fhir/types";

function statusBadge(status: string | undefined): { label: string; cls: string } {
  switch (status) {
    case "active":
      return { label: "Active", cls: "bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300" };
    case "resolved":
      return { label: "Resolved", cls: "bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300" };
    case "inactive":
      return { label: "Inactive", cls: "bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400" };
    default:
      return { label: status ?? "Unknown", cls: "bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400" };
  }
}

export async function Problems({ patientId }: { patientId: string }) {
  let entries: Condition[];
  try {
    const bundle = await fhirGet<Bundle<Condition>>(
      `Condition?patient=${encodeURIComponent(patientId)}&category=problem-list-item`,
    );
    entries = bundleEntries(bundle);
  } catch (err) {
    return (
      <CardShell title="Problem List">
        <p className="text-xs text-red-600">
          Could not load problems{err instanceof FhirError ? ` (${err.status})` : ""}.
        </p>
      </CardShell>
    );
  }
  if (entries.length === 0) {
    return (
      <CardShell title="Problem List" count={0}>
        <p className="text-xs text-gray-500">No active problems.</p>
      </CardShell>
    );
  }
  return (
    <CardShell title="Problem List" count={entries.length}>
      <ul className="divide-y divide-gray-100 dark:divide-gray-800">
        {entries.map((c) => {
          const status = c.clinicalStatus?.coding?.[0]?.code ?? c.clinicalStatus?.text;
          const badge = statusBadge(status);
          return (
            <li key={c.id ?? Math.random()} className="flex items-baseline justify-between gap-2 py-2">
              <span className="font-medium">
                {c.code?.text ?? c.code?.coding?.[0]?.display ?? "(unspecified)"}
              </span>
              <div className="flex items-center gap-2 text-[10px]">
                {c.onsetDateTime && <span className="text-gray-500">{c.onsetDateTime.slice(0, 10)}</span>}
                <span className={`rounded px-1.5 py-0.5 ${badge.cls}`}>{badge.label}</span>
              </div>
            </li>
          );
        })}
      </ul>
    </CardShell>
  );
}
