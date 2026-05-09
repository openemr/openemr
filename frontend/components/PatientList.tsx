/**
 * Server component that lists recent patients on the home page.
 *
 * Fills the bridge between the sign-in front door and the per-patient
 * dashboard at /patient/[id]. Mirrors the legacy OpenEMR finder
 * (interface/main/finder/dynamic_finder.php) shape: name, DOB, sex, MRN.
 *
 * Fetches via the dashboard's own /api/fhir proxy so panel-scope and
 * OAuth bearer injection happen server-side in the proxy route.
 */

import Link from "next/link";
import { fhirGet, FhirError } from "@/lib/fhir/client";
import { bundleEntries, findIdentifier } from "@/lib/fhir/bundle";
import { renderName } from "@/lib/fhir/patient-name";
import type { Bundle, Patient } from "@/lib/fhir/types";

const MRN_SYSTEMS = [
  "http://hl7.org/fhir/sid/us-mrn",
  "urn:oid:1.2.36.146.595.217.0.1",
];
const MRN_FALLBACK_CODES = ["MR", "MRN"];

function formatGender(gender: Patient["gender"]): string {
  if (!gender) return "—";
  return gender.charAt(0).toUpperCase() + gender.slice(1);
}

interface PatientListProps {
  /** Max rows to fetch. Defaults to 25. */
  count?: number;
}

export async function PatientList({ count = 25 }: PatientListProps = {}) {
  let patients: Patient[];
  let total: number | undefined;
  try {
    const bundle = await fhirGet<Bundle<Patient>>(
      `Patient?_count=${encodeURIComponent(String(count))}&_sort=family`,
    );
    patients = bundleEntries(bundle);
    total = bundle.total;
  } catch (err) {
    return (
      <section className="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
        <p>
          Could not load patient list{err instanceof FhirError ? ` (${err.status})` : ""}.
          {err instanceof FhirError && err.status === 401 && (
            <> Try signing out and back in.</>
          )}
        </p>
      </section>
    );
  }

  if (patients.length === 0) {
    return (
      <section className="rounded-md border border-gray-200 bg-white p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
        <p>No patients found.</p>
        <p className="mt-1 text-xs">
          Use the legacy OpenEMR UI to create patient records, then return here.
        </p>
      </section>
    );
  }

  return (
    <section className="rounded-md border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
      <header className="flex items-baseline justify-between border-b border-gray-100 px-4 py-2 dark:border-gray-800">
        <h2 className="text-sm font-semibold tracking-tight">Patients</h2>
        <span className="text-xs text-gray-500">
          Showing {patients.length}
          {typeof total === "number" && total > patients.length && ` of ${total}`}
        </span>
      </header>
      <div className="overflow-x-auto">
        <table className="w-full text-left text-sm">
          <thead className="border-b border-gray-100 text-xs uppercase tracking-wide text-gray-500 dark:border-gray-800">
            <tr>
              <th scope="col" className="px-4 py-2 font-medium">Name</th>
              <th scope="col" className="px-4 py-2 font-medium">DOB</th>
              <th scope="col" className="px-4 py-2 font-medium">Sex</th>
              <th scope="col" className="px-4 py-2 font-medium">MRN</th>
            </tr>
          </thead>
          <tbody>
            {patients.map((p) => {
              const name = renderName(p.name);
              const dob = p.birthDate ?? "—";
              const sex = formatGender(p.gender);
              const mrn = findIdentifier(p.identifier, MRN_SYSTEMS, MRN_FALLBACK_CODES) ?? "—";
              const id = p.id ?? "";
              return (
                <tr
                  key={id || Math.random()}
                  className="border-b border-gray-50 hover:bg-blue-50 dark:border-gray-800 dark:hover:bg-blue-900/20"
                >
                  <td className="px-4 py-2">
                    {id ? (
                      <Link
                        href={`/patient/${encodeURIComponent(id)}`}
                        className="font-medium text-blue-700 hover:underline dark:text-blue-300"
                      >
                        {name}
                      </Link>
                    ) : (
                      <span className="text-gray-500">{name}</span>
                    )}
                  </td>
                  <td className="px-4 py-2 font-mono text-xs text-gray-700 dark:text-gray-300">{dob}</td>
                  <td className="px-4 py-2 text-gray-700 dark:text-gray-300">{sex}</td>
                  <td className="px-4 py-2 font-mono text-xs text-gray-700 dark:text-gray-300">{mrn}</td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </section>
  );
}
