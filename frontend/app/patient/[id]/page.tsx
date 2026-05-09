import { fhirGet, FhirError } from "@/lib/fhir/client";
import { PatientHeader } from "@/components/PatientHeader";
import type { Patient } from "@/lib/fhir/types";

interface PageProps {
  params: Promise<{ id: string }>;
}

export default async function PatientPage({ params }: PageProps) {
  const { id } = await params;
  let patient: Patient;
  try {
    patient = await fhirGet<Patient>(`Patient/${encodeURIComponent(id)}`);
  } catch (err) {
    if (err instanceof FhirError && err.status === 401) {
      return (
        <main className="mx-auto max-w-3xl p-6">
          <p className="text-sm text-gray-600 dark:text-gray-400">
            Not signed in. <a className="underline" href="/api/auth/login">Sign in with OpenEMR</a>.
          </p>
        </main>
      );
    }
    throw err;
  }
  return (
    <main className="mx-auto flex max-w-5xl flex-col gap-4 p-6">
      <PatientHeader patient={patient} />
      {/* Cards land in subsequent tasks (5.2 / 5.3). */}
    </main>
  );
}
