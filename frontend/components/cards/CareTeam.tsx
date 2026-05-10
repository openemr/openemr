import { fhirGet, FhirError } from "@/lib/fhir/client";
import { bundleEntries } from "@/lib/fhir/bundle";
import { CardShell } from "./CardShell";
import type { Bundle, CareTeam as CareTeamResource } from "@/lib/fhir/types";

interface MemberRow {
  member: string;
  role?: string;
}

function flattenMembers(teams: CareTeamResource[]): MemberRow[] {
  const out: MemberRow[] = [];
  for (const t of teams) {
    for (const p of t.participant ?? []) {
      const member = p.member?.display ?? p.member?.reference ?? "(unnamed)";
      const role = p.role?.[0]?.text ?? p.role?.[0]?.coding?.[0]?.display;
      out.push({ member, role });
    }
  }
  return out;
}

export async function CareTeam({ patientId }: { patientId: string }) {
  let entries: CareTeamResource[];
  try {
    const bundle = await fhirGet<Bundle<CareTeamResource>>(
      `CareTeam?patient=${encodeURIComponent(patientId)}`,
    );
    entries = bundleEntries(bundle);
  } catch (err) {
    return (
      <CardShell title="Care Team">
        <p className="text-xs text-red-600">
          Could not load care team{err instanceof FhirError ? ` (${err.status})` : ""}.
        </p>
      </CardShell>
    );
  }
  const members = flattenMembers(entries);
  if (members.length === 0) {
    return (
      <CardShell title="Care Team" count={0}>
        <p className="text-xs text-gray-500">No care team members on file.</p>
      </CardShell>
    );
  }
  return (
    <CardShell title="Care Team" count={members.length}>
      <ul className="divide-y divide-gray-100 dark:divide-gray-800">
        {members.map((m, i) => (
          <li key={`${m.member}-${i}`} className="flex items-baseline justify-between gap-2 py-2">
            <span className="font-medium">{m.member}</span>
            {m.role && <span className="text-xs text-gray-600 dark:text-gray-400">{m.role}</span>}
          </li>
        ))}
      </ul>
    </CardShell>
  );
}
