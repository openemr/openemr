import { fhirGet, FhirError } from "@/lib/fhir/client";
import { bundleEntries } from "@/lib/fhir/bundle";
import { CardShell } from "./CardShell";
import type { Bundle, Encounter } from "@/lib/fhir/types";

function encounterTypeText(e: Encounter): string {
  const t = e.type?.[0];
  return t?.text ?? t?.coding?.[0]?.display ?? e.class?.display ?? "(encounter)";
}

function periodLabel(e: Encounter): string {
  const start = e.period?.start?.slice(0, 10);
  const end = e.period?.end?.slice(0, 10);
  if (start && end && start !== end) return `${start} → ${end}`;
  return start ?? end ?? "—";
}

function reasonText(e: Encounter): string | null {
  const r = e.reasonCode?.[0];
  return r?.text ?? r?.coding?.[0]?.display ?? null;
}

export async function Encounters({ patientId }: { patientId: string }) {
  let entries: Encounter[];
  try {
    const bundle = await fhirGet<Bundle<Encounter>>(
      `Encounter?patient=${encodeURIComponent(patientId)}&_sort=-date&_count=10`,
    );
    entries = bundleEntries(bundle);
  } catch (err) {
    return (
      <CardShell title="Recent Encounters">
        <p className="text-xs text-red-600">
          Could not load encounters{err instanceof FhirError ? ` (${err.status})` : ""}.
        </p>
      </CardShell>
    );
  }
  if (entries.length === 0) {
    return (
      <CardShell title="Recent Encounters" count={0}>
        <p className="text-xs text-gray-500">No recent encounters.</p>
      </CardShell>
    );
  }
  return (
    <CardShell title="Recent Encounters" count={entries.length}>
      <ul className="divide-y divide-gray-100 dark:divide-gray-800">
        {entries.map((e) => {
          const reason = reasonText(e);
          return (
            <li key={e.id ?? Math.random()} className="py-2">
              <div className="flex items-baseline justify-between gap-2">
                <span className="font-medium">{encounterTypeText(e)}</span>
                <span className="text-[10px] text-gray-500 font-mono">{periodLabel(e)}</span>
              </div>
              {reason && <p className="text-xs text-gray-600 dark:text-gray-400">{reason}</p>}
            </li>
          );
        })}
      </ul>
    </CardShell>
  );
}
