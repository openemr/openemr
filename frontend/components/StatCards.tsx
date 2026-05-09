/**
 * Three at-a-glance stat tiles for the home page (Patients, Encounters,
 * Active Medications). Uses FHIR `_summary=count` to fetch only the
 * Bundle.total without entries — much cheaper than dragging full payloads
 * across the proxy just to count them.
 *
 * Each tile renders independently of the others; one failed fetch shows
 * "—" in just that tile rather than tearing down the whole row.
 */

import { fhirGet } from "@/lib/fhir/client";
import type { Bundle } from "@/lib/fhir/types";

interface StatTile {
  label: string;
  value: string;
  sublabel?: string;
  tone?: "default" | "blue" | "amber";
}

async function countOf(query: string): Promise<number | null> {
  try {
    const bundle = await fhirGet<Bundle<unknown>>(`${query}${query.includes("?") ? "&" : "?"}_summary=count`);
    return typeof bundle.total === "number" ? bundle.total : null;
  } catch {
    return null;
  }
}

function fmt(n: number | null): string {
  if (n === null) return "—";
  return n.toLocaleString();
}

export async function StatCards() {
  const [patients, encounters, meds] = await Promise.all([
    countOf("Patient"),
    countOf("Encounter"),
    countOf("MedicationRequest?status=active"),
  ]);

  const tiles: StatTile[] = [
    { label: "Patients", value: fmt(patients), tone: "blue" },
    { label: "Encounters", value: fmt(encounters), sublabel: "all-time" },
    { label: "Active medications", value: fmt(meds), tone: "amber" },
  ];

  return (
    <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
      {tiles.map((t) => (
        <div
          key={t.label}
          className="rounded-md border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900"
        >
          <div className="text-xs uppercase tracking-wide text-gray-500">{t.label}</div>
          <div
            className={
              "mt-1 text-2xl font-semibold tabular-nums " +
              (t.tone === "blue"
                ? "text-blue-700 dark:text-blue-300"
                : t.tone === "amber"
                  ? "text-amber-700 dark:text-amber-300"
                  : "text-gray-900 dark:text-gray-100")
            }
          >
            {t.value}
          </div>
          {t.sublabel && <div className="mt-0.5 text-[11px] text-gray-500">{t.sublabel}</div>}
        </div>
      ))}
    </div>
  );
}
