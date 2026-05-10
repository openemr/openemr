import { renderName } from "@/lib/fhir/patient-name";
import { findIdentifier } from "@/lib/fhir/bundle";
import type { Patient } from "@/lib/fhir/types";

interface PatientHeaderProps {
  patient: Patient;
}

const MRN_SYSTEMS = [
  "http://hl7.org/fhir/sid/us-mrn",
  "urn:oid:1.2.36.146.595.217.0.1",
];
const MRN_FALLBACK_CODES = ["MR", "MRN"];

function formatGender(gender: Patient["gender"]): string {
  if (!gender) return "—";
  return gender.charAt(0).toUpperCase() + gender.slice(1);
}

export function PatientHeader({ patient }: PatientHeaderProps) {
  const name = renderName(patient.name);
  const mrn = findIdentifier(patient.identifier, MRN_SYSTEMS, MRN_FALLBACK_CODES) ?? "—";
  const dob = patient.birthDate ?? "—";
  const sex = formatGender(patient.gender);
  const isActive = patient.active !== false; // missing → assume active

  return (
    <header className="flex items-center gap-6 rounded-md border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-900">
      <div className="flex-1 min-w-0">
        <div className="truncate text-lg font-semibold tracking-tight">{name}</div>
      </div>
      <dl className="flex gap-6 text-xs text-gray-700 dark:text-gray-300">
        <div>
          <dt className="text-[10px] uppercase tracking-wide text-gray-500">DOB</dt>
          <dd className="font-mono">{dob}</dd>
        </div>
        <div>
          <dt className="text-[10px] uppercase tracking-wide text-gray-500">Sex</dt>
          <dd>{sex}</dd>
        </div>
        <div>
          <dt className="text-[10px] uppercase tracking-wide text-gray-500">MRN</dt>
          <dd className="font-mono">{mrn}</dd>
        </div>
        <div>
          <dt className="text-[10px] uppercase tracking-wide text-gray-500">Status</dt>
          <dd>
            <span
              className={
                isActive
                  ? "rounded-full bg-green-100 px-2 py-0.5 text-green-800 dark:bg-green-900/40 dark:text-green-300"
                  : "rounded-full bg-gray-100 px-2 py-0.5 text-gray-700 dark:bg-gray-800 dark:text-gray-300"
              }
            >
              {isActive ? "Active" : "Inactive"}
            </span>
          </dd>
        </div>
      </dl>
    </header>
  );
}
