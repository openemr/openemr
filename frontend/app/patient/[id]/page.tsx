import { fhirGet, FhirError } from "@/lib/fhir/client";
import { PatientHeader } from "@/components/PatientHeader";
import { Allergies } from "@/components/cards/Allergies";
import { Problems } from "@/components/cards/Problems";
import { Medications } from "@/components/cards/Medications";
import { Prescriptions } from "@/components/cards/Prescriptions";
import { CareTeam } from "@/components/cards/CareTeam";
import { Encounters } from "@/components/cards/Encounters";
import { CopilotRail } from "@/components/CopilotRail";
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
  const copilotUrl = process.env.COPILOT_URL ?? "";
  return (
    <div className="flex min-h-screen">
      <main className="mx-auto flex max-w-5xl flex-1 flex-col gap-4 p-6">
        <PatientHeader patient={patient} />
        <div className="grid gap-3 md:grid-cols-2">
          <Allergies patientId={id} />
          <Problems patientId={id} />
          <Medications patientId={id} />
          <Prescriptions patientId={id} />
          <CareTeam patientId={id} />
          <Encounters patientId={id} />
        </div>
      </main>
      {copilotUrl && <CopilotRail patientId={id} copilotUrl={copilotUrl} />}
    </div>
  );
}
