import { fhirGet, FhirError } from "@/lib/fhir/client";
import { bundleEntries } from "@/lib/fhir/bundle";
import { CardShell } from "./CardShell";
import type { AllergyIntolerance, Bundle } from "@/lib/fhir/types";

function severityClass(criticality: AllergyIntolerance["criticality"]): string {
  if (criticality === "high") return "text-red-700 dark:text-red-300";
  if (criticality === "low") return "text-amber-700 dark:text-amber-300";
  return "text-gray-700 dark:text-gray-300";
}

function reactionText(allergy: AllergyIntolerance): string {
  const r = allergy.reaction?.[0];
  if (!r) return "";
  return r.manifestation?.map((m) => m.text ?? m.coding?.[0]?.display).filter(Boolean).join(", ") ?? "";
}

export async function Allergies({ patientId }: { patientId: string }) {
  let entries: AllergyIntolerance[];
  try {
    const bundle = await fhirGet<Bundle<AllergyIntolerance>>(
      `AllergyIntolerance?patient=${encodeURIComponent(patientId)}`,
    );
    entries = bundleEntries(bundle);
  } catch (err) {
    return (
      <CardShell title="Allergies">
        <p className="text-xs text-red-600">
          Could not load allergies{err instanceof FhirError ? ` (${err.status})` : ""}.
        </p>
      </CardShell>
    );
  }
  if (entries.length === 0) {
    return (
      <CardShell title="Allergies" count={0}>
        <p className="text-xs text-gray-500">No known allergies on file.</p>
      </CardShell>
    );
  }
  return (
    <CardShell title="Allergies" count={entries.length}>
      <ul className="divide-y divide-gray-100 dark:divide-gray-800">
        {entries.map((a) => (
          <li key={a.id ?? Math.random()} className="py-2">
            <div className="flex items-baseline justify-between gap-2">
              <span className={`font-medium ${severityClass(a.criticality)}`}>
                {a.code?.text ?? a.code?.coding?.[0]?.display ?? "(unspecified)"}
              </span>
              {a.recordedDate && <span className="text-[10px] text-gray-500">{a.recordedDate}</span>}
            </div>
            {reactionText(a) && (
              <p className="text-xs text-gray-600 dark:text-gray-400">→ {reactionText(a)}</p>
            )}
          </li>
        ))}
      </ul>
    </CardShell>
  );
}
